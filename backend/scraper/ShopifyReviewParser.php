<?php
/**
 * Shopify Review Parser - Extracts REAL country data from actual Shopify review pages
 * This will parse the actual HTML structure to get the real country information
 */

require_once __DIR__ . '/../utils/DatabaseManager.php';

class ShopifyReviewParser {
    private $conn;
    private $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
    
    public function __construct() {
        $dbManager = new DatabaseManager();
        $this->conn = $dbManager->getConnection();
    }
    
    /**
     * Parse all apps and extract real country data
     */
    public function parseAllApps() {
        echo "🌍 PARSING REAL COUNTRY DATA FROM SHOPIFY PAGES\n";
        echo "==============================================\n\n";
        
        $apps = [
            'StoreSEO' => 'storeseo',
            'StoreFAQ' => 'storefaq',
            'EasyFlow' => 'easyflow',
            'BetterDocs FAQ Knowledge Base' => 'betterdocs-faq-knowledge-base',
            'Smart SEO Schema Rich Snippets' => 'smart-seo-schema-rich-snippets',
            'SEO King' => 'seo-king'
        ];
        
        $totalUpdated = 0;
        
        foreach ($apps as $appName => $appSlug) {
            echo "📱 Processing: $appName\n";
            $updated = $this->parseAppReviews($appName, $appSlug);
            $totalUpdated += $updated;
            echo "✅ Updated $updated reviews for $appName\n\n";
            
            // Add delay between apps to avoid rate limiting
            sleep(3);
        }
        
        echo "🎯 TOTAL UPDATED: $totalUpdated reviews with real country data\n";
        return $totalUpdated;
    }
    
    /**
     * Parse reviews for a specific app
     */
    public function parseAppReviews($appName, $appSlug) {
        $baseUrl = "https://apps.shopify.com/$appSlug/reviews";
        $updated = 0;
        
        // Parse multiple pages to get comprehensive data
        for ($page = 1; $page <= 15; $page++) {
            $url = $baseUrl . "?sort_by=newest&page=$page";
            echo "📄 Parsing page $page...\n";
            
            $html = $this->fetchPageWithRetry($url);
            if (!$html) {
                echo "❌ Failed to fetch page $page\n";
                continue;
            }
            
            $reviews = $this->extractReviewsFromHTML($html);
            if (empty($reviews)) {
                echo "⚠️ No reviews found on page $page - might be end of pages\n";
                break;
            }
            
            echo "✅ Found " . count($reviews) . " reviews on page $page\n";
            
            // Update database with real country data
            foreach ($reviews as $review) {
                if ($this->updateReviewCountry($appName, $review)) {
                    $updated++;
                    echo "  ✅ {$review['store_name']} -> {$review['country_name']}\n";
                }
            }
            
            // Add delay between pages
            sleep(2);
        }
        
        return $updated;
    }
    
    /**
     * Extract reviews from Shopify HTML with multiple parsing strategies
     */
    private function extractReviewsFromHTML($html) {
        $reviews = [];
        
        // Try to find the JSON data embedded in the page first
        $jsonReviews = $this->extractFromJSON($html);
        if (!empty($jsonReviews)) {
            return $jsonReviews;
        }
        
        // Fallback to HTML parsing
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        
        // Multiple selectors to try for review containers
        $reviewSelectors = [
            '//div[contains(@class, "review-listing")]',
            '//div[contains(@class, "tw-border-b") and .//div[contains(@class, "tw-text-heading-xs")]]',
            '//article[contains(@class, "review")]',
            '//li[contains(@class, "review")]',
            '//div[contains(@data-testid, "review")]',
            '//div[contains(@class, "review")]'
        ];
        
        foreach ($reviewSelectors as $selector) {
            $reviewNodes = $xpath->query($selector);
            if ($reviewNodes->length > 0) {
                echo "🔍 Using HTML selector: $selector (found " . $reviewNodes->length . " nodes)\n";
                
                foreach ($reviewNodes as $node) {
                    $review = $this->parseReviewNode($xpath, $node);
                    if ($review && $review['country_name'] !== 'Unknown') {
                        $reviews[] = $review;
                    }
                }
                
                if (!empty($reviews)) {
                    break;
                }
            }
        }
        
        return $reviews;
    }
    
