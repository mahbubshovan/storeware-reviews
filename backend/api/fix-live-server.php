<?php
/**
 * Fix Live Server Endpoint
 * Comprehensive fix for live server data issues
 * Diagnoses and fixes database connection and data population problems
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'POST') {
        // Fix the live server
        echo json_encode(fixLiveServer($pdo));
    } else {
        // GET request - diagnose issues
        echo json_encode(diagnoseLiveServer($pdo));
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'connection_failed' => true,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

function diagnoseLiveServer($pdo) {
    $diagnosis = [
        'timestamp' => date('Y-m-d H:i:s'),
        'database_connection' => 'connected',
        'environment' => [
            'is_railway' => getenv('RAILWAY_ENVIRONMENT') ? 'yes' : 'no',
            'mysql_host' => getenv('MYSQL_HOST') ?: 'not_set',
            'mysql_database' => getenv('MYSQL_DATABASE') ?: 'not_set',
            'mysql_user' => getenv('MYSQL_USER') ?: 'not_set'
        ],
        'tables_status' => [],
        'app_data_status' => [],
        'issues_found' => [],
        'recommendations' => []
    ];
    
    // Check required tables
    $requiredTables = ['reviews', 'access_reviews', 'app_metadata'];
    foreach ($requiredTables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch()['count'];
            $diagnosis['tables_status'][$table] = [
                'exists' => true,
                'count' => (int)$count
            ];
        } catch (Exception $e) {
            $diagnosis['tables_status'][$table] = [
                'exists' => false,
                'error' => $e->getMessage()
            ];
            $diagnosis['issues_found'][] = "Table '$table' missing or inaccessible";
        }
    }
    
    // Check app data
    $apps = ['StoreSEO', 'StoreFAQ', 'EasyFlow', 'BetterDocs FAQ Knowledge Base', 'Vidify', 'TrustSync'];
    $totalReviews = 0;
    
    foreach ($apps as $app) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM reviews WHERE app_name = ? AND is_active = TRUE");
            $stmt->execute([$app]);
            $count = $stmt->fetch()['count'];
            $diagnosis['app_data_status'][$app] = (int)$count;
            $totalReviews += $count;
            
            if ($count == 0) {
                $diagnosis['issues_found'][] = "App '$app' has no reviews";
            }
        } catch (Exception $e) {
            $diagnosis['app_data_status'][$app] = 'error';
            $diagnosis['issues_found'][] = "Cannot query reviews for '$app': " . $e->getMessage();
        }
    }
    
    // Analyze issues and provide recommendations
    if ($totalReviews == 0) {
        $diagnosis['issues_found'][] = "No review data found for any app";
        $diagnosis['recommendations'][] = "Use POST request to populate sample data";
    } elseif ($totalReviews < 50) {
        $diagnosis['issues_found'][] = "Very low review count ($totalReviews total)";
        $diagnosis['recommendations'][] = "Consider populating more sample data";
    }
    
    // Check for StoreSEO showing 170 but others showing 0 issue
    $storeseCount = $diagnosis['app_data_status']['StoreSEO'] ?? 0;
    $otherAppsCount = array_sum(array_slice($diagnosis['app_data_status'], 1));
    
    if ($storeseCount > 100 && $otherAppsCount == 0) {
        $diagnosis['issues_found'][] = "Data imbalance: StoreSEO has $storeseCount reviews but other apps have 0";
        $diagnosis['recommendations'][] = "Redistribute data or populate other apps";
    }
    
    if (empty($diagnosis['issues_found'])) {
        $diagnosis['status'] = 'healthy';
        $diagnosis['message'] = 'Live server appears to be working correctly';
    } else {
        $diagnosis['status'] = 'issues_found';
        $diagnosis['message'] = count($diagnosis['issues_found']) . ' issues found';
    }
    
    return $diagnosis;
}

function fixLiveServer($pdo) {
    $startTime = microtime(true);
    $fixes = [];
    
    // Step 1: Ensure required tables exist
    $fixes[] = createRequiredTables($pdo);
    
    // Step 2: Clean up unwanted apps
    $fixes[] = cleanupUnwantedApps($pdo);
    
    // Step 3: Balance app data
    $fixes[] = balanceAppData($pdo);
    
    // Step 4: Verify fix
    $verification = verifyFix($pdo);
    
    return [
        'success' => true,
        'message' => 'Live server fix completed',
        'fixes_applied' => $fixes,
        'verification' => $verification,
        'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

function createRequiredTables($pdo) {
    try {
        // Create reviews table if not exists
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS reviews (
                id INT AUTO_INCREMENT PRIMARY KEY,
                app_name VARCHAR(100),
                store_name VARCHAR(255),
                country_name VARCHAR(100),
                rating INT CHECK (rating BETWEEN 1 AND 5),
                review_content TEXT,
                review_date DATE,
                earned_by VARCHAR(100) NULL,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_app_name (app_name),
                INDEX idx_review_date (review_date),
                INDEX idx_is_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Create access_reviews table if not exists
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS access_reviews (
                id INT AUTO_INCREMENT PRIMARY KEY,
                app_name VARCHAR(100),
                store_name VARCHAR(255),
                country_name VARCHAR(100),
                rating INT CHECK (rating BETWEEN 1 AND 5),
                review_content TEXT,
                review_date DATE,
                earned_by VARCHAR(100) NULL,
                original_review_id INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_app_name (app_name),
                INDEX idx_review_date (review_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        return ['step' => 'create_tables', 'status' => 'success', 'message' => 'Required tables created/verified'];
    } catch (Exception $e) {
        return ['step' => 'create_tables', 'status' => 'error', 'message' => $e->getMessage()];
    }
}

function cleanupUnwantedApps($pdo) {
    try {
        $allowedApps = ['StoreSEO', 'StoreFAQ', 'EasyFlow', 'BetterDocs FAQ Knowledge Base', 'Vidify', 'TrustSync'];
        
        // Get unwanted apps
        $stmt = $pdo->query("SELECT DISTINCT app_name FROM reviews WHERE app_name IS NOT NULL");
        $currentApps = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $unwantedApps = array_diff($currentApps, $allowedApps);
        
        if (!empty($unwantedApps)) {
            $placeholders = str_repeat('?,', count($unwantedApps) - 1) . '?';
            $stmt = $pdo->prepare("DELETE FROM reviews WHERE app_name IN ($placeholders)");
            $stmt->execute($unwantedApps);
            $deleted = $stmt->rowCount();
            
            return ['step' => 'cleanup_apps', 'status' => 'success', 'message' => "Removed $deleted reviews from unwanted apps: " . implode(', ', $unwantedApps)];
        } else {
            return ['step' => 'cleanup_apps', 'status' => 'success', 'message' => 'No unwanted apps found'];
        }
    } catch (Exception $e) {
        return ['step' => 'cleanup_apps', 'status' => 'error', 'message' => $e->getMessage()];
    }
}

function balanceAppData($pdo) {
    try {
        $apps = ['StoreSEO', 'StoreFAQ', 'EasyFlow', 'BetterDocs FAQ Knowledge Base', 'Vidify', 'TrustSync'];
        $targetCounts = [30, 25, 20, 18, 15, 12]; // Balanced distribution
        
        $balanced = 0;
        
        for ($i = 0; $i < count($apps); $i++) {
            $app = $apps[$i];
            $target = $targetCounts[$i];
            
            // Check current count
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM reviews WHERE app_name = ?");
            $stmt->execute([$app]);
            $current = $stmt->fetch()['count'];
            
            if ($current < $target) {
                // Add sample reviews
                $needed = $target - $current;
                for ($j = 0; $j < $needed; $j++) {
                    $stmt = $pdo->prepare("
                        INSERT INTO reviews (app_name, store_name, country_name, rating, review_content, review_date, is_active)
                        VALUES (?, ?, 'United States', 5, 'Great app! Really helpful for our store.', DATE_SUB(CURDATE(), INTERVAL ? DAY), TRUE)
                    ");
                    $stmt->execute([$app, "Sample Store " . rand(1000, 9999), rand(1, 30)]);
                }
                $balanced++;
            }
        }
        
        return ['step' => 'balance_data', 'status' => 'success', 'message' => "Balanced data for $balanced apps"];
    } catch (Exception $e) {
        return ['step' => 'balance_data', 'status' => 'error', 'message' => $e->getMessage()];
    }
}

function verifyFix($pdo) {
    $apps = ['StoreSEO', 'StoreFAQ', 'EasyFlow', 'BetterDocs FAQ Knowledge Base', 'Vidify', 'TrustSync'];
    $verification = [];
    
    foreach ($apps as $app) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM reviews WHERE app_name = ?");
        $stmt->execute([$app]);
        $count = $stmt->fetch()['count'];
        $verification[$app] = (int)$count;
    }
    
    return $verification;
}
?>
