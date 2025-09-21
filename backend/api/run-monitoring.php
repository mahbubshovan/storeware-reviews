<?php
/**
 * Run Monitoring API
 * Triggers the first page monitoring system on demand
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../utils/FirstPageMonitor.php';

header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $appName = isset($input['app_name']) ? $input['app_name'] : null;
    
    $monitor = new FirstPageMonitor();
    
    if ($appName) {
        // Monitor specific app
        $apps = [
            'StoreSEO' => 'https://apps.shopify.com/storeseo/reviews?sort_by=newest&page=1',
            'StoreFAQ' => 'https://apps.shopify.com/storefaq/reviews?sort_by=newest&page=1',
            'EasyFlow' => 'https://apps.shopify.com/product-options-4/reviews?sort_by=newest&page=1',
            'TrustSync' => 'https://apps.shopify.com/customer-review-app/reviews?sort_by=newest&page=1',
            'Vitals' => 'https://apps.shopify.com/vitals/reviews?sort_by=newest&page=1',
            'BetterDocs FAQ' => 'https://apps.shopify.com/betterdocs-knowledgebase/reviews?sort_by=newest&page=1',
            'Vidify' => 'https://apps.shopify.com/vidify/reviews?sort_by=newest&page=1'
        ];
        
        if (!isset($apps[$appName])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid app name']);
            exit;
        }
        
        $startTime = microtime(true);
        $newReviews = $monitor->monitorApp($appName, $apps[$appName]);
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        
        // Get updated stats for this app
        $stats = $monitor->getMonitoringStats();
        $appStats = isset($stats[$appName]) ? $stats[$appName] : null;
        
        echo json_encode([
            'success' => true,
            'app_name' => $appName,
            'new_reviews_found' => $newReviews,
            'updated_stats' => $appStats,
            'execution_time_ms' => $executionTime,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        // Monitor all apps
        $startTime = microtime(true);
        
        // Capture output
        ob_start();
        $totalNewReviews = $monitor->monitorAllApps();
        $output = ob_get_clean();
        
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        
        // Get updated stats for all apps
        $allStats = $monitor->getMonitoringStats();
        
        echo json_encode([
            'success' => true,
            'total_new_reviews' => $totalNewReviews,
            'updated_stats' => $allStats,
            'execution_time_ms' => $executionTime,
            'output' => $output,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>
