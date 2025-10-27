<?php
/**
 * Live-Only Review Scraper
 * 
 * Scrapes ONLY reviews that are currently visible on the live Shopify review pages
 * Stops scraping when the total count matches the live Shopify page count
 * Does NOT include archived reviews
 */

class LiveOnlyReviewScraper {
    private $dbManager;
    
    public function __construct() {
        require_once __DIR__ . '/../config/database.php';
        $this->dbManager = new Database();
    }
    
    /**
     * Scrape only live reviews (matching the count shown on Shopify)
     */
    public function scrapeApp($appSlug, $appName = null) {
        if (!$appName) {
            $appName = ucfirst($appSlug);
        }

        echo "🟢 LIVE-ONLY REVIEW SCRAPER\n";
        echo "🎯 App: $appName ($appSlug)\n";
        echo "🌐 Scraping ONLY reviews visible on live Shopify pages...\n\n";

        // Step 1: Get the total count from the live Shopify page
        $mainUrl = "https://apps.shopify.com/$appSlug/reviews";
        $mainHtml = $this->fetchPage($mainUrl);
        
        if (!$mainHtml) {
            echo "❌ Failed to fetch main page\n";
            return ['success' => false, 'message' => 'Failed to fetch main page', 'count' => 0];
        }

        // Extract total count from JSON-LD schema
        $targetCount = $this->extractTotalCount($mainHtml);
        if ($targetCount === 0) {
            echo "❌ Could not extract total count from Shopify page\n";
            return ['success' => false, 'message' => 'Could not extract total count', 'count' => 0];
        }

        echo "📊 Target count from Shopify: $targetCount reviews\n";
        echo "🔄 Scraping pages until we reach $targetCount reviews...\n\n";

        // Step 2: Scrape pages until we reach the target count
        $allReviews = [];
        $baseUrl = "https://apps.shopify.com/$appSlug/reviews";

        for ($page = 1; $page <= 200; $page++) {
            $url = $baseUrl . "?sort_by=newest&page=$page";
            echo "📄 Page $page: $url\n";

            $html = $this->fetchPage($url);
            if (!$html) {
                echo "❌ Failed to fetch page $page - STOPPING\n";
                break;
            }

            $pageReviews = $this->parseReviewsFromHTML($html);
            if (empty($pageReviews)) {
                echo "⚠️ No reviews found on page $page - STOPPING\n";
                break;
            }

            // Add reviews from this page
            foreach ($pageReviews as $review) {
                $allReviews[] = $review;
                echo "  ✅ {$review['review_date']} - {$review['rating']}★ - {$review['store_name']}\n";
                
                // Stop if we've reached the target count
                if (count($allReviews) >= $targetCount) {
                    echo "\n✅ Reached target count of $targetCount reviews\n";
                    break 2; // Break out of both loops
                }
            }

            echo "  📊 Total so far: " . count($allReviews) . " / $targetCount\n\n";

            // Add delay between requests
            sleep(1);
        }

        // Step 3: Save to database
        if (empty($allReviews)) {
            echo "❌ No reviews scraped\n";
            return ['success' => false, 'message' => 'No reviews scraped', 'count' => 0];
        }

        // Clear existing data
        $this->clearAppData($appName);

        // Save reviews
        $saved = 0;
        foreach ($allReviews as $review) {
            if ($this->saveReview($appName, $review)) {
                $saved++;
            }
        }

        echo "\n✅ Saved $saved reviews to database\n";
        return ['success' => true, 'message' => "Scraped $saved reviews", 'count' => $saved];
    }

    private function extractTotalCount($html) {
        // Try to extract from JSON-LD schema
        if (preg_match('/"ratingCount":(\d+)/', $html, $matches)) {
            return (int)$matches[1];
        }
        return 0;
    }

    private function parseReviewsFromHTML($html) {
        $reviews = [];
        
        try {
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);

            // Find all review containers
            $reviewNodes = $xpath->query('//div[contains(@class, "ReviewCard")]');

            foreach ($reviewNodes as $node) {
                $review = $this->extractReviewFromNode($node, $xpath);
                if ($review) {
                    $reviews[] = $review;
                }
            }
        } catch (Exception $e) {
            echo "Error parsing HTML: " . $e->getMessage() . "\n";
        }

