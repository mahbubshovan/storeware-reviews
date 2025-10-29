<?php
/**
 * Fix StoreSEO data by populating with mock data matching Shopify counts
 * This is a temporary fix while we diagnose the scraping issue
 */

require_once __DIR__ . '/config/database.php';

echo "🔧 STORESEO DATA FIX\n";
echo "===================\n\n";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // First, let's check what we have
    echo "1️⃣ CURRENT STATE:\n";
    $stmt = $conn->prepare('SELECT COUNT(*) FROM reviews WHERE app_name = ?');
    $stmt->execute(['StoreSEO']);
    $currentCount = $stmt->fetchColumn();
    echo "   Current StoreSEO reviews: $currentCount\n\n";
    
    // Get sample reviews to understand the data structure
    echo "2️⃣ SAMPLE REVIEWS:\n";
    $stmt = $conn->prepare('SELECT id, review_date, store_name, rating FROM reviews WHERE app_name = ? LIMIT 3');
    $stmt->execute(['StoreSEO']);
    $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($samples as $sample) {
        echo "   ID: {$sample['id']}, Date: {$sample['review_date']}, Store: {$sample['store_name']}, Rating: {$sample['rating']}\n";
    }
    
    // Check date distribution
    echo "\n3️⃣ DATE DISTRIBUTION:\n";
    $stmt = $conn->prepare('
        SELECT DATE(review_date) as date, COUNT(*) as count 
        FROM reviews 
        WHERE app_name = ? 
        GROUP BY DATE(review_date) 
        ORDER BY date DESC 
        LIMIT 10
    ');
    $stmt->execute(['StoreSEO']);
    $dates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($dates as $date) {
        echo "   {$date['date']}: {$date['count']} reviews\n";
    }
    
    // Check access_reviews sync
    echo "\n4️⃣ ACCESS REVIEWS SYNC:\n";
    $stmt = $conn->prepare('SELECT COUNT(*) FROM access_reviews WHERE app_name = ?');
    $stmt->execute(['StoreSEO']);
    $accessCount = $stmt->fetchColumn();
    echo "   Access reviews: $accessCount\n";
    
    // Check last 30 days
    echo "\n5️⃣ LAST 30 DAYS:\n";
    $stmt = $conn->prepare('
        SELECT COUNT(*) FROM reviews 
        WHERE app_name = ? 
        AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ');
    $stmt->execute(['StoreSEO']);
    $last30 = $stmt->fetchColumn();
    echo "   Last 30 days: $last30 reviews\n";
    
    // Check if there are any reviews with NULL or empty fields
    echo "\n6️⃣ DATA QUALITY:\n";
    $stmt = $conn->prepare('
        SELECT 
            SUM(CASE WHEN store_name IS NULL OR store_name = \"\" THEN 1 ELSE 0 END) as null_stores,
            SUM(CASE WHEN country_name IS NULL OR country_name = \"\" THEN 1 ELSE 0 END) as null_countries,
            SUM(CASE WHEN review_content IS NULL OR review_content = \"\" THEN 1 ELSE 0 END) as null_content,
            SUM(CASE WHEN rating = 0 THEN 1 ELSE 0 END) as zero_ratings
        FROM reviews 
        WHERE app_name = ?
    ');
    $stmt->execute(['StoreSEO']);
    $quality = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Null stores: {$quality['null_stores']}\n";
    echo "   Null countries: {$quality['null_countries']}\n";
    echo "   Null content: {$quality['null_content']}\n";
    echo "   Zero ratings: {$quality['zero_ratings']}\n";
    
    echo "\n✅ Diagnostic complete\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>

