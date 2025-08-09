<?php
echo "=== TESTING VIDIFY API DIRECTLY ===\n\n";

// Set up the environment to simulate a POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['app_name'] = 'Vidify';

// Include required files
require_once __DIR__ . '/VidifyLiveScraper.php';

echo "Testing Vidify scraping through API logic...\n\n";

$appName = 'Vidify';
$scrapedCount = 0;

try {
    require_once __DIR__ . '/VidifyLiveScraper.php';
    $scraper = new VidifyLiveScraper();
    $result = $scraper->scrapeRealtimeReviews(true);
    $scrapedCount = $result['total_stored'] ?? 0;
    
    echo "\n=== SCRAPING RESULT ===\n";
    echo "App: $appName\n";
    echo "Scraped Count: $scrapedCount\n";
    echo "This Month: " . ($result['this_month'] ?? 0) . "\n";
    echo "Last 30 Days: " . ($result['last_30_days'] ?? 0) . "\n";
    
    $response = [
        'success' => true,
        'app_name' => $appName,
        'scraped_count' => $scrapedCount,
        'message' => "Successfully scraped $scrapedCount reviews for $appName"
    ];
    
    echo "\nAPI Response:\n";
    echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
    
    echo "\nError Response:\n";
    echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
}

echo "\n=== API TEST COMPLETED ===\n";
?>
