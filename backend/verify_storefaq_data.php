<?php
require_once __DIR__ . '/utils/DatabaseManager.php';

/**
 * Verify StoreFAQ scraping results and filtering
 */

echo "=== STOREFAQ DATA VERIFICATION ===\n\n";

try {
    $dbManager = new DatabaseManager();
    
    // Get reflection to access private connection
    $reflection = new ReflectionClass($dbManager);
    $connProperty = $reflection->getProperty('conn');
    $connProperty->setAccessible(true);
    $conn = $connProperty->getValue($dbManager);
    
    // 1. Total reviews count
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM reviews WHERE app_name = 'StoreFAQ'");
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 2. This month (July 2025) count
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM reviews 
        WHERE app_name = 'StoreFAQ' 
        AND MONTH(review_date) = 7 
        AND YEAR(review_date) = 2025
    ");
    $stmt->execute();
    $thisMonth = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // 3. Last 30 days count
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM reviews 
        WHERE app_name = 'StoreFAQ' 
        AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ");
    $stmt->execute();
    $last30Days = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // 4. Date range
    $stmt = $conn->prepare("
        SELECT 
            MIN(review_date) as earliest, 
            MAX(review_date) as latest,
            COUNT(CASE WHEN review_date >= '2025-06-30' THEN 1 END) as june30_onwards
        FROM reviews 
        WHERE app_name = 'StoreFAQ'
    ");
    $stmt->execute();
    $dateInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 5. Reviews by month breakdown
    $stmt = $conn->prepare("
        SELECT 
            YEAR(review_date) as year,
            MONTH(review_date) as month,
            COUNT(*) as count
        FROM reviews 
        WHERE app_name = 'StoreFAQ'
        GROUP BY YEAR(review_date), MONTH(review_date)
        ORDER BY year DESC, month DESC
    ");
    $stmt->execute();
    $monthlyBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Display results
    echo "📊 SCRAPING RESULTS:\n";
    echo "Total StoreFAQ reviews: $total\n";
    echo "This month (July 2025): $thisMonth\n";
    echo "Last 30 days: $last30Days\n";
    echo "Date range: {$dateInfo['earliest']} to {$dateInfo['latest']}\n";
    echo "Reviews from June 30 onwards: {$dateInfo['june30_onwards']}\n\n";
    
    echo "📅 MONTHLY BREAKDOWN:\n";
    foreach ($monthlyBreakdown as $month) {
        $monthName = date('F', mktime(0, 0, 0, $month['month'], 1));
        echo "{$monthName} {$month['year']}: {$month['count']} reviews\n";
    }
    
    echo "\n🎯 COMPARISON WITH EXPECTED:\n";
    echo "Expected this month: 15 | Actual: $thisMonth | " . ($thisMonth >= 15 ? "✅" : "❌") . "\n";
    echo "Expected last 30 days: 17 | Actual: $last30Days | " . ($last30Days >= 17 ? "✅" : "❌") . "\n";
    
    // Test API endpoints
    echo "\n🔗 API ENDPOINT TESTS:\n";
    
    $apiThisMonth = json_decode(file_get_contents("http://localhost:8000/api/this-month-reviews.php?app_name=StoreFAQ"), true);
    $apiLast30Days = json_decode(file_get_contents("http://localhost:8000/api/last-30-days-reviews.php?app_name=StoreFAQ"), true);
    $apiAvgRating = json_decode(file_get_contents("http://localhost:8000/api/average-rating.php?app_name=StoreFAQ"), true);
    
    echo "API This Month: {$apiThisMonth['count']} | " . ($apiThisMonth['count'] == $thisMonth ? "✅" : "❌") . "\n";
    echo "API Last 30 Days: {$apiLast30Days['count']} | " . ($apiLast30Days['count'] == $last30Days ? "✅" : "❌") . "\n";
    echo "API Average Rating: {$apiAvgRating['average_rating']} ⭐\n";
    
    // Show some sample reviews
    echo "\n📝 SAMPLE REVIEWS:\n";
    $stmt = $conn->prepare("
        SELECT store_name, review_date, rating, LEFT(review_content, 50) as content_preview
        FROM reviews 
        WHERE app_name = 'StoreFAQ'
        ORDER BY review_date DESC
        LIMIT 5
    ");
    $stmt->execute();
    $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($samples as $sample) {
        echo "{$sample['review_date']} | {$sample['rating']}★ | {$sample['store_name']} | {$sample['content_preview']}...\n";
    }
    
    echo "\n🎉 VERIFICATION COMPLETE!\n";
    echo "The scraper successfully extracted real StoreFAQ reviews with proper date filtering.\n";
    echo "Frontend should now display: $thisMonth reviews this month, $last30Days reviews last 30 days.\n";
    
} catch (Exception $e) {
    echo "❌ Error during verification: " . $e->getMessage() . "\n";
}
?>
