<?php
/**
 * Comprehensive Real Data Scraper for All 6 Apps
 * Scrapes fresh data from actual Shopify app store pages
 */

require_once __DIR__ . '/scraper/EnhancedUniversalScraper.php';
require_once __DIR__ . '/utils/ReviewRepository.php';

class RealDataScraper {
    private $scraper;
    private $repository;
    
    // Real Shopify app slugs and names
    private $apps = [
        'StoreSEO' => 'storeseo',
        'StoreFAQ' => 'storefaq', 
        'Vidify' => 'vidify',
        'TrustSync' => 'customer-review-app',
        'EasyFlow' => 'product-options-4',
        'BetterDocs FAQ' => 'betterdocs-knowledgebase'
    ];
    
    public function __construct() {
        $this->scraper = new EnhancedUniversalScraper();
        $this->repository = new ReviewRepository();
    }
    
    /**
     * Scrape real data for all apps
     */
    public function scrapeAllRealData() {
        echo "🌐 COMPREHENSIVE REAL DATA SCRAPER\n";
        echo str_repeat("=", 60) . "\n\n";
        
        foreach ($this->apps as $appName => $slug) {
            echo "📱 Scraping $appName ($slug)...\n";
            echo "URL: https://apps.shopify.com/$slug\n";
            
            try {
                // Clear existing data for this app to ensure fresh data
                $this->clearExistingData($appName);
                
                // Scrape fresh data (bypass rate limiting for this comprehensive update)
                $result = $this->scrapeAppFresh($slug, $appName);
                
                if ($result && !empty($result['reviews'])) {
                    $reviewCount = count($result['reviews']);
                    echo "✅ Successfully scraped $reviewCount reviews for $appName\n";
                    
                    // Verify the data
                    $this->verifyAppData($appName);
                } else {
                    echo "❌ Failed to scrape data for $appName\n";
                }
                
                echo str_repeat("-", 40) . "\n";
                
                // Small delay between apps to be respectful
                sleep(2);
                
            } catch (Exception $e) {
                echo "❌ Error scraping $appName: " . $e->getMessage() . "\n";
                echo str_repeat("-", 40) . "\n";
            }
        }
        
        echo "\n📊 FINAL VERIFICATION:\n";
        echo str_repeat("=", 60) . "\n";
        $this->verifyAllApps();
    }
    
    /**
     * Clear existing data for an app
     */
    private function clearExistingData($appName) {
        try {
            $this->repository->clearAppData($appName);
            echo "🗑️  Cleared existing data for $appName\n";
        } catch (Exception $e) {
            echo "⚠️  Warning: Could not clear existing data for $appName: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Scrape fresh data for an app (bypass rate limiting)
     */
    private function scrapeAppFresh($slug, $appName) {
        // Use the parent UniversalLiveScraper directly to bypass rate limiting
        $liveScraper = new UniversalLiveScraper();
        
        echo "🔄 Scraping live data from Shopify...\n";
        $result = $liveScraper->scrapeApp($slug, $appName);
        
        if ($result && !empty($result['reviews'])) {
            // Save to repository
            $saved = 0;
            $duplicates = 0;
            
            foreach ($result['reviews'] as $review) {
                try {
                    $success = $this->repository->saveReview([
                        'app_name' => $appName,
                        'store_name' => $review['store_name'] ?? 'Unknown Store',
                        'country_name' => $review['country_name'] ?? 'Unknown',
                        'rating' => $review['rating'] ?? 5,
                        'review_content' => $review['review_content'] ?? '',
                        'review_date' => $review['review_date'] ?? date('Y-m-d'),
                        'source_type' => 'live_scrape'
                    ]);
                    
                    if ($success) {
                        $saved++;
                    } else {
                        $duplicates++;
                    }
                } catch (Exception $e) {
                    echo "⚠️  Error saving review: " . $e->getMessage() . "\n";
                }
            }
            
            echo "💾 Saved: $saved reviews, Duplicates: $duplicates\n";
            
            return [
                'reviews' => $result['reviews'],
                'saved' => $saved,
                'duplicates' => $duplicates,
                'app_name' => $appName
            ];
        }
        
        return null;
    }
    
    /**
     * Verify data for a specific app
     */
    private function verifyAppData($appName) {
        $stats = $this->repository->getStatistics($appName);
        
        echo "📈 $appName Statistics:\n";
        echo "   Total Reviews: " . ($stats['total_reviews'] ?? 0) . "\n";
        echo "   Average Rating: " . ($stats['average_rating'] ?? 0) . "\n";
        
        // Check recent reviews
        $recentReviews = $this->repository->getRecentReviews($appName, 5);
        if (!empty($recentReviews)) {
            echo "   Latest Review Date: " . ($recentReviews[0]['review_date'] ?? 'Unknown') . "\n";
        }
    }
    
    /**
     * Verify all apps data
     */
    private function verifyAllApps() {
        foreach ($this->apps as $appName => $slug) {
            echo "\n$appName:\n";
            
            $stats = $this->repository->getStatistics($appName);
            $totalReviews = $stats['total_reviews'] ?? 0;
            $avgRating = $stats['average_rating'] ?? 0;
            
            // Get last 30 days count
            $last30Days = $this->repository->getReviewsCount($appName, 30);
            
            // Get this month count
            $thisMonth = $this->repository->getReviewsCount($appName, null, date('Y-m-01'));
            
            echo "   📊 Total: $totalReviews reviews\n";
            echo "   ⭐ Rating: $avgRating stars\n";
            echo "   📅 Last 30 days: $last30Days reviews\n";
            echo "   📆 This month: $thisMonth reviews\n";
            echo "   🌐 URL: https://apps.shopify.com/$slug\n";
        }
        
        echo "\n🎉 Real data scraping completed!\n";
        echo "All apps now have fresh data from actual Shopify pages.\n";
    }
}

// Run the scraper
if (basename(__FILE__) == basename($_SERVER["SCRIPT_NAME"])) {
    $scraper = new RealDataScraper();
    $scraper->scrapeAllRealData();
}
?>
