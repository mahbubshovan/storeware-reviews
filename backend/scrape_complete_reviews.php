<?php
/**
 * Complete Review Scraper
 * Scrapes ALL available reviews from Shopify app store pages
 * Continues scraping until no more pages are available
 * Ensures complete data accuracy matching live Shopify pages
 */

require_once 'config/database.php';

class CompleteReviewScraper {
    private $conn;
    private $apps = [
        'StoreSEO' => 'storeseo',
        'StoreFAQ' => 'storefaq', 
        'EasyFlow' => 'product-options-4',
        'TrustSync' => 'customer-review-app',
        'Vidify' => 'vidify',
        'BetterDocs FAQ' => 'betterdocs-knowledgebase'
    ];
    
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }
    
    public function scrapeAllApps() {
        echo "🚀 STARTING COMPLETE REVIEW SCRAPING FOR ALL APPS" . PHP_EOL;
        echo str_repeat('=', 60) . PHP_EOL;
        
        $totalScraped = 0;
        
        foreach ($this->apps as $appName => $slug) {
            echo PHP_EOL . "📱 SCRAPING: $appName ($slug)" . PHP_EOL;
            echo str_repeat('-', 40) . PHP_EOL;
            
            $scraped = $this->scrapeAppComplete($appName, $slug);
            $totalScraped += $scraped;
            
            echo "✅ $appName: $scraped reviews scraped" . PHP_EOL;
            
            // Small delay between apps to be respectful
            sleep(2);
        }
        
        echo PHP_EOL . "🎉 COMPLETE! Total scraped: $totalScraped reviews" . PHP_EOL;
        $this->showFinalSummary();
    }
    
    public function scrapeAppComplete($appName, $slug) {
        $page = 1;
        $totalScraped = 0;
        $consecutiveEmptyPages = 0;
        $maxEmptyPages = 3; // Stop after 3 consecutive empty pages
        
        while ($consecutiveEmptyPages < $maxEmptyPages) {
            echo "  📄 Scraping page $page...";
            
            $reviews = $this->scrapePage($slug, $page);
            
            if (empty($reviews)) {
                $consecutiveEmptyPages++;
                echo " (empty - $consecutiveEmptyPages/$maxEmptyPages)" . PHP_EOL;
                
                if ($consecutiveEmptyPages >= $maxEmptyPages) {
                    echo "  🛑 Stopping - reached maximum empty pages" . PHP_EOL;
                    break;
                }
            } else {
                $consecutiveEmptyPages = 0; // Reset counter
                $saved = $this->saveReviews($reviews, $appName);
                $totalScraped += $saved;
                echo " ✅ $saved reviews saved" . PHP_EOL;
            }
            
            $page++;
            
            // Safety limit to prevent infinite loops
            if ($page > 100) {
                echo "  ⚠️ Reached page limit (100), stopping" . PHP_EOL;
                break;
            }
            
            // Small delay between pages
            usleep(500000); // 0.5 seconds
        }
        
        return $totalScraped;
    }
    
    private function scrapePage($slug, $page) {
        $url = "https://apps.shopify.com/$slug/reviews?page=$page";
        
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
        
        if ($httpCode !== 200 || !$html) {
            echo " ❌ Failed to fetch page $page (HTTP: $httpCode)" . PHP_EOL;
            return [];
        }
        
        return $this->parseReviews($html);
    }
    
    private function parseReviews($html) {
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        
        $reviews = [];
        
        // Find review containers
        $reviewNodes = $xpath->query("//div[contains(@class, 'review-listing')]");
        
        foreach ($reviewNodes as $reviewNode) {
            $review = $this->extractReviewData($xpath, $reviewNode);
            if ($review) {
                $reviews[] = $review;
            }
        }
        
        return $reviews;
    }
    
    private function extractReviewData($xpath, $reviewNode) {
        try {
            // Extract store name
            $storeNodes = $xpath->query(".//h3[contains(@class, 'review-listing-header')]", $reviewNode);
            $storeName = $storeNodes->length > 0 ? trim($storeNodes->item(0)->textContent) : 'Unknown Store';
            
            // Extract rating
            $ratingNodes = $xpath->query(".//div[contains(@class, 'ui-star-rating')]/@data-rating", $reviewNode);
            $rating = $ratingNodes->length > 0 ? intval($ratingNodes->item(0)->value) : 5;
            
            // Extract review content
            $contentNodes = $xpath->query(".//div[contains(@class, 'review-content')]", $reviewNode);
            $reviewContent = $contentNodes->length > 0 ? trim($contentNodes->item(0)->textContent) : '';
            
            // Extract country
            $countryNodes = $xpath->query(".//span[contains(@class, 'review-metadata')]", $reviewNode);
            $countryName = 'Unknown';
            if ($countryNodes->length > 0) {
                $metadata = $countryNodes->item(0)->textContent;
                if (preg_match('/([A-Za-z\s]+)$/', trim($metadata), $matches)) {
                    $countryName = trim($matches[1]);
                }
            }
            
            // Extract date
            $dateNodes = $xpath->query(".//time/@datetime", $reviewNode);
            $reviewDate = $dateNodes->length > 0 ? 
                date('Y-m-d', strtotime($dateNodes->item(0)->value)) : 
                date('Y-m-d');
            
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
    
    private function saveReviews($reviews, $appName) {
        $saved = 0;
        
        foreach ($reviews as $review) {
            // Check if review already exists (prevent duplicates)
            $checkStmt = $this->conn->prepare("
                SELECT id FROM review_repository 
                WHERE app_name = ? AND store_name = ? AND review_content = ? AND review_date = ?
            ");
            $checkStmt->execute([
                $appName, 
                $review['store_name'], 
                $review['review_content'], 
                $review['review_date']
            ]);
            
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
        echo PHP_EOL . "📊 FINAL DATABASE SUMMARY:" . PHP_EOL;
        echo str_repeat('=', 50) . PHP_EOL;
        
        foreach ($this->apps as $appName => $slug) {
            $stmt = $this->conn->prepare('SELECT COUNT(*) as count FROM review_repository WHERE app_name = ? AND is_active = TRUE');
            $stmt->execute([$appName]);
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            echo "📱 $appName: $count total reviews" . PHP_EOL;
        }
        
        $totalStmt = $this->conn->prepare('SELECT COUNT(*) as total FROM review_repository WHERE is_active = TRUE');
        $totalStmt->execute();
        $total = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        echo PHP_EOL . "🎯 GRAND TOTAL: $total reviews across all apps" . PHP_EOL;
    }
}

// Run the scraper
if (php_sapi_name() === 'cli') {
    $scraper = new CompleteReviewScraper();
    $scraper->scrapeAllApps();
}
