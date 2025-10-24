<?php
/**
 * Fix StoreSEO Review Count Mismatch
 * 
 * Current state:
 * - Live Shopify: 526 reviews
 * - Database: 557 reviews (31 extra)
 * 
 * Solution:
 * 1. Clear all StoreSEO reviews from database
 * 2. Perform full incremental sync to get exact 526 reviews
 * 3. Verify count matches live page
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/scraper/IncrementalSyncScraper.php';

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║     FIX STORESEO REVIEW COUNT MISMATCH                     ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Step 1: Show current state
    echo "\n📊 STEP 1: Current State\n";
    echo "─────────────────────────────────────\n";
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM reviews WHERE app_name = 'StoreSEO'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $currentCount = $result['count'];
    
    echo "Current StoreSEO reviews in DB: $currentCount\n";
    echo "Live Shopify shows: 526 reviews\n";
    echo "Difference: " . ($currentCount - 526) . " extra reviews\n";
    
    // Step 2: Clear all StoreSEO reviews
    echo "\n🗑️  STEP 2: Clearing All StoreSEO Reviews\n";
    echo "─────────────────────────────────────\n";
    
    $stmt = $conn->prepare("DELETE FROM reviews WHERE app_name = 'StoreSEO'");
    $stmt->execute();
    $deletedCount = $stmt->rowCount();
    
    echo "Deleted: $deletedCount reviews\n";
    
    // Verify deletion
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM reviews WHERE app_name = 'StoreSEO'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $afterDelete = $result['count'];
    
    echo "Remaining: $afterDelete reviews\n";
    
    // Step 3: Perform full sync
    echo "\n🔄 STEP 3: Performing Full Incremental Sync\n";
    echo "─────────────────────────────────────\n";
    
    $scraper = new IncrementalSyncScraper();
    $startTime = microtime(true);
    $syncResult = $scraper->incrementalSync('storeseo', 'StoreSEO');
    $duration = round(microtime(true) - $startTime, 2);
    
    echo "\nSync Result:\n";
    echo "  Success: " . ($syncResult['success'] ? 'YES' : 'NO') . "\n";
    echo "  Message: " . $syncResult['message'] . "\n";
    echo "  Reviews Synced: " . $syncResult['count'] . "\n";
    echo "  Live Total: " . $syncResult['total_count'] . "\n";
    echo "  Duration: {$duration}s\n";
    
    // Step 4: Verify final count
    echo "\n✅ STEP 4: Final Verification\n";
    echo "─────────────────────────────────────\n";
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM reviews WHERE app_name = 'StoreSEO'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $finalCount = $result['count'];
    
    echo "Final StoreSEO reviews in DB: $finalCount\n";
    echo "Live Shopify shows: " . $syncResult['total_count'] . " reviews\n";
    
    if ($finalCount == $syncResult['total_count']) {
        echo "✅ PERFECT MATCH! Count is now accurate.\n";
    } else {
        $diff = abs($finalCount - $syncResult['total_count']);
        echo "⚠️ Difference: $diff reviews\n";
        echo "   This may be due to reviews being added/removed on live page during sync.\n";
    }
    
    // Step 5: Show latest reviews
    echo "\n📝 STEP 5: Latest 10 Reviews\n";
    echo "─────────────────────────────────────\n";
    
    $stmt = $conn->prepare("
        SELECT store_name, rating, review_date, country_name
        FROM reviews
        WHERE app_name = 'StoreSEO'
        ORDER BY review_date DESC, created_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($reviews as $i => $review) {
        echo ($i + 1) . ". {$review['store_name']} - {$review['rating']}★ ({$review['review_date']}) - {$review['country_name']}\n";
    }
    
    echo "\n✅ FIX COMPLETE\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n\n";
    exit(1);
}
?>

