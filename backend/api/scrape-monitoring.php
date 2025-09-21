<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/IPRateLimitManager.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    $rateLimitManager = new IPRateLimitManager();
    
    // Get current rate limit status
    $currentIP = $rateLimitManager->getClientIP();
    
    // Get active rate limits
    $activeLimitsStmt = $conn->prepare("
        SELECT 
            ip_address,
            app_name,
            last_scrape_timestamp,
            cooldown_expiry,
            scrape_count,
            TIMESTAMPDIFF(SECOND, NOW(), cooldown_expiry) as remaining_seconds
        FROM ip_scrape_limits 
        WHERE cooldown_expiry > NOW()
        ORDER BY cooldown_expiry DESC
        LIMIT 50
    ");
    $activeLimitsStmt->execute();
    $activeLimits = $activeLimitsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent activity summary
    $activityStmt = $conn->prepare("
        SELECT 
            action,
            COUNT(*) as count,
            MAX(timestamp) as last_occurrence
        FROM scrape_activity_log 
        WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        GROUP BY action
        ORDER BY count DESC
    ");
    $activityStmt->execute();
    $activitySummary = $activityStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get top IPs by scraping activity
    $topIPsStmt = $conn->prepare("
        SELECT 
            ip_address,
            COUNT(*) as total_requests,
            COUNT(DISTINCT app_name) as apps_scraped,
            MAX(timestamp) as last_activity,
            SUM(CASE WHEN action = 'scrape_blocked' THEN 1 ELSE 0 END) as blocked_requests
        FROM scrape_activity_log 
        WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY ip_address
        ORDER BY total_requests DESC
        LIMIT 20
    ");
    $topIPsStmt->execute();
    $topIPs = $topIPsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get app-specific statistics
    $appStatsStmt = $conn->prepare("
        SELECT 
            app_name,
            COUNT(*) as total_requests,
            COUNT(DISTINCT ip_address) as unique_ips,
            SUM(CASE WHEN action = 'scrape_allowed' THEN 1 ELSE 0 END) as allowed_requests,
            SUM(CASE WHEN action = 'scrape_blocked' THEN 1 ELSE 0 END) as blocked_requests,
            MAX(timestamp) as last_activity
        FROM scrape_activity_log 
        WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY app_name
        ORDER BY total_requests DESC
    ");
    $appStatsStmt->execute();
    $appStats = $appStatsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get hourly activity for the last 24 hours
    $hourlyStmt = $conn->prepare("
        SELECT 
            DATE_FORMAT(timestamp, '%Y-%m-%d %H:00:00') as hour,
            COUNT(*) as total_requests,
            SUM(CASE WHEN action = 'scrape_allowed' THEN 1 ELSE 0 END) as allowed,
            SUM(CASE WHEN action = 'scrape_blocked' THEN 1 ELSE 0 END) as blocked
        FROM scrape_activity_log 
        WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        GROUP BY DATE_FORMAT(timestamp, '%Y-%m-%d %H:00:00')
        ORDER BY hour DESC
    ");
    $hourlyStmt->execute();
    $hourlyActivity = $hourlyStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // System health metrics
    $totalLimitsStmt = $conn->prepare("SELECT COUNT(*) as total FROM ip_scrape_limits");
    $totalLimitsStmt->execute();
    $totalLimits = $totalLimitsStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $totalLogsStmt = $conn->prepare("SELECT COUNT(*) as total FROM scrape_activity_log");
    $totalLogsStmt->execute();
    $totalLogs = $totalLogsStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo json_encode([
        'success' => true,
        'data' => [
            'current_ip' => $currentIP,
            'system_health' => [
                'total_rate_limits' => $totalLimits,
                'active_rate_limits' => count($activeLimits),
                'total_activity_logs' => $totalLogs,
                'monitoring_since' => date('Y-m-d H:i:s', time() - (7 * 24 * 3600)) // 7 days ago
            ],
            'active_rate_limits' => $activeLimits,
            'activity_summary_24h' => $activitySummary,
            'top_ips_7d' => $topIPs,
            'app_statistics_7d' => $appStats,
            'hourly_activity_24h' => $hourlyActivity
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
