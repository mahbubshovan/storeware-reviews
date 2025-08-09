<?php
require_once __DIR__ . '/scraper/UnifiedRealtimeScraper.php';

$scraper = new UnifiedRealtimeScraper();
$result = $scraper->updateApp('StoreFAQ', $scraper->getStoreFAQData());

echo json_encode([
    'success' => true,
    'message' => "Successfully scraped {$result['total_scraped']} new reviews for StoreFAQ",
    'scraped_count' => $result['total_scraped'],
    'this_month' => $result['this_month'],
    'last_30_days' => $result['last_30_days']
]);
