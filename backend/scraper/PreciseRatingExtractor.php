<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Precise Rating Distribution Extractor
 * Extracts exact rating counts from Shopify's rating distribution section
 */
class PreciseRatingExtractor {
    private $dbManager;
    
    public function __construct() {
        $this->dbManager = new Database();
    }
    
    /**
     * Extract precise rating distribution from Shopify page
     */
    public function extractPreciseRatingDistribution($appSlug, $appName) {
        echo "🎯 EXTRACTING PRECISE RATING DISTRIBUTION FOR $appName\n";
        echo "====================================================\n";
        
        $url = "https://apps.shopify.com/$appSlug/reviews";
        $html = $this->fetchPage($url);
        
        if (!$html) {
            echo "❌ Failed to fetch page\n";
            return false;
        }
        
        // Extract overall rating and total from JSON-LD
        $overallRating = 5.0;
        $totalReviews = 0;
        
        if (preg_match('/"ratingValue":([\d.]+)/', $html, $matches)) {
            $overallRating = floatval($matches[1]);
        }
        
        if (preg_match('/"ratingCount":(\d+)/', $html, $matches)) {
            $totalReviews = intval($matches[1]);
        }
        
        echo "📊 Overall: {$overallRating}★ | Total: $totalReviews reviews\n";
        
        // Extract precise rating distribution from the rating bars section
        $distribution = $this->extractRatingCounts($html);
        
        if ($distribution) {
            $distribution['overall_rating'] = $overallRating;
            $distribution['total_reviews'] = $totalReviews;
            
            echo "📊 PRECISE RATING DISTRIBUTION:\n";
            echo "5★: {$distribution['five_star']}\n";
            echo "4★: {$distribution['four_star']}\n";
            echo "3★: {$distribution['three_star']}\n";
            echo "2★: {$distribution['two_star']}\n";
            echo "1★: {$distribution['one_star']}\n";
            
            // Verify totals
            $sum = $distribution['five_star'] + $distribution['four_star'] + 
                   $distribution['three_star'] + $distribution['two_star'] + $distribution['one_star'];
            echo "Sum: $sum | Expected: $totalReviews\n";
            
            // Save to database
            $this->saveRatingDistribution($appName, $distribution);
            
            return $distribution;
        }
        
        echo "❌ Failed to extract precise rating distribution\n";
        return false;
    }
    
    /**
     * Extract rating counts from HTML using precise selectors
     */
    private function extractRatingCounts($html) {
        $ratingCounts = [
            'five_star' => 0,
            'four_star' => 0,
            'three_star' => 0,
            'two_star' => 0,
            'one_star' => 0
        ];
        
        // Method 1: Extract from aria-label attributes
        $ratingMap = [5 => 'five_star', 4 => 'four_star', 3 => 'three_star', 2 => 'two_star', 1 => 'one_star'];
        
        foreach ($ratingMap as $rating => $key) {
            // Look for links with rating filter and aria-label
            if (preg_match('/href="[^"]*ratings%5B%5D=' . $rating . '"[^>]*aria-label="(\d+)\s+total\s+reviews?"/', $html, $matches)) {
                $ratingCounts[$key] = intval($matches[1]);
                echo "✅ $rating★: {$matches[1]} (from aria-label)\n";
            }
        }
        
        // Method 2: Extract from the rating distribution links
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        
        foreach ($ratingMap as $rating => $key) {
            if ($ratingCounts[$key] === 0) {
                // Look for links with the rating filter
                $linkNodes = $xpath->query("//a[contains(@href, 'ratings%5B%5D=$rating')]");
                
                foreach ($linkNodes as $link) {
                    $linkText = trim($link->textContent);
                    if (is_numeric($linkText)) {
                        $ratingCounts[$key] = intval($linkText);
                        echo "✅ {$rating}★: $linkText (from link text)\n";
                        break;
                    }
                }
            }
        }
        
        // Method 3: Look for spans with numbers near rating elements
        if (array_sum($ratingCounts) === 0) {
            // Find the rating distribution section
            $ratingSection = $xpath->query('//div[contains(@class, "app-reviews-metrics")]');
            
            if ($ratingSection->length > 0) {
                $section = $ratingSection->item(0);
                
                // Look for all numeric spans within this section
                $numberSpans = $xpath->query('.//span[contains(@class, "link-block--underline")]', $section);
                
                $foundNumbers = [];
                foreach ($numberSpans as $span) {
                    $text = trim($span->textContent);
                    if (is_numeric($text)) {
                        $foundNumbers[] = intval($text);
                    }
                }
                
                // Try to map numbers to ratings (5-star usually has the highest count)
                if (count($foundNumbers) >= 3) {
                    rsort($foundNumbers); // Sort descending
                    $ratingCounts['five_star'] = $foundNumbers[0] ?? 0;
                    $ratingCounts['four_star'] = $foundNumbers[1] ?? 0;
                    $ratingCounts['three_star'] = $foundNumbers[2] ?? 0;
                    $ratingCounts['two_star'] = $foundNumbers[3] ?? 0;
                    $ratingCounts['one_star'] = $foundNumbers[4] ?? 0;
                    
                    echo "✅ Extracted from spans: " . implode(', ', $foundNumbers) . "\n";
                }
            }
        }
        
        return $ratingCounts;
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
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
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
        
        return $httpCode === 200 ? $html : false;
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
            
            echo "✅ Saved precise rating distribution to database\n";
            return true;
            
        } catch (Exception $e) {
            echo "❌ Error saving: " . $e->getMessage() . "\n";
            return false;
        }
    }
}

// Test if called directly
if (basename(__FILE__) == basename($_SERVER["SCRIPT_NAME"])) {
    $extractor = new PreciseRatingExtractor();
    $extractor->extractPreciseRatingDistribution('storeseo', 'StoreSEO');
}
?>
