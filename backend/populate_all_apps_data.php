<?php
/**
 * Comprehensive Data Population Script for All 6 Apps
 * Clears existing data and populates with fresh data from Shopify
 * Target counts: StoreSEO(513), StoreFAQ(96), EasyFlow(308), BetterDocs FAQ(31), Vidify(8), TrustSync(38)
 */

require_once __DIR__ . '/scraper/UniversalLiveScraper.php';
require_once __DIR__ . '/config/database.php';

class ComprehensiveDataPopulator {
    private $scraper;
    private $conn;
    
    // Target apps with their Shopify slugs and expected counts
    private $apps = [
        'StoreSEO' => [
            'slug' => 'storeseo',
            'target' => 513
        ],
        'StoreFAQ' => [
            'slug' => 'storefaq',
            'target' => 96
        ],
        'EasyFlow' => [
            'slug' => 'product-options-4',
            'target' => 308
        ],
        'BetterDocs FAQ Knowledge Base' => [
            'slug' => 'betterdocs-knowledgebase',
            'target' => 31
        ],
        'Vidify' => [
            'slug' => 'vidify',
            'target' => 8
        ],
        'TrustSync' => [
            'slug' => 'customer-review-app',
            'target' => 38
        ]
    ];
    
    public function __construct() {
        $this->scraper = new UniversalLiveScraper();
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Main method to populate all apps
     */
    public function populateAllApps() {
        echo "🚀 COMPREHENSIVE DATA POPULATION FOR ALL 6 APPS\n";
        echo "===============================================\n\n";
        
        $totalStartTime = microtime(true);
        $results = [];
        
        foreach ($this->apps as $appName => $config) {
            echo "📱 Processing: $appName\n";
            echo "   Target: {$config['target']} reviews\n";
            echo "   Slug: {$config['slug']}\n";
            echo "   URL: https://apps.shopify.com/{$config['slug']}/reviews\n\n";
            
            $startTime = microtime(true);
            
            try {
                // Clear existing data first
                $this->clearAppData($appName);
                
                // Scrape fresh data
                $result = $this->scraper->scrapeApp($config['slug'], $appName);
                
                if ($result && $result['success']) {
                    $scrapedCount = $result['count'];
                    $executionTime = round(microtime(true) - $startTime, 2);
                    
                    echo "✅ SUCCESS: $appName\n";
                    echo "   Scraped: $scrapedCount reviews\n";
                    echo "   Target: {$config['target']} reviews\n";
                    echo "   Time: {$executionTime}s\n";
                    
                    // Verify data in database
                    $dbCount = $this->verifyAppData($appName);
                    echo "   DB Count: $dbCount reviews\n";
                    
                    $results[$appName] = [
                        'success' => true,
                        'scraped' => $scrapedCount,
                        'target' => $config['target'],
                        'db_count' => $dbCount,
                        'time' => $executionTime
                    ];
                    
                    if ($dbCount >= ($config['target'] * 0.9)) { // Within 90% of target
                        echo "   🎯 Target achieved!\n";
                    } else {
                        echo "   ⚠️ Below target (got $dbCount, expected {$config['target']})\n";
                    }
                    
                } else {
                    echo "❌ FAILED: $appName\n";
                    if ($result && isset($result['message'])) {
                        echo "   Error: {$result['message']}\n";
                    }
                    
                    $results[$appName] = [
                        'success' => false,
                        'error' => $result['message'] ?? 'Unknown error'
                    ];
                }
                
            } catch (Exception $e) {
                echo "❌ EXCEPTION: $appName - " . $e->getMessage() . "\n";
                $results[$appName] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
            
            echo str_repeat("-", 60) . "\n\n";
            
            // Small delay between apps to be respectful
            sleep(3);
        }
        
        // Final summary
        $totalTime = round(microtime(true) - $totalStartTime, 2);
        $this->printSummary($results, $totalTime);
        
        return $results;
    }
    
    /**
     * Clear existing data for an app (handle foreign key constraints)
     */
    private function clearAppData($appName) {
        echo "🗑️  Clearing existing data for $appName...\n";

        try {
            // First, clear access_reviews table (child table)
            $stmt = $this->conn->prepare('DELETE FROM access_reviews WHERE app_name = ?');
            $stmt->execute([$appName]);
            $accessDeleted = $stmt->rowCount();

            // Then clear reviews table (parent table)
            $stmt = $this->conn->prepare('DELETE FROM reviews WHERE app_name = ?');
            $stmt->execute([$appName]);
            $reviewsDeleted = $stmt->rowCount();

            // Clear from review_repository table
            $stmt = $this->conn->prepare('UPDATE review_repository SET is_active = FALSE WHERE app_name = ?');
            $stmt->execute([$appName]);
            $repoUpdated = $stmt->rowCount();

            echo "   Cleared: $reviewsDeleted from reviews, $repoUpdated from repository, $accessDeleted from access_reviews\n";

        } catch (Exception $e) {
            echo "   ⚠️ Error clearing data: " . $e->getMessage() . "\n";
            echo "   Attempting alternative cleanup method...\n";

            // Alternative: Just mark as inactive instead of deleting
            $stmt = $this->conn->prepare('UPDATE reviews SET is_active = FALSE WHERE app_name = ?');
            $stmt->execute([$appName]);
            $reviewsUpdated = $stmt->rowCount();

            $stmt = $this->conn->prepare('UPDATE review_repository SET is_active = FALSE WHERE app_name = ?');
            $stmt->execute([$appName]);
            $repoUpdated = $stmt->rowCount();

            echo "   Marked inactive: $reviewsUpdated from reviews, $repoUpdated from repository\n";
        }
    }
    
    /**
     * Verify data was saved correctly
     */
    private function verifyAppData($appName) {
        $stmt = $this->conn->prepare('SELECT COUNT(*) as count FROM reviews WHERE app_name = ? AND is_active = TRUE');
        $stmt->execute([$appName]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }
    
    /**
     * Print final summary
     */
    private function printSummary($results, $totalTime) {
        echo "🎯 FINAL SUMMARY\n";
        echo "================\n";
        echo "Total execution time: {$totalTime}s\n\n";
        
        $successful = 0;
        $totalScraped = 0;
        $totalTarget = 0;
        
        foreach ($results as $appName => $result) {
            if ($result['success']) {
                $successful++;
                $totalScraped += $result['db_count'];
                $totalTarget += $result['target'];
                
                $status = $result['db_count'] >= ($result['target'] * 0.9) ? '🎯' : '⚠️';
                echo "$status $appName: {$result['db_count']}/{$result['target']} reviews ({$result['time']}s)\n";
            } else {
                echo "❌ $appName: FAILED - {$result['error']}\n";
            }
        }
        
        echo "\n📊 TOTALS:\n";
        echo "   Successful apps: $successful/6\n";
        echo "   Total reviews scraped: $totalScraped\n";
        echo "   Total target: $totalTarget\n";
        echo "   Success rate: " . round(($totalScraped / $totalTarget) * 100, 1) . "%\n";
        
        if ($successful == 6) {
            echo "\n🎉 ALL APPS SUCCESSFULLY POPULATED!\n";
            echo "   Access Review (Tabs) page is now ready with accurate data.\n";
        } else {
            echo "\n⚠️ Some apps failed. Check the logs above for details.\n";
        }
    }
}

// Run the population script
if (php_sapi_name() === 'cli') {
    $populator = new ComprehensiveDataPopulator();
    $populator->populateAllApps();
} else {
    echo "This script must be run from command line.\n";
}
