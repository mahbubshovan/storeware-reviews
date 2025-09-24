<?php
require_once __DIR__ . '/config/database.php';

$database = new Database();
$pdo = $database->getConnection();

echo "=== VERIFYING DATABASE DATA ===\n";

// Check StoreSEO specifically
$stmt = $pdo->prepare("SELECT COUNT(*) FROM access_reviews WHERE app_name = 'StoreSEO' AND review_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')");
$stmt->execute();
$thisMonth = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM access_reviews WHERE app_name = 'StoreSEO' AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
$stmt->execute();
$last30Days = $stmt->fetchColumn();

echo "StoreSEO access_reviews: This Month $thisMonth, Last 30 Days $last30Days\n";
echo "Target: This Month 5, Last 30 Days 13\n";

// Check reviews table too
$stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE app_name = 'StoreSEO' AND is_active = TRUE AND review_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')");
$stmt->execute();
$thisMonthReviews = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE app_name = 'StoreSEO' AND is_active = TRUE AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
$stmt->execute();
$last30DaysReviews = $stmt->fetchColumn();

echo "StoreSEO reviews: This Month $thisMonthReviews, Last 30 Days $last30DaysReviews\n";

// Check total reviews
$stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE app_name = 'StoreSEO' AND is_active = TRUE");
$stmt->execute();
$totalReviews = $stmt->fetchColumn();

echo "StoreSEO total reviews: $totalReviews\n";

// Output to file for verification
file_put_contents('db_verification.txt', "StoreSEO access_reviews: This Month $thisMonth, Last 30 Days $last30Days\nStoreSEO reviews: This Month $thisMonthReviews, Last 30 Days $last30DaysReviews\nStoreSEO total: $totalReviews\n");

echo "Results saved to db_verification.txt\n";
?>
