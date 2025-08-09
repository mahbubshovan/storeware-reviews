<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../utils/DatabaseManager.php';

try {
    $dbManager = new DatabaseManager();

    // Get app_name from query parameter
    $appName = isset($_GET['app_name']) ? $_GET['app_name'] : null;

    $count = $dbManager->getLastMonthReviews($appName);

    echo json_encode([
        'success' => true,
        'count' => $count,
        'app_name' => $appName,
        'description' => 'Reviews from last month (before current month)'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error'
    ]);
}
?>
