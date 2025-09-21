<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Check database connection
    require_once __DIR__ . '/../config/database.php';
    $db = new Database();
    $conn = $db->getConnection();
    
    // Simple query to test database
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM reviews LIMIT 1");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'status' => 'healthy',
        'database' => 'connected',
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => '1.0.0'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'status' => 'unhealthy',
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
