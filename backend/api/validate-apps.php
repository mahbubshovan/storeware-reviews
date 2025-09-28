<?php
/**
 * App Validation Endpoint
 * Ensures only the 6 specified apps exist in the system
 * Returns clean app list for frontend consumption
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    // Define the ONLY allowed apps (exact names as they should appear)
    $allowedApps = [
        'StoreSEO',
        'StoreFAQ', 
        'EasyFlow',
        'BetterDocs FAQ Knowledge Base',
        'Vidify',
        'TrustSync'
    ];
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'POST') {
        // Clean up unwanted apps
        echo json_encode(cleanupUnwantedApps($pdo, $allowedApps));
    } else {
        // GET request - return current app status
        echo json_encode(getAppValidationStatus($pdo, $allowedApps));
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

function getAppValidationStatus($pdo, $allowedApps) {
    // Get current apps in database
    $stmt = $pdo->query("
        SELECT DISTINCT app_name, COUNT(*) as review_count 
        FROM reviews 
        WHERE app_name IS NOT NULL 
        GROUP BY app_name 
        ORDER BY app_name
    ");
    $currentApps = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $validApps = [];
    $invalidApps = [];
    
    foreach ($currentApps as $app) {
        if (in_array($app['app_name'], $allowedApps)) {
            $validApps[] = $app;
        } else {
            $invalidApps[] = $app;
        }
    }
    
    // Check which allowed apps are missing
    $existingAppNames = array_column($currentApps, 'app_name');
    $missingApps = array_diff($allowedApps, $existingAppNames);
    
    return [
        'success' => true,
        'allowed_apps' => $allowedApps,
        'valid_apps' => $validApps,
        'invalid_apps' => $invalidApps,
        'missing_apps' => array_values($missingApps),
        'is_clean' => empty($invalidApps),
        'total_valid_apps' => count($validApps),
        'total_invalid_apps' => count($invalidApps),
        'cleanup_needed' => !empty($invalidApps),
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

function cleanupUnwantedApps($pdo, $allowedApps) {
    $startTime = microtime(true);
    
    // Get apps to remove
    $stmt = $pdo->query("
        SELECT DISTINCT app_name, COUNT(*) as count 
        FROM reviews 
        WHERE app_name IS NOT NULL 
        GROUP BY app_name
    ");
    $currentApps = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $appsToRemove = [];
    foreach ($currentApps as $app) {
        if (!in_array($app['app_name'], $allowedApps)) {
            $appsToRemove[] = $app['app_name'];
        }
    }
    
    if (empty($appsToRemove)) {
        return [
            'success' => true,
            'message' => 'No cleanup needed - database is already clean',
            'apps_removed' => [],
            'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
        ];
    }
    
    $cleanupResults = [];
    
    // Remove from reviews table
    $placeholders = str_repeat('?,', count($appsToRemove) - 1) . '?';
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE app_name IN ($placeholders)");
    $stmt->execute($appsToRemove);
    $reviewsDeleted = $stmt->rowCount();
    $cleanupResults['reviews_deleted'] = $reviewsDeleted;
    
    // Remove from access_reviews table
    try {
        $stmt = $pdo->prepare("DELETE FROM access_reviews WHERE app_name IN ($placeholders)");
        $stmt->execute($appsToRemove);
        $accessDeleted = $stmt->rowCount();
        $cleanupResults['access_reviews_deleted'] = $accessDeleted;
    } catch (Exception $e) {
        $cleanupResults['access_reviews_deleted'] = 'table_not_exists';
    }
    
    // Remove from app_metadata table
    try {
        $stmt = $pdo->prepare("DELETE FROM app_metadata WHERE app_name IN ($placeholders)");
        $stmt->execute($appsToRemove);
        $metaDeleted = $stmt->rowCount();
        $cleanupResults['app_metadata_deleted'] = $metaDeleted;
    } catch (Exception $e) {
        $cleanupResults['app_metadata_deleted'] = 'table_not_exists';
    }
    
    // Ensure all allowed apps exist in app_metadata
    $ensuredApps = [];
    try {
        $insertStmt = $pdo->prepare("
            INSERT IGNORE INTO app_metadata (app_name, total_reviews, overall_rating) 
            VALUES (?, 0, 0.0)
        ");
        
        foreach ($allowedApps as $app) {
            $insertStmt->execute([$app]);
            $ensuredApps[] = $app;
        }
    } catch (Exception $e) {
        // app_metadata table might not exist
    }
    
    return [
        'success' => true,
        'message' => 'Database cleanup completed successfully',
        'apps_removed' => $appsToRemove,
        'cleanup_results' => $cleanupResults,
        'apps_ensured_in_metadata' => $ensuredApps,
        'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
    ];
}
?>
