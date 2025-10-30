<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/ReviewRepository.php';

/**
 * Universal Live Scraper for ANY Shopify App
 * NO MOCK DATA - ONLY REAL-TIME SCRAPING
 */
class UniversalLiveScraper {
    private $dbManager;
    private $repository;

    public function __construct() {
        $this->dbManager = new Database();
        $this->repository = new ReviewRepository();
    }
    
    /**
     * Scrape any Shopify app reviews live
     */
    public function scrapeApp($appSlug, $appName = null, $targetCount = null) {
        if (!$appName) {
            $appName = ucfirst($appSlug);
        }

        echo "🔴 UNIVERSAL LIVE SCRAPER - NO MOCK DATA\n";
        echo "🎯 App: $appName ($appSlug)\n";
        echo "🌐 Scraping ONLY from live Shopify pages...\n";

        $baseUrl = "https://apps.shopify.com/$appSlug/reviews";

        // If no target count specified, get it from the live Shopify page
        if ($targetCount === null) {
            $mainUrl = "https://apps.shopify.com/$appSlug/reviews";
            $mainHtml = $this->fetchPage($mainUrl);
            if ($mainHtml && preg_match('/"ratingCount":(\d+)/', $mainHtml, $matches)) {
                $targetCount = (int)$matches[1];
                echo "📊 Target count from Shopify: $targetCount reviews\n";
            }
        }

        $allReviews = [];
        $consecutiveEmptyPages = 0;

        // Scrape ALL pages until no more reviews found (complete historical data)
        for ($page = 1; $page <= 200; $page++) {
            $url = $baseUrl . "?sort_by=newest&page=$page";
            echo "📄 Fetching page $page: $url\n";

            $html = $this->fetchPage($url);
            if (!$html) {
                echo "❌ Failed to fetch page $page - STOPPING\n";
                break;
            }

            // Add delay between requests to avoid rate limiting
            if ($page < 200) {
                sleep(1);
            }

            $pageReviews = $this->parseReviewsFromHTML($html);
            if (empty($pageReviews)) {
                $consecutiveEmptyPages++;
                echo "⚠️ No reviews found on page $page (empty count: $consecutiveEmptyPages)\n";

                // Only stop after 3 consecutive empty pages
                if ($consecutiveEmptyPages >= 3) {
                    echo "⚠️ Reached 3 consecutive empty pages - STOPPING\n";
                    break;
                }
                continue;
            }

            // Reset empty page counter when we find reviews
            $consecutiveEmptyPages = 0;

            $addedFromPage = 0;
            $oldestOnPage = null;

            // Add ALL reviews from this page (no date filtering)
            foreach ($pageReviews as $review) {
                $allReviews[] = $review;
                $addedFromPage++;
                echo "✅ Live: {$review['review_date']} - {$review['rating']}★ - {$review['store_name']}\n";

                // Stop if we've reached the target count
                if ($targetCount !== null && count($allReviews) >= $targetCount) {
                    echo "\n✅ Reached target count of $targetCount reviews\n";
                    break 2; // Break out of both loops
                }
            }

            echo "📊 Page $page: Found " . count($pageReviews) . " reviews, total so far: " . count($allReviews) . "\n";
        }

        if (empty($allReviews)) {
            echo "⚠️ No recent reviews found, trying to scrape ALL reviews for rating calculation...\n";

            // Fallback: scrape ALL reviews (not just recent ones) for apps with older reviews
            $allHistoricalReviews = $this->scrapeAllReviews($baseUrl, $appName);

            if (empty($allHistoricalReviews)) {
                echo "❌ CRITICAL: No live reviews found for $appName\n";
                return ['success' => false, 'message' => 'No live reviews found', 'count' => 0];
            } else {
                echo "✅ Found " . count($allHistoricalReviews) . " historical reviews for $appName\n";
                $allReviews = $allHistoricalReviews;
            }
        }

        // Only clear data if we successfully scraped reviews
        if (!empty($allReviews)) {
            $this->clearAppData($appName);

            // Save all reviews to both old table and new repository
            $saved = 0;
            foreach ($allReviews as $review) {
                // Save to old table for backward compatibility
                if ($this->saveReview($appName, $review)) {
                    $saved++;
                }

                // Save to new repository for enhanced functionality
                $this->repository->addReview(
                    $appName,
                    $review['store_name'],
                    $review['country_name'],
                    $review['rating'],
                    $review['review_content'],
                    $review['review_date'],
                    'live_scrape'
                );
            }

            // Update metadata (fetch fresh page if needed)
            $mainUrl = "https://apps.shopify.com/{$appSlug}/reviews";
            $mainHtml = $this->fetchPage($mainUrl);
            if (!empty($mainHtml)) {
                $this->updateAppMetadata($appName, $mainHtml);
            }

            echo "🎯 SCRAPING COMPLETE: $saved reviews saved for $appName\n";
            return ['success' => true, 'message' => "Scraped $saved reviews", 'count' => $saved];
        } else {
            echo "❌ CRITICAL: No reviews could be scraped for $appName\n";
            return ['success' => false, 'message' => 'No reviews scraped', 'count' => 0];
        }
    }
    
