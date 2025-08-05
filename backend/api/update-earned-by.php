<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../utils/AccessReviewsSync.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode([
            'success' => false,
            'message' => 'Only POST method allowed'
        ]);
        exit;
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['review_id']) || !isset($input['earned_by'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing review_id or earned_by parameter'
        ]);
        exit;
    }

    $reviewId = $input['review_id'];
    $earnedBy = trim($input['earned_by']);

    // Initialize the sync class
    $sync = new AccessReviewsSync();
    
    // Update the earned_by field
    $result = $sync->updateEarnedBy($reviewId, $earnedBy);

    // The updateEarnedBy method returns an array with success and message
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
