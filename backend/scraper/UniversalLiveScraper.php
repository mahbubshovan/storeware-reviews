<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Universal Live Scraper for ANY Shopify App
 * NO MOCK DATA - ONLY REAL-TIME SCRAPING
 */
class UniversalLiveScraper {
    private $dbManager;
    
    public function __construct() {
        $this->dbManager = new Database();
    }
    
    /**
     * Scrape any Shopify app reviews live
     */
    public function scrapeApp($appSlug, $appName = null) {
        if (!$appName) {
            $appName = ucfirst($appSlug);
        }
        
        echo "🔴 UNIVERSAL LIVE SCRAPER - NO MOCK DATA\n";
        echo "🎯 App: $appName ($appSlug)\n";
        echo "🌐 Scraping ONLY from live Shopify pages...\n";
        
        $baseUrl = "https://apps.shopify.com/$appSlug/reviews";
        
        // Clear existing data for this app
        $this->clearAppData($appName);
        
        $allReviews = [];
        $thirtyDaysAgo = strtotime('-30 days');
        
        // Scrape pages until we get all recent reviews
        for ($page = 1; $page <= 10; $page++) {
            $url = $baseUrl . "?sort_by=newest&page=$page";
            echo "📄 Fetching page $page: $url\n";
            
            $html = $this->fetchPage($url);
            if (!$html) {
                echo "❌ Failed to fetch page $page - STOPPING\n";
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
                    echo "✅ Live: {$review['review_date']} - {$review['rating']}★ - {$review['store_name']}\n";
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
            echo "❌ CRITICAL: No live reviews found for $appName\n";
            return ['success' => false, 'message' => 'No live reviews found', 'count' => 0];
        }
        
        // Save all live reviews
        $saved = 0;
        foreach ($allReviews as $review) {
            if ($this->saveReview($appName, $review)) {
                $saved++;
            }
        }
        
        // Update metadata
        $this->updateAppMetadata($appName, $html);
        
        echo "🎯 LIVE SCRAPING COMPLETE: $saved reviews saved for $appName\n";
        return ['success' => true, 'message' => "Scraped $saved live reviews", 'count' => $saved];
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
     * Parse reviews from HTML - Universal method
     */
    private function parseReviewsFromHTML($html) {
        $reviews = [];
        
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        
        // Find review containers (Shopify uses this structure)
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
     * Extract review data from DOM node - Universal method
     */
    private function extractReviewData($xpath, $node) {
        try {
            // Extract rating (count filled stars)
            $starNodes = $xpath->query('.//svg[contains(@class, "tw-fill-fg-primary")]', $node);
            $rating = $starNodes->length;

            // If no stars found, try alternative selectors
            if ($rating === 0) {
                $starNodes = $xpath->query('.//div[contains(@aria-label, "stars")]', $node);
                if ($starNodes->length > 0) {
                    $ariaLabel = $starNodes->item(0)->getAttribute('aria-label');
                    if (preg_match('/(\d+)\s*out\s*of\s*\d+\s*stars/', $ariaLabel, $matches)) {
                        $rating = intval($matches[1]);
                    }
                }
            }

            // Extract date - look for multiple year patterns
            $currentYear = date('Y');
            $lastYear = $currentYear - 1;
            $dateNode = $xpath->query('.//div[contains(@class, "tw-text-body-xs") and contains(@class, "tw-text-fg-tertiary") and (contains(text(), "' . $currentYear . '") or contains(text(), "' . $lastYear . '"))]', $node);
            $reviewDate = '';
            if ($dateNode->length > 0) {
                $dateText = trim($dateNode->item(0)->textContent);
                $reviewDate = date('Y-m-d', strtotime($dateText));
            }

            // If no date found, try alternative selectors
            if (empty($reviewDate)) {
                $allDateNodes = $xpath->query('.//div[contains(@class, "tw-text-fg-tertiary")]', $node);
                foreach ($allDateNodes as $dNode) {
                    $text = trim($dNode->textContent);
                    if (preg_match('/\b(January|February|March|April|May|June|July|August|September|October|November|December)\s+\d{1,2},\s+\d{4}\b/', $text)) {
                        $reviewDate = date('Y-m-d', strtotime($text));
                        break;
                    }
                }
            }

            // Extract store name
            $storeNode = $xpath->query('.//div[contains(@class, "tw-text-heading-xs") and contains(@class, "tw-text-fg-primary")]', $node);
            $storeName = '';
            if ($storeNode->length > 0) {
                $storeName = trim($storeNode->item(0)->textContent);
            }

            // If no store name found, try alternative selectors
            if (empty($storeName)) {
                $altStoreNodes = $xpath->query('.//div[contains(@class, "tw-text-fg-primary") and contains(@class, "tw-overflow-hidden")]', $node);
                if ($altStoreNodes->length > 0) {
                    $storeName = trim($altStoreNodes->item(0)->textContent);
                }
            }

            // Extract country
            $countryNodes = $xpath->query('.//div[contains(@class, "tw-text-fg-tertiary") and contains(@class, "tw-text-body-xs")]', $node);
            $country = 'Unknown';
            foreach ($countryNodes as $cNode) {
                $text = trim($cNode->textContent);
                if (!empty($text) && !preg_match('/\d{4}/', $text) && !strpos($text, 'replied') && strlen($text) < 50 && strlen($text) > 2) {
                    // Skip common non-country text
                    if (!in_array(strtolower($text), ['show more', 'show less', 'helpful', 'not helpful'])) {
                        $country = $text;
                        break;
                    }
                }
            }

            // Extract review content
            $contentNode = $xpath->query('.//p[contains(@class, "tw-break-words")]', $node);
            $reviewContent = '';
            if ($contentNode->length > 0) {
                $reviewContent = trim($contentNode->item(0)->textContent);
            }

            // If no content found, try alternative selectors
            if (empty($reviewContent)) {
                $altContentNodes = $xpath->query('.//div[contains(@class, "tw-text-body-md")]//p', $node);
                if ($altContentNodes->length > 0) {
                    $reviewContent = trim($altContentNodes->item(0)->textContent);
                }
            }

            // Validate required fields
            if (empty($storeName) || empty($reviewDate) || $rating === 0) {
                echo "⚠️ Skipping incomplete review: store='$storeName', date='$reviewDate', rating=$rating\n";
                return null;
            }

            return [
                'store_name' => $storeName,
                'country_name' => substr($country, 0, 50),
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
     * Clear existing app data
     */
    private function clearAppData($appName) {
        try {
            $conn = $this->dbManager->getConnection();
            $stmt = $conn->prepare("DELETE FROM reviews WHERE app_name = ?");
            $stmt->execute([$appName]);
            echo "✅ Cleared existing $appName data\n";
        } catch (Exception $e) {
            echo "❌ Error clearing data: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Save review to database
     */
    private function saveReview($appName, $review) {
        try {
            $conn = $this->dbManager->getConnection();
            $stmt = $conn->prepare("
                INSERT INTO reviews (app_name, store_name, country_name, rating, review_content, review_date)
                VALUES (?, ?, ?, ?, ?, ?)
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
    
    /**
     * Update app metadata from live page
     */
    private function updateAppMetadata($appName, $html) {
        try {
            // Extract metadata from JSON-LD
            if (preg_match('/"ratingCount":(\d+)/', $html, $matches)) {
                $totalReviews = intval($matches[1]);
                
                $conn = $this->dbManager->getConnection();
                $stmt = $conn->prepare("
                    INSERT INTO app_metadata (app_name, total_reviews, last_updated)
                    VALUES (?, ?, NOW())
                    ON DUPLICATE KEY UPDATE
                    total_reviews = ?, last_updated = NOW()
                ");
                $stmt->execute([$appName, $totalReviews, $totalReviews]);
                
                echo "✅ Updated $appName metadata: $totalReviews total reviews\n";
            }
        } catch (Exception $e) {
            echo "❌ Error updating metadata: " . $e->getMessage() . "\n";
        }
    }
}
?>
