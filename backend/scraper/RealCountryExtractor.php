<?php
/**
 * Real Country Extractor - Extracts actual country data from live Shopify review pages
 * NO statistical guessing - only real data from Shopify HTML
 */

require_once __DIR__ . '/../utils/DatabaseManager.php';

class RealCountryExtractor {
    private $conn;
    
    public function __construct() {
        $dbManager = new DatabaseManager();
        $this->conn = $dbManager->getConnection();
    }
    
    /**
     * Extract real countries from live Shopify pages
     */
    public function extractRealCountries($appName, $appSlug) {
        echo "🌍 EXTRACTING REAL COUNTRIES FROM LIVE SHOPIFY PAGES\n";
        echo "App: $appName ($appSlug)\n";
        echo "================================================\n\n";
        
        $baseUrl = "https://apps.shopify.com/$appSlug/reviews";
        $updated = 0;
        
        // Scrape multiple pages to get real country data
        for ($page = 1; $page <= 10; $page++) {
            $url = $baseUrl . "?sort_by=newest&page=$page";
            echo "📄 Scraping page $page: $url\n";
            
            $html = $this->fetchPage($url);
            if (!$html) {
                echo "❌ Failed to fetch page $page\n";
                continue;
            }
            
            $realReviews = $this->extractRealReviewData($html);
            if (empty($realReviews)) {
                echo "⚠️ No reviews found on page $page\n";
                continue;
            }
            
            echo "✅ Found " . count($realReviews) . " reviews on page $page\n";
            
            // Update database with real country data
            foreach ($realReviews as $review) {
                if ($this->updateReviewCountry($appName, $review)) {
                    $updated++;
                    echo "✅ {$review['store_name']} -> {$review['country_name']}\n";
                }
            }
            
            // Add delay to avoid rate limiting
            sleep(2);
        }
        
        echo "\n🎯 Updated $updated reviews with real country data\n";
        return $updated;
    }
    
    /**
     * Extract real review data from Shopify HTML
     */
    private function extractRealReviewData($html) {
        $reviews = [];
        
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        
        // Find review containers - try multiple selectors
        $reviewSelectors = [
            '//div[contains(@class, "review-listing")]',
            '//div[contains(@class, "tw-border-b") and .//div[contains(@class, "tw-text-heading-xs")]]',
            '//article[contains(@class, "review")]',
            '//li[contains(@class, "review")]'
        ];
        
        foreach ($reviewSelectors as $selector) {
            $reviewNodes = $xpath->query($selector);
            if ($reviewNodes->length > 0) {
                echo "🔍 Using selector: $selector (found " . $reviewNodes->length . " nodes)\n";
                
                foreach ($reviewNodes as $node) {
                    $review = $this->extractSingleReview($xpath, $node);
                    if ($review && $review['country_name'] !== 'Unknown') {
                        $reviews[] = $review;
                    }
                }
                
                if (!empty($reviews)) {
                    break; // Use first working selector
                }
            }
        }
        
        return $reviews;
    }
    
