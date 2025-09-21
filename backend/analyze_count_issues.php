<?php
/**
 * Analyze Count Issues Script
 * Identifies inconsistencies between different tables and APIs
 */

require_once 'config/database.php';

echo "=== SHOPIFY REVIEWS COUNT ANALYSIS ===\n\n";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Check which tables exist
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Available tables: " . implode(', ', $tables) . "\n\n";
    
    // Define the apps we're tracking
    $apps = ['StoreSEO', 'StoreFAQ', 'EasyFlow', 'BetterDocs FAQ', 'Vidify', 'TrustSync'];
    
    echo "=== TABLE COMPARISON ===\n";
    
    foreach ($apps as $app) {
        echo "\n--- $app ---\n";
        
        // Check each table if it exists
        foreach (['reviews', 'access_reviews', 'review_repository'] as $table) {
            if (in_array($table, $tables)) {
                // Total count
                $stmt = $conn->prepare("SELECT COUNT(*) FROM $table WHERE app_name = ?");
                $stmt->execute([$app]);
                $total = $stmt->fetchColumn();
                
                // This month count
                $stmt = $conn->prepare("SELECT COUNT(*) FROM $table WHERE app_name = ? AND review_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')");
                $stmt->execute([$app]);
                $thisMonth = $stmt->fetchColumn();
                
                // Last 30 days count
                $stmt = $conn->prepare("SELECT COUNT(*) FROM $table WHERE app_name = ? AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
                $stmt->execute([$app]);
                $last30Days = $stmt->fetchColumn();
                
                echo "  $table: Total=$total, This Month=$thisMonth, Last 30 Days=$last30Days\n";
            }
        }
    }
    
    echo "\n=== API ENDPOINT ANALYSIS ===\n";
    
    // Test API endpoints for StoreSEO as example
    $testApp = 'StoreSEO';
    echo "\nTesting API endpoints for $testApp:\n";
    
    // Test this-month-reviews.php
    $thisMonthUrl = "http://localhost:8000/api/this-month-reviews.php?app_name=" . urlencode($testApp);
    $thisMonthResponse = @file_get_contents($thisMonthUrl);
    if ($thisMonthResponse) {
        $thisMonthData = json_decode($thisMonthResponse, true);
        echo "  this-month-reviews.php: " . ($thisMonthData['count'] ?? 'ERROR') . "\n";
    } else {
        echo "  this-month-reviews.php: FAILED TO CONNECT\n";
    }
    
    // Test last-30-days-reviews.php
    $last30DaysUrl = "http://localhost:8000/api/last-30-days-reviews.php?app_name=" . urlencode($testApp);
    $last30DaysResponse = @file_get_contents($last30DaysUrl);
    if ($last30DaysResponse) {
        $last30DaysData = json_decode($last30DaysResponse, true);
        echo "  last-30-days-reviews.php: " . ($last30DaysData['count'] ?? 'ERROR') . "\n";
    } else {
        echo "  last-30-days-reviews.php: FAILED TO CONNECT\n";
    }
    
    echo "\n=== DATE CALCULATION VERIFICATION ===\n";
    
    $today = date('Y-m-d');
    $firstOfMonth = date('Y-m-01');
    $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
    
    echo "Today: $today\n";
    echo "First of current month: $firstOfMonth\n";
    echo "30 days ago: $thirtyDaysAgo\n";
    
    // Check if there are any reviews in the date ranges
    if (in_array('reviews', $tables)) {
        $stmt = $conn->prepare("SELECT MIN(review_date) as min_date, MAX(review_date) as max_date FROM reviews WHERE app_name = ?");
        $stmt->execute([$testApp]);
        $dateRange = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "\n$testApp date range in reviews table: {$dateRange['min_date']} to {$dateRange['max_date']}\n";
    }
    
    echo "\n=== RECOMMENDATIONS ===\n";
    echo "1. Standardize all APIs to use the same table (preferably 'reviews')\n";
    echo "2. Ensure consistent date calculation logic across all endpoints\n";
    echo "3. Add proper error handling and logging\n";
    echo "4. Implement cache-busting for real-time updates\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
