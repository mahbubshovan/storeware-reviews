<?php

require_once __DIR__ . '/../utils/IPRateLimitManager.php';
require_once __DIR__ . '/../utils/ReviewRepository.php';
require_once __DIR__ . '/UniversalLiveScraper.php';

/**
 * Enhanced Universal Scraper with IP-based rate limiting and improved error handling
 */
class EnhancedUniversalScraper extends UniversalLiveScraper {
    private $rateLimitManager;
    private $repository;
    private $maxRetries = 3;
    private $baseDelay = 2; // Base delay in seconds
    private $maxDelay = 30; // Maximum delay in seconds
    
    public function __construct() {
        parent::__construct();
        $this->rateLimitManager = new IPRateLimitManager();
        $this->repository = new ReviewRepository();
    }
    
    /**
     * Scrape app with rate limiting and error handling
     */
    public function scrapeAppWithRateLimit($appSlug, $appName = null) {
        if (!$appName) {
            $appName = ucfirst($appSlug);
        }
        
        $clientIP = $this->rateLimitManager->getClientIP();
        echo "🔍 Checking rate limits for IP: $clientIP, App: $appName\n";
        
        // Check if IP can scrape
        if (!$this->rateLimitManager->canScrape($appName)) {
            $remainingTime = $this->rateLimitManager->getRemainingCooldown($appName);
            $hours = floor($remainingTime / 3600);
            $minutes = floor(($remainingTime % 3600) / 60);
            
            echo "⏳ Rate limit active. Returning cached data. Cooldown: {$hours}h {$minutes}m remaining\n";
            
            // Return cached data from repository
            return $this->getCachedData($appName);
        }
        
        echo "✅ Rate limit check passed. Proceeding with scraping...\n";
        
        // Record the scraping attempt
        $this->rateLimitManager->recordScrape($appName);
        
        // Perform scraping with enhanced error handling
        return $this->scrapeWithRetry($appSlug, $appName);
    }
    
    /**
     * Scrape with retry mechanism and exponential backoff
     */
    private function scrapeWithRetry($appSlug, $appName) {
        $attempt = 1;
        $delay = $this->baseDelay;
        
        while ($attempt <= $this->maxRetries) {
            echo "🔄 Scraping attempt $attempt/$this->maxRetries for $appName\n";
            
            try {
                $result = $this->performScraping($appSlug, $appName);
                
                if ($result && !empty($result['reviews'])) {
                    echo "✅ Scraping successful on attempt $attempt\n";
                    return $result;
                }
                
                if ($attempt < $this->maxRetries) {
                    echo "⚠️ Attempt $attempt failed, retrying in {$delay}s...\n";
                    sleep($delay);
                    $delay = min($delay * 2, $this->maxDelay); // Exponential backoff
                }
                
            } catch (Exception $e) {
                echo "❌ Scraping error on attempt $attempt: " . $e->getMessage() . "\n";
                
                if ($attempt < $this->maxRetries) {
                    echo "🔄 Retrying in {$delay}s...\n";
                    sleep($delay);
                    $delay = min($delay * 2, $this->maxDelay);
                }
            }
            
            $attempt++;
        }
        
        echo "❌ All scraping attempts failed. Returning cached data.\n";
        return $this->getCachedData($appName);
    }
    
    /**
     * Perform the actual scraping with enhanced error handling
     */
    private function performScraping($appSlug, $appName) {
        $baseUrl = "https://apps.shopify.com/$appSlug/reviews";
        
        // Try scraping recent reviews first
        $result = $this->scrapeApp($appSlug, $appName);
        
        if (!$result || empty($result['reviews'])) {
            echo "🔄 No recent reviews found, trying historical scraping...\n";
            
            // Fallback to historical scraping
            $allReviews = $this->scrapeAllReviews($baseUrl, $appName);
            
            if (!empty($allReviews)) {
                // Save to repository
                $this->saveReviewsToRepository($allReviews, $appName);
                
                // Return formatted result
                return [
                    'reviews' => $allReviews,
                    'total_reviews' => count($allReviews),
                    'app_name' => $appName,
                    'scraped_at' => date('Y-m-d H:i:s'),
                    'source' => 'historical_scrape'
                ];
            }
        }
        
        return $result;
    }
    
    /**
     * Enhanced fetchPage with better error handling and delays
     */
    protected function fetchPageEnhanced($url, $attempt = 1) {
        // Add random delay to avoid being detected as bot
        $randomDelay = rand(1, 3);
        sleep($randomDelay);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 45, // Increased timeout
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_USERAGENT => $this->getRandomUserAgent(),
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Accept-Encoding: gzip, deflate',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1',
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_ENCODING => '', // Enable compression
        ]);
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL Error: $error");
        }
        
        if ($httpCode === 429) {
            $backoffTime = min(pow(2, $attempt) * 5, 60); // Exponential backoff, max 60s
            echo "⚠️ Rate limited (429). Backing off for {$backoffTime}s...\n";
            sleep($backoffTime);
            throw new Exception("HTTP 429 - Rate Limited");
        }
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP Error: $httpCode");
        }
        
        return $html;
    }
    
    /**
     * Get random user agent to avoid detection
     */
    private function getRandomUserAgent() {
        $userAgents = [
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0'
        ];
        
        return $userAgents[array_rand($userAgents)];
    }
    
    /**
     * Get cached data from repository
     */
    private function getCachedData($appName) {
        try {
            $filters = ['app_name' => $appName];
            $reviews = $this->repository->getPaginatedReviews(1, 100, $filters); // Get first 100 reviews
            $stats = $this->repository->getStatistics($appName);

            return [
                'reviews' => $reviews['reviews'] ?? [],
                'total_reviews' => $stats['total_reviews'] ?? 0,
                'app_name' => $appName,
                'cached_at' => date('Y-m-d H:i:s'),
                'source' => 'cached_data',
                'rate_limited' => true
            ];

        } catch (Exception $e) {
            echo "⚠️ Error getting cached data: " . $e->getMessage() . "\n";
            return [
                'reviews' => [],
                'total_reviews' => 0,
                'app_name' => $appName,
                'error' => 'Failed to get cached data',
                'source' => 'error'
            ];
        }
    }
    
    /**
     * Save reviews to repository
     */
    private function saveReviewsToRepository($reviews, $appName) {
        $saved = 0;
        $duplicates = 0;
        
        foreach ($reviews as $review) {
            try {
                $this->repository->addReview(
                    $appName,
                    $review['store_name'],
                    $review['country_name'],
                    $review['rating'],
                    $review['review_content'],
                    $review['review_date'],
                    'live_scrape'
                );
                $saved++;
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $duplicates++;
                } else {
                    echo "⚠️ Error saving review: " . $e->getMessage() . "\n";
                }
            }
        }
        
        echo "💾 Saved $saved new reviews, $duplicates duplicates skipped\n";
    }
    
    /**
     * Get rate limit status for an IP
     */
    public function getRateLimitStatus($appName = null) {
        $clientIP = $this->rateLimitManager->getClientIP();
        $canScrape = $this->rateLimitManager->canScrape($appName);
        $remainingTime = $this->rateLimitManager->getRemainingCooldown($appName);
        
        return [
            'ip_address' => $clientIP,
            'can_scrape' => $canScrape,
            'remaining_cooldown_seconds' => $remainingTime,
            'remaining_cooldown_formatted' => $this->formatCooldownTime($remainingTime),
            'app_name' => $appName
        ];
    }
    
    /**
     * Format cooldown time in human readable format
     */
    private function formatCooldownTime($seconds) {
        if ($seconds <= 0) {
            return 'No cooldown';
        }
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }
}
?>
