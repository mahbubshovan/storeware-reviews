<?php
// Test script to fetch StoreSEO page 1 with newest reviews
echo "🔍 Testing StoreSEO Page 1 Fetch...\n";

$url = "https://apps.shopify.com/storeseo/reviews?sort_by=newest&page=1";
echo "URL: $url\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
    CURLOPT_HTTPHEADER => [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.5',
        'Connection: keep-alive',
        'Upgrade-Insecure-Requests: 1',
    ],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
]);

$html = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_error($ch)) {
    echo "❌ cURL Error: " . curl_error($ch) . "\n";
    curl_close($ch);
    exit;
}

curl_close($ch);

if ($httpCode !== 200) {
    echo "❌ HTTP Error: $httpCode\n";
    exit;
}

echo "✅ Fetched page successfully (" . strlen($html) . " bytes)\n";

// Save HTML for inspection
file_put_contents('debug_storeseo_page1.html', $html);
echo "📄 Saved to debug_storeseo_page1.html\n";

// Search for August 11 reviews
if (strpos($html, 'August 11') !== false) {
    echo "🎯 Found 'August 11' in the HTML!\n";
} else {
    echo "❌ 'August 11' not found in HTML\n";
}

// Search for the store names
if (strpos($html, 'sevengardens') !== false) {
    echo "🎯 Found 'sevengardens' in the HTML!\n";
} else {
    echo "❌ 'sevengardens' not found in HTML\n";
}

if (strpos($html, 'RawSpiceBar') !== false) {
    echo "🎯 Found 'RawSpiceBar' in the HTML!\n";
} else {
    echo "❌ 'RawSpiceBar' not found in HTML\n";
}

// Check canonical URL to confirm we got page 1
if (preg_match('/canonical.*?href="([^"]*)"/', $html, $matches)) {
    echo "📍 Canonical URL: " . $matches[1] . "\n";
}

echo "✅ Test complete\n";
?>
