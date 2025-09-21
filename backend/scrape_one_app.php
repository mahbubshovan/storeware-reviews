<?php
/**
 * Scrape one specific app with real data
 */

if ($argc < 3) {
    echo "Usage: php scrape_one_app.php <app_name> <app_slug>\n";
    echo "Example: php scrape_one_app.php 'StoreFAQ' 'storefaq'\n";
    exit(1);
}

$appName = $argv[1];
$appSlug = $argv[2];

require_once __DIR__ . '/scraper/UniversalLiveScraper.php';
require_once __DIR__ . '/utils/ReviewRepository.php';

echo "🌐 SCRAPING REAL DATA FOR $appName\n";
echo str_repeat("=", 50) . "\n";
echo "App: $appName\n";
echo "Slug: $appSlug\n";
echo "URL: https://apps.shopify.com/$appSlug\n\n";

try {
    // Clear existing data
    $repository = new ReviewRepository();
    $cleared = $repository->clearAppData($appName);
    echo "🗑️  Cleared $cleared existing reviews for $appName\n\n";
    
    // Scrape fresh data
    $scraper = new UniversalLiveScraper();
    echo "🔄 Starting live scrape...\n";
    
    $result = $scraper->scrapeApp($appSlug, $appName);
    
    if ($result && !empty($result['reviews'])) {
        $reviewCount = count($result['reviews']);
        echo "\n✅ Successfully scraped $reviewCount reviews for $appName\n";
        
        // Verify the results
        echo "\n📊 VERIFICATION:\n";
        echo str_repeat("-", 30) . "\n";
        
        $stats = $repository->getStatistics($appName);
        $totalReviews = $stats['total_reviews'] ?? 0;
        $avgRating = $stats['average_rating'] ?? 0;
        
        // Get last 30 days count
        $last30Days = $repository->getReviewsCount($appName, 30);
        
        // Get this month count  
        $thisMonth = $repository->getReviewsCount($appName, null, date('Y-m-01'));
        
        echo "Total Reviews: $totalReviews\n";
        echo "Average Rating: $avgRating stars\n";
        echo "Last 30 Days: $last30Days reviews\n";
        echo "This Month: $thisMonth reviews\n";
        
        // Show recent reviews
        $recentReviews = $repository->getRecentReviews($appName, 3);
        if (!empty($recentReviews)) {
            echo "\nRecent Reviews:\n";
            foreach ($recentReviews as $review) {
                echo "  " . $review['review_date'] . " - " . $review['rating'] . "★ - " . $review['store_name'] . "\n";
            }
        }
        
        echo "\n🎉 $appName scraping completed successfully!\n";
        
    } else {
        echo "❌ Failed to scrape data for $appName\n";
        if (isset($result['error'])) {
            echo "Error: " . $result['error'] . "\n";
        }
        exit(1);
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
