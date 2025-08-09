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
            
            // 2. Add new reviews from last 30 days
            $this->addNewReviews($conn);
            
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
     * Add new reviews from last 30 days to access_reviews table
     * Preserves existing earned_by values
     */
    private function addNewReviews($conn) {
        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
        
        // Get all reviews from last 30 days that aren't already in access_reviews
        $stmt = $conn->prepare("
            SELECT r.id, r.app_name, r.review_date, r.review_content, r.country_name
            FROM reviews r
            LEFT JOIN access_reviews ar ON r.id = ar.original_review_id
            WHERE r.review_date >= ? 
            AND ar.id IS NULL
            ORDER BY r.app_name, r.review_date DESC
        ");
        
        $stmt->execute([$thirtyDaysAgo]);
        $newReviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($newReviews)) {
            echo "No new reviews to add to access_reviews\n";
            return;
        }
        
        // Insert new reviews into access_reviews
        $insertStmt = $conn->prepare("
            INSERT INTO access_reviews (app_name, review_date, review_content, country_name, original_review_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $addedCount = 0;
        foreach ($newReviews as $review) {
            $success = $insertStmt->execute([
                $review['app_name'],
                $review['review_date'],
                $review['review_content'],
                $review['country_name'],
                $review['id']
            ]);
            
            if ($success) {
                $addedCount++;
            }
        }
        
        echo "Added $addedCount new reviews to access_reviews\n";
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
                SELECT app_name, review_date, review_content, country_name, earned_by, id, original_review_id
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

            return [
                'total_reviews' => $totalReviews,
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
