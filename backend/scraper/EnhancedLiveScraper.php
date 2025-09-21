<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/UniversalLiveScraper.php';

/**
 * Enhanced Live Scraper with Snapshot Support and Change Detection
 * Extends UniversalLiveScraper with content hashing and etag support
 */
class EnhancedLiveScraper extends UniversalLiveScraper {
    
    /**
     * Scrape app with enhanced snapshot support
     */
    public function scrapeAppWithSnapshot($appSlug, $appName = null) {
        if (!$appName) {
            $appName = ucfirst($appSlug);
        }
        
        error_log("🔄 Enhanced scraping started for {$appName} ({$appSlug})");
        
        $baseUrl = "https://apps.shopify.com/$appSlug/reviews";
        
        // Check for etag/last-modified headers for conditional requests
        $headers = $this->getPageHeaders($baseUrl);
        $etag = $headers['etag'] ?? null;
        $lastModified = $headers['last-modified'] ?? null;
        
        // Scrape all reviews with pagination
        $allReviews = [];
        $thirtyDaysAgo = strtotime('-30 days');
        $thisMonthStart = strtotime(date('Y-m-01'));
        
        for ($page = 1; $page <= 20; $page++) { // Increased page limit
            $url = $baseUrl . "?sort_by=newest&page=$page";
            
            $html = $this->fetchPageWithConditional($url, $etag, $lastModified);
            if (!$html) {
                if ($page === 1) {
                    return ['success' => false, 'error' => 'Failed to fetch first page'];
                }
                break; // No more pages
            }
            
            $pageReviews = $this->parseReviewsFromHTML($html);
            if (empty($pageReviews)) {
                break; // No more reviews
            }
            
            $allReviews = array_merge($allReviews, $pageReviews);
            
            // Stop if we've gone beyond our date range
            $oldestReviewDate = end($pageReviews)['review_date'] ?? null;
            if ($oldestReviewDate && strtotime($oldestReviewDate) < $thirtyDaysAgo) {
                break;
            }
            
            // Rate limiting between requests
            usleep(rand(200000, 500000)); // 200-500ms delay
        }
        
        // Generate stable review IDs and calculate content hash
        $processedReviews = [];
        $reviewHashes = [];
        
        foreach ($allReviews as $review) {
            // Create stable review ID
            $reviewId = $this->generateReviewId($review);
            $review['id'] = $reviewId;
            $review['updated_at'] = $review['review_date']; // Use review_date as updated_at
            
            $processedReviews[] = $review;
            $reviewHashes[] = $reviewId . '_' . $review['updated_at'];
        }
        
        // Sort hashes for consistent content hash
        sort($reviewHashes);
        $contentHash = hash('sha256', implode('|', $reviewHashes));
        
        // Calculate metrics
        $totals = $this->calculateTotals($processedReviews);
        $last30Days = $this->calculateLast30Days($processedReviews);
        $thisMonth = $this->calculateThisMonth($processedReviews);
        $ratingDistribution = $this->calculateRatingDistribution($processedReviews);
        $latestReviews = array_slice($processedReviews, 0, 50); // Latest 50 reviews
        
        return [
            'success' => true,
            'app_slug' => $appSlug,
            'app_name' => $appName,
            'source_url' => $baseUrl,
            'etag' => $etag,
            'last_modified' => $lastModified,
            'content_hash' => $contentHash,
            'reviews' => $processedReviews,
            'totals' => $totals,
            'last30Days' => $last30Days,
            'thisMonth' => $thisMonth,
            'ratingDistribution' => $ratingDistribution,
            'latestReviews' => $latestReviews,
            'scraped_count' => count($processedReviews)
        ];
    }
    
    /**
     * Generate stable review ID from review data
     */
    private function generateReviewId($review) {
        // Use store name + review date + first 50 chars of content as stable ID
        $content = substr($review['review_content'] ?? '', 0, 50);
        $identifier = $review['store_name'] . '_' . $review['review_date'] . '_' . $content;
        return hash('md5', $identifier);
    }
    
    /**
     * Get page headers for conditional requests
     */
    private function getPageHeaders($url) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_NOBODY => true, // HEAD request
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        
        $headerString = curl_exec($ch);
        curl_close($ch);
        
        $headers = [];
        if ($headerString) {
            $headerLines = explode("\n", $headerString);
            foreach ($headerLines as $line) {
                if (strpos($line, ':') !== false) {
                    list($key, $value) = explode(':', $line, 2);
                    $headers[strtolower(trim($key))] = trim($value);
                }
            }
        }
        
        return $headers;
    }
    
    /**
     * Fetch page with conditional request support
     */
    private function fetchPageWithConditional($url, $etag = null, $lastModified = null) {
        $ch = curl_init();
        
        $headers = [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Connection: keep-alive',
        ];
        
        // Add conditional headers if available
        if ($etag) {
            $headers[] = "If-None-Match: $etag";
        }
        if ($lastModified) {
            $headers[] = "If-Modified-Since: $lastModified";
        }
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 304) {
            // Not modified - return null to indicate no changes
            return null;
        }
        
        if ($httpCode !== 200) {
            return false;
        }
        
        return $html;
    }
    
    /**
     * Calculate total metrics
     */
    private function calculateTotals($reviews) {
        $total = count($reviews);
        $ratingSum = array_sum(array_column($reviews, 'rating'));
        $avgRating = $total > 0 ? round($ratingSum / $total, 1) : 0;
        
        return [
            'total_reviews' => $total,
            'average_rating' => $avgRating
        ];
    }
    
    /**
     * Calculate last 30 days metrics
     */
    private function calculateLast30Days($reviews) {
        $thirtyDaysAgo = strtotime('-30 days');
        $recentReviews = array_filter($reviews, function($review) use ($thirtyDaysAgo) {
            return strtotime($review['review_date']) >= $thirtyDaysAgo;
        });
        
        return $this->calculateTotals($recentReviews);
    }
    
    /**
     * Calculate this month metrics
     */
    private function calculateThisMonth($reviews) {
        $thisMonthStart = strtotime(date('Y-m-01'));
        $thisMonthReviews = array_filter($reviews, function($review) use ($thisMonthStart) {
            return strtotime($review['review_date']) >= $thisMonthStart;
        });
        
        return $this->calculateTotals($thisMonthReviews);
    }
    
    /**
     * Calculate rating distribution
     */
    private function calculateRatingDistribution($reviews) {
        $distribution = ['1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0];
        
        foreach ($reviews as $review) {
            $rating = (string)$review['rating'];
            if (isset($distribution[$rating])) {
                $distribution[$rating]++;
            }
        }
        
        return $distribution;
    }
}
