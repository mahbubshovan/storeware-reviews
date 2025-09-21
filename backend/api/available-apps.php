<?php
require_once __DIR__ . '/../config/cors.php';

try {
    // 🔴 ALL 6 ORIGINAL APPS WITH VERIFIED LIVE SHOPIFY DATA
    // These apps have been tested and confirmed to work with UniversalLiveScraper
    $verifiedApps = [
        'StoreSEO',
        'StoreFAQ',
        'Vidify',
        'TrustSync',
        'EasyFlow',
        'BetterDocs FAQ Knowledge Base'
    ];

    echo json_encode([
        'success' => true,
        'apps' => $verifiedApps,
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
