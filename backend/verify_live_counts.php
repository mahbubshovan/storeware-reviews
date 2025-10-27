<?php
/**
 * Verify that database counts match live Shopify page counts
 */

require_once __DIR__ . '/config/database.php';

echo "🔍 VERIFYING DATABASE COUNTS MATCH LIVE SHOPIFY PAGES\n";
echo "====================================================\n\n";

$conn = (new Database())->getConnection();

$apps = [
    'StoreSEO' => 'storeseo',
    'StoreFAQ' => 'storefaq',
    'EasyFlow' => 'product-options-4',
    'TrustSync' => 'customer-review-app',
    'BetterDocs FAQ Knowledge Base' => 'betterdocs-knowledgebase',
    'Vidify' => 'vidify',
];

// Get database counts
$stmt = $conn->prepare("SELECT app_name, COUNT(*) as total FROM reviews GROUP BY app_name ORDER BY app_name");
$stmt->execute();
$dbCounts = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $dbCounts[$row['app_name']] = $row['total'];
}

echo "📊 DATABASE COUNTS:\n";
$dbTotal = 0;
foreach ($dbCounts as $app => $count) {
    echo "  $app: $count\n";
    $dbTotal += $count;
}
echo "  TOTAL: $dbTotal\n\n";

// Get live Shopify counts
echo "🌐 FETCHING LIVE SHOPIFY COUNTS...\n\n";

$liveCount = [];
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
        $count = (int)$matches[1];
        $liveCount[$appName] = $count;
        echo "  $appName: $count\n";
    } else {
        echo "  $appName: ❌ Could not fetch\n";
    }
}

echo "\n📊 LIVE SHOPIFY COUNTS:\n";
$liveTotal = 0;
foreach ($liveCount as $app => $count) {
    echo "  $app: $count\n";
    $liveTotal += $count;
}
echo "  TOTAL: $liveTotal\n\n";

// Compare
echo "✅ COMPARISON:\n";
echo "================================================\n";
$allMatch = true;
foreach ($apps as $appName => $appSlug) {
    $db = $dbCounts[$appName] ?? 0;
    $live = $liveCount[$appName] ?? 0;
    $match = ($db === $live) ? "✅" : "❌";
    echo "$match $appName: DB=$db, Live=$live\n";
    if ($db !== $live) {
        $allMatch = false;
    }
}

echo "\n";
if ($allMatch) {
    echo "🎉 ALL COUNTS MATCH! Database is in sync with live Shopify pages!\n";
} else {
    echo "⚠️ Some counts don't match. Database may need updating.\n";
}

