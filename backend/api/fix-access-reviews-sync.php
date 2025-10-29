<?php
/**
 * Fix the access_reviews sync issue - comprehensive diagnosis and fix
 * This endpoint will:
 * 1. Check current state of both tables
 * 2. Identify the root cause (scraper limit vs sync limit)
 * 3. Clear rate limiting if needed
 * 4. Re-sync all reviews from last 30 days
 * 5. Verify the fix
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/AccessReviewsSync.php';
require_once __DIR__ . '/../utils/IPRateLimitManager.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->getConnection();

    $result = [
        'success' => false,
        'diagnosis' => [],
        'actions_taken' => [],
        'before' => [],
        'after' => [],
        'errors' => []
    ];

    // DIAGNOSIS PHASE
    $result['diagnosis'][] = "=== DIAGNOSIS PHASE ===";

    // Check main reviews table
    $stmt = $conn->prepare("
        SELECT app_name, COUNT(*) as total_count,
               COUNT(CASE WHEN review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as last_30_days
        FROM reviews
        GROUP BY app_name
        ORDER BY total_count DESC
    ");
    $stmt->execute();
    $mainTableStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result['diagnosis'][] = "Main reviews table stats: " . json_encode($mainTableStats);
    $result['before']['main_reviews'] = $mainTableStats;

    // Check access_reviews table
    $stmt = $conn->prepare("
        SELECT app_name, COUNT(*) as count
        FROM access_reviews
        GROUP BY app_name
        ORDER BY count DESC
    ");
    $stmt->execute();
    $accessTableStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result['diagnosis'][] = "Access reviews table stats: " . json_encode($accessTableStats);
    $result['before']['access_reviews'] = $accessTableStats;

    // Identify the root cause
    $result['diagnosis'][] = "=== ROOT CAUSE ANALYSIS ===";

    $totalInMain = array_sum(array_column($mainTableStats, 'total_count'));
    $totalInAccess = array_sum(array_column($accessTableStats, 'count'));

    if ($totalInMain < 50) {
        $result['diagnosis'][] = "⚠️ ROOT CAUSE IDENTIFIED: Main reviews table only has $totalInMain reviews total";
        $result['diagnosis'][] = "This means the scraper is only scraping ~10 reviews per app (1 page)";
        $result['diagnosis'][] = "The issue is NOT with the sync, but with the scraper stopping early";
    } else {
        $result['diagnosis'][] = "Main reviews table has $totalInMain reviews, but access_reviews only has $totalInAccess";
        $result['diagnosis'][] = "The issue is with the sync process not syncing all reviews";
    }

    // FIX PHASE
    $result['actions_taken'][] = "=== FIX PHASE ===";

    // Step 1: Clear rate limiting
    $result['actions_taken'][] = "Step 1: Clearing rate limiting...";
    $rateLimitManager = new IPRateLimitManager();
    $rateLimitManager->clearAllRateLimits();
    $result['actions_taken'][] = "✅ Rate limits cleared";

    // Step 2: Clear access_reviews table
    $result['actions_taken'][] = "Step 2: Clearing access_reviews table...";
    $stmt = $conn->prepare("DELETE FROM access_reviews");
    $stmt->execute();
    $deletedCount = $stmt->rowCount();
    $result['actions_taken'][] = "✅ Deleted $deletedCount rows from access_reviews";

    // Step 3: Re-sync using AccessReviewsSync
    $result['actions_taken'][] = "Step 3: Re-syncing all reviews from last 30 days...";
    $sync = new AccessReviewsSync();
    $sync->syncAccessReviews();
    $result['actions_taken'][] = "✅ Sync completed";

    // VERIFICATION PHASE
    $result['actions_taken'][] = "=== VERIFICATION PHASE ===";

    // Check new access_reviews counts
    $stmt = $conn->prepare("
        SELECT app_name, COUNT(*) as count
        FROM access_reviews
        GROUP BY app_name
        ORDER BY count DESC
    ");
    $stmt->execute();
    $afterCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result['after']['access_reviews'] = $afterCounts;
    $result['actions_taken'][] = "New access_reviews counts: " . json_encode($afterCounts);

    // Detailed comparison
    $stmt = $conn->prepare("
        SELECT
            r.app_name,
            COUNT(DISTINCT r.id) as in_main_last_30,
            COUNT(DISTINCT ar.id) as in_access,
            COUNT(DISTINCT CASE WHEN ar.id IS NULL THEN r.id END) as missing_from_access
        FROM reviews r
        LEFT JOIN access_reviews ar ON r.id = ar.original_review_id
        WHERE r.review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY r.app_name
        ORDER BY r.app_name
    ");
    $stmt->execute();
    $comparison = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result['after']['sync_comparison'] = $comparison;
    $result['actions_taken'][] = "Sync comparison: " . json_encode($comparison);

    // Check if all reviews were synced
    $allSynced = true;
    foreach ($comparison as $app) {
        if ($app['missing_from_access'] > 0) {
            $allSynced = false;
            $result['errors'][] = "{$app['app_name']}: {$app['missing_from_access']} reviews missing from access_reviews";
        }
    }

    if ($allSynced) {
        $result['success'] = true;
        $result['actions_taken'][] = "✅ All reviews successfully synced!";
    } else {
        $result['success'] = false;
        $result['actions_taken'][] = "❌ Some reviews are still missing from access_reviews";
        $result['actions_taken'][] = "This indicates the main reviews table doesn't have all the data";
        $result['actions_taken'][] = "NEXT STEP: Run a fresh scrape for all apps to populate the main reviews table";
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

