<?php
/**
 * Diagnose why live server shows only 10 reviews
 * Check: Database, Rate Limiting, Scraper Status
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/IPRateLimitManager.php';

header('Content-Type: application/json');

$diagnosis = [
    'timestamp' => date('Y-m-d H:i:s'),
    'environment' => php_uname(),
    'php_version' => phpversion(),
    'checks' => []
];

try {
    // Check 1: Database Connection
    $diagnosis['checks'][] = "=== DATABASE CONNECTION ===";
    $db = new Database();
    $conn = $db->getConnection();
    $diagnosis['checks'][] = "✅ Database connected successfully";
    
    // Check 2: Main reviews table data
    $diagnosis['checks'][] = "\n=== MAIN REVIEWS TABLE ===";
    
    $stmt = $conn->prepare("
        SELECT app_name, COUNT(*) as total, 
               COUNT(CASE WHEN review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as last_30_days
        FROM reviews
        WHERE is_active = TRUE
        GROUP BY app_name
        ORDER BY total DESC
    ");
    $stmt->execute();
    $mainTableStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($mainTableStats as $app) {
        $diagnosis['checks'][] = "{$app['app_name']}: {$app['total']} total, {$app['last_30_days']} last 30 days";
    }
    
    // Check 3: Rate Limiting Status
    $diagnosis['checks'][] = "\n=== RATE LIMITING STATUS ===";
    
    $rateLimitMgr = new IPRateLimitManager();
    $clientIP = $rateLimitMgr->getClientIP();
    $diagnosis['checks'][] = "Client IP: $clientIP";
    
    $stmt = $conn->prepare("SELECT * FROM ip_scrape_limits WHERE ip_address = ?");
    $stmt->execute([$clientIP]);
    $rateLimitRecord = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($rateLimitRecord) {
        $diagnosis['checks'][] = "Rate limit record found:";
        $diagnosis['checks'][] = "  - Last scrape: {$rateLimitRecord['last_scrape_time']}";
        $diagnosis['checks'][] = "  - Apps scraped: {$rateLimitRecord['apps_scraped']}";
        $diagnosis['checks'][] = "  - Cooldown until: {$rateLimitRecord['cooldown_until']}";
        
        $now = new DateTime();
        $cooldownUntil = new DateTime($rateLimitRecord['cooldown_until']);
        if ($now < $cooldownUntil) {
            $diff = $cooldownUntil->diff($now);
            $diagnosis['checks'][] = "  ⚠️ RATE LIMITED - Cooldown active for " . $diff->format('%h hours %i minutes');
        } else {
            $diagnosis['checks'][] = "  ✅ Cooldown expired - can scrape";
        }
    } else {
        $diagnosis['checks'][] = "✅ No rate limit record - IP can scrape";
    }
    
    // Check 4: Review Repository
    $diagnosis['checks'][] = "\n=== REVIEW REPOSITORY TABLE ===";
    
    $stmt = $conn->prepare("
        SELECT app_name, COUNT(*) as count
        FROM review_repository
        GROUP BY app_name
        ORDER BY count DESC
    ");
    $stmt->execute();
    $repoStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($repoStats)) {
        $diagnosis['checks'][] = "⚠️ Review repository is empty";
    } else {
        foreach ($repoStats as $app) {
            $diagnosis['checks'][] = "{$app['app_name']}: {$app['count']} reviews";
        }
    }
    
    // Check 5: Access Reviews Table
    $diagnosis['checks'][] = "\n=== ACCESS REVIEWS TABLE ===";
    
    $stmt = $conn->prepare("
        SELECT app_name, COUNT(*) as count
        FROM access_reviews
        GROUP BY app_name
        ORDER BY count DESC
    ");
    $stmt->execute();
    $accessStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($accessStats)) {
        $diagnosis['checks'][] = "⚠️ Access reviews table is empty";
    } else {
        foreach ($accessStats as $app) {
            $diagnosis['checks'][] = "{$app['app_name']}: {$app['count']} reviews";
        }
    }
    
    // Check 6: Root Cause Analysis
    $diagnosis['checks'][] = "\n=== ROOT CAUSE ANALYSIS ===";
    
    $totalMainReviews = array_sum(array_column($mainTableStats, 'total'));
    
    if ($totalMainReviews < 100) {
        $diagnosis['checks'][] = "❌ ISSUE FOUND: Main reviews table only has $totalMainReviews reviews";
        $diagnosis['checks'][] = "This means the scraper has NOT populated the database with all reviews";
        $diagnosis['checks'][] = "\nPossible causes:";
        $diagnosis['checks'][] = "1. Scraper is rate limited (check rate limit status above)";
        $diagnosis['checks'][] = "2. Scraper is timing out after first page";
        $diagnosis['checks'][] = "3. Scraper hasn't been run yet on live server";
        $diagnosis['checks'][] = "4. Database is different from local (check connection)";
    } else {
        $diagnosis['checks'][] = "✅ Main reviews table has sufficient data ($totalMainReviews reviews)";
        $diagnosis['checks'][] = "The issue is with the Access Reviews API, not the scraper";
    }
    
    // Check 7: Recommendations
    $diagnosis['checks'][] = "\n=== RECOMMENDATIONS ===";
    
    if ($rateLimitRecord && new DateTime() < new DateTime($rateLimitRecord['cooldown_until'])) {
        $diagnosis['checks'][] = "1. CLEAR RATE LIMITS: Run /api/clear-rate-limits.php";
        $diagnosis['checks'][] = "2. Then run fresh scrape for each app";
    } else if ($totalMainReviews < 100) {
        $diagnosis['checks'][] = "1. Run fresh scrape for StoreSEO: POST /api/scrape-app.php with {\"app_name\": \"StoreSEO\"}";
        $diagnosis['checks'][] = "2. Wait for scrape to complete (5-10 minutes)";
        $diagnosis['checks'][] = "3. Check this endpoint again to verify data is populated";
    } else {
        $diagnosis['checks'][] = "1. Access Reviews API is now fixed to query from main reviews table";
        $diagnosis['checks'][] = "2. Refresh the Access Reviews page in browser";
        $diagnosis['checks'][] = "3. Clear browser cache if needed";
    }
    
    $diagnosis['success'] = true;
    
} catch (Exception $e) {
    $diagnosis['success'] = false;
    $diagnosis['error'] = $e->getMessage();
    $diagnosis['trace'] = $e->getTraceAsString();
}

echo json_encode($diagnosis, JSON_PRETTY_PRINT);
?>

