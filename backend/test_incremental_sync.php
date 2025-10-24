<?php
/**
 * Test Incremental Sync System
 * 
 * This script tests the new incremental sync system for StoreSEO app
 * 
 * Usage: php backend/test_incremental_sync.php
 */

require_once __DIR__ . '/scraper/IncrementalSyncScraper.php';
require_once __DIR__ . '/config/database.php';

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║     INCREMENTAL SYNC SYSTEM - TEST SCRIPT                  ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Test 1: Check current StoreSEO count
    echo "\n📊 TEST 1: Current Database State\n";
    echo "─────────────────────────────────────\n";
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM reviews WHERE app_name = 'StoreSEO'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $currentCount = $result['count'];
    
    echo "Current StoreSEO reviews in DB: $currentCount\n";
    
    // Test 2: Run incremental sync
    echo "\n🔄 TEST 2: Running Incremental Sync\n";
    echo "─────────────────────────────────────\n";
    
    $scraper = new IncrementalSyncScraper();
    $startTime = microtime(true);
    $syncResult = $scraper->incrementalSync('storeseo', 'StoreSEO');
    $duration = round(microtime(true) - $startTime, 2);
    
    echo "\nSync Result:\n";
    echo "  Success: " . ($syncResult['success'] ? 'YES' : 'NO') . "\n";
    echo "  Message: " . $syncResult['message'] . "\n";
    echo "  New Reviews: " . $syncResult['new_reviews'] . "\n";
    echo "  Live Total: " . $syncResult['total_count'] . "\n";
    echo "  Duration: {$duration}s\n";
    
    // Test 3: Check updated count
    echo "\n📊 TEST 3: Updated Database State\n";
    echo "─────────────────────────────────────\n";
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM reviews WHERE app_name = 'StoreSEO'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $newCount = $result['count'];
    
    echo "Updated StoreSEO reviews in DB: $newCount\n";
    echo "Difference: " . ($newCount - $currentCount) . " new reviews\n";
    
    // Test 4: Verify live page total
    echo "\n🌐 TEST 4: Live Page Verification\n";
    echo "─────────────────────────────────────\n";
    
    echo "Live Shopify shows: " . $syncResult['total_count'] . " total reviews\n";
    echo "Database has: $newCount reviews\n";
    
    if ($newCount >= $syncResult['total_count'] * 0.95) {
        echo "✅ Count is within acceptable range (95%+)\n";
    } else {
        echo "⚠️ Count mismatch - may need full resync\n";
    }
    
    // Test 5: Show latest reviews
    echo "\n📝 TEST 5: Latest 5 Reviews\n";
    echo "─────────────────────────────────────\n";
    
    $stmt = $conn->prepare("
        SELECT store_name, rating, review_date, country_name
        FROM reviews
        WHERE app_name = 'StoreSEO'
        ORDER BY review_date DESC, created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($reviews as $i => $review) {
        echo ($i + 1) . ". {$review['store_name']} - {$review['rating']}★ ({$review['review_date']}) - {$review['country_name']}\n";
    }
    
    echo "\n✅ TEST COMPLETE\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n\n";
    exit(1);
}
?>

