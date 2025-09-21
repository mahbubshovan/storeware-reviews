<?php
/**
 * Enhanced Access Reviews API
 * Provides permanent storage and management for Access Reviews page
 * Never removes reviews - only filters display based on date ranges
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../utils/ReviewRepository.php';

header('Content-Type: application/json');

try {
    $repository = new ReviewRepository();
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGetAccessReviews($repository);
            break;
            
        case 'POST':
            handleUpdateAssignment($repository);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Access reviews enhanced error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}

function handleGetAccessReviews($repository) {
    // Get parameters
    $appFilter = $_GET['app'] ?? null;
    $dateRange = $_GET['date_range'] ?? 'last_30_days'; // 'last_30_days', 'current_month', 'all'
    $showAssigned = $_GET['show_assigned'] ?? 'all'; // 'all', 'assigned', 'unassigned'
    $groupByApp = $_GET['group_by_app'] ?? 'true';
    
    // Determine days back based on date range
    $daysBack = match($dateRange) {
        'last_30_days' => 30,
        'current_month' => date('j'), // Days since start of current month
        'last_7_days' => 7,
        'all' => 0, // No date filtering
        default => 30
    };
    
    // Get reviews from repository
    $reviews = $repository->getAccessReviews($appFilter, $daysBack);
    
    // Filter by assignment status
    if ($showAssigned === 'assigned') {
        $reviews = array_filter($reviews, fn($review) => !empty($review['earned_by']));
    } elseif ($showAssigned === 'unassigned') {
        $reviews = array_filter($reviews, fn($review) => empty($review['earned_by']));
    }
    
    // Group by app if requested
    $groupedReviews = [];
    if ($groupByApp === 'true') {
        foreach ($reviews as $review) {
            $appName = $review['app_name'];
            if (!isset($groupedReviews[$appName])) {
                $groupedReviews[$appName] = [
                    'app_name' => $appName,
                    'reviews' => [],
                    'stats' => [
                        'total' => 0,
                        'assigned' => 0,
                        'unassigned' => 0,
                        'average_rating' => 0
                    ]
                ];
            }
            
            $groupedReviews[$appName]['reviews'][] = $review;
            $groupedReviews[$appName]['stats']['total']++;
            
            if (!empty($review['earned_by'])) {
                $groupedReviews[$appName]['stats']['assigned']++;
            } else {
                $groupedReviews[$appName]['stats']['unassigned']++;
            }
        }
        
        // Calculate average ratings for each app
        foreach ($groupedReviews as $appName => &$appData) {
            if ($appData['stats']['total'] > 0) {
                $totalRating = array_sum(array_column($appData['reviews'], 'rating'));
                $appData['stats']['average_rating'] = round($totalRating / $appData['stats']['total'], 2);
            }
        }
        
        // Convert to indexed array and sort by app name
        $groupedReviews = array_values($groupedReviews);
        usort($groupedReviews, fn($a, $b) => strcmp($a['app_name'], $b['app_name']));
    }
    
    // Get overall statistics
    $totalStats = [
        'total_reviews' => count($reviews),
        'assigned_reviews' => count(array_filter($reviews, fn($r) => !empty($r['earned_by']))),
        'unassigned_reviews' => count(array_filter($reviews, fn($r) => empty($r['earned_by']))),
        'apps_count' => count(array_unique(array_column($reviews, 'app_name'))),
        'date_range' => $dateRange,
        'days_back' => $daysBack
    ];
    
    // Get available apps for filter dropdown
    $availableApps = $repository->getAvailableApps();
    
    echo json_encode([
        'success' => true,
        'data' => [
            'reviews' => $groupByApp === 'true' ? $groupedReviews : $reviews,
            'grouped_by_app' => $groupByApp === 'true',
            'statistics' => $totalStats,
            'available_apps' => $availableApps,
            'filters' => [
                'app' => $appFilter,
                'date_range' => $dateRange,
                'show_assigned' => $showAssigned,
                'group_by_app' => $groupByApp
            ]
        ],
        'meta' => [
            'generated_at' => date('Y-m-d H:i:s'),
            'query_time' => number_format((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2) . 'ms'
        ]
    ]);
}

function handleUpdateAssignment($repository) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['review_id']) || !isset($input['earned_by'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Missing required fields: review_id and earned_by'
        ]);
        return;
    }
    
    $reviewId = intval($input['review_id']);
    $earnedBy = trim($input['earned_by']);
    $assignedBy = $input['assigned_by'] ?? 'system';
    
    // Allow empty string to unassign
    if ($earnedBy === '') {
        $earnedBy = null;
    }
    
    $success = $repository->assignReview($reviewId, $earnedBy, $assignedBy);
    
    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => $earnedBy ? 'Review assigned successfully' : 'Review unassigned successfully',
            'data' => [
                'review_id' => $reviewId,
                'earned_by' => $earnedBy,
                'assigned_at' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to update assignment'
        ]);
    }
}
?>
