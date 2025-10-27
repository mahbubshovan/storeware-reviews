<?php
require_once __DIR__ . '/DatabaseManager.php';

/**
 * Synchronizes access_reviews table with main reviews table
 * Manages last 30 days reviews with editable "Earned By" field
 */
class AccessReviewsSync {
    private $dbManager;
    
    public function __construct() {
        $this->dbManager = new DatabaseManager();
    }
    
    /**
     * Synchronize access_reviews table with main reviews table
     * This should be called after each scraping session
     */
    public function syncAccessReviews() {
        echo "\n=== SYNCING ACCESS REVIEWS ===\n";
        
        try {
            $conn = $this->dbManager->getConnection();
            
            // Start transaction
            $conn->beginTransaction();
            
            // 1. Remove reviews older than 30 days from access_reviews
            $this->removeOldReviews($conn);

            // 2. SMART SYNC: Add/update reviews and preserve assignments (BEFORE removing orphans)
            $this->addNewReviews($conn);

            // 3. Remove truly orphaned reviews (after smart matching)
            $this->removeOrphanedReviews($conn);
            
            // Commit transaction
            $conn->commit();
            
            echo "✅ Access reviews sync completed successfully\n";
            
        } catch (Exception $e) {
            if (isset($conn)) {
                $conn->rollback();
            }
            echo "❌ Error syncing access reviews: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
    
    /**
     * Remove reviews older than 30 days from access_reviews table
     */
    private function removeOldReviews($conn) {
        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
        
        $stmt = $conn->prepare("DELETE FROM access_reviews WHERE review_date < ?");
        $stmt->execute([$thirtyDaysAgo]);
        
        $deletedCount = $stmt->rowCount();
        echo "Removed $deletedCount old reviews (older than $thirtyDaysAgo)\n";
    }

    /**
     * Remove reviews from access_reviews that no longer exist in the main reviews table
     * This handles cases where reviews are removed from the original Shopify page
     */
    private function removeOrphanedReviews($conn) {
        // Find access_reviews that don't have corresponding entries in the main reviews table
        $stmt = $conn->prepare("
            SELECT ar.id, ar.app_name, ar.earned_by, ar.review_date
            FROM access_reviews ar
            LEFT JOIN reviews r ON ar.original_review_id = r.id
            WHERE r.id IS NULL
        ");

        $stmt->execute();
        $orphanedReviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($orphanedReviews)) {
            echo "No orphaned reviews to remove from access_reviews\n";
            return;
        }

        // Log which assigned reviews are being removed
        $assignedOrphans = array_filter($orphanedReviews, function($review) {
            return !empty($review['earned_by']);
        });

        if (!empty($assignedOrphans)) {
            echo "Removing " . count($assignedOrphans) . " assigned reviews that no longer exist on source pages:\n";
            foreach ($assignedOrphans as $review) {
                echo "  - {$review['app_name']} review from {$review['review_date']} (assigned to: {$review['earned_by']})\n";
            }
        }

        // Remove orphaned reviews
        $deleteStmt = $conn->prepare("
            DELETE FROM access_reviews
            WHERE id IN (" . str_repeat('?,', count($orphanedReviews) - 1) . "?)
        ");

        $orphanedIds = array_column($orphanedReviews, 'id');
        $deleteStmt->execute($orphanedIds);

        $deletedCount = $deleteStmt->rowCount();
        echo "Removed $deletedCount orphaned reviews (no longer exist on source pages)\n";
    }

    /**
     * Add new reviews from last 30 days to access_reviews table
     * SMART SYNC: Preserves existing earned_by values by matching content and date
     */
    private function addNewReviews($conn) {
        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));

        // Get all reviews from last 30 days
        $stmt = $conn->prepare("
            SELECT r.id, r.app_name, r.review_date, r.review_content, r.country_name, r.rating
            FROM reviews r
            WHERE r.review_date >= ?
            ORDER BY r.app_name, r.review_date DESC
        ");

        $stmt->execute([$thirtyDaysAgo]);
        $allRecentReviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($allRecentReviews)) {
            echo "No recent reviews found to sync\n";
            return;
        }

        $addedCount = 0;
        $preservedCount = 0;

        foreach ($allRecentReviews as $review) {
            // Check if this review already exists in access_reviews by matching content and date
            // This handles cases where reviews were re-scraped and got new IDs
            $existingStmt = $conn->prepare("
                SELECT id, earned_by, original_review_id
                FROM access_reviews
                WHERE app_name = ?
                AND review_date = ?
                AND review_content = ?
                LIMIT 1
            ");

            $existingStmt->execute([
                $review['app_name'],
                $review['review_date'],
                $review['review_content']
            ]);

            $existingReview = $existingStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingReview) {
                // Review exists - update the original_review_id to maintain the link
                // and preserve the earned_by assignment
                // ALWAYS update country_name to ensure accuracy (fix Unknown countries)
                $updateStmt = $conn->prepare("
                    UPDATE access_reviews
                    SET original_review_id = ?,
                        country_name = COALESCE(NULLIF(?, ''), NULLIF(?, 'Unknown'), country_name),
                        rating = ?
                    WHERE id = ?
                ");

                $success = $updateStmt->execute([
                    $review['id'],
                    $review['country_name'], // First preference: new country data
                    $review['country_name'], // Second preference: if not 'Unknown'
                    $review['rating'],
                    $existingReview['id']
                ]);

                if ($success && !empty($existingReview['earned_by'])) {
                    $preservedCount++;
                    echo "✅ Preserved assignment: {$review['app_name']} review from {$review['review_date']} (assigned to: {$existingReview['earned_by']}) - Updated original_review_id to {$review['id']}, country: {$review['country_name']}\n";
                } elseif ($success) {
                    echo "🔄 Updated unassigned review link: {$review['app_name']} review from {$review['review_date']} - Updated original_review_id to {$review['id']}, country: {$review['country_name']}\n";
                } else {
                    echo "❌ Failed to update review link for {$review['app_name']} review from {$review['review_date']}\n";
                }
            } else {
                // New review - add it as unassigned
                $insertStmt = $conn->prepare("
                    INSERT INTO access_reviews (app_name, review_date, review_content, country_name, rating, original_review_id)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                $success = $insertStmt->execute([
                    $review['app_name'],
                    $review['review_date'],
                    $review['review_content'],
                    $review['country_name'],
                    $review['rating'],
                    $review['id']
                ]);

                if ($success) {
                    $addedCount++;
                }
            }
        }

        echo "Added $addedCount new reviews, preserved $preservedCount assignments\n";

        // Fix any remaining unknown countries
        $this->fixUnknownCountries($conn);
    }

    /**
     * Fix unknown countries in access_reviews by copying from main reviews table
     */
    private function fixUnknownCountries($conn) {
        echo "🌍 Fixing unknown countries in access_reviews...\n";

        $stmt = $conn->prepare("
            UPDATE access_reviews ar
            INNER JOIN reviews r ON ar.original_review_id = r.id
            SET ar.country_name = r.country_name
            WHERE (ar.country_name = 'Unknown' OR ar.country_name IS NULL OR ar.country_name = '')
            AND r.country_name IS NOT NULL
            AND r.country_name != 'Unknown'
            AND r.country_name != ''
        ");

        $stmt->execute();
        $fixed = $stmt->rowCount();

        if ($fixed > 0) {
            echo "✅ Fixed $fixed unknown countries in access_reviews\n";
        } else {
            echo "✅ No unknown countries found to fix\n";
        }
    }
    
    /**
     * Get all access reviews grouped by app name with date filtering
     */
    public function getAccessReviews($dateRange = '30_days') {
        try {
            $conn = $this->dbManager->getConnection();

            // Calculate date filter based on range
            $dateFilter = $this->getDateFilter($dateRange);

            $stmt = $conn->prepare("
                SELECT app_name, review_date, review_content, country_name, rating, earned_by, id, original_review_id
                FROM access_reviews
                WHERE review_date >= ?
                ORDER BY app_name, review_date DESC
            ");

            $stmt->execute([$dateFilter]);
            $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Group by app name
            $groupedReviews = [];
            foreach ($reviews as $review) {
                $appName = $review['app_name'];
                if (!isset($groupedReviews[$appName])) {
                    $groupedReviews[$appName] = [];
                }
                $groupedReviews[$appName][] = $review;
            }

            return $groupedReviews;

        } catch (Exception $e) {
            echo "❌ Error getting access reviews: " . $e->getMessage() . "\n";
            return [];
        }
    }

    /**
     * Calculate date filter based on date range selection
     */
    private function getDateFilter($dateRange) {
        switch ($dateRange) {
            case '7_days':
                return date('Y-m-d', strtotime('-7 days'));
            case 'this_month':
                return date('Y-m-01'); // First day of current month
            case 'last_month':
                return date('Y-m-01', strtotime('first day of last month'));
            case '30_days':
            default:
                return date('Y-m-d', strtotime('-30 days'));
        }
    }
    
    /**
     * Update earned_by field for a specific review
     */
    public function updateEarnedBy($reviewId, $earnedBy) {
        try {
            $conn = $this->dbManager->getConnection();
            
            $stmt = $conn->prepare("
                UPDATE access_reviews 
                SET earned_by = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            
            $success = $stmt->execute([$earnedBy, $reviewId]);
            
            if ($success && $stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Earned By updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Review not found or no changes made'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error updating Earned By: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get statistics for access reviews with date filtering
     */
    public function getAccessReviewsStats($dateRange = '30_days') {
        try {
            $conn = $this->dbManager->getConnection();

            // Calculate date filter based on range
            $dateFilter = $this->getDateFilter($dateRange);

            // Total reviews in date range
            $stmt = $conn->prepare("SELECT COUNT(*) FROM access_reviews WHERE review_date >= ?");
            $stmt->execute([$dateFilter]);
            $totalReviews = $stmt->fetchColumn();

            // Reviews with earned_by assigned
            $stmt = $conn->prepare("SELECT COUNT(*) FROM access_reviews WHERE review_date >= ? AND earned_by IS NOT NULL AND earned_by != ''");
            $stmt->execute([$dateFilter]);
            $assignedReviews = $stmt->fetchColumn();

            // Reviews by app
            $stmt = $conn->prepare("
                SELECT app_name, COUNT(*) as count
                FROM access_reviews
                WHERE review_date >= ?
                GROUP BY app_name
                ORDER BY count DESC
            ");
            $stmt->execute([$dateFilter]);
            $reviewsByApp = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get actual total counts from main reviews table for each app
            $totalShopifyReviews = 0;
            $appTotals = [];

            foreach ($reviewsByApp as $app) {
                $appName = $app['app_name'];

                // Get actual total from reviews table
                $totalStmt = $conn->prepare("
                    SELECT COUNT(*) as total FROM reviews
                    WHERE app_name = ? AND is_active = TRUE
                ");
                $totalStmt->execute([$appName]);
                $appTotal = $totalStmt->fetchColumn();
                $appTotals[$appName] = $appTotal;
                $totalShopifyReviews += $appTotal;
            }

            return [
                'total_reviews' => $totalReviews,
                'shopify_total_reviews' => $totalShopifyReviews, // Add Shopify total for display
                'assigned_reviews' => $assignedReviews,
                'unassigned_reviews' => $totalReviews - $assignedReviews,
                'reviews_by_app' => $reviewsByApp
            ];

        } catch (Exception $e) {
            echo "❌ Error getting access reviews stats: " . $e->getMessage() . "\n";
            return null;
        }
    }
}
