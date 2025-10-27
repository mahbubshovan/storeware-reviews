<?php
/**
 * Sync access_reviews table with recent reviews from reviews table
 * This ensures access_reviews has all reviews from the last 30 days
 */

require_once __DIR__ . '/config/database.php';

try {
    $conn = getDBConnection();
    
    echo "=== SYNCING ACCESS_REVIEWS TABLE ===\n\n";
    
    // Step 1: Get count of reviews in last 30 days
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count FROM reviews 
        WHERE review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ");
    $stmt->execute();
    $recentReviewsCount = $stmt->fetch()['count'];
    echo "1. Recent reviews (last 30 days): $recentReviewsCount\n";
    
    // Step 2: Get count in access_reviews
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM access_reviews");
    $stmt->execute();
    $accessReviewsCount = $stmt->fetch()['count'];
    echo "2. Current access_reviews count: $accessReviewsCount\n\n";
    
    // Step 3: Find reviews in reviews table but not in access_reviews
    $stmt = $conn->prepare("
        SELECT r.id, r.app_name, r.review_date, r.review_content, r.country_name, r.rating
        FROM reviews r
        LEFT JOIN access_reviews ar ON r.id = ar.original_review_id
        WHERE r.review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        AND ar.id IS NULL
        ORDER BY r.review_date DESC
    ");
    $stmt->execute();
    $missingReviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "3. Missing reviews (in reviews but not in access_reviews): " . count($missingReviews) . "\n";
    
    if (!empty($missingReviews)) {
        echo "   Adding missing reviews to access_reviews...\n";
        
        $insertStmt = $conn->prepare("
            INSERT INTO access_reviews (app_name, review_date, review_content, country_name, rating, original_review_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $addedCount = 0;
        foreach ($missingReviews as $review) {
            try {
                $insertStmt->execute([
                    $review['app_name'],
                    $review['review_date'],
                    $review['review_content'],
                    $review['country_name'],
                    $review['rating'],
                    $review['id']
                ]);
                $addedCount++;
            } catch (Exception $e) {
                echo "   ⚠️  Failed to add review ID {$review['id']}: " . $e->getMessage() . "\n";
            }
        }
        
        echo "   ✅ Added $addedCount reviews\n\n";
    }
    
    // Step 4: Verify October 6 review is in access_reviews
    $stmt = $conn->prepare("
        SELECT ar.id, ar.app_name, ar.review_date, ar.review_content, ar.country_name, ar.rating
        FROM access_reviews ar
        WHERE ar.review_date = '2025-10-06'
        AND ar.app_name = 'StoreSEO'
    ");
    $stmt->execute();
    $octReviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "4. October 6, 2025 reviews in access_reviews: " . count($octReviews) . "\n";
    if (!empty($octReviews)) {
        foreach ($octReviews as $review) {
            echo "   ✅ Found: {$review['app_name']} - {$review['review_date']} - {$review['country_name']}\n";
            echo "      Content: " . substr($review['review_content'], 0, 60) . "...\n";
        }
    }
    
    // Step 5: Final counts
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM access_reviews");
    $stmt->execute();
    $finalAccessCount = $stmt->fetch()['count'];
    echo "\n5. Final access_reviews count: $finalAccessCount\n";
    
    echo "\n✅ Sync completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

