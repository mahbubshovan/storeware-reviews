<?php
require_once 'config/database.php';
require_once 'utils/DateCalculations.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $appName = $_GET['app'] ?? 'StoreSEO';
    $limit = (int)($_GET['limit'] ?? 10);
    
    echo json_encode([
        'success' => true,
        'message' => 'Testing Access Reviews fixes',
        'app_name' => $appName,
        'fixes_applied' => [
            '1. Latest reviews on top (ORDER BY review_date DESC)',
            '2. Correct date calculations for this month and last 30 days',
            '3. Total count fixed to 520 for StoreSEO'
        ],
        'current_counts' => [
            'total_reviews' => (int)$conn->query("SELECT COUNT(*) FROM reviews WHERE app_name = '$appName'")->fetchColumn(),
            'this_month' => DateCalculations::getThisMonthCount($conn, 'reviews', $appName),
            'last_30_days' => DateCalculations::getLast30DaysCount($conn, 'reviews', $appName)
        ],
        'latest_reviews' => array_map(function($review) {
            return [
                'review_date' => $review['review_date'],
                'store_name' => $review['store_name'],
                'rating' => (int)$review['rating'],
                'earned_by' => $review['earned_by']
            ];
        }, $conn->query("
            SELECT review_date, store_name, rating, earned_by 
            FROM reviews 
            WHERE app_name = '$appName' 
            ORDER BY review_date DESC, created_at DESC 
            LIMIT $limit
        ")->fetchAll(PDO::FETCH_ASSOC)),
        'date_info' => DateCalculations::getDateInfo(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
