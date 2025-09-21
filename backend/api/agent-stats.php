<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../utils/DatabaseManager.php';

try {
    // Get the app name and filter from query parameters
    $appName = $_GET['app_name'] ?? null;
    $filter = $_GET['filter'] ?? 'last_30_days'; // Default to last 30 days

    if (!$appName) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'App name is required'
        ]);
        exit;
    }
    
    $dbManager = new DatabaseManager();
    $conn = $dbManager->getConnection();
    
    // Get agent statistics for the specified app with date filtering
    // Using standardized date calculations for consistency
    require_once __DIR__ . '/../utils/DateCalculations.php';

    // Determine date condition based on filter
    $dateCondition = '';
    if ($filter === 'last_30_days') {
        $dateCondition = DateCalculations::getLast30DaysCondition();
    } elseif ($filter === 'all_time') {
        $dateCondition = ''; // No date filtering for all time
    }

    $query = "
        SELECT
            earned_by as agent_name,
            COUNT(*) as review_count
        FROM reviews
        WHERE app_name = :app_name
        AND earned_by IS NOT NULL
        AND earned_by != ''
        AND is_active = TRUE";

    if ($dateCondition) {
        $query .= " AND $dateCondition";
    }

    $query .= "
        GROUP BY earned_by
        ORDER BY review_count DESC, earned_by ASC
    ";

    $stmt = $conn->prepare($query);
    
    $stmt->execute([':app_name' => $appName]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert review_count to integer
    foreach ($result as &$row) {
        $row['review_count'] = (int)$row['review_count'];
    }

    // If no assigned reviews, check if there are unassigned reviews
    if (empty($result)) {
        $totalQuery = "
            SELECT COUNT(*) as total_reviews
            FROM reviews
            WHERE app_name = :app_name
            AND is_active = TRUE";

        if ($dateCondition) {
            $totalQuery .= " AND $dateCondition";
        }

        $totalStmt = $conn->prepare($totalQuery);
        $totalStmt->execute([':app_name' => $appName]);
        $totalReviews = $totalStmt->fetchColumn();

        if ($totalReviews > 0) {
            // There are reviews but none assigned
            header('Content-Type: application/json');
            echo json_encode([
                'message' => 'no_assignments',
                'total_reviews' => (int)$totalReviews,
                'assigned_reviews' => 0,
                'info' => "Found {$totalReviews} reviews for {$appName} but none are assigned to agents yet."
            ]);
            exit;
        }
    }

    header('Content-Type: application/json');
    echo json_encode($result);
    
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Failed to fetch agent statistics: ' . $e->getMessage()
    ]);
}
?>
