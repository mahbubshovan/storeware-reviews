<?php
// Simple API test
$pdo = new PDO("mysql:host=localhost;dbname=shopify_reviews", 'root', '');

// Clear and insert simple test data
$pdo->exec("DELETE FROM access_reviews WHERE app_name = 'StoreSEO'");
$pdo->exec("DELETE FROM reviews WHERE app_name = 'StoreSEO'");

// Insert exactly 5 this month reviews
for ($i = 1; $i <= 5; $i++) {
    $date = '2025-09-' . sprintf('%02d', $i + 10);
    $pdo->exec("INSERT INTO access_reviews (app_name, store_name, country_name, rating, review_content, review_date) VALUES ('StoreSEO', 'Store$i', 'US', 5, 'Test review', '$date')");
    $pdo->exec("INSERT INTO reviews (app_name, store_name, country_name, rating, review_content, review_date, is_active) VALUES ('StoreSEO', 'Store$i', 'US', 5, 'Test review', '$date', 1)");
}

// Insert exactly 8 more for last 30 days (total 13)
for ($i = 6; $i <= 13; $i++) {
    $date = '2025-08-' . sprintf('%02d', $i + 15);
    $pdo->exec("INSERT INTO access_reviews (app_name, store_name, country_name, rating, review_content, review_date) VALUES ('StoreSEO', 'Store$i', 'US', 5, 'Test review', '$date')");
    $pdo->exec("INSERT INTO reviews (app_name, store_name, country_name, rating, review_content, review_date, is_active) VALUES ('StoreSEO', 'Store$i', 'US', 5, 'Test review', '$date', 1)");
}

echo "Simple test data inserted for StoreSEO\n";

// Test the count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM access_reviews WHERE app_name = 'StoreSEO' AND review_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')");
$stmt->execute();
$thisMonth = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM access_reviews WHERE app_name = 'StoreSEO' AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
$stmt->execute();
$last30Days = $stmt->fetchColumn();

echo "StoreSEO: This Month $thisMonth, Last 30 Days $last30Days\n";
echo "Should be: This Month 5, Last 30 Days 13\n";

file_put_contents('simple_test_result.txt', "StoreSEO: This Month $thisMonth, Last 30 Days $last30Days\n");
?>
