<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../scraper/ShopifyScraper.php';

try {
    $scraper = new ShopifyScraper();
    $apps = $scraper->getAvailableApps();
    
    echo json_encode([
        'success' => true,
        'apps' => $apps
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error'
    ]);
}
?>
