<?php
/**
 * Test all rating-related APIs to ensure they use live scraped data
 */

echo "🔍 TESTING RATING DISTRIBUTION APIS WITH LIVE DATA\n";
echo "=================================================\n\n";

$apps = ['StoreSEO', 'StoreFAQ', 'TrustSync', 'EasyFlow', 'BetterDocs FAQ', 'Vidify'];

foreach ($apps as $appName) {
    echo "📱 Testing $appName...\n";
    echo str_repeat('-', 40) . "\n";
    
    // Test review distribution
    $encodedAppName = urlencode($appName);
    $distributionUrl = "http://localhost:8000/api/review-distribution.php?app_name=$encodedAppName";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $distributionUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if ($data && $data['success']) {
            $dist = $data['distribution'];
            $total = $data['total_reviews'];
            
            echo "✅ Rating Distribution:\n";
            echo "   Total Reviews: $total\n";
            echo "   5★: {$dist['five_star']}\n";
            echo "   4★: {$dist['four_star']}\n";
            echo "   3★: {$dist['three_star']}\n";
            echo "   2★: {$dist['two_star']}\n";
            echo "   1★: {$dist['one_star']}\n";
            
            // Verify totals match
            $sum = $dist['five_star'] + $dist['four_star'] + $dist['three_star'] + $dist['two_star'] + $dist['one_star'];
            if ($sum === $total) {
                echo "✅ Distribution totals match\n";
            } else {
                echo "❌ Distribution totals don't match: $sum vs $total\n";
            }
        } else {
            echo "❌ Distribution API failed\n";
        }
    } else {
        echo "❌ Distribution API HTTP error: $httpCode\n";
    }
    
    // Test average rating
    $avgUrl = "http://localhost:8000/api/average-rating.php?app_name=$encodedAppName";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $avgUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if ($data && $data['success']) {
            $avgRating = $data['average_rating'];
            echo "✅ Average Rating: $avgRating\n";
            
            // Verify average makes sense
            if ($avgRating >= 1.0 && $avgRating <= 5.0) {
                echo "✅ Average rating is valid\n";
            } else {
                echo "❌ Average rating seems invalid: $avgRating\n";
            }
        } else {
            echo "❌ Average rating API failed\n";
        }
    } else {
        echo "❌ Average rating API HTTP error: $httpCode\n";
    }
    
    echo "\n";
}

echo "🔍 TESTING DIRECT DATABASE QUERIES...\n";
echo "=====================================\n";

// Test direct database queries to verify data integrity
require_once __DIR__ . '/../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->query("
        SELECT 
            app_name,
            COUNT(*) as total_reviews,
            AVG(rating) as avg_rating,
            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
        FROM reviews 
        GROUP BY app_name 
        ORDER BY total_reviews DESC
    ");
    
    $results = $stmt->fetchAll();
    
    echo "📊 DIRECT DATABASE RATING DISTRIBUTION:\n";
    foreach ($results as $row) {
        $appName = $row['app_name'];
        $total = $row['total_reviews'];
        $avg = round($row['avg_rating'], 1);
        
        echo "\n📱 $appName:\n";
        echo "   Total: $total reviews\n";
        echo "   Average: $avg★\n";
        echo "   5★: {$row['five_star']} | 4★: {$row['four_star']} | 3★: {$row['three_star']} | 2★: {$row['two_star']} | 1★: {$row['one_star']}\n";
        
        // Verify distribution adds up
        $sum = $row['five_star'] + $row['four_star'] + $row['three_star'] + $row['two_star'] + $row['one_star'];
        if ($sum == $total) {
            echo "   ✅ Distribution verified\n";
        } else {
            echo "   ❌ Distribution error: $sum vs $total\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Database query failed: " . $e->getMessage() . "\n";
}

echo "\n🎯 RATING DISTRIBUTION TEST COMPLETE\n";
?>
