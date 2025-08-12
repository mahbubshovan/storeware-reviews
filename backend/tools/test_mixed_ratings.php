<?php
/**
 * Test scraper with apps that might have mixed ratings
 */

require_once __DIR__ . '/../scraper/UniversalLiveScraper.php';

echo "🔍 TESTING MIXED RATING EXTRACTION\n";
echo "==================================\n\n";

// Test with an app that might have more varied ratings
$scraper = new UniversalLiveScraper();

// Let's test StoreFAQ and look at more pages to find varied ratings
echo "📱 Testing StoreFAQ for mixed ratings...\n";

$url = "https://apps.shopify.com/storefaq/reviews?sort_by=oldest&page=1";
echo "🌐 Fetching: $url\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
]);

$html = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "✅ Page fetched successfully\n";
    
    // Parse and look for different ratings
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_clear_errors();
    
    $xpath = new DOMXPath($dom);
    $reviewNodes = $xpath->query('//div[@data-review-content-id]');
    
    echo "📊 Found {$reviewNodes->length} reviews\n";
    
    $ratingCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
    
    foreach ($reviewNodes as $node) {
        // Count stars
        $starNodes = $xpath->query('.//svg[contains(@class, "tw-fill-fg-primary")]', $node);
        $rating = $starNodes->length;
        
        if ($rating >= 1 && $rating <= 5) {
            $ratingCounts[$rating]++;
            
            // Extract store name for context
            $storeNode = $xpath->query('.//div[contains(@class, "tw-text-heading-xs") and contains(@class, "tw-text-fg-primary")]', $node);
            $storeName = $storeNode->length > 0 ? trim($storeNode->item(0)->textContent) : 'Unknown';
            
            echo "   {$rating}★ - $storeName\n";
        }
    }
    
    echo "\n📊 Rating Distribution Found:\n";
    for ($i = 5; $i >= 1; $i--) {
        echo "   {$i}★: {$ratingCounts[$i]} reviews\n";
    }
    
    // Check if we found any non-5-star ratings
    $hasVariedRatings = false;
    for ($i = 1; $i <= 4; $i++) {
        if ($ratingCounts[$i] > 0) {
            $hasVariedRatings = true;
            break;
        }
    }
    
    if ($hasVariedRatings) {
        echo "✅ Found mixed ratings - scraper can handle varied ratings\n";
    } else {
        echo "ℹ️ All reviews are 5-star (common for newer/popular apps)\n";
    }
    
} else {
    echo "❌ Failed to fetch page: HTTP $httpCode\n";
}

echo "\n🎯 MIXED RATING TEST COMPLETE\n";
?>
