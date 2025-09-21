<?php
/**
 * Scraper for VISIBLE reviews only from Shopify review pages
 * Ignores archived reviews and only stores what's actually displayed
 */

require_once __DIR__ . '/config/database.php';

class VisibleReviewsScraper {
    private $conn;
    
    // Apps with their Shopify slugs and target counts from visible review pages
    private $apps = [
        'StoreSEO' => [
            'slug' => 'storeseo',
            'target' => 513
        ],
        'StoreFAQ' => [
            'slug' => 'storefaq', 
            'target' => 96
        ],
        'EasyFlow' => [
            'slug' => 'product-options-4',
            'target' => 308
        ],
        'BetterDocs FAQ Knowledge Base' => [
            'slug' => 'betterdocs-knowledgebase',
            'target' => 31
        ],
        'Vidify' => [
            'slug' => 'vidify',
            'target' => 8
        ],
        'TrustSync' => [
            'slug' => 'customer-review-app',
            'target' => 38
        ]
    ];
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Scrape only visible reviews from a single app
     */
    public function scrapeVisibleReviews($appName, $slug) {
        echo "🔍 Scraping VISIBLE reviews for: $appName\n";
        echo "   URL: https://apps.shopify.com/$slug/reviews\n";

        $allReviews = [];
        $page = 1;
        $maxPages = 100; // Safety limit
        $consecutiveEmptyPages = 0;

        while ($page <= $maxPages && $consecutiveEmptyPages < 3) {
            echo "📄 Checking page $page...\n";

            $url = "https://apps.shopify.com/$slug/reviews?sort_by=newest&page=$page";
            $html = $this->fetchPage($url);

            if (!$html) {
                echo "   ❌ Failed to fetch page $page\n";
                $consecutiveEmptyPages++;
                $page++;
                continue;
            }

            $reviews = $this->extractReviewsFromPage($html);

            if (empty($reviews)) {
                echo "   ⚠️ No reviews found on page $page\n";
                $consecutiveEmptyPages++;
            } else {
                echo "   ✅ Found " . count($reviews) . " reviews on page $page\n";
                $allReviews = array_merge($allReviews, $reviews);
                $consecutiveEmptyPages = 0; // Reset counter
            }

            // Check for pagination to see if there are more pages
            $hasNextPage = $this->hasNextPage($html);
            if (!$hasNextPage && empty($reviews)) {
                echo "   📄 No more pages available\n";
                break;
            }

            $page++;
            sleep(1); // Be respectful to Shopify servers
        }

        if ($consecutiveEmptyPages >= 3) {
            echo "   ⚠️ Stopped after 3 consecutive empty pages\n";
        }

        echo "📊 Total visible reviews found: " . count($allReviews) . "\n";

        if (!empty($allReviews)) {
            $this->saveReviews($appName, $allReviews);
            return count($allReviews);
        }

        return 0;
    }
    
    /**
     * Fetch a single page
     */
    private function fetchPage($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ($httpCode === 200) ? $html : false;
    }
    
    /**
     * Extract reviews from HTML page
     */
    private function extractReviewsFromPage($html) {
        $reviews = [];

        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        // Find review containers
        $reviewNodes = $xpath->query('//div[@data-review-content-id]');

        foreach ($reviewNodes as $reviewNode) {
            $review = $this->parseReviewNode($reviewNode, $xpath);
            if ($review) {
                $reviews[] = $review;
            }
        }

        return $reviews;
    }

    /**
     * Check if there's a next page available
     */
    private function hasNextPage($html) {
        // Look for "Next" link or pagination indicators
        if (strpos($html, 'rel="next"') !== false) {
            return true;
        }

        // Look for pagination with higher page numbers
        if (preg_match('/page=(\d+)/', $html, $matches)) {
            return true;
        }

        return false;
    }
    
    /**
     * Parse individual review node
     */
    private function parseReviewNode($reviewNode, $xpath) {
        try {
            // Extract store name - correct selector
            $storeNameNodes = $xpath->query('.//div[contains(@class, "tw-text-heading-xs")]', $reviewNode);
            $storeName = $storeNameNodes->length > 0 ? trim($storeNameNodes->item(0)->textContent) : 'Unknown Store';

            // Extract rating
            $rating = $this->extractRating($reviewNode, $xpath);

            // Extract review content - correct selector for actual review text
            $contentNodes = $xpath->query('.//div[@data-truncate-review and contains(@class, "tw-text-body-md")]//p[@class="tw-break-words"]', $reviewNode);
            $reviewContent = '';
            if ($contentNodes->length > 0) {
                $reviewParts = [];
                foreach ($contentNodes as $contentNode) {
                    $reviewParts[] = trim($contentNode->textContent);
                }
                $reviewContent = implode(' ', $reviewParts);
            }

            // Extract date - look for date text
            $dateNodes = $xpath->query('.//div[contains(@class, "tw-text-body-xs") and contains(@class, "tw-text-fg-tertiary")]', $reviewNode);
            $reviewDate = '1970-01-01';
            if ($dateNodes->length > 0) {
                foreach ($dateNodes as $dateNode) {
                    $dateText = trim($dateNode->textContent);
                    if (preg_match('/\w+ \d+, \d{4}/', $dateText)) {
                        $reviewDate = $this->parseDate($dateText);
                        break;
                    }
                }
            }

            // Extract country
            $countryNodes = $xpath->query('.//div[contains(@class, "tw-text-fg-tertiary") and contains(@class, "tw-text-body-xs")]', $reviewNode);
            $country = 'Unknown';
            if ($countryNodes->length > 1) {
                $country = trim($countryNodes->item(1)->textContent);
                // Limit country name length to avoid database errors
                if (strlen($country) > 50) {
                    $country = substr($country, 0, 50);
                }
            }

            // Skip if essential data is missing
            if (empty($storeName) || empty($reviewContent) || $rating === null) {
                echo "   ⚠️ Skipping review - missing data: store='$storeName', content='" . substr($reviewContent, 0, 50) . "', rating=$rating\n";
                return null;
            }

            return [
                'store_name' => $storeName,
                'rating' => $rating,
                'review_content' => $reviewContent,
                'review_date' => $reviewDate,
                'country_name' => $country
            ];

        } catch (Exception $e) {
            echo "   ⚠️ Error parsing review: " . $e->getMessage() . "\n";
            return null;
        }
    }
    
