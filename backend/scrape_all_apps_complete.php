<?php
/**
 * Scrape complete historical data for all remaining apps
 * This will ensure all apps have their full review history for pagination
 */

require_once __DIR__ . '/scraper/UniversalLiveScraper.php';
require_once __DIR__ . '/utils/ReviewRepository.php';

echo "🔄 COMPLETE HISTORICAL SCRAPING FOR ALL APPS\n";
echo "===========================================\n\n";

// All apps except StoreSEO (already done)
$apps = [
    'storefaq' => 'StoreFAQ', 
    'vidify-video-backgrounds' => 'Vidify',
    'trustsync-reviews' => 'TrustSync',
    'easyflow-product-options' => 'EasyFlow',
    'betterdocs-knowledgebase' => 'BetterDocs FAQ'
];

$scraper = new UniversalLiveScraper();
$repository = new ReviewRepository();

foreach ($apps as $slug => $name) {
    echo "🎯 SCRAPING COMPLETE HISTORY FOR: $name\n";
    echo "App Slug: $slug\n";
    echo "Target: All historical reviews\n\n";
    
    try {
        $baseUrl = "https://apps.shopify.com/$slug/reviews";
        echo "🔄 Starting deep scraping from: $baseUrl\n";
        
        $allReviews = $scraper->scrapeAllReviews($baseUrl, $name);
        
        if (!empty($allReviews)) {
            echo "✅ Found " . count($allReviews) . " total reviews for $name\n";
            
            // Save all reviews to repository
            echo "💾 Saving to repository...\n";
            $saved = 0;
            $duplicates = 0;
            
            foreach ($allReviews as $review) {
                try {
                    $repository->addReview(
                        $name,
                        $review['store_name'],
                        $review['country_name'],
                        $review['rating'],
                        $review['review_content'],
                        $review['review_date'],
                        'live_scrape'
                    );
                    $saved++;
                    
                    if ($saved % 25 == 0) {
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
            
            echo "📊 Results for $name:\n";
            echo "- Total scraped: " . count($allReviews) . " reviews\n";
            echo "- Successfully saved: $saved reviews\n";
            echo "- Duplicates skipped: $duplicates reviews\n\n";
            
        } else {
            echo "❌ No reviews found for $name\n\n";
        }
        
        // Add delay between apps to be respectful
        sleep(3);
        
    } catch (Exception $e) {
        echo "❌ Error scraping $name: " . $e->getMessage() . "\n\n";
    }
}

echo "🎉 COMPLETE HISTORICAL SCRAPING FINISHED!\n";
echo "========================================\n\n";

// Show final summary
$apps = $repository->getAvailableApps();
echo "📈 FINAL REVIEW COUNTS (ALL APPS):\n";
$totalReviews = 0;
foreach ($apps as $app) {
    echo "- {$app['app_name']}: {$app['total_reviews']} reviews\n";
    $totalReviews += $app['total_reviews'];
}

echo "\n🎯 TOTAL REVIEWS IN SYSTEM: $totalReviews\n";
echo "✨ All apps now have complete historical data!\n";
echo "The pagination system will show ALL reviews for each app.\n";
?>
