<?php
/**
 * Link a stored review to its page on the Shopify App Store.
 *
 * Shared by the Send to Slack button and the scheduled sync so both announce a
 * review with the same link.
 */

require_once __DIR__ . '/../scraper/ShopifyReviewScraper.php';

/**
 * Link to a stored review on the Shopify App Store, or null when it can't be
 * found. Never throws: a failed lookup mustn't stop the review being shared.
 */
function shopify_review_url($conn, $review) {
    try {
        // How many stored reviews are newer tells the scraper which page to open first.
        $stmt = $conn->prepare("
            SELECT COUNT(*)
            FROM reviews
            WHERE app_name = ? AND is_active = TRUE AND review_date > ?
        ");
        $stmt->execute([$review['app_name'], $review['review_date']]);

        $scraper = new ShopifyReviewScraper();
        $url = $scraper->findReviewUrl($review, (int) $stmt->fetchColumn());

        if (!$url) {
            error_log("Shopify link: none found for review {$review['id']} ({$review['app_name']} / {$review['store_name']})");
        }

        return $url;

    } catch (Throwable $e) {
        error_log('Shopify link lookup failed — ' . $e->getMessage());
        return null;
    }
}
