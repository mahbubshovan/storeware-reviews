<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();

    // Get app_name from query parameter
    $appName = isset($_GET['app_name']) ? $_GET['app_name'] : null;

    // Get latest reviews directly from reviews table
    $query = "SELECT app_name, store_name, country_name, rating, review_content, review_date
              FROM reviews";

    if ($appName) {
        $query .= " WHERE app_name = ? AND is_active = TRUE";
    } else {
        $query .= " WHERE is_active = TRUE";
    }

    $query .= " ORDER BY review_date DESC, created_at DESC LIMIT 10";

    $stmt = $conn->prepare($query);
    if ($appName) {
        $stmt->execute([$appName]);
    } else {
        $stmt->execute();
    }
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'reviews' => $reviews,
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
