<?php
/**
 * Restore Apps Data
 * Re-scrapes apps that were cleared due to rate limiting
 */

require_once 'config/database.php';
require_once 'scraper/UniversalLiveScraper.php';

try {
    $apps = [
        'StoreSEO' => 'storeseo',
        'StoreFAQ' => 'storefaq',
        'EasyFlow' => 'product-options-4',
        'TrustSync' => 'customer-review-app',
        'BetterDocs FAQ Knowledge Base' => 'betterdocs-knowledgebase',
        'Vidify' => 'vidify',
    ];

    echo "=== RESTORING APPS DATA ===\n\n";

    $scraper = new UniversalLiveScraper();

    foreach ($apps as $appName => $appSlug) {
        echo "📱 Restoring $appName...\n";
        
        $result = $scraper->scrapeApp($appSlug, $appName);

        if ($result && isset($result['count'])) {
            echo "✅ Restored $appName with {$result['count']} reviews\n\n";
        } else {
            echo "❌ Failed to restore $appName\n\n";
        }
        
        // Wait between apps to avoid rate limiting
        sleep(5);
    }

    echo "✅ Restoration complete!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

