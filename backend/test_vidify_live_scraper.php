<?php
require_once __DIR__ . '/VidifyLiveScraper.php';

echo "=== TESTING VIDIFY LIVE SCRAPER ===\n\n";

// Test the new live scraper
$scraper = new VidifyLiveScraper();
$result = $scraper->scrapeRealtimeReviews(true);

echo "\n=== SCRAPING RESULT ===\n";
if ($result) {
    echo "Total Stored: " . $result['total_stored'] . "\n";
    echo "This Month: " . $result['this_month'] . "\n";
    echo "Last 30 Days: " . $result['last_30_days'] . "\n";
    echo "New Reviews Count: " . $result['new_reviews_count'] . "\n";
    
    if (isset($result['date_range'])) {
        echo "Date Range: " . $result['date_range']['min_date'] . " to " . $result['date_range']['max_date'] . "\n";
    }
} else {
    echo "Scraping failed or returned no data.\n";
}

echo "\n=== TEST COMPLETED ===\n";
?>
