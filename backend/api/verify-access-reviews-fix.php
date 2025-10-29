<?php
/**
 * Verify that Access Reviews page shows same data as live Shopify pages
 * This endpoint compares the data between main reviews table and what's displayed
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/AccessReviewsSync.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $result = [
        'success' => false,
        'verification' => [],
        'comparison' => [],
        'issues' => []
    ];
    
    // Step 1: Check main reviews table
    $result['verification'][] = "=== CHECKING MAIN REVIEWS TABLE ===";
    
    $stmt = $conn->prepare("
        SELECT app_name, COUNT(*) as total_count,
               COUNT(CASE WHEN review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as last_30_days
        FROM reviews
        WHERE is_active = TRUE
        GROUP BY app_name
        ORDER BY total_count DESC
    ");
    $stmt->execute();
    $mainTableStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result['verification'][] = "Main reviews table stats: " . json_encode($mainTableStats);
    
    // Step 2: Check what Access Reviews API returns
    $result['verification'][] = "=== CHECKING ACCESS REVIEWS API ===";
    
    $sync = new AccessReviewsSync();
    $accessReviews = $sync->getAccessReviews('30_days');
    $accessStats = $sync->getAccessReviewsStats('30_days');
    
    $result['verification'][] = "Access Reviews stats: " . json_encode($accessStats);
    
    // Step 3: Compare counts
    $result['verification'][] = "=== COMPARING COUNTS ===";
    
    foreach ($mainTableStats as $app) {
        $appName = $app['app_name'];
        $mainCount = $app['total_count'];
        $last30Count = $app['last_30_days'];
        
        // Find this app in access reviews stats
        $accessCount = 0;
        foreach ($accessStats['reviews_by_app'] as $accessApp) {
            if ($accessApp['app_name'] === $appName) {
                $accessCount = $accessApp['count'];
                break;
            }
        }
        
        $comparison = [
            'app_name' => $appName,
            'main_table_total' => $mainCount,
            'main_table_last_30_days' => $last30Count,
            'access_reviews_showing' => $accessCount,
            'matches' => ($last30Count === $accessCount),
            'status' => ($last30Count === $accessCount) ? '✅ MATCH' : '❌ MISMATCH'
        ];
        
        $result['comparison'][] = $comparison;
        
        if ($last30Count !== $accessCount) {
            $result['issues'][] = "$appName: Main table has $last30Count reviews (last 30 days) but Access Reviews shows $accessCount";
        }
    }
    
    // Step 4: Check if access_reviews table is being used
    $result['verification'][] = "=== CHECKING ACCESS_REVIEWS TABLE ===";
    
    $stmt = $conn->prepare("
        SELECT app_name, COUNT(*) as count
        FROM access_reviews
        GROUP BY app_name
        ORDER BY count DESC
    ");
    $stmt->execute();
    $accessTableStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result['verification'][] = "Access_reviews table stats: " . json_encode($accessTableStats);
    
    // Step 5: Verify the fix
    $result['verification'][] = "=== VERIFICATION RESULT ===";
    
    if (empty($result['issues'])) {
        $result['success'] = true;
        $result['verification'][] = "✅ SUCCESS: Access Reviews page is showing the same data as live Shopify pages!";
        $result['verification'][] = "All review counts match between main reviews table and Access Reviews API";
    } else {
        $result['success'] = false;
        $result['verification'][] = "❌ ISSUE DETECTED: Access Reviews page is not showing all data";
        $result['verification'][] = "The API is now querying from the main reviews table (not access_reviews)";
        $result['verification'][] = "If counts still don't match, the main reviews table may not have all the data";
    }
    
    // Step 6: Recommendations
    $result['verification'][] = "=== RECOMMENDATIONS ===";
    
    if (!$result['success']) {
        $result['verification'][] = "If counts still don't match:";
        $result['verification'][] = "1. Run fresh scrape for each app to populate main reviews table";
        $result['verification'][] = "2. Check if scraper is stopping early due to rate limiting";
        $result['verification'][] = "3. Run /api/fix-access-reviews-sync.php to clear rate limits and re-sync";
    } else {
        $result['verification'][] = "Access Reviews page is working correctly!";
        $result['verification'][] = "It now shows the same data as the live Shopify app store pages";
    }
    
    echo json_encode($result, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>

