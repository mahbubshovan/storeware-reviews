<?php
try {
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();

    echo "FINAL VERIFICATION\n";
    echo "==================\n\n";

    // Test StoreFAQ first
    $stmt = $conn->prepare('SELECT COUNT(*) as count FROM access_reviews WHERE app_name = ? AND review_date >= DATE_FORMAT(CURDATE(), "%Y-%m-01")');
    $stmt->execute(['StoreFAQ']);
    $storefaqThisMonth = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    $stmt = $conn->prepare('SELECT COUNT(*) as count FROM access_reviews WHERE app_name = ? AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)');
    $stmt->execute(['StoreFAQ']);
    $storefaqLast30Days = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    echo "StoreFAQ: This Month $storefaqThisMonth (target 6), Last 30 Days $storefaqLast30Days (target 12)\n";

    // Test all apps
    $targets = [
        'StoreSEO' => ['this_month' => 5, 'last_30_days' => 13],
        'StoreFAQ' => ['this_month' => 6, 'last_30_days' => 12],
        'EasyFlow' => ['this_month' => 5, 'last_30_days' => 13],
        'TrustSync' => ['this_month' => 1, 'last_30_days' => 1],
        'BetterDocs FAQ Knowledge Base' => ['this_month' => 1, 'last_30_days' => 3],
        'Vidify' => ['this_month' => 0, 'last_30_days' => 0]
    ];

    foreach ($targets as $app => $target) {
        $stmt = $conn->prepare('SELECT COUNT(*) as count FROM access_reviews WHERE app_name = ? AND review_date >= DATE_FORMAT(CURDATE(), "%Y-%m-01")');
        $stmt->execute([$app]);
        $actualThisMonth = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        $stmt = $conn->prepare('SELECT COUNT(*) as count FROM access_reviews WHERE app_name = ? AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)');
        $stmt->execute([$app]);
        $actualLast30Days = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        $thisMonthStatus = ($actualThisMonth == $target['this_month']) ? 'MATCH' : 'MISMATCH';
        $last30DaysStatus = ($actualLast30Days == $target['last_30_days']) ? 'MATCH' : 'MISMATCH';

        echo "$app: This Month $actualThisMonth (target {$target['this_month']}) $thisMonthStatus, Last 30 Days $actualLast30Days (target {$target['last_30_days']}) $last30DaysStatus\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
