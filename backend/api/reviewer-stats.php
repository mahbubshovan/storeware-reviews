<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../utils/DatabaseManager.php';

try {
    // Get the reviewer name from query parameter
    $reviewerName = $_GET['reviewer_name'] ?? null;
    
    if (!$reviewerName) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Reviewer name is required'
        ]);
        exit;
    }
    
    $dbManager = new DatabaseManager();
    $conn = $dbManager->getConnection();
    
    // Get app statistics for the specified reviewer (all time data)
    $stmt = $conn->prepare("
        SELECT 
            app_name,
            COUNT(*) as review_count
        FROM access_reviews 
        WHERE earned_by = :reviewer_name 
        GROUP BY app_name 
        ORDER BY review_count DESC, app_name ASC
    ");
    
    $stmt->execute([':reviewer_name' => $reviewerName]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert review_count to integer
    foreach ($result as &$row) {
        $row['review_count'] = (int)$row['review_count'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($result);
    
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Failed to fetch reviewer statistics: ' . $e->getMessage()
    ]);
}
?>
