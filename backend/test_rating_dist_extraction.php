<?php
require_once __DIR__ . '/scraper/RatingDistributionExtractor.php';

echo "🔍 Testing Rating Distribution Extraction for StoreSEO\n";

$extractor = new RatingDistributionExtractor();
$result = $extractor->extractRatingDistribution('storeseo', 'StoreSEO');

if ($result) {
    echo "✅ Successfully extracted rating distribution!\n";
} else {
    echo "❌ Failed to extract rating distribution\n";
}
?>
