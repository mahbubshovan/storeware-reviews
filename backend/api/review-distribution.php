<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../utils/DatabaseManager.php';

try {
    $dbManager = new DatabaseManager();

    // Get app_name from query parameter
    $appName = isset($_GET['app_name']) ? $_GET['app_name'] : null;

    $distribution = $dbManager->getReviewDistribution($appName);

    echo json_encode([
        'success' => true,
        'total_reviews' => intval($distribution['total_reviews']),
        'distribution' => [
            'five_star' => intval($distribution['five_star']),
            'four_star' => intval($distribution['four_star']),
            'three_star' => intval($distribution['three_star']),
            'two_star' => intval($distribution['two_star']),
            'one_star' => intval($distribution['one_star'])
        ],
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
