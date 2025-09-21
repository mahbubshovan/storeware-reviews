<?php
/**
 * Background Scraper Cron Job
 * Runs every 5 minutes to check for clients that need automatic scraping
 * 
 * Usage: php background_scraper.php
 * Cron: */5 * * * * /usr/bin/php /path/to/backend/cron/background_scraper.php
 */

// Prevent web access
if (isset($_SERVER['HTTP_HOST'])) {
    die('This script can only be run from command line.');
}

// Set execution time and memory limits
set_time_limit(600); // 10 minutes
ini_set('memory_limit', '512M');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../scraper/EnhancedLiveScraper.php';

class BackgroundScraper {
    private $pdo;
    private $scraper;
    private $logFile;
    
    public function __construct() {
        $this->pdo = getDbConnection();
        $this->scraper = new EnhancedLiveScraper();
        $this->logFile = __DIR__ . '/../../logs/background_scraper.log';
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Main execution method
     */
    public function run() {
        $this->log("🚀 Background scraper started");
        
        try {
            // Find clients that are due for scraping
            $dueClients = $this->findDueClients();
            
            if (empty($dueClients)) {
                $this->log("✅ No clients due for scraping");
                return;
            }
            
            $this->log("📋 Found " . count($dueClients) . " clients due for scraping");
            
            foreach ($dueClients as $client) {
                $this->processDueClient($client);
                
                // Rate limiting between clients
                sleep(2);
            }
            
            // Cleanup old snapshots
            $this->cleanupOldSnapshots();
            
            $this->log("✅ Background scraper completed successfully");
            
        } catch (Exception $e) {
            $this->log("❌ Background scraper failed: " . $e->getMessage());
            error_log("Background scraper error: " . $e->getMessage());
        }
    }
    
    /**
     * Find clients that are due for automatic scraping
     */
    private function findDueClients() {
        $stmt = $this->pdo->prepare("
            SELECT 
                ss.app_slug,
                ss.client_id,
                ss.next_run_at,
                ss.last_run_at,
                c.last_seen,
                TIMESTAMPDIFF(SECOND, NOW(), ss.next_run_at) as seconds_until_due
            FROM scrape_schedule ss
            JOIN clients c ON ss.client_id = c.client_id
            WHERE ss.next_run_at <= NOW()
            AND c.last_seen >= DATE_SUB(NOW(), INTERVAL 7 DAY)  -- Only active clients
            ORDER BY ss.next_run_at ASC
            LIMIT 10  -- Process max 10 clients per run
        ");
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Process a single due client
     */
    private function processDueClient($client) {
        $appSlug = $client['app_slug'];
        $clientId = $client['client_id'];
        
        $this->log("🔄 Processing {$appSlug} for client {$clientId}");
        
        try {
            // Map app slugs to display names - STANDARDIZED MAPPING
            $appNames = [
                'storeseo' => 'StoreSEO',
                'storefaq' => 'StoreFAQ',
                'vidify' => 'Vidify',
                'customer-review-app' => 'TrustSync',
                'product-options-4' => 'EasyFlow',
                'betterdocs-knowledgebase' => 'BetterDocs FAQ Knowledge Base'
            ];
            
            $appName = $appNames[$appSlug] ?? ucfirst($appSlug);
            
            // Update schedule to prevent concurrent runs
            $stmt = $this->pdo->prepare("
                UPDATE scrape_schedule 
                SET last_run_at = NOW(),
                    next_run_at = DATE_ADD(NOW(), INTERVAL 6 HOUR),
                    updated_at = NOW()
                WHERE app_slug = ? AND client_id = ?
            ");
            $stmt->execute([$appSlug, $clientId]);
            
            // Perform scraping
            $result = $this->scraper->scrapeAppWithSnapshot($appSlug, $appName);
            
            if (!$result['success']) {
                throw new Exception("Scraping failed: " . ($result['error'] ?? 'Unknown error'));
            }
            
            // Create snapshot
            $stmt = $this->pdo->prepare("
                INSERT INTO snapshots (
                    app_slug, source_url, etag, last_modified, content_hash,
                    totals, last30Days, thisMonth, ratingDistribution, latestReviews
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $appSlug,
                $result['source_url'],
                $result['etag'],
                $result['last_modified'],
                $result['content_hash'],
                json_encode($result['totals']),
                json_encode($result['last30Days']),
                json_encode($result['thisMonth']),
                json_encode($result['ratingDistribution']),
                json_encode($result['latestReviews'])
            ]);
            
            $snapshotId = $this->pdo->lastInsertId();
            
            // Update snapshot pointer
            $stmt = $this->pdo->prepare("
                INSERT INTO snapshot_pointer (app_slug, client_id, snapshot_id)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    snapshot_id = VALUES(snapshot_id),
                    updated_at = NOW()
            ");
            $stmt->execute([$appSlug, $clientId, $snapshotId]);
            
            // Update upstream state
            $stmt = $this->pdo->prepare("
                INSERT INTO upstream_state (app_slug, last_content_hash)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE 
                    last_content_hash = VALUES(last_content_hash),
                    last_seen_at = NOW()
            ");
            $stmt->execute([$appSlug, $result['content_hash']]);
            
            $this->log("✅ Successfully scraped {$appName}: {$result['scraped_count']} reviews");
            
        } catch (Exception $e) {
            // Rollback schedule update on failure
            $stmt = $this->pdo->prepare("
                UPDATE scrape_schedule 
                SET next_run_at = last_run_at, 
                    last_run_at = NULL
                WHERE app_slug = ? AND client_id = ?
            ");
            $stmt->execute([$appSlug, $clientId]);
            
            $this->log("❌ Failed to scrape {$appSlug} for {$clientId}: " . $e->getMessage());
        }
    }
    
    /**
     * Cleanup old snapshots to prevent database bloat
     */
    private function cleanupOldSnapshots() {
        $this->log("🧹 Starting snapshot cleanup");
        
        try {
            // Keep only the latest 20 snapshots per app
            $stmt = $this->pdo->prepare("
                DELETE s1 FROM snapshots s1
                INNER JOIN (
                    SELECT id
                    FROM snapshots s2
                    WHERE s2.app_slug = s1.app_slug
                    ORDER BY scraped_at DESC
                    LIMIT 20, 18446744073709551615
                ) s2 ON s1.id = s2.id
            ");
            $stmt->execute();
            $deletedSnapshots = $stmt->rowCount();
            
            // Clean up orphaned snapshot pointers
            $stmt = $this->pdo->prepare("
                DELETE sp FROM snapshot_pointer sp
                LEFT JOIN snapshots s ON sp.snapshot_id = s.id
                WHERE s.id IS NULL
            ");
            $stmt->execute();
            $deletedPointers = $stmt->rowCount();
            
            // Clean up inactive clients (not seen in 30 days)
            $stmt = $this->pdo->prepare("
                DELETE FROM clients 
                WHERE last_seen < DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stmt->execute();
            $deletedClients = $stmt->rowCount();
            
            $this->log("🧹 Cleanup completed: {$deletedSnapshots} snapshots, {$deletedPointers} pointers, {$deletedClients} clients");
            
        } catch (Exception $e) {
            $this->log("❌ Cleanup failed: " . $e->getMessage());
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

// Run the background scraper
if (php_sapi_name() === 'cli') {
    $scraper = new BackgroundScraper();
    $scraper->run();
} else {
    die('This script can only be run from command line.');
}
