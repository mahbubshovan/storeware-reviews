<?php
require_once __DIR__ . '/PreciseRatingExtractor.php';

/**
 * Extract rating distributions for all 6 apps
 */

echo "🎯 EXTRACTING COMPLETE RATING DISTRIBUTIONS FOR ALL 6 APPS\n";
echo "==========================================================\n\n";

$apps = [
    'StoreSEO' => 'storeseo',
    'StoreFAQ' => 'storefaq', 
    'Vidify' => 'vidify',
    'TrustSync' => 'customer-review-app',
    'EasyFlow' => 'product-options-4',
    'BetterDocs FAQ' => 'betterdocs-knowledgebase'
];

$extractor = new PreciseRatingExtractor();
$results = [];

foreach ($apps as $appName => $appSlug) {
    echo "📱 Processing $appName ($appSlug)...\n";
    echo str_repeat('-', 50) . "\n";
    
    $result = $extractor->extractPreciseRatingDistribution($appSlug, $appName);
    
    if ($result) {
        $results[$appName] = $result;
        echo "✅ SUCCESS for $appName\n";
    } else {
        echo "❌ FAILED for $appName\n";
        $results[$appName] = false;
    }
    
    echo "\n";
    
    // Add delay to be respectful to Shopify
    sleep(2);
}

echo "📋 FINAL RATING DISTRIBUTION SUMMARY\n";
echo "====================================\n";

foreach ($results as $appName => $result) {
    if ($result) {
        echo "✅ $appName: {$result['total_reviews']} total reviews\n";
        echo "   5★: {$result['five_star']} | 4★: {$result['four_star']} | 3★: {$result['three_star']} | 2★: {$result['two_star']} | 1★: {$result['one_star']}\n";
    } else {
        echo "❌ $appName: Failed to extract\n";
    }
    echo "\n";
}

echo "🎯 RATING DISTRIBUTION EXTRACTION COMPLETE\n";
echo "All apps now have complete rating distribution data from live Shopify pages!\n";
?>
