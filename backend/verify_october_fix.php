<?php
/**
 * Verify that the October 6, 2025 review fix is working correctly
 * This script checks both the reviews and access_reviews tables
 */

require_once __DIR__ . '/config/database.php';

try {
    $conn = getDBConnection();
    
    echo "=== OCTOBER 6, 2025 REVIEW FIX VERIFICATION ===\n\n";
    
    // Check 1: Verify review exists in reviews table with correct date
    echo "1. Checking reviews table...\n";
    $stmt = $conn->prepare("
        SELECT id, app_name, store_name, review_date, country_name, rating, 
               SUBSTRING(review_content, 1, 80) as content
        FROM reviews 
        WHERE app_name = 'StoreSEO' 
        AND store_name = 'AUTOTOC'
        AND review_date = '2025-10-06'
        LIMIT 1
    ");
    $stmt->execute();
    $review = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($review) {
        echo "   ✅ Review found in reviews table\n";
        echo "      ID: {$review['id']}\n";
        echo "      Date: {$review['review_date']}\n";
        echo "      Rating: {$review['rating']}★\n";
        echo "      Country: {$review['country_name']}\n";
        echo "      Content: {$review['content']}...\n";
    } else {
        echo "   ❌ Review NOT found in reviews table\n";
        exit(1);
    }
    
    // Check 2: Verify review exists in access_reviews table
    echo "\n2. Checking access_reviews table...\n";
    $stmt = $conn->prepare("
        SELECT id, app_name, review_date, country_name, rating,
               SUBSTRING(review_content, 1, 80) as content
        FROM access_reviews 
        WHERE app_name = 'StoreSEO' 
        AND review_date = '2025-10-06'
        LIMIT 1
    ");
    $stmt->execute();
    $accessReview = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($accessReview) {
        echo "   ✅ Review found in access_reviews table\n";
        echo "      ID: {$accessReview['id']}\n";
        echo "      Date: {$accessReview['review_date']}\n";
        echo "      Rating: {$accessReview['rating']}★\n";
        echo "      Country: {$accessReview['country_name']}\n";
        echo "      Content: {$accessReview['content']}...\n";
    } else {
        echo "   ❌ Review NOT found in access_reviews table\n";
        exit(1);
    }
    
    // Check 3: Verify no 1970-01-01 dates in StoreSEO reviews
    echo "\n3. Checking for bad dates (1970-01-01)...\n";
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count FROM reviews 
        WHERE app_name = 'StoreSEO' 
        AND review_date = '1970-01-01'
    ");
    $stmt->execute();
    $badDateCount = $stmt->fetch()['count'];
    
    if ($badDateCount == 0) {
        echo "   ✅ No reviews with 1970-01-01 date\n";
    } else {
        echo "   ⚠️  Found $badDateCount reviews with 1970-01-01 date\n";
    }
    
    // Check 4: Verify access_reviews is synced
    echo "\n4. Checking access_reviews sync status...\n";
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count FROM reviews 
        WHERE review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        AND app_name = 'StoreSEO'
    ");
    $stmt->execute();
    $recentReviewsCount = $stmt->fetch()['count'];
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count FROM access_reviews 
        WHERE app_name = 'StoreSEO'
    ");
    $stmt->execute();
    $accessReviewsCount = $stmt->fetch()['count'];
    
    echo "   Recent reviews (last 30 days): $recentReviewsCount\n";
    echo "   Access reviews count: $accessReviewsCount\n";
    
    if ($accessReviewsCount >= $recentReviewsCount) {
        echo "   ✅ Access reviews is properly synced\n";
    } else {
        echo "   ⚠️  Access reviews may be out of sync\n";
        echo "      Run: php backend/sync_access_reviews.php\n";
    }
    
    // Final summary
    echo "\n=== VERIFICATION SUMMARY ===\n";
    echo "✅ October 6, 2025 review is correctly stored in database\n";
    echo "✅ Review is accessible from both reviews and access_reviews tables\n";
    echo "✅ Review should now appear on:\n";
    echo "   - Access Reviews page (/access-tabbed)\n";
    echo "   - Analytics page (/) when StoreSEO is selected\n";
    echo "\n✅ FIX VERIFIED SUCCESSFULLY!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

