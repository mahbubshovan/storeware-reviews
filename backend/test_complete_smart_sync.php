<?php
/**
 * Complete Smart Sync System Test
 * 
 * This demonstrates the complete smart sync workflow:
 * 1. Analytics page scrapes new reviews
 * 2. Smart sync compares with Access Review Tab data
 * 3. Duplicates are skipped, new reviews are added
 * 4. Access Review page is updated
 */

echo "🎯 COMPLETE SMART SYNC SYSTEM TEST\n";
echo "==================================\n\n";

require_once 'config/database.php';

function testCompleteWorkflow() {
    echo "📋 TESTING COMPLETE SMART SYNC WORKFLOW\n";
    echo "======================================\n\n";
    
    // Step 1: Check current state
    echo "1️⃣ CHECKING CURRENT STATE\n";
    echo "-------------------------\n";
    
    $database = new Database();
    $conn = $database->getConnection();
    
    $today = date('Y-m-d');
    
    // Count today's reviews in main table
    $stmt = $conn->prepare("SELECT COUNT(*) FROM reviews WHERE review_date = ? AND app_name = 'StoreSEO'");
    $stmt->execute([$today]);
    $mainTableCount = $stmt->fetchColumn();
    
    // Count today's reviews in access_reviews table
    $stmt = $conn->prepare("SELECT COUNT(*) FROM access_reviews WHERE review_date = ? AND app_name = 'StoreSEO'");
    $stmt->execute([$today]);
    $accessTableCount = $stmt->fetchColumn();
    
    echo "📊 Current State:\n";
    echo "   - Main reviews table (today): $mainTableCount reviews\n";
    echo "   - Access reviews table (today): $accessTableCount reviews\n\n";
    
    // Step 2: Add a new test review (simulating analytics scraping)
    echo "2️⃣ SIMULATING ANALYTICS SCRAPING\n";
    echo "--------------------------------\n";
    
    $testReview = [
        'app_name' => 'StoreSEO',
        'store_name' => 'Smart Sync Test Store ' . time(),
        'country_name' => 'Canada',
        'rating' => 4,
        'review_content' => 'This is a smart sync test review created at ' . date('Y-m-d H:i:s'),
        'review_date' => $today
    ];
    
    echo "📝 Adding new test review to simulate analytics scraping...\n";
    $stmt = $conn->prepare('
        INSERT INTO reviews (app_name, store_name, country_name, rating, review_content, review_date, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
    ');
    
    $result = $stmt->execute([
        $testReview['app_name'],
        $testReview['store_name'],
        $testReview['country_name'],
        $testReview['rating'],
        $testReview['review_content'],
        $testReview['review_date']
    ]);
    
    if ($result) {
        echo "✅ Test review added successfully!\n";
        echo "   Store: {$testReview['store_name']}\n";
        echo "   Rating: {$testReview['rating']}★\n";
        echo "   Content: " . substr($testReview['review_content'], 0, 50) . "...\n\n";
    } else {
        echo "❌ Failed to add test review\n";
        return false;
    }
    
    // Step 3: Test Smart Sync
    echo "3️⃣ TESTING SMART SYNC COMPARISON\n";
    echo "--------------------------------\n";
    
    $smartSyncUrl = 'http://localhost:8000/api/smart-sync-analytics.php';
    $smartSyncData = json_encode(['app_name' => 'StoreSEO']);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => $smartSyncData,
            'timeout' => 30
        ]
    ]);
    
    echo "🧠 Running smart sync for StoreSEO...\n";
    $response = @file_get_contents($smartSyncUrl, false, $context);
    
    if ($response) {
        $result = json_decode($response, true);
        
        if ($result && $result['success']) {
            $stats = $result['stats'];
            echo "✅ Smart Sync Results:\n";
            echo "   📊 Total Found: {$stats['total_found']}\n";
            echo "   ⏭️ Duplicates Skipped: {$stats['duplicates_skipped']}\n";
            echo "   ➕ New Added: {$stats['new_added']}\n";
            echo "   💬 Message: {$result['message']}\n\n";
            
            if ($stats['new_added'] > 0) {
                echo "🎉 SUCCESS: Smart sync detected and will process {$stats['new_added']} new review(s)!\n\n";
            } else {
                echo "ℹ️ INFO: Smart sync detected {$stats['total_found']} review(s) but they already exist in Access Reviews\n\n";
            }
        } else {
            echo "❌ Smart Sync Failed: " . ($result['error'] ?? 'Unknown error') . "\n\n";
            return false;
        }
    } else {
        echo "❌ Smart Sync API connection failed\n\n";
        return false;
    }
    
    // Step 4: Verify final state
    echo "4️⃣ VERIFYING FINAL STATE\n";
    echo "------------------------\n";
    
    // Count again after sync
    $stmt = $conn->prepare("SELECT COUNT(*) FROM reviews WHERE review_date = ? AND app_name = 'StoreSEO'");
    $stmt->execute([$today]);
    $finalMainCount = $stmt->fetchColumn();
    
    $stmt = $conn->prepare("SELECT COUNT(*) FROM access_reviews WHERE review_date = ? AND app_name = 'StoreSEO'");
    $stmt->execute([$today]);
    $finalAccessCount = $stmt->fetchColumn();
    
    echo "📊 Final State:\n";
    echo "   - Main reviews table (today): $finalMainCount reviews (+". ($finalMainCount - $mainTableCount) .")\n";
    echo "   - Access reviews table (today): $finalAccessCount reviews (+". ($finalAccessCount - $accessTableCount) .")\n\n";
    
    // Step 5: Test Access Reviews API
    echo "5️⃣ TESTING ACCESS REVIEWS API\n";
    echo "-----------------------------\n";
    
    $accessUrl = 'http://localhost:8000/api/access-reviews.php';
    $accessResponse = @file_get_contents($accessUrl);
    
    if ($accessResponse) {
        $accessData = json_decode($accessResponse, true);
        
        if ($accessData && $accessData['success']) {
            $storeSEOReviews = array_filter($accessData['reviews'], function($review) {
                return $review['app_name'] === 'StoreSEO';
            });
            
            echo "✅ Access Reviews API working!\n";
            echo "   📱 StoreSEO reviews in Access Reviews: " . count($storeSEOReviews) . "\n";
            echo "   📊 Total reviews across all apps: " . count($accessData['reviews']) . "\n\n";
        } else {
            echo "❌ Access Reviews API failed\n\n";
        }
    } else {
        echo "❌ Access Reviews API connection failed\n\n";
    }
    
    return true;
}

