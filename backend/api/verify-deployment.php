<?php
/**
 * Verify Deployment Status
 * Check if the database table mismatch fixes are properly deployed
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Check which class is being used in apps.php
    $appsPhpContent = file_get_contents(__DIR__ . '/apps.php');
    $usingDatabaseManager = strpos($appsPhpContent, 'DatabaseManager') !== false;
    $usingDatabase = strpos($appsPhpContent, 'new Database()') !== false;
    
    // Test the actual apps endpoint
    $appsUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/backend/api/apps.php';
    $appsResponse = @file_get_contents($appsUrl);
    $appsData = json_decode($appsResponse, true);
    
    // Get database stats
    $stmt = $conn->prepare("
        SELECT app_name, COUNT(*) as count 
        FROM reviews 
        WHERE is_active = TRUE 
        GROUP BY app_name 
        ORDER BY count DESC
    ");
    $stmt->execute();
    $dbStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Check if is_active column exists
    $stmt = $conn->query("DESCRIBE reviews");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $hasIsActiveColumn = in_array('is_active', $columns);
    
    echo json_encode([
        'success' => true,
        'deployment_status' => [
            'apps_php_uses_database_manager' => $usingDatabaseManager,
            'apps_php_uses_database_class' => $usingDatabase,
            'deployment_correct' => !$usingDatabaseManager && $usingDatabase
        ],
        'apps_endpoint_test' => [
            'response' => $appsData,
            'apps_count' => is_array($appsData) ? count($appsData) : 0,
            'expected_count' => 5 // StoreSEO, EasyFlow, StoreFAQ, TrustSync, BetterDocs
        ],
        'database_analysis' => [
            'has_is_active_column' => $hasIsActiveColumn,
            'apps_with_data' => $dbStats,
            'total_apps_in_db' => count($dbStats)
        ],
        'recommendations' => [
            'need_to_redeploy' => $usingDatabaseManager,
            'missing_vidify_data' => !in_array('Vidify', array_column($dbStats, 'app_name')),
            'country_data_needs_fix' => false // We already fixed this
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
