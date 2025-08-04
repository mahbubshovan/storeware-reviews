<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../utils/DatabaseManager.php';

try {
    $dbManager = new DatabaseManager();
    $conn = $dbManager->getConnection();
    
    // Get distinct app names from the access_reviews table
    $stmt = $conn->prepare("
        SELECT DISTINCT app_name 
        FROM access_reviews 
        WHERE app_name IS NOT NULL 
        ORDER BY app_name
    ");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Return just the array of app names
    header('Content-Type: application/json');
    echo json_encode($result);
    
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Failed to fetch apps: ' . $e->getMessage()
    ]);
}
?>
