<?php
/**
 * First Page Review Monitor Script
 * Runs the first page monitoring system to detect new reviews
 */

require_once __DIR__ . '/utils/FirstPageMonitor.php';

echo "🚀 Starting First Page Review Monitor\n";
echo "====================================\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

try {
    $monitor = new FirstPageMonitor();
    
    // Run monitoring for all apps
    $totalNewReviews = $monitor->monitorAllApps();
    
    // Get updated statistics
    echo "\n📈 Updated Statistics:\n";
    echo "=====================\n";
    $stats = $monitor->getMonitoringStats();
    
    foreach ($stats as $appName => $appStats) {
        echo sprintf("%-15s: %3d total | %3d last 30 days | %3d this month\n", 
            $appName, 
            $appStats['total_reviews'], 
            $appStats['last_30_days'], 
            $appStats['this_month']
        );
    }
    
    echo "\n✅ First Page Monitoring Complete!\n";
    echo "📊 Total new reviews added: {$totalNewReviews}\n";
    echo "⏰ Completed at: " . date('Y-m-d H:i:s') . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    exit(1);
}
?>
