<?php
/**
 * Complete StoreSEO Fix - All-in-one solution
 * 1. Clears rate limiting
 * 2. Scrapes all reviews
 * 3. Syncs to access_reviews
 * 4. Returns final status
 */

set_time_limit(300);
ini_set('max_execution_time', 300);

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../scraper/UniversalLiveScraper.php';
require_once __DIR__ . '/../utils/AccessReviewsSync.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $steps = [];
    
    // Step 1: Clear rate limit
    $steps[] = ['step' => 1, 'action' => 'Clearing rate limit for StoreSEO...'];
    $stmt = $conn->prepare("DELETE FROM ip_scrape_limits WHERE app_name = 'StoreSEO'");
    $stmt->execute();
    $steps[] = ['step' => 1, 'status' => 'complete', 'message' => 'Rate limit cleared'];
    
    // Step 2: Get current count before scraping
    $stmt = $conn->prepare('SELECT COUNT(*) FROM reviews WHERE app_name = ?');
    $stmt->execute(['StoreSEO']);
    $beforeCount = $stmt->fetchColumn();
    $steps[] = ['step' => 2, 'action' => 'Current reviews before scrape', 'count' => $beforeCount];
    
    // Step 3: Scrape StoreSEO
    $steps[] = ['step' => 3, 'action' => 'Scraping StoreSEO from live Shopify...'];
    $scraper = new UniversalLiveScraper();
    $result = $scraper->scrapeApp('storeseo', 'StoreSEO');
    $steps[] = ['step' => 3, 'status' => 'complete', 'scrape_result' => $result];
    
    // Step 4: Get count after scraping
    $stmt = $conn->prepare('SELECT COUNT(*) FROM reviews WHERE app_name = ?');
    $stmt->execute(['StoreSEO']);
    $afterCount = $stmt->fetchColumn();
    $steps[] = ['step' => 4, 'action' => 'Reviews after scrape', 'count' => $afterCount, 'added' => $afterCount - $beforeCount];
    
    // Step 5: Sync to access_reviews
    $steps[] = ['step' => 5, 'action' => 'Syncing to access_reviews table...'];
    $accessSync = new AccessReviewsSync();
    $accessSync->syncAccessReviews();
    
    $stmt = $conn->prepare('SELECT COUNT(*) FROM access_reviews WHERE app_name = ?');
    $stmt->execute(['StoreSEO']);
    $accessCount = $stmt->fetchColumn();
    $steps[] = ['step' => 5, 'status' => 'complete', 'access_reviews_count' => $accessCount];
    
    // Step 6: Get final statistics
    $stmt = $conn->prepare('SELECT COUNT(*) FROM reviews WHERE app_name = ? AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)');
    $stmt->execute(['StoreSEO']);
    $last30Count = $stmt->fetchColumn();
    
    $stmt = $conn->prepare('SELECT MIN(review_date) as oldest, MAX(review_date) as newest FROM reviews WHERE app_name = ?');
    $stmt->execute(['StoreSEO']);
    $dates = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $conn->prepare('
        SELECT 
            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
        FROM reviews WHERE app_name = ?
    ');
    $stmt->execute(['StoreSEO']);
    $distribution = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $steps[] = ['step' => 6, 'action' => 'Final statistics', 'status' => 'complete'];
    
    echo json_encode([
        'success' => true,
        'steps' => $steps,
        'final_status' => [
            'total_reviews' => $afterCount,
            'access_reviews' => $accessCount,
            'last_30_days' => $last30Count,
            'date_range' => $dates,
            'rating_distribution' => $distribution
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'steps' => $steps ?? [],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>

