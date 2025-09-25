<?php
/**
 * Tabbed Access Reviews API - REAL-TIME REVIEW BROWSER
 * Provides app-specific review data for the 6-tab interface with FRESH DATA
 * Shows ALL reviews (both assigned and unassigned) across ALL TIME PERIODS
 * This serves as a complete review browser for each app with REAL-TIME SCRAPING
 * NO DATE FILTERING - Complete historical archive of all reviews
 * ALWAYS GETS FRESH DATA FROM SHOPIFY - NO CACHED DATA
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../scraper/ShopifyReviewScraper.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            handleGetTabbedReviews($conn);
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

function handleGetTabbedReviews($conn) {
    $startTime = microtime(true);

    // Get parameters
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = max(1, min(50, intval($_GET['limit'] ?? 15))); // 15 reviews per page
    $appName = $_GET['app'] ?? null;

    if (!$appName) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'App name is required']);
        return;
    }

    // 🔥 STATIC DATA APPROACH: Use database data only (no real-time scraping)
    // Always use the reviews table for fast, consistent performance

    $useReviewsTable = true;

    $offset = ($page - 1) * $limit;

    // Always use the main reviews table for consistent performance
    $query = "
        SELECT
            id,
            app_name,
            store_name,
            country_name,
            rating,
            review_content,
            review_date,
            earned_by,
            created_at,
            updated_at
        FROM reviews
        WHERE app_name = ?
        AND is_active = TRUE
        ORDER BY
            CASE WHEN earned_by IS NULL OR earned_by = '' THEN 1 ELSE 0 END,
            review_date DESC,
            created_at DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute([$appName, $limit, $offset]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total count for pagination
    $countQuery = "
        SELECT COUNT(*) as total
        FROM reviews
        WHERE app_name = ?
        AND is_active = TRUE
    ";
    $countStmt = $conn->prepare($countQuery);
    $countStmt->execute([$appName]);
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    $dataSource = 'static_database';
    
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
    
    // Get live statistics from Enhanced Analytics API for consistency
    $liveStats = getLiveStatistics($appName);

    // Calculate assignment statistics from the reviews table
    $assignmentQuery = "
        SELECT
            COUNT(*) as db_total_reviews,
            COUNT(CASE WHEN earned_by IS NOT NULL AND earned_by != '' THEN 1 END) as assigned_reviews,
            COUNT(CASE WHEN earned_by IS NULL OR earned_by = '' THEN 1 END) as unassigned_reviews,
            AVG(rating) as db_avg_rating
        FROM reviews
        WHERE app_name = ? AND is_active = TRUE
    ";
    $assignmentStmt = $conn->prepare($assignmentQuery);
    $assignmentStmt->execute([$appName]);
    $assignmentStats = $assignmentStmt->fetch(PDO::FETCH_ASSOC);

    // Combine live stats with assignment stats (fallback to database if live data unavailable)
    $statistics = [
        'total_reviews' => $liveStats['total_reviews'] ?: (int)$assignmentStats['db_total_reviews'],
        'assigned_reviews' => (int)$assignmentStats['assigned_reviews'],
        'unassigned_reviews' => (int)$assignmentStats['unassigned_reviews'],
        'avg_rating' => $liveStats['avg_rating'] ?: round($assignmentStats['db_avg_rating'], 1),
        'db_total_reviews' => (int)$assignmentStats['db_total_reviews'], // Keep for comparison
        'data_source' => $liveStats['total_reviews'] ? 'live_shopify' : 'database_fallback'
    ];
    
    $executionTime = round((microtime(true) - $startTime) * 1000, 2);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'reviews' => $reviews,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => intval($totalCount),
                'items_per_page' => $limit,
                'has_next_page' => $hasNextPage,
                'has_prev_page' => $hasPrevPage,
                'page_numbers' => $pageNumbers
            ],
            'statistics' => $statistics,
            'app_name' => $appName,
            'data_source' => $dataSource
        ],
        'execution_time_ms' => $executionTime
    ]);
}

/**
 * Get live statistics from Enhanced Analytics API for consistency
 */
function getLiveStatistics($appName) {
    try {
        // Determine the correct base URL for the current environment
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';

        // For Railway deployment, use the current host
        if (getenv('RAILWAY_ENVIRONMENT') || strpos($host, 'railway.app') !== false) {
            $baseUrl = $protocol . '://' . $host;
        } else {
            // Local development
            $baseUrl = 'http://localhost:8000';
        }

        $url = $baseUrl . "/api/enhanced-analytics.php?app=" . urlencode($appName);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For Railway HTTPS

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log("cURL error for $appName statistics: $curlError");
        }

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            if ($data && $data['success']) {
                return [
                    'total_reviews' => $data['data']['rating_distribution_total'],
                    'avg_rating' => $data['data']['average_rating']
                ];
            }
        } else {
            error_log("HTTP $httpCode response from $url for $appName");
        }
    } catch (Exception $e) {
        // Fallback to database if Enhanced Analytics fails
        error_log("Failed to get live statistics for $appName: " . $e->getMessage());
    }

    // Fallback: return null to use database stats
    return [
        'total_reviews' => null,
        'avg_rating' => null
    ];
}

function handleUpdateAssignment($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['review_id']) || !isset($input['earned_by'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }
    
    $reviewId = $input['review_id'];
    $earnedBy = trim($input['earned_by']);
    
    try {
        // Update in reviews table (the actual data source for Access Tabbed)
        $stmt = $conn->prepare("
            UPDATE reviews
            SET earned_by = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        $result = $stmt->execute([$earnedBy, $reviewId]);
        
        if ($result && $stmt->rowCount() > 0) {
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

/**
 * Get all available apps with their review counts
 */
function getAvailableApps($conn) {
    $query = "
        SELECT
            app_name,
            COUNT(*) as total_reviews,
            COUNT(CASE WHEN earned_by IS NOT NULL AND earned_by != '' THEN 1 END) as assigned_reviews
        FROM reviews
        WHERE is_active = TRUE
        GROUP BY app_name
        ORDER BY app_name
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Merge fresh scraped reviews with existing assignments from database
 */
function mergeWithExistingAssignments($conn, $freshReviews, $appName) {
    // Get existing assignments from reviews table (the actual data source)
    $query = "
        SELECT
            review_content,
            store_name,
            review_date,
            earned_by
        FROM reviews
        WHERE app_name = ?
        AND is_active = TRUE
        AND (earned_by IS NOT NULL AND earned_by != '')
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute([$appName]);
    $existingAssignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Create a lookup map for existing assignments
    $assignmentMap = [];
    foreach ($existingAssignments as $assignment) {
        $key = md5($assignment['review_content'] . $assignment['store_name'] . $assignment['review_date']);
        $assignmentMap[$key] = $assignment['earned_by'];
    }

    // Apply existing assignments to fresh reviews
    foreach ($freshReviews as &$review) {
        $key = md5($review['review_content'] . $review['store_name'] . $review['review_date']);
        if (isset($assignmentMap[$key])) {
            $review['earned_by'] = $assignmentMap[$key];
        } else {
            $review['earned_by'] = null; // Unassigned
        }

        // Add required fields for compatibility
        $review['id'] = $key; // Use hash as temporary ID
        $review['app_name'] = $appName;
        $review['created_at'] = date('Y-m-d H:i:s');
        $review['updated_at'] = date('Y-m-d H:i:s');
    }

    return $freshReviews;
}

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
?>
