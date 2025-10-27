<?php
/**
 * Fix reviews with 1970-01-01 dates (parsing failures)
 * This script will re-scrape the Shopify page to get correct dates
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/scraper/EnhancedUniversalScraper.php';

echo "=== FIXING 1970-01-01 DATES ===\n\n";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Find all reviews with 1970-01-01 date
    echo "1. Finding reviews with 1970-01-01 date...\n";
    $stmt = $conn->prepare("
        SELECT id, app_name, store_name, rating, review_content 
        FROM reviews 
        WHERE review_date = '1970-01-01'
        ORDER BY app_name, id
    ");
    $stmt->execute();
    $badReviews = $stmt->fetchAll();
    
    echo "   Found " . count($badReviews) . " reviews with bad dates\n\n";
    
    if (empty($badReviews)) {
        echo "✅ No reviews with 1970-01-01 dates found!\n";
        exit;
    }
    
    // Group by app
    $reviewsByApp = [];
    foreach ($badReviews as $review) {
        $app = $review['app_name'];
        if (!isset($reviewsByApp[$app])) {
            $reviewsByApp[$app] = [];
        }
        $reviewsByApp[$app][] = $review;
    }
    
    // For each app, re-scrape to get correct dates
    foreach ($reviewsByApp as $appName => $reviews) {
        echo "2. Re-scraping $appName to get correct dates...\n";
        echo "   Found " . count($reviews) . " reviews with bad dates\n";
        
        // Map app names to slugs
        $appSlugs = [
            'StoreSEO' => 'storeseo',
            'StoreFAQ' => 'storefaq',
            'EasyFlow' => 'product-options-4',
            'BetterDocs FAQ Knowledge Base' => 'betterdocs-knowledgebase',
            'Vidify' => 'vidify',
            'TrustSync' => 'customer-review-app'
        ];
        
        if (!isset($appSlugs[$appName])) {
            echo "   ⚠️ Unknown app: $appName\n";
            continue;
        }
        
        $appSlug = $appSlugs[$appName];
        
        // Scrape fresh data
        $scraper = new EnhancedUniversalScraper();
        $result = $scraper->scrapeAppWithRateLimit($appSlug, $appName);
        
        if (!isset($result['reviews']) || empty($result['reviews'])) {
            echo "   ❌ Failed to scrape $appName\n";
            continue;
        }
        
        echo "   ✅ Scraped " . count($result['reviews']) . " reviews\n";
        
        // Try to match and update bad reviews
        $updated = 0;
        foreach ($reviews as $badReview) {
            // Find matching review in scraped data
            foreach ($result['reviews'] as $scrapedReview) {
                // Match by store name and review content (first 50 chars)
                if ($scrapedReview['store_name'] === $badReview['store_name'] &&
                    strpos($scrapedReview['review_content'], substr($badReview['review_content'], 0, 50)) === 0) {
                    
                    // Update the review with correct date
                    $updateStmt = $conn->prepare("
                        UPDATE reviews 
                        SET review_date = ? 
                        WHERE id = ?
                    ");
                    $updateStmt->execute([
                        $scrapedReview['review_date'],
                        $badReview['id']
                    ]);
                    
                    echo "   ✅ Updated review ID {$badReview['id']}: {$badReview['store_name']} -> {$scrapedReview['review_date']}\n";
                    $updated++;
                    break;
                }
            }
        }
        
        echo "   Updated $updated / " . count($reviews) . " reviews\n\n";
    }
    
    // Verify the fix
    echo "3. Verifying fix...\n";
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM reviews WHERE review_date = '1970-01-01'");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        echo "   ✅ All 1970-01-01 dates have been fixed!\n";
    } else {
        echo "   ⚠️ Still " . $result['count'] . " reviews with 1970-01-01 dates\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>

