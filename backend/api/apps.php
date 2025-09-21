<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../utils/DatabaseManager.php';

try {
    $dbManager = new DatabaseManager();
    $conn = $dbManager->getConnection();
    
    // Get distinct app names from the reviews table (only apps we're actively tracking)
    // Exclude BetterDocs FAQ and Vitals as requested
    $stmt = $conn->prepare("
        SELECT DISTINCT app_name
        FROM reviews
        WHERE app_name IS NOT NULL
        AND is_active = TRUE
        AND app_name NOT IN ('BetterDocs FAQ', 'Vitals')
        AND app_name IN ('StoreSEO', 'StoreFAQ', 'EasyFlow', 'TrustSync', 'Vidify', 'BetterDocs FAQ Knowledge Base')
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
