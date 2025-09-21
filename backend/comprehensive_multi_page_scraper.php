<?php
require_once 'utils/DatabaseManager.php';
require_once 'utils/ReviewRepository.php';

/**
 * Comprehensive Multi-Page Scraper for All Shopify Apps
 * Scrapes ALL pages of reviews for each app to ensure complete data accuracy
 */
class ComprehensiveMultiPageScraper {
    private $dbManager;
    private $reviewRepo;
    
    // All apps with their Shopify slugs
    private $apps = [
        'StoreSEO' => 'storeseo',
        'StoreFAQ' => 'storefaq',
        'BetterDocs FAQ' => 'betterdocs-knowledgebase',
        'EasyFlow' => 'product-options-4',
        'TrustSync' => 'trustsync',
        'Vidify' => 'vidify'
    ];
    
    private $totalReviewsScraped = 0;
    private $appResults = [];
    
    public function __construct() {
        $this->dbManager = new DatabaseManager();
        $this->reviewRepo = new ReviewRepository();
    }
    
    /**
     * Scrape all apps with complete pagination
     */
    public function scrapeAllAppsComplete() {
        echo "🚀 COMPREHENSIVE MULTI-PAGE SCRAPER STARTING\n";
        echo "===========================================\n";
        echo "Target: ALL pages of ALL apps for complete accuracy\n\n";
        
        foreach ($this->apps as $appName => $appSlug) {
            echo "🎯 SCRAPING: $appName ($appSlug)\n";
            echo str_repeat("=", 50) . "\n";
            
            $appResult = $this->scrapeAppAllPages($appName, $appSlug);
            $this->appResults[$appName] = $appResult;
            
            echo "✅ $appName completed: {$appResult['total_reviews']} reviews scraped\n\n";
            
            // Small delay between apps to be respectful
            sleep(2);
        }
        
        $this->showFinalSummary();
    }
    
    /**
     * Scrape all pages for a single app
     */
    public function scrapeAppAllPages($appName, $appSlug) {
        $allReviews = [];
        $page = 1;
        $maxPages = 50; // Safety limit
        
        while ($page <= $maxPages) {
            echo "📄 Scraping page $page...\n";
            
            $url = "https://apps.shopify.com/$appSlug/reviews?page=$page&sort_by=newest";
            $html = $this->fetchPage($url);
            
            if (!$html) {
                echo "❌ Failed to fetch page $page\n";
                break;
            }
            
            // Save HTML for debugging
            file_put_contents("debug_{$appSlug}_page_{$page}.html", $html);
            
            $pageReviews = $this->parseReviewsFromHTML($html);
            
            if (empty($pageReviews)) {
                echo "📝 No reviews found on page $page - reached end\n";
                break;
            }
            
            echo "📝 Found " . count($pageReviews) . " reviews on page $page\n";
            $allReviews = array_merge($allReviews, $pageReviews);
            
            // Check if there's a next page
            if (!$this->hasNextPage($html)) {
                echo "📝 No more pages available\n";
                break;
            }
            
            $page++;
            
            // Small delay between pages
            sleep(1);
        }
        
        // Save all reviews to database
        $savedCount = $this->saveReviewsToDatabase($appName, $allReviews);
        
        return [
            'total_reviews' => count($allReviews),
            'pages_scraped' => $page - 1,
            'saved_to_db' => $savedCount,
            'rating_distribution' => $this->calculateRatingDistribution($allReviews)
        ];
    }
    
