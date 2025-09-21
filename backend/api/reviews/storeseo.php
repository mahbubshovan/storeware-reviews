<?php
/**
 * Compatibility endpoint for /api/reviews/storeseo
 * Provides fallback response until full migration is complete
 */

require_once __DIR__ . '/../../config/cors.php';

// Simple fallback response
header('Content-Type: application/json');

$clientId = $_GET['client_id'] ?? null;

if (!$clientId) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'client_id parameter is required'
    ]);
    exit;
}

// Return a basic response that indicates the new API is not fully implemented yet
echo json_encode([
    'app' => 'storeseo',
    'status' => 'fallback',
    'data' => null,
    'scrape' => [
        'allowed_now' => true,
        'next_run_at' => null,
        'last_run_at' => null,
        'remaining_seconds' => 0,
        'has_upstream_changes' => false
    ]
]);
?>
