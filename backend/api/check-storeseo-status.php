<?php
/**
 * Check StoreSEO Status
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

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
    
    echo json_encode([
        'success' => true,
        'main_reviews' => $mainCount,
        'access_reviews' => $accessCount,
        'last_30_days' => $last30Count,
        'date_range' => $dates,
        'rating_distribution' => $distribution,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>

