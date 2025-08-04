<?php
require_once __DIR__ . '/utils/DatabaseManager.php';

/**
 * Correct Vidify fix based on your feedback:
 * - 8 total reviews from Shopify (real)
 * - 5.0 rating from Shopify (real)  
 * - NO recent reviews (0 this month, 0 last 30 days) as you mentioned no reviews in 1.5 years
 */
class VidifyCorrectFix {
    private $dbManager;
    
    public function __construct() {
        $this->dbManager = new DatabaseManager();
    }
    
    public function fixVidifyCorrectly() {
        echo "=== VIDIFY CORRECT FIX ===\n";
        echo "Based on your feedback:\n";
        echo "- Real Shopify data: 8 total reviews, 5.0 rating\n";
        echo "- NO recent reviews (no reviews in last 1.5 years)\n";
        echo "- This month: 0 reviews\n";
        echo "- Last 30 days: 0 reviews\n\n";
        
        // Step 1: Clear ALL existing Vidify data
        $this->clearAllVidifyData();
        
        // Step 2: Set correct metadata with REAL Shopify data
        $this->setCorrectMetadata();
        
        // Step 3: Do NOT generate any recent reviews (since none in 1.5 years)
        echo "Not generating any recent reviews (as per your feedback - no reviews in 1.5 years)\n\n";
        
        // Step 4: Verify the fix
        $this->verifyFix();
        
        return [
            'total_reviews' => 8,
            'avg_rating' => 5.0,
            'this_month' => 0,
            'last_30_days' => 0
        ];
    }
    
    private function clearAllVidifyData() {
        echo "--- Clearing ALL Vidify data ---\n";
        
        try {
            $reflection = new ReflectionClass($this->dbManager);
            $connProperty = $reflection->getProperty('conn');
            $connProperty->setAccessible(true);
            $conn = $connProperty->getValue($this->dbManager);
            
            // Clear reviews table
            $query = "DELETE FROM reviews WHERE app_name = 'Vidify'";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $deletedReviews = $stmt->rowCount();
            echo "Cleared $deletedReviews reviews from reviews table\n";
            
            // Clear metadata table
            $query = "DELETE FROM app_metadata WHERE app_name = 'Vidify'";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $deletedMetadata = $stmt->rowCount();
            echo "Cleared $deletedMetadata entries from metadata table\n";
            
        } catch (Exception $e) {
            echo "Error clearing data: " . $e->getMessage() . "\n";
        }
    }
    
    private function setCorrectMetadata() {
        echo "--- Setting correct metadata ---\n";
        echo "Total reviews: 8 (REAL from Shopify)\n";
        echo "Average rating: 5.0 (REAL from Shopify)\n";
        echo "Rating distribution: All 8 reviews are 5-star (perfect 5.0 rating)\n";
        
        try {
            $reflection = new ReflectionClass($this->dbManager);
            $connProperty = $reflection->getProperty('conn');
            $connProperty->setAccessible(true);
            $conn = $connProperty->getValue($this->dbManager);
            
            // Real data from Shopify
            $appName = 'Vidify';
            $totalReviews = 8;
            $avgRating = 5.0;
            
            // For 5.0 rating, all 8 reviews must be 5-star
            $fiveStar = 8;
            $fourStar = 0;
            $threeStar = 0;
            $twoStar = 0;
            $oneStar = 0;
            
            $query = "INSERT INTO app_metadata 
                      (app_name, total_reviews, five_star_total, four_star_total, three_star_total, two_star_total, one_star_total, overall_rating) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($query);
            $stmt->execute([
                $appName,
                $totalReviews,
                $fiveStar,
                $fourStar,
                $threeStar,
                $twoStar,
                $oneStar,
                $avgRating
            ]);
            
            echo "✅ Metadata updated successfully\n";
            
        } catch (Exception $e) {
            echo "❌ Error updating metadata: " . $e->getMessage() . "\n";
        }
    }
    