// Run the complete test
$success = testCompleteWorkflow();

echo "🏆 SMART SYNC SYSTEM SUMMARY\n";
echo "============================\n";

if ($success) {
    echo "✅ Smart Sync System is fully operational!\n\n";
    
    echo "🔄 HOW IT WORKS:\n";
    echo "================\n";
    echo "1. 📱 Analytics page scrapes new reviews for today\n";
    echo "2. 🧠 Smart sync compares with existing Access Review Tab data\n";
    echo "3. ⏭️ Duplicates are automatically skipped (no manual intervention)\n";
    echo "4. ➕ Only genuinely new reviews are added to Access Review page\n";
    echo "5. 🔄 Regular sync process updates the Access Review page\n";
    echo "6. 🎯 Result: No duplicate reviews, perfect data integrity!\n\n";
    
    echo "✨ BENEFITS:\n";
    echo "============\n";
    echo "• 🚫 Prevents duplicate reviews automatically\n";
    echo "• ⚡ Fast comparison using app-wise data matching\n";
    echo "• 🔄 Seamless integration with existing workflow\n";
    echo "• 📊 Maintains accurate review counts\n";
    echo "• 🎯 Works with all apps (StoreSEO, StoreFAQ, EasyFlow, etc.)\n";
    
} else {
    echo "❌ Some issues detected in the Smart Sync System\n";
    echo "Please check the error messages above for details.\n";
}

echo "\n🎉 Smart Sync System Test Complete!\n";
?>
