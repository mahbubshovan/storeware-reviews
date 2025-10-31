<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

class EnhancedAnalytics {
    private $pdo;
    private $appSlugs = [
        'StoreSEO' => 'storeseo',
        'StoreFAQ' => 'storefaq', 
        'EasyFlow' => 'product-options-4',
        'BetterDocs FAQ Knowledge Base' => 'betterdocs-knowledgebase',
        'Vidify' => 'vidify',
        'TrustSync' => 'customer-review-app'
    ];
    
    public function __construct() {
        try {
            require_once __DIR__ . '/../config/database.php';
            $database = new Database();
            $this->pdo = $database->getConnection();
        } catch (Exception $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    public function getEnhancedAnalytics($appName) {
        try {
            // Step 1: Scrape first page of reviews to check for new reviews
            $newReviewsFound = $this->checkForNewReviews($appName);
            
            // Step 2: Get analytics data from database
            $analyticsData = $this->getAnalyticsFromDatabase($appName);
            
            // Step 3: Get latest reviews from access_reviews table
            $latestReviews = $this->getLatestReviews($appName);
            
            // Step 4: Get rating distribution and overall rating from live scraping
            $ratingData = $this->getLiveRatingData($appName);
            $ratingDistribution = $ratingData['distribution'];
            $overallRating = $ratingData['overall_rating'];

            // Step 5: Use extracted Shopify rating if available, otherwise calculate from distribution
            $preciseAverage = $this->calculateAverageFromDistribution($ratingDistribution);
            $displayRating = $overallRating ?: $preciseAverage; // Use extracted rating or calculated as fallback

            return [
                'success' => true,
                'data' => [
                    'app_name' => $appName,
                    'this_month_count' => $analyticsData['this_month_count'],
                    'last_30_days_count' => $analyticsData['last_30_days_count'],
                    'total_reviews' => $analyticsData['total_reviews'],
                    'average_rating' => $displayRating, // Use extracted Shopify rating (real data from page)
                    'shopify_display_rating' => $displayRating, // Same as average_rating for consistency
                    'calculated_average_rating' => $preciseAverage, // Calculated from distribution for comparison
                    'extracted_rating' => $overallRating, // What we extracted from page (if any)
                    'database_average_rating' => $analyticsData['average_rating'], // Keep for comparison
                    'rating_distribution' => $ratingDistribution,
                    'rating_distribution_source' => 'live_shopify_scraping',
                    'rating_distribution_total' => array_sum($ratingDistribution),
                    'latest_reviews' => $latestReviews,
                    'new_reviews_found' => $newReviewsFound,
                    'data_source' => $newReviewsFound > 0 ? 'live' : 'cached',
                    'last_updated' => date('Y-m-d H:i:s')
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function checkForNewReviews($appName) {
        if (!isset($this->appSlugs[$appName])) {
            throw new Exception("Unknown app: $appName");
        }
        
        $slug = $this->appSlugs[$appName];
        $url = "https://apps.shopify.com/$slug/reviews?page=1";
        
        // Fetch first page
        $html = $this->fetchPage($url);
        if (!$html) {
            return 0;
        }
        
        // Extract reviews from first page
        $reviews = $this->extractReviewsFromHtml($html);
        $newReviewsCount = 0;
        
        foreach ($reviews as $review) {
            if ($this->isValidReview($review)) {
                // Check if this review already exists in our database
                if (!$this->reviewExists($appName, $review)) {
                    // Save new review to both tables
                    $this->saveNewReview($appName, $review);
                    $newReviewsCount++;
                }
            }
        }
        
        return $newReviewsCount;
    }
    
    private function fetchPage($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ($httpCode === 200) ? $html : false;
    }
    
    private function extractReviewsFromHtml($html) {
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        
        $reviewNodes = $xpath->query('//div[@data-review-content-id]');
        $reviews = [];
        
        foreach ($reviewNodes as $reviewNode) {
            $review = $this->extractReviewData($xpath, $reviewNode);
            if ($review) {
                $reviews[] = $review;
            }
        }
        
        return $reviews;
    }
    
    private function extractReviewData($xpath, $reviewNode) {
        // Extract store name
        $storeNameNodes = $xpath->query('.//div[contains(@class, "tw-text-heading-xs")]', $reviewNode);
        $storeName = '';
        if ($storeNameNodes->length > 0) {
            $storeName = trim($storeNameNodes->item(0)->textContent);
        }
        
        // Extract review content
        $contentNodes = $xpath->query('.//div[@data-truncate-review and contains(@class, "tw-text-body-md")]//p[@class="tw-break-words"]', $reviewNode);
        $reviewContent = '';
        if ($contentNodes->length > 0) {
            $reviewContent = trim($contentNodes->item(0)->textContent);
        }
        
        // Extract rating
        $rating = $this->extractRating($xpath, $reviewNode);
        
        // Extract date from text content
        $reviewDate = null;
        $nodeText = $reviewNode->textContent;
        
        if (preg_match('/\b(January|February|March|April|May|June|July|August|September|October|November|December)\s+\d{1,2},?\s+\d{4}\b/', $nodeText, $matches)) {
            $dateStr = $matches[0];
            try {
                $date = new DateTime($dateStr);
                $reviewDate = $date->format('Y-m-d');
            } catch (Exception $e) {
                $reviewDate = date('Y-m-d');
            }
        } else {
            $reviewDate = date('Y-m-d');
        }
        
        // Extract country
        $countryNodes = $xpath->query('.//span[contains(@class, "tw-text-body-xs")]', $reviewNode);
        $country = 'United States';
        if ($countryNodes->length > 0) {
            $countryText = trim($countryNodes->item(0)->textContent);
            if (!empty($countryText) && strlen($countryText) < 50) {
                $country = $countryText;
            }
        }
        
        return [
            'store_name' => $storeName,
            'review_content' => $reviewContent,
            'rating' => $rating,
            'review_date' => $reviewDate,
            'country' => $country
        ];
    }
    
    private function extractRating($xpath, $reviewNode) {
        // Method 1: Look for aria-label with rating (most reliable)
        $ratingNodes = $xpath->query('.//*[@aria-label]', $reviewNode);
        if ($ratingNodes->length > 0) {
            foreach ($ratingNodes as $node) {
                $ariaLabel = $node->getAttribute('aria-label');
                // Look for patterns like "5 out of 5 stars", "Rated 4 stars", "4 star rating"
                if (preg_match('/(\d+)\s+out\s+of\s+\d+\s+star/i', $ariaLabel, $matches)) {
                    $rating = (int)$matches[1];
                    if ($rating >= 1 && $rating <= 5) {
                        return $rating;
                    }
                }
                if (preg_match('/(?:rated\s+)?(\d+)\s+star/i', $ariaLabel, $matches)) {
                    $rating = (int)$matches[1];
                    if ($rating >= 1 && $rating <= 5) {
                        return $rating;
                    }
                }
            }
        }

        // Method 2: Count filled vs total stars in star container
        $starContainers = $xpath->query('.//div[contains(@class, "star") or contains(@class, "rating")]', $reviewNode);
        foreach ($starContainers as $container) {
            $filledStars = $xpath->query('.//svg[contains(@class, "tw-fill-fg-primary") or contains(@class, "filled")]', $container);
            $allStars = $xpath->query('.//svg', $container);

            // If we have exactly 5 stars total, count filled ones
            if ($allStars->length == 5) {
                return $filledStars->length;
            }
        }

        // Method 3: Look for filled stars in the entire review node
        $filledStars = $xpath->query('.//svg[contains(@class, "tw-fill-fg-primary")]', $reviewNode);
        $allStars = $xpath->query('.//svg[contains(@viewBox, "0 0 20 20") or contains(@class, "star")]', $reviewNode);

        // If we have exactly 5 star SVGs, count the filled ones
        if ($allStars->length == 5) {
            return $filledStars->length;
        } else if ($filledStars->length > 0 && $filledStars->length <= 5) {
            return $filledStars->length;
        }

        // Method 2: Look for aria-label with rating
        $ratingNodes = $xpath->query('.//*[contains(@aria-label, "star") or contains(@aria-label, "rating")]', $reviewNode);
        if ($ratingNodes->length > 0) {
            $ariaLabel = $ratingNodes->item(0)->getAttribute('aria-label');
            if (preg_match('/(\d+)\s*(?:star|out of)/i', $ariaLabel, $matches)) {
                return (int)$matches[1];
            }
        }

        // Method 3: Look for data attributes
        $dataRatingNodes = $xpath->query('.//*[@data-rating or @data-stars]', $reviewNode);
        if ($dataRatingNodes->length > 0) {
            $rating = $dataRatingNodes->item(0)->getAttribute('data-rating') ?:
                     $dataRatingNodes->item(0)->getAttribute('data-stars');
            if ($rating && is_numeric($rating)) {
                return (int)$rating;
            }
        }

        // Method 4: Count all star elements and look for visual indicators
        $allStars = $xpath->query('.//svg[contains(@class, "star") or contains(@viewBox, "0 0 20 20")]', $reviewNode);
        if ($allStars->length > 0) {
            $filledCount = 0;
            foreach ($allStars as $star) {
                $classes = $star->getAttribute('class');
                $fill = $star->getAttribute('fill');

                // Check if star appears filled based on class or fill attribute
                if (strpos($classes, 'primary') !== false ||
                    strpos($classes, 'filled') !== false ||
                    strpos($fill, '#') !== false ||
                    strpos($classes, 'tw-fill-fg-primary') !== false) {
                    $filledCount++;
                }
            }

            if ($filledCount > 0 && $filledCount <= 5) {
                return $filledCount;
            }
        }

        // Method 5: Look for text-based rating
        $textContent = $reviewNode->textContent;
        if (preg_match('/(\d+)\s*(?:star|out of 5)/i', $textContent, $matches)) {
            return (int)$matches[1];
        }

        // Method 6: Advanced SVG analysis - look for filled paths
        $svgPaths = $xpath->query('.//svg//path', $reviewNode);
        $filledPaths = 0;
        foreach ($svgPaths as $path) {
            $fill = $path->getAttribute('fill');
            $classes = $path->getAttribute('class');

            // Check for filled star indicators
            if ($fill && $fill !== 'none' && $fill !== 'transparent' &&
                (strpos($fill, '#') === 0 || strpos($classes, 'fill') !== false)) {
                $filledPaths++;
            }
        }

        if ($filledPaths > 0 && $filledPaths <= 5) {
            return $filledPaths;
        }

        // Last resort: return null to indicate we couldn't determine rating
        return null;
    }
    
    private function isValidReview($review) {
        return !empty($review['store_name']) && 
               !empty($review['review_content']) && 
               !empty($review['review_date']) &&
               $review['rating'] >= 1 && $review['rating'] <= 5;
    }
    
    private function reviewExists($appName, $review) {
        $stmt = $this->pdo->prepare('
            SELECT COUNT(*) FROM reviews 
            WHERE app_name = ? AND store_name = ? AND review_date = ? AND review_content = ?
        ');
        $stmt->execute([$appName, $review['store_name'], $review['review_date'], $review['review_content']]);
        return $stmt->fetchColumn() > 0;
    }
    
    private function saveNewReview($appName, $review) {
        // Save to reviews table
        $stmt = $this->pdo->prepare('
            INSERT INTO reviews (app_name, store_name, country_name, rating, review_content, review_date, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ');
        $stmt->execute([
            $appName,
            $review['store_name'],
            $review['country'],
            $review['rating'],
            $review['review_content'],
            $review['review_date']
        ]);
        
        $reviewId = $this->pdo->lastInsertId();
        
        // Save to access_reviews table if it's within last 30 days
        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
        if ($review['review_date'] >= $thirtyDaysAgo) {
            $stmt = $this->pdo->prepare('
                INSERT INTO access_reviews (app_name, review_date, review_content, country_name, original_review_id, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ');
            $stmt->execute([
                $appName,
                $review['review_date'],
                $review['review_content'],
                $review['country'],
                $reviewId
            ]);
        }
    }

    private function getAnalyticsFromDatabase($appName) {
        // Get this month count from reviews table (primary data source) - only active reviews
        $stmt = $this->pdo->prepare('
            SELECT COUNT(*) FROM reviews
            WHERE app_name = ? AND review_date >= DATE_FORMAT(CURDATE(), "%Y-%m-01") AND is_active = TRUE
        ');
        $stmt->execute([$appName]);
        $thisMonthCount = $stmt->fetchColumn();

        // Get last 30 days count from reviews table (primary data source) - only active reviews
        $stmt = $this->pdo->prepare('
            SELECT COUNT(*) FROM reviews
            WHERE app_name = ? AND review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND is_active = TRUE
        ');
        $stmt->execute([$appName]);
        $last30DaysCount = $stmt->fetchColumn();

        // Get total reviews from app_metadata (Shopify's actual count) if available
        $stmt = $this->pdo->prepare('SELECT total_reviews, overall_rating FROM app_metadata WHERE app_name = ?');
        $stmt->execute([$appName]);
        $metadata = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($metadata && $metadata['total_reviews'] > 0) {
            // Use Shopify's actual total count from metadata
            $totalReviews = (int)$metadata['total_reviews'];
            $averageRating = (float)$metadata['overall_rating'];
        } else {
            // Fallback to database count if metadata not available
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM reviews WHERE app_name = ? AND is_active = TRUE');
            $stmt->execute([$appName]);
            $totalReviews = $stmt->fetchColumn();

            // Get average rating - only active reviews
            $stmt = $this->pdo->prepare('SELECT AVG(rating) FROM reviews WHERE app_name = ? AND is_active = TRUE');
            $stmt->execute([$appName]);
            $averageRating = round((int) $stmt->fetchColumn(), 1);
        }

        return [
            'this_month_count' => (int)$thisMonthCount,
            'last_30_days_count' => (int)$last30DaysCount,
            'total_reviews' => (int)$totalReviews,
            'average_rating' => $averageRating ?: 0
        ];
    }

    private function getLatestReviews($appName, $limit = 10) {
        $stmt = $this->pdo->prepare('
            SELECT store_name, rating, review_content, review_date, country_name
            FROM reviews
            WHERE app_name = ?
            ORDER BY review_date DESC, id DESC
            LIMIT ' . (int)$limit
        );
        $stmt->execute([$appName]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getLiveRatingData($appName) {
        if (!isset($this->appSlugs[$appName])) {
            // Fallback to database if app not found
            return [
                'distribution' => $this->getDatabaseRatingDistribution($appName),
                'overall_rating' => null
            ];
        }

        $slug = $this->appSlugs[$appName];
        $overallRating = null;

        // Always try to extract overall rating from main page first
        $url = "https://apps.shopify.com/$slug/reviews";
        $html = $this->fetchPage($url);
        if ($html) {
            $overallRating = $this->extractOverallRatingFromPage($html);
        }

        // Method 1: Try to scrape comprehensive rating distribution from multiple pages
        $distribution = $this->scrapeComprehensiveRatingDistribution($slug);

        // Method 2: Fallback to single page analysis if comprehensive fails
        if (array_sum($distribution) === 0 && $html) {
            $pageData = $this->extractRatingDataFromPage($html);
            $distribution = $pageData['distribution'];
            // Overall rating already extracted above
        }

        // Method 3: Final fallback to database
        if (array_sum($distribution) === 0) {
            $distribution = $this->getDatabaseRatingDistribution($appName);
        }

        return [
            'distribution' => $distribution,
            'overall_rating' => $overallRating ?? null
        ];
    }

    private function scrapeComprehensiveRatingDistribution($slug) {
        // First try to get the rating distribution summary from the main reviews page
        $summaryDistribution = $this->extractRatingDistributionSummary($slug);
        if ($summaryDistribution !== null) {
            return $summaryDistribution;
        }

        // Fallback to individual review scraping if summary not found
        $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        $page = 1;
        $maxPages = 5; // Reduce since we have summary method
        $emptyPageCount = 0;
        $maxEmptyPages = 2;

        // Scraping comprehensive rating distribution for $slug

        while ($page <= $maxPages && $emptyPageCount < $maxEmptyPages) {
            $url = "https://apps.shopify.com/$slug/reviews?page=$page";
            $html = $this->fetchPage($url);

            if (!$html) {
                $emptyPageCount++;
                $page++;
                continue;
            }

            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);

            $reviewNodes = $xpath->query('//div[@data-review-content-id]');

            if ($reviewNodes->length === 0) {
                $emptyPageCount++;
            } else {
                $emptyPageCount = 0;

                foreach ($reviewNodes as $reviewNode) {
                    $rating = $this->extractRating($xpath, $reviewNode);
                    if ($rating !== null && $rating >= 1 && $rating <= 5) {
                        $distribution[$rating]++;
                    }
                }

                // Found {$reviewNodes->length} reviews on page $page
            }

            $page++;
            sleep(1); // Be respectful to Shopify servers
        }

        $totalReviews = array_sum($distribution);
        // Total reviews analyzed: $totalReviews

        return $distribution;
    }

    private function extractRatingDistributionSummary($slug) {
        $url = "https://apps.shopify.com/$slug/reviews";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$html) {
            return null;
        }

        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        $percentages = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

        // First, get the total number of reviews from JSON-LD schema
        $totalReviews = 0;

        // Look for JSON-LD script with ratingCount
        if (preg_match('/"ratingCount":(\d+)/', $html, $matches)) {
            $totalReviews = (int)$matches[1];
        }

        // Fallback: look in page titles
        if ($totalReviews === 0) {
            $titleNodes = $xpath->query('//h1[contains(text(), "Reviews")] | //h2[contains(text(), "Reviews")] | //*[contains(text(), "Reviews")]');
            foreach ($titleNodes as $titleNode) {
                $titleText = $titleNode->textContent;
                if (preg_match('/Reviews\s*\((\d+)\)/', $titleText, $matches)) {
                    $totalReviews = (int)$matches[1];
                    break;
                }
            }
        }

        // Look for percentage-based distribution like "97% of ratings are 5 stars"
        $allTextNodes = $xpath->query('//text()[normalize-space()]');

        foreach ($allTextNodes as $textNode) {
            $text = trim($textNode->textContent);

            // Look for patterns like "97% of ratings are 5 stars"
            if (preg_match('/(\d+)%\s+of\s+ratings\s+are\s+(\d+)\s+star/i', $text, $matches)) {
                $percentage = (int)$matches[1];
                $rating = (int)$matches[2];

                if ($rating >= 1 && $rating <= 5) {
                    $percentages[$rating] = $percentage;
                }
            }
        }

        // Extract direct counts from the specific HTML structure
        $directCounts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

        // Look for the specific pattern in the HTML where the count appears after the rating bar
        // Based on the actual HTML structure from EasyFlow page
        $htmlLines = explode("\n", $html);

        for ($i = 0; $i < count($htmlLines); $i++) {
            $line = trim($htmlLines[$i]);

            // Look for lines that contain the rating percentage pattern
            if (preg_match('/(\d+)%\s+of\s+ratings\s+are\s+(\d+)\s+stars/', $line, $matches)) {
                $percentage = (int)$matches[1];
                $rating = (int)$matches[2];

                // Look ahead in the next few lines for the actual count
                for ($j = $i + 1; $j < min($i + 20, count($htmlLines)); $j++) {
                    $nextLine = trim($htmlLines[$j]);

                    // Look for a line that contains just a number (the count)
                    if (preg_match('/^\s*(\d+)\s*$/', $nextLine, $countMatch)) {
                        $count = (int)$countMatch[1];

                        // Validate that this count makes sense
                        if ($count > 0 && $count <= $totalReviews) {
                            // For 0% ratings, any small count (1-5) is reasonable
                            if ($percentage == 0 && $count <= 5) {
                                $directCounts[$rating] = $count;
                                break;
                            }
                            // For non-zero percentages, check if count matches roughly
                            elseif ($percentage > 0) {
                                $expectedCount = round(($percentage / 100) * $totalReviews);
                                // Be more lenient with validation - allow up to 3 difference or 20% variance
                                $allowedDifference = max(3, $expectedCount * 0.2);
                                $actualDifference = abs($count - $expectedCount);

                                if ($actualDifference <= $allowedDifference) {
                                    $directCounts[$rating] = $count;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }

        // Convert percentages to actual counts and merge with direct counts
        if ($totalReviews > 0) {
            $foundPercentages = false;
            foreach ($percentages as $rating => $percentage) {
                if ($percentage > 0) {
                    $distribution[$rating] = round(($percentage / 100) * $totalReviews);
                    $foundPercentages = true;
                }
            }

            // Override with direct counts where available (direct counts are more accurate)
            foreach ($directCounts as $rating => $count) {
                if ($count > 0) {
                    // Always use direct counts when available - they're extracted from the actual HTML
                    // and are more accurate than percentage-based calculations
                    $distribution[$rating] = $count;
                    $foundPercentages = true;
                }
            }

            if ($foundPercentages) {
                return $distribution;
            }
        }

        // Fallback: Look for direct count patterns like "5 ★ 497"
        foreach ($allTextNodes as $textNode) {
            $text = trim($textNode->textContent);

            if (preg_match('/(\d+)\s*[★⭐]\s*(\d+)/', $text, $matches)) {
                $rating = (int)$matches[1];
                $count = (int)$matches[2];

                if ($rating >= 1 && $rating <= 5) {
                    $distribution[$rating] = $count;
                }
            }
        }

        // Check if we found any meaningful distribution
        $totalFound = array_sum($distribution);
        if ($totalFound > 0) {
            return $distribution;
        }

        return null;
    }

    private function extractRatingDistributionFromPage($html) {
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        // Initialize distribution with zeros
        $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

        // Method 1: Look for rating summary section (most reliable)
        $ratingSummaryNodes = $xpath->query('//div[contains(@class, "review-summary") or contains(@class, "rating-summary")]');
        if ($ratingSummaryNodes->length > 0) {
            $summaryNode = $ratingSummaryNodes->item(0);

            // Look for individual rating counts
            for ($rating = 5; $rating >= 1; $rating--) {
                $ratingNodes = $xpath->query(".//span[contains(text(), '$rating star') or contains(text(), '$rating-star')]", $summaryNode);
                if ($ratingNodes->length > 0) {
                    $ratingText = $ratingNodes->item(0)->parentNode->textContent;
                    if (preg_match('/(\d+)/', $ratingText, $matches)) {
                        $distribution[$rating] = (int)$matches[1];
                    }
                }
            }
        }

        // Method 2: Count actual reviews on the page if summary not found
        if (array_sum($distribution) === 0) {
            $reviewNodes = $xpath->query('//div[@data-review-content-id]');

            foreach ($reviewNodes as $reviewNode) {
                $rating = $this->extractRating($xpath, $reviewNode);
                if ($rating >= 1 && $rating <= 5) {
                    $distribution[$rating]++;
                }
            }

            // If we only got first page, multiply by estimated total pages
            $totalReviews = $reviewNodes->length;
            if ($totalReviews > 0) {
                // Look for pagination to estimate total
                $paginationNodes = $xpath->query('//a[contains(@class, "pagination") or contains(@href, "page=")]');
                $maxPage = 1;

                foreach ($paginationNodes as $pageNode) {
                    $href = $pageNode->getAttribute('href');
                    if (preg_match('/page=(\d+)/', $href, $matches)) {
                        $maxPage = max($maxPage, (int)$matches[1]);
                    }
                }

                // Estimate total distribution based on first page sample
                if ($maxPage > 1) {
                    $multiplier = $maxPage;
                    foreach ($distribution as $rating => $count) {
                        $distribution[$rating] = (int)($count * $multiplier);
                    }
                }
            }
        }

        // Method 3: Look for overall rating and total count to estimate distribution
        if (array_sum($distribution) === 0) {
            $totalCountNodes = $xpath->query('//span[contains(text(), "review") and contains(text(), "total") or @class="review-count"]');
            $avgRatingNodes = $xpath->query('//span[contains(@class, "average-rating") or contains(@class, "rating-average")]');

            if ($totalCountNodes->length > 0 && $avgRatingNodes->length > 0) {
                $totalText = $totalCountNodes->item(0)->textContent;
                $avgText = $avgRatingNodes->item(0)->textContent;

                if (preg_match('/(\d+)/', $totalText, $totalMatches) &&
                    preg_match('/(\d+\.?\d*)/', $avgText, $avgMatches)) {

                    $totalCount = (int)$totalMatches[1];
                    $avgRating = (float)$avgMatches[1];

                    // Estimate distribution based on average (simplified model)
                    if ($avgRating >= 4.5) {
                        $distribution[5] = (int)($totalCount * 0.8);
                        $distribution[4] = (int)($totalCount * 0.15);
                        $distribution[3] = (int)($totalCount * 0.03);
                        $distribution[2] = (int)($totalCount * 0.01);
                        $distribution[1] = (int)($totalCount * 0.01);
                    } else if ($avgRating >= 4.0) {
                        $distribution[5] = (int)($totalCount * 0.6);
                        $distribution[4] = (int)($totalCount * 0.25);
                        $distribution[3] = (int)($totalCount * 0.1);
                        $distribution[2] = (int)($totalCount * 0.03);
                        $distribution[1] = (int)($totalCount * 0.02);
                    }
                    // Add more ranges as needed
                }
            }
        }

        return $distribution;
    }

    private function extractOverallRatingFromPage($html) {
        // Method 1: Extract rating from JSON-LD structured data (most reliable)
        if (preg_match('/"aggregateRating":\s*{[^}]*"ratingValue"\s*:\s*(\d+(?:\.\d+)?)/', $html, $matches)) {
            $rating = floatval($matches[1]);
            if ($rating >= 1 && $rating <= 5) {
                return $rating;
            }
        }

        // Method 2: Extract from script tags or data attributes
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        $scriptNodes = $xpath->query('//script[contains(text(), "ratingValue")]');
        if ($scriptNodes->length > 0) {
            foreach ($scriptNodes as $scriptNode) {
                $scriptContent = $scriptNode->textContent;
                if (preg_match('/"ratingValue"\s*:\s*(\d+(?:\.\d+)?)/', $scriptContent, $matches)) {
                    $rating = floatval($matches[1]);
                    if ($rating >= 1 && $rating <= 5) {
                        return $rating;
                    }
                }
            }
        }

        // Method 3: Look for rating in text content (fallback)
        $textNodes = $xpath->query('//text()[contains(., ".") and string-length(.) < 10]');
        foreach ($textNodes as $node) {
            $text = trim($node->textContent);
            if (preg_match('/^(\d+\.\d+)$/', $text, $matches)) {
                $rating = floatval($matches[1]);
                if ($rating >= 1 && $rating <= 5) {
                    return $rating;
                }
            }
        }

        return null;
    }

    private function extractRatingDataFromPage($html) {
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        // Initialize distribution with zeros
        $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        $overallRating = null;

        // Method 1: Extract rating from JSON-LD structured data (most reliable)
        if (preg_match('/"aggregateRating":\s*{[^}]*"ratingValue"\s*:\s*(\d+(?:\.\d+)?)/', $html, $matches)) {
            $rating = floatval($matches[1]);
            if ($rating >= 1 && $rating <= 5) {
                $overallRating = $rating;
            }
        }

        // Method 2: Extract from script tags or data attributes
        if ($overallRating === null) {
            $scriptNodes = $xpath->query('//script[contains(text(), "ratingValue")]');
            if ($scriptNodes->length > 0) {
                foreach ($scriptNodes as $scriptNode) {
                    $scriptContent = $scriptNode->textContent;
                    if (preg_match('/"ratingValue"\s*:\s*(\d+(?:\.\d+)?)/', $scriptContent, $matches)) {
                        $rating = floatval($matches[1]);
                        if ($rating >= 1 && $rating <= 5) {
                            $overallRating = $rating;
                            break;
                        }
                    }
                }
            }
        }

        // Method 3: Look for rating in text content (fallback)
        if ($overallRating === null) {
            $textNodes = $xpath->query('//text()[contains(., ".") and string-length(.) < 10]');
            foreach ($textNodes as $node) {
                $text = trim($node->textContent);
                if (preg_match('/^(\d+\.\d+)$/', $text, $matches)) {
                    $rating = floatval($matches[1]);
                    if ($rating >= 1 && $rating <= 5) {
                        $overallRating = $rating;
                        break;
                    }
                }
            }
        }

        // Method 2: Look for overall rating in other common locations
        if ($overallRating === null) {
            $ratingSelectors = [
                '//span[contains(@class, "overall-rating")]',
                '//div[contains(@class, "rating-value")]',
                '//span[contains(@class, "average-rating")]',
                '//div[contains(@class, "overall-rating")]//text()',
                '//h2[contains(text(), "★")]',
                '//div[contains(@class, "rating-summary")]//span[contains(text(), "★")]'
            ];

            foreach ($ratingSelectors as $selector) {
                $nodes = $xpath->query($selector);
                if ($nodes->length > 0) {
                    $text = trim($nodes->item(0)->textContent);
                    if (preg_match('/(\d+(?:\.\d+)?)/', $text, $matches)) {
                        $rating = floatval($matches[1]);
                        if ($rating >= 1 && $rating <= 5) {
                            $overallRating = $rating;
                            break;
                        }
                    }
                }
            }
        }

        // Extract rating distribution (reuse existing logic)
        $distribution = $this->extractRatingDistributionFromPage($html);

        return [
            'distribution' => $distribution,
            'overall_rating' => $overallRating
        ];
    }

    private function getDatabaseRatingDistribution($appName) {
        $stmt = $this->pdo->prepare('
            SELECT rating, COUNT(*) as count
            FROM reviews
            WHERE app_name = ?
            GROUP BY rating
            ORDER BY rating DESC
        ');
        $stmt->execute([$appName]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Initialize distribution with zeros
        $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

        // Fill in actual counts
        foreach ($results as $row) {
            $distribution[$row['rating']] = (int)$row['count'];
        }

        return $distribution;
    }

    /**
     * Calculate weighted average rating from rating distribution
     */
    private function calculateAverageFromDistribution($distribution) {
        $totalReviews = 0;
        $weightedSum = 0;

        foreach ($distribution as $rating => $count) {
            $totalReviews += $count;
            $weightedSum += ($rating * $count);
        }

        if ($totalReviews === 0) {
            return 0;
        }

        return round($weightedSum / $totalReviews, 1);
    }
}

// Handle the request
try {
    $appName = $_GET['app'] ?? '';

    if (empty($appName)) {
        throw new Exception('App name is required');
    }

    $analytics = new EnhancedAnalytics();
    $result = $analytics->getEnhancedAnalytics($appName);

    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
