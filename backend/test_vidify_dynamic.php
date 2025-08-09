<?php
require_once __DIR__ . '/VidifyDynamicScraper.php';

echo "=== TESTING VIDIFY DYNAMIC SCRAPER ===\n\n";

// Test the new dynamic scraper with REAL DATES
$scraper = new VidifyDynamicScraper();
$result = $scraper->scrapeRealtimeReviews(true);

echo "\n=== SCRAPING RESULT ===\n";
if ($result) {
    echo "Total Stored: " . $result['total_stored'] . "\n";
    echo "This Month: " . $result['this_month'] . "\n";
    echo "Last 30 Days: " . $result['last_30_days'] . "\n";
    echo "New Reviews Count: " . $result['new_reviews_count'] . "\n";
    
    if (isset($result['date_range'])) {
        echo "Date Range: " . $result['date_range']['min_date'] . " to " . $result['date_range']['max_date'] . "\n";
    }
} else {
    echo "Scraping failed or returned no data.\n";
}

echo "\n=== VERIFICATION ===\n";

// Check what's actually in the database
require_once __DIR__ . '/utils/DatabaseManager.php';
$db = new DatabaseManager();
$conn = $db->getConnection();

// Check reviews
$stmt = $conn->prepare('SELECT store_name, review_date, LEFT(review_content, 50) as content_preview FROM reviews WHERE app_name = "Vidify" ORDER BY review_date DESC');
$stmt->execute();
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Reviews in database:\n";
foreach ($reviews as $review) {
    echo "- {$review['store_name']} | {$review['review_date']} | {$review['content_preview']}...\n";
}

// Check metadata
$stmt = $conn->prepare('SELECT * FROM app_metadata WHERE app_name = "Vidify"');
$stmt->execute();
$metadata = $stmt->fetch(PDO::FETCH_ASSOC);

if ($metadata) {
    echo "\nMetadata:\n";
    echo "- Total reviews: {$metadata['total_reviews']}\n";
    echo "- Average rating: {$metadata['overall_rating']}\n";
    echo "- 5-star: {$metadata['five_star_total']}\n";
    echo "- Last updated: {$metadata['last_updated']}\n";
}

echo "\n=== TEST COMPLETED ===\n";
?>
