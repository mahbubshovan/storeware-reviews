<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../utils/DatabaseManager.php';

try {
    $dbManager = new DatabaseManager();
    $conn = $dbManager->getConnection();
    
    // Get distinct reviewer names from the reviews table (same as Access Tabbed)
    // Updated for consistency with Access Tabbed data source
    $stmt = $conn->prepare("
        SELECT DISTINCT earned_by
        FROM reviews
        WHERE earned_by IS NOT NULL
        AND earned_by != ''
        AND is_active = TRUE
        ORDER BY earned_by ASC
    ");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Return just the array of reviewer names
    header('Content-Type: application/json');
    echo json_encode($result);
    
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Failed to fetch reviewers: ' . $e->getMessage()
    ]);
}
?>
