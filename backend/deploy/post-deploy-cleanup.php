<?php
/**
 * Post-Deployment Cleanup Script
 * Automatically runs after deployment to ensure database consistency
 * Removes unwanted apps and ensures only the 6 specified apps exist
 */

require_once __DIR__ . '/../config/database.php';

echo "🚀 POST-DEPLOYMENT CLEANUP STARTING...\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
echo str_repeat('=', 60) . "\n";

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    // Define the ONLY allowed apps
    $allowedApps = [
        'StoreSEO',
        'StoreFAQ', 
        'EasyFlow',
        'BetterDocs FAQ Knowledge Base',
        'Vidify',
        'TrustSync'
    ];
    
    echo "✅ Database connection successful\n";
    echo "📋 Allowed apps: " . implode(', ', $allowedApps) . "\n\n";
    
    // Step 1: Identify unwanted apps
    echo "🔍 STEP 1: Identifying unwanted apps...\n";
    $stmt = $pdo->query("
        SELECT DISTINCT app_name, COUNT(*) as count 
        FROM reviews 
        WHERE app_name IS NOT NULL 
        GROUP BY app_name 
        ORDER BY app_name
    ");
    $currentApps = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $appsToRemove = [];
    $validApps = [];
    
    foreach ($currentApps as $app) {
        if (in_array($app['app_name'], $allowedApps)) {
            $validApps[] = $app;
            echo "  ✅ {$app['app_name']}: {$app['count']} reviews (KEEPING)\n";
        } else {
            $appsToRemove[] = $app['app_name'];
            echo "  ❌ {$app['app_name']}: {$app['count']} reviews (REMOVING)\n";
        }
    }
    
    if (empty($appsToRemove)) {
        echo "  🎉 No unwanted apps found!\n";
    } else {
        echo "  🗑️  Will remove: " . implode(', ', $appsToRemove) . "\n";
    }
    
    // Step 2: Remove unwanted apps
    if (!empty($appsToRemove)) {
        echo "\n🧹 STEP 2: Removing unwanted apps...\n";
        
        $placeholders = str_repeat('?,', count($appsToRemove) - 1) . '?';
        
        // Remove from reviews table
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE app_name IN ($placeholders)");
        $stmt->execute($appsToRemove);
        $reviewsDeleted = $stmt->rowCount();
        echo "  ✅ Deleted $reviewsDeleted reviews\n";
        
        // Remove from access_reviews table (if exists)
        try {
            $stmt = $pdo->prepare("DELETE FROM access_reviews WHERE app_name IN ($placeholders)");
            $stmt->execute($appsToRemove);
            $accessDeleted = $stmt->rowCount();
            echo "  ✅ Deleted $accessDeleted access_reviews\n";
        } catch (Exception $e) {
            echo "  ℹ️  access_reviews table not found (OK)\n";
        }
        
        // Remove from app_metadata table (if exists)
        try {
            $stmt = $pdo->prepare("DELETE FROM app_metadata WHERE app_name IN ($placeholders)");
            $stmt->execute($appsToRemove);
            $metaDeleted = $stmt->rowCount();
            echo "  ✅ Deleted $metaDeleted app_metadata entries\n";
        } catch (Exception $e) {
            echo "  ℹ️  app_metadata table not found (OK)\n";
        }
        
        // Clean up snapshot-related tables
        $snapshotTables = ['snapshots', 'scrape_schedule', 'snapshot_pointer'];
        foreach ($snapshotTables as $table) {
            try {
                // Convert app names to slugs
                $slugsToRemove = [];
                foreach ($appsToRemove as $appName) {
                    $slug = strtolower(str_replace([' ', 'FAQ'], ['-', 'faq'], $appName));
                    if ($appName === 'Vitals') $slug = 'vitals';
                    $slugsToRemove[] = $slug;
                }
                
                if (!empty($slugsToRemove)) {
                    $slugPlaceholders = str_repeat('?,', count($slugsToRemove) - 1) . '?';
                    $column = ($table === 'snapshots') ? 'app_slug' : 'app_slug';
                    $stmt = $pdo->prepare("DELETE FROM $table WHERE $column IN ($slugPlaceholders)");
                    $stmt->execute($slugsToRemove);
                    $deleted = $stmt->rowCount();
                    if ($deleted > 0) {
                        echo "  ✅ Deleted $deleted entries from $table\n";
                    }
                }
            } catch (Exception $e) {
                echo "  ℹ️  Table $table not found (OK)\n";
            }
        }
    }
    
    // Step 3: Ensure all allowed apps exist in app_metadata
    echo "\n📝 STEP 3: Ensuring app metadata exists...\n";
    try {
        // Create app_metadata table if it doesn't exist
        $pdo->exec("
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
        ");
        
        $insertStmt = $pdo->prepare("
            INSERT IGNORE INTO app_metadata (app_name, total_reviews, overall_rating) 
            VALUES (?, 0, 0.0)
        ");
        
        foreach ($allowedApps as $app) {
            $insertStmt->execute([$app]);
            echo "  ✅ Ensured $app exists in app_metadata\n";
        }
    } catch (Exception $e) {
        echo "  ⚠️  Could not create/update app_metadata: " . $e->getMessage() . "\n";
    }
    
    // Step 4: Final verification
    echo "\n📊 STEP 4: Final verification...\n";
    $stmt = $pdo->query("
        SELECT DISTINCT app_name, COUNT(*) as count 
        FROM reviews 
        WHERE app_name IS NOT NULL 
        GROUP BY app_name 
        ORDER BY app_name
    ");
    $finalApps = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $allValid = true;
    foreach ($finalApps as $app) {
        if (in_array($app['app_name'], $allowedApps)) {
            echo "  ✅ {$app['app_name']}: {$app['count']} reviews\n";
        } else {
            echo "  ❌ {$app['app_name']}: {$app['count']} reviews (UNEXPECTED!)\n";
            $allValid = false;
        }
    }
    
    echo "\n" . str_repeat('=', 60) . "\n";
    if ($allValid && count($finalApps) <= 6) {
        echo "🎉 POST-DEPLOYMENT CLEANUP SUCCESSFUL!\n";
        echo "✅ Database contains only the 6 specified apps\n";
        echo "✅ All unwanted apps have been removed\n";
    } else {
        echo "⚠️  POST-DEPLOYMENT CLEANUP COMPLETED WITH WARNINGS\n";
        echo "⚠️  Some unexpected apps may still exist\n";
    }
    
    echo "📊 Final app count: " . count($finalApps) . " apps\n";
    echo "⏱️  Cleanup completed at: " . date('Y-m-d H:i:s') . "\n";
    
} catch (Exception $e) {
    echo "❌ POST-DEPLOYMENT CLEANUP FAILED: " . $e->getMessage() . "\n";
    exit(1);
}
?>
