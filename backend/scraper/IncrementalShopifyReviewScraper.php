<?php
/**
 * Incremental Shopify Review Scraper
 * Only scrapes first 3 pages to detect new reviews
 * Preserves existing database records and only adds new reviews
 * Much faster than full re-scraping
 */

require_once __DIR__ . '/../config/database.php';

class IncrementalShopifyReviewScraper {
    
    private $conn;
    private $appUrls = [
        'StoreSEO' => 'https://apps.shopify.com/storeseo/reviews?sort_by=newest&show_archived=false',
        'StoreFAQ' => 'https://apps.shopify.com/storefaq/reviews?sort_by=newest&show_archived=false',
        'EasyFlow' => 'https://apps.shopify.com/product-options-4/reviews?sort_by=newest&show_archived=false',
        'TrustSync' => 'https://apps.shopify.com/customer-review-app/reviews?sort_by=newest&show_archived=false',
        'BetterDocs FAQ Knowledge Base' => 'https://apps.shopify.com/betterdocs-knowledgebase/reviews?sort_by=newest&show_archived=false',
        'Vidify' => 'https://apps.shopify.com/vidify/reviews?sort_by=newest&show_archived=false'
    ];
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Scrape only first 3 pages to detect new reviews
     * Much faster than full scraping - only takes 10-15 seconds
     * Preserves all existing database records
     */
    public function scrapeRecentReviewsOnly($appName, $silent = false) {
        if (!isset($this->appUrls[$appName])) {
            return [
                'success' => false,
                'error' => "App '$appName' not found",
                'new_reviews_count' => 0
            ];
        }

        $baseUrl = $this->appUrls[$appName];
        $newReviews = [];
        $seenReviewIds = [];
        $pagesScraped = 0;
        $maxPages = 3; // Only check first 3 pages for new reviews

        if (!$silent) echo "🚀 Incremental scraping for $appName (checking first $maxPages pages)...\n";

        for ($page = 1; $page <= $maxPages; $page++) {
            $url = $baseUrl . "&page=$page";
            if (!$silent) echo "📄 Checking page $page...\n";

            $html = $this->fetchPage($url);
            if (!$html) {
                if (!$silent) echo "❌ Failed to fetch page $page\n";
                break;
            }

            $pageReviews = $this->parseReviewsFromHTML($html, $appName);

            if (empty($pageReviews)) {
                if (!$silent) echo "⚠️ No reviews found on page $page\n";
                break;
            }

            $pagesScraped++;

            // Check each review to see if it's new
            foreach ($pageReviews as $review) {
                $reviewId = $this->generateReviewId($review);
                
                // Skip if we've already seen this review on this scrape
                if (isset($seenReviewIds[$reviewId])) {
                    continue;
                }
                
                $seenReviewIds[$reviewId] = true;

                // Check if review already exists in database
                if (!$this->reviewExists($review)) {
                    $newReviews[] = $review;
                }
            }

            if (!$silent) {
                echo "✅ Page $page: Found " . count($pageReviews) . " reviews\n";
            }

            usleep(1000000); // 1 second delay
        }

        if (!$silent) {
            echo "🎯 Incremental scan complete: Found $pagesScraped pages, $" . count($newReviews) . " new reviews\n";
        }

        return [
            'success' => true,
            'new_reviews' => $newReviews,
            'new_reviews_count' => count($newReviews),
            'pages_scanned' => $pagesScraped
        ];
    }

