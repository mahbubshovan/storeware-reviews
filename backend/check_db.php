<?php
require_once __DIR__ . '/config/database.php';

$database = new Database();
$pdo = $database->getConnection();

echo "=== CHECKING DATABASE REAL DATA ===\n";

// Check access_reviews table
$stmt = $pdo->query("SELECT app_name, COUNT(*) as total FROM access_reviews GROUP BY app_name ORDER BY app_name");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "access_reviews table:\n";
foreach ($results as $row) {
    echo "  {$row['app_name']}: {$row['total']} total reviews\n";
}

// Check reviews table  
$stmt = $pdo->query("SELECT app_name, COUNT(*) as total FROM reviews GROUP BY app_name ORDER BY app_name");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\nreviews table:\n";
foreach ($results as $row) {
    echo "  {$row['app_name']}: {$row['total']} total reviews\n";
}

// Check StoreFAQ specifically
echo "\n=== STOREFAQ DETAILED CHECK ===\n";

$stmt = $pdo->prepare("SELECT COUNT(*) FROM access_reviews WHERE app_name = 'StoreFAQ' AND review_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')");
$stmt->execute();
$thisMonth = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM access_reviews WHERE app_name = 'StoreFAQ' AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
$stmt->execute();
$last30Days = $stmt->fetchColumn();

echo "StoreFAQ in access_reviews: This Month $thisMonth, Last 30 Days $last30Days\n";

$stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE app_name = 'StoreFAQ' AND review_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')");
$stmt->execute();
$thisMonthReviews = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE app_name = 'StoreFAQ' AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
$stmt->execute();
$last30DaysReviews = $stmt->fetchColumn();

echo "StoreFAQ in reviews: This Month $thisMonthReviews, Last 30 Days $last30DaysReviews\n";

echo "\nTarget: StoreFAQ should be This Month 6, Last 30 Days 12\n";

// Write to file for verification
file_put_contents('db_check_results.txt', "StoreFAQ access_reviews: This Month $thisMonth, Last 30 Days $last30Days\nStoreFAQ reviews: This Month $thisMonthReviews, Last 30 Days $last30DaysReviews\n");
?>
