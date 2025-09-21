<?php
// Increase execution time for scraping
set_time_limit(120); // 2 minutes
ini_set('max_execution_time', 120);

// Start output buffering to capture any unwanted output
ob_start();

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../scraper/EnhancedUniversalScraper.php';

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
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['app_name'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'app_name is required'
        ]);
        exit;
    }
    
    $appName = $input['app_name'];

    // 🔴 UNIVERSAL LIVE SCRAPER - NO MOCK DATA FOR ANY APP
    error_log("🌐 Using Universal Live Scraper for: $appName");

    // Map app names to their VERIFIED Shopify slugs (ALL 6 ORIGINAL APPS CONFIRMED)
    $appSlugs = [
        // ✅ ORIGINAL 6 APPS - ALL VERIFIED AND WORKING
        'StoreSEO' => 'storeseo',
        'StoreFAQ' => 'storefaq',
        'Vidify' => 'vidify',
        'TrustSync' => 'customer-review-app',
        'EasyFlow' => 'product-options-4',
        'BetterDocs FAQ Knowledge Base' => 'betterdocs-knowledgebase'
    ];

    // Get the Shopify slug for the app
    if (!isset($appSlugs[$appName])) {
        // App not found in verified list
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => "App '$appName' not found on Shopify App Store or not yet supported",
            'scraped_count' => 0,
            'note' => 'This app may not exist on Shopify or may have a different name',
            'supported_apps' => array_keys($appSlugs)
        ]);
        exit;
    }

    $appSlug = $appSlugs[$appName];

    // Create enhanced universal scraper with rate limiting
    $scraper = new EnhancedUniversalScraper();
    $result = $scraper->scrapeAppWithRateLimit($appSlug, $appName);

    // Clear any unwanted output from scraping
    ob_clean();

    if ($result && !empty($result['reviews'])) {
        $scrapedCount = count($result['reviews']);
        $isRateLimited = $result['rate_limited'] ?? false;
        $source = $result['source'] ?? 'unknown';

        // Only trigger sync if we got fresh data (not cached/rate limited)
        if (!$isRateLimited && $source !== 'cached_data') {
            try {
                // Step 1: Perform smart sync to compare with Access Review Tab data
                $smartSyncUrl = 'http://localhost:8000/api/smart-sync-analytics.php';
                $smartSyncData = json_encode(['app_name' => $appName]);

                $context = stream_context_create([
                    'http' => [
                        'method' => 'POST',
                        'header' => 'Content-Type: application/json',
                        'content' => $smartSyncData,
                        'timeout' => 30
                    ]
                ]);

                $smartSyncResponse = @file_get_contents($smartSyncUrl, false, $context);
                $smartSyncResult = $smartSyncResponse ? json_decode($smartSyncResponse, true) : null;

                // Log smart sync results
                if ($smartSyncResult && $smartSyncResult['success']) {
                    $stats = $smartSyncResult['stats'];
                    error_log("Smart sync for {$appName}: {$stats['total_found']} found, {$stats['duplicates_skipped']} skipped, {$stats['new_added']} new");
                } else {
                    error_log("Smart sync failed for {$appName}: " . ($smartSyncResult['error'] ?? 'Unknown error'));
                }

                // Step 2: Regular access reviews sync (this will add the new reviews)
                require_once __DIR__ . '/../utils/AccessReviewsSync.php';
                $accessSync = new AccessReviewsSync();
                $accessSync->syncAccessReviews();

            } catch (Exception $syncError) {
                error_log("Access reviews sync failed: " . $syncError->getMessage());
            }
        }

        // Prepare response with smart sync info
        $response = [
            'success' => true,
            'message' => $isRateLimited ?
                "Rate limited - returned cached data ($scrapedCount reviews)" :
                "Successfully scraped $scrapedCount reviews",
            'scraped_count' => $scrapedCount,
            'rate_limited' => $isRateLimited,
            'source' => $source,
            'app_name' => $appName
        ];

        // Add smart sync results if available
        if (isset($smartSyncResult) && $smartSyncResult && $smartSyncResult['success']) {
            $response['smart_sync'] = $smartSyncResult['stats'];
            $response['message'] .= " | Smart sync: {$smartSyncResult['stats']['new_added']} new reviews added to Access Reviews";
        }

        echo json_encode($response);
    } else {
        // If scraping fails completely
        echo json_encode([
            'success' => false,
            'message' => $result['error'] ?? 'Scraping failed - no data available',
            'scraped_count' => 0,
            'source' => $result['source'] ?? 'error',
            'app_name' => $appName
        ]);
    }
    
} catch (Exception $e) {
    // Clear any unwanted output
    ob_clean();

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error: ' . $e->getMessage()
    ]);
}

// End output buffering
ob_end_flush();
?>
