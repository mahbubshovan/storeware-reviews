<?php
// Increase execution time for scraping
set_time_limit(120); // 2 minutes
ini_set('max_execution_time', 120);

// Start output buffering to capture any unwanted output
ob_start();

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../scraper/ShopifyScraper.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed'
    ]);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['app_name'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'app_name is required'
        ]);
        exit;
    }
    
    $appName = $input['app_name'];
    
    // Initialize scraper
    $scraper = new ShopifyScraper();
    $availableApps = $scraper->getAvailableApps();
    
    if (!in_array($appName, $availableApps)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid app name',
            'available_apps' => $availableApps
        ]);
        exit;
    }
    
    // Start scraping (this might take a while)
    $scrapedCount = $scraper->scrapeAppByName($appName);

    // Clear any unwanted output from scraping
    ob_clean();

    if ($scrapedCount !== false) {
        echo json_encode([
            'success' => true,
            'message' => "Successfully scraped $scrapedCount new reviews for $appName",
            'scraped_count' => $scrapedCount
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Scraping failed'
        ]);
    }
    
} catch (Exception $e) {
    // Clear any unwanted output
    ob_clean();

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error: ' . $e->getMessage()
    ]);
}

// End output buffering
ob_end_flush();
?>
