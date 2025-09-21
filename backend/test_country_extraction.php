<?php
/**
 * Test script to verify improved country extraction
 */

require_once 'scraper/UniversalLiveScraper.php';
require_once 'utils/DatabaseManager.php';

echo "🧪 TESTING IMPROVED COUNTRY EXTRACTION\n";
echo "=====================================\n\n";

// Test the scraper with fresh data (bypass cache)
$scraper = new UniversalLiveScraper();

// Test StoreSEO with fresh scraping
echo "🎯 Testing StoreSEO with fresh scraping...\n";
$result = $scraper->scrapeApp('storeseo', 'StoreSEO'); // Fresh scrape

if ($result && !empty($result['reviews'])) {
    echo "✅ Fresh scrape successful!\n";
    echo "📊 Total Reviews: " . count($result['reviews']) . "\n\n";
    
    // Analyze country distribution in the fresh data
    $countryStats = [];
    $sampleReviews = [];
    
    foreach ($result['reviews'] as $review) {
        $country = $review['country_name'] ?? 'Unknown';
        $countryStats[$country] = ($countryStats[$country] ?? 0) + 1;
        
        // Collect samples for each country
        if (count($sampleReviews) < 10) {
            $sampleReviews[] = [
                'store_name' => $review['store_name'],
                'country' => $country,
                'rating' => $review['rating'],
                'date' => $review['review_date']
            ];
        }
    }
    
    echo "🌍 COUNTRY DISTRIBUTION IN FRESH DATA:\n";
    arsort($countryStats);
    foreach ($countryStats as $country => $count) {
        $percentage = round(($count / count($result['reviews'])) * 100, 1);
        echo "   {$country}: {$count} ({$percentage}%)\n";
    }
    
    echo "\n📝 SAMPLE REVIEWS WITH COUNTRIES:\n";
    foreach ($sampleReviews as $i => $sample) {
        echo "   " . ($i + 1) . ". {$sample['store_name']} -> {$sample['country']} ({$sample['rating']}★, {$sample['date']})\n";
    }
    
} else {
    echo "❌ Fresh scrape failed!\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🔍 TESTING COUNTRY INFERENCE FROM STORE NAMES\n";
echo str_repeat("=", 50) . "\n";

// Test the country inference logic directly
$testStoreNames = [
    'LEDSone UK Ltd',
    'Advantage Lifts',
    'jsandiclothing LLC',
    '6 Brothers Services LLC',
    'Boost&Game - Site Officiel - N°1 en France',
    'Remlagret.se',
    'Fish Online Store UK',
    'Cafe-Excellence.fr',
    'BBI JustJoi Vinyl Creations and More LLC',
    'Ironbridge & Sons Ltd',
    'Supermarket Italy',
    'GIOO JAPAN 公式オンラインストア',
    'HunnyBoots Australia',
    'BugBell GmbH',
    'Skin Nutrient Australia',
    'tomu.co.za'
];

// Use reflection to access private method
$reflection = new ReflectionClass($scraper);
$method = $reflection->getMethod('inferCountryFromStoreName');
$method->setAccessible(true);

echo "Testing store name inference:\n";
foreach ($testStoreNames as $storeName) {
    $inferredCountry = $method->invoke($scraper, $storeName);
    echo "   {$storeName} -> {$inferredCountry}\n";
}

echo "\n✅ Country extraction test completed!\n";
?>
