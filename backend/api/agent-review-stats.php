<?php
/**
 * Agent Review Statistics API
 * Returns all agents and their review counts across all apps
 * Optionally filters by specific agent
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../utils/DatabaseManager.php';
require_once __DIR__ . '/../utils/DateCalculations.php';

try {
    $dbManager = new DatabaseManager();
    $conn = $dbManager->getConnection();
    
    // Get parameters
    $agentName = $_GET['agent_name'] ?? null;
    $filter = $_GET['filter'] ?? 'last_30_days';
    
    // Determine date condition based on filter
    $dateCondition = '';
    if ($filter === 'last_30_days') {
        $dateCondition = DateCalculations::getLast30DaysCondition();
    } elseif ($filter === 'all_time') {
        $dateCondition = ''; // No date filtering for all time
    }
    
    // If agent_name is provided, get detailed stats for that agent across all apps
    if ($agentName) {
        $query = "
            SELECT
                app_name,
                COUNT(*) as review_count,
                AVG(rating) as average_rating,
                MIN(review_date) as first_review_date,
                MAX(review_date) as last_review_date
            FROM reviews
            WHERE earned_by = :agent_name
            AND is_active = TRUE";
        
        if ($dateCondition) {
            $query .= " AND $dateCondition";
        }
        
        $query .= "
            GROUP BY app_name
            ORDER BY review_count DESC
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([':agent_name' => $agentName]);
        $appStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total stats for this agent
        $totalQuery = "
            SELECT
                COUNT(*) as total_reviews,
                AVG(rating) as average_rating,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star_count,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star_count,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star_count,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star_count,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star_count
            FROM reviews
            WHERE earned_by = :agent_name
            AND is_active = TRUE";
        
        if ($dateCondition) {
            $totalQuery .= " AND $dateCondition";
        }
        
        $totalStmt = $conn->prepare($totalQuery);
        $totalStmt->execute([':agent_name' => $agentName]);
        $totalStats = $totalStmt->fetch(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode([
            'agent_name' => $agentName,
            'total_reviews' => (int)$totalStats['total_reviews'],
            'average_rating' => round((float)$totalStats['average_rating'], 2),
            'rating_distribution' => [
                'five_star' => (int)$totalStats['five_star_count'],
                'four_star' => (int)$totalStats['four_star_count'],
                'three_star' => (int)$totalStats['three_star_count'],
                'two_star' => (int)$totalStats['two_star_count'],
                'one_star' => (int)$totalStats['one_star_count']
            ],
            'by_app' => $appStats
        ]);
        exit;
    }
    
    // Get all agents with their total review counts across all apps
    $query = "
        SELECT
            earned_by as agent_name,
            COUNT(*) as review_count,
            COUNT(DISTINCT app_name) as app_count,
            AVG(rating) as average_rating
        FROM reviews
        WHERE earned_by IS NOT NULL
        AND earned_by != ''
        AND is_active = TRUE";
    
    if ($dateCondition) {
        $query .= " AND $dateCondition";
    }
    
    $query .= "
        GROUP BY earned_by
        ORDER BY review_count DESC, agent_name ASC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert numeric fields
    foreach ($agents as &$agent) {
        $agent['review_count'] = (int)$agent['review_count'];
        $agent['app_count'] = (int)$agent['app_count'];
        $agent['average_rating'] = round((float)$agent['average_rating'], 2);
    }
    
    // If no agents found, return empty array
    if (empty($agents)) {
        header('Content-Type: application/json');
        echo json_encode([
            'message' => 'no_agents',
            'agents' => [],
            'info' => 'No agents have been assigned reviews yet.'
        ]);
        exit;
    }
    
    header('Content-Type: application/json');
    echo json_encode($agents);
    
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Failed to fetch agent statistics: ' . $e->getMessage()
    ]);
}
?>

