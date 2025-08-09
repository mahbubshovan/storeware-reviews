<?php
// Increase execution time for scraping
set_time_limit(120); // 2 minutes
ini_set('max_execution_time', 120);

// Start output buffering to capture any unwanted output
ob_start();

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../scraper/ShopifyScraper.php';

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
    
    // Handle different apps with specialized scrapers
    $result = null;

    switch ($appName) {
        case 'StoreFAQ':
            require_once __DIR__ . '/../scraper/StoreFAQUnified.php';
            $scraper = new StoreFAQUnified();
            $result = $scraper->scrapeStoreFAQ();
            $scrapedCount = $result['total_scraped'] ?? 0;
            break;



        case 'StoreSEO':
            require_once __DIR__ . '/../scraper/StoreSEORealtimeScraper.php';
            $scraper = new StoreSEORealtimeScraper();
            $result = $scraper->scrapeStoreSEO();
            $scrapedCount = $result['total_scraped'] ?? 0;
            break;

        case 'Vidify':
            require_once __DIR__ . '/../VidifyDynamicScraper.php';
            $scraper = new VidifyDynamicScraper();
            $result = $scraper->scrapeRealtimeReviews(true);
            $scrapedCount = $result['total_stored'] ?? 0;
            break;

        case 'TrustSync':
            require_once __DIR__ . '/../TrustSyncRealtimeScraper.php';
            $scraper = new TrustSyncRealtimeScraper();
            $result = $scraper->scrapeRealtimeReviews(true);
            $scrapedCount = $result['total_stored'] ?? 0;
            break;

        case 'EasyFlow':
            require_once __DIR__ . '/../EasyFlowRealtimeScraper.php';
            $scraper = new EasyFlowRealtimeScraper();
            $result = $scraper->scrapeRealtimeReviews();
            $scrapedCount = $result['total_stored'] ?? 0;
            break;

        case 'BetterDocs FAQ':
            require_once __DIR__ . '/../BetterDocsFAQRealtimeScraper.php';
            $scraper = new BetterDocsFAQRealtimeScraper();
            $result = $scraper->scrapeRealtimeReviews();
            $scrapedCount = $result['total_stored'] ?? 0;
            break;

        default:
            // Use the general Shopify scraper for other apps
            $scraper = new ShopifyScraper();
            $availableApps = $scraper->getAvailableApps();

            if (!in_array($appName, $availableApps)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid app name',
                    'available_apps' => $availableApps
                ]);
                exit;
            }

            // Start scraping (this might take a while)
            $scrapedCount = $scraper->scrapeAppByName($appName);
            break;
    }

    // Clear any unwanted output from scraping
    ob_clean();

    if ($scrapedCount !== false) {
        echo json_encode([
            'success' => true,
            'message' => "Successfully scraped $scrapedCount new reviews for $appName",
            'scraped_count' => $scrapedCount
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Scraping failed'
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
