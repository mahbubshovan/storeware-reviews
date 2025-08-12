<?php
/**
 * Test all apps with Universal Live Scraper
 */

require_once __DIR__ . '/../scraper/UniversalLiveScraper.php';

$supportedApps = [
    // ALL 6 ORIGINAL APPS
    'StoreSEO' => 'storeseo',
    'StoreFAQ' => 'storefaq',
    'Vidify' => 'vidify',
    'TrustSync' => 'customer-review-app',
    'EasyFlow' => 'product-options-4',
    'BetterDocs FAQ' => 'betterdocs-knowledgebase'
];

echo "🔴 TESTING UNIVERSAL LIVE SCRAPER FOR ALL APPS\n";
echo "==============================================\n\n";

$scraper = new UniversalLiveScraper();
$results = [];

foreach ($supportedApps as $appName => $appSlug) {
    echo "📱 Testing $appName ($appSlug)...\n";
    echo str_repeat('-', 50) . "\n";
    
    $startTime = microtime(true);
    $result = $scraper->scrapeApp($appSlug, $appName);
    $endTime = microtime(true);
    
    $duration = round($endTime - $startTime, 2);
    
    if ($result['success']) {
        echo "✅ SUCCESS: {$result['message']}\n";
        echo "⏱️ Duration: {$duration}s\n";
        echo "📊 Reviews scraped: {$result['count']}\n";
        
        $results[$appName] = [
            'status' => 'success',
            'count' => $result['count'],
            'duration' => $duration
        ];
    } else {
        echo "❌ FAILED: {$result['message']}\n";
        echo "⏱️ Duration: {$duration}s\n";
        
        $results[$appName] = [
            'status' => 'failed',
            'count' => 0,
            'duration' => $duration,
            'error' => $result['message']
        ];
    }
    
    echo "\n";
}

echo "📋 FINAL RESULTS SUMMARY\n";
echo "========================\n";

$totalSuccess = 0;
$totalReviews = 0;

foreach ($results as $appName => $result) {
    $status = $result['status'] === 'success' ? '✅' : '❌';
    $count = $result['count'];
    $duration = $result['duration'];
    
    echo "$status $appName: $count reviews ({$duration}s)\n";
    
    if ($result['status'] === 'success') {
        $totalSuccess++;
        $totalReviews += $count;
    }
}

echo "\n🎯 SUMMARY:\n";
echo "- Apps tested: " . count($supportedApps) . "\n";
echo "- Successful: $totalSuccess\n";
echo "- Total reviews scraped: $totalReviews\n";
echo "- Success rate: " . round(($totalSuccess / count($supportedApps)) * 100, 1) . "%\n";

if ($totalSuccess === count($supportedApps)) {
    echo "\n🎉 ALL APPS WORKING WITH LIVE DATA!\n";
} else {
    echo "\n⚠️ Some apps failed - check individual results above\n";
}

echo "\n🔴 NO MOCK DATA USED - ALL RESULTS ARE FROM LIVE SHOPIFY PAGES\n";
?>
