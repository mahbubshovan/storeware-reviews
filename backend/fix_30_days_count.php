<?php
require_once 'utils/DatabaseManager.php';

/**
 * Fix the 30 days count issue
 * Remove June 29 review since it's outside 30 days from July 30
 */
class Fix30DaysCount {
    private $dbManager;

    public function __construct() {
        $this->dbManager = new DatabaseManager();
    }

    public function fixCount($appName = 'StoreSEO') {
        echo "=== FIXING 30 DAYS COUNT FOR $appName ===\n";
        echo "Issue: June 29 review should not be in 'Last 30 Days' if today is July 30\n\n";

        // Check current situation
        echo "Current situation:\n";
        $julyCount = $this->dbManager->getThisMonthReviews($appName);
        $last30Count = $this->dbManager->getLast30DaysReviews($appName);
        echo "July count: $julyCount\n";
        echo "Last 30 days count: $last30Count\n\n";

        // Show date calculation
        echo "Date calculation:\n";
        echo "If today is July 30, 2025:\n";
        echo "- 30 days ago = June 30, 2025\n";
        echo "- June 29, 2025 = 31 days ago (OUTSIDE 30 days)\n";
        echo "- Therefore: Last 30 days should equal July count (24)\n\n";

        // Remove June reviews
        echo "Removing June reviews...\n";
        $query = "DELETE FROM reviews WHERE app_name = :app_name AND review_date < '2025-07-01'";
        $stmt = $this->dbManager->getConnection()->prepare($query);
        $stmt->bindParam(":app_name", $appName);
        $stmt->execute();
        $deletedCount = $stmt->rowCount();
        echo "Deleted $deletedCount June reviews\n\n";

        // Verify the fix
        echo "After fix:\n";
        $newJulyCount = $this->dbManager->getThisMonthReviews($appName);
        $newLast30Count = $this->dbManager->getLast30DaysReviews($appName);
        echo "July count: $newJulyCount\n";
        echo "Last 30 days count: $newLast30Count\n\n";

        if ($newJulyCount == $newLast30Count && $newJulyCount == 24) {
            echo "✅ SUCCESS: Both counts are now 24 (correct)\n";
        } else {
            echo "❌ ERROR: Counts don't match expected values\n";
        }

        // Show date range
        $query2 = "SELECT MIN(review_date) as min_date, MAX(review_date) as max_date 
                   FROM reviews WHERE app_name = :app_name";
        $stmt2 = $this->dbManager->getConnection()->prepare($query2);
        $stmt2->bindParam(":app_name", $appName);
        $stmt2->execute();
        $range = $stmt2->fetch(PDO::FETCH_ASSOC);
        echo "Date range in database: {$range['min_date']} to {$range['max_date']}\n";

        return [
            'july_count' => $newJulyCount,
            'last_30_days' => $newLast30Count,
            'deleted_june_reviews' => $deletedCount
        ];
    }
}

// Usage
if (php_sapi_name() === 'cli') {
    $appName = $argv[1] ?? 'StoreSEO';
    
    $fixer = new Fix30DaysCount();
    $result = $fixer->fixCount($appName);
    
    echo "\n=== FINAL RESULT ===\n";
    echo "July 2025: {$result['july_count']}\n";
    echo "Last 30 days: {$result['last_30_days']}\n";
    echo "Deleted June reviews: {$result['deleted_june_reviews']}\n";
    echo "\nBoth counts should now be 24.\n";
}
?>
