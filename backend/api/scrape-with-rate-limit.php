<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../scraper/EnhancedUniversalScraper.php';
require_once __DIR__ . '/../utils/IPRateLimitManager.php';

try {
    $scraper = new EnhancedUniversalScraper();
    $rateLimitManager = new IPRateLimitManager();
    
    // Get app name from request
    $appName = $_GET['app'] ?? $_POST['app'] ?? null;
    
    if (!$appName) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'App name is required',
            'usage' => 'GET /api/scrape-with-rate-limit.php?app=StoreSEO'
        ]);
        exit;
    }
    
    // Map app names to slugs
    $appSlugs = [
        'StoreSEO' => 'storeseo',
        'StoreFAQ' => 'storefaq',
        'Vidify' => 'vidify-video-backgrounds',
        'TrustSync' => 'trustsync-reviews',
        'EasyFlow' => 'easyflow-product-options',
        'BetterDocs FAQ' => 'betterdocs-knowledgebase'
    ];
    
    $appSlug = $appSlugs[$appName] ?? strtolower($appName);
    
    // Get rate limit status
    $rateLimitStatus = $scraper->getRateLimitStatus($appName);
    
    // Perform scraping with rate limiting
    $result = $scraper->scrapeAppWithRateLimit($appSlug, $appName);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'data' => $result,
            'rate_limit' => $rateLimitStatus,
            'timestamp' => date('Y-m-d H:i:s'),
            'ip_address' => $rateLimitManager->getClientIP()
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to scrape data',
            'rate_limit' => $rateLimitStatus,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
