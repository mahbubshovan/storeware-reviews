<?php
/**
 * GET /api/scrape/:app/status - Get scraping job status for polling
 * Returns current scraping status and metadata
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed'
    ]);
    exit;
}

try {
    // Get app slug from URL path
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $pathParts = explode('/', trim($path, '/'));
    
    // Expected: api/scrape/{app_slug}/status
    if (count($pathParts) < 4 || $pathParts[1] !== 'scrape' || $pathParts[3] !== 'status') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid URL format. Expected: /api/scrape/{app_slug}/status'
        ]);
        exit;
    }
    
    $appSlug = $pathParts[2];
    $clientId = $_GET['client_id'] ?? null;
    
    if (!$clientId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'client_id parameter is required'
        ]);
        exit;
    }
    
    // Validate UUID format
    if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $clientId)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid client_id format'
        ]);
        exit;
    }
    
    // Valid app slugs
    $validApps = [
        'storeseo', 'storefaq', 'vidify', 
        'customer-review-app', 'product-options-4', 'betterdocs-knowledgebase'
    ];
    
    if (!in_array($appSlug, $validApps)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid app slug'
        ]);
        exit;
    }
    
    $pdo = getDbConnection();
    
    // Get scraping schedule and recent activity
    $stmt = $pdo->prepare("
        SELECT 
            ss.next_run_at,
            ss.last_run_at,
            ss.updated_at,
            GREATEST(0, TIMESTAMPDIFF(SECOND, NOW(), ss.next_run_at)) as remaining_seconds,
            sp.snapshot_id,
            sp.updated_at as snapshot_updated_at,
            s.scraped_at,
            s.content_hash
        FROM scrape_schedule ss
        LEFT JOIN snapshot_pointer sp ON ss.app_slug = sp.app_slug AND ss.client_id = sp.client_id
        LEFT JOIN snapshots s ON sp.snapshot_id = s.id
        WHERE ss.app_slug = ? AND ss.client_id = ?
    ");
    $stmt->execute([$appSlug, $clientId]);
    $status = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get upstream state for change detection
    $stmt = $pdo->prepare("
        SELECT 
            last_content_hash,
            last_seen_at
        FROM upstream_state
        WHERE app_slug = ?
    ");
    $stmt->execute([$appSlug]);
    $upstream = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Determine job status
    $jobStatus = 'idle';
    $isRecentlyUpdated = false;
    
    if ($status) {
        $lastRunTime = strtotime($status['last_run_at']);
        $updateTime = strtotime($status['updated_at']);
        $now = time();
        
        // If last run was very recent (within 5 minutes), might still be running
        if ($lastRunTime && ($now - $lastRunTime) < 300) {
            // Check if snapshot was created after the run started
            if ($status['snapshot_updated_at']) {
                $snapshotTime = strtotime($status['snapshot_updated_at']);
                if ($snapshotTime >= $lastRunTime) {
                    $jobStatus = 'completed';
                    $isRecentlyUpdated = true;
                } else {
                    $jobStatus = 'running';
                }
            } else {
                $jobStatus = 'running';
            }
        } else if ($status['snapshot_id']) {
            $jobStatus = 'completed';
        }
    }
    
    // Check for upstream changes
    $hasUpstreamChanges = false;
    if ($status && $upstream && $status['content_hash']) {
        $hasUpstreamChanges = $status['content_hash'] !== $upstream['last_content_hash'];
    }
    
    // Build response
    $response = [
        'app' => $appSlug,
        'client_id' => $clientId,
        'status' => $jobStatus,
        'schedule' => null,
        'last_snapshot' => null,
        'upstream_changes' => $hasUpstreamChanges
    ];
    
    if ($status) {
        $response['schedule'] = [
            'next_run_at' => $status['next_run_at'],
            'last_run_at' => $status['last_run_at'],
            'remaining_seconds' => (int)$status['remaining_seconds'],
            'allowed_now' => (int)$status['remaining_seconds'] <= 0
        ];
        
        if ($status['snapshot_id']) {
            $response['last_snapshot'] = [
                'id' => (int)$status['snapshot_id'],
                'scraped_at' => $status['scraped_at'],
                'updated_at' => $status['snapshot_updated_at'],
                'content_hash' => $status['content_hash'],
                'is_recent' => $isRecentlyUpdated
            ];
        }
    } else {
        // First time visitor
        $response['schedule'] = [
            'next_run_at' => null,
            'last_run_at' => null,
            'remaining_seconds' => 0,
            'allowed_now' => true
        ];
    }
    
    // Add performance metrics if available
    if ($status && $status['last_run_at']) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_snapshots,
                   MAX(scraped_at) as latest_scrape
            FROM snapshots 
            WHERE app_slug = ?
        ");
        $stmt->execute([$appSlug]);
        $metrics = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($metrics) {
            $response['metrics'] = [
                'total_snapshots' => (int)$metrics['total_snapshots'],
                'latest_scrape' => $metrics['latest_scrape']
            ];
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    error_log("Error in scrape-status.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error'
    ]);
}
