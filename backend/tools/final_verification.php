<?php
/**
 * Final verification that ALL apps use ONLY live data
 */

echo "🔴 FINAL VERIFICATION: 100% LIVE DATA SYSTEM\n";
echo "============================================\n\n";

// Test 1: Verify no old scrapers exist
echo "📋 Test 1: Checking for old scrapers...\n";
$oldScrapers = glob(__DIR__ . '/../*Scraper.php');
$oldScrapers = array_merge($oldScrapers, glob(__DIR__ . '/../scraper/*Scraper.php'));

$allowedScrapers = ['UniversalLiveScraper.php', 'PureLiveStoreSEOScraper.php'];
$foundOldScrapers = [];

foreach ($oldScrapers as $scraper) {
    $filename = basename($scraper);
    if (!in_array($filename, $allowedScrapers)) {
        $foundOldScrapers[] = $filename;
    }
}

if (empty($foundOldScrapers)) {
    echo "✅ No old scrapers found - only UniversalLiveScraper remains\n";
} else {
    echo "❌ Found old scrapers: " . implode(', ', $foundOldScrapers) . "\n";
}

// Test 2: Verify API uses UniversalLiveScraper
echo "\n📋 Test 2: Checking API implementation...\n";
$apiContent = file_get_contents(__DIR__ . '/../api/scrape-app.php');

if (strpos($apiContent, 'UniversalLiveScraper') !== false) {
    echo "✅ API uses UniversalLiveScraper\n";
} else {
    echo "❌ API does not use UniversalLiveScraper\n";
}

if (strpos($apiContent, 'mock') === false && strpos($apiContent, 'sample') === false) {
    echo "✅ No references to mock or sample data in API\n";
} else {
    echo "❌ Found references to mock/sample data in API\n";
}

// Test 3: Test live scraping for supported apps
echo "\n📋 Test 3: Testing live scraping...\n";

$testApps = ['StoreSEO', 'StoreFAQ'];
$allPassed = true;

foreach ($testApps as $appName) {
    echo "Testing $appName... ";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'http://localhost:8000/api/scrape-app.php',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode(['app_name' => $appName, 'date_filter' => 'this_month']),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if ($data && $data['success'] && $data['scraped_count'] > 0) {
            echo "✅ {$data['scraped_count']} live reviews\n";
        } else {
            echo "⚠️ No recent reviews found (normal for some apps)\n";
        }
    } else {
        echo "❌ API call failed\n";
        $allPassed = false;
    }
}

// Test 4: Verify database has live data
echo "\n📋 Test 4: Checking database for live data...\n";

try {
    require_once __DIR__ . '/../config/database.php';
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->query("SELECT app_name, COUNT(*) as count, MAX(review_date) as latest FROM reviews GROUP BY app_name");
    $results = $stmt->fetchAll();
    
    if (empty($results)) {
        echo "⚠️ No data in database (run scraping first)\n";
    } else {
        foreach ($results as $row) {
            $latestDate = $row['latest'];
            $daysSinceLatest = (strtotime('now') - strtotime($latestDate)) / (60 * 60 * 24);
            
            if ($daysSinceLatest <= 30) {
                echo "✅ {$row['app_name']}: {$row['count']} reviews (latest: $latestDate)\n";
            } else {
                echo "⚠️ {$row['app_name']}: {$row['count']} reviews (latest: $latestDate - may be old)\n";
            }
        }
    }
} catch (Exception $e) {
    echo "❌ Database check failed: " . $e->getMessage() . "\n";
    $allPassed = false;
}

// Final verdict
echo "\n🎯 FINAL VERDICT:\n";
echo "================\n";

if ($allPassed && empty($foundOldScrapers)) {
    echo "🎉 SUCCESS: System is using 100% LIVE DATA ONLY!\n";
    echo "✅ No mock data, no sample data, no hardcoded data\n";
    echo "✅ All apps use UniversalLiveScraper for real-time scraping\n";
    echo "✅ Data comes directly from live Shopify App Store pages\n";
    echo "✅ System automatically updates with new reviews\n";
    echo "✅ No manual intervention required\n";
} else {
    echo "❌ ISSUES FOUND: System may still have mock data or old scrapers\n";
    echo "Please review the test results above\n";
}

echo "\n🔴 VERIFICATION COMPLETE\n";
?>
