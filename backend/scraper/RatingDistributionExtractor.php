<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Rating Distribution Extractor
 * Extracts the complete rating distribution directly from the Shopify rating summary
 */
class RatingDistributionExtractor {
    private $dbManager;
    
    public function __construct() {
        $this->dbManager = new Database();
    }
    
    /**
     * Extract complete rating distribution from Shopify page
     */
    public function extractRatingDistribution($appSlug, $appName) {
        echo "🔍 EXTRACTING RATING DISTRIBUTION FOR $appName\n";
        echo "==============================================\n";
        
        $baseUrl = "https://apps.shopify.com/$appSlug/reviews";
        
        $html = $this->fetchPage($baseUrl);
        if (!$html) {
            echo "❌ Failed to fetch page\n";
            return false;
        }
        
        // Save HTML for debugging
        file_put_contents("debug_{$appSlug}_ratings.html", $html);
        echo "📄 Saved HTML to debug_{$appSlug}_ratings.html\n";
        
        // Extract rating distribution from the page
        $distribution = $this->parseRatingDistribution($html);
        
        if ($distribution) {
            echo "📊 EXTRACTED RATING DISTRIBUTION:\n";
            echo "Overall Rating: {$distribution['overall_rating']}★\n";
            echo "Total Reviews: {$distribution['total_reviews']}\n";
            echo "5★: {$distribution['five_star']}\n";
            echo "4★: {$distribution['four_star']}\n";
            echo "3★: {$distribution['three_star']}\n";
            echo "2★: {$distribution['two_star']}\n";
            echo "1★: {$distribution['one_star']}\n";
            
            // Save to database
            $this->saveRatingDistribution($appName, $distribution);
            
            return $distribution;
        }
        
        echo "❌ Failed to extract rating distribution\n";
        return false;
    }
    
    /**
     * Parse rating distribution from HTML
     */
    private function parseRatingDistribution($html) {
        // Method 1: Try to extract from JSON-LD structured data
        $distribution = $this->extractFromJsonLD($html);
        if ($distribution) {
            return $distribution;
        }
        
        // Method 2: Try to extract from rating bars/summary section
        $distribution = $this->extractFromRatingBars($html);
        if ($distribution) {
            return $distribution;
        }
        
        // Method 3: Try to extract from any rating-related text
        $distribution = $this->extractFromRatingText($html);
        if ($distribution) {
            return $distribution;
        }
        
        return false;
    }
    
    /**
     * Extract from JSON-LD structured data
     */
    private function extractFromJsonLD($html) {
        // Look for aggregateRating in JSON-LD
        if (preg_match('/"aggregateRating":\s*{[^}]*"ratingValue":([\d.]+)[^}]*"ratingCount":(\d+)[^}]*}/', $html, $matches)) {
            $overallRating = floatval($matches[1]);
            $totalReviews = intval($matches[2]);

            echo "✅ Found JSON-LD data: {$overallRating}★ ($totalReviews reviews)\n";

            // Don't return here - continue to extract actual distribution
        }
        
        return false;
    }
    
    /**
     * Extract from rating bars/summary section
     */
    private function extractFromRatingBars($html) {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        // Extract the exact rating distribution from Shopify's structure
        $ratingCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

        // Look for rating links with aria-label containing review counts
        for ($rating = 5; $rating >= 1; $rating--) {
            // Find links with aria-label like "502 total reviews" for rating 5
            $linkNodes = $xpath->query("//a[contains(@href, 'ratings%5B%5D=$rating')]");

            foreach ($linkNodes as $link) {
                $ariaLabel = $link->getAttribute('aria-label');
                if (preg_match('/(\d+)\s+total\s+reviews?/', $ariaLabel, $matches)) {
                    $ratingCounts[$rating] = intval($matches[1]);
                    echo "✅ Found $rating★: {$matches[1]} reviews\n";
                    break;
                }

                // Also try to get the number from the link text
                $linkText = trim($link->textContent);
                if (is_numeric($linkText)) {
                    $ratingCounts[$rating] = intval($linkText);
                    echo "✅ Found $rating★: $linkText reviews (from text)\n";
                    break;
                }
            }
        }

        // Get overall data
        $overallRating = 0;
        $totalReviews = array_sum($ratingCounts);

        // Extract overall rating from JSON-LD
        if (preg_match('/"ratingValue":([\d.]+)/', $html, $matches)) {
            $overallRating = floatval($matches[1]);
        }

        // Verify total matches JSON-LD count
        if (preg_match('/"ratingCount":(\d+)/', $html, $matches)) {
            $jsonTotal = intval($matches[1]);
            if ($totalReviews !== $jsonTotal) {
                echo "⚠️ Total mismatch: scraped $totalReviews vs JSON-LD $jsonTotal\n";
                // Use JSON-LD total as authoritative
                $totalReviews = $jsonTotal;
            }
        }

        if ($totalReviews > 0) {
            return [
                'overall_rating' => $overallRating,
                'total_reviews' => $totalReviews,
                'five_star' => $ratingCounts[5],
                'four_star' => $ratingCounts[4],
                'three_star' => $ratingCounts[3],
                'two_star' => $ratingCounts[2],
                'one_star' => $ratingCounts[1]
            ];
        }

        return false;
    }
    
    /**
     * Extract from rating text patterns
     */
    private function extractFromRatingText($html) {
        // Look for patterns like "293 reviews" with "5 stars"
        if (preg_match_all('/(\d+)\s*(?:reviews?|ratings?).*?(\d+)\s*(?:stars?|★)/', $html, $matches, PREG_SET_ORDER)) {
            // Process matches to build distribution
            // This is complex and may not be reliable
        }
        
        return false;
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
            curl_close($ch);
            return false;
        }
        
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return false;
        }
        
        return $html;
    }
    
    /**
     * Save rating distribution to database
     */
    private function saveRatingDistribution($appName, $distribution) {
        try {
            $conn = $this->dbManager->getConnection();
            
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
                $distribution['total_reviews'],
                $distribution['overall_rating'],
                $distribution['five_star'],
                $distribution['four_star'],
                $distribution['three_star'],
                $distribution['two_star'],
                $distribution['one_star']
            ]);
            
            echo "✅ Saved rating distribution to database\n";
            return true;
            
        } catch (Exception $e) {
            echo "❌ Error saving: " . $e->getMessage() . "\n";
            return false;
        }
    }
}
?>
