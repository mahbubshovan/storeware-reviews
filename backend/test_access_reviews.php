<?php
require_once __DIR__ . '/utils/AccessReviewsSync.php';

echo "=== TESTING ACCESS REVIEWS FUNCTIONALITY ===\n\n";

$sync = new AccessReviewsSync();

// Test 1: Sync access reviews
echo "1. Testing sync functionality...\n";
$sync->syncAccessReviews();

// Test 2: Get access reviews
echo "\n2. Testing get access reviews...\n";
$reviews = $sync->getAccessReviews();
echo "Found reviews for " . count($reviews) . " apps:\n";
foreach ($reviews as $appName => $appReviews) {
    echo "  - $appName: " . count($appReviews) . " reviews\n";
}

// Test 3: Get stats
echo "\n3. Testing stats...\n";
$stats = $sync->getAccessReviewsStats();
if ($stats) {
    echo "Total reviews: " . $stats['total_reviews'] . "\n";
    echo "Assigned reviews: " . $stats['assigned_reviews'] . "\n";
    echo "Unassigned reviews: " . $stats['unassigned_reviews'] . "\n";
    echo "Apps with reviews: " . count($stats['reviews_by_app']) . "\n";
}

// Test 4: Update earned_by field
echo "\n4. Testing update earned_by...\n";
if (!empty($reviews)) {
    $firstApp = array_keys($reviews)[0];
    $firstReview = $reviews[$firstApp][0];
    $reviewId = $firstReview['id'];
    
    echo "Updating review ID $reviewId with earned_by 'Test User'...\n";
    $result = $sync->updateEarnedBy($reviewId, 'Test User');
    
    if ($result['success']) {
        echo "✅ Update successful: " . $result['message'] . "\n";
        
        // Verify the update
        $updatedReviews = $sync->getAccessReviews();
        $updatedReview = null;
        foreach ($updatedReviews as $appReviews) {
            foreach ($appReviews as $review) {
                if ($review['id'] == $reviewId) {
                    $updatedReview = $review;
                    break 2;
                }
            }
        }
        
        if ($updatedReview && $updatedReview['earned_by'] === 'Test User') {
            echo "✅ Verification successful: earned_by field updated correctly\n";
        } else {
            echo "❌ Verification failed: earned_by field not updated\n";
        }
    } else {
        echo "❌ Update failed: " . $result['message'] . "\n";
    }
}

echo "\n=== ACCESS REVIEWS TEST COMPLETED ===\n";
?>
