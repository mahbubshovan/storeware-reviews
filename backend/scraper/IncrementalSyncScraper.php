<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/ReviewRepository.php';

/**
 * Incremental Sync Scraper for Shopify Apps
 * 
 * Smart incremental syncing:
 * 1. First sync: Scrape all pages to get complete historical data
 * 2. Subsequent syncs: Only scrape page 1, detect new reviews, then scrape only new pages
 * 3. Always extract and store total review count from live page
 */
class IncrementalSyncScraper {
    private $dbManager;
    private $repository;
    private $conn;

    public function __construct() {
        $this->dbManager = new Database();
        $this->repository = new ReviewRepository();
        $this->conn = $this->dbManager->getConnection();
    }
    
    /**
     * Perform incremental sync for an app
     * Returns: ['success' => bool, 'message' => string, 'count' => int, 'total_count' => int, 'new_reviews' => int]
     */
    public function incrementalSync($appSlug, $appName = null) {
        if (!$appName) {
            $appName = ucfirst($appSlug);
        }
        
        echo "\n🔄 INCREMENTAL SYNC - $appName\n";
        echo "=====================================\n";
        
        $baseUrl = "https://apps.shopify.com/$appSlug/reviews";
        
        // Step 1: Check if this is first sync or subsequent sync
        $existingCount = $this->getExistingReviewCount($appName);
        $isFirstSync = $existingCount === 0;
        
        echo ($isFirstSync ? "📌 FIRST SYNC" : "🔄 INCREMENTAL SYNC") . " - Current DB count: $existingCount\n";
        
        // Step 2: Fetch page 1 and extract total count from live page
        $page1Html = $this->fetchPage($baseUrl . "?sort_by=newest&page=1");
        if (!$page1Html) {
            echo "❌ Failed to fetch page 1\n";
            return ['success' => false, 'message' => 'Failed to fetch page 1', 'count' => 0];
        }
        
        $page1Reviews = $this->parseReviewsFromHTML($page1Html);
        $liveTotal = $this->extractTotalReviewCount($page1Html);
        
        echo "📊 Live Shopify shows: $liveTotal total reviews\n";
        echo "📄 Page 1 has: " . count($page1Reviews) . " reviews\n";
        
        if ($isFirstSync) {
            return $this->performFullSync($baseUrl, $appName, $liveTotal);
        } else {
            return $this->performIncrementalSync($baseUrl, $appName, $page1Reviews, $liveTotal);
        }
    }
    
    /**
     * Perform full sync (first time only)
     */
    private function performFullSync($baseUrl, $appName, $liveTotal) {
        echo "\n🚀 FULL SYNC: Scraping all pages...\n";

        $allReviews = [];
        $totalScraped = 0;

        // Scrape all pages
        for ($page = 1; $page <= 200; $page++) {
            $url = $baseUrl . "?sort_by=newest&page=$page";
            echo "📄 Page $page: ";

            $html = $this->fetchPage($url);
            if (!$html) {
                echo "❌ Failed\n";
                break;
            }

            $pageReviews = $this->parseReviewsFromHTML($html);
            if (empty($pageReviews)) {
                echo "⚠️ No reviews found - STOPPING\n";
                break;
            }

            foreach ($pageReviews as $review) {
                $allReviews[] = $review;
                $totalScraped++;
            }

            echo "✅ " . count($pageReviews) . " reviews\n";
        }

        // Save all reviews
        $saved = $this->saveReviewsBatch($appName, $allReviews);

        // Trim to live total count (keep most recent reviews)
        $this->trimToLiveCount($appName, $liveTotal);

        // Update metadata with live total count
        $this->updateAppMetadata($appName, $liveTotal);

        echo "\n✅ FULL SYNC COMPLETE\n";
        echo "   Scraped: $totalScraped reviews\n";
        echo "   Saved: $saved reviews\n";
        echo "   Trimmed to: $liveTotal reviews (live total)\n";

        return [
            'success' => true,
            'message' => "Full sync complete: $liveTotal reviews",
            'count' => $liveTotal,
            'total_count' => $liveTotal,
            'new_reviews' => $liveTotal
        ];
    }

