<?php
/**
 * Archive Reviews Manager
 * Identifies and marks archived reviews (no longer on live Shopify pages)
 * Only counts live reviews in the Access Reviews page
 */

class ArchiveReviewsManager {
    private $conn;
    private $apps = [
        'StoreSEO' => 'storeseo',
        'StoreFAQ' => 'storefaq',
        'EasyFlow' => 'product-options-4',
        'TrustSync' => 'customer-review-app',
        'BetterDocs FAQ Knowledge Base' => 'betterdocs-knowledgebase',
        'Vidify' => 'vidify'
    ];

    public function __construct($connection) {
        $this->conn = $connection;
    }

    /**
     * Get live review count from Shopify page
     * Uses JSON-LD ratingCount for accurate total
     */
    public function getLiveReviewCountFromShopify($appSlug) {
        try {
            $url = "https://apps.shopify.com/{$appSlug}/reviews";
            $html = @file_get_contents($url);

            if (!$html) {
                return null;
            }

            // Method 1: Extract total reviews count from JSON-LD ratingCount (most accurate)
            if (preg_match('/"ratingCount":(\d+)/', $html, $matches)) {
                return (int)$matches[1];
            }

            // Method 2: Fallback to aria-label if JSON-LD not found
            if (preg_match('/aria-label="(\d+)\s+total\s+reviews"/', $html, $matches)) {
                return (int)$matches[1];
            }

            return null;
        } catch (Exception $e) {
            error_log("Error getting live count for {$appSlug}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get rating distribution from Shopify page
     * Returns array with counts for each star rating
     */
    public function getRatingDistributionFromShopify($appSlug) {
        try {
            $url = "https://apps.shopify.com/{$appSlug}/reviews";
            $html = @file_get_contents($url);

            if (!$html) {
                return null;
            }

            $distribution = [
                5 => 0,
                4 => 0,
                3 => 0,
                2 => 0,
                1 => 0
            ];

            // Extract rating counts from aria-label attributes
            // Pattern: aria-label="514 total reviews" for 5-star, etc.
            if (preg_match_all('/aria-label="(\d+)\s+total\s+reviews"/', $html, $matches)) {
                $counts = $matches[1];

                // The order in the HTML is typically: 5-star, 4-star, 3-star, 2-star, 1-star
                if (isset($counts[0])) $distribution[5] = (int)$counts[0];
                if (isset($counts[1])) $distribution[4] = (int)$counts[1];
                if (isset($counts[2])) $distribution[3] = (int)$counts[2];
                if (isset($counts[3])) $distribution[2] = (int)$counts[3];
                if (isset($counts[4])) $distribution[1] = (int)$counts[4];
            }

            return $distribution;
        } catch (Exception $e) {
            error_log("Error getting rating distribution for {$appSlug}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Mark reviews as archived if they exceed live count
     * Keeps newest reviews as active, marks oldest as archived
     */
    public function markArchivedReviews($appName, $liveCount) {
        if ($liveCount === null) {
            return ['status' => 'error', 'message' => 'Could not get live count'];
        }

        // Get current active count
        $query = "SELECT COUNT(*) as count FROM reviews WHERE app_name = ? AND is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$appName]);
        $currentActive = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        if ($currentActive <= $liveCount) {
            return [
                'status' => 'ok',
                'app' => $appName,
                'live_count' => $liveCount,
                'active_count' => $currentActive,
                'archived' => 0,
                'message' => 'No reviews to archive'
            ];
        }

        // Need to archive oldest reviews
        $toArchive = $currentActive - $liveCount;

        // Get oldest reviews to archive
        $query = "
            SELECT id FROM reviews 
            WHERE app_name = ? AND is_active = 1 
            ORDER BY review_date ASC, created_at ASC 
            LIMIT ?
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$appName, $toArchive]);
        $reviewsToArchive = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($reviewsToArchive)) {
            return ['status' => 'error', 'message' => 'No reviews found to archive'];
        }

        // Mark as archived
        $placeholders = implode(',', array_fill(0, count($reviewsToArchive), '?'));
        $updateQuery = "UPDATE reviews SET is_active = 0 WHERE id IN ({$placeholders})";
        $updateStmt = $this->conn->prepare($updateQuery);
        $updateStmt->execute($reviewsToArchive);

        return [
            'status' => 'success',
            'app' => $appName,
            'live_count' => $liveCount,
            'archived' => $toArchive,
            'message' => "Archived {$toArchive} old reviews"
        ];
    }

    /**
     * Sync all apps - mark archived reviews
     */
    public function syncAllApps() {
        $results = [];

        foreach ($this->apps as $appName => $appSlug) {
            $liveCount = $this->getLiveReviewCountFromShopify($appSlug);
            $result = $this->markArchivedReviews($appName, $liveCount);
            $results[$appName] = $result;
            
            echo "✅ {$appName}: " . $result['message'] . "\n";
        }

        return $results;
    }

    /**
     * Get only live reviews count for an app
     */
    public function getLiveReviewCount($appName) {
        $query = "SELECT COUNT(*) as count FROM reviews WHERE app_name = ? AND is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$appName]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    /**
     * Get live reviews for pagination
     */
    public function getLiveReviews($appName, $page = 1, $limit = 15) {
        $offset = ($page - 1) * $limit;
        
        $query = "
            SELECT * FROM reviews 
            WHERE app_name = ? AND is_active = 1 
            ORDER BY review_date DESC, created_at DESC 
            LIMIT ? OFFSET ?
        ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$appName, $limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

