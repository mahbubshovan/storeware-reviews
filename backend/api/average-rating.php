<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../utils/DatabaseManager.php';

try {
    $dbManager = new DatabaseManager();

    // Get app_name from query parameter
    $appName = isset($_GET['app_name']) ? $_GET['app_name'] : null;

    $averageRating = $dbManager->getAverageRating($appName);

    echo json_encode([
        'success' => true,
        'average_rating' => $averageRating ?: 0.0,
        'app_name' => $appName
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error'
    ]);
}
?>
