<?php
/**
 * COMPLETE DATABASE RESET AND REBUILD
 * 
 * This script:
 * 1. Clears ALL existing data (reviews, access_reviews, review_cache)
 * 2. Scrapes ONLY live, visible reviews from Shopify (no archived reviews)
 * 3. Stores only reviews currently visible in pagination
 * 4. Rebuilds the database from scratch with fresh data
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/scraper/UniversalLiveScraper.php';

echo "🔴 STARTING COMPLETE DATABASE RESET AND REBUILD\n";
echo "================================================\n\n";

$conn = (new Database())->getConnection();

// Step 1: Clear ALL existing data
echo "📋 STEP 1: Clearing all existing data...\n";
try {
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Clear all tables
    $conn->exec("DELETE FROM access_reviews");
    $conn->exec("DELETE FROM reviews");
    $conn->exec("DELETE FROM review_cache");
    $conn->exec("DELETE FROM app_metadata");
    
    $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "✅ All data cleared successfully\n\n";
} catch (Exception $e) {
    echo "❌ Error clearing data: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 2: Fresh scrape - Live reviews only
echo "📋 STEP 2: Fresh scrape of LIVE reviews only...\n";
echo "================================================\n\n";

$apps = [
    'StoreSEO' => 'storeseo',
    'StoreFAQ' => 'storefaq',
    'EasyFlow' => 'product-options-4',
    'TrustSync' => 'customer-review-app',
    'BetterDocs FAQ Knowledge Base' => 'betterdocs-knowledgebase',
    'Vidify' => 'vidify',
];

$scraper = new UniversalLiveScraper();
$totalScraped = 0;
$results = [];

foreach ($apps as $appName => $appSlug) {
    echo "\n📱 Scraping $appName ($appSlug)...\n";
    echo "-----------------------------------\n";
    
    try {
        $result = $scraper->scrapeApp($appSlug, $appName);
        
        if ($result['success']) {
            $count = $result['count'];
            $totalScraped += $count;
            $results[$appName] = [
                'success' => true,
                'count' => $count,
                'message' => $result['message']
            ];
            echo "✅ $appName: {$count} reviews scraped\n";
        } else {
            $results[$appName] = [
                'success' => false,
                'error' => $result['message']
            ];
            echo "❌ $appName: Failed - " . $result['message'] . "\n";
        }
    } catch (Exception $e) {
        $results[$appName] = [
            'success' => false,
            'error' => $e->getMessage()
        ];
        echo "❌ $appName: Exception - " . $e->getMessage() . "\n";
    }
}

// Step 3: Verify results
echo "\n\n📋 STEP 3: Verification\n";
echo "================================================\n\n";

$stmt = $conn->prepare("SELECT app_name, COUNT(*) as total FROM reviews GROUP BY app_name ORDER BY app_name");
$stmt->execute();
$dbResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "📊 Final Database Counts:\n";
$grandTotal = 0;
foreach ($dbResults as $row) {
    echo "  {$row['app_name']}: {$row['total']} reviews\n";
    $grandTotal += $row['total'];
}
echo "\n  TOTAL: $grandTotal reviews\n\n";

// Summary
echo "✅ DATABASE RESET AND REBUILD COMPLETE!\n";
echo "================================================\n";
echo "Summary:\n";
foreach ($results as $appName => $result) {
    if ($result['success']) {
        echo "  ✅ $appName: {$result['count']} reviews\n";
    } else {
        echo "  ❌ $appName: {$result['error']}\n";
    }
}
echo "\nTotal reviews in database: $grandTotal\n";
echo "All data is now LIVE and FRESH from Shopify!\n";

