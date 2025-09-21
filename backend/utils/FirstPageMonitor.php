<?php
/**
 * First Page Review Monitor
 * Efficiently monitors only the first page of each app's reviews to detect new reviews
 * for the Access Review Tab functionality
 */

require_once __DIR__ . '/DatabaseManager.php';

class FirstPageMonitor {
    private $dbManager;
    private $apps;
    
    public function __construct() {
        $this->dbManager = new DatabaseManager();
        $this->apps = [
            'StoreSEO' => 'https://apps.shopify.com/storeseo/reviews?sort_by=newest&page=1',
            'StoreFAQ' => 'https://apps.shopify.com/storefaq/reviews?sort_by=newest&page=1',
            'EasyFlow' => 'https://apps.shopify.com/product-options-4/reviews?sort_by=newest&page=1',
            'TrustSync' => 'https://apps.shopify.com/customer-review-app/reviews?sort_by=newest&page=1',
            'Vitals' => 'https://apps.shopify.com/vitals/reviews?sort_by=newest&page=1',
            'BetterDocs FAQ' => 'https://apps.shopify.com/betterdocs-knowledgebase/reviews?sort_by=newest&page=1',
            'Vidify' => 'https://apps.shopify.com/vidify/reviews?sort_by=newest&page=1'
        ];
    }
    
    /**
     * Monitor all apps for new reviews on their first pages
     */
    public function monitorAllApps() {
        echo "🔍 Starting First Page Review Monitoring\n";
        echo "========================================\n";
        
        $totalNewReviews = 0;
        
        foreach ($this->apps as $appName => $url) {
            echo "\n📱 Monitoring {$appName}...\n";
            $newReviews = $this->monitorApp($appName, $url);
            $totalNewReviews += $newReviews;
            
            // Add delay between apps to avoid rate limiting
            sleep(2);
        }
        
        echo "\n✅ Monitoring Complete!\n";
        echo "📊 Total new reviews detected: {$totalNewReviews}\n";
        
        return $totalNewReviews;
    }
    
    /**
     * Monitor a specific app for new reviews
     */
    public function monitorApp($appName, $url) {
        try {
            // Scrape first page
            $reviews = $this->scrapeFirstPage($url, $appName);
            
            if (empty($reviews)) {
                echo "⚠️  No reviews found on first page\n";
                return 0;
            }
            
            // Check for new reviews
            $newReviews = $this->detectNewReviews($appName, $reviews);
            
            if (count($newReviews) > 0) {
                // Add new reviews to access_reviews table
                $added = $this->addNewReviews($appName, $newReviews);
                echo "✅ Added {$added} new reviews to access_reviews\n";
                return $added;
            } else {
                echo "ℹ️  No new reviews detected\n";
                return 0;
            }
            
        } catch (Exception $e) {
            echo "❌ Error monitoring {$appName}: " . $e->getMessage() . "\n";
            return 0;
        }
    }
    
