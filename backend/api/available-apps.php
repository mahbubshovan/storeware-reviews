<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/apps.php';

try {
    // One list of apps, shared with the Analytics tab's sync and the cron.
    echo json_encode([
        'success' => true,
        'apps' => shopify_app_names(),
        'note' => 'All apps use live data scraping from Shopify App Store'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error'
    ]);
}
?>
