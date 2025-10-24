<?php
/**
 * Verify Live StoreSEO Review Count
 * 
 * This script fetches the live Shopify page and extracts the exact review count
 * shown on the page to understand the discrepancy.
 */

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║     VERIFY LIVE STORESEO REVIEW COUNT                      ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";

// Fetch the live page
echo "\n🌐 Fetching live Shopify page...\n";

$url = "https://apps.shopify.com/storeseo/reviews";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
    CURLOPT_SSL_VERIFYPEER => false,
]);

$html = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || !$html) {
    echo "❌ Failed to fetch page (HTTP $httpCode)\n";
    exit(1);
}

echo "✅ Page fetched successfully\n";

// Extract total count from JSON-LD
echo "\n📊 Extracting review count from page...\n";

$totalCount = 0;

// Method 1: JSON-LD ratingCount
if (preg_match('/"ratingCount":(\d+)/', $html, $matches)) {
    $totalCount = intval($matches[1]);
    echo "✅ Found via JSON-LD: $totalCount reviews\n";
}

// Method 2: Look for review count in page text
if (preg_match('/(\d+)\s+reviews?/i', $html, $matches)) {
    $textCount = intval($matches[1]);
    echo "✅ Found in page text: $textCount reviews\n";
}

// Method 3: Count actual review elements on page 1
$dom = new DOMDocument();
libxml_use_internal_errors(true);
$dom->loadHTML($html);
libxml_clear_errors();

$xpath = new DOMXPath($dom);
$reviewNodes = $xpath->query('//div[@data-review-content-id]');
$page1Count = $reviewNodes->length;

echo "✅ Reviews on page 1: $page1Count reviews\n";

// Extract rating distribution
echo "\n📈 Rating Distribution:\n";

if (preg_match_all('/"ratingValue":([0-9.]+)/', $html, $matches)) {
    echo "   Overall rating: " . $matches[1][0] . "\n";
}

// Look for star counts
if (preg_match_all('/(\d+)%\s+of\s+ratings\s+are\s+(\d+)\s+star/i', $html, $matches)) {
    for ($i = 0; $i < count($matches[0]); $i++) {
        echo "   " . $matches[2][$i] . "★: " . $matches[1][$i] . "%\n";
    }
}

echo "\n📌 Summary:\n";
echo "   Live page shows: $totalCount total reviews\n";
echo "   Page 1 has: $page1Count reviews\n";
echo "   Expected pages: " . ceil($totalCount / 10) . " pages\n";

echo "\n✅ VERIFICATION COMPLETE\n\n";
?>

