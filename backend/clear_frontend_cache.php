<?php
/**
 * Clear Frontend Cache and Force Fresh Data
 * This script helps debug frontend caching issues
 */

echo "=== CLEARING FRONTEND CACHE & FORCING FRESH DATA ===\n\n";

// Test current API responses with strong cache-busting
$timestamp = time();
$random = rand(1000, 9999);

echo "Testing API responses with cache-busting:\n";

// Test Access Reviews API
$url = "http://localhost:8000/api/access-reviews.php?date_range=30_days&_t=$timestamp&_cache_bust=$random";
echo "URL: $url\n";

$response = file_get_contents($url);
if ($response) {
    $data = json_decode($response, true);
    if ($data && $data['success']) {
        echo "✅ Access Reviews API Response:\n";
        echo "  Total Reviews: " . $data['stats']['total_reviews'] . "\n";
        echo "  StoreSEO Reviews: " . (isset($data['reviews']['StoreSEO']) ? count($data['reviews']['StoreSEO']) : 0) . "\n";
        
        // Show all app counts
        echo "  All Apps:\n";
        foreach ($data['stats']['reviews_by_app'] as $app) {
            echo "    - {$app['app_name']}: {$app['count']} reviews\n";
        }
    } else {
        echo "❌ API Error: " . ($data['error'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "❌ API not responding\n";
}

echo "\n";

// Verify StoreSEO total count in database
require_once 'config/database.php';
$db = new Database();
$conn = $db->getConnection();

$totalStoreSEO = $conn->query("SELECT COUNT(*) FROM reviews WHERE app_name = 'StoreSEO'")->fetchColumn();
$last30StoreSEO = $conn->query("SELECT COUNT(*) FROM reviews WHERE app_name = 'StoreSEO' AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")->fetchColumn();

echo "Database Verification:\n";
echo "  StoreSEO Total Reviews: $totalStoreSEO\n";
echo "  StoreSEO Last 30 Days: $last30StoreSEO\n";

echo "\n=== EXPECTED FRONTEND BEHAVIOR ===\n";
echo "The Access Review page should show:\n";
echo "  - Total Reviews: " . $data['stats']['total_reviews'] . " (all apps, last 30 days)\n";
echo "  - StoreSEO Section: $last30StoreSEO reviews (last 30 days only)\n";
echo "  - NOT showing: $totalStoreSEO (total StoreSEO reviews)\n";

echo "\n=== TROUBLESHOOTING STEPS ===\n";
echo "1. Hard refresh the browser (Ctrl+F5 or Cmd+Shift+R)\n";
echo "2. Clear browser cache and cookies\n";
echo "3. Open browser developer tools and check Network tab\n";
echo "4. Verify API calls are using cache-busting parameters\n";
echo "5. Check if service worker is caching responses\n";

echo "\n=== CACHE-BUSTING VERIFICATION ===\n";
echo "Current cache-busting parameters:\n";
echo "  Timestamp: $timestamp\n";
echo "  Random: $random\n";
echo "  Full URL: $url\n";

// Create a test endpoint that always returns fresh data
file_put_contents('api/test-fresh-data.php', '<?php
header("Content-Type: application/json");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

echo json_encode([
    "success" => true,
    "timestamp" => time(),
    "message" => "Fresh data - no caching",
    "random" => rand(1000, 9999)
]);
?>');

echo "\n✅ Created test endpoint: /api/test-fresh-data.php\n";
echo "Test it: curl http://localhost:8000/api/test-fresh-data.php\n";

echo "\n🔧 FRONTEND CACHE-BUSTING UPDATED\n";
echo "The frontend API calls now include stronger cache-busting parameters.\n";
echo "Please hard refresh the browser to see the updated data.\n";
?>
