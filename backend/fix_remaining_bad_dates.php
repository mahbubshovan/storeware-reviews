<?php
/**
 * Fix remaining reviews with 1970-01-01 dates by re-scraping the affected apps
 * This script identifies which apps have bad dates and re-scrapes them
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/scraper/EnhancedUniversalScraper.php';

try {
    $conn = getDBConnection();
    
    echo "=== FIXING REMAINING BAD DATES (1970-01-01) ===\n\n";
    
    // Step 1: Find which apps have bad dates
    echo "1. Identifying apps with bad dates...\n";
    $stmt = $conn->prepare("
        SELECT app_name, COUNT(*) as count 
        FROM reviews 
        WHERE review_date = '1970-01-01'
        GROUP BY app_name
        ORDER BY count DESC
    ");
    $stmt->execute();
    $appsWithBadDates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($appsWithBadDates)) {
        echo "   ✅ No apps with bad dates found!\n";
        exit(0);
    }
    
    echo "   Found " . count($appsWithBadDates) . " app(s) with bad dates:\n";
    foreach ($appsWithBadDates as $app) {
        echo "   - {$app['app_name']}: {$app['count']} reviews\n";
    }
    
    // Step 2: Map app names to slugs
    $appMapping = [
        'StoreSEO' => 'storeseo',
        'StoreFAQ' => 'storefaq',
        'EasyFlow' => 'product-options-4',
        'BetterDocs FAQ Knowledge Base' => 'betterdocs-knowledgebase',
        'Vidify' => 'vidify',
        'TrustSync' => 'customer-review-app'
    ];
    
    // Step 3: Re-scrape affected apps
    echo "\n2. Re-scraping affected apps with fixed date parsing...\n";
    
    $scraper = new EnhancedUniversalScraper();
    $fixedCount = 0;
    
    foreach ($appsWithBadDates as $app) {
        $appName = $app['app_name'];
        $appSlug = $appMapping[$appName] ?? null;
        
        if (!$appSlug) {
            echo "   ⚠️  Unknown app slug for: $appName\n";
            continue;
        }
        
        echo "   Scraping $appName ($appSlug)...\n";
        
        try {
            $result = $scraper->scrapeAppWithRateLimit($appSlug, $appName);
            
            if ($result['success']) {
                echo "   ✅ Scraped $appName successfully\n";
                $fixedCount++;
            } else {
                echo "   ❌ Failed to scrape $appName: " . ($result['error'] ?? 'Unknown error') . "\n";
            }
        } catch (Exception $e) {
            echo "   ❌ Error scraping $appName: " . $e->getMessage() . "\n";
        }
        
        // Rate limiting between apps
        sleep(2);
    }
    
    // Step 4: Verify fix
    echo "\n3. Verifying fix...\n";
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count FROM reviews 
        WHERE review_date = '1970-01-01'
    ");
    $stmt->execute();
    $remainingBadDates = $stmt->fetch()['count'];
    
    echo "   Remaining reviews with 1970-01-01: $remainingBadDates\n";
    
    if ($remainingBadDates == 0) {
        echo "   ✅ All bad dates have been fixed!\n";
    } else {
        echo "   ⚠️  Some bad dates remain. They may be from apps that are no longer available.\n";
    }
    
    // Step 5: Sync access_reviews
    echo "\n4. Syncing access_reviews table...\n";
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count FROM reviews 
        WHERE review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ");
    $stmt->execute();
    $recentCount = $stmt->fetch()['count'];
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count FROM access_reviews
    ");
    $stmt->execute();
    $accessCount = $stmt->fetch()['count'];
    
    if ($accessCount < $recentCount) {
        echo "   Syncing missing reviews...\n";
        $stmt = $conn->prepare("
            INSERT INTO access_reviews (app_name, review_date, review_content, country_name, rating, original_review_id)
            SELECT app_name, review_date, review_content, country_name, rating, id
            FROM reviews 
            WHERE review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            AND id NOT IN (SELECT original_review_id FROM access_reviews)
        ");
        $stmt->execute();
        echo "   ✅ Access reviews synced\n";
    }
    
    echo "\n✅ FIX COMPLETED!\n";
    echo "   - Re-scraped $fixedCount app(s)\n";
    echo "   - Remaining bad dates: $remainingBadDates\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

