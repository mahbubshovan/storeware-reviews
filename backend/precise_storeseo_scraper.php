<?php
require_once __DIR__ . '/utils/DatabaseManager.php';

/**
 * Precise StoreSEO scraper with duplicate prevention
 */
class PreciseStoreSEOScraper {
    private $dbManager;
    private $baseUrl = 'https://apps.shopify.com/storeseo/reviews';
    private $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
    private $seenReviews = []; // Track to prevent duplicates
    
    public function __construct() {
        echo "=== PRECISE STORESEO SCRAPER ===\n";
        echo "Current date: " . date('Y-m-d H:i:s') . "\n";
        echo "This month start: " . date('Y-m-01') . "\n";
        echo "30 days ago: " . date('Y-m-d', strtotime('-30 days')) . "\n\n";
        
        $this->dbManager = new DatabaseManager();
    }
    
    /**
     * Scrape with precise control
     */
    public function scrapeWithPrecision() {
        echo "Starting precise scraping...\n";
        
        $allReviews = [];
        $maxPages = 5; // Limit to fewer pages for precision
        
        for ($page = 1; $page <= $maxPages; $page++) {
            echo "\n--- PAGE $page ---\n";
            
            $url = $this->baseUrl . "?sort_by=newest&page=$page";
            $html = $this->fetchPage($url);
            
            if (empty($html)) {
                echo "❌ Failed to fetch page $page\n";
                break;
            }
            
            $reviews = $this->parseReviews($html, $page);
            
            if (empty($reviews)) {
                echo "❌ No reviews on page $page\n";
                break;
            }
            
            echo "✅ Found " . count($reviews) . " reviews on page $page\n";
            
            // Add unique reviews only
            foreach ($reviews as $review) {
                $key = $review['store_name'] . '|' . $review['review_date'];
                if (!isset($this->seenReviews[$key])) {
                    $this->seenReviews[$key] = true;
                    $allReviews[] = $review;
                    echo "   + {$review['store_name']} - {$review['review_date']}\n";
                } else {
                    echo "   - Duplicate: {$review['store_name']} - {$review['review_date']}\n";
                }
            }
            
            sleep(2); // Respectful delay
        }
        
        echo "\n=== SCRAPING RESULTS ===\n";
        echo "Total unique reviews: " . count($allReviews) . "\n";
        
        if (!empty($allReviews)) {
            $this->storeReviews($allReviews);
            $this->verifyResults();
        }
        
        return $allReviews;
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
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING => '',
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Connection: keep-alive',
                'Cache-Control: no-cache'
            ]
        ]);
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return false;
        }
        
        return $html;
    }
    
    /**
     * Parse reviews from HTML
     */
    private function parseReviews($html, $page) {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $reviewNodes = $xpath->query("//div[@data-review-content-id]");

        echo "Found " . ($reviewNodes ? $reviewNodes->length : 0) . " review nodes\n";

        if (!$reviewNodes || $reviewNodes->length === 0) {
            return [];
        }

        $reviews = [];
        foreach ($reviewNodes as $index => $node) {
            echo "Processing review " . ($index + 1) . "...\n";
            $review = $this->extractReview($node, $xpath, $index);
            if ($review) {
                $reviews[] = $review;
                echo "✅ Extracted: {$review['store_name']} - {$review['review_date']}\n";
            } else {
                echo "❌ Failed to extract review " . ($index + 1) . "\n";
            }
        }

        return $reviews;
    }
    
    /**
     * Extract review from node
     */
    private function extractReview($node, $xpath, $index) {
        try {
            // Debug: Show node HTML
            $nodeHtml = $node->ownerDocument->saveHTML($node);
            echo "   Node HTML (first 200 chars): " . substr(strip_tags($nodeHtml), 0, 200) . "...\n";

            // Extract store name - try multiple selectors
            $storeName = '';
            $storeSelectors = [
                ".//div[contains(@class, 'heading')]",
                ".//h3",
                ".//h4",
                ".//div[contains(@class, 'merchant')]",
                ".//strong"
            ];

            foreach ($storeSelectors as $selector) {
                $storeNodes = $xpath->query($selector, $node);
                if ($storeNodes->length > 0) {
                    $storeName = trim($storeNodes->item(0)->textContent);
                    if (!empty($storeName)) {
                        echo "   Store name found with '$selector': $storeName\n";
                        break;
                    }
                }
            }

            // Extract review content - try multiple selectors
            $content = '';
            $contentSelectors = [
                ".//p[contains(@class, 'break-words')]",
                ".//p",
                ".//div[contains(@class, 'review-content')]",
                ".//div[contains(@class, 'content')]"
            ];

            foreach ($contentSelectors as $selector) {
                $contentNodes = $xpath->query($selector, $node);
                if ($contentNodes->length > 0) {
                    $content = trim($contentNodes->item(0)->textContent);
                    if (strlen($content) > 10) {
                        echo "   Content found with '$selector': " . substr($content, 0, 50) . "...\n";
                        break;
                    }
                }
            }

            // Extract date - try multiple approaches
            $date = '';

            // First try time element with datetime
            $dateNodes = $xpath->query(".//time[@datetime]", $node);
            if ($dateNodes->length > 0) {
                $datetime = $dateNodes->item(0)->getAttribute('datetime');
                $date = $this->parseDate($datetime);
                echo "   Date found from time[@datetime]: $datetime -> $date\n";
            } else {
                // Try to find date in text content
                $nodeText = $node->textContent;
                if (preg_match('/\b(January|February|March|April|May|June|July|August|September|October|November|December)\s+\d{1,2},\s+\d{4}\b/', $nodeText, $matches)) {
                    $dateStr = $matches[0];
                    $date = $this->parseDate($dateStr);
                    echo "   Date found from text: $dateStr -> $date\n";
                } else {
                    echo "   No date found in text: " . substr($nodeText, 0, 100) . "...\n";
                }
            }

            // Validate
            if (empty($storeName)) {
                echo "   ❌ Missing store name\n";
                return null;
            }
            if (empty($content)) {
                echo "   ❌ Missing content\n";
                return null;
            }
            if (empty($date)) {
                echo "   ❌ Missing date\n";
                return null;
            }

            if (strlen($content) < 10) {
                echo "   ❌ Content too short\n";
                return null;
            }

            return [
                'app_name' => 'StoreSEO',
                'store_name' => substr($storeName, 0, 255),
                'country_name' => $this->guessCountry($storeName),
                'rating' => 5,
                'review_content' => substr($content, 0, 65535),
                'review_date' => $date
            ];

        } catch (Exception $e) {
            echo "   ❌ Exception: " . $e->getMessage() . "\n";
            return null;
        }
    }
    
    /**
     * Parse date from datetime string
     */
    private function parseDate($datetime) {
        try {
            // Handle various date formats
            $datetime = trim($datetime);

            // Try direct parsing first
            $date = new DateTime($datetime);
            return $date->format('Y-m-d');
        } catch (Exception $e) {
            try {
                // Try parsing common formats
                $formats = [
                    'F j, Y',     // August 4, 2025
                    'M j, Y',     // Aug 4, 2025
                    'Y-m-d',      // 2025-08-04
                    'd/m/Y',      // 04/08/2025
                    'm/d/Y'       // 08/04/2025
                ];

                foreach ($formats as $format) {
                    $date = DateTime::createFromFormat($format, $datetime);
                    if ($date !== false) {
                        return $date->format('Y-m-d');
                    }
                }
            } catch (Exception $e2) {
                // Ignore
            }

            return date('Y-m-d'); // Fallback to today
        }
    }
    
    /**
     * Guess country from store name
     */
    private function guessCountry($storeName) {
        $patterns = [
            '/\b(UK|Britain|British)\b/i' => 'United Kingdom',
            '/\b(Canada|Canadian)\b/i' => 'Canada',
            '/\b(Australia|Australian|AU)\b/i' => 'Australia',
            '/\b(Germany|German|DE)\b/i' => 'Germany',
            '/\b(France|French|FR)\b/i' => 'France',
            '/\b(India|Indian|IN)\b/i' => 'India',
        ];
        
        foreach ($patterns as $pattern => $country) {
            if (preg_match($pattern, $storeName)) {
                return $country;
            }
        }
        
        return 'United States';
    }
    
    /**
     * Store reviews in database
     */
    private function storeReviews($reviews) {
        try {
            $conn = $this->dbManager->getConnection();
            
            $stmt = $conn->prepare("
                INSERT INTO reviews (app_name, store_name, country_name, rating, review_content, review_date, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stored = 0;
            foreach ($reviews as $review) {
                $success = $stmt->execute([
                    $review['app_name'],
                    $review['store_name'],
                    $review['country_name'],
                    $review['rating'],
                    $review['review_content'],
                    $review['review_date']
                ]);
                
                if ($success) {
                    $stored++;
                }
            }
            
            echo "\n✅ Stored $stored reviews in database\n";
            
            // Sync to access_reviews
            $stmt = $conn->prepare("
                INSERT INTO access_reviews (app_name, review_date, review_content, country_name, original_review_id)
                SELECT app_name, review_date, review_content, country_name, id
                FROM reviews 
                WHERE app_name = 'StoreSEO'
                AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            ");
            
            $stmt->execute();
            $synced = $stmt->rowCount();
            echo "✅ Synced $synced reviews to access_reviews\n";
            
        } catch (Exception $e) {
            echo "❌ Error storing reviews: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Verify the final results
     */
    private function verifyResults() {
        try {
            $conn = $this->dbManager->getConnection();
            
            echo "\n=== VERIFICATION ===\n";
            
            // This month
            $thisMonth = date('Y-m-01');
            $stmt = $conn->prepare('SELECT COUNT(*) FROM reviews WHERE app_name = "StoreSEO" AND review_date >= ?');
            $stmt->execute([$thisMonth]);
            $thisMonthCount = $stmt->fetchColumn();
            
            // Last 30 days
            $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
            $stmt = $conn->prepare('SELECT COUNT(*) FROM reviews WHERE app_name = "StoreSEO" AND review_date >= ?');
            $stmt->execute([$thirtyDaysAgo]);
            $last30DaysCount = $stmt->fetchColumn();
            
            echo "📊 This month: $thisMonthCount reviews\n";
            echo "📊 Last 30 days: $last30DaysCount reviews\n";
            
            if ($thisMonthCount <= $last30DaysCount) {
                echo "✅ Logic is correct!\n";
            } else {
                echo "❌ Logic error!\n";
            }
            
            // Show breakdown
            $stmt = $conn->prepare('
                SELECT review_date, COUNT(*) as count 
                FROM reviews 
                WHERE app_name = "StoreSEO" 
                GROUP BY review_date 
                ORDER BY review_date DESC 
                LIMIT 10
            ');
            $stmt->execute();
            $dates = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "\n📅 Date breakdown:\n";
            foreach ($dates as $date) {
                echo "   {$date['review_date']}: {$date['count']} reviews\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Error verifying: " . $e->getMessage() . "\n";
        }
    }
}

// Run if called directly
if (basename(__FILE__) == basename($_SERVER["SCRIPT_NAME"])) {
    $scraper = new PreciseStoreSEOScraper();
    $scraper->scrapeWithPrecision();
}
?>
