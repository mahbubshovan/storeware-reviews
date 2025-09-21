<?php
/**
 * Shopify Review Scraper for Real-Time Access Reviews
 * Scrapes fresh data from Shopify app store pages
 */
class ShopifyReviewScraper {
    
    private $appUrls = [
        'StoreSEO' => 'https://apps.shopify.com/storeseo/reviews?sort_by=newest',
        'StoreFAQ' => 'https://apps.shopify.com/storefaq/reviews?sort_by=newest',
        'EasyFlow' => 'https://apps.shopify.com/product-options-4/reviews?sort_by=newest',
        'TrustSync' => 'https://apps.shopify.com/customer-review-app/reviews?sort_by=newest',
        'BetterDocs FAQ Knowledge Base' => 'https://apps.shopify.com/betterdocs-knowledgebase/reviews?sort_by=newest',
        'Vidify' => 'https://apps.shopify.com/vidify/reviews?sort_by=newest'
    ];
    
    /**
     * Scrape all reviews for a specific app
     */
    public function scrapeAppReviews($appName) {
        if (!isset($this->appUrls[$appName])) {
            return [
                'success' => false,
                'error' => "App '$appName' not found in supported apps",
                'reviews' => []
            ];
        }
        
        $baseUrl = $this->appUrls[$appName];
        $allReviews = [];
        
        // Scrape all pages until no more reviews found
        for ($page = 1; $page <= 200; $page++) {
            $url = $baseUrl . "&page=$page";

            $html = $this->fetchPage($url);
            if (!$html) {
                error_log("Failed to fetch page $page for $appName");
                break;
            }

            $pageReviews = $this->parseReviewsFromHTML($html);
            if (empty($pageReviews)) {
                error_log("No reviews found on page $page for $appName - stopping");
                break; // No more reviews
            }

            $allReviews = array_merge($allReviews, $pageReviews);
            error_log("Page $page for $appName: Found " . count($pageReviews) . " reviews, total so far: " . count($allReviews));

            // Stop if we got less than expected (last page) - Shopify typically shows 12 reviews per page
            if (count($pageReviews) < 10) {
                error_log("Got fewer than 10 reviews on page $page for $appName - likely last page");
                break;
            }

            // Add a small delay to be respectful to Shopify servers
            usleep(500000); // 0.5 second delay
        }
        
        return [
            'success' => true,
            'reviews' => $allReviews,
            'total_count' => count($allReviews)
        ];
    }
    
    /**
     * Fetch HTML content from URL
     */
    private function fetchPage($url) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Accept-Encoding: gzip, deflate',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1',
            ],
            CURLOPT_ENCODING => 'gzip',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200 || !$html) {
            return false;
        }
        
        return $html;
    }
    
    /**
     * Parse reviews from HTML content
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

        foreach ($reviewNodes as $reviewNode) {
            $review = $this->extractReviewData($xpath, $reviewNode);
            if ($review) {
                $reviews[] = $review;
            }
        }

        return $reviews;
    }
    
    /**
     * Extract review data from a review node
     */
    private function extractReviewData($xpath, $node) {
        try {
            // Extract rating (count filled stars)
            $starNodes = $xpath->query('.//svg[contains(@class, "tw-fill-fg-primary")]', $node);
            $rating = $starNodes->length;

            // If no stars found, try alternative selectors
            if ($rating === 0) {
                $starNodes = $xpath->query('.//div[contains(@aria-label, "stars")]', $node);
                if ($starNodes->length > 0) {
                    $ariaLabel = $starNodes->item(0)->getAttribute('aria-label');
                    if (preg_match('/(\d+)\s*out\s*of\s*\d+\s*stars/', $ariaLabel, $matches)) {
                        $rating = intval($matches[1]);
                    }
                }
            }

            // Extract date - look for multiple year patterns
            $currentYear = date('Y');
            $lastYear = $currentYear - 1;
            $dateNode = $xpath->query('.//div[contains(@class, "tw-text-body-xs") and contains(@class, "tw-text-fg-tertiary") and (contains(text(), "' . $currentYear . '") or contains(text(), "' . $lastYear . '"))]', $node);
            $reviewDate = '';
            if ($dateNode->length > 0) {
                $dateText = trim($dateNode->item(0)->textContent);
                $reviewDate = date('Y-m-d', strtotime($dateText));
            }

            // If no date found, try alternative selectors
            if (empty($reviewDate)) {
                $allDateNodes = $xpath->query('.//div[contains(@class, "tw-text-fg-tertiary")]', $node);
                foreach ($allDateNodes as $dNode) {
                    $text = trim($dNode->textContent);
                    if (preg_match('/\b(January|February|March|April|May|June|July|August|September|October|November|December)\s+\d{1,2},\s+\d{4}\b/', $text)) {
                        $reviewDate = date('Y-m-d', strtotime($text));
                        break;
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

            // Extract country
            $country = 'Unknown';
            $storeContainer = $xpath->query('.//div[contains(@class, "tw-text-heading-xs") and contains(@class, "tw-text-fg-primary")]', $node);
            if ($storeContainer->length > 0) {
                $parentContainer = $storeContainer->item(0)->parentNode;
                $childDivs = $xpath->query('./div', $parentContainer);

                foreach ($childDivs as $childDiv) {
                    $text = trim($childDiv->textContent);
                    if (!empty($text) &&
                        !$childDiv->hasAttribute('title') &&
                        !preg_match('/\d{4}/', $text) &&
                        !preg_match('/\d+\s+(day|week|month|year)/', $text) &&
                        !stripos($text, 'using') &&
                        !stripos($text, 'replied') &&
                        strlen($text) > 2 && strlen($text) < 30) {

                        if (preg_match('/^[A-Za-z\s]+$/', $text)) {
                            $country = $text;
                            break;
                        }
                    }
                }
            }

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
            return null;
        }
    }
    

}
