<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    echo "=== CHECKING REAL DATABASE DATA ===\n";
    
    $apps = ['StoreSEO', 'StoreFAQ', 'EasyFlow', 'TrustSync', 'BetterDocs FAQ Knowledge Base', 'Vidify'];
    
    foreach ($apps as $app) {
        echo "\n$app:\n";
        
        // Check access_reviews table
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM access_reviews WHERE app_name = ? AND review_date >= DATE_FORMAT(CURDATE(), "%Y-%m-01")');
        $stmt->execute([$app]);
        $thisMonth = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM access_reviews WHERE app_name = ? AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)');
        $stmt->execute([$app]);
        $last30Days = $stmt->fetchColumn();
        
        echo "  access_reviews: This Month $thisMonth, Last 30 Days $last30Days\n";
        
        // Check reviews table
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM reviews WHERE app_name = ? AND review_date >= DATE_FORMAT(CURDATE(), "%Y-%m-01")');
        $stmt->execute([$app]);
        $thisMonthReviews = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM reviews WHERE app_name = ? AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)');
        $stmt->execute([$app]);
        $last30DaysReviews = $stmt->fetchColumn();
        
        echo "  reviews: This Month $thisMonthReviews, Last 30 Days $last30DaysReviews\n";
    }
    
    echo "\n=== TARGET COUNTS (YOUR REAL DATA) ===\n";
    $targets = [
        'StoreSEO' => ['this_month' => 5, 'last_30_days' => 13],
        'StoreFAQ' => ['this_month' => 6, 'last_30_days' => 12],
        'EasyFlow' => ['this_month' => 5, 'last_30_days' => 13],
        'TrustSync' => ['this_month' => 1, 'last_30_days' => 1],
        'BetterDocs FAQ Knowledge Base' => ['this_month' => 1, 'last_30_days' => 3],
        'Vidify' => ['this_month' => 0, 'last_30_days' => 0]
    ];
    
    foreach ($targets as $app => $target) {
        echo "$app: Should be This Month {$target['this_month']}, Last 30 Days {$target['last_30_days']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
