<?php
/**
 * POST /api/scrape/:app/trigger - Trigger per-device rate limited scraping
 * Enforces 6-hour rate limiting per client device
 */

// Increase execution time for scraping
set_time_limit(300); // 5 minutes
ini_set('max_execution_time', 300);

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../scraper/EnhancedLiveScraper.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
    
    // Expected: api/scrape/{app_slug}/trigger
    if (count($pathParts) < 4 || $pathParts[1] !== 'scrape' || $pathParts[3] !== 'trigger') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid URL format. Expected: /api/scrape/{app_slug}/trigger'
        ]);
        exit;
    }
    
    $appSlug = $pathParts[2];
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $clientId = $input['client_id'] ?? null;
    
    if (!$clientId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'client_id is required'
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
    
    // Valid app slugs with their display names
    $appConfig = [
        'storeseo' => 'StoreSEO',
        'storefaq' => 'StoreFAQ',
        'vidify' => 'Vidify',
        'customer-review-app' => 'TrustSync',
        'product-options-4' => 'EasyFlow',
        'betterdocs-knowledgebase' => 'BetterDocs FAQ'
    ];
    
    if (!isset($appConfig[$appSlug])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid app slug'
        ]);
        exit;
    }
    
    $appName = $appConfig[$appSlug];
    $pdo = getDbConnection();
    
    // Upsert client record
    $stmt = $pdo->prepare("
        INSERT INTO clients (client_id, first_seen, last_seen) 
        VALUES (?, NOW(), NOW())
        ON DUPLICATE KEY UPDATE last_seen = NOW()
    ");
    $stmt->execute([$clientId]);
    
    // Check current rate limiting status
    $stmt = $pdo->prepare("
        SELECT 
            next_run_at,
            last_run_at,
            GREATEST(0, TIMESTAMPDIFF(SECOND, NOW(), next_run_at)) as remaining_seconds
        FROM scrape_schedule
        WHERE app_slug = ? AND client_id = ?
    ");
    $stmt->execute([$appSlug, $clientId]);
    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if scraping is allowed
    if ($schedule && (int)$schedule['remaining_seconds'] > 0) {
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'error' => 'Rate limited',
            'remaining_seconds' => (int)$schedule['remaining_seconds'],
            'next_allowed_at' => $schedule['next_run_at'],
            'message' => 'Next scrape allowed in ' . ceil($schedule['remaining_seconds'] / 60) . ' minutes'
        ]);
        exit;
    }
    
    // Generate idempotency key for this 6-hour window
    $windowStart = floor(time() / 21600) * 21600; // 6-hour windows
    $idempotencyKey = "{$appSlug}:{$clientId}:{$windowStart}";
    
    error_log("🚀 Starting rate-limited scrape for {$appName} (client: {$clientId})");
    
    // Initialize or update scrape schedule
    $stmt = $pdo->prepare("
        INSERT INTO scrape_schedule (app_slug, client_id, next_run_at, last_run_at)
        VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 6 HOUR), NOW())
        ON DUPLICATE KEY UPDATE 
            last_run_at = NOW(),
            next_run_at = DATE_ADD(NOW(), INTERVAL 6 HOUR),
            updated_at = NOW()
    ");
    $stmt->execute([$appSlug, $clientId]);
    
    // Perform the scraping using enhanced scraper
    $scraper = new EnhancedLiveScraper();
    $result = $scraper->scrapeAppWithSnapshot($appSlug, $appName);

    if (!$result['success']) {
        // Rollback schedule update on failure
        $stmt = $pdo->prepare("
            UPDATE scrape_schedule
            SET next_run_at = last_run_at, last_run_at = NULL
            WHERE app_slug = ? AND client_id = ?
        ");
        $stmt->execute([$appSlug, $clientId]);

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Scraping failed: ' . ($result['error'] ?? 'Unknown error')
        ]);
        exit;
    }

    // Create snapshot with enhanced data
    $stmt = $pdo->prepare("
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
    
    $snapshotId = $pdo->lastInsertId();
    
    // Update snapshot pointer for this client
    $stmt = $pdo->prepare("
        INSERT INTO snapshot_pointer (app_slug, client_id, snapshot_id)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            snapshot_id = VALUES(snapshot_id),
            updated_at = NOW()
    ");
    $stmt->execute([$appSlug, $clientId, $snapshotId]);
    
    // Update upstream state for change detection
    $stmt = $pdo->prepare("
        INSERT INTO upstream_state (app_slug, last_content_hash)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE
            last_content_hash = VALUES(last_content_hash),
            last_seen_at = NOW()
    ");
    $stmt->execute([$appSlug, $result['content_hash']]);

    // Return success response
    echo json_encode([
        'success' => true,
        'app' => $appSlug,
        'scraped_count' => $result['scraped_count'],
        'snapshot_id' => $snapshotId,
        'content_hash' => $result['content_hash'],
        'next_scrape_allowed_at' => date('c', time() + 21600), // 6 hours from now
        'message' => "Successfully scraped {$appName}: " . $result['scraped_count'] . " reviews"
    ]);
    
} catch (Exception $e) {
    error_log("Error in scrape-trigger.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error'
    ]);
}
