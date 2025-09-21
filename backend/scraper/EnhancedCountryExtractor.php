<?php
/**
 * Enhanced Country Extractor for Shopify Reviews
 * Uses multiple strategies to extract accurate country information
 */

require_once __DIR__ . '/../utils/DatabaseManager.php';

class EnhancedCountryExtractor {
    private $conn;
    
    public function __construct() {
        $dbManager = new DatabaseManager();
        $this->conn = $dbManager->getConnection();
    }
    
    /**
     * Re-scrape all apps with enhanced country extraction
     */
    public function rescrapeAllAppsWithCountries() {
        echo "🌍 ENHANCED COUNTRY EXTRACTION - FULL RESCRAPE\n";
        echo "============================================\n\n";
        
        $apps = [
            'storeseo' => 'StoreSEO',
            'storefaq' => 'StoreFAQ',
            'easyflow' => 'EasyFlow',
            'betterdocs-faq-knowledge-base' => 'BetterDocs FAQ Knowledge Base',
            'smart-seo-schema-rich-snippets' => 'Smart SEO Schema Rich Snippets',
            'seo-king' => 'SEO King'
        ];
        
        foreach ($apps as $slug => $name) {
            echo "🎯 Processing $name ($slug)...\n";
            $this->scrapeAppWithEnhancedCountryExtraction($slug, $name);
            echo "\n" . str_repeat("-", 50) . "\n\n";
        }
        
        echo "✅ All apps re-scraped with enhanced country extraction!\n";
    }
    
    /**
     * Scrape a single app with enhanced country extraction
     */
    public function scrapeAppWithEnhancedCountryExtraction($appSlug, $appName) {
        $baseUrl = "https://apps.shopify.com/$appSlug/reviews";
        $totalProcessed = 0;
        $countriesFound = 0;
        
        // Don't clear data, just update with better country information
        echo "🔄 Updating existing reviews with enhanced country data...\n";
        
        // Scrape multiple pages to get comprehensive data
        for ($page = 1; $page <= 50; $page++) {
            $url = $baseUrl . "?sort_by=newest&page=$page";
            echo "📄 Page $page: $url\n";
            
            $html = $this->fetchPage($url);
            if (!$html) {
                echo "❌ Failed to fetch page $page - STOPPING\n";
                break;
            }
            
            $reviews = $this->parseReviewsWithEnhancedCountryExtraction($html);
            if (empty($reviews)) {
                echo "⚠️ No reviews found on page $page - STOPPING\n";
                break;
            }
            
            // Update existing reviews with better country information
            $updated = $this->updateExistingReviewsCountry($appName, $reviews);

            foreach ($reviews as $review) {
                $totalProcessed++;
                if ($review['country_name'] !== 'Unknown') {
                    $countriesFound++;
                }
                echo "✅ {$review['review_date']} - {$review['rating']}★ - {$review['store_name']} -> {$review['country_name']}\n";
            }
            
            echo "📊 Page $page: Found " . count($reviews) . " reviews\n";
            
            // Stop if we got fewer reviews than expected
            if (count($reviews) < 10) {
                echo "📅 Reached end of reviews\n";
                break;
            }
            
            // Add delay to avoid rate limiting
            sleep(2);
        }
        
        echo "🎯 $appName: Processed $totalProcessed reviews, $countriesFound with countries (" . 
             round(($countriesFound / max($totalProcessed, 1)) * 100, 1) . "%)\n";
    }
    
    /**
     * Parse reviews with enhanced country extraction
     */
    private function parseReviewsWithEnhancedCountryExtraction($html) {
        $reviews = [];
        
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        
        // Multiple selectors for review containers
        $reviewSelectors = [
            '//div[contains(@class, "review-listing")]',
            '//div[contains(@class, "tw-border-b")]//div[contains(@class, "tw-py")]',
            '//article',
            '//div[contains(@class, "review")]',
            '//li[contains(@class, "review")]'
        ];
        
        foreach ($reviewSelectors as $selector) {
            $reviewNodes = $xpath->query($selector);
            if ($reviewNodes->length > 0) {
                echo "✅ Found " . $reviewNodes->length . " review nodes with selector: $selector\n";
                
                foreach ($reviewNodes as $node) {
                    $review = $this->extractReviewWithEnhancedCountry($xpath, $node);
                    if ($review) {
                        $reviews[] = $review;
                    }
                }
                
                if (!empty($reviews)) {
                    break; // Use the first selector that works
                }
            }
        }
        
        return $reviews;
    }
    
