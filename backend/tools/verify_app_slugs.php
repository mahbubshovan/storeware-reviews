<?php
/**
 * Tool to verify correct Shopify app slugs
 */

$testApps = [
    // ORIGINAL 6 APPS FROM THE SYSTEM
    'StoreSEO' => ['storeseo'],
    'StoreFAQ' => ['storefaq', 'store-faq'],
    'Vidify' => ['vidify', 'video-king', 'vidify-video-king'],
    'TrustSync' => ['customer-review-app', 'trustsync', 'trust-sync', 'product-reviews-addon'],
    'EasyFlow' => ['product-options-4', 'easyflow', 'easy-flow', 'product-options'],
    'BetterDocs FAQ' => ['betterdocs-knowledgebase', 'betterdocs-faq', 'betterdocs', 'better-docs-faq', 'helpdesk-faq']
];

function testAppSlug($slug) {
    $url = "https://apps.shopify.com/$slug/reviews";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
        CURLOPT_NOBODY => true, // HEAD request only
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);
    
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode === 200;
}

echo "🔍 Verifying Shopify App Slugs...\n\n";

$validSlugs = [];

foreach ($testApps as $appName => $slugs) {
    echo "📱 Testing $appName:\n";
    
    foreach ($slugs as $slug) {
        echo "   Testing '$slug'... ";
        
        if (testAppSlug($slug)) {
            echo "✅ VALID\n";
            $validSlugs[$appName] = $slug;
            break;
        } else {
            echo "❌ Invalid\n";
        }
    }
    
    if (!isset($validSlugs[$appName])) {
        echo "   ⚠️ No valid slug found for $appName\n";
    }
    
    echo "\n";
}

echo "📋 VALID SLUGS FOUND:\n";
echo "===================\n";
foreach ($validSlugs as $appName => $slug) {
    echo "'$appName' => '$slug',\n";
}

echo "\n🎯 Use these slugs in the UniversalLiveScraper mapping!\n";
?>
