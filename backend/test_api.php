<?php
// Test the enhanced analytics API directly
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Clear all caches first
$conn->exec('DELETE FROM review_cache');

// Test StoreFAQ data
$stmt = $conn->prepare('SELECT COUNT(*) as count FROM access_reviews WHERE app_name = ? AND review_date >= DATE_FORMAT(CURDATE(), "%Y-%m-01")');
$stmt->execute(['StoreFAQ']);
$thisMonth = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

$stmt = $conn->prepare('SELECT COUNT(*) as count FROM access_reviews WHERE app_name = ? AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)');
$stmt->execute(['StoreFAQ']);
$last30Days = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Output results
file_put_contents('test_results.txt', "StoreFAQ: This Month $thisMonth, Last 30 Days $last30Days\n");

// Test the enhanced analytics API
$url = 'http://localhost:8000/api/enhanced-analytics.php?app=StoreFAQ';
$response = file_get_contents($url);
file_put_contents('api_response.json', $response);

echo "Test completed. Check test_results.txt and api_response.json files.\n";
?>