    /**
     * Trim reviews to match live total count (keep most recent)
     */
    private function trimToLiveCount($appName, $targetCount) {
        try {
            // Get IDs of reviews to keep (most recent)
            $stmt = $this->conn->prepare("
                SELECT id FROM reviews
                WHERE app_name = ?
                ORDER BY review_date DESC, created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$appName, $targetCount]);
            $idsToKeep = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (count($idsToKeep) > 0) {
                $placeholders = implode(',', array_fill(0, count($idsToKeep), '?'));

                $stmt = $this->conn->prepare("
                    DELETE FROM reviews
                    WHERE app_name = ?
                    AND id NOT IN ($placeholders)
                ");

                $params = array_merge([$appName], $idsToKeep);
                $stmt->execute($params);
                $deleted = $stmt->rowCount();

                if ($deleted > 0) {
                    echo "   Removed $deleted older reviews to match live count\n";
                }
            }
        } catch (Exception $e) {
            // Silently fail - not critical
        }
    }
    
    /**
     * Perform incremental sync (subsequent syncs)
     */
    private function performIncrementalSync($baseUrl, $appName, $page1Reviews, $liveTotal) {
        echo "\n🔍 INCREMENTAL SYNC: Detecting new reviews...\n";
        
        // Get most recent review from database
        $mostRecentReview = $this->getMostRecentReview($appName);
        
        if (!$mostRecentReview) {
            echo "⚠️ No reviews in database, falling back to full sync\n";
            return $this->performFullSync($baseUrl, $appName, $liveTotal);
        }
        
        echo "📌 Most recent in DB: {$mostRecentReview['review_date']} - {$mostRecentReview['store_name']}\n";
        
        // Check if page 1 reviews already exist in database
        $newReviews = [];
        $foundExisting = false;
        
        foreach ($page1Reviews as $review) {
            if ($this->reviewExists($appName, $review)) {
                $foundExisting = true;
                echo "✅ Found existing review: {$review['store_name']} ({$review['review_date']})\n";
                break;
            } else {
                $newReviews[] = $review;
                echo "🆕 New review: {$review['store_name']} ({$review['review_date']})\n";
            }
        }
        
        // If all page 1 reviews are new, scrape more pages
        if (!$foundExisting && !empty($newReviews)) {
            echo "\n📄 All page 1 reviews are new, scraping more pages...\n";
            
            for ($page = 2; $page <= 200; $page++) {
                $url = $baseUrl . "?sort_by=newest&page=$page";
                echo "📄 Page $page: ";
                
                $html = $this->fetchPage($url);
                if (!$html) {
                    echo "❌ Failed\n";
                    break;
                }
                
                $pageReviews = $this->parseReviewsFromHTML($html);
                if (empty($pageReviews)) {
                    echo "⚠️ No reviews\n";
                    break;
                }
                
                $foundExistingOnPage = false;
                foreach ($pageReviews as $review) {
                    if ($this->reviewExists($appName, $review)) {
                        $foundExistingOnPage = true;
                        echo "✅ Found existing - STOPPING\n";
                        break;
                    } else {
                        $newReviews[] = $review;
                    }
                }
                
                echo "✅ " . count($pageReviews) . " reviews\n";
                
                if ($foundExistingOnPage) {
                    break;
                }
            }
        }
        
        // Save new reviews
        $saved = $this->saveReviewsBatch($appName, $newReviews);
        
        // Update metadata with live total count
        $this->updateAppMetadata($appName, $liveTotal);
        
        echo "\n✅ INCREMENTAL SYNC COMPLETE\n";
        echo "   New reviews: " . count($newReviews) . "\n";
        echo "   Saved: $saved reviews\n";
        echo "   Live Total: $liveTotal reviews\n";
        
        return [
            'success' => true,
            'message' => "Incremental sync complete: $saved new reviews",
            'count' => $saved,
            'total_count' => $liveTotal,
            'new_reviews' => $saved
        ];
    }
    
