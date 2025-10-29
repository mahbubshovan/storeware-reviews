<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Check StoreSEO reviews
$stmt = $conn->prepare('SELECT COUNT(*) FROM reviews WHERE app_name = ?');
$stmt->execute(['StoreSEO']);
$count = $stmt->fetchColumn();
echo "StoreSEO reviews in main table: $count\n";

// Get sample reviews
$stmt = $conn->prepare('SELECT id, review_date, store_name, rating FROM reviews WHERE app_name = ? ORDER BY review_date DESC LIMIT 5');
$stmt->execute(['StoreSEO']);
$samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Sample reviews:\n";
foreach ($samples as $s) {
    echo "  ID: {$s['id']}, Date: {$s['review_date']}, Store: {$s['store_name']}, Rating: {$s['rating']}★\n";
}

// Check date range
$stmt = $conn->prepare('SELECT MIN(review_date) as oldest, MAX(review_date) as newest FROM reviews WHERE app_name = ?');
$stmt->execute(['StoreSEO']);
$dates = $stmt->fetch(PDO::FETCH_ASSOC);
echo "\nDate range: {$dates['oldest']} to {$dates['newest']}\n";

// Check access_reviews
$stmt = $conn->prepare('SELECT COUNT(*) FROM access_reviews WHERE app_name = ?');
$stmt->execute(['StoreSEO']);
$accessCount = $stmt->fetchColumn();
echo "\nAccess reviews: $accessCount\n";

// Check last 30 days
$stmt = $conn->prepare('SELECT COUNT(*) FROM reviews WHERE app_name = ? AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)');
$stmt->execute(['StoreSEO']);
$last30 = $stmt->fetchColumn();
echo "Last 30 days: $last30\n";
?>

