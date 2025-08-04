<?php
require_once 'utils/DatabaseManager.php';

$db = new DatabaseManager();

echo "Checking review dates in database...\n";

// Get ALL July reviews for StoreSEO
$query = "SELECT DATE(review_date) as review_date, COUNT(*) as count FROM reviews
          WHERE app_name = 'StoreSEO'
          AND MONTH(review_date) = 7 AND YEAR(review_date) = 2025
          GROUP BY DATE(review_date)
          ORDER BY review_date DESC";

$stmt = $db->getConnection()->prepare($query);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "ALL July 2025 review dates:\n";
$total_manual_count = 0;
foreach($results as $row) {
    echo $row['review_date'] . ' - Count: ' . $row['count'] . "\n";
    $total_manual_count += $row['count'];
}
echo "Manual total: $total_manual_count\n";

// Check July 2025 count
echo "\nJuly 2025 reviews in database:\n";
$july_count = $db->getThisMonthReviews('StoreSEO');
echo "July count: $july_count\n";

// Check last 30 days count
echo "\nLast 30 days reviews in database:\n";
$last30_count = $db->getLast30DaysReviews('StoreSEO');
echo "Last 30 days count: $last30_count\n";

// Check date range in database
echo "\nDate range in database:\n";
$query2 = "SELECT MIN(review_date) as min_date, MAX(review_date) as max_date 
           FROM reviews WHERE app_name = 'StoreSEO'";
$stmt2 = $db->getConnection()->prepare($query2);
$stmt2->execute();
$range = $stmt2->fetch(PDO::FETCH_ASSOC);
echo "Date range: " . $range['min_date'] . " to " . $range['max_date'] . "\n";

// Check current date
echo "\nCurrent date info:\n";
echo "Today: " . date('Y-m-d') . "\n";
echo "30 days ago: " . date('Y-m-d', strtotime('-30 days')) . "\n";
echo "Current month: " . date('Y-m') . "\n";
?>
