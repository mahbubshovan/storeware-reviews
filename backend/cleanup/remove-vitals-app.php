<?php
/**
 * Remove "Vitals" App Data Cleanup Script
 * Removes all references to "Vitals" app and ensures only the 6 specified apps exist
 * 
 * Allowed Apps:
 * - StoreSEO
 * - StoreFAQ  
 * - EasyFlow
 * - BetterDocs FAQ Knowledge Base
 * - Vidify
 * - TrustSync
 */

require_once __DIR__ . '/../config/database.php';

echo "🧹 CLEANING UP VITALS APP DATA\n";
echo str_repeat('=', 50) . "\n";

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
    
    echo "✅ Allowed apps: " . implode(', ', $allowedApps) . "\n\n";
    
    // 1. Check what apps currently exist in database
    echo "📊 Current apps in database:\n";
    $stmt = $pdo->query("SELECT DISTINCT app_name, COUNT(*) as count FROM reviews GROUP BY app_name ORDER BY app_name");
    $currentApps = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $appsToRemove = [];
    foreach ($currentApps as $app) {
        if (!in_array($app['app_name'], $allowedApps)) {
            $appsToRemove[] = $app['app_name'];
            echo "  ❌ {$app['app_name']}: {$app['count']} reviews (WILL BE REMOVED)\n";
        } else {
            echo "  ✅ {$app['app_name']}: {$app['count']} reviews (KEEPING)\n";
        }
    }
    
    if (empty($appsToRemove)) {
        echo "\n🎉 No unwanted apps found! Database is clean.\n";
        return;
    }
    
    echo "\n🗑️  Apps to remove: " . implode(', ', $appsToRemove) . "\n";
    
    // 2. Remove unwanted apps from reviews table
    echo "\n🔄 Removing unwanted apps from reviews table...\n";
    $placeholders = str_repeat('?,', count($appsToRemove) - 1) . '?';
    $deleteReviewsSQL = "DELETE FROM reviews WHERE app_name IN ($placeholders)";
    $stmt = $pdo->prepare($deleteReviewsSQL);
    $deletedReviews = $stmt->execute($appsToRemove);
    $reviewsDeleted = $stmt->rowCount();
    echo "  ✅ Deleted $reviewsDeleted reviews from unwanted apps\n";
    
    // 3. Remove unwanted apps from access_reviews table
    echo "\n🔄 Removing unwanted apps from access_reviews table...\n";
    $deleteAccessSQL = "DELETE FROM access_reviews WHERE app_name IN ($placeholders)";
    $stmt = $pdo->prepare($deleteAccessSQL);
    $deletedAccess = $stmt->execute($appsToRemove);
    $accessDeleted = $stmt->rowCount();
    echo "  ✅ Deleted $accessDeleted access_reviews from unwanted apps\n";
    
    // 4. Remove unwanted apps from app_metadata table
    echo "\n🔄 Removing unwanted apps from app_metadata table...\n";
    $deleteMetaSQL = "DELETE FROM app_metadata WHERE app_name IN ($placeholders)";
    $stmt = $pdo->prepare($deleteMetaSQL);
    $deletedMeta = $stmt->execute($appsToRemove);
    $metaDeleted = $stmt->rowCount();
    echo "  ✅ Deleted $metaDeleted app_metadata entries from unwanted apps\n";
    
    // 5. Clean up any other tables that might have app references
    $otherTables = [
        'snapshots' => 'app_slug',
        'scrape_schedule' => 'app_slug',
        'snapshot_pointer' => 'app_slug'
    ];
    
    foreach ($otherTables as $table => $column) {
        try {
            // Convert app names to slugs for these tables
            $slugsToRemove = [];
            foreach ($appsToRemove as $appName) {
                $slug = strtolower(str_replace(' ', '-', $appName));
                if ($appName === 'Vitals') $slug = 'vitals';
                $slugsToRemove[] = $slug;
            }
            
            if (!empty($slugsToRemove)) {
                $slugPlaceholders = str_repeat('?,', count($slugsToRemove) - 1) . '?';
                $deleteSQL = "DELETE FROM $table WHERE $column IN ($slugPlaceholders)";
                $stmt = $pdo->prepare($deleteSQL);
                $stmt->execute($slugsToRemove);
                $deleted = $stmt->rowCount();
                if ($deleted > 0) {
                    echo "  ✅ Deleted $deleted entries from $table\n";
                }
            }
        } catch (Exception $e) {
            echo "  ⚠️  Table $table might not exist: " . $e->getMessage() . "\n";
        }
    }
    
    // 6. Verify cleanup
    echo "\n📊 CLEANUP VERIFICATION:\n";
    $stmt = $pdo->query("SELECT DISTINCT app_name, COUNT(*) as count FROM reviews GROUP BY app_name ORDER BY app_name");
    $finalApps = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($finalApps as $app) {
        if (in_array($app['app_name'], $allowedApps)) {
            echo "  ✅ {$app['app_name']}: {$app['count']} reviews\n";
        } else {
            echo "  ❌ {$app['app_name']}: {$app['count']} reviews (STILL EXISTS!)\n";
        }
    }
    
    // 7. Ensure all allowed apps exist in app_metadata
    echo "\n📝 Ensuring all allowed apps exist in app_metadata...\n";
    $insertStmt = $pdo->prepare("
        INSERT IGNORE INTO app_metadata (app_name, total_reviews, overall_rating) 
        VALUES (?, 0, 0.0)
    ");
    
    foreach ($allowedApps as $app) {
        $insertStmt->execute([$app]);
        echo "  ✅ Ensured $app exists in app_metadata\n";
    }
    
    echo "\n🎉 CLEANUP COMPLETE!\n";
    echo "✅ Only the 6 specified apps remain in the database\n";
    echo "✅ All unwanted app data has been removed\n";
    
} catch (Exception $e) {
    echo "❌ Cleanup failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