    /**
     * Check if a review already exists in the database
     */
    private function reviewExists($review) {
        $stmt = $this->conn->prepare("
            SELECT id FROM reviews 
            WHERE app_name = ? 
            AND store_name = ? 
            AND review_date = ?
            AND review_content = ?
            LIMIT 1
        ");
        
        $stmt->execute([
            $review['app_name'],
            $review['store_name'],
            $review['review_date'],
            $review['review_content']
        ]);
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Add new reviews to database (preserves existing records)
     */
    public function addNewReviewsToDatabase($appName, $newReviews) {
        if (empty($newReviews)) {
            return ['success' => true, 'added_count' => 0];
        }

        $insertStmt = $this->conn->prepare("
            INSERT INTO reviews (
                app_name, store_name, country_name, rating, review_content, 
                review_date, earned_by, is_active, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, TRUE, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                is_active = TRUE,
                updated_at = NOW()
        ");

        $addedCount = 0;
        foreach ($newReviews as $review) {
            try {
                $insertStmt->execute([
                    $review['app_name'],
                    $review['store_name'],
                    $review['country_name'],
                    $review['rating'],
                    $review['review_content'],
                    $review['review_date'],
                    $review['earned_by'] ?? null
                ]);
                $addedCount++;
            } catch (Exception $e) {
                error_log("Error adding review: " . $e->getMessage());
            }
        }

        return [
            'success' => true,
            'added_count' => $addedCount
        ];
    }

    /**
     * Fetch a page from Shopify
     */
    private function fetchPage($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $html = curl_exec($ch);
        curl_close($ch);

        return $html ?: null;
    }

    /**
     * Parse reviews from HTML
     */
    private function parseReviewsFromHTML($html, $appName) {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $reviewNodes = $xpath->query('//div[@data-review-content-id]');

        $reviews = [];
        foreach ($reviewNodes as $reviewNode) {
            $review = $this->extractReviewData($reviewNode, $xpath, $appName);
            if ($review) {
                $reviews[] = $review;
            }
        }

        return $reviews;
    }

    /**
     * Extract review data from a review node
     */
    private function extractReviewData($reviewNode, $xpath, $appName) {
        // Extract store name
        $storeNodes = $xpath->query('.//div[contains(@class, "tw-text-heading-xs")]', $reviewNode);
        $storeName = $storeNodes->length > 0 ? trim($storeNodes->item(0)->textContent) : 'Unknown';

        // Extract rating
        $ratingNodes = $xpath->query('.//span[contains(@class, "tw-text-heading-sm")]', $reviewNode);
        $rating = 0;
        if ($ratingNodes->length > 0) {
            $ratingText = trim($ratingNodes->item(0)->textContent);
            $rating = intval(substr($ratingText, 0, 1));
        }

        // Extract review content
        $contentNodes = $xpath->query('.//p[contains(@class, "tw-text-body-sm")]', $reviewNode);
        $reviewContent = $contentNodes->length > 0 ? trim($contentNodes->item(0)->textContent) : '';

        // Extract date
        $dateNodes = $xpath->query('.//div[contains(@class, "tw-text-body-xs") and contains(@class, "tw-text-fg-tertiary")]', $reviewNode);
        $dateText = $dateNodes->length > 0 ? trim($dateNodes->item(0)->textContent) : '';
        $reviewDate = $this->parseReviewDateSafely($dateText);

        // Extract country
        $countryNodes = $xpath->query('.//div[contains(@class, "tw-text-body-xs")]', $reviewNode);
        $countryName = 'Unknown';
        foreach ($countryNodes as $node) {
            $text = trim($node->textContent);
            if (strlen($text) > 0 && strlen($text) < 50 && !preg_match('/^(January|February|March|April|May|June|July|August|September|October|November|December)/', $text)) {
                $countryName = $text;
                break;
            }
        }

        if (empty($reviewDate) || empty($reviewContent)) {
            return null;
        }

        return [
            'app_name' => $appName,
            'store_name' => $storeName,
            'country_name' => $countryName,
            'rating' => $rating,
            'review_content' => $reviewContent,
            'review_date' => $reviewDate,
            'earned_by' => null
        ];
    }

    /**
     * Generate unique review ID for deduplication
     */
    private function generateReviewId($review) {
        return md5($review['store_name'] . $review['review_date'] . $review['review_content']);
    }

    /**
     * Parse review date safely
     */
    private function parseReviewDateSafely($dateText) {
        $dateText = trim($dateText);

        if (preg_match('/\b(January|February|March|April|May|June|July|August|September|October|November|December)\s+(\d{1,2}),\s+(\d{4})\b/', $dateText, $matches)) {
            $monthName = $matches[1];
            $day = $matches[2];
            $year = $matches[3];
            $cleanDateStr = "$monthName $day, $year";
            $timestamp = strtotime($cleanDateStr);

            if ($timestamp !== false) {
                return date('Y-m-d', $timestamp);
            }
        }

        $timestamp = strtotime($dateText);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return '';
    }
}
?>

