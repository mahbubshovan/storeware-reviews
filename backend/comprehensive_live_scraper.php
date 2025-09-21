<?php
require_once __DIR__ . '/config/database.php';

/**
 * Comprehensive Live Scraper for ALL Shopify Apps
 * Scrapes real star ratings from actual Shopify review pages
 */
class ComprehensiveLiveScraper {
    private $dbManager;
    
    // All apps with their Shopify slugs
    private $apps = [
        'StoreSEO' => 'storeseo',
        'StoreFAQ' => 'storefaq',
        'BetterDocs FAQ' => 'betterdocs-knowledgebase',
        'EasyFlow' => 'product-options-4',
        'OnlineFilter' => 'online-store-2-0-filter-search',
        'Searchanise' => 'smart-search-filter'
    ];
    
    public function __construct() {
        $this->dbManager = new Database();
    }
    
    /**
     * Scrape all apps and update ratings
     */
    public function scrapeAllApps() {
        echo "🔴 COMPREHENSIVE LIVE SCRAPER - REAL SHOPIFY DATA ONLY\n";
        echo "=====================================================\n";
        
        foreach ($this->apps as $appName => $appSlug) {
            echo "\n🎯 SCRAPING: $appName ($appSlug)\n";
            echo str_repeat("=", 50) . "\n";
            
            $this->scrapeAppReviews($appName, $appSlug);
            
            // Small delay between apps
            sleep(2);
        }
        
        echo "\n🎉 ALL APPS SCRAPED SUCCESSFULLY!\n";
    }
    
    /**
     * Scrape reviews for a specific app
     */
    private function scrapeAppReviews($appName, $appSlug) {
        $url = "https://apps.shopify.com/$appSlug/reviews?sort_by=newest";
        echo "🌐 Fetching: $url\n";
        
        $html = $this->fetchPage($url);
        if (!$html) {
            echo "❌ Failed to fetch page for $appName\n";
            return;
        }
        
        // Save HTML for debugging
        file_put_contents("debug_{$appSlug}.html", $html);
        echo "📄 Saved HTML to debug_{$appSlug}.html\n";
        
        $reviews = $this->parseReviewsFromHTML($html);
        echo "📝 Found " . count($reviews) . " reviews\n";
        
        if (empty($reviews)) {
            echo "⚠️ No reviews found for $appName\n";
            return;
        }
        
        // Update database with correct ratings
        $this->updateDatabaseRatings($appName, $reviews);
    }
    
    /**
     * Parse reviews from HTML
     */
    private function parseReviewsFromHTML($html) {
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        
        // Find review content nodes
        $reviewNodes = $xpath->query('//p[@class="tw-break-words"]');
        echo "📝 Found " . $reviewNodes->length . " review content nodes\n";
        
        $reviews = [];
        foreach ($reviewNodes as $contentNode) {
            $content = trim($contentNode->textContent);
            if (empty($content) || strlen($content) < 10) continue;
            
            // Find the parent container that holds the entire review
            $reviewContainer = $this->findReviewContainer($contentNode, $xpath);
            if (!$reviewContainer) continue;
            
            // Extract rating by counting filled stars
            $rating = $this->extractRating($reviewContainer, $xpath);
            
            // Extract store name
            $storeName = $this->extractStoreName($reviewContainer, $xpath);
            
            // Extract date
            $reviewDate = $this->extractDate($reviewContainer, $xpath);
            
            if ($rating > 0 && !empty($storeName)) {
                $reviews[] = [
                    'store_name' => $storeName,
                    'rating' => $rating,
                    'content' => $content,
                    'date' => $reviewDate ?: date('Y-m-d')
                ];
                
                echo "⭐ {$rating}★ - {$storeName}: " . substr($content, 0, 50) . "...\n";
            }
        }
        
        return $reviews;
    }
    
    /**
     * Find the review container that holds all review data
     */
    private function findReviewContainer($contentNode, $xpath) {
        $reviewContainer = $contentNode;
        for ($i = 0; $i < 10; $i++) {
            $reviewContainer = $reviewContainer->parentNode;
            if (!$reviewContainer) break;
            
            // Look for store name in this container
            $storeNameNodes = $xpath->query('.//div[@class="tw-text-heading-xs tw-text-fg-primary tw-overflow-hidden tw-text-ellipsis tw-whitespace-nowrap"]', $reviewContainer);
            if ($storeNameNodes->length > 0) {
                return $reviewContainer; // Found the right container
            }
        }
        return null;
    }
    
