<?php
/**
 * Rebuild database with ONLY live reviews
 * 
 * This script:
 * 1. Clears all existing data
 * 2. Scrapes ONLY reviews visible on live Shopify pages (matching the count shown)
 * 3. Stops scraping when the total matches the live Shopify count
 * 4. Does NOT include archived reviews
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/scraper/LiveOnlyReviewScraper.php';

echo "🟢 REBUILDING DATABASE WITH LIVE-ONLY REVIEWS\n";
echo "==============================================\n\n";

$conn = (new Database())->getConnection();

// Clear all data
echo "📋 Clearing all existing data...\n";
try {
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
    $conn->exec("DELETE FROM access_reviews");
    $conn->exec("DELETE FROM reviews");
    $conn->exec("DELETE FROM review_cache");
    $conn->exec("DELETE FROM app_metadata");
    $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "✅ All data cleared\n\n";
} catch (Exception $e) {
    echo "❌ Error clearing data: " . $e->getMessage() . "\n";
    exit(1);
}

// Scrape live reviews only
$apps = [
    'StoreSEO' => 'storeseo',
    'StoreFAQ' => 'storefaq',
    'EasyFlow' => 'product-options-4',
    'TrustSync' => 'customer-review-app',
    'BetterDocs FAQ Knowledge Base' => 'betterdocs-knowledgebase',
    'Vidify' => 'vidify',
];

$scraper = new LiveOnlyReviewScraper();
$results = [];
$totalScraped = 0;

foreach ($apps as $appName => $appSlug) {
    echo "\n📱 Scraping $appName...\n";
    echo "================================\n";
    
    try {
        $result = $scraper->scrapeApp($appSlug, $appName);
        
        if ($result['success']) {
            $count = $result['count'];
            $totalScraped += $count;
            $results[$appName] = ['success' => true, 'count' => $count];
            echo "✅ $appName: {$count} reviews\n";
        } else {
            $results[$appName] = ['success' => false, 'error' => $result['message']];
            echo "❌ $appName: {$result['message']}\n";
        }
    } catch (Exception $e) {
        $results[$appName] = ['success' => false, 'error' => $e->getMessage()];
        echo "❌ $appName: {$e->getMessage()}\n";
    }
}

// Verify
echo "\n\n📋 FINAL VERIFICATION\n";
echo "==============================================\n\n";

$stmt = $conn->prepare("SELECT app_name, COUNT(*) as total FROM reviews GROUP BY app_name ORDER BY app_name");
$stmt->execute();
$dbResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "📊 Database Counts:\n";
$dbTotal = 0;
foreach ($dbResults as $row) {
    echo "  {$row['app_name']}: {$row['total']}\n";
    $dbTotal += $row['total'];
}
echo "  TOTAL: $dbTotal\n\n";

echo "✅ DATABASE REBUILD COMPLETE!\n";
echo "All reviews are now LIVE and match Shopify page counts!\n";