    /**
     * Get existing review count for app
     */
    private function getExistingReviewCount($appName) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM reviews WHERE app_name = ?");
        $stmt->execute([$appName]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
    
    /**
     * Get most recent review from database
     */
    private function getMostRecentReview($appName) {
        $stmt = $this->conn->prepare("
            SELECT id, store_name, review_date, review_content, rating
            FROM reviews
            WHERE app_name = ?
            ORDER BY review_date DESC, created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$appName]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Check if review already exists in database
     */
    private function reviewExists($appName, $review) {
        $stmt = $this->conn->prepare("
            SELECT id FROM reviews
            WHERE app_name = ? AND store_name = ? AND review_date = ? AND rating = ?
            LIMIT 1
        ");
        $stmt->execute([
            $appName,
            $review['store_name'],
            $review['review_date'],
            $review['rating']
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }
    
    /**
     * Save batch of reviews with deduplication
     */
    private function saveReviewsBatch($appName, $reviews) {
        $saved = 0;
        $duplicates = 0;

        // First, deduplicate within the batch
        $uniqueReviews = [];
        $seen = [];

        foreach ($reviews as $review) {
            $key = md5($review['store_name'] . $review['review_date'] . $review['rating']);

            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $uniqueReviews[] = $review;
            } else {
                $duplicates++;
            }
        }

        echo "   Deduplicated: Removed $duplicates duplicates from batch\n";

        // Now save unique reviews
        foreach ($uniqueReviews as $review) {
            try {
                $stmt = $this->conn->prepare("
                    INSERT INTO reviews (app_name, store_name, country_name, rating, review_content, review_date)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $appName,
                    $review['store_name'],
                    $review['country_name'],
                    $review['rating'],
                    $review['review_content'],
                    $review['review_date']
                ]);
                $saved++;
            } catch (Exception $e) {
                // Duplicate or error - skip
            }
        }
        return $saved;
    }
    
    /**
     * Extract total review count from live page
     */
    private function extractTotalReviewCount($html) {
        // Try JSON-LD first
        if (preg_match('/"ratingCount":(\d+)/', $html, $matches)) {
            return intval($matches[1]);
        }
        
        // Fallback: count from page
        return 0;
    }
    
    /**
     * Update app metadata with total count
     */
    private function updateAppMetadata($appName, $totalCount) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO app_metadata (app_name, total_reviews, updated_at)
                VALUES (?, ?, NOW())
                ON DUPLICATE KEY UPDATE total_reviews = ?, updated_at = NOW()
            ");
            $stmt->execute([$appName, $totalCount, $totalCount]);
        } catch (Exception $e) {
            // Table might not exist, skip
        }
    }
    
    /**
     * Fetch page with retry logic
     */
    private function fetchPage($url) {
        $maxRetries = 3;
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            
            $html = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200 && $html) {
                return $html;
            }
            
            if ($attempt < $maxRetries) {
                sleep(pow(2, $attempt));
            }
        }
        return false;
    }
    
    /**
     * Parse reviews from HTML
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
            // Extract rating
            $starNodes = $xpath->query('.//svg[contains(@class, "tw-fill-fg-primary")]', $node);
            $rating = $starNodes->length;

            if ($rating === 0) {
                $starNodes = $xpath->query('.//div[contains(@aria-label, "stars")]', $node);
                if ($starNodes->length > 0) {
                    $ariaLabel = $starNodes->item(0)->getAttribute('aria-label');
                    if (preg_match('/(\d+)\s*out\s*of\s*\d+\s*stars/', $ariaLabel, $matches)) {
                        $rating = intval($matches[1]);
                    }
                }
            }

            // Extract date
            $currentYear = date('Y');
            $lastYear = $currentYear - 1;
            $dateNode = $xpath->query('.//div[contains(@class, "tw-text-body-xs") and contains(@class, "tw-text-fg-tertiary")]', $node);
            $reviewDate = '';
            if ($dateNode->length > 0) {
                $dateText = trim($dateNode->item(0)->textContent);
                $reviewDate = date('Y-m-d', strtotime($dateText));
            }

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

            if (empty($storeName)) {
                $altStoreNodes = $xpath->query('.//div[contains(@class, "tw-text-fg-primary") and contains(@class, "tw-overflow-hidden")]', $node);
                if ($altStoreNodes->length > 0) {
                    $storeName = trim($altStoreNodes->item(0)->textContent);
                }
            }

            // Extract country
            $country = $this->extractCountryFromReview($node);

            // Extract review content
            $contentNode = $xpath->query('.//p[contains(@class, "tw-break-words")]', $node);
            $reviewContent = '';
            if ($contentNode->length > 0) {
                $reviewContent = trim($contentNode->item(0)->textContent);
            }

            if (empty($reviewContent)) {
                $altContentNodes = $xpath->query('.//div[contains(@class, "tw-text-body-md")]//p', $node);
                if ($altContentNodes->length > 0) {
                    $reviewContent = trim($altContentNodes->item(0)->textContent);
                }
            }

            // Validate required fields
            if (empty($storeName) || empty($reviewDate) || $rating === 0) {
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
            return null;
        }
    }

    /**
     * Extract country from review
     */
    private function extractCountryFromReview($node) {
        try {
            $xpath = new DOMXPath($node->ownerDocument);
            $countryNodes = $xpath->query('.//div[contains(@class, "tw-text-body-xs")]', $node);

            foreach ($countryNodes as $cNode) {
                $text = trim($cNode->textContent);
                if (strlen($text) > 2 && strlen($text) < 50 && !is_numeric($text)) {
                    return $text;
                }
            }
        } catch (Exception $e) {
            // Return default
        }

        return 'Unknown';
    }
}
?>

