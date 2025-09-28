<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Get app_name from query parameter
    $appName = isset($_GET['app_name']) ? $_GET['app_name'] : null;

    // Get average rating directly from reviews table
    $query = "SELECT AVG(rating) as avg_rating FROM reviews WHERE is_active = TRUE";

    if ($appName) {
        $query .= " AND app_name = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$appName]);
    } else {
        $stmt = $conn->prepare($query);
        $stmt->execute();
    }

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $averageRating = $result['avg_rating'] ? round($result['avg_rating'], 1) : 0.0;

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