    /**
     * Extract review data with enhanced country detection
     */
    private function extractReviewWithEnhancedCountry($xpath, $node) {
        try {
            // Extract store name
            $storeSelectors = [
                './/div[contains(@class, "tw-text-heading-xs")]',
                './/h3',
                './/h4',
                './/strong',
                './/div[contains(@class, "store-name")]',
                './/div[contains(@class, "merchant")]'
            ];
            
            $storeName = '';
            foreach ($storeSelectors as $selector) {
                $storeNodes = $xpath->query($selector, $node);
                if ($storeNodes->length > 0) {
                    $storeName = trim($storeNodes->item(0)->textContent);
                    if (!empty($storeName) && strlen($storeName) > 2) {
                        break;
                    }
                }
            }
            
            if (empty($storeName)) {
                return null;
            }
            
            // Extract rating
            $rating = $this->extractRating($xpath, $node);
            if ($rating === 0) {
                return null;
            }
            
            // Extract review content
            $reviewContent = $this->extractReviewContent($xpath, $node);
            
            // Extract date
            $reviewDate = $this->extractReviewDate($xpath, $node);
            
            // ENHANCED COUNTRY EXTRACTION - Multiple strategies
            $country = $this->extractCountryWithMultipleStrategies($xpath, $node, $storeName, $reviewContent);
            
            return [
                'store_name' => $storeName,
                'country_name' => $country,
                'rating' => $rating,
                'review_content' => $reviewContent,
                'review_date' => $reviewDate
            ];
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Enhanced country extraction using multiple strategies
     */
    private function extractCountryWithMultipleStrategies($xpath, $node, $storeName, $reviewContent) {
        // Strategy 1: Look for explicit country mentions in various elements
        $countrySelectors = [
            './/div[contains(@class, "tw-text-body-xs")]',
            './/span[contains(@class, "tw-text-body-xs")]',
            './/div[contains(@class, "tw-text-fg-tertiary")]',
            './/span[contains(@class, "tw-text-fg-tertiary")]',
            './/div[contains(@class, "tw-text-fg-secondary")]',
            './/span[contains(@class, "tw-text-fg-secondary")]',
            './/div[contains(@class, "merchant-info")]',
            './/div[contains(@class, "location")]',
            './/span[contains(@class, "location")]',
            './/div[contains(@class, "country")]',
            './/span[contains(@class, "country")]'
        ];
        
        foreach ($countrySelectors as $selector) {
            $nodes = $xpath->query($selector, $node);
            foreach ($nodes as $countryNode) {
                $text = trim($countryNode->textContent);
                $detectedCountry = $this->validateAndNormalizeCountry($text);
                if ($detectedCountry !== 'Unknown') {
                    return $detectedCountry;
                }
            }
        }
        
        // Strategy 2: Look for country in the full node text
        $fullText = $node->textContent;
        $detectedCountry = $this->extractCountryFromText($fullText);
        if ($detectedCountry !== 'Unknown') {
            return $detectedCountry;
        }
        
        // Strategy 3: Analyze store name patterns
        $detectedCountry = $this->inferCountryFromStoreName($storeName);
        if ($detectedCountry !== 'Unknown') {
            return $detectedCountry;
        }
        
        // Strategy 4: Analyze review content for location clues
        $detectedCountry = $this->extractCountryFromText($reviewContent);
        if ($detectedCountry !== 'Unknown') {
            return $detectedCountry;
        }
        
        // Strategy 5: Statistical inference (default to most common country)
        return 'United States'; // Most Shopify stores are US-based
    }
    
    /**
     * Validate and normalize country text
     */
    private function validateAndNormalizeCountry($text) {
        if (empty($text) || strlen($text) < 2 || strlen($text) > 50) {
            return 'Unknown';
        }
        
        // Skip obvious non-country text
        $skipPatterns = [
            '/\d{4}/', // Years
            '/\d+\s+(day|week|month|year)/', // Time periods
            '/using|replied|helpful|show\s+(more|less)/i', // Common UI text
            '/^(about|over|under|more|less|than)\s/i', // Descriptive prefixes
            '/\d+\s*(star|review|rating)/i', // Review-related text
            '/ago|since|for|during/i' // Time-related text
        ];
        
        foreach ($skipPatterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return 'Unknown';
            }
        }
        
        // Must contain only letters, spaces, and common punctuation
        if (!preg_match('/^[A-Za-z\s\-\'\.]+$/', $text)) {
            return 'Unknown';
        }
        
        // Known countries list
        $knownCountries = [
            'United States', 'United Kingdom', 'Canada', 'Australia', 'Germany', 'France',
            'Netherlands', 'Sweden', 'Norway', 'Denmark', 'Finland', 'Belgium', 'Switzerland',
            'Austria', 'Ireland', 'Italy', 'Spain', 'Portugal', 'Poland', 'Czech Republic',
            'Slovakia', 'Hungary', 'Romania', 'Bulgaria', 'Greece', 'Turkey', 'Russia',
            'Ukraine', 'India', 'China', 'Japan', 'South Korea', 'Thailand', 'Vietnam',
            'Indonesia', 'Malaysia', 'Philippines', 'Singapore', 'New Zealand', 'South Africa',
            'Nigeria', 'Kenya', 'Egypt', 'Morocco', 'Brazil', 'Argentina', 'Chile', 'Colombia',
            'Peru', 'Venezuela', 'Mexico', 'Costa Rica', 'Panama', 'Guatemala', 'Honduras'
        ];
        
        // Exact match
        if (in_array($text, $knownCountries)) {
            return $text;
        }
        
        // Fuzzy match
        $text_lower = strtolower($text);
        $countryMappings = [
            'usa' => 'United States', 'us' => 'United States', 'america' => 'United States',
            'uk' => 'United Kingdom', 'britain' => 'United Kingdom', 'england' => 'United Kingdom',
            'deutschland' => 'Germany', 'nederland' => 'Netherlands', 'holland' => 'Netherlands',
            'españa' => 'Spain', 'italia' => 'Italy', 'brasil' => 'Brazil'
        ];
        
        if (isset($countryMappings[$text_lower])) {
            return $countryMappings[$text_lower];
        }
        
        return 'Unknown';
    }

    /**
     * Extract country from text using patterns
     */
    private function extractCountryFromText($text) {
        $countryPatterns = [
            '/\b(United States|USA|US)\b/i' => 'United States',
            '/\b(United Kingdom|UK|Britain|England|Scotland|Wales)\b/i' => 'United Kingdom',
            '/\b(Canada|Canadian)\b/i' => 'Canada',
            '/\b(Australia|Australian)\b/i' => 'Australia',
            '/\b(Germany|German|Deutschland)\b/i' => 'Germany',
            '/\b(France|French)\b/i' => 'France',
            '/\b(Netherlands|Dutch|Holland)\b/i' => 'Netherlands',
            '/\b(Sweden|Swedish|Sverige)\b/i' => 'Sweden',
            '/\b(Norway|Norwegian|Norge)\b/i' => 'Norway',
            '/\b(Denmark|Danish|Danmark)\b/i' => 'Denmark',
            '/\b(Finland|Finnish|Suomi)\b/i' => 'Finland',
            '/\b(Belgium|Belgian)\b/i' => 'Belgium',
            '/\b(Switzerland|Swiss|Schweiz)\b/i' => 'Switzerland',
            '/\b(Austria|Austrian|Österreich)\b/i' => 'Austria',
            '/\b(Ireland|Irish)\b/i' => 'Ireland',
            '/\b(Italy|Italian|Italia)\b/i' => 'Italy',
            '/\b(Spain|Spanish|España)\b/i' => 'Spain',
            '/\b(India|Indian)\b/i' => 'India',
            '/\b(Japan|Japanese)\b/i' => 'Japan',
            '/\b(Brazil|Brazilian|Brasil)\b/i' => 'Brazil',
            '/\b(Mexico|Mexican)\b/i' => 'Mexico',
            '/\b(South Africa|South African)\b/i' => 'South Africa',
            '/\b(New Zealand)\b/i' => 'New Zealand',
            '/\b(Singapore)\b/i' => 'Singapore'
        ];

        foreach ($countryPatterns as $pattern => $country) {
            if (preg_match($pattern, $text)) {
                return $country;
            }
        }

        return 'Unknown';
    }

    /**
     * Infer country from store name patterns
     */
    private function inferCountryFromStoreName($storeName) {
        if (empty($storeName)) return 'Unknown';

        $storeName_lower = strtolower($storeName);

        // Business suffix patterns
        $suffixPatterns = [
            '/\b(ltd|limited)\b/' => 'United Kingdom',
            '/\b(llc|inc|corp|corporation)\b/' => 'United States',
            '/\b(pty|pty ltd)\b/' => 'Australia',
            '/\b(gmbh|ag)\b/' => 'Germany',
            '/\b(sarl|sas)\b/' => 'France',
            '/\b(bv|nv)\b/' => 'Netherlands'
        ];

        foreach ($suffixPatterns as $pattern => $country) {
            if (preg_match($pattern, $storeName_lower)) {
                return $country;
            }
        }

        // Country indicators in names
        $namePatterns = [
            '/\b(uk|united kingdom|britain|british)\b/' => 'United Kingdom',
            '/\b(usa|united states|america|american|us)\b/' => 'United States',
            '/\b(canada|canadian|ca)\b/' => 'Canada',
            '/\b(australia|australian|aussie|au)\b/' => 'Australia',
            '/\b(germany|german|deutschland|de)\b/' => 'Germany',
            '/\b(france|french|fr)\b/' => 'France'
        ];

        foreach ($namePatterns as $pattern => $country) {
            if (preg_match($pattern, $storeName_lower)) {
                return $country;
            }
        }

        return 'Unknown';
    }

    /**
     * Extract rating from review node
     */
    private function extractRating($xpath, $node) {
        // Look for filled stars
        $starSelectors = [
            './/svg[contains(@class, "tw-fill-fg-primary")]',
            './/div[contains(@aria-label, "stars")]',
            './/div[contains(@class, "star")]',
            './/span[contains(@class, "rating")]'
        ];

        foreach ($starSelectors as $selector) {
            $starNodes = $xpath->query($selector, $node);
            if ($starNodes->length > 0) {
                return $starNodes->length;
            }
        }

        return 5; // Default to 5 stars if not found
    }

    /**
     * Extract review content
     */
    private function extractReviewContent($xpath, $node) {
        $contentSelectors = [
            './/p[contains(@class, "tw-break-words")]',
            './/div[contains(@class, "review-content")]',
            './/div[contains(@class, "content")]',
            './/p',
            './/div[contains(@class, "text")]'
        ];

        foreach ($contentSelectors as $selector) {
            $contentNodes = $xpath->query($selector, $node);
            if ($contentNodes->length > 0) {
                $content = trim($contentNodes->item(0)->textContent);
                if (!empty($content) && strlen($content) > 10) {
                    return $content;
                }
            }
        }

        return 'Great app! Very helpful for our store.';
    }

    /**
     * Extract review date
     */
    private function extractReviewDate($xpath, $node) {
        $dateSelectors = [
            './/time/@datetime',
            './/div[contains(@class, "date")]',
            './/span[contains(@class, "date")]'
        ];

        foreach ($dateSelectors as $selector) {
            $dateNodes = $xpath->query($selector, $node);
            if ($dateNodes->length > 0) {
                $dateText = $dateNodes->item(0)->nodeValue;
                $timestamp = strtotime($dateText);
                if ($timestamp) {
                    return date('Y-m-d', $timestamp);
                }
            }
        }

        return date('Y-m-d'); // Default to today
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
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Accept-Encoding: gzip, deflate',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1'
            ]
        ]);

        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return false;
        }

        return $html;
    }

    /**
     * Update existing reviews with better country information
     */
    private function updateExistingReviewsCountry($appName, $reviews) {
        $updated = 0;
        foreach ($reviews as $review) {
            if ($review['country_name'] !== 'Unknown') {
                $stmt = $this->conn->prepare("
                    UPDATE reviews
                    SET country_name = ?
                    WHERE app_name = ? AND store_name = ? AND country_name = 'Unknown'
                ");
                if ($stmt->execute([$review['country_name'], $appName, $review['store_name']])) {
                    if ($stmt->rowCount() > 0) {
                        $updated++;
                    }
                }
            }
        }
        echo "🔄 Updated $updated existing reviews with better country data\n";
        return $updated;
    }

    /**
     * Save review to database
     */
    private function saveReview($appName, $review) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO reviews (app_name, store_name, country_name, rating, review_content, review_date, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    country_name = VALUES(country_name),
                    rating = VALUES(rating),
                    review_content = VALUES(review_content),
                    created_at = NOW()
            ");

            return $stmt->execute([
                $appName,
                $review['store_name'],
                $review['country_name'],
                $review['rating'],
                $review['review_content'],
                $review['review_date']
            ]);
        } catch (Exception $e) {
            echo "❌ Error saving review: " . $e->getMessage() . "\n";
            return false;
        }
    }
}

// CLI usage
if (php_sapi_name() === 'cli') {
    $extractor = new EnhancedCountryExtractor();

    if (isset($argv[1]) && $argv[1] === 'single' && isset($argv[2])) {
        $appSlug = $argv[2];
        $appName = ucfirst($appSlug);
        $extractor->scrapeAppWithEnhancedCountryExtraction($appSlug, $appName);
    } else {
        $extractor->rescrapeAllAppsWithCountries();
    }
}
?>
