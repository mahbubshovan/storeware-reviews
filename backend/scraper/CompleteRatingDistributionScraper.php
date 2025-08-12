<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Complete Rating Distribution Scraper
 * Scrapes ALL pages to get complete rating distribution like the Shopify page shows
 */
class CompleteRatingDistributionScraper {
    private $dbManager;
    
    public function __construct() {
        $this->dbManager = new Database();
    }
    
    /**
     * Scrape complete rating distribution for an app
     */
    public function scrapeCompleteRatingDistribution($appSlug, $appName) {
        echo "🔍 SCRAPING COMPLETE RATING DISTRIBUTION FOR $appName\n";
        echo "====================================================\n";
        
        $baseUrl = "https://apps.shopify.com/$appSlug/reviews";
        
        // First, get the overall rating and total count from the main page
        $overallData = $this->getOverallRatingData($baseUrl);
        
        if (!$overallData) {
            echo "❌ Failed to get overall rating data\n";
            return false;
        }
        
        echo "📊 Overall Rating: {$overallData['overall_rating']}★\n";
        echo "📊 Total Reviews: {$overallData['total_reviews']}\n";
        
        // Now scrape rating distribution from all pages
        $ratingDistribution = $this->scrapeAllPagesForRatings($baseUrl, $overallData['total_reviews']);
        
        if ($ratingDistribution) {
            // Save the complete rating distribution
            $this->saveRatingDistribution($appName, $overallData, $ratingDistribution);
            
            echo "✅ Complete rating distribution saved for $appName\n";
            return true;
        }
        
        return false;
    }
    
    /**
     * Get overall rating and total count from main page
     */
    private function getOverallRatingData($baseUrl) {
        $html = $this->fetchPage($baseUrl);
        if (!$html) {
            return false;
        }
        
        // Extract from JSON-LD data
        $overallRating = 0;
        $totalReviews = 0;
        
        // Look for JSON-LD structured data
        if (preg_match('/"ratingValue":([\d.]+)/', $html, $matches)) {
            $overallRating = floatval($matches[1]);
        }
        
        if (preg_match('/"ratingCount":(\d+)/', $html, $matches)) {
            $totalReviews = intval($matches[1]);
        }
        
        // Also try to extract from the rating distribution section
        if (preg_match('/(\d+)\s*★.*?(\d+)\s*reviews?/i', $html, $matches)) {
            if (!$overallRating) $overallRating = floatval($matches[1]);
            if (!$totalReviews) $totalReviews = intval($matches[2]);
        }
        
        return [
            'overall_rating' => $overallRating,
            'total_reviews' => $totalReviews
        ];
    }
    
    /**
     * Scrape all pages to get complete rating distribution
     */
    private function scrapeAllPagesForRatings($baseUrl, $expectedTotal) {
        $ratingCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        $totalScraped = 0;
        $maxPages = ceil($expectedTotal / 10) + 5; // Add buffer
        
        echo "🌐 Scraping all pages for complete rating distribution...\n";
        echo "📄 Expected total reviews: $expectedTotal\n";
        echo "📄 Max pages to check: $maxPages\n\n";
        
        for ($page = 1; $page <= $maxPages; $page++) {
            $url = $baseUrl . "?page=$page";
            echo "📄 Page $page: ";
            
            $html = $this->fetchPage($url);
            if (!$html) {
                echo "❌ Failed to fetch\n";
                break;
            }
            
            $pageRatings = $this->extractRatingsFromPage($html);
            
            if (empty($pageRatings)) {
                echo "⚠️ No reviews found - end of pages\n";
                break;
            }
            
            foreach ($pageRatings as $rating) {
                if ($rating >= 1 && $rating <= 5) {
                    $ratingCounts[$rating]++;
                    $totalScraped++;
                }
            }
            
            echo "✅ Found " . count($pageRatings) . " reviews (Total: $totalScraped)\n";
            
            // Stop if we've scraped enough reviews
            if ($totalScraped >= $expectedTotal) {
                echo "🎯 Reached expected total, stopping\n";
                break;
            }
            
            // Add small delay to be respectful
            usleep(500000); // 0.5 seconds
        }
        
        echo "\n📊 COMPLETE RATING DISTRIBUTION:\n";
        echo "5★: {$ratingCounts[5]}\n";
        echo "4★: {$ratingCounts[4]}\n";
        echo "3★: {$ratingCounts[3]}\n";
        echo "2★: {$ratingCounts[2]}\n";
        echo "1★: {$ratingCounts[1]}\n";
        echo "Total scraped: $totalScraped\n";
        
        return $ratingCounts;
    }
    
    /**
     * Extract ratings from a single page
     */
    private function extractRatingsFromPage($html) {
        $ratings = [];
        
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        $reviewNodes = $xpath->query('//div[@data-review-content-id]');
        
        foreach ($reviewNodes as $node) {
            // Count filled stars
            $starNodes = $xpath->query('.//svg[contains(@class, "tw-fill-fg-primary")]', $node);
            $rating = $starNodes->length;
            
            if ($rating >= 1 && $rating <= 5) {
                $ratings[] = $rating;
            }
        }
        
        return $ratings;
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
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1',
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            echo "❌ cURL Error: " . curl_error($ch) . "\n";
            curl_close($ch);
            return false;
        }
        
        curl_close($ch);
        
        if ($httpCode !== 200) {
            echo "❌ HTTP Error: $httpCode\n";
            return false;
        }
        
        return $html;
    }
    
    /**
     * Save complete rating distribution to database
     */
    private function saveRatingDistribution($appName, $overallData, $ratingCounts) {
        try {
            $conn = $this->dbManager->getConnection();
            
            // Update or insert into app_metadata table
            $stmt = $conn->prepare("
                INSERT INTO app_metadata (
                    app_name, total_reviews, overall_rating,
                    five_star_total, four_star_total, three_star_total, 
                    two_star_total, one_star_total, last_updated
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    total_reviews = VALUES(total_reviews),
                    overall_rating = VALUES(overall_rating),
                    five_star_total = VALUES(five_star_total),
                    four_star_total = VALUES(four_star_total),
                    three_star_total = VALUES(three_star_total),
                    two_star_total = VALUES(two_star_total),
                    one_star_total = VALUES(one_star_total),
                    last_updated = NOW()
            ");
            
            $stmt->execute([
                $appName,
                $overallData['total_reviews'],
                $overallData['overall_rating'],
                $ratingCounts[5],
                $ratingCounts[4],
                $ratingCounts[3],
                $ratingCounts[2],
                $ratingCounts[1]
            ]);
            
            echo "✅ Saved complete rating distribution to database\n";
            return true;
            
        } catch (Exception $e) {
            echo "❌ Error saving rating distribution: " . $e->getMessage() . "\n";
            return false;
        }
    }
}

// Test if called directly
if (basename(__FILE__) == basename($_SERVER["SCRIPT_NAME"])) {
    $scraper = new CompleteRatingDistributionScraper();
    $scraper->scrapeCompleteRatingDistribution('storeseo', 'StoreSEO');
}
?>
