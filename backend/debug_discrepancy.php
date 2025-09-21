<?php
// Debug the discrepancy between access reviews page and main review page
$pdo = new PDO("mysql:host=localhost;dbname=shopify_reviews", 'root', '');

echo "=== DEBUGGING DISCREPANCY ===\n";
echo "StoreSEO showing 517 in access review page vs 519 in main review page\n\n";

// Check reviews table (used by access-reviews-tabbed.php)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE app_name = 'StoreSEO' AND is_active = TRUE");
$stmt->execute();
$reviewsTableCount = $stmt->fetchColumn();

echo "reviews table (access-reviews-tabbed.php uses this): $reviewsTableCount\n";

// Check access_reviews table (used by analytics APIs)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM access_reviews WHERE app_name = 'StoreSEO'");
$stmt->execute();
$accessReviewsTableCount = $stmt->fetchColumn();

echo "access_reviews table (analytics APIs use this): $accessReviewsTableCount\n";

// Check this month and last 30 days from both tables
echo "\n=== THIS MONTH & LAST 30 DAYS COMPARISON ===\n";

// From reviews table
$stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE app_name = 'StoreSEO' AND is_active = TRUE AND review_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')");
$stmt->execute();
$reviewsThisMonth = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE app_name = 'StoreSEO' AND is_active = TRUE AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
$stmt->execute();
$reviewsLast30Days = $stmt->fetchColumn();

echo "reviews table: This Month $reviewsThisMonth, Last 30 Days $reviewsLast30Days\n";

// From access_reviews table
$stmt = $pdo->prepare("SELECT COUNT(*) FROM access_reviews WHERE app_name = 'StoreSEO' AND review_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')");
$stmt->execute();
$accessThisMonth = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM access_reviews WHERE app_name = 'StoreSEO' AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
$stmt->execute();
$accessLast30Days = $stmt->fetchColumn();

echo "access_reviews table: This Month $accessThisMonth, Last 30 Days $accessLast30Days\n";

echo "\n=== YOUR TARGET COUNTS ===\n";
echo "StoreSEO should be: This Month 5, Last 30 Days 13\n";

echo "\n=== PROBLEM IDENTIFIED ===\n";
echo "1. access-reviews-tabbed.php uses 'reviews' table\n";
echo "2. Analytics APIs use 'access_reviews' table\n";
echo "3. These tables have different data!\n";
echo "4. Need to sync both tables with your exact real counts\n";

// Write results to file
file_put_contents('discrepancy_debug.txt', 
    "reviews table: $reviewsTableCount total\n" .
    "access_reviews table: $accessReviewsTableCount total\n" .
    "reviews table: This Month $reviewsThisMonth, Last 30 Days $reviewsLast30Days\n" .
    "access_reviews table: This Month $accessThisMonth, Last 30 Days $accessLast30Days\n" .
    "TARGET: This Month 5, Last 30 Days 13\n"
);

echo "\nResults saved to discrepancy_debug.txt\n";
?>
