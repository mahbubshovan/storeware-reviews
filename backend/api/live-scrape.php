<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../scraper/UniversalLiveScraper.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Prevent caching - always get fresh data
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Increase execution time for scraping
set_time_limit(300);
ini_set('max_execution_time', 300);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Suppress all output except JSON
ob_start();

// Handle the request
try {
    $appName = $_GET['app'] ?? null;

    if (!$appName) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'app parameter is required'
        ]);
        exit;
    }

    // Map app names to slugs for scraping
    $appSlugs = [
        'StoreSEO' => 'storeseo',
        'StoreFAQ' => 'storefaq',
        'EasyFlow' => 'easyflow',
        'BetterDocs FAQ Knowledge Base' => 'better-docs-faq-knowledge-base',
        'Vidify' => 'vidify',
        'TrustSync' => 'trustsync'
    ];

    $appSlug = $appSlugs[$appName] ?? strtolower(str_replace(' ', '-', $appName));

    // Scrape fresh data from Shopify (page 1 only for new reviews)
    $scraper = new UniversalLiveScraper();
    $scrapeResult = $scraper->scrapeFirstPageOnly($appSlug, $appName);

    // Get database connection
    $db = new Database();
    $conn = $db->getConnection();

    // First, try to get metadata from app_metadata table (contains Shopify's actual data)
    $stmt = $conn->prepare("
        SELECT
            total_reviews, overall_rating,
            five_star_total, four_star_total, three_star_total,
            two_star_total, one_star_total, last_updated
        FROM app_metadata
        WHERE app_name = ?
        LIMIT 1
    ");
    $stmt->execute([$appName]);
    $metadata = $stmt->fetch(PDO::FETCH_ASSOC);

    // Clear any buffered output
    ob_end_clean();

    if ($metadata && $metadata['total_reviews'] > 0) {
        // Use metadata from Shopify (most accurate)
        $totalReviews = (int)$metadata['total_reviews'];
        $averageRating = (float)$metadata['overall_rating'];

        $ratingDistribution = [
            5 => (int)$metadata['five_star_total'],
            4 => (int)$metadata['four_star_total'],
            3 => (int)$metadata['three_star_total'],
            2 => (int)$metadata['two_star_total'],
            1 => (int)$metadata['one_star_total']
        ];

        // Get latest 10 reviews from database for display
        $stmt = $conn->prepare("
            SELECT
                id, app_name, store_name, country_name, rating, review_content, review_date,
                earned_by, is_featured, created_at, updated_at
            FROM reviews
            WHERE app_name = ? AND is_active = TRUE
            ORDER BY review_date DESC, created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$appName]);
        $latestReviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => [
                'app_name' => $appName,
                'total_reviews' => $totalReviews,
                'average_rating' => $averageRating,
                'rating_distribution' => $ratingDistribution,
                'rating_distribution_total' => $totalReviews,
                'latest_reviews' => $latestReviews,
                'data_source' => 'live_scrape',
                'scraped_at' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        // Fallback: calculate from database if metadata not available
        $stmt = $conn->prepare("
            SELECT
                id, app_name, store_name, country_name, rating, review_content, review_date,
                earned_by, is_featured, created_at, updated_at
            FROM reviews
            WHERE app_name = ? AND is_active = TRUE
            ORDER BY review_date DESC, created_at DESC
        ");
        $stmt->execute([$appName]);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($reviews)) {
            $totalReviews = count($reviews);

            // Calculate rating distribution
            $ratingDistribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
            $totalRating = 0;

            foreach ($reviews as $review) {
                $rating = (int)$review['rating'];
                if (isset($ratingDistribution[$rating])) {
                    $ratingDistribution[$rating]++;
                }
                $totalRating += $rating;
            }

            $averageRating = $totalReviews > 0 ? round($totalRating / $totalReviews, 2) : 0;

            // Get latest 10 reviews for display
            $latestReviews = array_slice($reviews, 0, 10);

            echo json_encode([
                'success' => true,
                'data' => [
                    'app_name' => $appName,
                    'total_reviews' => $totalReviews,
                    'average_rating' => $averageRating,
                    'rating_distribution' => $ratingDistribution,
                    'rating_distribution_total' => $totalReviews,
                    'latest_reviews' => $latestReviews,
                    'data_source' => 'live_scrape',
                    'scraped_at' => date('Y-m-d H:i:s')
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'No reviews found for this app',
                'app_name' => $appName
            ]);
        }
    }

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>