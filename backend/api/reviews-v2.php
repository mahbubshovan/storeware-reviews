<?php
/**
 * GET /api/reviews/:app - Per-device rate limited review data endpoint
 * Returns cached snapshot data with scraping metadata
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
    
    // Expected: api/reviews/{app_slug}
    if (count($pathParts) < 3 || $pathParts[1] !== 'reviews') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid URL format. Expected: /api/reviews/{app_slug}'
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
    
    // Upsert client record
    $stmt = $pdo->prepare("
        INSERT INTO clients (client_id, first_seen, last_seen) 
        VALUES (?, NOW(), NOW())
        ON DUPLICATE KEY UPDATE last_seen = NOW()
    ");
    $stmt->execute([$clientId]);
    
    // Get current snapshot data for this client
    $stmt = $pdo->prepare("
        SELECT 
            s.totals,
            s.last30Days,
            s.thisMonth,
            s.ratingDistribution,
            s.latestReviews,
            s.scraped_at,
            s.content_hash
        FROM snapshot_pointer sp
        JOIN snapshots s ON sp.snapshot_id = s.id
        WHERE sp.app_slug = ? AND sp.client_id = ?
    ");
    $stmt->execute([$appSlug, $clientId]);
    $snapshot = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get scraping schedule info
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
    
    // Get upstream change status
    $stmt = $pdo->prepare("
        SELECT 
            last_content_hash,
            last_seen_at
        FROM upstream_state
        WHERE app_slug = ?
    ");
    $stmt->execute([$appSlug]);
    $upstream = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Determine if upstream has changes
    $hasUpstreamChanges = false;
    if ($snapshot && $upstream) {
        $hasUpstreamChanges = $snapshot['content_hash'] !== $upstream['last_content_hash'];
    }
    
    // Build response
    $response = [
        'app' => $appSlug,
        'status' => 'ok',
        'data' => null,
        'scrape' => [
            'allowed_now' => false,
            'next_run_at' => null,
            'last_run_at' => null,
            'remaining_seconds' => 0,
            'has_upstream_changes' => $hasUpstreamChanges
        ]
    ];
    
    // Add snapshot data if available
    if ($snapshot) {
        $response['data'] = [
            'totals' => json_decode($snapshot['totals'], true),
            'last30Days' => json_decode($snapshot['last30Days'], true),
            'thisMonth' => json_decode($snapshot['thisMonth'], true),
            'ratingDistribution' => json_decode($snapshot['ratingDistribution'], true),
            'latestReviews' => json_decode($snapshot['latestReviews'], true),
            'scraped_at' => $snapshot['scraped_at']
        ];
    }
    
    // Add schedule info if available
    if ($schedule) {
        $response['scrape'] = [
            'allowed_now' => (int)$schedule['remaining_seconds'] <= 0,
            'next_run_at' => $schedule['next_run_at'],
            'last_run_at' => $schedule['last_run_at'],
            'remaining_seconds' => (int)$schedule['remaining_seconds'],
            'has_upstream_changes' => $hasUpstreamChanges
        ];
    } else {
        // First time visitor - allow immediate scrape
        $response['scrape']['allowed_now'] = true;
    }
    
    header('Content-Type: application/json');
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    error_log("Error in reviews-v2.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error'
    ]);
}
