<?php
/**
 * Clear all rate limits to allow fresh scraping
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Clear all rate limit records
    $stmt = $conn->prepare("DELETE FROM ip_scrape_limits");
    $stmt->execute();
    $deletedCount = $stmt->rowCount();
    
    echo json_encode([
        'success' => true,
        'message' => "Cleared $deletedCount rate limit records",
        'timestamp' => date('Y-m-d H:i:s'),
        'next_step' => 'Run fresh scrape for each app using /api/scrape-app.php'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>

