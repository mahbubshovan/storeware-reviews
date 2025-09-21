<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Database Manager for Reviews
 */
class DatabaseManager {
    private $conn;
    private $table_name = "review_repository";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Insert a new review - supports both array and individual parameters
     */
    public function insertReview($app_name_or_array, $store_name = null, $country_name = null, $rating = null, $review_content = null, $review_date = null) {
        // Handle array input
        if (is_array($app_name_or_array)) {
            $review = $app_name_or_array;
            $app_name = $review['app_name'];
            $store_name = $review['store_name'];
            $country_name = $review['country_name'];
            $rating = $review['rating'];
            $review_content = $review['review_content'];
            $review_date = $review['review_date'];
        } else {
            // Handle individual parameters
            $app_name = $app_name_or_array;
        }

        $query = "INSERT INTO " . $this->table_name . "
                  (app_name, store_name, country_name, rating, review_content, review_date)
                  VALUES (:app_name, :store_name, :country_name, :rating, :review_content, :review_date)";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $app_name = htmlspecialchars(strip_tags($app_name));
        $store_name = htmlspecialchars(strip_tags($store_name));
        $country_name = htmlspecialchars(strip_tags($country_name));
        $rating = intval($rating);
        $review_content = htmlspecialchars(strip_tags($review_content));

        // Bind values
        $stmt->bindParam(":app_name", $app_name);
        $stmt->bindParam(":store_name", $store_name);
        $stmt->bindParam(":country_name", $country_name);
        $stmt->bindParam(":rating", $rating);
        $stmt->bindParam(":review_content", $review_content);
        $stmt->bindParam(":review_date", $review_date);

        try {
            if($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            // Handle duplicate key error gracefully
            if ($e->getCode() == 23000) { // Integrity constraint violation
                // This is a duplicate, which is expected behavior
                return false; // Don't treat as error, just skip
            }
            // Re-throw other errors
            throw $e;
        }
    }

    /**
     * Check if review already exists (prevent duplicates)
     */
    public function reviewExists($app_name, $store_name, $review_content, $review_date) {
        $query = "SELECT id FROM " . $this->table_name . "
                  WHERE app_name = :app_name
                  AND store_name = :store_name
                  AND review_content = :review_content
                  AND review_date = :review_date
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":app_name", $app_name);
        $stmt->bindParam(":store_name", $store_name);
        $stmt->bindParam(":review_content", $review_content);
        $stmt->bindParam(":review_date", $review_date);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Get reviews count for current month (from 1st of current month to today)
     */
    public function getThisMonthReviews($app_name = null) {
        // Get first day of current month
        $firstOfMonth = date('Y-m-01');

        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . "
                  WHERE review_date >= :first_of_month
                  AND review_date <= CURDATE()
                  AND is_active = TRUE";

        if ($app_name) {
            $query .= " AND app_name = :app_name";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":first_of_month", $firstOfMonth);
        if ($app_name) {
            $stmt->bindParam(":app_name", $app_name);
        }
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['count'];
    }

    /**
     * Get reviews count for last 30 days
     */
    public function getLast30DaysReviews($app_name = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . "
                  WHERE review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                  AND is_active = TRUE";

        if ($app_name) {
            $query .= " AND app_name = :app_name";
        }

        $stmt = $this->conn->prepare($query);
        if ($app_name) {
            $stmt->bindParam(":app_name", $app_name);
        }
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['count'];
    }

    /**
     * Get reviews count for last month (before current month)
     * Based on today being August 9th, "last month" means July 9th and earlier
     */
    public function getLastMonthReviews($app_name = null) {
        // Calculate the cutoff date (30 days ago from today)
        $cutoffDate = date('Y-m-d', strtotime('-30 days'));

        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . "
                  WHERE review_date <= :cutoff_date";

        if ($app_name) {
            $query .= " AND app_name = :app_name";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cutoff_date", $cutoffDate);
        if ($app_name) {
            $stmt->bindParam(":app_name", $app_name);
        }
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['count'];
    }

    /**
     * Get average rating - PRIORITIZE ACTUAL SCRAPED REVIEWS
     */
    public function getAverageRating($app_name = null) {
        // 🔴 ALWAYS USE ACTUAL SCRAPED REVIEW DATA, NOT METADATA
        // This ensures average rating reflects the real scraped reviews

        $query = "SELECT AVG(rating) as avg_rating FROM " . $this->table_name . " WHERE is_active = TRUE";

        if ($app_name) {
            $query .= " AND app_name = :app_name";
        }

        $stmt = $this->conn->prepare($query);
        if ($app_name) {
            $stmt->bindParam(":app_name", $app_name);
        }
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['avg_rating'] ? round($result['avg_rating'], 1) : 0.0;
    }

    /**
     * Get review distribution by rating - USE COMPLETE DISTRIBUTION DATA
     */
    public function getReviewDistribution($app_name = null) {
        if ($app_name) {
            // 🎯 FIRST: Try to get COMPLETE rating distribution from metadata
            // This contains the full rating breakdown from ALL reviews on Shopify
            $metaQuery = "SELECT
                            total_reviews,
                            five_star_total as five_star,
                            four_star_total as four_star,
                            three_star_total as three_star,
                            two_star_total as two_star,
                            one_star_total as one_star
                          FROM app_metadata
                          WHERE app_name = :app_name
                          AND total_reviews > 0";

            $stmt = $this->conn->prepare($metaQuery);
            $stmt->bindParam(":app_name", $app_name);
            $stmt->execute();
            $metaResult = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($metaResult && $metaResult['total_reviews'] > 0) {
                // Using complete rating distribution from metadata
                return $metaResult;
            }
        }

        // 🔄 FALLBACK: Use actual scraped review data (recent reviews only)
        // Using recent scraped reviews only (limited data)

        $query = "SELECT
                    COUNT(*) as total_reviews,
                    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                  FROM " . $this->table_name;

        if ($app_name) {
            $query .= " WHERE app_name = :app_name";
        }

        $stmt = $this->conn->prepare($query);
        if ($app_name) {
            $stmt->bindParam(":app_name", $app_name);
        }
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Ensure all values are integers and not null
        $result['total_reviews'] = intval($result['total_reviews'] ?? 0);
        $result['five_star'] = intval($result['five_star'] ?? 0);
        $result['four_star'] = intval($result['four_star'] ?? 0);
        $result['three_star'] = intval($result['three_star'] ?? 0);
        $result['two_star'] = intval($result['two_star'] ?? 0);
        $result['one_star'] = intval($result['one_star'] ?? 0);

        return $result;
    }

    /**
     * Get latest 10 reviews
     */
    public function getLatestReviews($limit = 10, $app_name = null) {
        $query = "SELECT app_name, store_name, country_name, rating, review_content, review_date
                  FROM " . $this->table_name;

        if ($app_name) {
            $query .= " WHERE app_name = :app_name";
        }

        $query .= " ORDER BY review_date DESC, created_at DESC LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        if ($app_name) {
            $stmt->bindParam(":app_name", $app_name);
        }
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get database connection for debugging
     */
    public function getConnection() {
        return $this->conn;
    }
}
?>
