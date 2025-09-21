<?php
/**
 * Complete System Test for Review Count Discrepancy Fix and Smart Caching
 * Tests all apps for accurate review counts and caching functionality
 */

echo "🧪 COMPLETE SYSTEM TEST - REVIEW COUNT & CACHING\n";
echo "===============================================\n\n";

// All supported apps
$apps = [
    'StoreSEO' => ['expected' => 519, 'url' => 'https://apps.shopify.com/storeseo/reviews'],
    'StoreFAQ' => ['expected' => 96, 'url' => 'https://apps.shopify.com/storefaq/reviews'],
    'EasyFlow' => ['expected' => 308, 'url' => 'https://apps.shopify.com/product-options-4/reviews'],
    'TrustSync' => ['expected' => 45, 'url' => 'https://apps.shopify.com/customer-review-app/reviews'],
    'BetterDocs FAQ Knowledge Base' => ['expected' => 33, 'url' => 'https://apps.shopify.com/betterdocs-knowledgebase/reviews'],
    'Vidify' => ['expected' => 28, 'url' => 'https://apps.shopify.com/vidify/reviews']
];

$results = [];
$totalStartTime = microtime(true);

echo "🎯 PHASE 1: INITIAL SCRAPING (Cache Miss)\n";
echo "=========================================\n";

foreach ($apps as $appName => $appInfo) {
    echo "\n📱 Testing $appName:\n";
    echo "   Expected: {$appInfo['expected']} reviews\n";
    echo "   URL: {$appInfo['url']}\n";
    
    $startTime = microtime(true);
    
    // Test the cached API
    $apiUrl = "http://localhost:8000/api/access-reviews-cached.php?app=" . urlencode($appName) . "&page=1&limit=10";
    $response = @file_get_contents($apiUrl);
    
    $endTime = microtime(true);
    $executionTime = round(($endTime - $startTime) * 1000, 1);
    
    if ($response) {
        $data = json_decode($response, true);
        
        if ($data && $data['success']) {
            $actualCount = $data['data']['statistics']['total_reviews'];
            $cacheStatus = $data['data']['statistics']['cache_status'];
            $assignedCount = $data['data']['statistics']['assigned_reviews'];
            $unassignedCount = $data['data']['statistics']['unassigned_reviews'];
            
            $accuracy = $actualCount == $appInfo['expected'] ? '✅ PERFECT' : 
                       ($actualCount > $appInfo['expected'] ? '⚠️ +' . ($actualCount - $appInfo['expected']) : 
                        '❌ -' . ($appInfo['expected'] - $actualCount));
            
            echo "   📊 Result: $actualCount reviews ($accuracy)\n";
            echo "   🕒 Cache: $cacheStatus\n";
            echo "   👥 Assigned: $assignedCount | Unassigned: $unassignedCount\n";
            echo "   ⏱️ Time: {$executionTime}ms\n";
            
            $results[$appName] = [
                'success' => true,
                'actual' => $actualCount,
                'expected' => $appInfo['expected'],
                'cache_status' => $cacheStatus,
                'execution_time' => $executionTime,
                'accuracy' => $accuracy
            ];
        } else {
            echo "   ❌ API Error: " . ($data['error'] ?? 'Unknown error') . "\n";
            $results[$appName] = ['success' => false, 'error' => $data['error'] ?? 'Unknown error'];
        }
    } else {
        echo "   ❌ Failed to connect to API\n";
        $results[$appName] = ['success' => false, 'error' => 'Connection failed'];
    }
    
    // Small delay between requests
    sleep(1);
}

echo "\n\n🚀 PHASE 2: CACHE HIT TESTING (Fast Tab Switching)\n";
echo "==================================================\n";

$cacheTestApps = ['StoreSEO', 'StoreFAQ', 'EasyFlow'];
$cacheResults = [];

foreach ($cacheTestApps as $appName) {
    echo "\n⚡ Testing cache hit for $appName:\n";
    
    $startTime = microtime(true);
    $apiUrl = "http://localhost:8000/api/access-reviews-cached.php?app=" . urlencode($appName) . "&page=1&limit=5";
    $response = @file_get_contents($apiUrl);
    $endTime = microtime(true);
    
    $executionTime = round(($endTime - $startTime) * 1000, 1);
    
    if ($response) {
        $data = json_decode($response, true);
        if ($data && $data['success']) {
            $cacheStatus = $data['data']['statistics']['cache_status'];
            echo "   🕒 Cache Status: $cacheStatus\n";
            echo "   ⚡ Speed: {$executionTime}ms\n";
            
            $cacheResults[$appName] = [
                'cache_status' => $cacheStatus,
                'speed' => $executionTime
            ];
        }
    }
}

echo "\n\n📊 FINAL RESULTS SUMMARY\n";
echo "========================\n";

$totalEndTime = microtime(true);
$totalTime = round(($totalEndTime - $totalStartTime) / 60, 1);

echo "🕒 Total Test Time: {$totalTime} minutes\n\n";

// Accuracy Summary
$perfectCount = 0;
$totalApps = count($apps);

echo "🎯 ACCURACY RESULTS:\n";
foreach ($results as $appName => $result) {
    if ($result['success']) {
        echo "   $appName: {$result['accuracy']}\n";
        if (strpos($result['accuracy'], '✅') !== false) {
            $perfectCount++;
        }
    } else {
        echo "   $appName: ❌ FAILED - {$result['error']}\n";
    }
}

$accuracyRate = round(($perfectCount / $totalApps) * 100, 1);
echo "\n📈 Overall Accuracy: $perfectCount/$totalApps apps perfect ($accuracyRate%)\n";

// Cache Performance Summary
echo "\n⚡ CACHE PERFORMANCE:\n";
$fastSwitches = 0;
foreach ($cacheResults as $appName => $cacheResult) {
    $status = $cacheResult['cache_status'] === 'hit' ? '✅ HIT' : '❌ MISS';
    $speed = $cacheResult['speed'] < 500 ? '🚀 FAST' : '🐌 SLOW';
    echo "   $appName: $status ($speed - {$cacheResult['speed']}ms)\n";
    
    if ($cacheResult['cache_status'] === 'hit' && $cacheResult['speed'] < 500) {
        $fastSwitches++;
    }
}

$cacheEfficiency = round(($fastSwitches / count($cacheResults)) * 100, 1);
echo "\n🎯 Cache Efficiency: $fastSwitches/" . count($cacheResults) . " fast switches ($cacheEfficiency%)\n";

// Final Assessment
echo "\n🏆 SYSTEM ASSESSMENT:\n";
echo "====================\n";

if ($accuracyRate >= 80 && $cacheEfficiency >= 80) {
    echo "✅ EXCELLENT: System is working perfectly!\n";
    echo "   - Review counts are accurate\n";
    echo "   - Caching provides fast tab switching\n";
    echo "   - Ready for production use\n";
} elseif ($accuracyRate >= 60 && $cacheEfficiency >= 60) {
    echo "⚠️ GOOD: System is mostly working with minor issues\n";
    echo "   - Most review counts are accurate\n";
    echo "   - Caching is mostly effective\n";
    echo "   - May need minor adjustments\n";
} else {
    echo "❌ NEEDS WORK: System has significant issues\n";
    echo "   - Review count accuracy needs improvement\n";
    echo "   - Caching performance needs optimization\n";
    echo "   - Requires further development\n";
}

echo "\n🎉 Test completed!\n";
?>
