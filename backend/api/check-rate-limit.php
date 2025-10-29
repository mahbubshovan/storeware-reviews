<?php
/**
 * Check Rate Limit Status
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get rate limit records
    $stmt = $conn->prepare("
        SELECT * FROM ip_scrape_limits 
        WHERE app_name = 'StoreSEO'
        ORDER BY last_scrape_timestamp DESC
        LIMIT 5
    ");
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'records' => $records,
        'current_time' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>

