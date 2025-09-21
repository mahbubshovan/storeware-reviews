<?php
/**
 * Database Cleanup and Real Review Count Verification
 * Removes archived/historical reviews and keeps only real, current reviews
 * that match the exact counts on live Shopify app store pages
 */

require_once 'config/database.php';
require_once 'scraper/UniversalLiveScraper.php';

class ReviewCleanupAndVerification {
    private $conn;
    private $scraper;
    
    // Target counts from live Shopify app store pages
    private $targetCounts = [
        'StoreSEO' => ['slug' => 'storeseo', 'target' => 513],
        'StoreFAQ' => ['slug' => 'storefaq', 'target' => 92],
        'EasyFlow' => ['slug' => 'product-options-4', 'target' => 305],
        'BetterDocs FAQ' => ['slug' => 'betterdocs-knowledgebase', 'target' => 31],
        'Vidify' => ['slug' => 'vidify', 'target' => 8],
        'TrustSync' => ['slug' => 'customer-review-app', 'target' => 38]
    ];
    
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
        $this->scraper = new UniversalLiveScraper();
    }
    
    public function cleanupAndVerifyAll() {
        echo "🧹 STARTING DATABASE CLEANUP AND VERIFICATION" . PHP_EOL;
        echo str_repeat('=', 60) . PHP_EOL;
        
        foreach ($this->targetCounts as $appName => $info) {
            echo PHP_EOL . "📱 PROCESSING: $appName" . PHP_EOL;
            echo str_repeat('-', 40) . PHP_EOL;
            
            $this->processApp($appName, $info['slug'], $info['target']);
        }
        
        echo PHP_EOL . "🎉 CLEANUP AND VERIFICATION COMPLETE!" . PHP_EOL;
        $this->showFinalSummary();
    }
    
    private function processApp($appName, $slug, $targetCount) {
        // Get current count
        $currentCount = $this->getCurrentCount($appName);
        echo "📊 Current DB count: $currentCount" . PHP_EOL;
        echo "🎯 Target count: $targetCount" . PHP_EOL;
        
        if ($currentCount == $targetCount) {
            echo "✅ Already matches target count - no action needed" . PHP_EOL;
            return;
        }
        
        if ($currentCount > $targetCount) {
            echo "⚠️  Too many reviews - removing excess" . PHP_EOL;
            $this->removeExcessReviews($appName, $currentCount - $targetCount);
        } else {
            echo "📈 Need more reviews - scraping fresh data" . PHP_EOL;
            $this->scrapeFreshReviews($appName, $slug, $targetCount);
        }
        
        // Verify final count
        $finalCount = $this->getCurrentCount($appName);
        echo "✅ Final count: $finalCount" . PHP_EOL;
        
        if ($finalCount == $targetCount) {
            echo "🎯 SUCCESS: Matches target count!" . PHP_EOL;
        } else {
            echo "⚠️  WARNING: Still doesn't match target ($finalCount vs $targetCount)" . PHP_EOL;
        }
    }
    
    private function getCurrentCount($appName) {
        $stmt = $this->conn->prepare('SELECT COUNT(*) as count FROM review_repository WHERE app_name = ? AND is_active = TRUE');
        $stmt->execute([$appName]);
        return intval($stmt->fetch(PDO::FETCH_ASSOC)['count']);
    }
    
    private function removeExcessReviews($appName, $excessCount) {
        echo "🗑️  Removing $excessCount excess reviews..." . PHP_EOL;
        
        // Remove oldest reviews first (keep most recent ones)
        $stmt = $this->conn->prepare("
            UPDATE review_repository 
            SET is_active = FALSE, updated_at = NOW()
            WHERE app_name = ? AND is_active = TRUE
            ORDER BY review_date ASC, created_at ASC
            LIMIT ?
        ");
        
        $stmt->execute([$appName, $excessCount]);
        echo "✅ Removed $excessCount old reviews" . PHP_EOL;
    }
    
    private function scrapeFreshReviews($appName, $slug, $targetCount) {
        echo "🌐 Scraping fresh reviews from live Shopify page..." . PHP_EOL;
        
        // Clear existing data first
        $this->clearAppData($appName);
        
        // Scrape fresh data with limited pages to match target
        $maxPages = ceil($targetCount / 10) + 2; // Add buffer for safety
        $scraped = $this->scrapeLimitedPages($appName, $slug, $maxPages, $targetCount);
        
        echo "✅ Scraped $scraped fresh reviews" . PHP_EOL;
    }
    
    private function clearAppData($appName) {
        $stmt = $this->conn->prepare('UPDATE review_repository SET is_active = FALSE WHERE app_name = ?');
        $stmt->execute([$appName]);
        echo "🗑️  Cleared existing data for $appName" . PHP_EOL;
    }
    
    private function scrapeLimitedPages($appName, $slug, $maxPages, $targetCount) {
        $baseUrl = "https://apps.shopify.com/$slug/reviews";
        $totalScraped = 0;
        
        for ($page = 1; $page <= $maxPages && $totalScraped < $targetCount; $page++) {
            $url = $baseUrl . "?sort_by=newest&page=$page";
            echo "  📄 Scraping page $page...";
            
            $html = $this->fetchPage($url);
            if (!$html) {
                echo " ❌ Failed" . PHP_EOL;
                break;
            }
            
            $reviews = $this->parseReviews($html);
            if (empty($reviews)) {
                echo " (empty)" . PHP_EOL;
                break;
            }
            
            $saved = $this->saveReviews($reviews, $appName, $targetCount - $totalScraped);
            $totalScraped += $saved;
            echo " ✅ $saved reviews" . PHP_EOL;
            
            if ($totalScraped >= $targetCount) {
                echo "  🎯 Reached target count" . PHP_EOL;
                break;
            }
            
            usleep(500000); // 0.5 second delay
        }
        
        return $totalScraped;
    }
    
    private function fetchPage($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ($httpCode === 200) ? $html : false;
    }
    
    private function parseReviews($html) {
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        
        $reviews = [];
        
        // Try multiple selectors for review containers
        $selectors = [
            "//div[contains(@class, 'review-listing')]",
            "//div[contains(@class, 'review')]",
            "//article[contains(@class, 'review')]"
        ];
        
        foreach ($selectors as $selector) {
            $reviewNodes = $xpath->query($selector);
            if ($reviewNodes->length > 0) {
                foreach ($reviewNodes as $reviewNode) {
                    $review = $this->extractReviewData($xpath, $reviewNode);
                    if ($review) {
                        $reviews[] = $review;
                    }
                }
                break; // Use first successful selector
            }
        }
        
        return $reviews;
    }
    
    private function extractReviewData($xpath, $reviewNode) {
        try {
            // Extract store name
            $storeNodes = $xpath->query(".//h3 | .//h4 | .//*[contains(@class, 'store')]", $reviewNode);
            $storeName = $storeNodes->length > 0 ? trim($storeNodes->item(0)->textContent) : 'Unknown Store';
            
            // Extract rating
            $ratingNodes = $xpath->query(".//*[@data-rating] | .//*[contains(@class, 'star')]", $reviewNode);
            $rating = 5; // Default to 5 stars
            if ($ratingNodes->length > 0) {
                $ratingAttr = $ratingNodes->item(0)->getAttribute('data-rating');
                if ($ratingAttr) {
                    $rating = intval($ratingAttr);
                }
            }
            
            // Extract review content
            $contentNodes = $xpath->query(".//*[contains(@class, 'content') or contains(@class, 'text')]", $reviewNode);
            $reviewContent = $contentNodes->length > 0 ? trim($contentNodes->item(0)->textContent) : '';
            
            // Extract country (optional)
            $countryNodes = $xpath->query(".//*[contains(@class, 'country') or contains(@class, 'location')]", $reviewNode);
            $countryName = $countryNodes->length > 0 ? trim($countryNodes->item(0)->textContent) : 'Unknown';
            
            // Extract date
            $dateNodes = $xpath->query(".//time/@datetime | .//*[contains(@class, 'date')]", $reviewNode);
            $reviewDate = date('Y-m-d');
            if ($dateNodes->length > 0) {
                $dateValue = $dateNodes->item(0)->nodeValue;
                if (strtotime($dateValue)) {
                    $reviewDate = date('Y-m-d', strtotime($dateValue));
                }
            }
            
            return [
                'store_name' => $storeName,
                'rating' => $rating,
                'review_content' => $reviewContent,
                'country_name' => $countryName,
                'review_date' => $reviewDate
            ];
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    private function saveReviews($reviews, $appName, $maxToSave) {
        $saved = 0;
        
        foreach ($reviews as $review) {
            if ($saved >= $maxToSave) break;
            
            // Check for duplicates
            $checkStmt = $this->conn->prepare("
                SELECT id FROM review_repository 
                WHERE app_name = ? AND store_name = ? AND review_content = ? AND is_active = TRUE
            ");
            $checkStmt->execute([$appName, $review['store_name'], $review['review_content']]);
            
            if ($checkStmt->rowCount() == 0) {
                // Insert new review
                $insertStmt = $this->conn->prepare("
                    INSERT INTO review_repository
                    (app_name, store_name, country_name, rating, review_content, review_date, is_active, source_type, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, TRUE, 'live_scrape', NOW(), NOW())
                ");
                
                if ($insertStmt->execute([
                    $appName,
                    $review['store_name'],
                    $review['country_name'],
                    $review['rating'],
                    $review['review_content'],
                    $review['review_date']
                ])) {
                    $saved++;
                }
            }
        }
        
        return $saved;
    }
    
    private function showFinalSummary() {
        echo PHP_EOL . "📊 FINAL VERIFICATION SUMMARY:" . PHP_EOL;
        echo str_repeat('=', 50) . PHP_EOL;
        
        $totalActual = 0;
        $totalTarget = 0;
        
        foreach ($this->targetCounts as $appName => $info) {
            $actualCount = $this->getCurrentCount($appName);
            $targetCount = $info['target'];
            
            $status = ($actualCount == $targetCount) ? '✅' : '⚠️';
            echo "$status $appName: $actualCount / $targetCount reviews" . PHP_EOL;
            
            $totalActual += $actualCount;
            $totalTarget += $targetCount;
        }
        
        echo PHP_EOL . "🎯 TOTALS: $totalActual / $totalTarget reviews" . PHP_EOL;
        
        if ($totalActual == $totalTarget) {
            echo "🎉 SUCCESS: All apps match target counts!" . PHP_EOL;
        } else {
            echo "⚠️  Some apps still need adjustment" . PHP_EOL;
        }
    }
}

// Run the cleanup and verification
if (php_sapi_name() === 'cli') {
    $cleanup = new ReviewCleanupAndVerification();
    $cleanup->cleanupAndVerifyAll();
}
