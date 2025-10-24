<?php
/**
 * Deduplicate StoreSEO Reviews
 * 
 * Removes duplicate reviews based on store_name, review_date, and rating
 * Keeps only the first occurrence of each duplicate
 */

require_once __DIR__ . '/config/database.php';

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║     DEDUPLICATE STORESEO REVIEWS                           ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Step 1: Find duplicates
    echo "\n🔍 STEP 1: Finding Duplicates\n";
    echo "─────────────────────────────────────\n";
    
    $stmt = $conn->prepare("
        SELECT store_name, review_date, rating, COUNT(*) as count
        FROM reviews
        WHERE app_name = 'StoreSEO'
        GROUP BY store_name, review_date, rating
        HAVING count > 1
        ORDER BY count DESC
    ");
    $stmt->execute();
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($duplicates) . " duplicate groups\n";
    
    $totalDuplicates = 0;
    foreach ($duplicates as $dup) {
        $extra = $dup['count'] - 1;
        $totalDuplicates += $extra;
        echo "  - {$dup['store_name']} ({$dup['review_date']}, {$dup['rating']}★): {$dup['count']} copies, removing $extra\n";
    }
    
    echo "\nTotal duplicate records to remove: $totalDuplicates\n";
    
    // Step 2: Remove duplicates (keep first, delete rest)
    echo "\n🗑️  STEP 2: Removing Duplicates\n";
    echo "─────────────────────────────────────\n";
    
    $removed = 0;
    foreach ($duplicates as $dup) {
        // Get all IDs for this duplicate group
        $stmt = $conn->prepare("
            SELECT id FROM reviews
            WHERE app_name = 'StoreSEO'
            AND store_name = ?
            AND review_date = ?
            AND rating = ?
            ORDER BY id ASC
        ");
        $stmt->execute([
            $dup['store_name'],
            $dup['review_date'],
            $dup['rating']
        ]);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Keep first, delete rest
        if (count($ids) > 1) {
            $idsToDelete = array_slice($ids, 1);
            $placeholders = implode(',', array_fill(0, count($idsToDelete), '?'));
            
            $stmt = $conn->prepare("
                DELETE FROM reviews
                WHERE id IN ($placeholders)
            ");
            $stmt->execute($idsToDelete);
            $removed += count($idsToDelete);
        }
    }
    
    echo "Removed: $removed duplicate records\n";
    
    // Step 3: Verify final count
    echo "\n✅ STEP 3: Final Verification\n";
    echo "─────────────────────────────────────\n";
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM reviews WHERE app_name = 'StoreSEO'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $finalCount = $result['count'];
    
    echo "Final StoreSEO reviews in DB: $finalCount\n";
    echo "Live Shopify shows: 526 reviews\n";
    
    if ($finalCount == 526) {
        echo "✅ PERFECT MATCH!\n";
    } else {
        $diff = abs($finalCount - 526);
        echo "⚠️ Difference: $diff reviews\n";
    }
    
    // Step 4: Show latest reviews
    echo "\n📝 STEP 4: Latest 10 Reviews\n";
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
    
    echo "\n✅ DEDUPLICATION COMPLETE\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n\n";
    exit(1);
}
?>

