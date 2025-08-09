<?php
/**
 * Test script to verify StoreSEO data accuracy fixes
 * Run this to test all the fixes we made
 */

require_once __DIR__ . '/StoreSEORealtimeScraper.php';
require_once __DIR__ . '/utils/DatabaseManager.php';

echo "=== TESTING STORESEO DATA ACCURACY FIXES ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "Today is August 9th, 2025\n\n";

// Clear existing data for fresh test
echo "1. Clearing existing StoreSEO data...\n";
$dbManager = new DatabaseManager();
$conn = $dbManager->getConnection();

$stmt = $conn->prepare("DELETE FROM reviews WHERE app_name = 'StoreSEO'");
$stmt->execute();

$stmt = $conn->prepare("DELETE FROM app_metadata WHERE app_name = 'StoreSEO'");
$stmt->execute();

echo "✅ Cleared existing data\n\n";

// Test the scraper
echo "2. Running StoreSEO scraper with fixes...\n";
$scraper = new StoreSEORealtimeScraper();
$result = $scraper->scrapeRealtimeReviews(true);

echo "\n3. Testing database queries...\n";

// Test this month count (August 1-9)
$thisMonth = $dbManager->getThisMonthReviews('StoreSEO');
echo "This Month (Aug 1-9): $thisMonth reviews\n";

// Test last 30 days count
$last30Days = $dbManager->getLast30DaysReviews('StoreSEO');
echo "Last 30 Days: $last30Days reviews\n";

// Test last month count (July 9 and earlier)
$lastMonth = $dbManager->getLastMonthReviews('StoreSEO');
echo "Last Month (July 9 and earlier): $lastMonth reviews\n";

// Test rating distribution
echo "\n4. Testing rating distribution...\n";
$distribution = $dbManager->getReviewDistribution('StoreSEO');
echo "Total Reviews: {$distribution['total_reviews']}\n";
echo "5-star: {$distribution['five_star']}\n";
echo "4-star: {$distribution['four_star']}\n";
echo "3-star: {$distribution['three_star']}\n";
echo "2-star: {$distribution['two_star']}\n";
echo "1-star: {$distribution['one_star']}\n";

// Verify star counts add up
$totalStars = $distribution['five_star'] + $distribution['four_star'] + 
              $distribution['three_star'] + $distribution['two_star'] + $distribution['one_star'];
echo "Star count verification: $totalStars vs {$distribution['total_reviews']}\n";

// Test API endpoints
echo "\n5. Testing API endpoints...\n";

function testAPI($endpoint) {
    $url = "http://localhost:8000/api/$endpoint?app_name=StoreSEO&_t=" . time();
    $response = @file_get_contents($url);
    if ($response) {
        $data = json_decode($response, true);
        return $data;
    }
    return null;
}

$thisMonthAPI = testAPI('this-month-reviews.php');
$last30DaysAPI = testAPI('last-30-days-reviews.php');
$lastMonthAPI = testAPI('last-month-reviews.php');
$distributionAPI = testAPI('review-distribution.php');

if ($thisMonthAPI) {
    echo "API This Month: {$thisMonthAPI['count']}\n";
} else {
    echo "❌ This Month API failed\n";
}

if ($last30DaysAPI) {
    echo "API Last 30 Days: {$last30DaysAPI['count']}\n";
} else {
    echo "❌ Last 30 Days API failed\n";
}

if ($lastMonthAPI) {
    echo "API Last Month: {$lastMonthAPI['count']}\n";
} else {
    echo "❌ Last Month API failed\n";
}

if ($distributionAPI) {
    echo "API Total Reviews: {$distributionAPI['total_reviews']}\n";
} else {
    echo "❌ Distribution API failed\n";
}

// Summary
echo "\n=== TEST SUMMARY ===\n";
echo "Expected behavior based on August 9th:\n";
echo "- This Month: Reviews from August 1-9\n";
echo "- Last 30 Days: Reviews from July 10 onwards\n";
echo "- Last Month: Reviews from July 9 and earlier\n";
echo "- Total Reviews: Should match live StoreSEO page (516)\n";
echo "- Rating Distribution: Should reflect real data from StoreSEO\n\n";

echo "Actual results:\n";
echo "- This Month: $thisMonth\n";
echo "- Last 30 Days: $last30Days\n";
echo "- Last Month: $lastMonth\n";
echo "- Total Reviews: {$distribution['total_reviews']}\n";
echo "- Star Distribution Valid: " . ($totalStars == $distribution['total_reviews'] ? 'Yes' : 'No') . "\n";

echo "\n✅ Test completed! Check results above.\n";
?>
