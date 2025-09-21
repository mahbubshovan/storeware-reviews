<?php
require_once 'utils/DatabaseManager.php';
require_once 'utils/ReviewRepository.php';

class ExactCountScraper {
    private $dbManager;
    private $reviewRepo;
    private $apps;
    
    public function __construct() {
        $this->dbManager = new DatabaseManager();
        $this->reviewRepo = new ReviewRepository();
        
        // Define apps with their correct Shopify URLs and exact target counts
        $this->apps = [
            'EasyFlow' => ['slug' => 'product-options-4', 'target' => 308],
            'BetterDocs FAQ' => ['slug' => 'betterdocs-knowledgebase', 'target' => 31],
            'TrustSync' => ['slug' => 'customer-review-app', 'target' => 38],
            'Vidify' => ['slug' => 'vidify', 'target' => 8]
        ];
    }
    
    public function scrapeAllAppsExact() {
        echo "🎯 EXACT COUNT SCRAPER\n";
        echo "======================\n";
        echo "Fixing apps to match official Shopify page counts exactly\n\n";
        
        $totalResults = [];
        
        foreach ($this->apps as $appName => $config) {
            echo "📱 Starting {$appName} (Target: {$config['target']} reviews)...\n";
            echo str_repeat("-", 60) . "\n";
            
            // Clean existing data first
            $this->cleanAppData($appName);
            
            // Scrape the app to exact count
            $result = $this->scrapeAppExact($appName, $config['slug'], $config['target']);
            $totalResults[$appName] = $result;
            
            echo "✅ {$appName} completed: {$result['total_reviews']} reviews (Target: {$config['target']})\n\n";
            
            // Wait between apps to avoid rate limiting
            sleep(5);
        }
        
        $this->showFinalSummary($totalResults);
        return $totalResults;
    }
    
    private function cleanAppData($appName) {
        echo "🧹 Cleaning existing {$appName} data...\n";
        $conn = $this->dbManager->getConnection();
        
        // Delete in proper order due to foreign key constraints
        $stmt = $conn->prepare('DELETE FROM access_reviews WHERE app_name = ?');
        $stmt->execute([$appName]);
        
        $stmt = $conn->prepare('DELETE FROM reviews WHERE app_name = ?');
        $stmt->execute([$appName]);
        
        $stmt = $conn->prepare('DELETE FROM review_repository WHERE app_name = ?');
        $stmt->execute([$appName]);
        
        echo "✅ {$appName} data cleaned\n";
    }
    
    private function scrapeAppExact($appName, $appSlug, $targetCount) {
        $baseUrl = "https://apps.shopify.com/{$appSlug}/reviews";
        $allReviews = [];
        $page = 1;
        $maxPages = 50; // Should be enough for most apps
        
        while ($page <= $maxPages && count($allReviews) < $targetCount) {
            echo "📄 Scraping {$appName} page {$page}...\n";
            
            $url = "{$baseUrl}?page={$page}&sort_by=newest";
            $reviews = $this->scrapePage($url, $appName);
            
            // Handle rate limiting with retries
            if (empty($reviews) && $page <= 40) {
                echo "⏳ Possible rate limit, waiting 10 seconds...\n";
                sleep(10);
                $reviews = $this->scrapePage($url, $appName);
                
                if (empty($reviews)) {
                    echo "⏳ Still no reviews, waiting 30 seconds for final retry...\n";
                    sleep(30);
                    $reviews = $this->scrapePage($url, $appName);
                }
            }
            
            if (empty($reviews)) {
                echo "📝 No reviews found on page {$page} - reached end\n";
                break;
            }
            
            $allReviews = array_merge($allReviews, $reviews);
            echo "📝 Found " . count($reviews) . " reviews on page {$page}\n";
            echo "📊 Total so far: " . count($allReviews) . " reviews (Target: {$targetCount})\n";
            
            // Stop if we've reached or exceeded the target count
            if (count($allReviews) >= $targetCount) {
                echo "🎯 Target reached! Trimming to exactly {$targetCount} reviews\n";
                $allReviews = array_slice($allReviews, 0, $targetCount);
                break;
            }
            
            $page++;
            // Delay between requests to avoid rate limiting
            usleep(1500000); // 1.5 second delay
        }
        
        echo "✅ {$appName} scraping completed!\n";
        echo "📊 Total reviews collected: " . count($allReviews) . " (Target: {$targetCount})\n";
        
        // Verify we got the exact count
        if (count($allReviews) != $targetCount) {
            echo "⚠️ WARNING: Got " . count($allReviews) . " reviews but target was {$targetCount}\n";
        }
        
        // Save to database
        $this->saveReviews($allReviews, $appName);
        
        return [
            'total_reviews' => count($allReviews),
            'target_count' => $targetCount,
            'pages_scraped' => $page - 1,
            'rating_distribution' => $this->calculateRatingDistribution($allReviews)
        ];
    }
    
