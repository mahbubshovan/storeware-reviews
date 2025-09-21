<?php
/**
 * Test script for the improved scraper
 * Tests review count accuracy and caching functionality
 */

require_once 'scraper/ImprovedShopifyReviewScraper.php';

echo "🧪 TESTING IMPROVED SHOPIFY REVIEW SCRAPER\n";
echo "==========================================\n\n";

$scraper = new ImprovedShopifyReviewScraper();

// Test StoreSEO first (the problematic one)
$appName = 'StoreSEO';
echo "🎯 Testing $appName (Expected: 519 reviews)\n";
echo "============================================\n";

$startTime = microtime(true);
$result = $scraper->getReviewsWithCaching($appName);
$endTime = microtime(true);

if ($result['success']) {
    echo "✅ SUCCESS!\n";
    echo "📊 Total Reviews: " . $result['total_reviews'] . "\n";
    echo "🕒 Cache Status: " . $result['cache_status'] . "\n";
    echo "⏱️ Execution Time: " . round($endTime - $startTime, 2) . " seconds\n";
    echo "📅 Scraped At: " . $result['scraped_at'] . "\n";
    echo "⏰ Expires At: " . $result['expires_at'] . "\n";
    
    // Show sample reviews
    echo "\n📝 Sample Reviews (first 3):\n";
    $sampleReviews = array_slice($result['data'], 0, 3);
    foreach ($sampleReviews as $i => $review) {
        echo "   " . ($i + 1) . ". Store: {$review['store_name']}\n";
        echo "      Rating: {$review['rating']}★\n";
        echo "      Date: {$review['review_date']}\n";
        echo "      Content: " . substr($review['review_content'], 0, 100) . "...\n\n";
    }
    
    // Test caching - run again immediately
    echo "🔄 Testing Cache Hit (running again immediately):\n";
    $cacheStartTime = microtime(true);
    $cacheResult = $scraper->getReviewsWithCaching($appName);
    $cacheEndTime = microtime(true);
    
    if ($cacheResult['success']) {
        echo "✅ Cache Test SUCCESS!\n";
        echo "📊 Total Reviews: " . $cacheResult['total_reviews'] . "\n";
        echo "🕒 Cache Status: " . $cacheResult['cache_status'] . "\n";
        echo "⏱️ Execution Time: " . round($cacheEndTime - $cacheStartTime, 2) . " seconds\n";
        echo "🚀 Speed Improvement: " . round(($endTime - $startTime) / ($cacheEndTime - $cacheStartTime), 1) . "x faster\n";
    }
    
} else {
    echo "❌ FAILED: " . $result['error'] . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🎯 ACCURACY CHECK\n";
echo str_repeat("=", 50) . "\n";

$expectedCounts = [
    'StoreSEO' => 519,
    'StoreFAQ' => 96,  // Update these based on current live counts
    'EasyFlow' => 308,
    'BetterDocs FAQ Knowledge Base' => 33
];

foreach ($expectedCounts as $app => $expected) {
    echo "\n📱 Testing $app (Expected: $expected)\n";
    
    $testStart = microtime(true);
    $testResult = $scraper->getReviewsWithCaching($app);
    $testEnd = microtime(true);
    
    if ($testResult['success']) {
        $actual = $testResult['total_reviews'];
        $difference = $actual - $expected;
        $accuracy = $difference === 0 ? "✅ PERFECT" : ($difference > 0 ? "⚠️ +" . $difference : "❌ " . $difference);
        
        echo "   Result: $actual reviews ($accuracy)\n";
        echo "   Cache: {$testResult['cache_status']}\n";
        echo "   Time: " . round($testEnd - $testStart, 2) . "s\n";
    } else {
        echo "   ❌ Failed: " . $testResult['error'] . "\n";
    }
}

echo "\n🏁 Test completed!\n";
