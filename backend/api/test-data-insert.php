<?php
/**
 * Test Data Insert Endpoint
 * Tests if data can be inserted into the database on live server
 * Use this to verify database write permissions work
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'POST') {
        // Test inserting a sample review
        $testReview = [
            'app_name' => 'StoreSEO',
            'store_name' => 'Test Store ' . date('Y-m-d H:i:s'),
            'country_name' => 'United States',
            'rating' => 5,
            'review_content' => 'Test review inserted on ' . date('Y-m-d H:i:s'),
            'review_date' => date('Y-m-d'),
            'earned_by' => null
        ];
        
        $insertSQL = "
            INSERT INTO reviews (app_name, store_name, country_name, rating, review_content, review_date, earned_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";
        
        $stmt = $pdo->prepare($insertSQL);
        $result = $stmt->execute([
            $testReview['app_name'],
            $testReview['store_name'],
            $testReview['country_name'],
            $testReview['rating'],
            $testReview['review_content'],
            $testReview['review_date'],
            $testReview['earned_by']
        ]);
        
        if ($result) {
            $insertId = $pdo->lastInsertId();
            
            // Verify the insert by reading it back
            $verifyStmt = $pdo->prepare("SELECT * FROM reviews WHERE id = ?");
            $verifyStmt->execute([$insertId]);
            $insertedReview = $verifyStmt->fetch();
            
            echo json_encode([
                'success' => true,
                'message' => 'Test data inserted successfully',
                'inserted_id' => $insertId,
                'inserted_data' => $insertedReview,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to insert test data',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
        
    } else {
        // GET request - show current test data
        $stmt = $pdo->prepare("
            SELECT * FROM reviews 
            WHERE store_name LIKE 'Test Store%' 
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        $stmt->execute();
        $testData = $stmt->fetchAll();
        
        // Get total counts
        $countStmt = $pdo->query("SELECT COUNT(*) as total FROM reviews");
        $totalCount = $countStmt->fetch()['total'];
        
        $testCountStmt = $pdo->query("SELECT COUNT(*) as test_count FROM reviews WHERE store_name LIKE 'Test Store%'");
        $testCount = $testCountStmt->fetch()['test_count'];
        
        echo json_encode([
            'success' => true,
            'total_reviews' => $totalCount,
            'test_reviews' => $testCount,
            'recent_test_data' => $testData,
            'message' => 'Use POST request to insert test data',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
