<?php
require_once __DIR__ . '/../config/cors.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/AccessReviewsSync.php';

/**
 * Smart Sync Analytics API
 * 
 * When analytics page scrapes data, this API:
 * 1. Gets today's new reviews from analytics scraping
 * 2. Compares with existing Access Review Tab data app-wise
 * 3. Skips duplicates if review already exists
 * 4. Adds new reviews to Access Review page as regular process
 */

class SmartSyncAnalytics {
    private $pdo;
    private $accessSync;
    
    public function __construct() {
        $database = new Database();
        $this->pdo = $database->getConnection();
        $this->accessSync = new AccessReviewsSync();
    }
    
    /**
     * Main smart sync function
     */
    public function performSmartSync($appName) {
        try {
            $today = date('Y-m-d');
            
            // Step 1: Get today's reviews from main reviews table (from analytics scraping)
            $todaysReviews = $this->getTodaysReviews($appName, $today);
            
            if (empty($todaysReviews)) {
                return [
                    'success' => true,
                    'message' => "No new reviews found for {$appName} today",
                    'stats' => [
                        'total_found' => 0,
                        'duplicates_skipped' => 0,
                        'new_added' => 0
                    ]
                ];
            }
            
            // Step 2: Compare with existing Access Review Tab data
            $stats = $this->compareAndSync($appName, $todaysReviews);
            
            // Step 3: Trigger regular access reviews sync to update the Access Review page
            // Capture output to prevent interference with JSON response
            ob_start();
            $this->accessSync->syncAccessReviews();
            ob_end_clean();
            
            return [
                'success' => true,
                'message' => "Smart sync completed for {$appName}",
                'stats' => $stats
            ];
            
        } catch (Exception $e) {
            error_log("Smart sync error for {$appName}: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get today's reviews from main reviews table
     */
    private function getTodaysReviews($appName, $today) {
        $stmt = $this->pdo->prepare("
            SELECT id, app_name, store_name, country_name, rating, review_content, review_date, created_at
            FROM reviews 
            WHERE app_name = ? AND review_date = ?
            ORDER BY created_at DESC
        ");
        
        $stmt->execute([$appName, $today]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Compare today's reviews with existing Access Review data and sync
     */
    private function compareAndSync($appName, $todaysReviews) {
        $totalFound = count($todaysReviews);
        $duplicatesSkipped = 0;
        $newAdded = 0;
        
        foreach ($todaysReviews as $review) {
            // Check if this review already exists in access_reviews table
            if ($this->reviewExistsInAccessReviews($review)) {
                $duplicatesSkipped++;
                continue;
            }
            
            // Check if it exists in access_reviews by content matching (in case of slight differences)
            if ($this->reviewExistsByContent($appName, $review)) {
                $duplicatesSkipped++;
                continue;
            }
            
            // This is a new review - it will be added by the regular sync process
            $newAdded++;
        }
        
        return [
            'total_found' => $totalFound,
            'duplicates_skipped' => $duplicatesSkipped,
            'new_added' => $newAdded,
            'app_name' => $appName
        ];
    }
    
    /**
     * Check if review exists in access_reviews table by exact match
     */
    private function reviewExistsInAccessReviews($review) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM access_reviews 
            WHERE original_review_id = ?
        ");
        
        $stmt->execute([$review['id']]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Check if review exists by content matching (for duplicate detection)
     */
    private function reviewExistsByContent($appName, $review) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM access_reviews 
            WHERE app_name = ? 
            AND review_content = ? 
            AND review_date = ?
            AND rating = ?
        ");
        
        $stmt->execute([
            $appName,
            $review['review_content'],
            $review['review_date'],
            $review['rating']
        ]);
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Get sync statistics for all apps
     */
    public function getAllAppsSyncStats() {
        $today = date('Y-m-d');
        
        $stmt = $this->pdo->prepare("
            SELECT 
                app_name,
                COUNT(*) as todays_reviews,
                COUNT(CASE WHEN created_at >= ? THEN 1 END) as newly_scraped
            FROM reviews 
            WHERE review_date = ?
            GROUP BY app_name
            ORDER BY todays_reviews DESC
        ");
        
        $todayStart = date('Y-m-d 00:00:00');
        $stmt->execute([$todayStart, $today]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Handle the request
try {
    $smartSync = new SmartSyncAnalytics();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $appName = $input['app_name'] ?? null;
        
        if (!$appName) {
            throw new Exception('App name is required');
        }
        
        $result = $smartSync->performSmartSync($appName);
        
    } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Get sync stats for all apps
        $stats = $smartSync->getAllAppsSyncStats();
        
        $result = [
            'success' => true,
            'stats' => $stats,
            'message' => 'Sync statistics retrieved'
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    error_log("Smart sync API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
