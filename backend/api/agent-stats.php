<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../utils/DatabaseManager.php';

try {
    // Get the app name from query parameter
    $appName = $_GET['app_name'] ?? null;

    if (!$appName) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'App name is required'
        ]);
        exit;
    }
    
    $dbManager = new DatabaseManager();
    $conn = $dbManager->getConnection();
    
    // Get agent statistics for the specified app from the last 30 days
    $stmt = $conn->prepare("
        SELECT 
            earned_by as agent_name,
            COUNT(*) as review_count
        FROM access_reviews 
        WHERE app_name = :app_name 
        AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        AND earned_by != 'Click to assign'
        GROUP BY earned_by 
        ORDER BY review_count DESC, earned_by ASC
    ");
    
    $stmt->execute([':app_name' => $appName]);
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
        'error' => 'Failed to fetch agent statistics: ' . $e->getMessage()
    ]);
}
?>
