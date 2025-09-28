<?php
/**
 * Homepage Statistics API
 * Provides real-time review counts using the new first page monitoring system
 * Combines data from access_reviews table with main reviews table for accurate counts
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    $appName = isset($_GET['app_name']) ? $_GET['app_name'] : null;
    $metric = isset($_GET['metric']) ? $_GET['metric'] : 'all';

    $database = new Database();
    $conn = $database->getConnection();
    
    if ($appName) {
        // Get stats for specific app
        $stats = getAppStats($conn, $appName);
        
        if ($metric === 'all') {
            echo json_encode([
                'success' => true,
                'app_name' => $appName,
                'stats' => $stats
            ]);
        } else {
            // Return specific metric
            $value = isset($stats[$metric]) ? $stats[$metric] : 0;
            echo json_encode([
                'success' => true,
                'app_name' => $appName,
                'metric' => $metric,
                'count' => $value
            ]);
        }
    } else {
        // Get stats for all apps
        $allStats = getAllAppsStats($conn);
        
        echo json_encode([
            'success' => true,
            'stats' => $allStats,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}

function getAppStats($conn, $appName) {
    // Get comprehensive stats combining access_reviews and main reviews tables
    $stmt = $conn->prepare("
        SELECT 
            -- From access_reviews table (monitored reviews)
            COUNT(ar.id) as monitored_total,
            COUNT(CASE WHEN ar.review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as monitored_last_30_days,
            COUNT(CASE WHEN ar.review_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN 1 END) as monitored_this_month,
            COUNT(CASE WHEN ar.earned_by IS NOT NULL AND ar.earned_by != '' THEN 1 END) as assigned_reviews,
            AVG(ar.rating) as monitored_avg_rating,
            MAX(ar.review_date) as latest_monitored_date
        FROM access_reviews ar
        WHERE ar.app_name = ?
    ");
    $stmt->execute([$appName]);
    $accessStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get total reviews from main reviews table
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_reviews,
            COUNT(CASE WHEN review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as main_last_30_days,
            COUNT(CASE WHEN review_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN 1 END) as main_this_month,
            AVG(rating) as main_avg_rating
        FROM reviews 
        WHERE app_name = ? AND is_active = TRUE
    ");
    $stmt->execute([$appName]);
    $mainStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return [
        // Primary metrics (from monitoring system)
        'this_month' => intval($accessStats['monitored_this_month']),
        'last_30_days' => intval($accessStats['monitored_last_30_days']),
        'total_reviews' => intval($mainStats['total_reviews']), // Use main table for total
        
        // Additional metrics
        'monitored_total' => intval($accessStats['monitored_total']),
        'assigned_reviews' => intval($accessStats['assigned_reviews']),
        'unassigned_reviews' => intval($accessStats['monitored_total']) - intval($accessStats['assigned_reviews']),
        'avg_rating' => round(floatval($accessStats['monitored_avg_rating'] ?: $mainStats['main_avg_rating']), 1),
        'latest_review_date' => $accessStats['latest_monitored_date'],
        
        // Comparison metrics
        'main_table_total' => intval($mainStats['total_reviews']),
        'main_last_30_days' => intval($mainStats['main_last_30_days']),
        'main_this_month' => intval($mainStats['main_this_month'])
    ];
}

function getAllAppsStats($conn) {
    $apps = ['StoreSEO', 'StoreFAQ', 'EasyFlow', 'TrustSync', 'BetterDocs FAQ Knowledge Base', 'Vidify'];
    $allStats = [];
    
    foreach ($apps as $appName) {
        $allStats[$appName] = getAppStats($conn, $appName);
    }
    
    // Calculate totals
    $totals = [
        'this_month' => 0,
        'last_30_days' => 0,
        'total_reviews' => 0,
        'monitored_total' => 0,
        'assigned_reviews' => 0,
        'unassigned_reviews' => 0
    ];
    
    foreach ($allStats as $stats) {
        $totals['this_month'] += $stats['this_month'];
        $totals['last_30_days'] += $stats['last_30_days'];
        $totals['total_reviews'] += $stats['total_reviews'];
        $totals['monitored_total'] += $stats['monitored_total'];
        $totals['assigned_reviews'] += $stats['assigned_reviews'];
        $totals['unassigned_reviews'] += $stats['unassigned_reviews'];
    }
    
    $allStats['_totals'] = $totals;
    
    return $allStats;
}
?>
