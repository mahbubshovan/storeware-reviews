<?php
require_once __DIR__ . '/config/cors.php';

// Simple router for API endpoints
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Remove base path if running in subdirectory
$path = str_replace('/backend', '', $path);

switch ($path) {
    case '/api/this-month-reviews':
        require_once __DIR__ . '/api/this-month-reviews.php';
        break;
        
    case '/api/last-30-days-reviews':
        require_once __DIR__ . '/api/last-30-days-reviews.php';
        break;

    case '/api/last-month-reviews':
        require_once __DIR__ . '/api/last-month-reviews.php';
        break;
        
    case '/api/average-rating':
        require_once __DIR__ . '/api/average-rating.php';
        break;
        
    case '/api/review-distribution':
        require_once __DIR__ . '/api/review-distribution.php';
        break;
        
    case '/api/latest-reviews':
        require_once __DIR__ . '/api/latest-reviews.php';
        break;

    case '/api/available-apps':
        require_once __DIR__ . '/api/available-apps.php';
        break;

    case '/api/scrape-app':
        require_once __DIR__ . '/api/scrape-app.php';
        break;

    case '/api/apps':
        require_once __DIR__ . '/api/apps.php';
        break;

    default:
        // Handle dynamic routes like /api/agent-stats/{appName}
        if (preg_match('/^\/api\/agent-stats\/(.+)$/', $path, $matches)) {
            require_once __DIR__ . '/api/agent-stats.php';
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Endpoint not found'
            ]);
        }
        break;
}
?>
