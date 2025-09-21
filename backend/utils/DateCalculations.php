<?php
/**
 * Standardized Date Calculation Utilities
 * Ensures consistent date calculations across all APIs and components
 */

class DateCalculations {
    
    /**
     * Get the SQL condition for "this month" reviews
     * From the 1st of current month to today (inclusive)
     */
    public static function getThisMonthCondition() {
        return "review_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND review_date <= CURDATE()";
    }
    
    /**
     * Get the SQL condition for "last 30 days" reviews
     * From 30 days ago to today (inclusive)
     */
    public static function getLast30DaysCondition() {
        return "review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND review_date <= CURDATE()";
    }
    
    /**
     * Get the SQL condition for "last month" reviews
     * Reviews older than 30 days from today
     */
    public static function getLastMonthCondition() {
        return "review_date < DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    }
    
    /**
     * Get standardized date values for debugging
     */
    public static function getDateInfo() {
        return [
            'today' => date('Y-m-d'),
            'first_of_month' => date('Y-m-01'),
            'thirty_days_ago' => date('Y-m-d', strtotime('-30 days')),
            'mysql_today' => 'CURDATE()',
            'mysql_first_of_month' => "DATE_FORMAT(CURDATE(), '%Y-%m-01')",
            'mysql_thirty_days_ago' => 'DATE_SUB(CURDATE(), INTERVAL 30 DAY)'
        ];
    }
    
    /**
     * Get count for this month from any table
     */
    public static function getThisMonthCount($conn, $tableName, $appName = null) {
        $query = "SELECT COUNT(*) as count FROM $tableName WHERE " . self::getThisMonthCondition();
        $params = [];
        
        if ($appName) {
            $query .= " AND app_name = ?";
            $params[] = $appName;
        }
        
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        return intval($stmt->fetch(PDO::FETCH_ASSOC)['count']);
    }
    
    /**
     * Get count for last 30 days from any table
     */
    public static function getLast30DaysCount($conn, $tableName, $appName = null) {
        $query = "SELECT COUNT(*) as count FROM $tableName WHERE " . self::getLast30DaysCondition();
        $params = [];
        
        if ($appName) {
            $query .= " AND app_name = ?";
            $params[] = $appName;
        }
        
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        return intval($stmt->fetch(PDO::FETCH_ASSOC)['count']);
    }
    
    /**
     * Get comprehensive stats for an app from any table
     */
    public static function getAppStats($conn, $tableName, $appName) {
        $query = "
            SELECT 
                COUNT(*) as total_reviews,
                COUNT(CASE WHEN " . self::getThisMonthCondition() . " THEN 1 END) as this_month,
                COUNT(CASE WHEN " . self::getLast30DaysCondition() . " THEN 1 END) as last_30_days,
                AVG(rating) as average_rating,
                MIN(review_date) as earliest_review,
                MAX(review_date) as latest_review
            FROM $tableName 
            WHERE app_name = ?
        ";
        
        // Add is_active condition if the table has it
        $columns = $conn->query("DESCRIBE $tableName")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('is_active', $columns)) {
            $query .= " AND is_active = TRUE";
        }
        
        $stmt = $conn->prepare($query);
        $stmt->execute([$appName]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'total_reviews' => intval($result['total_reviews']),
            'this_month' => intval($result['this_month']),
            'last_30_days' => intval($result['last_30_days']),
            'average_rating' => round(floatval($result['average_rating']), 1),
            'earliest_review' => $result['earliest_review'],
            'latest_review' => $result['latest_review']
        ];
    }
    
    /**
     * Validate date calculations by comparing different methods
     */
    public static function validateDateCalculations($conn, $tableName, $appName) {
        echo "=== DATE CALCULATION VALIDATION for $appName in $tableName ===\n";
        
        $dateInfo = self::getDateInfo();
        foreach ($dateInfo as $key => $value) {
            echo "$key: $value\n";
        }
        echo "\n";
        
        // Method 1: Using our standardized functions
        $thisMonth1 = self::getThisMonthCount($conn, $tableName, $appName);
        $last30Days1 = self::getLast30DaysCount($conn, $tableName, $appName);
        
        // Method 2: Direct SQL (old way)
        $stmt = $conn->prepare("SELECT COUNT(*) FROM $tableName WHERE app_name = ? AND review_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')");
        $stmt->execute([$appName]);
        $thisMonth2 = intval($stmt->fetchColumn());
        
        $stmt = $conn->prepare("SELECT COUNT(*) FROM $tableName WHERE app_name = ? AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
        $stmt->execute([$appName]);
        $last30Days2 = intval($stmt->fetchColumn());
        
        echo "This Month - Standardized: $thisMonth1, Direct SQL: $thisMonth2\n";
        echo "Last 30 Days - Standardized: $last30Days1, Direct SQL: $last30Days2\n";
        
        $thisMonthMatch = ($thisMonth1 === $thisMonth2) ? "✅ MATCH" : "❌ MISMATCH";
        $last30DaysMatch = ($last30Days1 === $last30Days2) ? "✅ MATCH" : "❌ MISMATCH";
        
        echo "This Month: $thisMonthMatch\n";
        echo "Last 30 Days: $last30DaysMatch\n\n";
        
        return [
            'this_month_match' => $thisMonth1 === $thisMonth2,
            'last_30_days_match' => $last30Days1 === $last30Days2,
            'this_month' => $thisMonth1,
            'last_30_days' => $last30Days1
        ];
    }
}
?>
