<?php
require_once __DIR__ . '/StoreFAQRealtimeScraper.php';
require_once __DIR__ . '/StoreSEORealtimeScraper.php';

echo "=== TESTING BOTH SCRAPERS ===\n\n";

// Test the StoreFAQ scraper
echo "=== TESTING STOREFAQ SCRAPER ===\n";
$storefaqScraper = new StoreFAQRealtimeScraper();
$storefaqResult = $storefaqScraper->scrapeRealtimeReviews();

echo "\n=== STOREFAQ RESULT ===\n";
if ($storefaqResult) {
    echo "This Month: " . $storefaqResult['this_month'] . "\n";
    echo "Last 30 Days: " . $storefaqResult['last_30_days'] . "\n";
    echo "Total Stored: " . $storefaqResult['total_stored'] . "\n";

    if (isset($storefaqResult['date_range'])) {
        echo "Date Range: " . $storefaqResult['date_range']['min_date'] . " to " . $storefaqResult['date_range']['max_date'] . "\n";
    }
} else {
    echo "StoreFAQ scraping failed.\n";
}

echo "\n" . str_repeat("=", 50) . "\n\n";

// Test the StoreSEO scraper
echo "=== TESTING STORESEO SCRAPER ===\n";
$storeseoScraper = new StoreSEORealtimeScraper();
$storeseoResult = $storeseoScraper->scrapeRealtimeReviews();

echo "\n=== STORESEO RESULT ===\n";
if ($storeseoResult) {
    echo "This Month: " . $storeseoResult['this_month'] . "\n";
    echo "Last 30 Days: " . $storeseoResult['last_30_days'] . "\n";
    echo "Total Stored: " . $storeseoResult['total_stored'] . "\n";

    if (isset($storeseoResult['date_range'])) {
        echo "Date Range: " . $storeseoResult['date_range']['earliest'] . " to " . $storeseoResult['date_range']['latest'] . "\n";
    }
} else {
    echo "StoreSEO scraping failed.\n";
}

echo "\n=== VERIFICATION ===\n";
echo "Please verify these numbers match the actual Shopify app review pages:\n";
echo "StoreFAQ: https://apps.shopify.com/storefaq/reviews?sort_by=newest&page=1\n";
echo "StoreSEO: https://apps.shopify.com/storeseo/reviews?sort_by=newest&page=1\n";
?>
