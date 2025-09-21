<?php
/**
 * Scrape ALL historical reviews for all apps (complete data collection)
 * This script will collect the complete review history, not just recent reviews
 */

require_once __DIR__ . '/scraper/UniversalLiveScraper.php';
require_once __DIR__ . '/utils/ReviewRepository.php';

echo "🔄 COMPLETE HISTORICAL REVIEW SCRAPING\n";
echo "=====================================\n\n";

// All supported apps with their slugs
$apps = [
    'storeseo' => 'StoreSEO',
    'storefaq' => 'StoreFAQ', 
    'vidify-video-backgrounds' => 'Vidify',
    'trustsync-reviews' => 'TrustSync',
    'easyflow-product-options' => 'EasyFlow',
    'betterdocs-knowledgebase' => 'BetterDocs FAQ'
];

$scraper = new UniversalLiveScraper();
$repository = new ReviewRepository();

foreach ($apps as $slug => $name) {
    echo "🎯 SCRAPING ALL HISTORICAL DATA FOR: $name\n";
    echo "App Slug: $slug\n";
    echo "Target: Complete review history (all pages)\n\n";
    
    try {
        // Use the scrapeAllReviews method for complete historical data
        $baseUrl = "https://apps.shopify.com/$slug/reviews";
        $allReviews = $scraper->scrapeAllReviews($baseUrl, $name);
        
        if (!empty($allReviews)) {
            echo "✅ Found " . count($allReviews) . " total reviews for $name\n";
            
            // Save all reviews to repository
            $saved = 0;
            foreach ($allReviews as $review) {
                try {
                    $repository->addReview(
                        $name,
                        $review['store_name'],
                        $review['country_name'],
                        $review['rating'],
                        $review['review_content'],
                        $review['review_date'],
                        'scrape'
                    );
                    $saved++;
                } catch (Exception $e) {
                    // Skip duplicates
                    if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                        echo "⚠️ Error saving review: " . $e->getMessage() . "\n";
                    }
                }
            }
            
            echo "💾 Saved $saved new reviews to repository\n";
            
            // Get final count for this app
            $apps = $repository->getAvailableApps();
            $finalCount = 0;
            foreach ($apps as $app) {
                if ($app['app_name'] === $name) {
                    $finalCount = $app['total_reviews'];
                    break;
                }
            }
            echo "📊 Total reviews in repository for $name: $finalCount\n\n";
            
        } else {
            echo "❌ No reviews found for $name\n\n";
        }
        
        // Add delay between apps to be respectful
        sleep(2);
        
    } catch (Exception $e) {
        echo "❌ Error scraping $name: " . $e->getMessage() . "\n\n";
    }
}

echo "🎉 HISTORICAL SCRAPING COMPLETE!\n";
echo "================================\n\n";

// Show final summary
$apps = $repository->getAvailableApps();
echo "📈 FINAL REVIEW COUNTS:\n";
foreach ($apps as $app) {
    echo "- {$app['app_name']}: {$app['total_reviews']} reviews\n";
}

echo "\n✨ All apps now have complete historical data!\n";
echo "The pagination system will now show all reviews for each app.\n";
?>
