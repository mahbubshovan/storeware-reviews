<?php
/**
 * Database Initialization Script
 * Ensures all required tables exist on live server
 * Run this after deploying to Railway to verify database setup
 */

require_once __DIR__ . '/../config/database.php';

echo "🚀 Initializing database for live server...\n";

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    echo "✅ Database connection successful\n";
    
    // Check and create required tables
    $tables = [
        'reviews' => "
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
                INDEX idx_rating (rating),
                INDEX idx_earned_by (earned_by),
                INDEX idx_is_active (is_active),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'access_reviews' => "
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
                INDEX idx_review_date (review_date),
                INDEX idx_earned_by (earned_by),
                INDEX idx_original_review_id (original_review_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'app_metadata' => "
            CREATE TABLE IF NOT EXISTS app_metadata (
                id INT AUTO_INCREMENT PRIMARY KEY,
                app_name VARCHAR(100) UNIQUE,
                total_reviews INT DEFAULT 0,
                five_star_total INT DEFAULT 0,
                four_star_total INT DEFAULT 0,
                three_star_total INT DEFAULT 0,
                two_star_total INT DEFAULT 0,
                one_star_total INT DEFAULT 0,
                overall_rating DECIMAL(2,1) DEFAULT 0.0,
                last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_app_name (app_name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        "
    ];
    
    foreach ($tables as $tableName => $createSQL) {
        try {
            $pdo->exec($createSQL);
            
            // Check if table has data
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $tableName");
            $count = $stmt->fetch()['count'];
            
            echo "✅ Table '$tableName' ready (contains $count records)\n";
            
        } catch (Exception $e) {
            echo "❌ Error creating table '$tableName': " . $e->getMessage() . "\n";
        }
    }
    
    // Insert sample apps if app_metadata is empty
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM app_metadata");
    $appCount = $stmt->fetch()['count'];
    
    if ($appCount == 0) {
        echo "📝 Inserting sample app metadata...\n";
        
        $sampleApps = [
            'StoreSEO', 'StoreFAQ', 'EasyFlow', 'TrustSync', 
            'Vidify', 'BetterDocs FAQ Knowledge Base'
        ];
        
        $insertStmt = $pdo->prepare("
            INSERT INTO app_metadata (app_name, total_reviews, overall_rating) 
            VALUES (?, 0, 0.0)
        ");
        
        foreach ($sampleApps as $app) {
            try {
                $insertStmt->execute([$app]);
                echo "  ✅ Added $app to app_metadata\n";
            } catch (Exception $e) {
                echo "  ⚠️  $app already exists or error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n🎉 Database initialization complete!\n";
    echo "📊 Summary:\n";
    
    // Show final counts
    foreach (array_keys($tables) as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $count = $stmt->fetch()['count'];
        echo "  - $table: $count records\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database initialization failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
