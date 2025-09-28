<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Define the ONLY allowed apps (hardcoded to ensure consistency)
    $allowedApps = [
        'StoreSEO',
        'StoreFAQ',
        'EasyFlow',
        'BetterDocs FAQ Knowledge Base',
        'Vidify',
        'TrustSync'
    ];

    // Get only apps that exist in database AND are in our allowed list
    $placeholders = str_repeat('?,', count($allowedApps) - 1) . '?';
    $stmt = $conn->prepare("
        SELECT DISTINCT app_name
        FROM reviews
        WHERE app_name IS NOT NULL
        AND is_active = TRUE
        AND app_name IN ($placeholders)
        ORDER BY
            CASE app_name
                WHEN 'StoreSEO' THEN 1
                WHEN 'StoreFAQ' THEN 2
                WHEN 'EasyFlow' THEN 3
                WHEN 'BetterDocs FAQ Knowledge Base' THEN 4
                WHEN 'Vidify' THEN 5
                WHEN 'TrustSync' THEN 6
                ELSE 7
            END
    ");
    $stmt->execute($allowedApps);
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
