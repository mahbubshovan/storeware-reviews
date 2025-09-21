<?php
/**
 * Test Date Calculations
 */

require_once 'config/database.php';
require_once 'utils/DateCalculations.php';

echo "=== TESTING STANDARDIZED DATE CALCULATIONS ===\n\n";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Test with StoreSEO across all tables
    $testApp = 'StoreSEO';
    $tables = ['reviews', 'access_reviews', 'review_repository'];
    
    foreach ($tables as $table) {
        // Check if table exists
        $tableExists = $conn->query("SHOW TABLES LIKE '$table'")->rowCount() > 0;
        if (!$tableExists) {
            echo "Table $table does not exist, skipping...\n\n";
            continue;
        }
        
        echo "=== TESTING $table TABLE ===\n";
        
        // Validate calculations
        $validation = DateCalculations::validateDateCalculations($conn, $table, $testApp);
        
        // Get comprehensive stats
        $stats = DateCalculations::getAppStats($conn, $table, $testApp);
        echo "Comprehensive Stats:\n";
        foreach ($stats as $key => $value) {
            echo "  $key: $value\n";
        }
        echo "\n";
    }
    
    echo "=== DATE INFO ===\n";
    $dateInfo = DateCalculations::getDateInfo();
    foreach ($dateInfo as $key => $value) {
        echo "$key: $value\n";
    }
    
    echo "\n=== RECOMMENDATIONS ===\n";
    echo "1. Use DateCalculations::getThisMonthCount() for all 'this month' calculations\n";
    echo "2. Use DateCalculations::getLast30DaysCount() for all 'last 30 days' calculations\n";
    echo "3. Standardize on 'reviews' table as the primary data source\n";
    echo "4. Update all API endpoints to use these standardized functions\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
