<?php
/**
 * Test if we can fetch Shopify pages
 */

echo "Testing Shopify page fetch...\n\n";

$url = 'https://apps.shopify.com/storeseo/reviews?sort_by=newest&page=1';
echo "URL: $url\n\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    CURLOPT_HTTPHEADER => [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.5',
        'Connection: keep-alive',
    ],
    CURLOPT_SSL_VERIFYPEER => false,
]);

$html = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Error: " . ($error ?: "None") . "\n";
echo "HTML Length: " . strlen($html) . " bytes\n\n";

if ($httpCode === 200 && !empty($html)) {
    echo "✅ Successfully fetched page\n";
    
    // Check for review containers
    if (preg_match_all('/data-review-content-id/', $html, $matches)) {
        echo "✅ Found " . count($matches[0]) . " review containers\n";
    } else {
        echo "⚠️ No review containers found\n";
    }
    
    // Check for rating count
    if (preg_match('/"ratingCount":(\d+)/', $html, $matches)) {
        echo "✅ Total reviews on Shopify: " . $matches[1] . "\n";
    } else {
        echo "⚠️ Could not extract total review count\n";
    }
} else {
    echo "❌ Failed to fetch page\n";
}
?>

