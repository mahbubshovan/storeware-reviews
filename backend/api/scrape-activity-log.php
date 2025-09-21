<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get query parameters
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = min(100, max(10, intval($_GET['limit'] ?? 20)));
    $appName = $_GET['app'] ?? null;
    $action = $_GET['action'] ?? null;
    $ipAddress = $_GET['ip'] ?? null;
    
    $offset = ($page - 1) * $limit;
    
    // Build WHERE clause
    $whereConditions = [];
    $params = [];
    
    if ($appName) {
        $whereConditions[] = "app_name = ?";
        $params[] = $appName;
    }
    
    if ($action) {
        $whereConditions[] = "action = ?";
        $params[] = $action;
    }
    
    if ($ipAddress) {
        $whereConditions[] = "ip_address = ?";
        $params[] = $ipAddress;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM scrape_activity_log $whereClause";
    $countStmt = $conn->prepare($countSql);
    $countStmt->execute($params);
    $totalItems = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get paginated results
    $sql = "
        SELECT 
            id,
            ip_address,
            app_name,
            action,
            message,
            timestamp
        FROM scrape_activity_log 
        $whereClause
        ORDER BY timestamp DESC 
        LIMIT $limit OFFSET $offset
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate pagination info
    $totalPages = ceil($totalItems / $limit);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'logs' => $logs,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalItems,
                'items_per_page' => $limit,
                'has_next_page' => $page < $totalPages,
                'has_prev_page' => $page > 1
            ]
        ],
        'filters' => [
            'app_name' => $appName,
            'action' => $action,
            'ip_address' => $ipAddress
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
