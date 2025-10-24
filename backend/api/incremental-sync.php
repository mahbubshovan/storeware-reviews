<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../scraper/IncrementalSyncScraper.php';

/**
 * Incremental Sync API Endpoint
 * 
 * Usage:
 * GET /backend/api/incremental-sync.php?app=StoreSEO
 * 
 * Response:
 * {
 *   "success": true,
 *   "message": "Incremental sync complete: 5 new reviews",
 *   "count": 5,
 *   "total_count": 526,
 *   "new_reviews": 5,
 *   "duration_seconds": 12.5
 * }
 */

try {
    $startTime = microtime(true);
    
    // Get app parameter
    $app = isset($_GET['app']) ? trim($_GET['app']) : null;
    
    if (!$app) {
        echo json_encode([
            'success' => false,
            'error' => 'Missing app parameter'
        ]);
        exit;
    }
    
    // Map app name to slug
    $appSlugs = [
        'StoreSEO' => 'storeseo',
        'StoreFAQ' => 'storefaq',
        'EasyFlow' => 'easyflow',
        'BetterDocs' => 'betterdocs-knowledgebase',
        'TrustSync' => 'trustsync',
        'Vidify' => 'vidify'
    ];
    
    if (!isset($appSlugs[$app])) {
        echo json_encode([
            'success' => false,
            'error' => "Unknown app: $app"
        ]);
        exit;
    }
    
    $appSlug = $appSlugs[$app];
    
    // Perform incremental sync
    $scraper = new IncrementalSyncScraper();
    $result = $scraper->incrementalSync($appSlug, $app);
    
    // Calculate duration
    $duration = round(microtime(true) - $startTime, 2);
    
    // Add duration to result
    $result['duration_seconds'] = $duration;
    
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>

