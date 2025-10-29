<?php
/**
 * Force Scrape - Bypass rate limiting and scrape StoreSEO
 */

set_time_limit(300);
ini_set('max_execution_time', 300);

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../scraper/UniversalLiveScraper.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Clear rate limit for StoreSEO
    $stmt = $conn->prepare("DELETE FROM ip_scrape_limits WHERE app_name = 'StoreSEO'");
    $stmt->execute();
    
    echo json_encode([
        'status' => 'cleared_rate_limit',
        'message' => 'Rate limit cleared for StoreSEO'
    ]);
    
    // Now trigger the scrape
    $scraper = new UniversalLiveScraper();
    $result = $scraper->scrapeApp('storeseo', 'StoreSEO');
    
    // Get final count
    $stmt = $conn->prepare('SELECT COUNT(*) FROM reviews WHERE app_name = ?');
    $stmt->execute(['StoreSEO']);
    $finalCount = $stmt->fetchColumn();
    
    echo json_encode([
        'status' => 'complete',
        'scrape_result' => $result,
        'final_count' => $finalCount,
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

