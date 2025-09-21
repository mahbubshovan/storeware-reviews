<?php
/**
 * Scrape ALL StoreSEO reviews (complete historical data)
 * This script will collect all 512+ reviews for StoreSEO
 */

require_once __DIR__ . '/scraper/UniversalLiveScraper.php';
require_once __DIR__ . '/utils/ReviewRepository.php';

echo "🎯 COMPLETE STORESEO HISTORICAL SCRAPING\n";
echo "=======================================\n\n";

$scraper = new UniversalLiveScraper();
$repository = new ReviewRepository();

$appSlug = 'storeseo';
$appName = 'StoreSEO';

echo "App: $appName\n";
echo "Target: ALL historical reviews (complete dataset)\n";
echo "Expected: 512+ reviews\n\n";

try {
    // Use the enhanced scrapeAllReviews method (duplicates will be handled automatically)
    $baseUrl = "https://apps.shopify.com/$appSlug/reviews";
    echo "🔄 Starting deep scraping from: $baseUrl\n\n";
    
    $allReviews = $scraper->scrapeAllReviews($baseUrl, $appName);
    
    if (!empty($allReviews)) {
        echo "\n✅ SCRAPING COMPLETE!\n";
        echo "Found " . count($allReviews) . " total reviews for $appName\n\n";
        
        // Save all reviews to repository
        echo "💾 Saving to repository...\n";
        $saved = 0;
        $duplicates = 0;
        
        foreach ($allReviews as $review) {
            try {
                $repository->addReview(
                    $appName,
                    $review['store_name'],
                    $review['country_name'],
                    $review['rating'],
                    $review['review_content'],
                    $review['review_date'],
                    'live_scrape'
                );
                $saved++;
                
                if ($saved % 50 == 0) {
                    echo "💾 Saved $saved reviews...\n";
                }
                
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $duplicates++;
                } else {
                    echo "⚠️ Error saving review: " . $e->getMessage() . "\n";
                }
            }
        }
        
        echo "\n📊 FINAL RESULTS:\n";
        echo "- Total scraped: " . count($allReviews) . " reviews\n";
        echo "- Successfully saved: $saved reviews\n";
        echo "- Duplicates skipped: $duplicates reviews\n";
        
        // Get final count from repository
        $apps = $repository->getAvailableApps();
        $finalCount = 0;
        foreach ($apps as $app) {
            if ($app['app_name'] === $appName) {
                $finalCount = $app['total_reviews'];
                break;
            }
        }
        
        echo "- Final count in repository: $finalCount reviews\n\n";
        
        if ($finalCount >= 500) {
            echo "🎉 SUCCESS! StoreSEO now has complete historical data!\n";
        } else {
            echo "⚠️ Expected 512+ reviews, but only got $finalCount. May need deeper scraping.\n";
        }
        
    } else {
        echo "❌ No reviews found for $appName\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error scraping $appName: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n✨ StoreSEO scraping complete!\n";
echo "The pagination system will now show all StoreSEO reviews.\n";
?>
