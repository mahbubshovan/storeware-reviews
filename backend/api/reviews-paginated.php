<?php
/**
 * Paginated Reviews API Endpoint
 * Provides comprehensive pagination and filtering for homepage Latest Reviews section
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../utils/DatabaseManager.php';

header('Content-Type: application/json');

try {
    $dbManager = new DatabaseManager();
    $conn = $dbManager->getConnection();
    
    // Get pagination parameters
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = min(50, max(5, intval($_GET['limit'] ?? 10))); // Between 5-50 items per page
    $offset = ($page - 1) * $limit;
    
    // Get filtering parameters
    $appFilter = $_GET['app'] ?? null;
    $ratingFilter = isset($_GET['rating']) ? intval($_GET['rating']) : null;
    $dateFrom = $_GET['date_from'] ?? null;
    $dateTo = $_GET['date_to'] ?? null;
    $sortOrder = $_GET['sort'] ?? 'newest';
    
    // Validate sort order
    $validSorts = ['newest', 'oldest', 'rating_high', 'rating_low'];
    if (!in_array($sortOrder, $validSorts)) {
        $sortOrder = 'newest';
    }
    
    // Build WHERE clause
    $whereConditions = ['rr.is_active = TRUE'];
    $params = [];
    
    if ($appFilter && $appFilter !== 'all') {
        $whereConditions[] = 'rr.app_name = ?';
        $params[] = $appFilter;
    }
    
    if ($ratingFilter && $ratingFilter >= 1 && $ratingFilter <= 5) {
        $whereConditions[] = 'rr.rating = ?';
        $params[] = $ratingFilter;
    }
    
    if ($dateFrom) {
        $whereConditions[] = 'rr.review_date >= ?';
        $params[] = $dateFrom;
    }
    
    if ($dateTo) {
        $whereConditions[] = 'rr.review_date <= ?';
        $params[] = $dateTo;
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    // Build ORDER BY clause
    $orderBy = match($sortOrder) {
        'newest' => 'rr.review_date DESC, rr.created_at DESC, rr.id DESC',
        'oldest' => 'rr.review_date ASC, rr.created_at ASC, rr.id ASC',
        'rating_high' => 'rr.rating DESC, rr.review_date DESC, rr.id DESC',
        'rating_low' => 'rr.rating ASC, rr.review_date DESC, rr.id DESC',
        default => 'rr.review_date DESC, rr.created_at DESC, rr.id DESC'
    };
    
    // Get total count for pagination
    $countQuery = "
        SELECT COUNT(*) as total 
        FROM review_repository rr 
        WHERE $whereClause
    ";
    
    $countStmt = $conn->prepare($countQuery);
    $countStmt->execute($params);
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalCount / $limit);
    
    // Get paginated reviews
    $reviewsQuery = "
        SELECT 
            rr.id,
            rr.app_name,
            rr.store_name,
            rr.country_name,
            rr.rating,
            rr.review_content,
            rr.review_date,
            rr.earned_by,
            rr.is_featured,
            rr.created_at,
            rr.updated_at,
            CASE 
                WHEN rr.review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 'recent'
                WHEN rr.review_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN 'current_month'
                ELSE 'older'
            END as time_category
        FROM review_repository rr 
        WHERE $whereClause
        ORDER BY $orderBy
        LIMIT ? OFFSET ?
    ";
    
    $reviewsStmt = $conn->prepare($reviewsQuery);
    $reviewsStmt->execute([...$params, $limit, $offset]);
    $reviews = $reviewsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get available apps for filter dropdown
    $appsQuery = "
        SELECT DISTINCT app_name, COUNT(*) as review_count
        FROM review_repository 
        WHERE is_active = TRUE
        GROUP BY app_name
        ORDER BY app_name
    ";
    $appsStmt = $conn->prepare($appsQuery);
    $appsStmt->execute();
    $availableApps = $appsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get rating distribution for current filters
    $ratingQuery = "
        SELECT 
            rating,
            COUNT(*) as count
        FROM review_repository rr 
        WHERE $whereClause
        GROUP BY rating
        ORDER BY rating DESC
    ";
    $ratingStmt = $conn->prepare($ratingQuery);
    $ratingStmt->execute($params);
    $ratingDistribution = $ratingStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate pagination info
    $hasNextPage = $page < $totalPages;
    $hasPrevPage = $page > 1;
    $startItem = $offset + 1;
    $endItem = min($offset + $limit, $totalCount);
    
    // Generate page numbers for pagination UI
    $pageNumbers = [];
    $maxPageNumbers = 7; // Show max 7 page numbers
    $startPage = max(1, $page - floor($maxPageNumbers / 2));
    $endPage = min($totalPages, $startPage + $maxPageNumbers - 1);
    
    // Adjust start page if we're near the end
    if ($endPage - $startPage + 1 < $maxPageNumbers) {
        $startPage = max(1, $endPage - $maxPageNumbers + 1);
    }
    
    for ($i = $startPage; $i <= $endPage; $i++) {
        $pageNumbers[] = $i;
    }
    
    // Response
    echo json_encode([
        'success' => true,
        'data' => [
            'reviews' => $reviews,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalCount,
                'items_per_page' => $limit,
                'has_next_page' => $hasNextPage,
                'has_prev_page' => $hasPrevPage,
                'start_item' => $startItem,
                'end_item' => $endItem,
                'page_numbers' => $pageNumbers
            ],
            'filters' => [
                'app' => $appFilter,
                'rating' => $ratingFilter,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'sort' => $sortOrder
            ],
            'available_apps' => $availableApps,
            'rating_distribution' => $ratingDistribution
        ],
        'meta' => [
            'generated_at' => date('Y-m-d H:i:s'),
            'query_time' => number_format((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2) . 'ms'
        ]
    ]);

} catch (Exception $e) {
    error_log("Paginated reviews error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch paginated reviews',
        'message' => $e->getMessage()
    ]);
}
?>
