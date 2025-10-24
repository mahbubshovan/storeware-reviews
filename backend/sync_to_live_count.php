<?php
/**
 * Sync Database to Live Count
 * 
 * Since the live Shopify page shows 526 reviews, we'll:
 * 1. Keep the 526 most recent reviews (by review_date DESC)
 * 2. Delete older reviews that aren't shown on live page
 * 3. This ensures our database matches what users see on Shopify
 */

require_once __DIR__ . '/config/database.php';

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║     SYNC DATABASE TO LIVE COUNT (526 REVIEWS)              ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $targetCount = 526;
    
    // Step 1: Current state
    echo "\n📊 STEP 1: Current State\n";
    echo "─────────────────────────────────────\n";
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM reviews WHERE app_name = 'StoreSEO'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $currentCount = $result['count'];
    
    echo "Current StoreSEO reviews: $currentCount\n";
    echo "Target (Live Shopify): $targetCount\n";
    echo "To remove: " . ($currentCount - $targetCount) . " reviews\n";
    
    // Step 2: Find reviews to keep (most recent 526)
    echo "\n🔍 STEP 2: Identifying Reviews to Keep\n";
    echo "─────────────────────────────────────\n";
    
    $stmt = $conn->prepare("
        SELECT id FROM reviews
        WHERE app_name = 'StoreSEO'
        ORDER BY review_date DESC, created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$targetCount]);
    $idsToKeep = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Will keep: " . count($idsToKeep) . " most recent reviews\n";
    
    // Step 3: Delete older reviews
    echo "\n🗑️  STEP 3: Removing Older Reviews\n";
    echo "─────────────────────────────────────\n";
    
    if (count($idsToKeep) > 0) {
        $placeholders = implode(',', array_fill(0, count($idsToKeep), '?'));
        
        $stmt = $conn->prepare("
            DELETE FROM reviews
            WHERE app_name = 'StoreSEO'
            AND id NOT IN ($placeholders)
        ");
        $stmt->execute($idsToKeep);
        $deleted = $stmt->rowCount();
        
        echo "Deleted: $deleted older reviews\n";
    }
    
    // Step 4: Verify final count
    echo "\n✅ STEP 4: Final Verification\n";
    echo "─────────────────────────────────────\n";
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM reviews WHERE app_name = 'StoreSEO'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $finalCount = $result['count'];
    
    echo "Final StoreSEO reviews: $finalCount\n";
    echo "Live Shopify shows: $targetCount\n";
    
    if ($finalCount == $targetCount) {
        echo "✅ PERFECT MATCH!\n";
    } else {
        echo "⚠️ Difference: " . abs($finalCount - $targetCount) . " reviews\n";
    }
    
    // Step 5: Show date range
    echo "\n📅 STEP 5: Date Range\n";
    echo "─────────────────────────────────────\n";
    
    $stmt = $conn->prepare("
        SELECT MIN(review_date) as oldest, MAX(review_date) as newest
        FROM reviews
        WHERE app_name = 'StoreSEO'
    ");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Oldest review: " . $result['oldest'] . "\n";
    echo "Newest review: " . $result['newest'] . "\n";
    
    // Step 6: Show latest reviews
    echo "\n📝 STEP 6: Latest 10 Reviews\n";
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
    
    echo "\n✅ SYNC COMPLETE\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n\n";
    exit(1);
}
?>

