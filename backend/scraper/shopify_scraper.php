<?php
/**
 * Shopify Scraper Command Line Interface
 * Usage: php shopify_scraper.php [SHOPIFY_APP_URL]
 */

require_once __DIR__ . '/ShopifyScraper.php';

// Check if URL is provided
if ($argc < 2) {
    echo "Usage: php shopify_scraper.php [SHOPIFY_APP_URL]\n";
    echo "Example: php shopify_scraper.php https://apps.shopify.com/example-app\n";
    exit(1);
}

$appUrl = $argv[1];

// Validate URL
if (!filter_var($appUrl, FILTER_VALIDATE_URL) || strpos($appUrl, 'apps.shopify.com') === false) {
    echo "Error: Please provide a valid Shopify app URL\n";
    echo "Example: https://apps.shopify.com/example-app\n";
    exit(1);
}

echo "=== Shopify Review Scraper ===\n";
echo "Target URL: $appUrl\n";
echo "Starting scrape process...\n\n";

try {
    $scraper = new ShopifyScraper();
    $result = $scraper->scrapeApp($appUrl);
    
    if ($result !== false) {
        echo "\n=== Scraping Completed Successfully ===\n";
        echo "Total new reviews scraped: $result\n";
    } else {
        echo "\n=== Scraping Failed ===\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "\nError: " . $e->getMessage() . "\n";
    exit(1);
}
?>
