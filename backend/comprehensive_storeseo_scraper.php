<?php
/**
 * Comprehensive StoreSEO Scraper - Fetches ALL reviews from Shopify
 * No pagination limits, no date filtering, complete historical data
 */

require_once __DIR__ . '/config/database.php';

class ComprehensiveStoreSEOScraper {
    private $db;
    private $baseUrl = 'https://apps.shopify.com/storeseo/reviews';
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function scrapeAll() {
        echo "🚀 COMPREHENSIVE STORESEO SCRAPER\n";
        echo "================================\n\n";
        
        $allReviews = [];
        $page = 1;
        $maxPages = 200; // Allow up to 200 pages
        
        while ($page <= $maxPages) {
            echo "📄 Scraping page $page...\n";
            
            $url = $this->baseUrl . "?sort_by=newest&page=$page";
            $html = $this->fetchPage($url);
            
            if (!$html) {
                echo "❌ Failed to fetch page $page\n";
                break;
            }
            
            $pageReviews = $this->parseReviews($html);
            
            if (empty($pageReviews)) {
                echo "✅ No more reviews found on page $page - reached end\n";
                break;
            }
            
            echo "✅ Found " . count($pageReviews) . " reviews on page $page\n";
            $allReviews = array_merge($allReviews, $pageReviews);
            
            echo "📊 Total so far: " . count($allReviews) . " reviews\n\n";
            
            $page++;
            sleep(1); // Respectful delay
        }
        
        echo "\n=== SCRAPING COMPLETE ===\n";
        echo "Total reviews collected: " . count($allReviews) . "\n\n";
        
        if (!empty($allReviews)) {
            $this->saveToDatabase($allReviews);
            $this->syncAccessReviews();
        }
        
        return $allReviews;
    }
    
    private function fetchPage($url) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Connection: keep-alive',
            ],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            echo "⚠️ HTTP $httpCode\n";
            return false;
        }
        
        return $html;
    }
    
    private function parseReviews($html) {
        $reviews = [];
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        $reviewNodes = $xpath->query("//div[@data-review-content-id]");
        
        foreach ($reviewNodes as $node) {
            $review = $this->extractReview($node, $xpath);
            if ($review) {
                $reviews[] = $review;
            }
        }
        
        return $reviews;
    }
    
    private function extractReview($node, $xpath) {
        try {
            // Extract rating
            $ratingNode = $xpath->query(".//span[contains(@aria-label, 'star')]", $node);
            $rating = 0;
            if ($ratingNode->length > 0) {
                $ariaLabel = $ratingNode->item(0)->getAttribute('aria-label');
                if (preg_match('/(\d+)\s+out of 5 stars/', $ariaLabel, $matches)) {
                    $rating = (int)$matches[1];
                }
            }
            
            // Extract store name
            $storeNode = $xpath->query(".//div[contains(@class, 'tw-text-heading-xs')]", $node);
            $storeName = '';
            if ($storeNode->length > 0) {
                $storeName = trim($storeNode->item(0)->textContent);
            }
            
            // Extract date
            $dateNode = $xpath->query(".//div[contains(@class, 'tw-text-fg-tertiary')]", $node);
            $reviewDate = '';
            if ($dateNode->length > 0) {
                $dateText = trim($dateNode->item(0)->textContent);
                $reviewDate = $this->parseDate($dateText);
            }
            
            // Extract content
            $contentNode = $xpath->query(".//p[contains(@class, 'tw-break-words')]", $node);
            $reviewContent = '';
            if ($contentNode->length > 0) {
                $reviewContent = trim($contentNode->item(0)->textContent);
            }
            
            // Extract country
            $country = $this->extractCountry($node, $xpath);
            
            if ($rating > 0 && !empty($storeName) && !empty($reviewDate)) {
                return [
                    'store_name' => $storeName,
                    'country_name' => $country,
                    'rating' => $rating,
                    'review_content' => $reviewContent,
                    'review_date' => $reviewDate
                ];
            }
        } catch (Exception $e) {
            // Skip this review
        }
        
        return null;
    }
    
    private function parseDate($dateText) {
        if (preg_match('/(\w+)\s+(\d{1,2}),\s+(\d{4})/', $dateText, $matches)) {
            $date = DateTime::createFromFormat('F j, Y', $matches[0]);
            if ($date) {
                return $date->format('Y-m-d');
            }
        }
        return date('Y-m-d');
    }
    
    private function extractCountry($node, $xpath) {
        $countryNode = $xpath->query(".//div[contains(@class, 'tw-text-fg-secondary')]", $node);
        if ($countryNode->length > 0) {
            $text = trim($countryNode->item(0)->textContent);
            if (preg_match('/from\s+(.+)$/', $text, $matches)) {
                return trim($matches[1]);
            }
        }
        return 'Unknown';
    }
    
    private function saveToDatabase($reviews) {
        echo "\n💾 SAVING TO DATABASE\n";
        echo "====================\n";
        
        $conn = $this->db->getConnection();
        
        // Clear existing data
        $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
        $conn->prepare("DELETE FROM access_reviews WHERE app_name = 'StoreSEO'")->execute();
        $conn->prepare("DELETE FROM reviews WHERE app_name = 'StoreSEO'")->execute();
        $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
        
        $saved = 0;
        foreach ($reviews as $review) {
            try {
                $stmt = $conn->prepare("
                    INSERT INTO reviews (app_name, store_name, country_name, rating, review_content, review_date, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, 1)
                ");
                
                if ($stmt->execute([
                    'StoreSEO',
                    $review['store_name'],
                    $review['country_name'],
                    $review['rating'],
                    $review['review_content'],
                    $review['review_date']
                ])) {
                    $saved++;
                }
            } catch (Exception $e) {
                // Skip duplicates
            }
        }
        
        echo "✅ Saved $saved reviews to database\n";
    }
    
    private function syncAccessReviews() {
        echo "\n🔄 SYNCING ACCESS REVIEWS\n";
        echo "========================\n";
        
        $conn = $this->db->getConnection();
        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
        
        $stmt = $conn->prepare("
            INSERT INTO access_reviews (app_name, review_date, review_content, country_name, rating, original_review_id)
            SELECT app_name, review_date, review_content, country_name, rating, id
            FROM reviews
            WHERE app_name = 'StoreSEO' AND review_date >= ?
        ");
        
        $stmt->execute([$thirtyDaysAgo]);
        echo "✅ Synced last 30 days reviews to access_reviews\n";
    }
}

// Run the scraper
$scraper = new ComprehensiveStoreSEOScraper();
$scraper->scrapeAll();
?>

