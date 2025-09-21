<?php
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../scraper/PreciseRatingExtractor.php';

/**
 * API to refresh rating distributions for all apps
 */

try {
    $apps = [
        'StoreSEO' => 'storeseo',
        'StoreFAQ' => 'storefaq',
        'Vidify' => 'vidify',
        'TrustSync' => 'customer-review-app',
        'EasyFlow' => 'product-options-4',
        'BetterDocs FAQ Knowledge Base' => 'betterdocs-knowledgebase'
    ];
    
    $extractor = new PreciseRatingExtractor();
    $results = [];
    
    foreach ($apps as $appName => $appSlug) {
        $result = $extractor->extractPreciseRatingDistribution($appSlug, $appName);
        
        if ($result) {
            $results[$appName] = [
                'success' => true,
                'total_reviews' => $result['total_reviews'],
                'overall_rating' => $result['overall_rating'],
                'distribution' => [
                    'five_star' => $result['five_star'],
                    'four_star' => $result['four_star'],
                    'three_star' => $result['three_star'],
                    'two_star' => $result['two_star'],
                    'one_star' => $result['one_star']
                ]
            ];
        } else {
            $results[$appName] = [
                'success' => false,
                'error' => 'Failed to extract rating distribution'
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Rating distributions refreshed for all apps',
        'results' => $results
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to refresh rating distributions: ' . $e->getMessage()
    ]);
}
?>
