<?php
/**
 * Diagnostic script to check StoreSEO review counts and sync status
 */

require_once __DIR__ . '/config/database.php';

echo "=== STORESEO REVIEW DIAGNOSTIC ===\n\n";

try {
    $db = new Database();
    $conn = $db->getConnection();

    // 1. Check main reviews table
    echo "1️⃣ MAIN REVIEWS TABLE:\n";
    $stmt = $conn->prepare('SELECT COUNT(*) FROM reviews WHERE app_name = ?');
    $stmt->execute(['StoreSEO']);
    $mainCount = $stmt->fetchColumn();
    echo "   Total StoreSEO reviews: $mainCount\n";

    // 2. Check access_reviews table
    echo "\n2️⃣ ACCESS REVIEWS TABLE:\n";
    $stmt = $conn->prepare('SELECT COUNT(*) FROM access_reviews WHERE app_name = ?');
    $stmt->execute(['StoreSEO']);
    $accessCount = $stmt->fetchColumn();
    echo "   Total StoreSEO reviews: $accessCount\n";

    // 3. Check last 30 days
    echo "\n3️⃣ LAST 30 DAYS (Main Table):\n";
    $stmt = $conn->prepare('SELECT COUNT(*) FROM reviews WHERE app_name = ? AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)');
    $stmt->execute(['StoreSEO']);
    $last30Count = $stmt->fetchColumn();
    echo "   Reviews in last 30 days: $last30Count\n";

    // 4. Check date range
    echo "\n4️⃣ DATE RANGE:\n";
    $stmt = $conn->prepare('SELECT MIN(review_date) as oldest, MAX(review_date) as newest FROM reviews WHERE app_name = ?');
    $stmt->execute(['StoreSEO']);
    $dates = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Oldest: {$dates['oldest']}\n";
    echo "   Newest: {$dates['newest']}\n";

    // 5. Check for orphaned reviews
    echo "\n5️⃣ ORPHANED REVIEWS (in access_reviews but not in reviews):\n";
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM access_reviews ar
        LEFT JOIN reviews r ON ar.original_review_id = r.id
        WHERE ar.app_name = ? AND r.id IS NULL
    ");
    $stmt->execute(['StoreSEO']);
    $orphanedCount = $stmt->fetchColumn();
    echo "   Orphaned reviews: $orphanedCount\n";

    // 6. Check is_active flag
    echo "\n6️⃣ IS_ACTIVE FLAG:\n";
    $stmt = $conn->prepare('SELECT COUNT(*) FROM reviews WHERE app_name = ? AND is_active = 1');
    $stmt->execute(['StoreSEO']);
    $activeCount = $stmt->fetchColumn();
    $stmt = $conn->prepare('SELECT COUNT(*) FROM reviews WHERE app_name = ? AND is_active = 0');
    $stmt->execute(['StoreSEO']);
    $inactiveCount = $stmt->fetchColumn();
    echo "   Active: $activeCount\n";
    echo "   Inactive: $inactiveCount\n";

    // 7. Sample reviews
    echo "\n7️⃣ SAMPLE REVIEWS (first 5):\n";
    $stmt = $conn->prepare('SELECT id, review_date, store_name, rating, is_active FROM reviews WHERE app_name = ? ORDER BY review_date DESC LIMIT 5');
    $stmt->execute(['StoreSEO']);
    $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($samples as $review) {
        echo "   ID: {$review['id']}, Date: {$review['review_date']}, Store: {$review['store_name']}, Rating: {$review['rating']}, Active: {$review['is_active']}\n";
    }

    echo "\n✅ Diagnostic complete\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>