    private function verifyFix() {
        echo "--- Verification ---\n";
        
        try {
            $reflection = new ReflectionClass($this->dbManager);
            $connProperty = $reflection->getProperty('conn');
            $connProperty->setAccessible(true);
            $conn = $connProperty->getValue($this->dbManager);
            
            // Check reviews table
            $query = "SELECT COUNT(*) as total, 
                             COUNT(CASE WHEN YEAR(review_date) = 2025 AND MONTH(review_date) = 7 THEN 1 END) as this_month,
                             COUNT(CASE WHEN review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as last_30_days
                      FROM reviews WHERE app_name = 'Vidify'";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $reviewData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "Database reviews:\n";
            echo "- Total: {$reviewData['total']}\n";
            echo "- This month: {$reviewData['this_month']}\n";
            echo "- Last 30 days: {$reviewData['last_30_days']}\n";
            
            // Check metadata table
            $query = "SELECT total_reviews, overall_rating FROM app_metadata WHERE app_name = 'Vidify'";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $metaData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "\nDatabase metadata:\n";
            echo "- Total reviews: {$metaData['total_reviews']}\n";
            echo "- Average rating: {$metaData['overall_rating']}\n";
            
            // Check API responses
            echo "\nAPI responses:\n";
            $apiThisMonth = json_decode(file_get_contents("http://localhost:8000/api/this-month-reviews.php?app_name=Vidify"), true);
            $apiLast30Days = json_decode(file_get_contents("http://localhost:8000/api/last-30-days-reviews.php?app_name=Vidify"), true);
            $apiRating = json_decode(file_get_contents("http://localhost:8000/api/average-rating.php?app_name=Vidify"), true);
            $apiDistribution = json_decode(file_get_contents("http://localhost:8000/api/review-distribution.php?app_name=Vidify"), true);
            
            echo "- This month: {$apiThisMonth['count']}\n";
            echo "- Last 30 days: {$apiLast30Days['count']}\n";
            echo "- Average rating: {$apiRating['average_rating']}\n";
            echo "- Total reviews: {$apiDistribution['total_reviews']}\n";
            
            // Verify correctness
            echo "\n=== FINAL VERIFICATION ===\n";
            $correct = true;
            
            if ($reviewData['total'] != 0) {
                echo "❌ Reviews table should have 0 reviews (has {$reviewData['total']})\n";
                $correct = false;
            } else {
                echo "✅ Reviews table: 0 reviews (correct)\n";
            }
            
            if ($reviewData['this_month'] != 0) {
                echo "❌ This month should be 0 (has {$reviewData['this_month']})\n";
                $correct = false;
            } else {
                echo "✅ This month: 0 (correct)\n";
            }
            
            if ($reviewData['last_30_days'] != 0) {
                echo "❌ Last 30 days should be 0 (has {$reviewData['last_30_days']})\n";
                $correct = false;
            } else {
                echo "✅ Last 30 days: 0 (correct)\n";
            }
            
            if ($metaData['total_reviews'] != 8) {
                echo "❌ Total reviews should be 8 (has {$metaData['total_reviews']})\n";
                $correct = false;
            } else {
                echo "✅ Total reviews: 8 (correct - from Shopify)\n";
            }
            
            if ($metaData['overall_rating'] != 5.0) {
                echo "❌ Average rating should be 5.0 (has {$metaData['overall_rating']})\n";
                $correct = false;
            } else {
                echo "✅ Average rating: 5.0 (correct - from Shopify)\n";
            }
            
            if ($apiThisMonth['count'] != 0) {
                echo "❌ API this month should be 0 (has {$apiThisMonth['count']})\n";
                $correct = false;
            } else {
                echo "✅ API this month: 0 (correct)\n";
            }
            
            if ($apiLast30Days['count'] != 0) {
                echo "❌ API last 30 days should be 0 (has {$apiLast30Days['count']})\n";
                $correct = false;
            } else {
                echo "✅ API last 30 days: 0 (correct)\n";
            }
            
            if ($apiRating['average_rating'] != 5.0) {
                echo "❌ API rating should be 5.0 (has {$apiRating['average_rating']})\n";
                $correct = false;
            } else {
                echo "✅ API rating: 5.0 (correct)\n";
            }
            
            if ($apiDistribution['total_reviews'] != 8) {
                echo "❌ API total should be 8 (has {$apiDistribution['total_reviews']})\n";
                $correct = false;
            } else {
                echo "✅ API total: 8 (correct)\n";
            }
            
            if ($correct) {
                echo "\n🎉 ALL CORRECT! Vidify is now properly fixed.\n";
            } else {
                echo "\n❌ Some issues remain. Please check the errors above.\n";
            }
            
        } catch (Exception $e) {
            echo "Error during verification: " . $e->getMessage() . "\n";
        }
    }
}

// Run the correct fix
$fixer = new VidifyCorrectFix();
$results = $fixer->fixVidifyCorrectly();
?>
