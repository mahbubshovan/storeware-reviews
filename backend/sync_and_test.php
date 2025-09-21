<?php
require_once 'utils/AccessReviewsSync.php';

echo "Syncing access_reviews table...\n";
$sync = new AccessReviewsSync();
$sync->syncAccessReviews();

echo "Testing API responses...\n";

// Test StoreFAQ
$url = 'http://localhost:8000/api/this-month-reviews.php?app_name=StoreFAQ';
$response = @file_get_contents($url);
if ($response) {
    $data = json_decode($response, true);
    echo "StoreFAQ This Month: " . ($data['count'] ?? 'ERROR') . "\n";
} else {
    echo "StoreFAQ This Month: API ERROR\n";
}

$url = 'http://localhost:8000/api/last-30-days-reviews.php?app_name=StoreFAQ';
$response = @file_get_contents($url);
if ($response) {
    $data = json_decode($response, true);
    echo "StoreFAQ Last 30 Days: " . ($data['count'] ?? 'ERROR') . "\n";
} else {
    echo "StoreFAQ Last 30 Days: API ERROR\n";
}

echo "Done.\n";
?>
