<?php
/**
 * Cleanup script for IP rate limiting system
 * Removes old rate limit records and activity logs
 */

require_once __DIR__ . '/IPRateLimitManager.php';

echo "🧹 RATE LIMITING CLEANUP SCRIPT\n";
echo "==============================\n\n";

try {
    $rateLimitManager = new IPRateLimitManager();
    
    echo "🔄 Starting cleanup process...\n";
    
    // Run cleanup
    $rateLimitManager->cleanup();
    
    echo "✅ Cleanup completed successfully!\n";
    echo "- Removed rate limit records older than 7 days\n";
    echo "- Removed activity logs older than 30 days\n\n";
    
    // Show current statistics
    require_once __DIR__ . '/../config/database.php';
    $db = new Database();
    $conn = $db->getConnection();
    
    // Count remaining records
    $limitStmt = $conn->prepare("SELECT COUNT(*) as count FROM ip_scrape_limits");
    $limitStmt->execute();
    $limitCount = $limitStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $logStmt = $conn->prepare("SELECT COUNT(*) as count FROM scrape_activity_log");
    $logStmt->execute();
    $logCount = $logStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $activeLimitStmt = $conn->prepare("SELECT COUNT(*) as count FROM ip_scrape_limits WHERE cooldown_expiry > NOW()");
    $activeLimitStmt->execute();
    $activeLimitCount = $activeLimitStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "📊 CURRENT STATISTICS:\n";
    echo "- Total rate limit records: $limitCount\n";
    echo "- Active rate limits: $activeLimitCount\n";
    echo "- Total activity log entries: $logCount\n";
    
    echo "\n🎉 Cleanup completed!\n";
    
} catch (Exception $e) {
    echo "❌ Cleanup failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
