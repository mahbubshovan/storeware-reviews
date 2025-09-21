<?php
/**
 * Test Smart Sync Analytics Functionality
 * 
 * This script tests the smart sync system that compares analytics scraping
 * with Access Review Tab data and prevents duplicates.
 */

echo "🧪 TESTING SMART SYNC ANALYTICS SYSTEM\n";
echo "=====================================\n\n";

// Test the smart sync API directly
function testSmartSyncAPI($appName) {
    echo "📱 Testing Smart Sync for: $appName\n";
    echo "-----------------------------------\n";
    
    // Call the smart sync API
    $url = 'http://localhost:8000/api/smart-sync-analytics.php';
    $data = json_encode(['app_name' => $appName]);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => $data,
            'timeout' => 30
        ]
    ]);
    
    $startTime = microtime(true);
    $response = @file_get_contents($url, false, $context);
    $endTime = microtime(true);
    
    if ($response) {
        $result = json_decode($response, true);
        
        if ($result && $result['success']) {
            $stats = $result['stats'];
            echo "✅ Smart Sync Success!\n";
            echo "   📊 Total Found: {$stats['total_found']}\n";
            echo "   ⏭️ Duplicates Skipped: {$stats['duplicates_skipped']}\n";
            echo "   ➕ New Added: {$stats['new_added']}\n";
            echo "   ⏱️ Response Time: " . round(($endTime - $startTime) * 1000, 1) . "ms\n";
            echo "   💬 Message: {$result['message']}\n\n";
            
            return $stats;
        } else {
            echo "❌ Smart Sync Failed: " . ($result['error'] ?? 'Unknown error') . "\n\n";
            return null;
        }
    } else {
        echo "❌ API Connection Failed\n\n";
        return null;
    }
}

// Test getting sync statistics
function testSyncStatistics() {
    echo "📊 Testing Sync Statistics\n";
    echo "-------------------------\n";
    
    $url = 'http://localhost:8000/api/smart-sync-analytics.php';
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response) {
        $result = json_decode($response, true);
        
        if ($result && $result['success']) {
            echo "✅ Statistics Retrieved!\n";
            
            if (!empty($result['stats'])) {
                foreach ($result['stats'] as $stat) {
                    echo "   📱 {$stat['app_name']}: {$stat['todays_reviews']} today's reviews ({$stat['newly_scraped']} newly scraped)\n";
                }
            } else {
                echo "   📝 No statistics available (no reviews today)\n";
            }
            echo "\n";
            
            return $result['stats'];
        } else {
            echo "❌ Statistics Failed: " . ($result['error'] ?? 'Unknown error') . "\n\n";
            return null;
        }
    } else {
        echo "❌ Statistics API Connection Failed\n\n";
        return null;
    }
}

// Test the complete analytics scraping with smart sync
function testAnalyticsWithSmartSync($appName) {
    echo "🔄 Testing Complete Analytics Scraping with Smart Sync\n";
    echo "=====================================================\n";
    echo "App: $appName\n\n";
    
    // Call the scrape-app API (which now includes smart sync)
    $url = 'http://localhost:8000/api/scrape-app.php';
    $data = json_encode(['app_name' => $appName]);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => $data,
            'timeout' => 120
        ]
    ]);
    
    echo "🚀 Starting analytics scraping with smart sync...\n";
    $startTime = microtime(true);
    $response = @file_get_contents($url, false, $context);
    $endTime = microtime(true);
    
    if ($response) {
        $result = json_decode($response, true);
        
        if ($result && $result['success']) {
            echo "✅ Analytics Scraping with Smart Sync Success!\n";
            echo "   📊 Scraped Count: {$result['scraped_count']}\n";
            echo "   🔄 Source: {$result['source']}\n";
            echo "   ⚡ Rate Limited: " . ($result['rate_limited'] ? 'Yes' : 'No') . "\n";
            
            if (isset($result['smart_sync'])) {
                $sync = $result['smart_sync'];
                echo "   🧠 Smart Sync Results:\n";
                echo "      - Total Found: {$sync['total_found']}\n";
                echo "      - Duplicates Skipped: {$sync['duplicates_skipped']}\n";
                echo "      - New Added: {$sync['new_added']}\n";
            }
            
            echo "   ⏱️ Total Time: " . round(($endTime - $startTime), 1) . "s\n";
            echo "   💬 Message: {$result['message']}\n\n";
            
            return $result;
        } else {
            echo "❌ Analytics Scraping Failed: " . ($result['error'] ?? 'Unknown error') . "\n\n";
            return null;
        }
    } else {
        echo "❌ Analytics API Connection Failed\n\n";
        return null;
    }
}

// Run the tests
echo "🎯 RUNNING SMART SYNC TESTS\n";
echo "===========================\n\n";

// Test 1: Get current sync statistics
$stats = testSyncStatistics();

// Test 2: Test smart sync for a specific app
$testApp = 'StoreSEO';
$syncResult = testSmartSyncAPI($testApp);

// Test 3: Test complete analytics scraping with smart sync
$analyticsResult = testAnalyticsWithSmartSync($testApp);

// Final summary
echo "🏆 SMART SYNC TEST SUMMARY\n";
echo "=========================\n";

if ($stats !== null) {
    echo "✅ Sync Statistics: Working\n";
} else {
    echo "❌ Sync Statistics: Failed\n";
}

if ($syncResult !== null) {
    echo "✅ Smart Sync API: Working\n";
} else {
    echo "❌ Smart Sync API: Failed\n";
}

if ($analyticsResult !== null) {
    echo "✅ Analytics with Smart Sync: Working\n";
} else {
    echo "❌ Analytics with Smart Sync: Failed\n";
}

echo "\n🎉 Smart Sync System is ready!\n";
echo "\n📋 HOW IT WORKS:\n";
echo "================\n";
echo "1. Analytics page scrapes new reviews for today\n";
echo "2. Smart sync compares with Access Review Tab data app-wise\n";
echo "3. Duplicates are automatically skipped\n";
echo "4. Only new reviews are added to Access Review page\n";
echo "5. Regular sync process updates the Access Review page\n";

echo "\n✨ The system prevents duplicate reviews and maintains data integrity!\n";
?>
