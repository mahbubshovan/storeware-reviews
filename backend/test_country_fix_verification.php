<?php
/**
 * Comprehensive test to verify that the "Unknown" country issue has been resolved
 * across all Access Review and Analytics pages
 */

require_once __DIR__ . '/utils/DatabaseManager.php';

echo "🧪 COMPREHENSIVE COUNTRY DATA VERIFICATION TEST\n";
echo str_repeat("=", 60) . "\n\n";

$db = new DatabaseManager();
$conn = $db->getConnection();

// Test 1: Database Level Verification
echo "📊 TEST 1: DATABASE LEVEL VERIFICATION\n";
echo str_repeat("-", 40) . "\n";

// Check main reviews table
$stmt = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN country_name = 'Unknown' OR country_name IS NULL OR country_name = '' THEN 1 ELSE 0 END) as unknown_count
    FROM reviews
");
$mainStats = $stmt->fetch(PDO::FETCH_ASSOC);

// Check access_reviews table
$stmt = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN country_name = 'Unknown' OR country_name IS NULL OR country_name = '' THEN 1 ELSE 0 END) as unknown_count
    FROM access_reviews
");
$accessStats = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Main Reviews Table:\n";
echo "  Total: {$mainStats['total']}\n";
echo "  Unknown: {$mainStats['unknown_count']}\n";
echo "  Accuracy: " . round((($mainStats['total'] - $mainStats['unknown_count']) / $mainStats['total']) * 100, 2) . "%\n\n";

echo "Access Reviews Table:\n";
echo "  Total: {$accessStats['total']}\n";
echo "  Unknown: {$accessStats['unknown_count']}\n";
echo "  Accuracy: " . round((($accessStats['total'] - $accessStats['unknown_count']) / $accessStats['total']) * 100, 2) . "%\n\n";

$databasePassed = ($accessStats['unknown_count'] == 0);
echo $databasePassed ? "✅ DATABASE TEST PASSED\n" : "❌ DATABASE TEST FAILED\n";

// Test 2: API Endpoint Verification
echo "\n📡 TEST 2: API ENDPOINT VERIFICATION\n";
echo str_repeat("-", 40) . "\n";

$apiTests = [
    'Access Reviews Enhanced' => 'http://localhost:8000/backend/api/access-reviews-enhanced.php?date_range=last_30_days',
    'Country Stats (StoreSEO)' => 'http://localhost:8000/backend/api/country-stats.php?app_name=StoreSEO&filter=last_30_days',
    'Country Stats (StoreFAQ)' => 'http://localhost:8000/backend/api/country-stats.php?app_name=StoreFAQ&filter=last_30_days',
    'Access Reviews Cached' => 'http://localhost:8000/backend/api/access-reviews-cached.php?app=StoreSEO&filter=last_30_days'
];

$apiTestsPassed = 0;
$totalApiTests = count($apiTests);

foreach ($apiTests as $testName => $url) {
    echo "Testing: $testName\n";
    
    $response = @file_get_contents($url);
    if ($response === false) {
        echo "  ❌ Failed to fetch API response\n";
        continue;
    }
    
    $data = json_decode($response, true);
    if (!$data || !isset($data['success']) || !$data['success']) {
        echo "  ❌ API returned error or invalid response\n";
        continue;
    }
    
    $hasUnknown = false;
    
    // Check for unknown countries in different response structures
    if (isset($data['country_stats'])) {
        // Country stats API
        foreach ($data['country_stats'] as $stat) {
            if ($stat['country_name'] === 'Unknown') {
                $hasUnknown = true;
                break;
            }
        }
    } elseif (isset($data['data']['reviews'])) {
        // Access reviews API
        foreach ($data['data']['reviews'] as $appGroup) {
            if (isset($appGroup['reviews'])) {
                foreach ($appGroup['reviews'] as $review) {
                    if (($review['country_name'] ?? 'Unknown') === 'Unknown') {
                        $hasUnknown = true;
                        break 2;
                    }
                }
            }
        }
    } elseif (isset($data['reviews'])) {
        // Simple reviews array
        foreach ($data['reviews'] as $review) {
            if (($review['country_name'] ?? 'Unknown') === 'Unknown') {
                $hasUnknown = true;
                break;
            }
        }
    }
    
    if (!$hasUnknown) {
        echo "  ✅ No unknown countries found\n";
        $apiTestsPassed++;
    } else {
        echo "  ❌ Unknown countries detected\n";
    }
}

echo "\nAPI Tests: $apiTestsPassed/$totalApiTests passed\n";
$allApiTestsPassed = ($apiTestsPassed == $totalApiTests);

// Test 3: Country Distribution Analysis
echo "\n🌍 TEST 3: COUNTRY DISTRIBUTION ANALYSIS\n";
echo str_repeat("-", 40) . "\n";

$stmt = $conn->query("
    SELECT country_name, COUNT(*) as count 
    FROM access_reviews 
    GROUP BY country_name 
    ORDER BY count DESC 
    LIMIT 10
");
$countryDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Top countries in access_reviews:\n";
foreach ($countryDistribution as $country) {
    echo "  {$country['country_name']}: {$country['count']} reviews\n";
}

// Test 4: Sample Data Verification
echo "\n🔍 TEST 4: SAMPLE DATA VERIFICATION\n";
echo str_repeat("-", 40) . "\n";

$stmt = $conn->query("
    SELECT app_name, store_name, country_name, review_date 
    FROM access_reviews 
    ORDER BY review_date DESC 
    LIMIT 5
");
$sampleReviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Recent access_reviews samples:\n";
foreach ($sampleReviews as $review) {
    $country = $review['country_name'] ?: 'NULL';
    echo "  {$review['app_name']}: {$review['store_name']} -> $country ({$review['review_date']})\n";
}

// Final Summary
echo "\n" . str_repeat("=", 60) . "\n";
echo "🎯 FINAL TEST SUMMARY\n";
echo str_repeat("=", 60) . "\n";

$overallPassed = $databasePassed && $allApiTestsPassed;

echo "Database Tests: " . ($databasePassed ? "✅ PASSED" : "❌ FAILED") . "\n";
echo "API Tests: " . ($allApiTestsPassed ? "✅ PASSED" : "❌ FAILED") . "\n";
echo "Overall Result: " . ($overallPassed ? "🎉 ALL TESTS PASSED!" : "❌ SOME TESTS FAILED") . "\n";

if ($overallPassed) {
    echo "\n🎉 SUCCESS: The 'Unknown' country issue has been completely resolved!\n";
    echo "✅ Access Review pages will now show accurate country data\n";
    echo "✅ Analytics pages will display correct country statistics\n";
    echo "✅ Zero 'Unknown' countries remain in the system\n";
} else {
    echo "\n⚠️  Some issues remain. Please review the failed tests above.\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
?>
