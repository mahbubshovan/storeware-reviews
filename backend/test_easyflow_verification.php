<?php
require_once 'utils/DatabaseManager.php';

echo "=== EASYFLOW DATABASE VERIFICATION ===\n\n";

try {
    $dbManager = new DatabaseManager();
    $conn = $dbManager->getConnection();

    // Check reviews data
    echo "1. Checking EasyFlow reviews data...\n";
    $stmt = $conn->prepare("SELECT COUNT(*) as total, MIN(review_date) as earliest, MAX(review_date) as latest FROM reviews WHERE app_name = 'EasyFlow'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Total reviews in DB: {$result['total']}\n";
    echo "Date range: {$result['earliest']} to {$result['latest']}\n\n";

    // Check this month count
    echo "2. Checking this month count...\n";
    $currentMonth = date('Y-m');
    $firstOfMonth = date('Y-m-01');
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM reviews WHERE app_name = 'EasyFlow' AND review_date >= ?");
    $stmt->execute([$firstOfMonth]);
    $thisMonthCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "This month (from {$firstOfMonth}): {$thisMonthCount} reviews\n\n";

    // Check last 30 days count
    echo "3. Checking last 30 days count...\n";
    $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM reviews WHERE app_name = 'EasyFlow' AND review_date >= ?");
    $stmt->execute([$thirtyDaysAgo]);
    $last30DaysCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "Last 30 days (from {$thirtyDaysAgo}): {$last30DaysCount} reviews\n\n";

    // Check metadata
    echo "4. Checking metadata...\n";
    $stmt = $conn->prepare("SELECT * FROM app_metadata WHERE app_name = 'EasyFlow'");
    $stmt->execute();
    $metadata = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($metadata) {
        echo "Total reviews: {$metadata['total_reviews']}\n";
        echo "Overall rating: {$metadata['overall_rating']}\n";
        echo "5-star: {$metadata['five_star_total']}\n";
        echo "4-star: {$metadata['four_star_total']}\n";
        echo "3-star: {$metadata['three_star_total']}\n";
        echo "2-star: {$metadata['two_star_total']}\n";
        echo "1-star: {$metadata['one_star_total']}\n";
        
        $totalStars = $metadata['five_star_total'] + $metadata['four_star_total'] + 
                     $metadata['three_star_total'] + $metadata['two_star_total'] + $metadata['one_star_total'];
        echo "Star count verification: {$totalStars} vs {$metadata['total_reviews']}\n";
    } else {
        echo "No metadata found!\n";
    }

    echo "\n=== EXPECTED VS ACTUAL ===\n";
    echo "Expected this month: 1 (August 5 review)\n";
    echo "Actual this month: {$thisMonthCount}\n";
    echo "Expected last 30 days: ~9-11 (July 10 - August 9)\n";
    echo "Actual last 30 days: {$last30DaysCount}\n";
    echo "Expected total reviews: 295\n";
    echo "Actual total reviews: {$metadata['total_reviews']}\n";
    echo "Expected 5-star: 292\n";
    echo "Actual 5-star: {$metadata['five_star_total']}\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
