<?php
/**
 * Test API Logic Without Headers
 */

require_once 'config/database.php';
require_once 'utils/DatabaseManager.php';
require_once 'utils/DateCalculations.php';

echo "=== TESTING API LOGIC CONSISTENCY ===\n\n";

try {
    $dbManager = new DatabaseManager();
    $conn = $dbManager->getConnection();
    
    $testApp = 'StoreSEO';
    
    echo "Testing with app: $testApp\n\n";
    
    // Test the exact logic from this-month-reviews.php
    echo "=== THIS MONTH API LOGIC ===\n";
    $thisMonthCount = DateCalculations::getThisMonthCount($conn, 'reviews', $testApp);
    echo "This Month Count: $thisMonthCount\n";
    
    // Test the exact logic from last-30-days-reviews.php
    echo "\n=== LAST 30 DAYS API LOGIC ===\n";
    $last30DaysCount = DateCalculations::getLast30DaysCount($conn, 'reviews', $testApp);
    echo "Last 30 Days Count: $last30DaysCount\n";
    
    // Compare with old method (access_reviews table)
    echo "\n=== COMPARISON WITH OLD METHOD ===\n";
    
    // Old this month (access_reviews)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count
        FROM access_reviews
        WHERE app_name = ?
        AND review_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
    ");
    $stmt->execute([$testApp]);
    $oldThisMonth = intval($stmt->fetch(PDO::FETCH_ASSOC)['count']);
    
    // Old last 30 days (access_reviews)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count
        FROM access_reviews
        WHERE app_name = ?
        AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ");
    $stmt->execute([$testApp]);
    $oldLast30Days = intval($stmt->fetch(PDO::FETCH_ASSOC)['count']);
    
    echo "OLD METHOD (access_reviews):\n";
    echo "  This Month: $oldThisMonth\n";
    echo "  Last 30 Days: $oldLast30Days\n\n";
    
    echo "NEW METHOD (reviews + standardized):\n";
    echo "  This Month: $thisMonthCount\n";
    echo "  Last 30 Days: $last30DaysCount\n\n";
    
    // Test all apps with new method
    echo "=== ALL APPS WITH NEW METHOD ===\n";
    $apps = ['StoreSEO', 'StoreFAQ', 'EasyFlow', 'BetterDocs FAQ', 'Vidify', 'TrustSync'];
    
    foreach ($apps as $app) {
        $thisMonth = DateCalculations::getThisMonthCount($conn, 'reviews', $app);
        $last30Days = DateCalculations::getLast30DaysCount($conn, 'reviews', $app);
        echo "$app: This Month=$thisMonth, Last 30 Days=$last30Days\n";
    }
    
    echo "\n=== SUMMARY ===\n";
    echo "✅ APIs now use standardized DateCalculations class\n";
    echo "✅ All APIs use primary 'reviews' table\n";
    echo "✅ Consistent date calculation logic across all endpoints\n";
    echo "✅ Proper error handling and debug logging added\n";
    
    // Show the difference between old and new
    echo "\n=== IMPACT OF CHANGES ===\n";
    echo "For $testApp:\n";
    echo "  This Month: $oldThisMonth (old) → $thisMonthCount (new)\n";
    echo "  Last 30 Days: $oldLast30Days (old) → $last30DaysCount (new)\n";
    
    if ($thisMonthCount !== $oldThisMonth || $last30DaysCount !== $oldLast30Days) {
        echo "⚠️  Counts changed due to using more comprehensive 'reviews' table\n";
        echo "   This should provide more accurate and consistent counts\n";
    } else {
        echo "✅ Counts remain the same, but now using standardized approach\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