    /**
     * Extract single review data from HTML node
     */
    private function extractSingleReview($xpath, $node) {
        try {
            // Extract store name
            $storeName = $this->extractStoreName($xpath, $node);
            if (empty($storeName)) {
                return null;
            }
            
            // Extract country - this is the critical part
            $country = $this->extractRealCountry($xpath, $node);
            
            return [
                'store_name' => $storeName,
                'country_name' => $country
            ];
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Extract store name from review node
     */
    private function extractStoreName($xpath, $node) {
        $storeSelectors = [
            './/div[contains(@class, "tw-text-heading-xs") and contains(@class, "tw-text-fg-primary")]',
            './/h3[contains(@class, "tw-text-heading-xs")]',
            './/div[contains(@class, "tw-text-heading")]',
            './/strong'
        ];
        
        foreach ($storeSelectors as $selector) {
            $storeNodes = $xpath->query($selector, $node);
            if ($storeNodes->length > 0) {
                $storeName = trim($storeNodes->item(0)->textContent);
                if (!empty($storeName) && strlen($storeName) > 2) {
                    return $storeName;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Extract REAL country from Shopify HTML structure
     */
    private function extractRealCountry($xpath, $node) {
        // Strategy 1: Look for country in merchant info sections
        $countrySelectors = [
            // Look for text elements that might contain country
            './/div[contains(@class, "tw-text-body-xs") and contains(@class, "tw-text-fg-tertiary")]',
            './/span[contains(@class, "tw-text-body-xs") and contains(@class, "tw-text-fg-tertiary")]',
            './/div[contains(@class, "tw-text-body-xs") and contains(@class, "tw-text-fg-secondary")]',
            './/span[contains(@class, "tw-text-body-xs") and contains(@class, "tw-text-fg-secondary")]',
            './/div[contains(@class, "tw-text-fg-tertiary")]',
            './/span[contains(@class, "tw-text-fg-tertiary")]'
        ];
        
        foreach ($countrySelectors as $selector) {
            $nodes = $xpath->query($selector, $node);
            foreach ($nodes as $countryNode) {
                $text = trim($countryNode->textContent);
                
                // Skip obvious non-country text
                if (empty($text) || 
                    preg_match('/\d{4}/', $text) || // Years
                    preg_match('/\d+\s+(day|week|month|year|hour)/', $text) || // Time periods
                    stripos($text, 'using') !== false ||
                    stripos($text, 'replied') !== false ||
                    stripos($text, 'helpful') !== false ||
                    stripos($text, 'ago') !== false ||
                    strlen($text) < 3 || strlen($text) > 50) {
                    continue;
                }
                
                // Check if it's a valid country
                $validatedCountry = $this->validateRealCountry($text);
                if ($validatedCountry !== 'Unknown') {
                    return $validatedCountry;
                }
            }
        }
        
        // Strategy 2: Look in the parent container structure
        $parentContainer = $node->parentNode;
        if ($parentContainer) {
            $allText = $parentContainer->textContent;
            $lines = explode("\n", $allText);
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $validatedCountry = $this->validateRealCountry($line);
                    if ($validatedCountry !== 'Unknown') {
                        return $validatedCountry;
                    }
                }
            }
        }
        
        return 'Unknown';
    }
    
    /**
     * Validate if text is a real country name
     */
    private function validateRealCountry($text) {
        if (empty($text)) return 'Unknown';
        
        // List of real countries that appear on Shopify
        $realCountries = [
            'United States', 'United Kingdom', 'Canada', 'Australia', 'Germany', 'France',
            'Netherlands', 'Sweden', 'Norway', 'Denmark', 'Finland', 'Belgium', 'Switzerland',
            'Austria', 'Ireland', 'Italy', 'Spain', 'Portugal', 'Poland', 'Czech Republic',
            'Slovakia', 'Hungary', 'Romania', 'Bulgaria', 'Greece', 'Turkey', 'Russia',
            'Ukraine', 'India', 'China', 'Japan', 'South Korea', 'Thailand', 'Vietnam',
            'Indonesia', 'Malaysia', 'Philippines', 'Singapore', 'New Zealand', 'South Africa',
            'Nigeria', 'Kenya', 'Egypt', 'Morocco', 'Brazil', 'Argentina', 'Chile', 'Colombia',
            'Peru', 'Venezuela', 'Mexico', 'Costa Rica', 'Panama', 'Guatemala', 'Honduras',
            'El Salvador', 'Nicaragua', 'Jamaica', 'Cuba', 'Dominican Republic', 'Haiti',
            'Trinidad and Tobago', 'Barbados', 'Bahamas', 'Puerto Rico'
        ];
        
        // Exact match
        if (in_array($text, $realCountries)) {
            return $text;
        }
        
        // Common variations
        $variations = [
            'USA' => 'United States',
            'US' => 'United States',
            'America' => 'United States',
            'UK' => 'United Kingdom',
            'Britain' => 'United Kingdom',
            'England' => 'United Kingdom',
            'Scotland' => 'United Kingdom',
            'Wales' => 'United Kingdom',
            'Deutschland' => 'Germany',
            'Nederland' => 'Netherlands',
            'Holland' => 'Netherlands',
            'España' => 'Spain',
            'Italia' => 'Italy',
            'Brasil' => 'Brazil',
            'Österreich' => 'Austria',
            'Schweiz' => 'Switzerland',
            'Sverige' => 'Sweden',
            'Norge' => 'Norway',
            'Danmark' => 'Denmark',
            'Suomi' => 'Finland'
        ];
        
        if (isset($variations[$text])) {
            return $variations[$text];
        }
        
        return 'Unknown';
    }
    
    /**
     * Update review country in database
     */
    private function updateReviewCountry($appName, $review) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE reviews 
                SET country_name = ? 
                WHERE app_name = ? AND store_name = ?
            ");
            
            return $stmt->execute([
                $review['country_name'],
                $appName,
                $review['store_name']
            ]);
        } catch (Exception $e) {
            echo "❌ Error updating {$review['store_name']}: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Fetch page with proper headers
     */
    private function fetchPage($url) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Accept-Encoding: gzip, deflate, br',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1',
                'Sec-Fetch-Dest: document',
                'Sec-Fetch-Mode: navigate',
                'Sec-Fetch-Site: none'
            ]
        ]);
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            echo "❌ cURL Error: $error\n";
            return false;
        }
        
        if ($httpCode !== 200) {
            echo "❌ HTTP Error: $httpCode\n";
            return false;
        }
        
        return $html;
    }
}

// CLI usage
if (php_sapi_name() === 'cli') {
    $extractor = new RealCountryExtractor();
    
    if (isset($argv[1]) && isset($argv[2])) {
        $appName = $argv[1];
        $appSlug = $argv[2];
        $extractor->extractRealCountries($appName, $appSlug);
    } else {
        echo "Usage: php RealCountryExtractor.php <AppName> <app-slug>\n";
        echo "Example: php RealCountryExtractor.php StoreSEO storeseo\n";
    }
}
?>
