<?php
/**
 * This Month Reviews API - Standardized Count Calculation
 * Returns this month's review count using standardized date calculations and primary reviews table
 */
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../utils/DatabaseManager.php';
require_once __DIR__ . '/../utils/DateCalculations.php';

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

try {
    $dbManager = new DatabaseManager();
    $conn = $dbManager->getConnection();

    // Get app_name from query parameter
    $appName = isset($_GET['app_name']) ? $_GET['app_name'] : null;

    // Use standardized date calculations with primary reviews table
    $count = DateCalculations::getThisMonthCount($conn, 'reviews', $appName);

    // Debug logging
    error_log("THIS MONTH API (Standardized) - App: $appName, Count: $count, Time: " . date('Y-m-d H:i:s'));

    echo json_encode([
        'success' => true,
        'count' => $count,
        'app_name' => $appName,
        'source' => 'reviews_table_standardized',
        'debug_time' => date('Y-m-d H:i:s'),
        'date_range' => 'From first of current month to today'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("THIS MONTH API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'debug_message' => $e->getMessage()
    ]);
}
?>
