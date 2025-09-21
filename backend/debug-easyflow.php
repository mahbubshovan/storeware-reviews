<?php
// Debug EasyFlow rating extraction

echo "🔍 DEBUGGING EASYFLOW RATING EXTRACTION\n";
echo "======================================\n";

$url = "https://apps.shopify.com/product-options-4/reviews";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36');
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$html = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || !$html) {
    echo "❌ Failed to fetch page\n";
    exit;
}

$dom = new DOMDocument();
@$dom->loadHTML($html);
$xpath = new DOMXPath($dom);

// Get total reviews
$totalReviews = 0;
if (preg_match('/"ratingCount":(\d+)/', $html, $matches)) {
    $totalReviews = (int)$matches[1];
    echo "📊 Total reviews from JSON-LD: $totalReviews\n\n";
}

// Look for the rating distribution section
echo "🔍 Looking for rating distribution section...\n";

// Find all text nodes that contain rating counts
$allTextNodes = $xpath->query('//text()[normalize-space()]');

$percentages = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
$directCounts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

echo "\n📈 Extracting percentages...\n";
foreach ($allTextNodes as $textNode) {
    $text = trim($textNode->textContent);
    
    if (preg_match('/(\d+)%\s+of\s+ratings\s+are\s+(\d+)\s+star/i', $text, $matches)) {
        $percentage = (int)$matches[1];
        $rating = (int)$matches[2];
        
        echo "   Found: $percentage% are $rating stars\n";
        
        if ($rating >= 1 && $rating <= 5) {
            $percentages[$rating] = $percentage;
        }
    }
}

echo "\n🔢 Looking for direct counts in rating distribution...\n";

// Look for the specific structure with actual counts
// The HTML shows: 5 star rating, then "307" as the count
$ratingCountPattern = '/(\d+)\s*\n\s*(\d+)/';
if (preg_match_all($ratingCountPattern, $html, $matches, PREG_SET_ORDER)) {
    foreach ($matches as $match) {
        $rating = (int)$match[1];
        $count = (int)$match[2];
        
        if ($rating >= 1 && $rating <= 5 && $count > 0 && $count <= $totalReviews) {
            echo "   Found direct count: $rating stars = $count reviews\n";
            $directCounts[$rating] = $count;
        }
    }
}

// Alternative approach: look for the specific HTML structure
echo "\n🎯 Looking for specific count elements...\n";

// Look for elements that contain just numbers and might be counts
$numberElements = $xpath->query('//text()[normalize-space() and string-length(normalize-space()) < 5 and number(normalize-space()) = normalize-space()]');

$foundCounts = [];
foreach ($numberElements as $element) {
    $number = trim($element->textContent);
    
    if (is_numeric($number) && $number > 0 && $number <= $totalReviews) {
        // Check the context to see if this is a rating count
        $parent = $element->parentNode;
        $context = '';
        
        // Get surrounding text context
        for ($i = 0; $i < 3; $i++) {
            if ($parent) {
                $context .= $parent->textContent . ' ';
                $parent = $parent->parentNode;
            }
        }
        
        // Check if context contains rating indicators
        if (preg_match('/[1-5]\s*star|rating|review/i', $context)) {
            echo "   Number: $number, Context: " . substr($context, 0, 100) . "...\n";
            $foundCounts[] = (int)$number;
        }
    }
}

// Try to extract from the specific HTML pattern we saw
echo "\n🔍 Looking for specific HTML patterns...\n";

// Look for the pattern where rating number is followed by count
if (preg_match_all('/>\s*([1-5])\s*<.*?>\s*(\d+)\s*</s', $html, $matches, PREG_SET_ORDER)) {
    foreach ($matches as $match) {
        $rating = (int)$match[1];
        $count = (int)$match[2];
        
        if ($count > 0 && $count <= $totalReviews) {
            echo "   HTML pattern: $rating stars = $count reviews\n";
            $directCounts[$rating] = $count;
        }
    }
}

echo "\n📊 FINAL RESULTS:\n";
echo "Percentages found:\n";
foreach ($percentages as $rating => $percentage) {
    echo "   $rating★: $percentage%\n";
}

echo "\nDirect counts found:\n";
foreach ($directCounts as $rating => $count) {
    echo "   $rating★: $count reviews\n";
}

echo "\nCalculated from percentages:\n";
foreach ($percentages as $rating => $percentage) {
    $calculated = round(($percentage / 100) * $totalReviews);
    echo "   $rating★: $calculated reviews (from $percentage%)\n";
}

// Final distribution
$finalDistribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

foreach ($percentages as $rating => $percentage) {
    $calculated = round(($percentage / 100) * $totalReviews);
    $finalDistribution[$rating] = $calculated;
    
    // Override with direct count if available and different
    if ($directCounts[$rating] > 0 && $directCounts[$rating] != $calculated) {
        $finalDistribution[$rating] = $directCounts[$rating];
    }
}

echo "\n🎯 FINAL DISTRIBUTION:\n";
$total = array_sum($finalDistribution);
foreach ($finalDistribution as $rating => $count) {
    $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
    echo "   $rating★: $count reviews ($percentage%)\n";
}

echo "\nTotal: $total reviews\n";
?>
