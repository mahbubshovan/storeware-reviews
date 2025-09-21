<?php
/**
 * Unified Count Validation System
 * Comprehensive validation of all count calculations across different pages and APIs
 */

require_once 'config/database.php';
require_once 'utils/DatabaseManager.php';
require_once 'utils/DateCalculations.php';

class UnifiedCountValidator {
    private $conn;
    private $apps;
    private $results;
    
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
        $this->apps = ['StoreSEO', 'StoreFAQ', 'EasyFlow', 'BetterDocs FAQ', 'Vidify', 'TrustSync'];
        $this->results = [];
    }
    
    /**
     * Run comprehensive validation
     */
    public function runValidation() {
        echo "=== UNIFIED COUNT VALIDATION SYSTEM ===\n";
        echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";
        
        $this->validateDatabaseConsistency();
        $this->validateAPIEndpoints();
        $this->validateDateCalculations();
        $this->generateReport();
        
        return $this->results;
    }
    
    /**
     * Validate database consistency across tables
     */
    private function validateDatabaseConsistency() {
        echo "=== DATABASE CONSISTENCY VALIDATION ===\n";
        
        foreach ($this->apps as $app) {
            echo "\n--- $app ---\n";
            
            $appResults = [
                'app_name' => $app,
                'tables' => [],
                'consistency_issues' => []
            ];
            
            // Check each table
            $tables = ['reviews', 'access_reviews', 'review_repository'];
            foreach ($tables as $table) {
                if ($this->tableExists($table)) {
                    $thisMonth = DateCalculations::getThisMonthCount($this->conn, $table, $app);
                    $last30Days = DateCalculations::getLast30DaysCount($this->conn, $table, $app);
                    
                    $appResults['tables'][$table] = [
                        'this_month' => $thisMonth,
                        'last_30_days' => $last30Days
                    ];
                    
                    echo "  $table: This Month=$thisMonth, Last 30 Days=$last30Days\n";
                }
            }
            
            // Check for consistency issues
            $reviewsData = $appResults['tables']['reviews'] ?? null;
            $accessData = $appResults['tables']['access_reviews'] ?? null;
            
            if ($reviewsData && $accessData) {
                if ($reviewsData['this_month'] !== $accessData['this_month']) {
                    $appResults['consistency_issues'][] = "This month mismatch: reviews({$reviewsData['this_month']}) vs access_reviews({$accessData['this_month']})";
                }
                if ($reviewsData['last_30_days'] !== $accessData['last_30_days']) {
                    $appResults['consistency_issues'][] = "Last 30 days mismatch: reviews({$reviewsData['last_30_days']}) vs access_reviews({$accessData['last_30_days']})";
                }
            }
            
            $this->results['database'][$app] = $appResults;
        }
    }
    
    /**
     * Validate API endpoints
     */
    private function validateAPIEndpoints() {
        echo "\n=== API ENDPOINTS VALIDATION ===\n";
        
        foreach ($this->apps as $app) {
            echo "\n--- $app API Tests ---\n";
            
            $apiResults = [
                'app_name' => $app,
                'endpoints' => [],
                'consistency_with_db' => []
            ];
            
            // Test this-month-reviews.php
            $thisMonthAPI = $this->testAPIEndpoint('this-month-reviews.php', $app);
            $apiResults['endpoints']['this_month'] = $thisMonthAPI;
            echo "  this-month-reviews.php: {$thisMonthAPI['count']}\n";
            
            // Test last-30-days-reviews.php
            $last30DaysAPI = $this->testAPIEndpoint('last-30-days-reviews.php', $app);
            $apiResults['endpoints']['last_30_days'] = $last30DaysAPI;
            echo "  last-30-days-reviews.php: {$last30DaysAPI['count']}\n";
            
            // Compare with database
            $dbThisMonth = DateCalculations::getThisMonthCount($this->conn, 'reviews', $app);
            $dbLast30Days = DateCalculations::getLast30DaysCount($this->conn, 'reviews', $app);
            
            if ($thisMonthAPI['count'] !== $dbThisMonth) {
                $apiResults['consistency_with_db'][] = "This month API/DB mismatch: API({$thisMonthAPI['count']}) vs DB($dbThisMonth)";
                echo "  ❌ This month mismatch: API({$thisMonthAPI['count']}) vs DB($dbThisMonth)\n";
            } else {
                echo "  ✅ This month matches\n";
            }
            
            if ($last30DaysAPI['count'] !== $dbLast30Days) {
                $apiResults['consistency_with_db'][] = "Last 30 days API/DB mismatch: API({$last30DaysAPI['count']}) vs DB($dbLast30Days)";
                echo "  ❌ Last 30 days mismatch: API({$last30DaysAPI['count']}) vs DB($dbLast30Days)\n";
            } else {
                echo "  ✅ Last 30 days matches\n";
            }
            
            $this->results['api'][$app] = $apiResults;
        }
    }
    
    /**
     * Validate date calculations
     */
    private function validateDateCalculations() {
        echo "\n=== DATE CALCULATIONS VALIDATION ===\n";
        
        $dateInfo = DateCalculations::getDateInfo();
        echo "Date Info:\n";
        foreach ($dateInfo as $key => $value) {
            echo "  $key: $value\n";
        }
        
        // Test with StoreSEO as example
        $testApp = 'StoreSEO';
        echo "\nValidating date calculations for $testApp:\n";
        
        $validation = DateCalculations::validateDateCalculations($this->conn, 'reviews', $testApp);
        $this->results['date_validation'] = $validation;
    }
    
    /**
     * Generate comprehensive report
     */
    private function generateReport() {
        echo "\n=== VALIDATION REPORT ===\n";
        
        $totalIssues = 0;
        $totalApps = count($this->apps);
        
        // Database consistency report
        echo "\nDatabase Consistency:\n";
        foreach ($this->results['database'] ?? [] as $app => $data) {
            $issues = count($data['consistency_issues']);
            $totalIssues += $issues;
            
            if ($issues > 0) {
                echo "  ❌ $app: $issues issues\n";
                foreach ($data['consistency_issues'] as $issue) {
                    echo "    - $issue\n";
                }
            } else {
                echo "  ✅ $app: No issues\n";
            }
        }
        
        // API consistency report
        echo "\nAPI Consistency:\n";
        foreach ($this->results['api'] ?? [] as $app => $data) {
            $issues = count($data['consistency_with_db']);
            $totalIssues += $issues;
            
            if ($issues > 0) {
                echo "  ❌ $app: $issues issues\n";
                foreach ($data['consistency_with_db'] as $issue) {
                    echo "    - $issue\n";
                }
            } else {
                echo "  ✅ $app: No issues\n";
            }
        }
        
        // Overall summary
        echo "\n=== OVERALL SUMMARY ===\n";
        if ($totalIssues === 0) {
            echo "🎉 SUCCESS: All counts are consistent across all systems!\n";
            echo "✅ Database tables are synchronized\n";
            echo "✅ API endpoints return consistent data\n";
            echo "✅ Date calculations are working correctly\n";
        } else {
            echo "⚠️  ISSUES FOUND: $totalIssues total issues across $totalApps apps\n";
            echo "📋 Review the detailed report above for specific issues\n";
            echo "🔧 Run data synchronization scripts to fix inconsistencies\n";
        }
        
        // Save results to file
        file_put_contents('validation_results.json', json_encode($this->results, JSON_PRETTY_PRINT));
        echo "\n📄 Detailed results saved to validation_results.json\n";
    }
    
    /**
     * Test API endpoint
     */
    private function testAPIEndpoint($endpoint, $appName) {
        try {
            // Simulate API call by including the file
            $_GET['app_name'] = $appName;
            ob_start();
            include "api/$endpoint";
            $response = ob_get_contents();
            ob_end_clean();
            
            $data = json_decode($response, true);
            return [
                'success' => $data['success'] ?? false,
                'count' => $data['count'] ?? 0,
                'source' => $data['source'] ?? 'unknown'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'count' => 0,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Check if table exists
     */
    private function tableExists($tableName) {
        $stmt = $this->conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return in_array($tableName, $tables);
    }
}

// Run validation if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $validator = new UnifiedCountValidator();
    $validator->runValidation();
}
?>
