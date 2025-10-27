<?php
/**
 * Scrape Missing Reviews
 * Fetches reviews from Shopify pages to match the exact live count
 */

require_once 'config/database.php';
require_once 'scraper/UniversalLiveScraper.php';

try {
    $database = new Database();
    $conn = $database->getConnection();

    $apps = [
        'StoreSEO' => 'storeseo',
        'StoreFAQ' => 'storefaq',
        'EasyFlow' => 'product-options-4',
        'TrustSync' => 'customer-review-app',
        'BetterDocs FAQ Knowledge Base' => 'betterdocs-knowledgebase',
        'Vidify' => 'vidify'
    ];

    // Target counts from live Shopify pages (from JSON-LD ratingCount)
    $targetCounts = [
        'StoreSEO' => 527,
        'StoreFAQ' => 110,
        'EasyFlow' => 320,
        'TrustSync' => 41,
        'BetterDocs FAQ Knowledge Base' => 35,
        'Vidify' => 8
    ];

    echo "=== SCRAPING TO MATCH LIVE SHOPIFY COUNTS ===\n\n";

    $scraper = new UniversalLiveScraper();
    $totalAdded = 0;

    foreach ($apps as $appName => $appSlug) {
        // Get current count
        $query = "SELECT COUNT(*) as count FROM reviews WHERE app_name = ? AND is_active = 1";
        $stmt = $conn->prepare($query);
        $stmt->execute([$appName]);
        $currentCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        $targetCount = $targetCounts[$appName];
        $needed = $targetCount - $currentCount;

        echo "📱 $appName:\n";
        echo "   Current: $currentCount | Target: $targetCount | Needed: $needed\n";

        if ($needed <= 0) {
            echo "   ✅ Already at target\n\n";
            continue;
        }

        // Scrape all reviews
        echo "   🔄 Scraping all pages...\n";

        $result = $scraper->scrapeApp($appSlug, $appName);

        if ($result && isset($result['reviews'])) {
            $scraped = count($result['reviews']);
            echo "   ✅ Scraped $scraped reviews\n";

            // Get new count
            $query = "SELECT COUNT(*) as count FROM reviews WHERE app_name = ? AND is_active = 1";
            $stmt = $conn->prepare($query);
            $stmt->execute([$appName]);
            $newCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            $added = $newCount - $currentCount;
            $totalAdded += $added;
            echo "   ✅ Added $added new reviews (Total now: $newCount)\n";
        } else {
            echo "   ❌ Scraping failed\n";
        }

        echo "\n";
        sleep(2); // Delay between apps
    }

    echo "✅ Scraping complete! Total reviews added: $totalAdded\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

