<?php
echo "🔍 Testing Rating Distribution Extraction\n";

$url = "https://apps.shopify.com/storeseo/reviews";

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

if ($httpCode !== 200) {
    echo "❌ Failed to fetch page: HTTP $httpCode\n";
    exit;
}

echo "✅ Fetched page successfully\n";

// Save for inspection
file_put_contents('debug_storeseo_ratings.html', $html);
echo "📄 Saved to debug_storeseo_ratings.html\n";

// Look for JSON-LD data
if (preg_match('/"aggregateRating":\s*{([^}]+)}/', $html, $matches)) {
    echo "🎯 Found aggregateRating JSON-LD:\n";
    echo $matches[1] . "\n";
    
    if (preg_match('/"ratingValue":([\d.]+)/', $matches[1], $ratingMatch)) {
        echo "Overall Rating: " . $ratingMatch[1] . "★\n";
    }
    
    if (preg_match('/"ratingCount":(\d+)/', $matches[1], $countMatch)) {
        echo "Total Reviews: " . $countMatch[1] . "\n";
    }
}

// Look for rating distribution numbers
echo "\n🔍 Searching for rating distribution numbers...\n";

// Search for patterns like "293" followed by rating indicators
if (preg_match_all('/(\d+)\s*(?:reviews?|ratings?)/', $html, $matches, PREG_SET_ORDER)) {
    echo "Found review count patterns:\n";
    foreach ($matches as $match) {
        echo "- " . $match[1] . " reviews\n";
    }
}

// Look for star rating patterns
if (preg_match_all('/(\d+)\s*(?:star|★)/', $html, $matches, PREG_SET_ORDER)) {
    echo "\nFound star rating patterns:\n";
    foreach ($matches as $match) {
        echo "- " . $match[1] . " star\n";
    }
}

// Look for the specific rating distribution section
echo "\n🔍 Looking for rating distribution section...\n";

$dom = new DOMDocument();
libxml_use_internal_errors(true);
$dom->loadHTML($html);
libxml_clear_errors();

$xpath = new DOMXPath($dom);

// Look for elements that might contain rating distribution
$ratingElements = $xpath->query('//div[contains(@class, "rating") or contains(text(), "★") or contains(text(), "star")]');

echo "Found " . $ratingElements->length . " potential rating elements\n";

// Look for specific numbers that might be rating counts
$numberElements = $xpath->query('//div[contains(text(), "293") or contains(text(), "518") or contains(text(), "501")]');

echo "Found " . $numberElements->length . " elements with specific numbers\n";

foreach ($numberElements as $element) {
    $text = trim($element->textContent);
    if (!empty($text) && strlen($text) < 100) {
        echo "- Found: '$text'\n";
    }
}

echo "\n✅ Rating extraction test complete\n";
?>
