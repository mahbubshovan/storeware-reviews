<?php
/**
 * CORS Configuration
 */

// Production CORS settings
$allowedOrigins = [
    'http://localhost:5173', // Development
    'http://localhost:3000',  // Alternative development
    'https://shopify-reviews-frontend.vercel.app', // Vercel frontend
    'https://shopify-reviews.vercel.app', // Alternative Vercel domain
    'https://shopify-reviews-git-main.vercel.app', // Vercel git branch domain
];

// Allow any Railway backend domain for development
if (isset($_SERVER['HTTP_ORIGIN'])) {
    $origin = $_SERVER['HTTP_ORIGIN'];
    // Allow Railway domains
    if (strpos($origin, '.railway.app') !== false ||
        strpos($origin, '.up.railway.app') !== false ||
        strpos($origin, 'localhost') !== false) {
        $allowedOrigins[] = $origin;
    }
}

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

// Set content type to JSON
header('Content-Type: application/json');
?>