    /**
     * Fetch page with proper headers and retry logic for rate limiting
     */
    private function fetchPage($url, $retries = 3) {
        for ($attempt = 1; $attempt <= $retries; $attempt++) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                CURLOPT_HTTPHEADER => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Language: en-US,en;q=0.5',
                    'Connection: keep-alive',
                    'Upgrade-Insecure-Requests: 1',
                ],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);

            $html = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_error($ch)) {
                echo "❌ cURL Error: " . curl_error($ch) . "\n";
                curl_close($ch);
                return false;
            }

            curl_close($ch);

            // Handle rate limiting (429)
            if ($httpCode === 429) {
                if ($attempt < $retries) {
                    $waitTime = pow(2, $attempt) * 5; // Exponential backoff: 10s, 20s, 40s
                    echo "⏳ Rate limited (429). Waiting {$waitTime}s before retry {$attempt}/{$retries}...\n";
                    sleep($waitTime);
                    continue;
                } else {
                    echo "❌ Rate limited after {$retries} retries. Stopping.\n";
                    return false;
                }
            }

            if ($httpCode !== 200) {
                echo "❌ HTTP Error: $httpCode\n";
                return false;
            }

            return $html;
        }

        return false;
    }
    
    /**
     * Parse reviews from HTML - Universal method
     */
    private function parseReviewsFromHTML($html) {
        $reviews = [];
        
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        
        $xpath = new DOMXPath($dom);
        
        // Find review containers (Shopify uses this structure)
        $reviewNodes = $xpath->query('//div[@data-review-content-id]');
        
        foreach ($reviewNodes as $node) {
            $review = $this->extractReviewData($xpath, $node);
            if ($review) {
                $reviews[] = $review;
            }
        }
        
        return $reviews;
    }
    
    /**
     * Extract review data from DOM node - Universal method
     */
    private function extractReviewData($xpath, $node) {
        try {
            // Extract rating from aria-label (most reliable method)
            $rating = 0;
            $starNodes = $xpath->query('.//div[contains(@aria-label, "stars")]', $node);
            if ($starNodes->length > 0) {
                $ariaLabel = $starNodes->item(0)->getAttribute('aria-label');
                if (preg_match('/(\d+)\s*out\s*of\s*\d+\s*stars/', $ariaLabel, $matches)) {
                    $rating = intval($matches[1]);
                }
            }

            // If aria-label extraction failed, try counting filled stars
            if ($rating === 0) {
                $starNodes = $xpath->query('.//svg[contains(@class, "tw-fill-fg-primary")]', $node);
                $rating = $starNodes->length;
            }

            // Extract date - look for multiple year patterns
            $currentYear = date('Y');
            $lastYear = $currentYear - 1;
            $dateNode = $xpath->query('.//div[contains(@class, "tw-text-body-xs") and contains(@class, "tw-text-fg-tertiary") and (contains(text(), "' . $currentYear . '") or contains(text(), "' . $lastYear . '"))]', $node);
            $reviewDate = '';
            if ($dateNode->length > 0) {
                $dateText = trim($dateNode->item(0)->textContent);
                $reviewDate = $this->parseReviewDateSafely($dateText);
            }

            // If no date found, try alternative selectors
            if (empty($reviewDate)) {
                $allDateNodes = $xpath->query('.//div[contains(@class, "tw-text-fg-tertiary")]', $node);
                foreach ($allDateNodes as $dNode) {
                    $text = trim($dNode->textContent);
                    if (preg_match('/\b(January|February|March|April|May|June|July|August|September|October|November|December)\s+\d{1,2},\s+\d{4}\b/', $text)) {
                        $reviewDate = $this->parseReviewDateSafely($text);
                        if (!empty($reviewDate)) {
                            break;
                        }
                    }
                }
            }

            // Extract store name
            $storeNode = $xpath->query('.//div[contains(@class, "tw-text-heading-xs") and contains(@class, "tw-text-fg-primary")]', $node);
            $storeName = '';
            if ($storeNode->length > 0) {
                $storeName = trim($storeNode->item(0)->textContent);
            }

            // If no store name found, try alternative selectors
            if (empty($storeName)) {
                $altStoreNodes = $xpath->query('.//div[contains(@class, "tw-text-fg-primary") and contains(@class, "tw-overflow-hidden")]', $node);
                if ($altStoreNodes->length > 0) {
                    $storeName = trim($altStoreNodes->item(0)->textContent);
                }
            }

            // Extract country using enhanced detection
            $country = $this->extractCountryFromReview($xpath, $node, $storeName);

            // Extract review content
            $contentNode = $xpath->query('.//p[contains(@class, "tw-break-words")]', $node);
            $reviewContent = '';
            if ($contentNode->length > 0) {
                $reviewContent = trim($contentNode->item(0)->textContent);
            }

            // If no content found, try alternative selectors
            if (empty($reviewContent)) {
                $altContentNodes = $xpath->query('.//div[contains(@class, "tw-text-body-md")]//p', $node);
                if ($altContentNodes->length > 0) {
                    $reviewContent = trim($altContentNodes->item(0)->textContent);
                }
            }

            // Validate required fields
            if (empty($storeName) || empty($reviewDate) || $rating === 0) {
                echo "⚠️ Skipping incomplete review: store='$storeName', date='$reviewDate', rating=$rating\n";
                return null;
            }

            return [
                'store_name' => $storeName,
                'country_name' => substr($country, 0, 50),
                'rating' => $rating,
                'review_content' => $reviewContent,
                'review_date' => $reviewDate
            ];

        } catch (Exception $e) {
            echo "❌ Error extracting review: " . $e->getMessage() . "\n";
            return null;
        }
    }
    
    /**
     * Clear existing app data - Smart clearing that handles foreign key constraints
     */
    private function clearAppData($appName) {
        try {
            $conn = $this->dbManager->getConnection();

            // First, temporarily disable foreign key checks
            $conn->exec("SET FOREIGN_KEY_CHECKS = 0");

            // Delete from access_reviews first (child table)
            $stmt = $conn->prepare("DELETE FROM access_reviews WHERE app_name = ?");
            $stmt->execute([$appName]);

            // Then delete from reviews (parent table)
            $stmt = $conn->prepare("DELETE FROM reviews WHERE app_name = ?");
            $stmt->execute([$appName]);

            // Re-enable foreign key checks
            $conn->exec("SET FOREIGN_KEY_CHECKS = 1");

            echo "✅ Cleared existing $appName data (including access_reviews)\n";
        } catch (Exception $e) {
            // Re-enable foreign key checks in case of error
            try {
                $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
            } catch (Exception $fkError) {
                // Ignore FK re-enable errors
            }
            echo "❌ Error clearing data: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Save review to database - Handle duplicates gracefully
     */
    private function saveReview($appName, $review) {
        try {
            $conn = $this->dbManager->getConnection();
            $stmt = $conn->prepare("
                INSERT INTO reviews (app_name, store_name, country_name, rating, review_content, review_date, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    rating = VALUES(rating),
                    review_content = VALUES(review_content),
                    created_at = NOW()
            ");

            return $stmt->execute([
                $appName,
                $review['store_name'],
                $review['country_name'],
                $review['rating'],
                $review['review_content'],
                $review['review_date']
            ]);
        } catch (Exception $e) {
            echo "❌ Error saving review: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Update app metadata from live page - INCLUDING RATING DISTRIBUTION
     */
    private function updateAppMetadata($appName, $html) {
        try {
            // Skip if HTML is empty
            if (empty($html)) {
                echo "⚠️ Skipping metadata update - empty HTML\n";
                return;
            }

            $totalReviews = 0;
            $overallRating = 0.0;
            $fiveStarTotal = 0;
            $fourStarTotal = 0;
            $threeStarTotal = 0;
            $twoStarTotal = 0;
            $oneStarTotal = 0;

            // Extract total reviews and overall rating from JSON-LD
            if (preg_match('/"ratingCount":(\d+)/', $html, $matches)) {
                $totalReviews = intval($matches[1]);
            }

            if (preg_match('/"ratingValue":([0-9.]+)/', $html, $matches)) {
                $overallRating = floatval($matches[1]);
            }

            // Extract individual star rating counts from the page
            // Look for rating filter links that contain the star counts
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);

            // Try to find rating distribution from filter links
            for ($rating = 5; $rating >= 1; $rating--) {
                // Look for links with href containing ratings filter
                $ratingLinks = $xpath->query("//a[contains(@href, 'ratings%5B%5D=$rating')]");

                foreach ($ratingLinks as $link) {
                    $ariaLabel = $link->getAttribute('aria-label');
                    $linkText = trim($link->textContent);

                    // Try to extract count from aria-label
                    if (preg_match('/(\d+)\s+total\s+reviews?/', $ariaLabel, $matches)) {
                        $count = intval($matches[1]);
                        $this->setStarCount($rating, $count, $fiveStarTotal, $fourStarTotal, $threeStarTotal, $twoStarTotal, $oneStarTotal);
                        echo "✅ Found {$rating}★: $count reviews (from aria-label)\n";
                        break;
                    }

                    // Try to extract count from link text
                    if (is_numeric($linkText)) {
                        $count = intval($linkText);
                        $this->setStarCount($rating, $count, $fiveStarTotal, $fourStarTotal, $threeStarTotal, $twoStarTotal, $oneStarTotal);
                        echo "✅ Found {$rating}★: $count reviews (from text)\n";
                        break;
                    }
                }
            }

            // If we couldn't extract individual star counts, calculate from scraped reviews
            if ($fiveStarTotal + $fourStarTotal + $threeStarTotal + $twoStarTotal + $oneStarTotal == 0) {
                echo "⚠️ No rating distribution found in HTML, calculating from scraped reviews...\n";
                $this->calculateRatingDistributionFromScrapedData($appName, $fiveStarTotal, $fourStarTotal, $threeStarTotal, $twoStarTotal, $oneStarTotal);
            }

            // Update database with complete metadata
            $conn = $this->dbManager->getConnection();
            $stmt = $conn->prepare("
                INSERT INTO app_metadata (
                    app_name, total_reviews, overall_rating,
                    five_star_total, four_star_total, three_star_total,
                    two_star_total, one_star_total, last_updated
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    total_reviews = VALUES(total_reviews),
                    overall_rating = VALUES(overall_rating),
                    five_star_total = VALUES(five_star_total),
                    four_star_total = VALUES(four_star_total),
                    three_star_total = VALUES(three_star_total),
                    two_star_total = VALUES(two_star_total),
                    one_star_total = VALUES(one_star_total),
                    last_updated = NOW()
            ");

            $stmt->execute([
                $appName, $totalReviews, $overallRating,
                $fiveStarTotal, $fourStarTotal, $threeStarTotal,
                $twoStarTotal, $oneStarTotal
            ]);

            echo "✅ Updated $appName metadata: $totalReviews total reviews, {$overallRating}★ rating\n";
            echo "   Rating distribution: 5★:$fiveStarTotal, 4★:$fourStarTotal, 3★:$threeStarTotal, 2★:$twoStarTotal, 1★:$oneStarTotal\n";

        } catch (Exception $e) {
            echo "❌ Error updating metadata: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Scrape ALL reviews (not just recent ones) for apps with older reviews
     * This is used as a fallback when no recent reviews are found
     */
    public function scrapeAllReviews($baseUrl, $appName) {
        echo "🔄 FALLBACK MODE: Scraping ALL reviews for $appName (not just recent)\n";

        $allReviews = [];
        $consecutiveEmptyPages = 0;

        // Scrape pages until we get all reviews (no date filtering)
        for ($page = 1; $page <= 200; $page++) { // Scrape up to 200 pages for complete historical data
            $url = $baseUrl . "?sort_by=newest&page=$page";
            echo "📄 Historical page $page: $url\n";

            $html = $this->fetchPage($url);
            if (!$html) {
                echo "❌ Failed to fetch historical page $page - STOPPING\n";
                break;
            }

            $pageReviews = $this->parseReviewsFromHTML($html);
            if (empty($pageReviews)) {
                $consecutiveEmptyPages++;
                echo "⚠️ No reviews found on historical page $page (empty count: $consecutiveEmptyPages)\n";

                // Only stop after 3 consecutive empty pages
                if ($consecutiveEmptyPages >= 3) {
                    echo "📅 Reached 3 consecutive empty pages - STOPPING\n";
                    break;
                }
                continue;
            }

            // Reset empty page counter when we find reviews
            $consecutiveEmptyPages = 0;

            // Add ALL reviews (no date filtering)
            foreach ($pageReviews as $review) {
                $allReviews[] = $review;
                echo "✅ Historical: {$review['review_date']} - {$review['rating']}★ - {$review['store_name']}\n";
            }

            echo "📊 Historical page $page: Found " . count($pageReviews) . " reviews, total: " . count($allReviews) . "\n";

            // Add delay between requests
            sleep(1);
        }

        echo "✅ Historical scraping complete: " . count($allReviews) . " total reviews\n";
        return $allReviews;
    }

    /**
     * Helper method to set star count for specific rating
     */
    private function setStarCount($rating, $count, &$fiveStarTotal, &$fourStarTotal, &$threeStarTotal, &$twoStarTotal, &$oneStarTotal) {
        switch ($rating) {
            case 5:
                $fiveStarTotal = $count;
                break;
            case 4:
                $fourStarTotal = $count;
                break;
            case 3:
                $threeStarTotal = $count;
                break;
            case 2:
                $twoStarTotal = $count;
                break;
            case 1:
                $oneStarTotal = $count;
                break;
        }
    }

    /**
     * Calculate rating distribution from scraped reviews in database
     */
    private function calculateRatingDistributionFromScrapedData($appName, &$fiveStarTotal, &$fourStarTotal, &$threeStarTotal, &$twoStarTotal, &$oneStarTotal) {
        try {
            $conn = $this->dbManager->getConnection();
            $stmt = $conn->prepare("
                SELECT
                    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                FROM reviews
                WHERE app_name = ?
            ");

            $stmt->execute([$appName]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $fiveStarTotal = intval($result['five_star'] ?? 0);
                $fourStarTotal = intval($result['four_star'] ?? 0);
                $threeStarTotal = intval($result['three_star'] ?? 0);
                $twoStarTotal = intval($result['two_star'] ?? 0);
                $oneStarTotal = intval($result['one_star'] ?? 0);

                echo "✅ Calculated rating distribution from scraped data: 5★:$fiveStarTotal, 4★:$fourStarTotal, 3★:$threeStarTotal, 2★:$twoStarTotal, 1★:$oneStarTotal\n";
            }
        } catch (Exception $e) {
            echo "❌ Error calculating rating distribution: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Enhanced country extraction from review node
     */
    private function extractCountryFromReview($xpath, $node, $storeName) {
        $country = 'Unknown';

        // Method 1: Look for country in the merchant info section
        $merchantInfoSelectors = [
            './/div[contains(@class, "tw-text-fg-tertiary") and contains(@class, "tw-text-body-xs")]',
            './/div[contains(@class, "tw-text-body-xs") and contains(@class, "tw-text-fg-secondary")]',
            './/span[contains(@class, "tw-text-body-xs")]'
        ];

        foreach ($merchantInfoSelectors as $selector) {
            $nodes = $xpath->query($selector, $node);
            foreach ($nodes as $infoNode) {
                $text = trim($infoNode->textContent);
                $detectedCountry = $this->validateCountryText($text);
                if ($detectedCountry !== 'Unknown') {
                    return $detectedCountry;
                }
            }
        }

        // Method 2: Look in the store name container's siblings
        $storeContainer = $xpath->query('.//div[contains(@class, "tw-text-heading-xs") and contains(@class, "tw-text-fg-primary")]', $node);
        if ($storeContainer->length > 0) {
            $parentContainer = $storeContainer->item(0)->parentNode;
            $childDivs = $xpath->query('./div', $parentContainer);

            foreach ($childDivs as $childDiv) {
                $text = trim($childDiv->textContent);
                if (!empty($text) && $text !== $storeName) {
                    $detectedCountry = $this->validateCountryText($text);
                    if ($detectedCountry !== 'Unknown') {
                        return $detectedCountry;
                    }
                }
            }
        }

        // Method 3: Look for country patterns in the entire review node text
        $fullText = $node->textContent;
        $detectedCountry = $this->extractCountryFromText($fullText, $storeName);
        if ($detectedCountry !== 'Unknown') {
            return $detectedCountry;
        }

        // Method 4: Infer from store name as last resort
        return $this->inferCountryFromStoreName($storeName);
    }

    /**
     * Validate if text looks like a country name
     */
    private function validateCountryText($text) {
        if (empty($text) || strlen($text) < 2 || strlen($text) > 50) {
            return 'Unknown';
        }

        // Skip obvious non-country text
        $skipPatterns = [
            '/\d{4}/', // Years
            '/\d+\s+(day|week|month|year)/', // Time periods
            '/using|replied|helpful|show\s+(more|less)/i', // Common UI text
            '/^(about|over|under|more|less|than)\s/i', // Descriptive prefixes
            '/\d+\s*(star|review|rating)/i' // Review-related text
        ];

        foreach ($skipPatterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return 'Unknown';
            }
        }

        // Must contain only letters, spaces, and common country punctuation
        if (!preg_match('/^[A-Za-z\s\-\'\.]+$/', $text)) {
            return 'Unknown';
        }

        // Check against known countries list
        $knownCountries = [
            'United States', 'United Kingdom', 'Canada', 'Australia', 'Germany', 'France',
            'Netherlands', 'Sweden', 'Norway', 'Denmark', 'Finland', 'Belgium', 'Switzerland',
            'Austria', 'Ireland', 'Italy', 'Spain', 'Portugal', 'Poland', 'Czech Republic',
            'Slovakia', 'Hungary', 'Romania', 'Bulgaria', 'Greece', 'Turkey', 'Russia',
            'Ukraine', 'India', 'China', 'Japan', 'South Korea', 'Thailand', 'Vietnam',
            'Indonesia', 'Malaysia', 'Philippines', 'Singapore', 'New Zealand', 'South Africa',
            'Nigeria', 'Kenya', 'Egypt', 'Morocco', 'Brazil', 'Argentina', 'Chile', 'Colombia',
            'Peru', 'Venezuela', 'Mexico', 'Costa Rica', 'Panama', 'Guatemala', 'Honduras',
            'El Salvador', 'Nicaragua', 'Belize', 'Jamaica', 'Cuba', 'Dominican Republic',
            'Haiti', 'Trinidad and Tobago', 'Barbados', 'Bahamas', 'Puerto Rico'
        ];

        // Exact match
        if (in_array($text, $knownCountries)) {
            return $text;
        }

        // Fuzzy match for common variations
        $text_lower = strtolower($text);
        $countryMappings = [
            'usa' => 'United States',
            'us' => 'United States',
            'america' => 'United States',
            'uk' => 'United Kingdom',
            'britain' => 'United Kingdom',
            'england' => 'United Kingdom',
            'scotland' => 'United Kingdom',
            'wales' => 'United Kingdom',
            'deutschland' => 'Germany',
            'nederland' => 'Netherlands',
            'holland' => 'Netherlands',
            'españa' => 'Spain',
            'italia' => 'Italy',
            'brasil' => 'Brazil',
            'österreich' => 'Austria',
            'schweiz' => 'Switzerland',
            'sverige' => 'Sweden',
            'norge' => 'Norway',
            'danmark' => 'Denmark',
            'suomi' => 'Finland'
        ];

        if (isset($countryMappings[$text_lower])) {
            return $countryMappings[$text_lower];
        }

        return 'Unknown';
    }

    /**
     * Extract country from full text using patterns
     */
    private function extractCountryFromText($text, $storeName) {
        // Look for country patterns in the text
        $countryPatterns = [
            '/\b(United States|USA|US)\b/i' => 'United States',
            '/\b(United Kingdom|UK|Britain|England|Scotland|Wales)\b/i' => 'United Kingdom',
            '/\b(Canada|Canadian)\b/i' => 'Canada',
            '/\b(Australia|Australian)\b/i' => 'Australia',
            '/\b(Germany|German|Deutschland)\b/i' => 'Germany',
            '/\b(France|French)\b/i' => 'France',
            '/\b(Netherlands|Dutch|Holland)\b/i' => 'Netherlands',
            '/\b(Sweden|Swedish|Sverige)\b/i' => 'Sweden',
            '/\b(Norway|Norwegian|Norge)\b/i' => 'Norway',
            '/\b(Denmark|Danish|Danmark)\b/i' => 'Denmark',
            '/\b(Finland|Finnish|Suomi)\b/i' => 'Finland',
            '/\b(Belgium|Belgian)\b/i' => 'Belgium',
            '/\b(Switzerland|Swiss|Schweiz)\b/i' => 'Switzerland',
            '/\b(Austria|Austrian|Österreich)\b/i' => 'Austria',
            '/\b(Ireland|Irish)\b/i' => 'Ireland',
            '/\b(Italy|Italian|Italia)\b/i' => 'Italy',
            '/\b(Spain|Spanish|España)\b/i' => 'Spain',
            '/\b(India|Indian)\b/i' => 'India',
            '/\b(Japan|Japanese)\b/i' => 'Japan',
            '/\b(Brazil|Brazilian|Brasil)\b/i' => 'Brazil',
            '/\b(Mexico|Mexican)\b/i' => 'Mexico',
            '/\b(South Africa|South African)\b/i' => 'South Africa',
            '/\b(New Zealand)\b/i' => 'New Zealand',
            '/\b(Singapore)\b/i' => 'Singapore'
        ];

        foreach ($countryPatterns as $pattern => $country) {
            if (preg_match($pattern, $text)) {
                return $country;
            }
        }

        return 'Unknown';
    }

    /**
     * Infer country from store name patterns
     */
    private function inferCountryFromStoreName($storeName) {
        if (empty($storeName)) return 'Unknown';

        $storeName_lower = strtolower($storeName);

        // Business suffix patterns that indicate country
        $suffixPatterns = [
            '/\b(ltd|limited)\b/' => 'United Kingdom',
            '/\b(llc|inc|corp|corporation)\b/' => 'United States',
            '/\b(pty|pty ltd)\b/' => 'Australia',
            '/\b(gmbh|ag)\b/' => 'Germany',
            '/\b(sarl|sas)\b/' => 'France',
            '/\b(bv|nv)\b/' => 'Netherlands',
            '/\b(ab|aktiebolag)\b/' => 'Sweden',
            '/\b(as|asa)\b/' => 'Norway',
            '/\b(aps|a\/s)\b/' => 'Denmark',
            '/\b(oy|oyj)\b/' => 'Finland',
            '/\b(sa|nv)\b/' => 'Belgium',
            '/\b(spa|srl)\b/' => 'Italy',
            '/\b(sl|sa)\b/' => 'Spain'
        ];

        foreach ($suffixPatterns as $pattern => $country) {
            if (preg_match($pattern, $storeName_lower)) {
                return $country;
            }
        }

        // Country indicators in store names
        $namePatterns = [
            '/\b(uk|united kingdom|britain|british)\b/' => 'United Kingdom',
            '/\b(usa|united states|america|american|us)\b/' => 'United States',
            '/\b(canada|canadian|ca)\b/' => 'Canada',
            '/\b(australia|australian|aussie|au)\b/' => 'Australia',
            '/\b(germany|german|deutschland|de)\b/' => 'Germany',
            '/\b(france|french|fr)\b/' => 'France',
            '/\b(netherlands|dutch|holland|nl)\b/' => 'Netherlands',
            '/\b(sweden|swedish|se)\b/' => 'Sweden',
            '/\b(norway|norwegian|no)\b/' => 'Norway',
            '/\b(denmark|danish|dk)\b/' => 'Denmark',
            '/\b(finland|finnish|fi)\b/' => 'Finland',
            '/\b(belgium|belgian|be)\b/' => 'Belgium',
            '/\b(switzerland|swiss|ch)\b/' => 'Switzerland',
            '/\b(austria|austrian|at)\b/' => 'Austria',
            '/\b(ireland|irish|ie)\b/' => 'Ireland',
            '/\b(italy|italian|it)\b/' => 'Italy',
            '/\b(spain|spanish|es)\b/' => 'Spain',
            '/\b(india|indian|in)\b/' => 'India',
            '/\b(japan|japanese|jp)\b/' => 'Japan',
            '/\b(brazil|brazilian|br)\b/' => 'Brazil',
            '/\b(mexico|mexican|mx)\b/' => 'Mexico',
            '/\b(south africa|za)\b/' => 'South Africa',
            '/\b(singapore|sg)\b/' => 'Singapore'
        ];

        foreach ($namePatterns as $pattern => $country) {
            if (preg_match($pattern, $storeName_lower)) {
                return $country;
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

        // If all parsing fails, return empty string (will be skipped)
        return '';
    }
}
?>
