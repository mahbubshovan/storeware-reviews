<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Prevent caching - always get fresh data
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Increase execution time for scraping
set_time_limit(120);
ini_set('max_execution_time', 120);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

class LiveScraper {
    private $appSlugs = [
        'StoreSEO' => 'storeseo',
        'StoreFAQ' => 'storefaq',
        'EasyFlow' => 'product-options-4',
        'BetterDocs FAQ Knowledge Base' => 'betterdocs-knowledgebase',
        'Vidify' => 'vidify',
        'TrustSync' => 'customer-review-app'
    ];

    public function scrapeAppLive($appName) {
        try {
            if (!isset($this->appSlugs[$appName])) {
                return [
                    'success' => false,
                    'error' => "App '$appName' not found",
                    'supported_apps' => array_keys($this->appSlugs)
                ];
            }

            $slug = $this->appSlugs[$appName];
            $url = "https://apps.shopify.com/$slug/reviews?page=1";

            error_log("🌐 Live scraping: $appName from $url");

            // Fetch the page
            $html = $this->fetchPage($url);
            if (!$html) {
                return [
                    'success' => false,
                    'error' => 'Failed to fetch page from Shopify'
                ];
            }

            // Parse the HTML
            $dom = new DOMDocument();
            @$dom->loadHTML($html);
            $xpath = new DOMXPath($dom);

            // Extract total review count
            $totalCount = $this->extractTotalReviewCount($xpath);

            // Extract overall rating
            $overallRating = $this->extractOverallRating($xpath);

            // Extract rating distribution
            $ratingDistribution = $this->extractRatingDistribution($xpath);

            // Extract latest reviews
            $reviews = $this->extractReviews($xpath);

            error_log("✅ Live scrape successful: $totalCount reviews, rating: $overallRating");

            return [
                'success' => true,
                'data' => [
                    'app_name' => $appName,
                    'total_reviews' => $totalCount,
                    'average_rating' => $overallRating,
                    'rating_distribution' => $ratingDistribution,
                    'latest_reviews' => $reviews,
                    'data_source' => 'live_scrape',
                    'scraped_at' => date('Y-m-d H:i:s'),
                    'note' => 'This data was scraped directly from the live Shopify app store page'
                ]
            ];

        } catch (Exception $e) {
            error_log("❌ Live scrape error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function fetchPage($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("HTTP Error: $httpCode for $url");
            return null;
        }

        return $response;
    }

    private function extractTotalReviewCount($xpath) {
        // Try multiple selectors to find total review count
        $selectors = [
            "//span[contains(text(), 'review')]",
            "//div[@class='ReviewsCount']",
            "//span[@class='ReviewCount']"
        ];

        foreach ($selectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes->length > 0) {
                $text = $nodes->item(0)->textContent;
                if (preg_match('/(\d+)\s*review/i', $text, $matches)) {
                    return (int)$matches[1];
                }
            }
        }

        return 0;
    }

    private function extractOverallRating($xpath) {
        // Try to find the overall rating
        $selectors = [
            "//span[@class='Rating']",
            "//div[@class='OverallRating']//span",
            "//span[contains(@class, 'rating')]"
        ];

        foreach ($selectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes->length > 0) {
                $text = $nodes->item(0)->textContent;
                if (preg_match('/(\d+\.?\d*)\s*out of\s*5/i', $text, $matches)) {
                    return (float)$matches[1];
                }
                if (preg_match('/^(\d+\.?\d*)/', trim($text), $matches)) {
                    $rating = (float)$matches[1];
                    if ($rating > 0 && $rating <= 5) {
                        return $rating;
                    }
                }
            }
        }

        return 0;
    }

    private function extractRatingDistribution($xpath) {
        $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

        // Try to find rating distribution bars
        $selectors = [
            "//div[@class='RatingBar']",
            "//div[contains(@class, 'rating-bar')]",
            "//span[@class='RatingCount']"
        ];

        foreach ($selectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes->length > 0) {
                foreach ($nodes as $node) {
                    $text = $node->textContent;
                    if (preg_match('/(\d+)\s*star.*?(\d+)/i', $text, $matches)) {
                        $stars = (int)$matches[1];
                        $count = (int)$matches[2];
                        if (isset($distribution[$stars])) {
                            $distribution[$stars] = $count;
                        }
                    }
                }
            }
        }

        return $distribution;
    }

    private function extractReviews($xpath) {
        $reviews = [];

        // Find review elements
        $reviewNodes = $xpath->query("//div[@class='Review']");
        if ($reviewNodes->length === 0) {
            $reviewNodes = $xpath->query("//div[contains(@class, 'review')]");
        }

        for ($i = 0; $i < min($reviewNodes->length, 10); $i++) {
            $node = $reviewNodes->item($i);
            $review = [
                'reviewer_name' => $this->extractReviewerName($node, $xpath),
                'rating' => $this->extractReviewRating($node, $xpath),
                'date' => $this->extractReviewDate($node, $xpath),
                'title' => $this->extractReviewTitle($node, $xpath),
                'text' => $this->extractReviewText($node, $xpath)
            ];
            $reviews[] = $review;
        }

        return $reviews;
    }

    private function extractReviewerName($node, $xpath) {
        $nameNodes = $xpath->query(".//span[@class='ReviewerName']", $node);
        if ($nameNodes->length > 0) {
            return trim($nameNodes->item(0)->textContent);
        }
        return 'Anonymous';
    }

    private function extractReviewRating($node, $xpath) {
        $ratingNodes = $xpath->query(".//span[@class='ReviewRating']", $node);
        if ($ratingNodes->length > 0) {
            $text = $ratingNodes->item(0)->textContent;
            if (preg_match('/(\d+)/', $text, $matches)) {
                return (int)$matches[1];
            }
        }
        return 0;
    }

    private function extractReviewDate($node, $xpath) {
        $dateNodes = $xpath->query(".//span[@class='ReviewDate']", $node);
        if ($dateNodes->length > 0) {
            return trim($dateNodes->item(0)->textContent);
        }
        return date('Y-m-d');
    }

    private function extractReviewTitle($node, $xpath) {
        $titleNodes = $xpath->query(".//span[@class='ReviewTitle']", $node);
        if ($titleNodes->length > 0) {
            return trim($titleNodes->item(0)->textContent);
        }
        return '';
    }

    private function extractReviewText($node, $xpath) {
        $textNodes = $xpath->query(".//p[@class='ReviewText']", $node);
        if ($textNodes->length > 0) {
            return trim($textNodes->item(0)->textContent);
        }
        return '';
    }
}

// Handle the request
try {
    $appName = $_GET['app'] ?? null;

    if (!$appName) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'app parameter is required'
        ]);
        exit;
    }

    $scraper = new LiveScraper();
    $result = $scraper->scrapeAppLive($appName);

    echo json_encode($result);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

