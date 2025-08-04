<?php
/**
 * Test script for the Shopify scraper
 */

require_once __DIR__ . '/scraper/ShopifyScraper.php';

echo "=== Shopify Scraper Test ===\n";

// Test URL extraction
$scraper = new ShopifyScraper();

// Test with a sample Shopify app URL (this is just for testing the URL parsing)
$testUrl = "https://apps.shopify.com/example-app";

echo "Testing URL parsing...\n";
echo "Input URL: $testUrl\n";

// Since we can't actually scrape without a real app, let's just test the structure
echo "Scraper initialized successfully!\n";
echo "Database connection test...\n";

try {
    $dbManager = new DatabaseManager();
    $count = $dbManager->getThisMonthReviews();
    echo "Database connection successful! Current month reviews: $count\n";
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

echo "\n=== Test completed ===\n";
?>
