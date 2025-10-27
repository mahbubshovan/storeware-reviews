<?php
/**
 * FINAL REBUILD - Live Reviews Only
 * 
 * Scrapes ONLY the reviews currently visible on live Shopify pages
 * Stops when reaching the exact count shown on Shopify
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/scraper/UniversalLiveScraper.php';

echo "🟢 FINAL REBUILD - LIVE REVIEWS ONLY\n";
echo "====================================\n\n";

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

// Get live counts from Shopify
echo "📋 Getting live counts from Shopify...\n";
$apps = [
    'StoreSEO' => 'storeseo',
    'StoreFAQ' => 'storefaq',
    'EasyFlow' => 'product-options-4',
    'TrustSync' => 'customer-review-app',
    'BetterDocs FAQ Knowledge Base' => 'betterdocs-knowledgebase',
    'Vidify' => 'vidify',
];

$liveCounts = [];
foreach ($apps as $appName => $appSlug) {
    $url = "https://apps.shopify.com/$appSlug/reviews";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    $html = curl_exec($ch);
    curl_close($ch);
    
    if ($html && preg_match('/"ratingCount":(\d+)/', $html, $matches)) {
        $liveCounts[$appName] = (int)$matches[1];
        echo "  $appName: {$liveCounts[$appName]}\n";
    }
}
echo "\n";

// Scrape with target counts
$scraper = new UniversalLiveScraper();
$results = [];
$totalScraped = 0;

foreach ($apps as $appName => $appSlug) {
    $targetCount = $liveCounts[$appName] ?? null;
    
    echo "\n📱 Scraping $appName (target: $targetCount)...\n";
    echo "================================\n";
    
    try {
        $result = $scraper->scrapeApp($appSlug, $appName, $targetCount);
        
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
echo "====================================\n\n";

$stmt = $conn->prepare("SELECT app_name, COUNT(*) as total FROM reviews GROUP BY app_name ORDER BY app_name");
$stmt->execute();
$dbResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "📊 Database vs Live Shopify:\n";
$dbTotal = 0;
$allMatch = true;
foreach ($dbResults as $row) {
    $app = $row['app_name'];
    $dbCount = $row['total'];
    $liveCount = $liveCounts[$app] ?? 0;
    $match = ($dbCount === $liveCount) ? "✅" : "❌";
    echo "$match {$app}: DB={$dbCount}, Live={$liveCount}\n";
    $dbTotal += $dbCount;
    if ($dbCount !== $liveCount) {
        $allMatch = false;
    }
}
echo "\nTOTAL: DB=$dbTotal, Live=" . array_sum($liveCounts) . "\n\n";

if ($allMatch) {
    echo "🎉 SUCCESS! All counts match live Shopify pages!\n";
    echo "Database contains ONLY live, visible reviews!\n";
} else {
    echo "⚠️ Some counts don't match. Check the scraping results above.\n";
}

