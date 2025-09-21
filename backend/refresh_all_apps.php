<?php
/**
 * Refresh all apps with fresh data from Shopify App Store
 * This ensures all apps have current, accurate review data
 */

require_once __DIR__ . '/scraper/UniversalLiveScraper.php';

echo "🔄 REFRESHING ALL APPS WITH FRESH DATA\n";
echo "=====================================\n\n";

// All 6 supported apps with their verified Shopify slugs
$apps = [
    'StoreSEO' => 'storeseo',
    'StoreFAQ' => 'storefaq', 
    'Vidify' => 'vidify',
    'TrustSync' => 'customer-review-app',
    'EasyFlow' => 'product-options-4',
    'BetterDocs FAQ' => 'betterdocs-knowledgebase'
];

$scraper = new UniversalLiveScraper();
$results = [];

foreach ($apps as $appName => $appSlug) {
    echo "🎯 Processing: $appName ($appSlug)\n";
    echo str_repeat('-', 50) . "\n";
    
    $startTime = microtime(true);
    $result = $scraper->scrapeApp($appSlug, $appName);
    $endTime = microtime(true);
    
    $duration = round($endTime - $startTime, 2);
    
    $results[$appName] = [
        'success' => $result['success'],
        'message' => $result['message'],
        'count' => $result['count'] ?? 0,
        'duration' => $duration
    ];
    
    if ($result['success']) {
        echo "✅ SUCCESS: {$result['message']} (took {$duration}s)\n";
    } else {
        echo "❌ FAILED: {$result['message']} (took {$duration}s)\n";
    }
    
    echo "\n";
}

echo "📊 SUMMARY REPORT\n";
echo "================\n";

$totalSuccess = 0;
$totalReviews = 0;
$totalTime = 0;

foreach ($results as $appName => $result) {
    $status = $result['success'] ? '✅' : '❌';
    echo "$status $appName: {$result['count']} reviews ({$result['duration']}s)\n";
    
    if ($result['success']) {
        $totalSuccess++;
        $totalReviews += $result['count'];
    }
    $totalTime += $result['duration'];
}

echo "\n";
echo "📈 TOTALS:\n";
echo "- Apps processed: " . count($apps) . "\n";
echo "- Successful: $totalSuccess\n";
echo "- Failed: " . (count($apps) - $totalSuccess) . "\n";
echo "- Total reviews: $totalReviews\n";
echo "- Total time: " . round($totalTime, 2) . "s\n";

if ($totalSuccess === count($apps)) {
    echo "\n🎉 ALL APPS SUCCESSFULLY REFRESHED!\n";
    echo "The dashboard now shows current, accurate data from Shopify App Store.\n";
} else {
    echo "\n⚠️ Some apps failed to refresh. Check the logs above for details.\n";
}

echo "\n✨ Fresh data synchronization complete!\n";
?>
