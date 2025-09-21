<?php
/**
 * Health Check Script for Per-Device Rate Limiting System
 * Monitors system health and sends alerts if issues are detected
 * 
 * Usage: php health_check.php
 * Cron: 0 * * * * /usr/bin/php /path/to/backend/cron/health_check.php
 */

// Prevent web access
if (isset($_SERVER['HTTP_HOST'])) {
    die('This script can only be run from command line.');
}

require_once __DIR__ . '/../config/database.php';

class HealthChecker {
    private $pdo;
    private $logFile;
    private $issues = [];
    
    public function __construct() {
        $this->pdo = getDbConnection();
        $this->logFile = __DIR__ . '/../../logs/health.log';
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Run all health checks
     */
    public function runChecks() {
        $this->log("🏥 Starting health checks");
        
        $this->checkDatabaseConnection();
        $this->checkTableIntegrity();
        $this->checkDiskSpace();
        $this->checkStaleSchedules();
        $this->checkOrphanedRecords();
        $this->checkRecentActivity();
        
        if (empty($this->issues)) {
            $this->log("✅ All health checks passed");
        } else {
            $this->log("⚠️ Health check issues found:");
            foreach ($this->issues as $issue) {
                $this->log("  • " . $issue);
            }
        }
        
        return empty($this->issues);
    }
    
    /**
     * Check database connection
     */
    private function checkDatabaseConnection() {
        try {
            $stmt = $this->pdo->query("SELECT 1");
            if (!$stmt) {
                $this->issues[] = "Database connection failed";
            }
        } catch (Exception $e) {
            $this->issues[] = "Database error: " . $e->getMessage();
        }
    }
    
    /**
     * Check table integrity
     */
    private function checkTableIntegrity() {
        $requiredTables = [
            'clients', 'scrape_schedule', 'snapshots', 
            'snapshot_pointer', 'upstream_state'
        ];
        
        foreach ($requiredTables as $table) {
            try {
                $stmt = $this->pdo->query("SELECT COUNT(*) FROM $table");
                if (!$stmt) {
                    $this->issues[] = "Table $table is not accessible";
                }
            } catch (Exception $e) {
                $this->issues[] = "Table $table error: " . $e->getMessage();
            }
        }
    }
    
    /**
     * Check disk space
     */
    private function checkDiskSpace() {
        $logDir = dirname($this->logFile);
        $freeBytes = disk_free_space($logDir);
        $totalBytes = disk_total_space($logDir);
        
        if ($freeBytes && $totalBytes) {
            $freePercent = ($freeBytes / $totalBytes) * 100;
            
            if ($freePercent < 10) {
                $this->issues[] = "Low disk space: " . round($freePercent, 1) . "% free";
            }
        }
    }
    
    /**
     * Check for stale schedules
     */
    private function checkStaleSchedules() {
        try {
            // Find schedules that haven't been updated in over 24 hours
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as stale_count
                FROM scrape_schedule 
                WHERE updated_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
                AND last_run_at IS NOT NULL
            ");
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result['stale_count'] > 0) {
                $this->issues[] = "Found {$result['stale_count']} stale schedules";
            }
        } catch (Exception $e) {
            $this->issues[] = "Error checking stale schedules: " . $e->getMessage();
        }
    }
    
    /**
     * Check for orphaned records
     */
    private function checkOrphanedRecords() {
        try {
            // Check for orphaned snapshot pointers
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as orphaned_count
                FROM snapshot_pointer sp
                LEFT JOIN snapshots s ON sp.snapshot_id = s.id
                WHERE s.id IS NULL
            ");
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result['orphaned_count'] > 0) {
                $this->issues[] = "Found {$result['orphaned_count']} orphaned snapshot pointers";
            }
            
            // Check for orphaned schedules
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as orphaned_count
                FROM scrape_schedule ss
                LEFT JOIN clients c ON ss.client_id = c.client_id
                WHERE c.client_id IS NULL
            ");
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result['orphaned_count'] > 0) {
                $this->issues[] = "Found {$result['orphaned_count']} orphaned schedules";
            }
        } catch (Exception $e) {
            $this->issues[] = "Error checking orphaned records: " . $e->getMessage();
        }
    }
    
    /**
     * Check recent activity
     */
    private function checkRecentActivity() {
        try {
            // Check if there's been any recent scraping activity
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as recent_snapshots
                FROM snapshots 
                WHERE scraped_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ");
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if there are active clients
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as active_clients
                FROM clients 
                WHERE last_seen >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ");
            
            $activeClients = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Only flag as issue if there are active clients but no recent snapshots
            if ($activeClients['active_clients'] > 0 && $result['recent_snapshots'] == 0) {
                $this->issues[] = "No recent scraping activity despite {$activeClients['active_clients']} active clients";
            }
        } catch (Exception $e) {
            $this->issues[] = "Error checking recent activity: " . $e->getMessage();
        }
    }
    
    /**
     * Get system statistics
     */
    public function getStats() {
        try {
            $stats = [];
            
            // Client statistics
            $stmt = $this->pdo->query("
                SELECT 
                    COUNT(*) as total_clients,
                    COUNT(CASE WHEN last_seen >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as active_24h,
                    COUNT(CASE WHEN last_seen >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 1 END) as active_1h
                FROM clients
            ");
            $stats['clients'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Snapshot statistics
            $stmt = $this->pdo->query("
                SELECT 
                    COUNT(*) as total_snapshots,
                    COUNT(CASE WHEN scraped_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as recent_24h,
                    COUNT(CASE WHEN scraped_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 1 END) as recent_1h
                FROM snapshots
            ");
            $stats['snapshots'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Schedule statistics
            $stmt = $this->pdo->query("
                SELECT 
                    COUNT(*) as total_schedules,
                    COUNT(CASE WHEN next_run_at <= NOW() THEN 1 END) as due_now,
                    COUNT(CASE WHEN last_run_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as ran_24h
                FROM scrape_schedule
            ");
            $stats['schedules'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $stats;
        } catch (Exception $e) {
            $this->log("Error getting stats: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Log message with timestamp
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
        
        // Write to log file
        file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
        
        // Also output to console if running from CLI
        if (php_sapi_name() === 'cli') {
            echo $logMessage;
        }
    }
}

// Run health checks
if (php_sapi_name() === 'cli') {
    $checker = new HealthChecker();
    $healthy = $checker->runChecks();
    
    // Output statistics
    $stats = $checker->getStats();
    if ($stats) {
        echo "\n📊 System Statistics:\n";
        echo "Clients: {$stats['clients']['total_clients']} total, {$stats['clients']['active_24h']} active (24h), {$stats['clients']['active_1h']} active (1h)\n";
        echo "Snapshots: {$stats['snapshots']['total_snapshots']} total, {$stats['snapshots']['recent_24h']} recent (24h), {$stats['snapshots']['recent_1h']} recent (1h)\n";
        echo "Schedules: {$stats['schedules']['total_schedules']} total, {$stats['schedules']['due_now']} due now, {$stats['schedules']['ran_24h']} ran (24h)\n";
    }
    
    exit($healthy ? 0 : 1);
} else {
    die('This script can only be run from command line.');
}
