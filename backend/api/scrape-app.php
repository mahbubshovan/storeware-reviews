<?php
// Increase execution time for scraping
set_time_limit(120); // 2 minutes
ini_set('max_execution_time', 120);

// Start output buffering to capture any unwanted output
ob_start();

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../scraper/UniversalLiveScraper.php';

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
        'BetterDocs FAQ' => 'betterdocs-knowledgebase'
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

    // Create universal live scraper - NO FALLBACKS, NO MOCK DATA
    $scraper = new UniversalLiveScraper();
    $result = $scraper->scrapeApp($appSlug, $appName);

    $scrapedCount = $result['count'] ?? 0;

    if ($result['success']) {
        // Trigger access reviews sync after successful scraping
        try {
            require_once __DIR__ . '/../utils/AccessReviewsSync.php';
            $accessSync = new AccessReviewsSync();
            $accessSync->syncAccessReviews();
        } catch (Exception $syncError) {
            error_log("Access reviews sync failed: " . $syncError->getMessage());
        }
    }

    // Clear any unwanted output from scraping and sync
    ob_clean();

    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => $result['message'],
            'scraped_count' => $scrapedCount
        ]);
    } else {
        // If live scraping fails, return empty results - NO MOCK DATA
        echo json_encode([
            'success' => false,
            'message' => $result['message'],
            'scraped_count' => 0,
            'note' => 'Live scraping failed - no fallback data provided'
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
