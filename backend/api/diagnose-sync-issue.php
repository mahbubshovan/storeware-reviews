<?php
/**
 * Diagnose the 10-review limit issue in access_reviews
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $diagnosis = [];
    
    // 1. Check main reviews table
    $stmt = $conn->prepare("
        SELECT app_name, COUNT(*) as count
        FROM reviews
        GROUP BY app_name
        ORDER BY count DESC
    ");
    $stmt->execute();
    $mainCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $diagnosis['main_reviews_by_app'] = $mainCounts;
    
    // 2. Check access_reviews table
    $stmt = $conn->prepare("
        SELECT app_name, COUNT(*) as count
        FROM access_reviews
        GROUP BY app_name
        ORDER BY count DESC
    ");
    $stmt->execute();
    $accessCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $diagnosis['access_reviews_by_app'] = $accessCounts;
    
    // 3. Check last 30 days in main reviews
    $stmt = $conn->prepare("
        SELECT app_name, COUNT(*) as count
        FROM reviews
        WHERE review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY app_name
        ORDER BY count DESC
    ");
    $stmt->execute();
    $last30Counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $diagnosis['main_reviews_last_30_days'] = $last30Counts;
    
    // 4. Check date range in access_reviews
    $stmt = $conn->prepare("
        SELECT 
            app_name,
            MIN(review_date) as oldest,
            MAX(review_date) as newest,
            COUNT(*) as count
        FROM access_reviews
        GROUP BY app_name
    ");
    $stmt->execute();
    $accessDateRanges = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $diagnosis['access_reviews_date_ranges'] = $accessDateRanges;
    
    // 5. Check if there's a LIMIT clause somewhere
    $stmt = $conn->prepare("
        SELECT 
            app_name,
            COUNT(*) as total_in_main,
            (SELECT COUNT(*) FROM access_reviews ar WHERE ar.app_name = r.app_name AND ar.review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) as should_be_in_access
        FROM reviews r
        GROUP BY app_name
    ");
    $stmt->execute();
    $comparison = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $diagnosis['main_vs_expected_access'] = $comparison;
    
    // 6. Check if access_reviews has all reviews from last 30 days
    $stmt = $conn->prepare("
        SELECT 
            r.app_name,
            COUNT(DISTINCT r.id) as in_main_last_30,
            COUNT(DISTINCT ar.id) as in_access,
            COUNT(DISTINCT CASE WHEN ar.id IS NULL THEN r.id END) as missing_from_access
        FROM reviews r
        LEFT JOIN access_reviews ar ON r.id = ar.original_review_id
        WHERE r.review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY r.app_name
    ");
    $stmt->execute();
    $syncComparison = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $diagnosis['sync_comparison'] = $syncComparison;
    
    echo json_encode([
        'success' => true,
        'diagnosis' => $diagnosis,
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

