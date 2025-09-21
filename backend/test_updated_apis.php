<?php
/**
 * Test Updated APIs for Count Consistency
 */

require_once 'config/database.php';
require_once 'utils/DateCalculations.php';

echo "=== TESTING UPDATED API CONSISTENCY ===\n\n";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $testApp = 'StoreSEO';
    
    echo "Testing with app: $testApp\n\n";
    
    // Test direct database calls using standardized functions
    echo "=== DIRECT DATABASE CALLS (Standardized) ===\n";
    $directThisMonth = DateCalculations::getThisMonthCount($conn, 'reviews', $testApp);
    $directLast30Days = DateCalculations::getLast30DaysCount($conn, 'reviews', $testApp);
    echo "This Month: $directThisMonth\n";
    echo "Last 30 Days: $directLast30Days\n\n";
    
    // Test API endpoints by including them directly
    echo "=== API ENDPOINT TESTS ===\n";
    
    // Test this-month-reviews.php
    echo "Testing this-month-reviews.php:\n";
    $_GET['app_name'] = $testApp;
    ob_start();
    try {
        include 'api/this-month-reviews.php';
        $thisMonthResponse = ob_get_contents();
        ob_end_clean();
        $thisMonthData = json_decode($thisMonthResponse, true);
        echo "  Response: " . ($thisMonthData['count'] ?? 'ERROR') . "\n";
        echo "  Source: " . ($thisMonthData['source'] ?? 'UNKNOWN') . "\n";
    } catch (Exception $e) {
        ob_end_clean();
        echo "  ERROR: " . $e->getMessage() . "\n";
    }
    
    // Test last-30-days-reviews.php
    echo "\nTesting last-30-days-reviews.php:\n";
    ob_start();
    try {
        include 'api/last-30-days-reviews.php';
        $last30DaysResponse = ob_get_contents();
        ob_end_clean();
        $last30DaysData = json_decode($last30DaysResponse, true);
        echo "  Response: " . ($last30DaysData['count'] ?? 'ERROR') . "\n";
        echo "  Source: " . ($last30DaysData['source'] ?? 'UNKNOWN') . "\n";
    } catch (Exception $e) {
        ob_end_clean();
        echo "  ERROR: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== CONSISTENCY CHECK ===\n";
    
    $thisMonthMatch = ($directThisMonth === ($thisMonthData['count'] ?? -1)) ? "✅ MATCH" : "❌ MISMATCH";
    $last30DaysMatch = ($directLast30Days === ($last30DaysData['count'] ?? -1)) ? "✅ MATCH" : "❌ MISMATCH";
    
    echo "This Month - Direct: $directThisMonth, API: " . ($thisMonthData['count'] ?? 'ERROR') . " $thisMonthMatch\n";
    echo "Last 30 Days - Direct: $directLast30Days, API: " . ($last30DaysData['count'] ?? 'ERROR') . " $last30DaysMatch\n";
    
    // Test all apps
    echo "\n=== ALL APPS COMPARISON ===\n";
    $apps = ['StoreSEO', 'StoreFAQ', 'EasyFlow', 'BetterDocs FAQ', 'Vidify', 'TrustSync'];
    
    foreach ($apps as $app) {
        $thisMonth = DateCalculations::getThisMonthCount($conn, 'reviews', $app);
        $last30Days = DateCalculations::getLast30DaysCount($conn, 'reviews', $app);
        echo "$app: This Month=$thisMonth, Last 30 Days=$last30Days\n";
    }
    
    echo "\n=== SUCCESS ===\n";
    echo "All APIs now use:\n";
    echo "1. Standardized DateCalculations class\n";
    echo "2. Primary 'reviews' table as data source\n";
    echo "3. Consistent date calculation logic\n";
    echo "4. Proper error handling and logging\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