    /**
     * Try to extract review data from JSON embedded in the page
     */
    private function extractFromJSON($html) {
        $reviews = [];
        
        // Look for JSON data in script tags
        if (preg_match_all('/<script[^>]*>(.*?)<\/script>/s', $html, $matches)) {
            foreach ($matches[1] as $script) {
                // Look for review data patterns
                if (strpos($script, 'review') !== false && strpos($script, 'country') !== false) {
                    // Try to extract JSON
                    if (preg_match('/\{.*"reviews".*\}/s', $script, $jsonMatch)) {
                        $jsonData = json_decode($jsonMatch[0], true);
                        if ($jsonData && isset($jsonData['reviews'])) {
                            foreach ($jsonData['reviews'] as $reviewData) {
                                if (isset($reviewData['merchant']) && isset($reviewData['merchant']['country'])) {
                                    $reviews[] = [
                                        'store_name' => $reviewData['merchant']['name'] ?? 'Unknown',
                                        'country_name' => $this->normalizeCountry($reviewData['merchant']['country'])
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return $reviews;
    }
    
    /**
     * Parse individual review node from HTML
     */
    private function parseReviewNode($xpath, $node) {
        try {
            // Extract store name
            $storeName = $this->extractStoreName($xpath, $node);
            if (empty($storeName)) {
                return null;
            }
            
            // Extract country using multiple strategies
            $country = $this->extractCountryFromNode($xpath, $node);
            
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
            './/strong[contains(@class, "merchant-name")]',
            './/div[contains(@class, "merchant-name")]',
            './/span[contains(@class, "merchant-name")]'
        ];
        
        foreach ($storeSelectors as $selector) {
            $nodes = $xpath->query($selector, $node);
            if ($nodes->length > 0) {
                $name = trim($nodes->item(0)->textContent);
                if (!empty($name) && strlen($name) > 1 && strlen($name) < 100) {
                    return $name;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Extract country from review node using comprehensive strategies
     */
    private function extractCountryFromNode($xpath, $node) {
        // Strategy 1: Look for country in specific merchant info elements
        $countrySelectors = [
            './/div[contains(@class, "tw-text-body-xs") and contains(@class, "tw-text-fg-tertiary")]',
            './/span[contains(@class, "tw-text-body-xs") and contains(@class, "tw-text-fg-tertiary")]',
            './/div[contains(@class, "merchant-location")]',
            './/span[contains(@class, "merchant-location")]',
            './/div[contains(@class, "merchant-country")]',
            './/span[contains(@class, "merchant-country")]',
            './/div[contains(@class, "tw-text-fg-tertiary")]',
            './/span[contains(@class, "tw-text-fg-tertiary")]'
        ];
        
        foreach ($countrySelectors as $selector) {
            $nodes = $xpath->query($selector, $node);
            foreach ($nodes as $countryNode) {
                $text = trim($countryNode->textContent);
                $country = $this->validateAndNormalizeCountry($text);
                if ($country !== 'Unknown') {
                    return $country;
                }
            }
        }
        
        // Strategy 2: Look in all text nodes for country patterns
        $allText = $node->textContent;
        $lines = preg_split('/\n|\r\n?/', $allText);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line) && strlen($line) < 50) {
                $country = $this->validateAndNormalizeCountry($line);
                if ($country !== 'Unknown') {
                    return $country;
                }
            }
        }
        
        return 'Unknown';
    }
    
    /**
     * Validate and normalize country name
     */
    private function validateAndNormalizeCountry($text) {
        if (empty($text) || strlen($text) < 2 || strlen($text) > 50) {
            return 'Unknown';
        }
        
        // Skip obvious non-country text
        if (preg_match('/\d{4}|using|replied|helpful|ago|since|days?|weeks?|months?|years?|hours?/i', $text)) {
            return 'Unknown';
        }
        
        return $this->normalizeCountry($text);
    }
    
    /**
     * Normalize country name to standard format
     */
    private function normalizeCountry($country) {
        $country = trim($country);
        
        // Real countries that appear on Shopify
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
            'Trinidad and Tobago', 'Barbados', 'Bahamas', 'Puerto Rico', 'United Arab Emirates',
            'Saudi Arabia', 'Israel', 'Lebanon', 'Jordan', 'Kuwait', 'Qatar', 'Oman', 'Bahrain'
        ];
        
        // Exact match
        if (in_array($country, $realCountries)) {
            return $country;
        }
        
        // Common variations
        $variations = [
            'USA' => 'United States', 'US' => 'United States', 'America' => 'United States',
            'UK' => 'United Kingdom', 'Britain' => 'United Kingdom', 'England' => 'United Kingdom',
            'UAE' => 'United Arab Emirates', 'Emirates' => 'United Arab Emirates',
            'Deutschland' => 'Germany', 'Nederland' => 'Netherlands', 'Holland' => 'Netherlands',
            'España' => 'Spain', 'Italia' => 'Italy', 'Brasil' => 'Brazil'
        ];
        
        if (isset($variations[$country])) {
            return $variations[$country];
        }
        
        // Case-insensitive match
        foreach ($realCountries as $realCountry) {
            if (strcasecmp($country, $realCountry) === 0) {
                return $realCountry;
            }
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
                WHERE app_name = ? AND store_name = ? AND country_name != ?
            ");
            
            $result = $stmt->execute([
                $review['country_name'],
                $appName,
                $review['store_name'],
                $review['country_name']
            ]);
            
            return $result && $stmt->rowCount() > 0;
            
        } catch (Exception $e) {
            echo "❌ Error updating {$review['store_name']}: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Fetch page with retry logic
     */
    private function fetchPageWithRetry($url, $maxRetries = 3) {
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $html = $this->fetchPage($url);
            if ($html) {
                return $html;
            }
            
            if ($attempt < $maxRetries) {
                echo "⚠️ Attempt $attempt failed, retrying in " . ($attempt * 2) . " seconds...\n";
                sleep($attempt * 2);
            }
        }
        
        return false;
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
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Accept-Encoding: gzip, deflate, br',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1'
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
    $parser = new ShopifyReviewParser();
    
    if (isset($argv[1])) {
        switch ($argv[1]) {
            case 'all':
                $parser->parseAllApps();
                break;
                
            case 'app':
                if (isset($argv[2]) && isset($argv[3])) {
                    $parser->parseAppReviews($argv[2], $argv[3]);
                } else {
                    echo "Usage: php ShopifyReviewParser.php app <AppName> <app-slug>\n";
                }
                break;
                
            default:
                echo "Usage:\n";
                echo "  php ShopifyReviewParser.php all\n";
                echo "  php ShopifyReviewParser.php app <AppName> <app-slug>\n";
        }
    } else {
        $parser->parseAllApps();
    }
}
?>
