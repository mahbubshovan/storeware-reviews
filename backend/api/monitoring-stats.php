<?php
/**
 * Monitoring Statistics API
 * Provides real-time review counts from the first page monitoring system
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../utils/FirstPageMonitor.php';
require_once __DIR__ . '/../utils/DatabaseManager.php';

header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        handleGetStats();
    } elseif ($method === 'POST') {
        handleRunMonitoring();
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}

function handleGetStats() {
    $appName = isset($_GET['app_name']) ? $_GET['app_name'] : null;
    
    $monitor = new FirstPageMonitor();
    $dbManager = new DatabaseManager();
    
    if ($appName) {
        // Get stats for specific app
        $stats = getAppStats($dbManager, $appName);
        
        echo json_encode([
            'success' => true,
            'app_name' => $appName,
            'stats' => $stats
        ]);
    } else {
        // Get stats for all apps
        $allStats = $monitor->getMonitoringStats();
        
        echo json_encode([
            'success' => true,
            'stats' => $allStats,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}

function handleRunMonitoring() {
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
            return;
        }
        
        $newReviews = $monitor->monitorApp($appName, $apps[$appName]);
        
        echo json_encode([
            'success' => true,
            'app_name' => $appName,
            'new_reviews_found' => $newReviews,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } else {
        // Monitor all apps
        $totalNewReviews = $monitor->monitorAllApps();
        
        echo json_encode([
            'success' => true,
            'total_new_reviews' => $totalNewReviews,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}

function getAppStats($dbManager, $appName) {
    $conn = $dbManager->getConnection();
    
    // Get comprehensive stats from access_reviews table
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_reviews,
            COUNT(CASE WHEN review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as last_30_days,
            COUNT(CASE WHEN review_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN 1 END) as this_month,
            COUNT(CASE WHEN earned_by IS NOT NULL AND earned_by != '' THEN 1 END) as assigned_reviews,
            COUNT(CASE WHEN earned_by IS NULL OR earned_by = '' THEN 1 END) as unassigned_reviews,
            AVG(rating) as avg_rating,
            MAX(review_date) as latest_review_date
        FROM access_reviews 
        WHERE app_name = ?
    ");
    $stmt->execute([$appName]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Also get total reviews from main reviews table for comparison
    $stmt = $conn->prepare("
        SELECT COUNT(*) as main_table_total
        FROM reviews 
        WHERE app_name = ? AND is_active = TRUE
    ");
    $stmt->execute([$appName]);
    $mainTableResult = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return [
        'total_reviews' => intval($result['total_reviews']),
        'last_30_days' => intval($result['last_30_days']),
        'this_month' => intval($result['this_month']),
        'assigned_reviews' => intval($result['assigned_reviews']),
        'unassigned_reviews' => intval($result['unassigned_reviews']),
        'avg_rating' => round(floatval($result['avg_rating']), 1),
        'latest_review_date' => $result['latest_review_date'],
        'main_table_total' => intval($mainTableResult['main_table_total'])
    ];
}
?>