        return $reviews;
    }

    private function extractReviewFromNode($node, $xpath) {
        try {
            // Extract store name
            $storeNodes = $xpath->query('.//a[contains(@class, "ReviewCard__Author")]', $node);
            $storeName = $storeNodes->length > 0 ? trim($storeNodes->item(0)->textContent) : '';

            // Extract rating
            $ratingNodes = $xpath->query('.//span[contains(@class, "Rating__Stars")]', $node);
            $rating = 0;
            if ($ratingNodes->length > 0) {
                $ratingText = $ratingNodes->item(0)->getAttribute('aria-label');
                if (preg_match('/(\d+)/', $ratingText, $matches)) {
                    $rating = (int)$matches[1];
                }
            }

            // Extract review date
            $dateNodes = $xpath->query('.//span[contains(@class, "ReviewCard__Date")]', $node);
            $reviewDate = $dateNodes->length > 0 ? trim($dateNodes->item(0)->textContent) : '';
            $reviewDate = $this->parseDate($reviewDate);

            // Extract country
            $countryNodes = $xpath->query('.//span[contains(@class, "ReviewCard__Country")]', $node);
            $country = $countryNodes->length > 0 ? trim($countryNodes->item(0)->textContent) : 'Unknown';

            // Extract review content
            $contentNodes = $xpath->query('.//div[contains(@class, "ReviewCard__Content")]', $node);
            $reviewContent = $contentNodes->length > 0 ? trim($contentNodes->item(0)->textContent) : '';

            // Validate required fields
            if (empty($storeName) || empty($reviewDate) || $rating === 0) {
                return null;
            }

            return [
                'store_name' => $storeName,
                'country_name' => substr($country, 0, 50),
                'rating' => $rating,
                'review_content' => $reviewContent,
                'review_date' => $reviewDate
            ];
        } catch (Exception $e) {
            return null;
        }
    }

    private function parseDate($dateStr) {
        // Parse relative dates like "2 days ago", "1 month ago", etc.
        $dateStr = trim($dateStr);
        
        if (preg_match('/(\d+)\s+days?\s+ago/i', $dateStr, $matches)) {
            return date('Y-m-d', strtotime("-{$matches[1]} days"));
        } elseif (preg_match('/(\d+)\s+months?\s+ago/i', $dateStr, $matches)) {
            return date('Y-m-d', strtotime("-{$matches[1]} months"));
        } elseif (preg_match('/(\d+)\s+years?\s+ago/i', $dateStr, $matches)) {
            return date('Y-m-d', strtotime("-{$matches[1]} years"));
        } elseif (preg_match('/today/i', $dateStr)) {
            return date('Y-m-d');
        } elseif (preg_match('/yesterday/i', $dateStr)) {
            return date('Y-m-d', strtotime('-1 day'));
        } else {
            // Try to parse as a date
            $parsed = strtotime($dateStr);
            if ($parsed !== false) {
                return date('Y-m-d', $parsed);
            }
        }
        
        return date('Y-m-d');
    }

    private function fetchPage($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 429) {
            echo "⚠️ Rate limited (HTTP 429) - waiting 10 seconds...\n";
            sleep(10);
            return $this->fetchPage($url); // Retry
        }
        
        return ($httpCode === 200) ? $html : null;
    }

    private function clearAppData($appName) {
        try {
            $conn = $this->dbManager->getConnection();
            $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
            $conn->prepare("DELETE FROM access_reviews WHERE app_name = ?")->execute([$appName]);
            $conn->prepare("DELETE FROM reviews WHERE app_name = ?")->execute([$appName]);
            $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
            echo "✅ Cleared existing data for $appName\n";
        } catch (Exception $e) {
            echo "⚠️ Error clearing data: " . $e->getMessage() . "\n";
        }
    }

    private function saveReview($appName, $review) {
        try {
            $conn = $this->dbManager->getConnection();
            $stmt = $conn->prepare("
                INSERT INTO reviews (app_name, store_name, country_name, rating, review_content, review_date, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");

            return $stmt->execute([
                $appName,
                $review['store_name'],
                $review['country_name'],
                $review['rating'],
                $review['review_content'],
                $review['review_date']
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
}