    /**
     * Extract rating by analyzing star SVG elements
     */
    private function extractRating($reviewContainer, $xpath) {
        // Method 1: Count filled stars with specific class (most reliable)
        $filledStars = $xpath->query('.//svg[@class="tw-fill-fg-primary tw-w-md tw-h-md"]', $reviewContainer);
        if ($filledStars->length > 0) {
            return min($filledStars->length, 5);
        }

        // Method 2: Look for aria-label with rating
        $ratingNodes = $xpath->query('.//*[contains(@aria-label, "out of") and contains(@aria-label, "stars")]', $reviewContainer);
        foreach ($ratingNodes as $node) {
            $ariaLabel = $node->getAttribute('aria-label');
            if (preg_match('/(\d+)\s*out\s*of\s*\d+\s*stars/', $ariaLabel, $matches)) {
                return intval($matches[1]);
            }
        }

        // Method 3: Look for different filled star class variations
        $starVariations = [
            './/svg[contains(@class, "tw-fill-fg-primary")]',
            './/svg[contains(@class, "filled")]',
            './/svg[contains(@class, "star-filled")]'
        ];

        foreach ($starVariations as $selector) {
            $stars = $xpath->query($selector, $reviewContainer);
            if ($stars->length > 0 && $stars->length <= 5) {
                return $stars->length;
            }
        }

        // Method 4: Look for rating in text content
        $textNodes = $xpath->query('.//text()', $reviewContainer);
        foreach ($textNodes as $textNode) {
            $text = trim($textNode->textContent);
            if (preg_match('/(\d+)\s*(?:star|★)/', $text, $matches)) {
                $rating = intval($matches[1]);
                if ($rating >= 1 && $rating <= 5) {
                    return $rating;
                }
            }
        }

        // Return 0 if no rating found (don't assume 5 stars)
        return 0;
    }
    
    /**
     * Extract store name
     */
    private function extractStoreName($reviewContainer, $xpath) {
        $storeNameNodes = $xpath->query('.//div[@class="tw-text-heading-xs tw-text-fg-primary tw-overflow-hidden tw-text-ellipsis tw-whitespace-nowrap"]', $reviewContainer);
        return $storeNameNodes->length > 0 ? trim($storeNameNodes->item(0)->textContent) : 'Unknown Store';
    }
    
    /**
     * Extract review date
     */
    private function extractDate($reviewContainer, $xpath) {
        $dateNodes = $xpath->query('.//div[contains(@class, "tw-text-body-xs") and contains(@class, "tw-text-fg-tertiary")]', $reviewContainer);
        foreach ($dateNodes as $dateNode) {
            $dateText = trim($dateNode->textContent);
            if (preg_match('/\w+ \d{1,2}, \d{4}/', $dateText)) {
                return date('Y-m-d', strtotime($dateText));
            }
        }
        return '';
    }
    
    /**
     * Update database with correct ratings
     */
    private function updateDatabaseRatings($appName, $reviews) {
        $conn = $this->dbManager->getConnection();
        $updated = 0;

        foreach ($reviews as $review) {
            // Update reviews table
            $stmt = $conn->prepare("UPDATE reviews SET rating = ? WHERE store_name = ? AND app_name = ?");
            $stmt->execute([$review['rating'], $review['store_name'], $appName]);

            if ($stmt->rowCount() > 0) {
                $updated++;
                echo "✅ Updated {$review['store_name']} to {$review['rating']}★\n";
            }
        }

        // Update access_reviews table
        $stmt = $conn->prepare("
            UPDATE access_reviews ar
            JOIN reviews r ON ar.original_review_id = r.id
            SET ar.rating = r.rating
            WHERE r.app_name = ?
        ");
        $stmt->execute([$appName]);

        echo "✅ Updated $updated ratings for $appName\n";
        echo "✅ Updated access_reviews table for $appName\n";
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
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200 ? $html : false;
    }
}

// Run the scraper
if (php_sapi_name() === 'cli') {
    $scraper = new ComprehensiveLiveScraper();
    $scraper->scrapeAllApps();
}
?>