    private function scrapePage($url, $appName) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Accept-Encoding: gzip, deflate',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
        ]);
        curl_setopt($ch, CURLOPT_ENCODING, ''); // Handle gzip
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            echo "❌ cURL Error: " . curl_error($ch) . "\n";
            curl_close($ch);
            return [];
        }
        
        if ($httpCode !== 200) {
            echo "❌ HTTP Error: {$httpCode} for {$url}\n";
            if ($httpCode == 429) {
                echo "⚠️ Rate limit detected (429), will retry with delay\n";
            }
            curl_close($ch);
            return [];
        }
        
        curl_close($ch);
        
        return $this->parseReviews($html, $appName);
    }
    
    private function parseReviews($html, $appName) {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        
        // Find review containers
        $reviewContainers = $xpath->query("//div[@data-review-content-id]");
        
        $reviews = [];
        
        foreach ($reviewContainers as $container) {
            $review = $this->extractReviewData($container, $xpath, $appName);
            if ($review) {
                $reviews[] = $review;
            }
        }
        
        return $reviews;
    }
    
    private function extractReviewData($container, $xpath, $appName) {
        // Extract review content using the same selector as comprehensive scraper
        $contentNodes = $xpath->query(".//p[@class='tw-break-words']", $container);
        if ($contentNodes->length === 0) return null;

        $reviewContent = trim($contentNodes->item(0)->textContent);
        if (empty($reviewContent) || strlen($reviewContent) < 10) return null;

        // Extract rating using aria-label first (most reliable)
        $rating = $this->extractRatingFromContainer($container, $xpath);

        // Extract store name
        $storeName = $this->extractStoreName($container, $xpath);

        // Extract date
        $reviewDate = $this->extractDate($container, $xpath);

        if ($rating > 0 && !empty($storeName)) {
            return [
                'app_name' => $appName,
                'store_name' => $storeName,
                'country_name' => 'US', // Default
                'rating' => $rating,
                'review_content' => $reviewContent,
                'review_date' => $reviewDate ?: date('Y-m-d'),
                'earned_by' => null,
                'is_featured' => 0,
                'source_type' => 'targeted_scrape'
            ];
        }

        return null;
    }
    
    private function extractRatingFromContainer($container, $xpath) {
        // Method 1: Look for aria-label with rating (most reliable)
        $ratingNodes = $xpath->query(".//*[contains(@aria-label, 'out of') and contains(@aria-label, 'stars')]", $container);
        foreach ($ratingNodes as $node) {
            $ariaLabel = $node->getAttribute('aria-label');
            if (preg_match('/(\d+)\s*out\s*of\s*\d+\s*stars/', $ariaLabel, $matches)) {
                return intval($matches[1]);
            }
        }

        // Method 2: Count filled stars (backup method)
        $filledStars = $xpath->query(".//svg[contains(@class, 'tw-fill-fg-primary')]", $container);
        if ($filledStars->length > 0 && $filledStars->length <= 5) {
            return $filledStars->length;
        }

        // Method 3: Alternative star classes
        $starVariations = [
            ".//svg[contains(@class, 'filled')]",
            ".//span[contains(@class, 'star') and contains(@class, 'filled')]"
        ];

        foreach ($starVariations as $selector) {
            $stars = $xpath->query($selector, $container);
            if ($stars->length > 0 && $stars->length <= 5) {
                return $stars->length;
            }
        }

        return 0;
    }

    private function extractStoreName($container, $xpath) {
        // Look for store name in various possible locations
        $storeSelectors = [
            ".//div[contains(@class, 'tw-text-heading-xs') and contains(@class, 'tw-text-fg-primary')]",
            ".//h3[contains(@class, 'tw-text-heading-xs')]",
            ".//div[contains(@class, 'tw-font-semibold')]"
        ];

        foreach ($storeSelectors as $selector) {
            $nodes = $xpath->query($selector, $container);
            if ($nodes->length > 0) {
                $storeName = trim($nodes->item(0)->textContent);
                if (!empty($storeName) && strlen($storeName) > 2) {
                    return $storeName;
                }
            }
        }

        return 'Unknown Store';
    }

    private function extractDate($container, $xpath) {
        $dateSelectors = [
            ".//div[contains(@class, 'tw-text-body-xs') and contains(@class, 'tw-text-fg-tertiary')]",
            ".//time",
            ".//span[contains(@class, 'date')]"
        ];

        foreach ($dateSelectors as $selector) {
            $nodes = $xpath->query($selector, $container);
            foreach ($nodes as $node) {
                $dateText = trim($node->textContent);
                if (preg_match('/(\w+)\s+(\d{1,2}),\s+(\d{4})/', $dateText, $matches)) {
                    return date('Y-m-d', strtotime($dateText));
                }
            }
        }

        return date('Y-m-d'); // Default to today
    }
    
    private function saveReviews($reviews, $appName) {
        echo "💾 Saving {$appName} reviews to database...\n";
        
        $saved = 0;
        foreach ($reviews as $review) {
            try {
                $this->reviewRepo->addReview(
                    $review['app_name'],
                    $review['store_name'],
                    $review['country_name'],
                    $review['rating'],
                    $review['review_content'],
                    $review['review_date'],
                    $review['source_type']
                );
                $saved++;
            } catch (Exception $e) {
                echo "❌ Error saving review: " . $e->getMessage() . "\n";
            }
        }
        
        echo "✅ Saved {$saved} reviews to database\n";
        
        // Also copy to main reviews table
        $this->copyToMainTable($appName);
        
        // Update access_reviews for recent reviews
        $this->updateAccessReviews($appName);
    }
    
    private function copyToMainTable($appName) {
        $conn = $this->dbManager->getConnection();
        $stmt = $conn->prepare('INSERT INTO reviews (app_name, store_name, country_name, rating, review_content, review_date, earned_by, is_featured) SELECT app_name, store_name, country_name, rating, review_content, review_date, earned_by, is_featured FROM review_repository WHERE app_name = ?');
        $stmt->execute([$appName]);
        echo "✅ Copied to main reviews table\n";
    }
    
    private function updateAccessReviews($appName) {
        $conn = $this->dbManager->getConnection();
        $stmt = $conn->prepare('INSERT INTO access_reviews (app_name, country_name, rating, review_content, review_date, earned_by, original_review_id) SELECT app_name, country_name, rating, review_content, review_date, earned_by, id FROM reviews WHERE app_name = ? AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)');
        $stmt->execute([$appName]);
        echo "✅ Updated access_reviews table\n";
    }
    
    private function calculateRatingDistribution($reviews) {
        $distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        
        foreach ($reviews as $review) {
            $rating = $review['rating'];
            if (isset($distribution[$rating])) {
                $distribution[$rating]++;
            }
        }
        
        return $distribution;
    }
    
    private function showFinalSummary($results) {
        echo "\n" . str_repeat("=", 70) . "\n";
        echo "🎉 EXACT COUNT SCRAPING COMPLETED!\n";
        echo str_repeat("=", 70) . "\n";
        
        $grandTotal = 0;
        
        foreach ($results as $appName => $result) {
            $status = ($result['total_reviews'] == $result['target_count']) ? '✅' : '❌';
            echo "\n📱 {$appName}: {$status}\n";
            echo "   Actual Reviews: {$result['total_reviews']}\n";
            echo "   Target Reviews: {$result['target_count']}\n";
            echo "   Pages Scraped: {$result['pages_scraped']}\n";
            echo "   Rating Distribution:\n";
            
            foreach ($result['rating_distribution'] as $rating => $count) {
                if ($count > 0) {
                    echo "     {$rating}★: {$count} reviews\n";
                }
            }
            
            $grandTotal += $result['total_reviews'];
        }
        
        echo "\n🎯 TOTAL: {$grandTotal} reviews from corrected apps\n";
        echo str_repeat("=", 70) . "\n";
    }
}

// Run the scraper
$scraper = new ExactCountScraper();
$scraper->scrapeAllAppsExact();