    /**
     * Scrape only the first page of reviews
     */
    private function scrapeFirstPage($url, $appName) {
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
                'Accept-Encoding: gzip, deflate',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1',
            ],
            CURLOPT_ENCODING => 'gzip'
        ]);
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP {$httpCode} error for {$appName}");
        }
        
        if (!$html) {
            throw new Exception("Empty response for {$appName}");
        }
        
        return $this->extractReviews($html, $appName);
    }
    
    /**
     * Extract reviews from HTML using DOMDocument
     */
    private function extractReviews($html, $appName) {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        $reviews = [];
        
        // Find review containers using data-review-content-id attribute
        $reviewContainers = $xpath->query("//div[@data-review-content-id]");
        
        echo "📝 Found " . $reviewContainers->length . " review containers\n";
        
        foreach ($reviewContainers as $container) {
            $review = $this->extractReviewFromContainer($container, $xpath);
            if ($review) {
                $reviews[] = $review;
            }
        }
        
        return $reviews;
    }
    
    /**
     * Extract individual review data from container
     */
    private function extractReviewFromContainer($container, $xpath) {
        // Extract rating using multiple methods
        $rating = $this->extractRatingFromContainer($container, $xpath);

        // Extract review text - try multiple selectors
        $reviewText = '';
        $reviewSelectors = [
            ".//div[contains(@class, 'review-content')]//p",
            ".//div[contains(@class, 'truncate-content-copy')]",
            ".//div[contains(@class, 'review-content')]",
            ".//p[contains(@class, 'review-text')]",
            ".//div[@class='review-content-body']//p",
            ".//div[contains(@class, 'review-listing-content')]//p"
        ];

        foreach ($reviewSelectors as $selector) {
            $reviewTextNodes = $xpath->query($selector, $container);
            foreach ($reviewTextNodes as $textNode) {
                $text = trim($textNode->textContent);
                if (!empty($text) && strlen($text) > 10) {
                    $reviewText = $text;
                    break 2;
                }
            }
        }

        // If no specific review text found, get all text content and extract meaningful part
        if (empty($reviewText)) {
            $allText = trim($container->textContent);
            $lines = explode("\n", $allText);
            foreach ($lines as $line) {
                $line = trim($line);
                if (strlen($line) > 20 && !preg_match('/^\d+/', $line) && !preg_match('/^[A-Z]{2,3}$/', $line)) {
                    $reviewText = $line;
                    break;
                }
            }
        }

        // Extract store name - try multiple approaches
        $storeName = '';
        $storeSelectors = [
            ".//h3[contains(@class, 'review-listing-header')]",
            ".//div[contains(@class, 'review-metadata')]//strong",
            ".//strong[contains(@class, 'review-author')]",
            ".//div[contains(@class, 'review-listing-header')]//h3",
            ".//h3"
        ];

        foreach ($storeSelectors as $selector) {
            $storeNameNodes = $xpath->query($selector, $container);
            foreach ($storeNameNodes as $nameNode) {
                $name = trim($nameNode->textContent);
                if (!empty($name) && strlen($name) > 2 && strlen($name) < 100) {
                    $storeName = $name;
                    break 2;
                }
            }
        }

        // Extract country (if available)
        $country = '';
        $countryNodes = $xpath->query(".//span[contains(@class, 'review-metadata')] | .//div[contains(@class, 'review-metadata')]//span", $container);
        foreach ($countryNodes as $countryNode) {
            $text = trim($countryNode->textContent);
            if (preg_match('/^[A-Z]{2,3}$/', $text) || (strlen($text) > 2 && strlen($text) < 30)) {
                $country = $text;
                break;
            }
        }

        // Extract date - try to find date in the container
        $reviewDate = date('Y-m-d'); // Default to today
        $allText = $container->textContent;

        // Look for date patterns in the text
        if (preg_match('/(\w+\s+\d+,\s+\d{4})/', $allText, $matches)) {
            $parsedDate = $this->parseReviewDate($matches[1]);
            if ($parsedDate) {
                $reviewDate = $parsedDate;
            }
        } elseif (preg_match('/(\d{1,2}\/\d{1,2}\/\d{4})/', $allText, $matches)) {
            $parsedDate = $this->parseReviewDate($matches[1]);
            if ($parsedDate) {
                $reviewDate = $parsedDate;
            }
        }

        // Debug output
        echo "⭐ {$rating}★ - {$storeName}: " . substr($reviewText, 0, 50) . "...\n";

        // Validate required fields
        if (empty($reviewText) || $rating < 1 || $rating > 5) {
            return null;
        }

        return [
            'store_name' => $storeName,
            'country_name' => $country,
            'rating' => $rating,
            'review_content' => $reviewText,
            'review_date' => $reviewDate
        ];
    }

    /**
     * Extract rating from review container using multiple methods
     */
    private function extractRatingFromContainer($container, $xpath) {
        // Method 1: Look for aria-label with rating (most reliable)
        $ratingNodes = $xpath->query(".//*[contains(@aria-label, 'out of') and contains(@aria-label, 'stars')]", $container);
        foreach ($ratingNodes as $node) {
            $ariaLabel = $node->getAttribute('aria-label');
            if (preg_match('/(\d+)\s*out\s*of\s*\d+\s*stars/', $ariaLabel, $matches)) {
                return intval($matches[1]);
            }
        }

        // Method 2: Count filled star elements
        $starNodes = $xpath->query(".//span[contains(@class, 'star') and contains(@class, 'filled')] | .//i[contains(@class, 'star') and contains(@class, 'filled')]", $container);
        if ($starNodes->length > 0) {
            return $starNodes->length;
        }

        // Method 3: Look for rating in text content
        $textNodes = $xpath->query(".//*[contains(text(), 'star') or contains(text(), '★')]", $container);
        foreach ($textNodes as $node) {
            $text = $node->textContent;
            if (preg_match('/(\d+)\s*star/', $text, $matches)) {
                return intval($matches[1]);
            }
            if (preg_match('/★{1,5}/', $text, $matches)) {
                return strlen($matches[0]);
            }
        }

        // Default to 5 stars if no rating found (most reviews are 5 stars)
        return 5;
    }

    /**
     * Parse review date from various formats
     */
    private function parseReviewDate($dateText) {
        // Try different date formats
        $formats = [
            'Y-m-d',
            'm/d/Y',
            'd/m/Y',
            'M d, Y',
            'F d, Y',
            'Y-m-d H:i:s'
        ];

        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, $dateText);
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }

        // Try strtotime as fallback
        $timestamp = strtotime($dateText);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return null;
    }

    /**
     * Detect new reviews by comparing against existing access_reviews data
     */
    private function detectNewReviews($appName, $scrapedReviews) {
        $conn = $this->dbManager->getConnection();

        // Get existing reviews from access_reviews table for this app
        $stmt = $conn->prepare("
            SELECT review_content, review_date, store_name
            FROM access_reviews
            WHERE app_name = ?
        ");
        $stmt->execute([$appName]);
        $existingReviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Create lookup array for faster comparison
        $existingLookup = [];
        foreach ($existingReviews as $existing) {
            $key = $this->createReviewKey($existing['review_content'], $existing['store_name'], $existing['review_date']);
            $existingLookup[$key] = true;
        }

        // Find new reviews
        $newReviews = [];
        foreach ($scrapedReviews as $review) {
            $key = $this->createReviewKey($review['review_content'], $review['store_name'], $review['review_date']);
            if (!isset($existingLookup[$key])) {
                $newReviews[] = $review;
            }
        }

        echo "📊 Found " . count($scrapedReviews) . " reviews on first page, " . count($newReviews) . " are new\n";

        return $newReviews;
    }

    /**
     * Create a unique key for review comparison
     */
    private function createReviewKey($content, $storeName, $date) {
        // Use first 100 characters of content + store name + date for uniqueness
        $contentSnippet = substr(trim($content), 0, 100);
        return md5($contentSnippet . '|' . $storeName . '|' . $date);
    }

    /**
     * Add new reviews to access_reviews table
     */
    private function addNewReviews($appName, $newReviews) {
        if (empty($newReviews)) {
            return 0;
        }

        $conn = $this->dbManager->getConnection();

        // First, add reviews to main reviews table to get IDs
        $insertedIds = [];
        foreach ($newReviews as $review) {
            $stmt = $conn->prepare("
                INSERT INTO reviews (app_name, store_name, country_name, rating, review_content, review_date, source_type)
                VALUES (?, ?, ?, ?, ?, ?, 'first_page_monitor')
            ");

            $stmt->execute([
                $appName,
                $review['store_name'],
                $review['country_name'],
                $review['rating'],
                $review['review_content'],
                $review['review_date']
            ]);

            $insertedIds[] = $conn->lastInsertId();
        }

        // Then add to access_reviews table
        $addedCount = 0;
        foreach ($insertedIds as $index => $reviewId) {
            $review = $newReviews[$index];

            try {
                $stmt = $conn->prepare("
                    INSERT INTO access_reviews (app_name, review_date, review_content, country_name, rating, store_name, original_review_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $appName,
                    $review['review_date'],
                    $review['review_content'],
                    $review['country_name'],
                    $review['rating'],
                    $review['store_name'],
                    $reviewId
                ]);

                $addedCount++;
            } catch (PDOException $e) {
                // Skip duplicates (unique constraint violation)
                if ($e->getCode() !== '23000') {
                    throw $e;
                }
            }
        }

        return $addedCount;
    }

    /**
     * Get monitoring statistics
     */
    public function getMonitoringStats() {
        $conn = $this->dbManager->getConnection();

        $stats = [];
        foreach (array_keys($this->apps) as $appName) {
            // Get counts from access_reviews table
            $stmt = $conn->prepare("
                SELECT
                    COUNT(*) as total_reviews,
                    COUNT(CASE WHEN review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as last_30_days,
                    COUNT(CASE WHEN review_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN 1 END) as this_month
                FROM access_reviews
                WHERE app_name = ?
            ");
            $stmt->execute([$appName]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $stats[$appName] = [
                'total_reviews' => intval($result['total_reviews']),
                'last_30_days' => intval($result['last_30_days']),
                'this_month' => intval($result['this_month'])
            ];
        }

        return $stats;
    }
}
