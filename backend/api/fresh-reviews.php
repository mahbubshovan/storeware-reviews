<?php
// Increase execution time for fresh scraping
set_time_limit(120);
ini_set('max_execution_time', 120);

// Start output buffering to capture scraper output
ob_start();

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../scraper/UniversalLiveScraper.php';
require_once __DIR__ . '/../utils/DatabaseManager.php';

try {
    // Get app_name from query parameter
    $appName = isset($_GET['app_name']) ? $_GET['app_name'] : null;
    
    if (!$appName) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'app_name parameter is required'
        ]);
        exit;
    }

    // Map app names to their Shopify slugs - STANDARDIZED MAPPING
    $appSlugs = [
        'StoreSEO' => 'storeseo',
        'StoreFAQ' => 'storefaq',
        'Vidify' => 'vidify',
        'TrustSync' => 'customer-review-app',
        'EasyFlow' => 'product-options-4',
        'BetterDocs FAQ Knowledge Base' => 'betterdocs-knowledgebase'
    ];

    if (!isset($appSlugs[$appName])) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => "App '$appName' not supported",
            'supported_apps' => array_keys($appSlugs)
        ]);
        exit;
    }

    $appSlug = $appSlugs[$appName];

    // Force fresh scrape
    error_log("🔄 Force scraping fresh data for: $appName");

    $scraper = new UniversalLiveScraper();
    $scrapeResult = $scraper->scrapeApp($appSlug, $appName);

    // Clear the output buffer (remove scraper output)
    ob_clean();

    // Get fresh reviews from database
    $dbManager = new DatabaseManager();
    $reviews = $dbManager->getLatestReviews(10, $appName);

    // Get updated stats
    $thisMonthCount = $dbManager->getThisMonthReviews($appName);
    $last30DaysCount = $dbManager->getLast30DaysReviews($appName);
    $avgRating = $dbManager->getAverageRating($appName);

    echo json_encode([
        'success' => true,
        'message' => 'Fresh data scraped successfully',
        'scrape_result' => $scrapeResult,
        'reviews' => $reviews,
        'stats' => [
            'this_month' => $thisMonthCount,
            'last_30_days' => $last30DaysCount,
            'average_rating' => $avgRating['average_rating'] ?? 0
        ],
        'app_name' => $appName,
        'scraped_at' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    // Clear any output buffer in case of error
    ob_clean();

    error_log("Fresh reviews error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch fresh reviews: ' . $e->getMessage()
    ]);
}

// End output buffering
ob_end_flush();
?>