    /**
     * Fetch page content with proper headers
     */
    private function fetchPage($url) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING => '', // This handles gzip/deflate automatically
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1',
                'Cache-Control: no-cache',
                'Pragma: no-cache'
            ]
        ]);

        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($httpCode !== 200) {
            echo "❌ HTTP Error: $httpCode for $url\n";
            return false;
        }

        echo "📡 Fetched " . strlen($html) . " bytes, Content-Type: $contentType\n";

        return $html;
    }
    
    /**
     * Parse reviews from HTML page using data-review-content-id containers
     */
    private function parseReviewsFromHTML($html) {
        $reviews = [];

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        // Look for review containers with data-review-content-id
        $reviewContainers = $xpath->query('//div[@data-review-content-id]');
        echo "📝 Found " . $reviewContainers->length . " review containers\n";

        foreach ($reviewContainers as $container) {
            // Extract review content
            $contentNodes = $xpath->query(".//p[@class='tw-break-words']", $container);
            if ($contentNodes->length === 0) continue;

            $content = trim($contentNodes->item(0)->textContent);
            if (empty($content) || strlen($content) < 10) continue;

            // Extract rating using aria-label first (most reliable)
            $rating = $this->extractRatingFromContainer($container, $xpath);

            // Extract store name
            $storeName = $this->extractStoreName($container, $xpath);

            // Extract date
            $reviewDate = $this->extractDate($container, $xpath);

            if ($rating > 0 && !empty($storeName)) {
                $reviews[] = [
                    'store_name' => $storeName,
                    'rating' => $rating,
                    'review_content' => $content,
                    'review_date' => $reviewDate ?: date('Y-m-d'),
                    'country_name' => 'US' // Default, will be extracted if available
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
        // Walk up the DOM tree to find the review container
        $current = $contentNode;
        $maxLevels = 10;
        $level = 0;

        while ($current && $level < $maxLevels) {
            $current = $current->parentNode;
            $level++;

            if ($current && $current->nodeType === XML_ELEMENT_NODE) {
                // Look for containers that likely hold review data
                $class = $current->getAttribute('class');
                if (strpos($class, 'review') !== false ||
                    strpos($class, 'tw-border') !== false ||
                    strpos($class, 'tw-p-') !== false) {

                    // Check if this container has star ratings
                    $stars = $xpath->query(".//svg[contains(@class, 'tw-fill-fg-primary')]", $current);
                    if ($stars->length > 0) {
                        return $current;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Extract rating from review container
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

    /**
     * Extract store name from review container
     */
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

    /**
     * Extract date from review container
     */
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
    

    
    /**
     * Check if there's a next page
     */
    private function hasNextPage($html) {
        return strpos($html, 'Next page') !== false ||
               strpos($html, 'next-page') !== false ||
               preg_match('/page=\d+/', $html);
    }

    /**
     * Save reviews to database
     */
    private function saveReviewsToDatabase($appName, $reviews) {
        $savedCount = 0;

        foreach ($reviews as $review) {
            try {
                // Insert into both tables for compatibility
                $result1 = $this->dbManager->insertReview(
                    $appName,
                    $review['store_name'],
                    $review['country_name'],
                    $review['rating'],
                    $review['review_content'],
                    $review['review_date']
                );

                $result2 = $this->reviewRepo->addReview(
                    $appName,
                    $review['store_name'],
                    $review['country_name'],
                    $review['rating'],
                    $review['review_content'],
                    $review['review_date'],
                    'comprehensive_scrape'
                );

                if ($result1 || $result2) {
                    $savedCount++;
                }

            } catch (Exception $e) {
                echo "⚠️ Error saving review: " . $e->getMessage() . "\n";
            }
        }

        return $savedCount;
    }

    /**
     * Calculate rating distribution
     */
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

    /**
     * Show final summary
     */
    private function showFinalSummary() {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "🎉 COMPREHENSIVE SCRAPING COMPLETED!\n";
        echo str_repeat("=", 60) . "\n";

        $totalReviews = 0;

        foreach ($this->appResults as $appName => $result) {
            echo "\n📱 $appName:\n";
            echo "   Total Reviews: {$result['total_reviews']}\n";
            echo "   Pages Scraped: {$result['pages_scraped']}\n";
            echo "   Saved to DB: {$result['saved_to_db']}\n";
            echo "   Rating Distribution:\n";

            foreach ($result['rating_distribution'] as $rating => $count) {
                if ($count > 0) {
                    echo "     {$rating}★: $count reviews\n";
                }
            }

            $totalReviews += $result['total_reviews'];
        }

        echo "\n📊 OVERALL SUMMARY:\n";
        echo "   Total Reviews Scraped: $totalReviews\n";
        echo "   Apps Processed: " . count($this->appResults) . "\n";

        // Verify database counts
        echo "\n🔍 DATABASE VERIFICATION:\n";
        $this->verifyDatabaseCounts();
    }

    /**
     * Verify database counts match scraped data
     */
    private function verifyDatabaseCounts() {
        $conn = $this->dbManager->getConnection();

        foreach (array_keys($this->apps) as $appName) {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM reviews WHERE app_name = ?");
            $stmt->execute([$appName]);
            $dbCount = $stmt->fetchColumn();

            $scrapedCount = isset($this->appResults[$appName]) ? $this->appResults[$appName]['total_reviews'] : 0;

            $status = ($dbCount >= $scrapedCount * 0.9) ? "✅" : "⚠️"; // Allow 10% variance
            echo "   $status $appName: $dbCount in DB (scraped: $scrapedCount)\n";
        }
    }
}

// Run the comprehensive scraper
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $scraper = new ComprehensiveMultiPageScraper();

    // Test with StoreFAQ first to verify the approach
    echo "🧪 TESTING WITH STOREFAQ FIRST\n";
    echo "==============================\n";
    $testResult = $scraper->scrapeAppAllPages('StoreFAQ', 'storefaq');
    echo "Test result: " . json_encode($testResult, JSON_PRETTY_PRINT) . "\n";

    // If test is successful, ask user to continue with all apps
    echo "\n🤔 Test completed. Run full scraper? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $response = trim(fgets($handle));
    fclose($handle);

    if (strtolower($response) === 'y' || strtolower($response) === 'yes') {
        echo "\n🚀 Running full comprehensive scraper...\n";
        $scraper->scrapeAllAppsComplete();
    } else {
        echo "Scraping stopped by user.\n";
    }
}
?>
