<?php
/**
 * Test Scraping Endpoint - Direct scraping without rate limiting
 */

set_time_limit(300); // 5 minutes
ini_set('max_execution_time', 300);

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../scraper/UniversalLiveScraper.php';

header('Content-Type: application/json');

try {
    $appName = $_GET['app'] ?? 'StoreSEO';
    $appSlug = $_GET['slug'] ?? 'storeseo';
    
    echo json_encode([
        'status' => 'starting',
        'app' => $appName,
        'slug' => $appSlug,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
    // Flush output
    ob_flush();
    flush();
    
    // Create scraper and run
    $scraper = new UniversalLiveScraper();
    $result = $scraper->scrapeApp($appSlug, $appName);
    
    echo json_encode([
        'status' => 'complete',
        'result' => $result,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>

