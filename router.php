<?php
/**
 * Simple PHP Router for Single Domain Deployment
 * Handles both frontend and backend routing
 */

$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove query string from URI
$uri = parse_url($requestUri, PHP_URL_PATH);

// Handle backend API requests
if (strpos($uri, '/backend/') === 0) {
    // Remove /backend prefix and route to actual backend files
    $backendPath = substr($uri, 8); // Remove '/backend'
    $backendFile = __DIR__ . '/backend' . $backendPath;

    // Log backend requests for debugging (remove in production)
    // error_log("Backend request: URI=$uri, Path=$backendPath, File=$backendFile");

    if (file_exists($backendFile) && is_file($backendFile)) {
        // Set the correct working directory for backend scripts
        $originalDir = getcwd();
        chdir(__DIR__ . '/backend');

        // Set up $_SERVER variables for the backend script
        $_SERVER['SCRIPT_NAME'] = $backendPath;
        $_SERVER['SCRIPT_FILENAME'] = $backendFile;

        // Include the backend file
        include $backendFile;

        // Restore original directory
        chdir($originalDir);
        exit;
    } else {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'API endpoint not found',
            'path' => $backendPath,
            'file' => $backendFile,
            'exists' => file_exists($backendFile),
            'is_file' => is_file($backendFile)
        ]);
        exit;
    }
}

// Handle static assets (CSS, JS, images, etc.)
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$/', $uri)) {
    $filePath = __DIR__ . $uri;
    if (file_exists($filePath)) {
        // Set appropriate content type
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject'
        ];
        
        if (isset($mimeTypes[$extension])) {
            header('Content-Type: ' . $mimeTypes[$extension]);
        }
        
        readfile($filePath);
        exit;
    }
}

// For all other requests (frontend routes), serve index.html
$indexPath = __DIR__ . '/index.html';
if (file_exists($indexPath)) {
    header('Content-Type: text/html');
    readfile($indexPath);
} else {
    http_response_code(404);
    echo '404 - Page not found';
}
?>
