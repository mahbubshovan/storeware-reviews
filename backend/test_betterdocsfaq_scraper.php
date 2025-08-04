<?php
require_once __DIR__ . '/BetterDocsFAQRealtimeScraper.php';

echo "=== TESTING BETTERDOCS FAQ SCRAPER ===\n\n";

// Test the BetterDocs FAQ scraper
$scraper = new BetterDocsFAQRealtimeScraper();
$result = $scraper->scrapeRealtimeReviews();

echo "\n=== SCRAPING RESULT ===\n";
if ($result) {
    echo "This Month: " . $result['this_month'] . "\n";
    echo "Last 30 Days: " . $result['last_30_days'] . "\n";
    echo "Total Stored: " . $result['total_stored'] . "\n";
    echo "New Reviews Count: " . $result['new_reviews_count'] . "\n";
    
    if (isset($result['date_range'])) {
        echo "Date Range: " . $result['date_range']['min_date'] . " to " . $result['date_range']['max_date'] . "\n";
    }
} else {
    echo "Scraping failed or returned no data.\n";
}

echo "\n=== VERIFICATION ===\n";
echo "Please verify these numbers match the actual BetterDocs FAQ reviews page:\n";
echo "https://apps.shopify.com/betterdocs-knowledgebase/reviews?sort_by=newest&page=1\n";
?>