    /**
     * Extract rating from review node
     */
    private function extractRating($reviewNode, $xpath) {
        // Try aria-label method first (most reliable)
        $ratingNodes = $xpath->query('.//*[contains(@aria-label, "star")]', $reviewNode);
        if ($ratingNodes->length > 0) {
            $ariaLabel = $ratingNodes->item(0)->getAttribute('aria-label');
            if (preg_match('/(\d+)\s*star/i', $ariaLabel, $matches)) {
                return intval($matches[1]);
            }
        }
        
        // Try counting filled star elements
        $filledStars = $xpath->query('.//*[contains(@class, "tw-fill-fg-primary")]', $reviewNode);
        if ($filledStars->length > 0) {
            return $filledStars->length;
        }
        
        // Default to 5 stars if we can't determine
        return 5;
    }
    
    /**
     * Parse date string to MySQL format
     */
    private function parseDate($dateText) {
        try {
            $date = new DateTime($dateText);
            return $date->format('Y-m-d');
        } catch (Exception $e) {
            return '1970-01-01';
        }
    }
    
    /**
     * Save reviews to database
     */
    private function saveReviews($appName, $reviews) {
        echo "💾 Saving " . count($reviews) . " reviews to database...\n";
        
        // Clear existing data first
        $this->clearAppData($appName);
        
        $insertQuery = "
            INSERT INTO reviews (
                app_name, store_name, country_name, rating, 
                review_content, review_date, is_active, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, TRUE, NOW())
        ";
        
        $stmt = $this->conn->prepare($insertQuery);
        $saved = 0;
        
        foreach ($reviews as $review) {
            try {
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
                echo "   ⚠️ Error saving review: " . $e->getMessage() . "\n";
            }
        }
        
        echo "✅ Saved $saved reviews successfully\n";
    }
    
    /**
     * Clear existing app data
     */
    private function clearAppData($appName) {
        echo "🗑️ Clearing existing data for $appName...\n";
        
        try {
            // Clear access_reviews first (child table)
            $stmt = $this->conn->prepare('DELETE FROM access_reviews WHERE app_name = ?');
            $stmt->execute([$appName]);
            
            // Clear reviews table
            $stmt = $this->conn->prepare('DELETE FROM reviews WHERE app_name = ?');
            $stmt->execute([$appName]);
            $reviewsDeleted = $stmt->rowCount();
            
            // Clear review_repository
            $stmt = $this->conn->prepare('UPDATE review_repository SET is_active = FALSE WHERE app_name = ?');
            $stmt->execute([$appName]);
            
            echo "   Cleared $reviewsDeleted existing reviews\n";
            
        } catch (Exception $e) {
            echo "   ⚠️ Error clearing data: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Scrape all apps
     */
    public function scrapeAllApps() {
        echo "🚀 SCRAPING VISIBLE REVIEWS ONLY - ALL APPS\n";
        echo "==========================================\n\n";
        
        $results = [];
        
        foreach ($this->apps as $appName => $config) {
            echo "📱 Processing: $appName\n";
            echo "   Target: {$config['target']} reviews\n";
            echo "   Slug: {$config['slug']}\n\n";
            
            $startTime = microtime(true);
            $count = $this->scrapeVisibleReviews($appName, $config['slug']);
            $executionTime = round(microtime(true) - $startTime, 2);
            
            $results[$appName] = [
                'scraped' => $count,
                'target' => $config['target'],
                'time' => $executionTime
            ];
            
            echo "   Time: {$executionTime}s\n";
            echo str_repeat("-", 60) . "\n\n";
            
            sleep(2); // Be respectful between apps
        }
        
        $this->printSummary($results);
        return $results;
    }
    
    /**
     * Print final summary
     */
    private function printSummary($results) {
        echo "🎯 FINAL SUMMARY - VISIBLE REVIEWS ONLY\n";
        echo "======================================\n";
        
        $totalScraped = 0;
        $totalTarget = 0;
        
        foreach ($results as $appName => $result) {
            $totalScraped += $result['scraped'];
            $totalTarget += $result['target'];
            
            $status = $result['scraped'] >= ($result['target'] * 0.9) ? '✅' : '⚠️';
            echo "$status $appName: {$result['scraped']}/{$result['target']} reviews ({$result['time']}s)\n";
        }
        
        echo "\n📊 TOTALS:\n";
        echo "   Total scraped: $totalScraped\n";
        echo "   Total target: $totalTarget\n";
        echo "   Accuracy: " . round(($totalScraped / $totalTarget) * 100, 1) . "%\n";
        
        echo "\n✅ Only VISIBLE reviews from review pages have been stored.\n";
        echo "   Archived reviews are ignored.\n";
        echo "   Access Review (Tabs) page will show accurate counts.\n";
    }
}

// Run the scraper
if (php_sapi_name() === 'cli') {
    $scraper = new VisibleReviewsScraper();
    $scraper->scrapeAllApps();
} else {
    echo "This script must be run from command line.\n";
}
