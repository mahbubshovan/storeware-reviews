<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../utils/IPRateLimitManager.php';

try {
    $rateLimitManager = new IPRateLimitManager();
    $appName = $_GET['app'] ?? null;
    
    $clientIP = $rateLimitManager->getClientIP();
    $canScrape = $rateLimitManager->canScrape($appName);
    $remainingTime = $rateLimitManager->getRemainingCooldown($appName);
    
    // Format remaining time
    $hours = floor($remainingTime / 3600);
    $minutes = floor(($remainingTime % 3600) / 60);
    $seconds = $remainingTime % 60;
    
    echo json_encode([
        'success' => true,
        'data' => [
            'ip_address' => $clientIP,
            'app_name' => $appName,
            'can_scrape' => $canScrape,
            'rate_limited' => !$canScrape,
            'remaining_cooldown_seconds' => $remainingTime,
            'remaining_cooldown_formatted' => sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds),
            'cooldown_expires_at' => $remainingTime > 0 ? date('Y-m-d H:i:s', time() + $remainingTime) : null,
            'next_scrape_allowed' => $remainingTime > 0 ? date('Y-m-d H:i:s', time() + $remainingTime) : 'Now'
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
