<?php
/**
 * CLI Script to Scrape StoreSEO Reviews
 * Run: php cli_scrape_storeseo.php
 */

set_time_limit(600); // 10 minutes
ini_set('max_execution_time', 600);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/scraper/UniversalLiveScraper.php';

echo "\n";
echo "╔════════════════════════════════════════╗\n";
echo "║  STORESEO COMPREHENSIVE SCRAPER        ║\n";
echo "║  Direct CLI Execution                  ║\n";
echo "╚════════════════════════════════════════╝\n\n";

try {
    // Create scraper
    $scraper = new UniversalLiveScraper();
    
    // Scrape StoreSEO
    echo "Starting scrape for StoreSEO...\n\n";
    $result = $scraper->scrapeApp('storeseo', 'StoreSEO');
    
    echo "\n\n";
    echo "╔════════════════════════════════════════╗\n";
    echo "║  SCRAPING RESULTS                      ║\n";
    echo "╚════════════════════════════════════════╝\n\n";
    
    if ($result['success']) {
        echo "✅ SUCCESS\n";
        echo "   Message: {$result['message']}\n";
        echo "   Count: {$result['count']}\n";
    } else {
        echo "❌ FAILED\n";
        echo "   Message: {$result['message']}\n";
        echo "   Count: {$result['count']}\n";
    }
    
    // Check database
    echo "\n\n";
    echo "╔════════════════════════════════════════╗\n";
    echo "║  DATABASE VERIFICATION                ║\n";
    echo "╚════════════════════════════════════════╝\n\n";
    
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare('SELECT COUNT(*) FROM reviews WHERE app_name = ?');
    $stmt->execute(['StoreSEO']);
    $mainCount = $stmt->fetchColumn();
    
    $stmt = $conn->prepare('SELECT COUNT(*) FROM access_reviews WHERE app_name = ?');
    $stmt->execute(['StoreSEO']);
    $accessCount = $stmt->fetchColumn();
    
    $stmt = $conn->prepare('SELECT COUNT(*) FROM reviews WHERE app_name = ? AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)');
    $stmt->execute(['StoreSEO']);
    $last30Count = $stmt->fetchColumn();
    
    echo "Main reviews table: $mainCount\n";
    echo "Access reviews table: $accessCount\n";
    echo "Last 30 days: $last30Count\n";
    
    echo "\n✅ Scraping complete!\n\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
?>

