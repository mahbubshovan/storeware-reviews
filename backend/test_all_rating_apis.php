<?php
echo "🔍 TESTING ALL RATING DISTRIBUTION APIS\n";
echo "=======================================\n\n";

$apps = ['StoreSEO', 'StoreFAQ', 'Vidify', 'TrustSync', 'EasyFlow', 'BetterDocs FAQ'];

foreach ($apps as $appName) {
    echo "📱 $appName:\n";
    
    $encodedAppName = urlencode($appName);
    
    // Test rating distribution
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost:8000/api/review-distribution.php?app_name=$encodedAppName");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    if ($data && $data['success']) {
        $dist = $data['distribution'];
        $total = $data['total_reviews'];
        
        echo "   Total: $total reviews\n";
        echo "   5★: {$dist['five_star']} | 4★: {$dist['four_star']} | 3★: {$dist['three_star']} | 2★: {$dist['two_star']} | 1★: {$dist['one_star']}\n";
        
        // Calculate average
        $weightedSum = ($dist['five_star'] * 5) + ($dist['four_star'] * 4) + 
                      ($dist['three_star'] * 3) + ($dist['two_star'] * 2) + ($dist['one_star'] * 1);
        $average = $total > 0 ? round($weightedSum / $total, 1) : 0;
        echo "   Average: {$average}★\n";
        
        echo "   ✅ Complete rating distribution available\n";
    } else {
        echo "   ❌ Rating distribution API failed\n";
    }
    
    echo "\n";
}

echo "🎯 ALL RATING DISTRIBUTION APIS TESTED\n";
?>
