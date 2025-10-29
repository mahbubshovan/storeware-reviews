<?php
/**
 * StoreSEO Data Fix Endpoint
 * Diagnoses and fixes StoreSEO review data
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $action = $_GET['action'] ?? 'diagnose';
    
    if ($action === 'diagnose') {
        // Diagnose current state
        $stmt = $conn->prepare('SELECT COUNT(*) FROM reviews WHERE app_name = ?');
        $stmt->execute(['StoreSEO']);
        $mainCount = $stmt->fetchColumn();
        
        $stmt = $conn->prepare('SELECT COUNT(*) FROM access_reviews WHERE app_name = ?');
        $stmt->execute(['StoreSEO']);
        $accessCount = $stmt->fetchColumn();
        
        $stmt = $conn->prepare('SELECT COUNT(*) FROM reviews WHERE app_name = ? AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)');
        $stmt->execute(['StoreSEO']);
        $last30Count = $stmt->fetchColumn();
        
        echo json_encode([
            'success' => true,
            'action' => 'diagnose',
            'main_reviews' => $mainCount,
            'access_reviews' => $accessCount,
            'last_30_days' => $last30Count
        ]);
        
    } elseif ($action === 'sync_access') {
        // Sync access_reviews from main reviews table
        $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
        $conn->prepare("DELETE FROM access_reviews WHERE app_name = 'StoreSEO'")->execute();
        $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
        
        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
        $stmt = $conn->prepare("
            INSERT INTO access_reviews (app_name, review_date, review_content, country_name, rating, original_review_id)
            SELECT app_name, review_date, review_content, country_name, rating, id
            FROM reviews
            WHERE app_name = 'StoreSEO' AND review_date >= ?
        ");
        $stmt->execute([$thirtyDaysAgo]);
        
        $stmt = $conn->prepare('SELECT COUNT(*) FROM access_reviews WHERE app_name = ?');
        $stmt->execute(['StoreSEO']);
        $newCount = $stmt->fetchColumn();
        
        echo json_encode([
            'success' => true,
            'action' => 'sync_access',
            'synced_count' => $newCount,
            'message' => "Synced $newCount reviews to access_reviews"
        ]);
        
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Unknown action',
            'available_actions' => ['diagnose', 'sync_access']
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>

