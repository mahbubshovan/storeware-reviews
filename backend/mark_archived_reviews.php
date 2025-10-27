<?php
/**
 * Mark Archived Reviews Script
 * Identifies reviews that are no longer on live Shopify pages and marks them as archived
 */

require_once 'config/database.php';
require_once 'utils/ArchiveReviewsManager.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    $manager = new ArchiveReviewsManager($conn);
    
    echo "=== MARKING ARCHIVED REVIEWS ===\n\n";
    
    $results = $manager->syncAllApps();
    
    echo "\n=== SUMMARY ===\n";
    $totalArchived = 0;
    foreach ($results as $app => $result) {
        if (isset($result['archived'])) {
            $totalArchived += $result['archived'];
        }
        echo $app . ': ' . $result['message'] . "\n";
    }
    
    echo "\n✅ Total reviews archived: {$totalArchived}\n";
    
    // Show new counts
    echo "\n=== NEW LIVE COUNTS ===\n";
    $apps = [
        'StoreSEO',
        'StoreFAQ',
        'EasyFlow',
        'TrustSync',
        'BetterDocs FAQ Knowledge Base',
        'Vidify'
    ];
    
    foreach ($apps as $app) {
        $count = $manager->getLiveReviewCount($app);
        echo "$app: $count\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

