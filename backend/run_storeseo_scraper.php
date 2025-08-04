<?php
/**
 * Production script to run StoreSEO real-time scraper
 * Usage: php backend/run_storeseo_scraper.php
 */

require_once __DIR__ . '/StoreSEORealtimeScraper.php';

echo "🚀 STORESEO REAL-TIME SCRAPER\n";
echo str_repeat("=", 50) . "\n";

try {
    $scraper = new StoreSEORealtimeScraper();
    $results = $scraper->scrapeRealtimeReviews();
    
    if ($results) {
        echo "\n✅ SCRAPING COMPLETED SUCCESSFULLY!\n";
        echo "This Month: {$results['this_month']} reviews\n";
        echo "Last 30 Days: {$results['last_30_days']} reviews\n";
        echo "Total Stored: {$results['total_stored']} reviews\n";
        echo "Date Range: {$results['date_range']['earliest']} to {$results['date_range']['latest']}\n";
        echo "\n🎯 StoreSEO data updated successfully!\n";
    } else {
        echo "❌ SCRAPING FAILED\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

echo str_repeat("=", 50) . "\n";
?>
