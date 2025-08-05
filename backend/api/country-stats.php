<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../utils/DatabaseManager.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed'
        ]);
        exit;
    }
    
    // Get app_name parameter
    $appName = $_GET['app_name'] ?? '';
    
    if (empty($appName)) {
        echo json_encode([
            'success' => false,
            'message' => 'app_name parameter is required'
        ]);
        exit;
    }
    
    $dbManager = new DatabaseManager();
    $conn = $dbManager->getConnection();
    
    // Get country-wise review counts for the specified app from last 30 days
    $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
    
    $stmt = $conn->prepare("
        SELECT 
            country_name,
            COUNT(*) as review_count
        FROM access_reviews 
        WHERE app_name = ? 
        AND review_date >= ?
        AND country_name IS NOT NULL 
        AND country_name != ''
        GROUP BY country_name 
        ORDER BY review_count DESC, country_name ASC
    ");
    
    $stmt->execute([$appName, $thirtyDaysAgo]);
    $countryStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Also get total review count for this app
    $totalStmt = $conn->prepare("
        SELECT COUNT(*) as total_count
        FROM access_reviews 
        WHERE app_name = ? 
        AND review_date >= ?
    ");
    
    $totalStmt->execute([$appName, $thirtyDaysAgo]);
    $totalCount = $totalStmt->fetchColumn();
    
    // Calculate percentages and add additional info
    $enrichedStats = [];
    foreach ($countryStats as $stat) {
        $percentage = $totalCount > 0 ? round(($stat['review_count'] / $totalCount) * 100, 1) : 0;
        
        $enrichedStats[] = [
            'country_name' => $stat['country_name'],
            'review_count' => (int)$stat['review_count'],
            'percentage' => $percentage
        ];
    }
    
    echo json_encode([
        'success' => true,
        'app_name' => $appName,
        'total_reviews' => (int)$totalCount,
        'country_stats' => $enrichedStats,
        'countries_count' => count($enrichedStats),
        'period' => 'Last 30 days'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
