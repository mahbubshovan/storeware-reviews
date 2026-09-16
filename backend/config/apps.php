<?php
/**
 * The apps this dashboard tracks, in the order they appear in the UI, each
 * mapped to its Shopify App Store slug.
 *
 * Kept in one place so the app dropdown (api/available-apps.php), the Analytics
 * tab's "Sync Reviews" (api/live-scrape.php) and the scheduled sync
 * (cron/sync_all_apps.php) can't drift apart.
 */

function shopify_apps() {
    return [
        'StoreSEO' => 'storeseo',
        'StoreFAQ' => 'storefaq',
        'Vidify' => 'vidify',
        'TrustSync' => 'customer-review-app',
        'EasyFlow' => 'product-options-4',
        'BetterDocs FAQ Knowledge Base' => 'betterdocs-knowledgebase',
    ];
}

/**
 * App names, in display order.
 */
function shopify_app_names() {
    return array_keys(shopify_apps());
}

/**
 * Shopify slug for an app name, falling back to a slugified name so an app
 * that reached the database without being listed here still resolves.
 */
function shopify_app_slug($appName) {
    $apps = shopify_apps();

    return $apps[$appName] ?? strtolower(str_replace(' ', '-', (string) $appName));
}
