<?php
require_once __DIR__ . '/../config/cors.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../utils/AccessReviewsSync.php';

try {
    $sync = new AccessReviewsSync();
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get date range parameter, default to 30_days
        $dateRange = isset($_GET['date_range']) ? $_GET['date_range'] : '30_days';

        // Get all access reviews with date filtering
        $reviews = $sync->getAccessReviews($dateRange);
        $stats = $sync->getAccessReviewsStats($dateRange);

        echo json_encode([
            'success' => true,
            'reviews' => $reviews,
            'stats' => $stats
        ]);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Update earned_by field
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['review_id']) || !isset($input['earned_by'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Missing review_id or earned_by parameter'
            ]);
            exit;
        }
        
        $result = $sync->updateEarnedBy($input['review_id'], $input['earned_by']);
        echo json_encode($result);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Trigger sync
        $sync->syncAccessReviews();
        
        echo json_encode([
            'success' => true,
            'message' => 'Access reviews synchronized successfully'
        ]);
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
