<?php
require_once __DIR__ . '/scraper/ShopifyScraper.php';

echo "=== TESTING INCREMENTAL SCRAPING ===\n\n";

// Test StoreSEO first
echo "1. Testing StoreSEO incremental scraping...\n";
$scraper = new ShopifyScraper();
$result1 = $scraper->scrapeAppByName('StoreSEO');
echo "First run result: $result1 new reviews\n\n";

// Test StoreSEO again (should find fewer or no new reviews)
echo "2. Testing StoreSEO incremental scraping again (should find fewer/no new reviews)...\n";
$result2 = $scraper->scrapeAppByName('StoreSEO');
echo "Second run result: $result2 new reviews\n\n";

// Test StoreFAQ first
echo "3. Testing StoreFAQ incremental scraping...\n";
$result3 = $scraper->scrapeAppByName('StoreFAQ');
echo "First run result: $result3 new reviews\n\n";

// Test StoreFAQ again (should find fewer or no new reviews)
echo "4. Testing StoreFAQ incremental scraping again (should find fewer/no new reviews)...\n";
$result4 = $scraper->scrapeAppByName('StoreFAQ');
echo "Second run result: $result4 new reviews\n\n";

echo "=== TEST COMPLETED ===\n";
echo "StoreSEO: First run = $result1, Second run = $result2\n";
echo "StoreFAQ: First run = $result3, Second run = $result4\n";

if ($result2 <= $result1 && $result4 <= $result3) {
    echo "✅ SUCCESS: Incremental scraping is working correctly!\n";
} else {
    echo "❌ ISSUE: Incremental scraping may not be working as expected.\n";
}
?>
