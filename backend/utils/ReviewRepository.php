<?php
/**
 * Review Repository Manager
 * Handles comprehensive review storage, pagination, and filtering
 * Acts as the master repository for all review data
 */

require_once __DIR__ . '/DatabaseManager.php';

class ReviewRepository {
    private $conn;
    private $dbManager;
    
    public function __construct() {
        $this->dbManager = new DatabaseManager();
        $this->conn = $this->dbManager->getConnection();
    }
    
    /**
     * Add or update a review in the repository
     * Never deletes, only adds or updates existing reviews
     */
    public function addReview($appName, $storeName, $countryName, $rating, $reviewContent, $reviewDate, $sourceType = 'live_scrape') {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO review_repository 
                (app_name, store_name, country_name, rating, review_content, review_date, source_type, scraped_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    rating = VALUES(rating),
                    review_content = VALUES(review_content),
                    country_name = VALUES(country_name),
                    updated_at = NOW(),
                    scraped_at = NOW(),
                    is_active = TRUE
            ");
            
            return $stmt->execute([
                $appName, $storeName, $countryName, $rating, $reviewContent, $reviewDate, $sourceType
            ]);
        } catch (Exception $e) {
            error_log("Error adding review to repository: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clear all data for a specific app (for fresh scraping)
     */
    public function clearAppData($appName) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM review_repository WHERE app_name = ?");
            $result = $stmt->execute([$appName]);

            if ($result) {
                $deletedCount = $stmt->rowCount();
                error_log("Cleared $deletedCount reviews for $appName");
                return $deletedCount;
            }
            return 0;
        } catch (Exception $e) {
            error_log("Error clearing app data for $appName: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Migrate existing reviews from the old reviews table
     */
    public function migrateExistingReviews() {
        try {
            $stmt = $this->conn->prepare("
                INSERT IGNORE INTO review_repository 
                (app_name, store_name, country_name, rating, review_content, review_date, original_review_id, created_at, scraped_at, source_type)
                SELECT 
                    r.app_name, 
                    r.store_name, 
                    r.country_name, 
                    r.rating, 
                    r.review_content, 
                    r.review_date, 
                    r.id, 
                    r.created_at, 
                    r.created_at, 
                    'live_scrape'
                FROM reviews r
                WHERE r.id NOT IN (
                    SELECT COALESCE(original_review_id, 0) 
                    FROM review_repository 
                    WHERE original_review_id IS NOT NULL
                )
            ");
            
            $stmt->execute();
            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Error migrating reviews: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get paginated reviews with filtering
     */
    public function getPaginatedReviews($page = 1, $limit = 10, $filters = []) {
        try {
            $offset = ($page - 1) * $limit;
            
            // Build WHERE clause
            $whereConditions = ['is_active = TRUE'];
            $params = [];
            
            if (!empty($filters['app']) && $filters['app'] !== 'all') {
                $whereConditions[] = 'app_name = ?';
                $params[] = $filters['app'];
            }
            
            if (!empty($filters['rating'])) {
                $whereConditions[] = 'rating = ?';
                $params[] = $filters['rating'];
            }
            
            if (!empty($filters['date_from'])) {
                $whereConditions[] = 'review_date >= ?';
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $whereConditions[] = 'review_date <= ?';
                $params[] = $filters['date_to'];
            }
            
            $whereClause = implode(' AND ', $whereConditions);
            
            // Build ORDER BY clause
            $sortOrder = $filters['sort'] ?? 'newest';
            $orderBy = match($sortOrder) {
                'newest' => 'review_date DESC, created_at DESC, id DESC',
                'oldest' => 'review_date ASC, created_at ASC, id ASC',
                'rating_high' => 'rating DESC, review_date DESC, id DESC',
                'rating_low' => 'rating ASC, review_date DESC, id DESC',
                default => 'review_date DESC, created_at DESC, id DESC'
            };
            
            // Get total count
            $countStmt = $this->conn->prepare("SELECT COUNT(*) as total FROM review_repository WHERE $whereClause");
            $countStmt->execute($params);
            $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Get reviews
            $reviewsStmt = $this->conn->prepare("
                SELECT 
                    id, app_name, store_name, country_name, rating, review_content, review_date,
                    earned_by, is_featured, created_at, updated_at,
                    CASE 
                        WHEN review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 'recent'
                        WHEN review_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN 'current_month'
                        ELSE 'older'
                    END as time_category
                FROM review_repository 
                WHERE $whereClause
                ORDER BY $orderBy
                LIMIT ? OFFSET ?
            ");
            
            $reviewsStmt->execute([...$params, $limit, $offset]);
            $reviews = $reviewsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'reviews' => $reviews,
                'total_count' => $totalCount,
                'total_pages' => ceil($totalCount / $limit),
                'current_page' => $page,
                'items_per_page' => $limit
            ];
            
        } catch (Exception $e) {
            error_log("Error getting paginated reviews: " . $e->getMessage());
            return [
                'reviews' => [],
                'total_count' => 0,
                'total_pages' => 0,
                'current_page' => 1,
                'items_per_page' => $limit
            ];
        }
    }
    
    /**
     * Assign a review to someone (for Access Reviews functionality)
     */
    public function assignReview($reviewId, $earnedBy, $assignedBy = null) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE review_repository 
                SET earned_by = ?, assigned_at = NOW(), assigned_by = ?, updated_at = NOW()
                WHERE id = ? AND is_active = TRUE
            ");
            
            return $stmt->execute([$earnedBy, $assignedBy, $reviewId]);
        } catch (Exception $e) {
            error_log("Error assigning review: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get reviews for Access Reviews page (30-day filtering)
     */
    public function getAccessReviews($appName = null, $daysBack = 30) {
        try {
            $whereConditions = ['is_active = TRUE'];
            $params = [];
            
            if ($daysBack > 0) {
                $whereConditions[] = 'review_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)';
                $params[] = $daysBack;
            }
            
            if ($appName && $appName !== 'all') {
                $whereConditions[] = 'app_name = ?';
                $params[] = $appName;
            }
            
            $whereClause = implode(' AND ', $whereConditions);
            
            $stmt = $this->conn->prepare("
                SELECT 
                    id, app_name, store_name, country_name, rating, review_content, review_date,
                    earned_by, assigned_at, assigned_by, is_featured, created_at, updated_at,
                    CASE 
                        WHEN review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 'recent'
                        WHEN review_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN 'current_month'
                        ELSE 'older'
                    END as time_category
                FROM review_repository 
                WHERE $whereClause
                ORDER BY 
                    CASE WHEN earned_by IS NULL THEN 0 ELSE 1 END,
                    review_date DESC, 
                    created_at DESC
            ");
            
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error getting access reviews: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get statistics for an app or all apps
     */
    public function getStatistics($appName = null) {
        try {
            $whereCondition = 'is_active = TRUE';
            $params = [];
            
            if ($appName && $appName !== 'all') {
                $whereCondition .= ' AND app_name = ?';
                $params[] = $appName;
            }
            
            $stmt = $this->conn->prepare("
                SELECT 
                    " . ($appName ? "'$appName'" : 'app_name') . " as app_name,
                    COUNT(*) as total_reviews,
                    COUNT(CASE WHEN review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as last_30_days,
                    COUNT(CASE WHEN review_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN 1 END) as this_month,
                    AVG(rating) as average_rating,
                    COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
                    COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star,
                    COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star,
                    COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star,
                    COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star,
                    COUNT(CASE WHEN earned_by IS NOT NULL THEN 1 END) as assigned_reviews,
                    MAX(review_date) as latest_review_date,
                    MAX(updated_at) as last_updated
                FROM review_repository 
                WHERE $whereCondition
                " . ($appName ? '' : 'GROUP BY app_name') . "
            ");
            
            $stmt->execute($params);
            
            if ($appName) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
        } catch (Exception $e) {
            error_log("Error getting statistics: " . $e->getMessage());
            return $appName ? [] : [[]];
        }
    }
    
    /**
     * Archive reviews (soft delete - never actually delete)
     */
    public function archiveReviews($reviewIds) {
        try {
            if (empty($reviewIds)) return false;
            
            $placeholders = str_repeat('?,', count($reviewIds) - 1) . '?';
            $stmt = $this->conn->prepare("
                UPDATE review_repository 
                SET is_active = FALSE, updated_at = NOW()
                WHERE id IN ($placeholders)
            ");
            
            return $stmt->execute($reviewIds);
        } catch (Exception $e) {
            error_log("Error archiving reviews: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get available apps with review counts
     */
    public function getAvailableApps() {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    app_name,
                    COUNT(*) as total_reviews,
                    COUNT(CASE WHEN review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as recent_reviews,
                    AVG(rating) as average_rating,
                    MAX(review_date) as latest_review_date
                FROM review_repository 
                WHERE is_active = TRUE
                GROUP BY app_name
                ORDER BY app_name
            ");
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting available apps: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get reviews count for a specific time period
     */
    public function getReviewsCount($appName, $days = null, $fromDate = null) {
        try {
            $query = "SELECT COUNT(*) as count FROM review_repository WHERE app_name = ? AND is_active = TRUE";
            $params = [$appName];

            if ($days !== null) {
                $query .= " AND review_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)";
                $params[] = $days;
            } elseif ($fromDate !== null) {
                $query .= " AND review_date >= ?";
                $params[] = $fromDate;
            }

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error getting reviews count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get recent reviews for an app
     */
    public function getRecentReviews($appName, $limit = 10) {
        try {
            $stmt = $this->conn->prepare("
                SELECT * FROM review_repository
                WHERE app_name = ? AND is_active = TRUE
                ORDER BY review_date DESC, created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$appName, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting recent reviews: " . $e->getMessage());
            return [];
        }
    }
}
