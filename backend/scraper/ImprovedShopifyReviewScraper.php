<?php
/**
 * Improved Shopify Review Scraper with Smart Caching
 * Fixes review count discrepancy and implements 12-hour caching
 */

require_once __DIR__ . '/../config/database.php';

class ImprovedShopifyReviewScraper {
    
    private $conn;
    private $appUrls = [
        'StoreSEO' => 'https://apps.shopify.com/storeseo/reviews?sort_by=newest&show_archived=false',
        'StoreFAQ' => 'https://apps.shopify.com/storefaq/reviews?sort_by=newest&show_archived=false',
        'EasyFlow' => 'https://apps.shopify.com/product-options-4/reviews?sort_by=newest&show_archived=false',
        'TrustSync' => 'https://apps.shopify.com/customer-review-app/reviews?sort_by=newest&show_archived=false',
        'BetterDocs FAQ Knowledge Base' => 'https://apps.shopify.com/betterdocs-knowledgebase/reviews?sort_by=newest&show_archived=false',
        'Vidify' => 'https://apps.shopify.com/vidify/reviews?sort_by=newest&show_archived=false'
    ];
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->createCacheTable();
    }
    
    /**
     * Create cache table for storing scraped data with timestamps and IP tracking
     */
    private function createCacheTable() {
        $sql = "CREATE TABLE IF NOT EXISTS review_cache (
            id INT AUTO_INCREMENT PRIMARY KEY,
            app_name VARCHAR(255) NOT NULL,
            cache_data LONGTEXT NOT NULL,
            total_reviews INT NOT NULL,
            client_ip VARCHAR(45) DEFAULT '0.0.0.0',
            scraped_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NOT NULL,
            INDEX idx_app_expires (app_name, expires_at),
            INDEX idx_app_ip_expires (app_name, client_ip, expires_at)
        )";

        $this->conn->exec($sql);

        // Add client_ip column if it doesn't exist (for existing databases)
        try {
            $this->conn->exec("ALTER TABLE review_cache ADD COLUMN client_ip VARCHAR(45) DEFAULT '0.0.0.0' AFTER total_reviews");
        } catch (Exception $e) {
            // Column already exists, ignore error
        }
    }
    
    /**
     * Get reviews with smart caching (12-hour IP-based cache)
     * Uses incremental scraping to only check first 3 pages for new reviews
     * Much faster than full scraping - only takes 10-15 seconds
     * @param string $appName The app name to scrape
     * @param bool $forceFresh Force fresh scraping, ignore cache
     * @param string $clientIP Client IP address for IP-based caching
     */
    public function getReviewsWithCaching($appName, $forceFresh = false, $clientIP = null) {
        // Check if we have valid cached data (unless forcing fresh)
        if (!$forceFresh) {
            $cachedData = $this->getCachedData($appName);

            if ($cachedData) {
                return [
                    'success' => true,
                    'data' => json_decode($cachedData['cache_data'], true),
                    'total_reviews' => $cachedData['total_reviews'],
                    'cache_status' => 'hit',
                    'scraped_at' => $cachedData['scraped_at'],
                    'expires_at' => $cachedData['expires_at']
                ];
            }
        }

        // Check if database already has reviews for this app
        $dbCount = $this->getReviewCountFromDatabase($appName);

        if ($dbCount > 0) {
            // Database has reviews, use incremental scraping (only check first 3 pages for new reviews)
            // This is much faster than full scraping and preserves existing data
            $incrementalScraper = new \IncrementalShopifyReviewScraper();
            $incrementalResult = $incrementalScraper->scrapeRecentReviewsOnly($appName, true);

            if ($incrementalResult['success']) {
                // Add new reviews to database
                $addResult = $incrementalScraper->addNewReviewsToDatabase($appName, $incrementalResult['new_reviews']);

                // Get all reviews from database (including existing ones)
                $allReviews = $this->getAllReviewsFromDatabase($appName);

                // Cache the data for 12 hours with client IP tracking
                $this->cacheData($appName, $allReviews, count($allReviews), $clientIP);

                return [
                    'success' => true,
                    'data' => $allReviews,
                    'total_reviews' => count($allReviews),
                    'cache_status' => 'miss',
                    'new_reviews_added' => $incrementalResult['new_reviews_count'],
                    'scraped_at' => date('Y-m-d H:i:s'),
                    'expires_at' => date('Y-m-d H:i:s', strtotime('+12 hours'))
                ];
            }
        } else {
            // Database is empty, do a full scrape to populate it
            $scrapedData = $this->scrapeAllReviews($appName, true);

            if ($scrapedData['success']) {
                // Cache the data for 12 hours with client IP tracking
                $this->cacheData($appName, $scrapedData['reviews'], $scrapedData['total_count'], $clientIP);

                return [
                    'success' => true,
                    'data' => $scrapedData['reviews'],
                    'total_reviews' => $scrapedData['total_count'],
                    'cache_status' => 'miss',
                    'scraped_at' => date('Y-m-d H:i:s'),
                    'expires_at' => date('Y-m-d H:i:s', strtotime('+12 hours'))
                ];
            }
        }

        return [
            'success' => false,
            'error' => 'Failed to scrape reviews',
            'data' => []
        ];
    }

    /**
     * Get review count from database
     */
    private function getReviewCountFromDatabase($appName) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM reviews WHERE app_name = ?");
        $stmt->execute([$appName]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /**
     * Get all reviews for an app from database
     */
    private function getAllReviewsFromDatabase($appName) {
        $stmt = $this->conn->prepare("
            SELECT
                id, app_name, store_name, country_name, rating,
                review_content, review_date, earned_by, created_at, updated_at
            FROM reviews
            WHERE app_name = ? AND is_active = TRUE
            ORDER BY review_date DESC
        ");

        $stmt->execute([$appName]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get cached data if still valid (within 12 hours)
     */
    private function getCachedData($appName) {
        $stmt = $this->conn->prepare("
            SELECT cache_data, total_reviews, scraped_at, expires_at 
            FROM review_cache 
            WHERE app_name = ? AND expires_at > NOW() 
            ORDER BY scraped_at DESC 
            LIMIT 1
        ");
        
        $stmt->execute([$appName]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cache scraped data for 12 hours with IP tracking
     */
    private function cacheData($appName, $reviews, $totalCount, $clientIP = null) {
        // Remove old cache entries for this app
        $deleteStmt = $this->conn->prepare("DELETE FROM review_cache WHERE app_name = ?");
        $deleteStmt->execute([$appName]);

        // Insert new cache entry with client IP for IP-based caching
        $insertStmt = $this->conn->prepare("
            INSERT INTO review_cache (app_name, cache_data, total_reviews, client_ip, expires_at)
            VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 12 HOUR))
        ");

        $insertStmt->execute([
            $appName,
            json_encode($reviews),
            $totalCount,
            $clientIP ?? '0.0.0.0'
        ]);
    }
    
    /**
     * Scrape all reviews with improved accuracy
     * IMPORTANT: Scrapes ALL reviews, not just up to target count
     * This ensures all reviews are captured and persisted in the database
     */
    public function scrapeAllReviews($appName, $silent = false) {
        if (!isset($this->appUrls[$appName])) {
            return [
                'success' => false,
                'error' => "App '$appName' not found in supported apps",
                'reviews' => []
            ];
        }

        $baseUrl = $this->appUrls[$appName];
        $allReviews = [];
        $seenReviewIds = []; // Track unique reviews to avoid duplicates

        if (!$silent) echo "🚀 Starting improved scraping for $appName...\n";

        // Set exact target counts based on Shopify pages (excluding archived reviews)
        // Updated: 2025-10-24 - All counts verified from live Shopify app store pages
        $targetCounts = [
            'StoreSEO' => 526,
            'StoreFAQ' => 106,
            'EasyFlow' => 318,
            'TrustSync' => 41,
            'BetterDocs FAQ Knowledge Base' => 35,
            'Vidify' => 8
        ];

        $maxPages = 100; // Increased to scrape all pages
        $targetCount = $targetCounts[$appName] ?? 999999;

        for ($page = 1; $page <= $maxPages; $page++) {
            $url = $baseUrl . "&page=$page";
            if (!$silent) echo "📄 Scraping page $page...\n";

            $html = $this->fetchPage($url);
            if (!$html) {
                if (!$silent) echo "❌ Failed to fetch page $page - stopping\n";
                break;
            }

            $pageReviews = $this->parseReviewsFromHTML($html, $appName);

            if (empty($pageReviews)) {
                if (!$silent) echo "⚠️ No reviews found on page $page - end reached\n";
                break;
            }

            // Filter out duplicate reviews - DO NOT STOP AT TARGET COUNT
            // We need to scrape ALL reviews to ensure complete data
            $newReviews = [];
            foreach ($pageReviews as $review) {
                $reviewId = $this->generateReviewId($review);
                if (!isset($seenReviewIds[$reviewId])) {
                    $seenReviewIds[$reviewId] = true;
                    $newReviews[] = $review;
                }
            }

            $allReviews = array_merge($allReviews, $newReviews);

            if (!$silent) {
                echo "✅ Page $page: Found " . count($pageReviews) . " reviews (" . count($newReviews) . " new, " . (count($pageReviews) - count($newReviews)) . " duplicates)\n";
                echo "📊 Total unique reviews so far: " . count($allReviews) . "\n";
            }

            // Add delay to be respectful to Shopify servers
            usleep(2000000); // 2.0 second delay
        }

        if (!$silent) echo "🎯 Final count for $appName: " . count($allReviews) . " unique reviews\n";

        return [
            'success' => true,
            'reviews' => $allReviews,
            'total_count' => count($allReviews)
        ];
    }
    
    /**
     * Generate unique ID for review to detect duplicates
     */
    private function generateReviewId($review) {
        return md5($review['store_name'] . '|' . $review['review_content'] . '|' . $review['review_date']);
    }
    
    /**
     * Fetch page with improved error handling and automatic retry
     */
    private function fetchPage($url, $maxRetries = 3) {
        $attempt = 0;

        while ($attempt < $maxRetries) {
            $attempt++;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $html = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            // Success - return the HTML
            if ($httpCode === 200 && $html && !$error) {
//                if ($attempt > 1) {
//                    echo "✅ Success on attempt $attempt\n";
//                }
                return $html;
            }

//            // Failed - log and retry if attempts remaining
//            echo "❌ Attempt $attempt failed: HTTP $httpCode" . ($error ? ", Error: $error" : "") . "\n";

            if ($attempt < $maxRetries) {
                // Exponential backoff: 2s, 4s, 8s
                $waitTime = pow(2, $attempt);
//                echo "⏳ Waiting {$waitTime}s before retry...\n";
                sleep($waitTime);
            }
        }

//        // All retries exhausted
//        echo "❌ Failed after $maxRetries attempts\n";
        return false;
    }
    
    /**
     * Parse reviews from HTML with improved extraction
     */
    private function parseReviewsFromHTML($html, $appName) {
        $reviews = [];

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        // Find review containers using the correct selector
        $reviewNodes = $xpath->query('//div[@data-review-content-id]');

        foreach ($reviewNodes as $reviewNode) {
            $review = $this->extractReviewData($xpath, $reviewNode, $appName);
            if ($review) {
                $reviews[] = $review;
            }
        }

        return $reviews;
    }
    
    /**
     * Extract individual review data with improved accuracy
     */
    private function extractReviewData($xpath, $node, $appName) {
        try {
            // Extract store name - updated selector for new Shopify structure
            $storeNodes = $xpath->query('.//div[contains(@class, "tw-text-heading-xs") and contains(@class, "tw-text-fg-primary")]', $node);
            $storeName = '';
            if ($storeNodes->length > 0) {
                $storeName = trim($storeNodes->item(0)->textContent);
            }

            // Extract rating - updated selector for new Shopify structure
            $ratingNodes = $xpath->query('.//div[@aria-label and @role="img"]/@aria-label', $node);
            $rating = 0;
            if ($ratingNodes->length > 0) {
                $ariaLabel = $ratingNodes->item(0)->textContent;
                if (preg_match('/(\d+(?:\.\d+)?)\s+out\s+of\s+5\s+stars/', $ariaLabel, $matches)) {
                    $rating = (int)round(floatval($matches[1]));
                }
            }

            // Extract review date - updated selector for new Shopify structure
            $dateNodes = $xpath->query('.//div[contains(@class, "tw-text-body-xs") and contains(@class, "tw-text-fg-tertiary")]', $node);
            $reviewDate = '';
            if ($dateNodes->length > 0) {
                $dateText = trim($dateNodes->item(0)->textContent);
                // Parse date like "September 11, 2025" or "Edited October 6, 2025"
                $reviewDate = $this->parseReviewDateSafely($dateText);
            }

            // Extract review content - this selector is working
            $contentNodes = $xpath->query('.//p[contains(@class, "tw-break-words")]', $node);
            $reviewContent = '';
            if ($contentNodes->length > 0) {
                $reviewContent = trim($contentNodes->item(0)->textContent);
            }

            // Extract country using comprehensive country extraction
            $countryName = $this->extractCountryFromNode($xpath, $node);

            // Validate required fields
            if (empty($storeName) || empty($reviewDate) || $rating === 0) {
                // Skip invalid reviews silently in API mode
                return null;
            }

            return [
                'app_name' => $appName,
                'store_name' => $storeName,
                'country_name' => $countryName,
                'rating' => $rating,
                'review_content' => $reviewContent,
                'review_date' => $reviewDate,
                'earned_by' => null,
                'is_active' => true
            ];

        } catch (Exception $e) {
            echo "⚠️ Error extracting review: " . $e->getMessage() . "\n";
            return null;
        }
    }

    /**
     * Extract country from review node using comprehensive strategies
     */
    private function extractCountryFromNode($xpath, $node) {
        // Strategy 1: Look for country in specific merchant info elements
        $countrySelectors = [
            './/div[contains(@class, "tw-text-body-xs") and contains(@class, "tw-text-fg-tertiary")]',
            './/span[contains(@class, "tw-text-body-xs") and contains(@class, "tw-text-fg-tertiary")]',
            './/div[contains(@class, "merchant-location")]',
            './/span[contains(@class, "merchant-location")]',
            './/div[contains(@class, "merchant-country")]',
            './/span[contains(@class, "merchant-country")]',
            './/div[contains(@class, "tw-text-fg-tertiary")]',
            './/span[contains(@class, "tw-text-fg-tertiary")]'
        ];

        foreach ($countrySelectors as $selector) {
            $nodes = $xpath->query($selector, $node);
            foreach ($nodes as $countryNode) {
                $text = trim($countryNode->textContent);
                $country = $this->validateAndNormalizeCountry($text);
                if ($country !== 'Unknown') {
                    return $country;
                }
            }
        }

        // Strategy 2: Look in all text nodes for country patterns
        $allText = $node->textContent;
        $lines = preg_split('/\n|\r\n?/', $allText);

        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line) && strlen($line) < 50) {
                $country = $this->validateAndNormalizeCountry($line);
                if ($country !== 'Unknown') {
                    return $country;
                }
            }
        }

        return 'Unknown';
    }

    /**
     * Validate and normalize country name
     */
    private function validateAndNormalizeCountry($text) {
        if (empty($text) || strlen($text) < 2 || strlen($text) > 50) {
            return 'Unknown';
        }

        // Skip obvious non-country text
        if (preg_match('/\d{4}|using|replied|helpful|ago|since|days?|weeks?|months?|years?|hours?/i', $text)) {
            return 'Unknown';
        }

        return $this->normalizeCountry($text);
    }

    /**
     * Normalize country name to standard format
     */
    private function normalizeCountry($country) {
        $country = trim($country);

        // Comprehensive list of real countries
        $realCountries = [
            'United States', 'Canada', 'United Kingdom', 'Australia', 'Germany', 'France', 'Italy', 'Spain',
            'Netherlands', 'Belgium', 'Switzerland', 'Austria', 'Sweden', 'Norway', 'Denmark', 'Finland',
            'Poland', 'Czech Republic', 'Hungary', 'Romania', 'Bulgaria', 'Croatia', 'Slovenia', 'Slovakia',
            'Lithuania', 'Latvia', 'Estonia', 'Ireland', 'Portugal', 'Greece', 'Turkey', 'Russia',
            'Ukraine', 'Belarus', 'Moldova', 'Serbia', 'Bosnia and Herzegovina', 'Montenegro', 'Albania',
            'North Macedonia', 'Kosovo', 'Malta', 'Cyprus', 'Luxembourg', 'Liechtenstein', 'Monaco',
            'San Marino', 'Vatican City', 'Andorra', 'Iceland', 'Faroe Islands', 'Greenland',
            'China', 'Japan', 'South Korea', 'India', 'Indonesia', 'Thailand', 'Vietnam', 'Philippines',
            'Malaysia', 'Singapore', 'Myanmar', 'Cambodia', 'Laos', 'Bangladesh', 'Pakistan', 'Sri Lanka',
            'Nepal', 'Bhutan', 'Maldives', 'Afghanistan', 'Iran', 'Iraq', 'Syria', 'Yemen', 'Oman',
            'United Arab Emirates', 'Qatar', 'Kuwait', 'Bahrain', 'Saudi Arabia', 'Jordan', 'Lebanon',
            'Israel', 'Palestine', 'Egypt', 'Libya', 'Tunisia', 'Algeria', 'Morocco', 'Sudan',
            'South Sudan', 'Ethiopia', 'Eritrea', 'Djibouti', 'Somalia', 'Kenya', 'Uganda', 'Tanzania',
            'Rwanda', 'Burundi', 'Democratic Republic of the Congo', 'Republic of the Congo', 'Central African Republic',
            'Chad', 'Cameroon', 'Nigeria', 'Niger', 'Mali', 'Burkina Faso', 'Ghana', 'Togo', 'Benin',
            'Ivory Coast', 'Liberia', 'Sierra Leone', 'Guinea', 'Guinea-Bissau', 'Senegal', 'Gambia',
            'Mauritania', 'Cape Verde', 'Sao Tome and Principe', 'Equatorial Guinea', 'Gabon',
            'Angola', 'Zambia', 'Zimbabwe', 'Botswana', 'Namibia', 'South Africa', 'Lesotho', 'Swaziland',
            'Madagascar', 'Mauritius', 'Seychelles', 'Comoros', 'Mayotte', 'Reunion',
            'Brazil', 'Argentina', 'Chile', 'Peru', 'Colombia', 'Venezuela', 'Ecuador', 'Bolivia',
            'Paraguay', 'Uruguay', 'Guyana', 'Suriname', 'French Guiana', 'Falkland Islands',
            'Mexico', 'Guatemala', 'Belize', 'Honduras', 'El Salvador', 'Nicaragua', 'Costa Rica', 'Panama',
            'Cuba', 'Jamaica', 'Haiti', 'Dominican Republic', 'Puerto Rico', 'Trinidad and Tobago',
            'Barbados', 'Saint Lucia', 'Saint Vincent and the Grenadines', 'Grenada', 'Antigua and Barbuda',
            'Saint Kitts and Nevis', 'Dominica', 'Bahamas', 'Turks and Caicos Islands', 'Cayman Islands',
            'British Virgin Islands', 'US Virgin Islands', 'Anguilla', 'Montserrat', 'Guadeloupe', 'Martinique',
            'Saint Martin', 'Saint Barthelemy', 'Aruba', 'Curacao', 'Bonaire', 'Sint Maarten'
        ];

        // Exact match
        if (in_array($country, $realCountries)) {
            return $country;
        }

        // Common variations
        $variations = [
            'USA' => 'United States', 'US' => 'United States', 'America' => 'United States',
            'UK' => 'United Kingdom', 'Britain' => 'United Kingdom', 'England' => 'United Kingdom',
            'UAE' => 'United Arab Emirates', 'Emirates' => 'United Arab Emirates',
            'Deutschland' => 'Germany', 'Nederland' => 'Netherlands', 'Holland' => 'Netherlands',
            'España' => 'Spain', 'Italia' => 'Italy', 'Brasil' => 'Brazil'
        ];

        if (isset($variations[$country])) {
            return $variations[$country];
        }

        // Case-insensitive match
        foreach ($realCountries as $realCountry) {
            if (strcasecmp($country, $realCountry) === 0) {
                return $realCountry;
            }
        }

        return 'Unknown';
    }

    /**
     * Safely parse review date from various formats
     * Handles cases like "Edited October 6, 2025" where strtotime() might fail
     */
    private function parseReviewDateSafely($dateText) {
        $dateText = trim($dateText);

        // First, try to extract the date pattern from the text
        // This handles cases like "Edited October 6, 2025" or "October 6, 2025"
        if (preg_match('/\b(January|February|March|April|May|June|July|August|September|October|November|December)\s+(\d{1,2}),\s+(\d{4})\b/', $dateText, $matches)) {
            $monthName = $matches[1];
            $day = $matches[2];
            $year = $matches[3];

            // Construct a clean date string that strtotime can reliably parse
            $cleanDateStr = "$monthName $day, $year";
            $timestamp = strtotime($cleanDateStr);

            if ($timestamp !== false) {
                return date('Y-m-d', $timestamp);
            }
        }

        // Fallback: try parsing the entire text as-is
        $timestamp = strtotime($dateText);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        // If all parsing fails, return empty string
        return '';
    }
}
