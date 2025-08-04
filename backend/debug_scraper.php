<?php
require_once __DIR__ . '/scraper/ShopifyScraper.php';

echo "=== Debug Scraper ===\n";

$scraper = new ShopifyScraper();
$url = "https://apps.shopify.com/storeseo/reviews?sort_by=newest&page=1";

echo "Fetching URL: $url\n";

// Test the fetch method
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER => [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.5',
        'Accept-Encoding: gzip, deflate',
        'Connection: keep-alive',
    ]
]);

$html = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "cURL Error: " . ($error ?: 'None') . "\n";
echo "HTML Length: " . strlen($html) . " characters\n";

if ($html) {
    // Save HTML to file for inspection
    file_put_contents(__DIR__ . '/debug_html.html', $html);
    echo "HTML saved to debug_html.html\n";
    
    // Look for common review patterns
    $patterns = [
        'review-listing',
        'review-item',
        'review',
        'ui-review',
        'data-testid',
        'merchant',
        'store',
        'rating',
        'star'
    ];
    
    foreach ($patterns as $pattern) {
        $count = substr_count(strtolower($html), strtolower($pattern));
        echo "Pattern '$pattern': $count occurrences\n";
    }
    
    // Check if it's a JavaScript-heavy page
    $jsCount = substr_count($html, '<script');
    echo "Script tags: $jsCount\n";
    
    // Look for JSON data
    if (preg_match('/window\.__INITIAL_STATE__\s*=\s*({.*?});/s', $html, $matches)) {
        echo "Found initial state JSON data\n";
        file_put_contents(__DIR__ . '/debug_json.json', $matches[1]);
    }
    
    if (preg_match('/window\.__APP_DATA__\s*=\s*({.*?});/s', $html, $matches)) {
        echo "Found app data JSON\n";
        file_put_contents(__DIR__ . '/debug_app_data.json', $matches[1]);
    }
}

echo "\n=== Debug Complete ===\n";
?>
