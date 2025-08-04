<?php
/**
 * Debug scraper to understand the HTML structure of StoreFAQ reviews
 */

function debugStoreFAQPage($pageNumber = 1) {
    $url = "https://apps.shopify.com/storefaq/reviews?sort_by=newest&page=" . $pageNumber;
    echo "Fetching: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || !$html) {
        echo "❌ Failed to fetch page (HTTP $httpCode)\n";
        return;
    }
    
    // Save full HTML for inspection
    file_put_contents(__DIR__ . "/debug_storefaq_page_{$pageNumber}.html", $html);
    echo "✅ HTML saved to debug_storefaq_page_{$pageNumber}.html\n";
    
    // Try to find review patterns
    echo "\n=== ANALYZING HTML STRUCTURE ===\n";
    
    // Look for common review patterns
    $patterns = [
        'review-listing-review' => '/class="[^"]*review-listing-review[^"]*"/',
        'review-item' => '/class="[^"]*review-item[^"]*"/',
        'review-card' => '/class="[^"]*review-card[^"]*"/',
        'review-content' => '/class="[^"]*review-content[^"]*"/',
        'data-review' => '/data-review[^=]*="[^"]*"/',
        'review class' => '/class="[^"]*review[^"]*"/',
    ];
    
    foreach ($patterns as $name => $pattern) {
        $matches = preg_match_all($pattern, $html, $found);
        echo "$name: $matches matches\n";
        if ($matches > 0 && $matches < 20) {
            echo "  Examples: " . implode(', ', array_slice($found[0], 0, 3)) . "\n";
        }
    }
    
    // Look for JSON data
    echo "\n=== LOOKING FOR JSON DATA ===\n";
    if (preg_match('/window\.__INITIAL_STATE__\s*=\s*({.*?});/', $html, $matches)) {
        echo "Found window.__INITIAL_STATE__\n";
        $jsonData = json_decode($matches[1], true);
        if ($jsonData && isset($jsonData['reviews'])) {
            echo "Reviews found in JSON: " . count($jsonData['reviews']) . "\n";
        }
    }
    
    if (preg_match('/window\.__PRELOADED_STATE__\s*=\s*({.*?});/', $html, $matches)) {
        echo "Found window.__PRELOADED_STATE__\n";
    }
    
    // Look for script tags with review data
    if (preg_match_all('/<script[^>]*>.*?"reviews".*?<\/script>/s', $html, $matches)) {
        echo "Found " . count($matches[0]) . " script tags with 'reviews'\n";
    }
    
    // Extract a small sample of the HTML around reviews
    echo "\n=== HTML SAMPLE ===\n";
    if (preg_match('/(<div[^>]*review[^>]*>.*?<\/div>)/s', $html, $matches)) {
        echo "Sample review HTML:\n";
        echo substr($matches[1], 0, 500) . "...\n";
    }
}

// Debug first two pages
debugStoreFAQPage(1);
echo "\n" . str_repeat("=", 50) . "\n";
debugStoreFAQPage(2);
?>
