<?php
require_once __DIR__ . '/config/database.php';

echo "🔴 LIVE SCRAPING BETTERDOCS REVIEWS\n";
echo "====================================\n";

$url = "https://apps.shopify.com/betterdocs-knowledgebase/reviews?sort_by=newest";

// Fetch the page
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

// Save HTML for debugging
file_put_contents('debug_betterdocs.html', $html);
echo "📄 Saved HTML to debug_betterdocs.html\n";

// Parse HTML
$dom = new DOMDocument();
@$dom->loadHTML($html);
$xpath = new DOMXPath($dom);

// Find review nodes - look for containers with review content
$reviewNodes = $xpath->query('//p[@class="tw-break-words"]');
echo "📝 Found " . $reviewNodes->length . " review content nodes\n";

$reviews = [];
foreach ($reviewNodes as $contentNode) {
    $content = trim($contentNode->textContent);
    if (empty($content)) continue;

    // Find the parent container that holds the entire review
    $reviewContainer = $contentNode;
    for ($i = 0; $i < 10; $i++) {
        $reviewContainer = $reviewContainer->parentNode;
        if (!$reviewContainer) break;

        // Look for store name in this container
        $storeNameNodes = $xpath->query('.//div[@class="tw-text-heading-xs tw-text-fg-primary tw-overflow-hidden tw-text-ellipsis tw-whitespace-nowrap"]', $reviewContainer);
        if ($storeNameNodes->length > 0) {
            break; // Found the right container
        }
    }

    if (!$reviewContainer) continue;

    // Extract rating by counting filled stars in this container
    $starNodes = $xpath->query('.//svg[@class="tw-fill-fg-primary tw-w-md tw-h-md"]', $reviewContainer);
    $rating = $starNodes->length;

    // Extract store name
    $storeNameNodes = $xpath->query('.//div[@class="tw-text-heading-xs tw-text-fg-primary tw-overflow-hidden tw-text-ellipsis tw-whitespace-nowrap"]', $reviewContainer);
    $storeName = $storeNameNodes->length > 0 ? trim($storeNameNodes->item(0)->textContent) : 'Unknown Store';

    // Extract date
    $dateNodes = $xpath->query('.//div[contains(@class, "tw-text-body-xs") and contains(@class, "tw-text-fg-tertiary")]', $reviewContainer);
    $reviewDate = '';
    foreach ($dateNodes as $dateNode) {
        $dateText = trim($dateNode->textContent);
        if (preg_match('/\w+ \d{1,2}, \d{4}/', $dateText)) {
            $reviewDate = date('Y-m-d', strtotime($dateText));
            break;
        }
    }
    
    if ($rating > 0 && !empty($content) && !empty($storeName)) {
        $reviews[] = [
            'store_name' => $storeName,
            'rating' => $rating,
            'content' => $content,
            'date' => $reviewDate ?: date('Y-m-d')
        ];
        
        echo "⭐ {$rating}★ - {$storeName}: " . substr($content, 0, 50) . "...\n";
    }
}

echo "\n📊 EXTRACTED " . count($reviews) . " REVIEWS:\n";
foreach ($reviews as $review) {
    echo "🏪 {$review['store_name']}: {$review['rating']}★\n";
}

// Update database with correct ratings
$db = new Database();
$conn = $db->getConnection();

foreach ($reviews as $review) {
    $stmt = $conn->prepare("UPDATE reviews SET rating = ? WHERE store_name = ? AND app_name = 'BetterDocs FAQ'");
    $stmt->bind_param("is", $review['rating'], $review['store_name']);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo "✅ Updated {$review['store_name']} to {$review['rating']}★\n";
    }
}

// Also update access_reviews
$stmt = $conn->prepare("
    UPDATE access_reviews ar 
    JOIN reviews r ON ar.original_review_id = r.id 
    SET ar.rating = r.rating 
    WHERE r.app_name = 'BetterDocs FAQ'
");
$stmt->execute();

echo "✅ Updated access_reviews table\n";
echo "🎉 DONE!\n";
?>
