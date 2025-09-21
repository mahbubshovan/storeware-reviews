<?php
/**
 * Access Reviews API (Last 30 Days)
 * Displays reviews from the last 30 days with pagination and app-wise organization
 * Focuses on recent review management and assignments
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGetAllAccessReviews($conn);
            break;
            
        case 'POST':
            handleUpdateAssignment($conn);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}

function handleGetAllAccessReviews($conn) {
    $startTime = microtime(true);
    
    // Get parameters
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = max(1, min(100, intval($_GET['limit'] ?? 20))); // Max 100 per page
    $appFilter = $_GET['app'] ?? null;
    $showAssigned = $_GET['show_assigned'] ?? 'all'; // 'all', 'assigned', 'unassigned'
    $groupByApp = $_GET['group_by_app'] ?? 'true';
    $searchTerm = $_GET['search'] ?? null;
    
    $offset = ($page - 1) * $limit;
    
    // Build WHERE clause - ONLY show last 30 days reviews
    $whereConditions = [
        'is_active = TRUE',
        'review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)'
    ];
    $params = [];
    
    if ($appFilter && $appFilter !== 'all') {
        $whereConditions[] = 'app_name = ?';
        $params[] = $appFilter;
    }
    
    if ($showAssigned === 'assigned') {
        $whereConditions[] = 'earned_by IS NOT NULL AND earned_by != ""';
    } elseif ($showAssigned === 'unassigned') {
        $whereConditions[] = '(earned_by IS NULL OR earned_by = "")';
    }
    
    if ($searchTerm) {
        $whereConditions[] = '(review_content LIKE ? OR store_name LIKE ? OR app_name LIKE ?)';
        $searchParam = '%' . $searchTerm . '%';
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    // Get total count for pagination
    $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM review_repository WHERE $whereClause");
    $countStmt->execute($params);
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Calculate pagination info
    $totalPages = ceil($totalCount / $limit);
    $hasNextPage = $page < $totalPages;
    $hasPrevPage = $page > 1;
    
    // Generate page numbers for pagination UI
    $pageNumbers = [];
    $startPage = max(1, $page - 3);
    $endPage = min($totalPages, $page + 3);
    for ($i = $startPage; $i <= $endPage; $i++) {
        $pageNumbers[] = $i;
    }
    
    if ($groupByApp === 'true') {
        // Get reviews grouped by app
        $reviews = getReviewsGroupedByApp($conn, $whereClause, $params, $limit, $offset);
        $statistics = getGroupedStatistics($conn, $whereClause, $params);
    } else {
        // Get flat list of reviews
        $reviews = getReviewsFlat($conn, $whereClause, $params, $limit, $offset);
        $statistics = getFlatStatistics($conn, $whereClause, $params);
    }
    
    // Get available apps for filter dropdown
    $availableApps = getAvailableApps($conn);
    
    $queryTime = number_format((microtime(true) - $startTime) * 1000, 2);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'reviews' => $reviews,
            'grouped_by_app' => $groupByApp === 'true',
            'statistics' => $statistics,
            'available_apps' => $availableApps,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalCount,
                'items_per_page' => $limit,
                'has_next_page' => $hasNextPage,
                'has_prev_page' => $hasPrevPage,
                'start_item' => $offset + 1,
                'end_item' => min($offset + $limit, $totalCount),
                'page_numbers' => $pageNumbers
            ],
            'filters' => [
                'app' => $appFilter,
                'show_assigned' => $showAssigned,
                'group_by_app' => $groupByApp,
                'search' => $searchTerm,
                'page' => $page,
                'limit' => $limit
            ]
        ],
        'meta' => [
            'generated_at' => date('Y-m-d H:i:s'),
            'query_time' => $queryTime . 'ms',
            'total_reviews_in_db' => $totalCount
        ]
    ]);
}

function getReviewsGroupedByApp($conn, $whereClause, $params, $limit, $offset) {
    // First get the apps that have reviews matching the criteria
    $appStmt = $conn->prepare("
        SELECT app_name, COUNT(*) as review_count
        FROM review_repository
        WHERE $whereClause
        GROUP BY app_name
        ORDER BY app_name
    ");
    $appStmt->execute($params);
    $apps = $appStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $groupedReviews = [];
    
    foreach ($apps as $app) {
        $appName = $app['app_name'];
        
        // Get reviews for this app with pagination applied across all apps
        $reviewStmt = $conn->prepare("
            SELECT
                id, app_name, store_name, country_name, rating, review_content, review_date,
                earned_by, created_at, updated_at,
                CASE
                    WHEN review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 'recent'
                    WHEN review_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN 'current_month'
                    ELSE 'older'
                END as time_category
            FROM review_repository
            WHERE $whereClause AND app_name = ?
            ORDER BY
                CASE WHEN earned_by IS NULL OR earned_by = '' THEN 0 ELSE 1 END,
                review_date DESC,
                created_at DESC
            LIMIT ? OFFSET ?
        ");
        
        $appParams = array_merge($params, [$appName, $limit, $offset]);
        $reviewStmt->execute($appParams);
        $appReviews = $reviewStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($appReviews)) {
            // Calculate stats for this app
            $statsStmt = $conn->prepare("
                SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN earned_by IS NOT NULL AND earned_by != '' THEN 1 ELSE 0 END) as assigned,
                    SUM(CASE WHEN earned_by IS NULL OR earned_by = '' THEN 1 ELSE 0 END) as unassigned,
                    ROUND(AVG(rating), 1) as average_rating
                FROM review_repository
                WHERE $whereClause AND app_name = ?
            ");
            $statsStmt->execute(array_merge($params, [$appName]));
            $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
            
            $groupedReviews[] = [
                'app_name' => $appName,
                'reviews' => $appReviews,
                'stats' => [
                    'total' => intval($stats['total']),
                    'assigned' => intval($stats['assigned']),
                    'unassigned' => intval($stats['unassigned']),
                    'average_rating' => floatval($stats['average_rating'])
                ]
            ];
        }
    }
    
    return $groupedReviews;
}

function getReviewsFlat($conn, $whereClause, $params, $limit, $offset) {
    $stmt = $conn->prepare("
        SELECT
            id, app_name, store_name, country_name, rating, review_content, review_date,
            earned_by, created_at, updated_at,
            CASE
                WHEN review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 'recent'
                WHEN review_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN 'current_month'
                ELSE 'older'
            END as time_category
        FROM review_repository
        WHERE $whereClause
        ORDER BY
            CASE WHEN earned_by IS NULL OR earned_by = '' THEN 0 ELSE 1 END,
            review_date DESC,
            created_at DESC
        LIMIT ? OFFSET ?
    ");

    $flatParams = array_merge($params, [$limit, $offset]);
    $stmt->execute($flatParams);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getGroupedStatistics($conn, $whereClause, $params) {
    $stmt = $conn->prepare("
        SELECT
            COUNT(*) as total_reviews,
            SUM(CASE WHEN earned_by IS NOT NULL AND earned_by != '' THEN 1 ELSE 0 END) as assigned_reviews,
            SUM(CASE WHEN earned_by IS NULL OR earned_by = '' THEN 1 ELSE 0 END) as unassigned_reviews,
            ROUND(AVG(rating), 1) as average_rating,
            COUNT(DISTINCT app_name) as total_apps
        FROM review_repository
        WHERE $whereClause
    ");
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getFlatStatistics($conn, $whereClause, $params) {
    return getGroupedStatistics($conn, $whereClause, $params); // Same logic
}

function getAvailableApps($conn) {
    $stmt = $conn->prepare("
        SELECT app_name, COUNT(*) as review_count
        FROM review_repository
        WHERE is_active = TRUE
        AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY app_name
        ORDER BY app_name
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function handleUpdateAssignment($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['review_id']) || !isset($input['earned_by'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }
    
    $reviewId = intval($input['review_id']);
    $earnedBy = trim($input['earned_by']);
    
    try {
        $stmt = $conn->prepare("
            UPDATE review_repository
            SET earned_by = ?, updated_at = NOW()
            WHERE id = ? AND is_active = TRUE
        ");
        
        $success = $stmt->execute([$earnedBy, $reviewId]);
        
        if ($success && $stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Assignment updated successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Review not found or no changes made'
            ]);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
?>
