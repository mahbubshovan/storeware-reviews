<?php
/**
 * Verify StoreSEO Fix - Check current database state
 */

require_once 'config/database.php';

echo "=== STORESEO FIX VERIFICATION ===\n\n";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get counts
    $stmt = $conn->prepare('SELECT COUNT(*) FROM reviews WHERE app_name = ?');
    $stmt->execute(['StoreSEO']);
    $mainCount = $stmt->fetchColumn();
    
    $stmt = $conn->prepare('SELECT COUNT(*) FROM access_reviews WHERE app_name = ?');
    $stmt->execute(['StoreSEO']);
    $accessCount = $stmt->fetchColumn();
    
    $stmt = $conn->prepare('SELECT COUNT(*) FROM reviews WHERE app_name = ? AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)');
    $stmt->execute(['StoreSEO']);
    $last30Count = $stmt->fetchColumn();
    
    // Get date range
    $stmt = $conn->prepare('SELECT MIN(review_date) as oldest, MAX(review_date) as newest FROM reviews WHERE app_name = ?');
    $stmt->execute(['StoreSEO']);
    $dates = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get rating distribution
    $stmt = $conn->prepare('
        SELECT 
            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
        FROM reviews WHERE app_name = ?
    ');
    $stmt->execute(['StoreSEO']);
    $distribution = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "CURRENT STATUS:\n";
    echo "  Main reviews: $mainCount\n";
    echo "  Access reviews: $accessCount\n";
    echo "  Last 30 days: $last30Count\n";
    echo "  Date range: {$dates['oldest']} to {$dates['newest']}\n";
    echo "\nRATING DISTRIBUTION:\n";
    echo "  5★: {$distribution['five_star']}\n";
    echo "  4★: {$distribution['four_star']}\n";
    echo "  3★: {$distribution['three_star']}\n";
    echo "  2★: {$distribution['two_star']}\n";
    echo "  1★: {$distribution['one_star']}\n";
    
    // Check if we need to run the fix
    if ($mainCount < 100) {
        echo "\n⚠️ WARNING: Only $mainCount reviews found (expected 527+)\n";
        echo "Run: http://localhost:8000/api/fix-storeseo-complete.php\n";
    } else {
        echo "\n✅ SUCCESS: $mainCount reviews found (expected 527+)\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>

