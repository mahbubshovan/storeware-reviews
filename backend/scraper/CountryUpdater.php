<?php
/**
 * Country Updater - Updates existing reviews with better country information
 * Uses live scraping to get accurate country data for "Unknown" entries
 */

require_once __DIR__ . '/../utils/DatabaseManager.php';

class CountryUpdater {
    private $conn;
    
    public function __construct() {
        $dbManager = new DatabaseManager();
        $this->conn = $dbManager->getConnection();
    }
    
    /**
     * Update all unknown countries by re-scraping live data
     */
    public function updateAllUnknownCountries() {
        echo "🌍 UPDATING UNKNOWN COUNTRIES WITH LIVE DATA\n";
        echo "==========================================\n\n";
        
        // Get all reviews with Unknown country
        $stmt = $this->conn->prepare("
            SELECT DISTINCT app_name 
            FROM reviews 
            WHERE country_name = 'Unknown' OR country_name IS NULL
        ");
        $stmt->execute();
        $apps = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "📊 Found " . count($apps) . " apps with unknown countries\n\n";
        
        $appSlugs = [
            'StoreSEO' => 'storeseo',
            'StoreFAQ' => 'storefaq',
            'EasyFlow' => 'easyflow',
            'BetterDocs FAQ Knowledge Base' => 'betterdocs-faq-knowledge-base',
            'Smart SEO Schema Rich Snippets' => 'smart-seo-schema-rich-snippets',
            'SEO King' => 'seo-king'
        ];
        
        foreach ($apps as $appName) {
            if (isset($appSlugs[$appName])) {
                echo "🎯 Processing $appName...\n";
                $this->updateAppCountries($appName, $appSlugs[$appName]);
                echo "\n" . str_repeat("-", 50) . "\n\n";
            }
        }
        
        echo "✅ All unknown countries updated!\n";
    }
    
    /**
     * Update countries for a specific app
     */
    public function updateAppCountries($appName, $appSlug) {
        // Get reviews with unknown countries for this app
        $stmt = $this->conn->prepare("
            SELECT id, store_name, review_content, review_date
            FROM reviews 
            WHERE app_name = ? AND (country_name = 'Unknown' OR country_name IS NULL)
            ORDER BY review_date DESC
            LIMIT 100
        ");
        $stmt->execute([$appName]);
        $unknownReviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($unknownReviews)) {
            echo "✅ No unknown countries found for $appName\n";
            return;
        }
        
        echo "📊 Found " . count($unknownReviews) . " reviews with unknown countries\n";
        
        // Scrape recent reviews from Shopify to get country information
        $liveReviews = $this->scrapeLiveReviews($appSlug);
        
        if (empty($liveReviews)) {
            echo "❌ Could not scrape live reviews for $appName\n";
            return;
        }
        
        echo "📄 Scraped " . count($liveReviews) . " live reviews\n";
        
        // Match and update
        $updated = 0;
        foreach ($unknownReviews as $unknownReview) {
            $matchedCountry = $this->findMatchingCountry($unknownReview, $liveReviews);
            
            if ($matchedCountry && $matchedCountry !== 'Unknown') {
                $updateStmt = $this->conn->prepare("UPDATE reviews SET country_name = ? WHERE id = ?");
                if ($updateStmt->execute([$matchedCountry, $unknownReview['id']])) {
                    $updated++;
                    echo "✅ {$unknownReview['store_name']} -> $matchedCountry\n";
                }
            }
        }
        
        echo "🎯 Updated $updated reviews for $appName\n";
    }
    
    /**
     * Scrape live reviews from Shopify
     */
    private function scrapeLiveReviews($appSlug) {
        $reviews = [];
        $baseUrl = "https://apps.shopify.com/$appSlug/reviews";
        
        // Scrape first few pages to get recent reviews
        for ($page = 1; $page <= 5; $page++) {
            $url = $baseUrl . "?sort_by=newest&page=$page";
            echo "📄 Scraping page $page...\n";
            
            $html = $this->fetchPage($url);
            if (!$html) {
                break;
            }
            
            $pageReviews = $this->parseReviewsFromHTML($html);
            if (empty($pageReviews)) {
                break;
            }
            
            $reviews = array_merge($reviews, $pageReviews);
            
            // Add delay to avoid rate limiting
            sleep(1);
        }
        
        return $reviews;
    }
    
    /**
     * Parse reviews from HTML with enhanced country extraction
     */
    private function parseReviewsFromHTML($html) {
        $reviews = [];
        
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        
        // Look for review containers - try multiple selectors
        $reviewSelectors = [
            '//div[contains(@class, "review-listing")]',
            '//div[contains(@class, "tw-border-b") and contains(@class, "tw-py")]',
            '//article[contains(@class, "review")]',
            '//li[contains(@class, "review")]'
        ];
        
        foreach ($reviewSelectors as $selector) {
            $reviewNodes = $xpath->query($selector);
            if ($reviewNodes->length > 0) {
                foreach ($reviewNodes as $node) {
                    $review = $this->extractReviewData($xpath, $node);
                    if ($review) {
                        $reviews[] = $review;
                    }
                }
                break; // Use first working selector
            }
        }
        
        return $reviews;
    }
    
    /**
     * Extract review data from HTML node
     */
    private function extractReviewData($xpath, $node) {
        try {
            // Extract store name
            $storeSelectors = [
                './/div[contains(@class, "tw-text-heading-xs")]',
                './/h3', './/h4', './/strong'
            ];
            
            $storeName = '';
            foreach ($storeSelectors as $selector) {
                $storeNodes = $xpath->query($selector, $node);
                if ($storeNodes->length > 0) {
                    $storeName = trim($storeNodes->item(0)->textContent);
                    if (!empty($storeName)) break;
                }
            }
            
            if (empty($storeName)) return null;
            
            // Extract country using multiple strategies
            $country = $this->extractCountryFromNode($xpath, $node, $storeName);
            
            // Extract review content for matching
            $contentSelectors = [
                './/p[contains(@class, "tw-break-words")]',
                './/div[contains(@class, "content")]',
                './/p'
            ];
            
            $reviewContent = '';
            foreach ($contentSelectors as $selector) {
                $contentNodes = $xpath->query($selector, $node);
                if ($contentNodes->length > 0) {
                    $reviewContent = trim($contentNodes->item(0)->textContent);
                    if (!empty($reviewContent)) break;
                }
            }
            
            return [
                'store_name' => $storeName,
                'country_name' => $country,
                'review_content' => $reviewContent
            ];
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Extract country from review node using multiple strategies
     */
    private function extractCountryFromNode($xpath, $node, $storeName) {
        // Strategy 1: Look for country in various text elements
        $countrySelectors = [
            './/div[contains(@class, "tw-text-body-xs")]',
            './/span[contains(@class, "tw-text-body-xs")]',
            './/div[contains(@class, "tw-text-fg-tertiary")]',
            './/span[contains(@class, "tw-text-fg-tertiary")]',
            './/div[contains(@class, "merchant-info")]',
            './/div[contains(@class, "location")]'
        ];
        
        foreach ($countrySelectors as $selector) {
            $nodes = $xpath->query($selector, $node);
            foreach ($nodes as $countryNode) {
                $text = trim($countryNode->textContent);
                $country = $this->validateCountryText($text);
                if ($country !== 'Unknown') {
                    return $country;
                }
            }
        }
        
        // Strategy 2: Look in full node text
        $fullText = $node->textContent;
        $country = $this->extractCountryFromText($fullText);
        if ($country !== 'Unknown') {
            return $country;
        }
        
        // Strategy 3: Infer from store name
        $country = $this->inferCountryFromStoreName($storeName);
        if ($country !== 'Unknown') {
            return $country;
        }
        
        return 'Unknown';
    }
    
    /**
     * Validate country text
     */
    private function validateCountryText($text) {
        if (empty($text) || strlen($text) < 2 || strlen($text) > 50) {
            return 'Unknown';
        }
        
        // Skip non-country text
        if (preg_match('/\d{4}|using|replied|helpful|ago|since/', $text)) {
            return 'Unknown';
        }
        
        // Known countries
        $knownCountries = [
            'United States', 'United Kingdom', 'Canada', 'Australia', 'Germany', 'France',
            'Netherlands', 'Sweden', 'Norway', 'Denmark', 'Finland', 'Belgium', 'Switzerland',
            'Austria', 'Ireland', 'Italy', 'Spain', 'Portugal', 'Poland', 'India', 'Japan',
            'Brazil', 'Mexico', 'South Africa', 'New Zealand', 'Singapore'
        ];
        
        if (in_array($text, $knownCountries)) {
            return $text;
        }
        
        // Common variations
        $variations = [
            'USA' => 'United States', 'US' => 'United States',
            'UK' => 'United Kingdom', 'Britain' => 'United Kingdom'
        ];
        
        if (isset($variations[$text])) {
            return $variations[$text];
        }
        
        return 'Unknown';
    }
    
    /**
     * Extract country from text patterns
     */
    private function extractCountryFromText($text) {
        $patterns = [
            '/\b(United States|USA|US)\b/i' => 'United States',
            '/\b(United Kingdom|UK|Britain)\b/i' => 'United Kingdom',
            '/\b(Canada|Canadian)\b/i' => 'Canada',
            '/\b(Australia|Australian)\b/i' => 'Australia',
            '/\b(Germany|German)\b/i' => 'Germany',
            '/\b(France|French)\b/i' => 'France'
        ];
        
        foreach ($patterns as $pattern => $country) {
            if (preg_match($pattern, $text)) {
                return $country;
            }
        }
        
        return 'Unknown';
    }
    
    /**
     * Infer country from store name
     */
    private function inferCountryFromStoreName($storeName) {
        $patterns = [
            '/\b(ltd|limited)\b/i' => 'United Kingdom',
            '/\b(llc|inc|corp)\b/i' => 'United States',
            '/\b(pty)\b/i' => 'Australia',
            '/\b(gmbh)\b/i' => 'Germany',
            '/\b(uk|britain)\b/i' => 'United Kingdom',
            '/\b(usa|america)\b/i' => 'United States'
        ];
        
        foreach ($patterns as $pattern => $country) {
            if (preg_match($pattern, strtolower($storeName))) {
                return $country;
            }
        }
        
        return 'Unknown';
    }
    
    /**
     * Find matching country for a review
     */
    private function findMatchingCountry($unknownReview, $liveReviews) {
        // Try exact store name match first
        foreach ($liveReviews as $liveReview) {
            if ($liveReview['store_name'] === $unknownReview['store_name']) {
                return $liveReview['country_name'];
            }
        }
        
        // Try fuzzy store name match
        foreach ($liveReviews as $liveReview) {
            if (similar_text($liveReview['store_name'], $unknownReview['store_name']) > 0.8) {
                return $liveReview['country_name'];
            }
        }
        
        // Try content similarity match
        foreach ($liveReviews as $liveReview) {
            if (!empty($unknownReview['review_content']) && !empty($liveReview['review_content'])) {
                $similarity = similar_text($liveReview['review_content'], $unknownReview['review_content']);
                if ($similarity > 0.7) {
                    return $liveReview['country_name'];
                }
            }
        }
        
        return 'Unknown';
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
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'
        ]);
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ($httpCode === 200) ? $html : false;
    }
}

// CLI usage
if (php_sapi_name() === 'cli') {
    $updater = new CountryUpdater();
    
    if (isset($argv[1]) && isset($argv[2])) {
        $updater->updateAppCountries($argv[1], $argv[2]);
    } else {
        $updater->updateAllUnknownCountries();
    }
}
?>
