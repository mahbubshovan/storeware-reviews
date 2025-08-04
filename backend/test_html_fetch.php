<?php
// Test script to fetch and examine HTML from StoreSEO reviews page

$url = 'https://apps.shopify.com/storeseo/reviews?sort_by=newest&page=1';
$userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';

echo "Fetching HTML from: $url\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
    'Accept-Language: en-US,en;q=0.5',
    'Accept-Encoding: gzip, deflate',
    'Connection: keep-alive',
    'Upgrade-Insecure-Requests: 1',
]);
curl_setopt($ch, CURLOPT_ENCODING, ''); // Enable automatic decompression

$html = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "HTML Length: " . strlen($html) . " bytes\n";

if ($error) {
    echo "cURL Error: $error\n";
}

if ($httpCode == 200 && $html) {
    // Save the HTML to a file for inspection
    file_put_contents('fresh_storeseo_html.html', $html);
    echo "HTML saved to fresh_storeseo_html.html\n";
    
    // Look for review-related patterns
    echo "\n=== SEARCHING FOR REVIEW PATTERNS ===\n";
    
    // Check for common review selectors
    $patterns = [
        'review-listing' => '/review-listing/i',
        'review-item' => '/review-item/i', 
        'review-card' => '/review-card/i',
        'review-content' => '/review-content/i',
        'rating' => '/rating/i',
        'stars' => '/stars/i',
        'review-date' => '/review-date/i',
        'reviewer' => '/reviewer/i',
        'store-name' => '/store-name/i',
        'data-review' => '/data-review/i',
        'review-text' => '/review-text/i'
    ];
    
    foreach ($patterns as $name => $pattern) {
        $matches = preg_match_all($pattern, $html, $found);
        echo "$name: $matches matches\n";
    }
    
    // Look for JSON data
    echo "\n=== SEARCHING FOR JSON DATA ===\n";
    if (preg_match('/window\.__INITIAL_STATE__\s*=\s*({.*?});/s', $html, $matches)) {
        echo "Found window.__INITIAL_STATE__ JSON data\n";
        file_put_contents('storeseo_initial_state.json', $matches[1]);
        echo "JSON data saved to storeseo_initial_state.json\n";
    } else {
        echo "No window.__INITIAL_STATE__ found\n";
    }
    
    if (preg_match('/window\.__PRELOADED_STATE__\s*=\s*({.*?});/s', $html, $matches)) {
        echo "Found window.__PRELOADED_STATE__ JSON data\n";
        file_put_contents('storeseo_preloaded_state.json', $matches[1]);
        echo "JSON data saved to storeseo_preloaded_state.json\n";
    } else {
        echo "No window.__PRELOADED_STATE__ found\n";
    }
    
    // Look for any JSON containing review data
    if (preg_match_all('/{[^{}]*"review[^{}]*}/i', $html, $matches)) {
        echo "Found " . count($matches[0]) . " potential review JSON objects\n";
        foreach (array_slice($matches[0], 0, 3) as $i => $match) {
            echo "Sample " . ($i+1) . ": " . substr($match, 0, 100) . "...\n";
        }
    }
    
} else {
    echo "Failed to fetch HTML\n";
}
?>
