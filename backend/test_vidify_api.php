<?php
echo "=== TESTING VIDIFY API SCRAPING ===\n\n";

// Simulate the API call
$_POST['app_name'] = 'Vidify';

// Capture output
ob_start();

// Include the API file
include __DIR__ . '/api/scrape-app.php';

// Get the output
$output = ob_get_clean();

echo "API Output:\n";
echo $output;
echo "\n=== API TEST COMPLETED ===\n";
?>
