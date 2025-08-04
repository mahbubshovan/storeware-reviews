<?php
require_once __DIR__ . '/utils/DatabaseManager.php';

/**
 * StoreFAQ scraper focused on extracting real dates from HTML
 */

function scrapePage($pageNumber) {
    $url = "https://apps.shopify.com/storefaq/reviews?sort_by=newest&page=" . $pageNumber;
    echo "Fetching page $pageNumber: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || !$html) {
        echo "❌ Failed to fetch page $pageNumber\n";
        return [];
    }
    
    // Extract all date strings from the page
    $datePattern = '/(?:January|February|March|April|May|June|July|August|September|October|November|December)\s+\d{1,2},\s+\d{4}/';
    preg_match_all($datePattern, $html, $matches);
    
    $dates = array_unique($matches[0]);
    echo "Found " . count($dates) . " unique dates on page $pageNumber:\n";
    
    foreach ($dates as $date) {
        $parsedDate = date('Y-m-d', strtotime($date));
        echo "  $date -> $parsedDate\n";
    }
    
    return $dates;
}

echo "=== STOREFAQ REAL DATE ANALYSIS ===\n\n";

// Scrape first 3 pages to see date distribution
$allDates = [];
for ($page = 1; $page <= 3; $page++) {
    $pageDates = scrapePage($page);
    $allDates = array_merge($allDates, $pageDates);
    echo "\n";
    sleep(2); // Be nice to the server
}

// Analyze the date distribution
echo "=== DATE DISTRIBUTION ANALYSIS ===\n";
$datesByMonth = [];
$parsedDates = [];

foreach (array_unique($allDates) as $dateStr) {
    $parsedDate = date('Y-m-d', strtotime($dateStr));
    $parsedDates[] = $parsedDate;
    $monthKey = date('Y-m', strtotime($dateStr));
    
    if (!isset($datesByMonth[$monthKey])) {
        $datesByMonth[$monthKey] = 0;
    }
    $datesByMonth[$monthKey]++;
}

ksort($datesByMonth);
foreach ($datesByMonth as $month => $count) {
    $monthName = date('F Y', strtotime($month . '-01'));
    echo "$monthName: $count unique dates\n";
}

// Calculate what the filtering should show
sort($parsedDates);
$earliestDate = $parsedDates[0];
$latestDate = end($parsedDates);

echo "\nDate range: $earliestDate to $latestDate\n";

// Count for July 2025
$july2025Count = 0;
foreach ($parsedDates as $date) {
    if (date('Y-m', strtotime($date)) === '2025-07') {
        $july2025Count++;
    }
}

// Count for last 30 days (from July 29, 2025)
$thirtyDaysAgo = date('Y-m-d', strtotime('2025-07-29 -30 days')); // 2025-06-29
$last30DaysCount = 0;
foreach ($parsedDates as $date) {
    if ($date >= $thirtyDaysAgo) {
        $last30DaysCount++;
    }
}

echo "\nExpected filtering results:\n";
echo "This month (July 2025): $july2025Count unique dates\n";
echo "Last 30 days (from $thirtyDaysAgo): $last30DaysCount unique dates\n";

echo "\n🎯 This shows the actual date distribution from StoreFAQ reviews.\n";
echo "Each date might have multiple reviews, so the actual review counts will be higher.\n";
?>
