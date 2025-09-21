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
    
    // Get app_name and filter parameters
    $appName = $_GET['app_name'] ?? '';
    $filter = $_GET['filter'] ?? 'last_30_days'; // Default to last 30 days

    if (empty($appName)) {
        echo json_encode([
            'success' => false,
            'message' => 'app_name parameter is required'
        ]);
        exit;
    }
    
    $dbManager = new DatabaseManager();
    $conn = $dbManager->getConnection();
    
    // Get country-wise review counts for the specified app with date filtering
    // Using standardized date calculations for consistency
    require_once __DIR__ . '/../utils/DateCalculations.php';

    // Determine date condition based on filter
    $dateCondition = '';
    if ($filter === 'last_30_days') {
        $dateCondition = DateCalculations::getLast30DaysCondition();
    } elseif ($filter === 'all_time') {
        $dateCondition = ''; // No date filtering for all time
    }

    if ($filter === 'all_time') {
        $query = "
            SELECT
                country_name,
                COUNT(*) as review_count
            FROM reviews
            WHERE app_name = ?
            AND is_active = TRUE
            AND country_name IS NOT NULL
            AND country_name != ''
            GROUP BY country_name
            ORDER BY review_count DESC, country_name ASC
        ";
    } else {
        // last_30_days
        $query = "
            SELECT
                country_name,
                COUNT(*) as review_count
            FROM reviews
            WHERE app_name = ?
            AND is_active = TRUE
            AND country_name IS NOT NULL
            AND country_name != ''
            AND $dateCondition
            GROUP BY country_name
            ORDER BY review_count DESC, country_name ASC
        ";
    }

    $stmt = $conn->prepare($query);

    $stmt->execute([$appName]);
    $countryStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Also get total review count for this app
    if ($filter === 'all_time') {
        $totalQuery = "
            SELECT COUNT(*) as total_count
            FROM reviews
            WHERE app_name = ?
            AND is_active = TRUE
        ";
    } else {
        $totalQuery = "
            SELECT COUNT(*) as total_count
            FROM reviews
            WHERE app_name = ?
            AND is_active = TRUE
            AND $dateCondition
        ";
    }

    $totalStmt = $conn->prepare($totalQuery);
    $totalStmt->execute([$appName]);
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
