<?php
require_once 'utils/DatabaseManager.php';
require_once 'utils/ReviewRepository.php';

class AllAppsComprehensiveScraper {
    private $dbManager;
    private $reviewRepo;
    private $apps;
    
    public function __construct() {
        $this->dbManager = new DatabaseManager();
        $this->reviewRepo = new ReviewRepository();
        
        // Define all apps with their Shopify URLs (excluding StoreSEO since it's already done)
        $this->apps = [
            'StoreFAQ' => 'storefaq',
            'EasyFlow' => 'easyflow',
            'TrustSync' => 'trustsync-google-reviews',
            'Vitals' => 'vitals',
            'BetterDocs FAQ' => 'betterdocs-faq'
        ];
    }
    
    public function scrapeAllApps() {
        echo "🚀 COMPREHENSIVE ALL APPS SCRAPER\n";
        echo "=================================\n";
        echo "Apps to scrape: " . implode(', ', array_keys($this->apps)) . "\n\n";
        
        $totalResults = [];
        
        foreach ($this->apps as $appName => $appSlug) {
            echo "📱 Starting {$appName}...\n";
            echo str_repeat("-", 50) . "\n";
            
            // Clean existing data first
            $this->cleanAppData($appName);
            
            // Scrape the app
            $result = $this->scrapeApp($appName, $appSlug);
            $totalResults[$appName] = $result;
            
            echo "✅ {$appName} completed: {$result['total_reviews']} reviews\n\n";
            
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
    
    private function scrapeApp($appName, $appSlug) {
        $baseUrl = "https://apps.shopify.com/{$appSlug}/reviews";
        $allReviews = [];
        $page = 1;
        $maxPages = 100; // High limit to ensure we get all reviews
        
        while ($page <= $maxPages) {
            echo "📄 Scraping {$appName} page {$page}...\n";
            
            $url = "{$baseUrl}?page={$page}&sort_by=newest";
            $reviews = $this->scrapePage($url, $appName);
            
            // Handle rate limiting with retries
            if (empty($reviews) && $page <= 80) {
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
            echo "📊 Total so far: " . count($allReviews) . " reviews\n";
            
            $page++;
            // Delay between requests to avoid rate limiting
            usleep(1000000); // 1 second delay
        }
        
        echo "✅ {$appName} scraping completed!\n";
        echo "📊 Total reviews collected: " . count($allReviews) . "\n";
        
        // Save to database
        $this->saveReviews($allReviews, $appName);
        
        return [
            'total_reviews' => count($allReviews),
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
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $contentLength = strlen($html);
        
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
                'source_type' => 'comprehensive_scrape'
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
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "🎉 ALL APPS COMPREHENSIVE SCRAPING COMPLETED!\n";
        echo str_repeat("=", 60) . "\n";
        
        $grandTotal = 0;
        
        foreach ($results as $appName => $result) {
            echo "\n📱 {$appName}:\n";
            echo "   Total Reviews: {$result['total_reviews']}\n";
            echo "   Pages Scraped: {$result['pages_scraped']}\n";
            echo "   Rating Distribution:\n";
            
            foreach ($result['rating_distribution'] as $rating => $count) {
                if ($count > 0) {
                    echo "     {$rating}★: {$count} reviews\n";
                }
            }
            
            $grandTotal += $result['total_reviews'];
        }
        
        echo "\n🎯 GRAND TOTAL: {$grandTotal} reviews across all apps\n";
        echo str_repeat("=", 60) . "\n";
    }
}

// Run the scraper
$scraper = new AllAppsComprehensiveScraper();
$scraper->scrapeAllApps();
