<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Pure Live StoreSEO Scraper - NO MOCK DATA
 * Only scrapes real-time data from live Shopify pages
 */
class PureLiveStoreSEOScraper {
    private $baseUrl = 'https://apps.shopify.com/storeseo/reviews';
    private $dbManager;
    
    public function __construct() {
        $this->dbManager = new Database();
    }
    
    /**
     * Scrape ONLY live reviews - no fallbacks, no mock data
     */
    public function scrapeLiveReviews() {
        echo "🔴 PURE LIVE SCRAPER - NO MOCK DATA ALLOWED\n";
        echo "🌐 Scraping ONLY from live Shopify pages...\n";
        
        // Clear existing data
        $this->clearStoreSEOData();
        
        $allReviews = [];
        $thirtyDaysAgo = strtotime('-30 days');
        
        // Scrape pages until we get all recent reviews
        for ($page = 1; $page <= 10; $page++) {
            $url = $this->baseUrl . "?sort_by=newest&page=$page";
            echo "📄 Fetching page $page: $url\n";
            
            $html = $this->fetchPage($url);
            if (!$html) {
                echo "❌ Failed to fetch page $page - STOPPING (no fallback allowed)\n";
                break;
            }
            
            $pageReviews = $this->parseReviewsFromHTML($html);
            if (empty($pageReviews)) {
                echo "⚠️ No reviews found on page $page - STOPPING\n";
                break;
            }
            
            $addedFromPage = 0;
            $oldestOnPage = null;
            
            foreach ($pageReviews as $review) {
                $reviewTime = strtotime($review['review_date']);
                
                if (!$oldestOnPage || $reviewTime < $oldestOnPage) {
                    $oldestOnPage = $reviewTime;
                }
                
                // Only collect reviews from last 30 days
                if ($reviewTime >= $thirtyDaysAgo) {
                    $allReviews[] = $review;
                    $addedFromPage++;
                    echo "✅ Live review: {$review['review_date']} - {$review['rating']}★ - {$review['store_name']}\n";
                }
            }
            
            echo "📊 Page $page: Found " . count($pageReviews) . " reviews, added $addedFromPage recent ones\n";
            
            // Stop if we've gone beyond 30 days
            if ($oldestOnPage && $oldestOnPage < $thirtyDaysAgo) {
                echo "📅 Reached reviews older than 30 days, stopping\n";
                break;
            }
        }
        
        if (empty($allReviews)) {
            echo "❌ CRITICAL: No live reviews found - system will show empty data\n";
            echo "❌ This means either scraping failed or there are no recent reviews\n";
            return false;
        }
        
        // Save all live reviews
        $saved = 0;
        foreach ($allReviews as $review) {
            if ($this->saveReview($review)) {
                $saved++;
            }
        }
        
        echo "🎯 LIVE SCRAPING COMPLETE: $saved reviews saved from " . count($allReviews) . " found\n";
        return true;
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
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1',
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            echo "❌ cURL Error: " . curl_error($ch) . "\n";
            curl_close($ch);
            return false;
        }
        
        curl_close($ch);
        
        if ($httpCode !== 200) {
            echo "❌ HTTP Error: $httpCode\n";
            return false;
        }
        
        return $html;
    }
    
    /**
     * Parse reviews from HTML - ONLY real data
     */
    private function parseReviewsFromHTML($html) {
        $reviews = [];
        
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        
        // Find review containers
        $reviewNodes = $xpath->query('//div[@data-review-content-id]');
        
        foreach ($reviewNodes as $node) {
            $review = $this->extractReviewData($xpath, $node);
            if ($review) {
                $reviews[] = $review;
            }
        }
        
        return $reviews;
    }
    
    /**
     * Extract review data from DOM node
     */
    private function extractReviewData($xpath, $node) {
        try {
            // Extract rating (count filled stars)
            $starNodes = $xpath->query('.//svg[contains(@class, "tw-fill-fg-primary")]', $node);
            $rating = $starNodes->length;
            
            // Extract date
            $dateNode = $xpath->query('.//div[contains(@class, "tw-text-body-xs") and contains(@class, "tw-text-fg-tertiary") and contains(text(), "2025")]', $node);
            $reviewDate = '';
            if ($dateNode->length > 0) {
                $dateText = trim($dateNode->item(0)->textContent);
                $reviewDate = date('Y-m-d', strtotime($dateText));
            }
            
            // Extract store name
            $storeNode = $xpath->query('.//div[contains(@class, "tw-text-heading-xs") and contains(@class, "tw-text-fg-primary")]', $node);
            $storeName = '';
            if ($storeNode->length > 0) {
                $storeName = trim($storeNode->item(0)->textContent);
            }
            
            // Extract country
            $countryNodes = $xpath->query('.//div[contains(@class, "tw-text-fg-tertiary") and contains(@class, "tw-text-body-xs")]', $node);
            $country = 'Unknown';
            foreach ($countryNodes as $cNode) {
                $text = trim($cNode->textContent);
                if (!empty($text) && !preg_match('/\d{4}/', $text) && $text !== 'Storeware replied' && strlen($text) < 50) {
                    $country = $text;
                    break;
                }
            }
            
            // Extract review content
            $contentNode = $xpath->query('.//p[contains(@class, "tw-break-words")]', $node);
            $reviewContent = '';
            if ($contentNode->length > 0) {
                $reviewContent = trim($contentNode->item(0)->textContent);
            }
            
            // Validate required fields
            if (empty($storeName) || empty($reviewDate) || $rating === 0) {
                return null;
            }
            
            return [
                'store_name' => $storeName,
                'country_name' => substr($country, 0, 50), // Truncate to fit DB
                'rating' => $rating,
                'review_content' => $reviewContent,
                'review_date' => $reviewDate
            ];
            
        } catch (Exception $e) {
            echo "❌ Error extracting review: " . $e->getMessage() . "\n";
            return null;
        }
    }
    
    /**
     * Clear existing StoreSEO data
     */
    private function clearStoreSEOData() {
        try {
            $conn = $this->dbManager->getConnection();
            $stmt = $conn->prepare("DELETE FROM reviews WHERE app_name = 'StoreSEO'");
            $stmt->execute();
            echo "✅ Cleared existing StoreSEO data\n";
        } catch (Exception $e) {
            echo "❌ Error clearing data: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Save review to database
     */
    private function saveReview($review) {
        try {
            $conn = $this->dbManager->getConnection();
            $stmt = $conn->prepare("
                INSERT INTO reviews (app_name, store_name, country_name, rating, review_content, review_date)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            return $stmt->execute([
                'StoreSEO',
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

// Run if called directly
if (basename(__FILE__) == basename($_SERVER["SCRIPT_NAME"])) {
    $scraper = new PureLiveStoreSEOScraper();
    $scraper->scrapeLiveReviews();
}
?>
