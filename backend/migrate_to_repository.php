<?php
/**
 * Migrate existing reviews to the new repository system
 * This script safely migrates data while preserving existing functionality
 */

require_once __DIR__ . '/utils/ReviewRepository.php';
require_once __DIR__ . '/utils/DatabaseManager.php';

echo "🔄 MIGRATING REVIEWS TO REPOSITORY SYSTEM\n";
echo "=========================================\n\n";

try {
    $repository = new ReviewRepository();
    $dbManager = new DatabaseManager();
    
    echo "📊 Checking current data...\n";
    
    // Get count of existing reviews
    $conn = $dbManager->getConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM reviews");
    $stmt->execute();
    $oldCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM review_repository");
    $stmt->execute();
    $repoCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "- Reviews in old table: $oldCount\n";
    echo "- Reviews in repository: $repoCount\n\n";
    
    if ($oldCount > 0) {
        echo "🔄 Starting migration...\n";
        $migrated = $repository->migrateExistingReviews();
        echo "✅ Migrated $migrated reviews to repository\n\n";
    }
    
    // Get final counts
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM review_repository");
    $stmt->execute();
    $finalCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "📈 MIGRATION SUMMARY:\n";
    echo "- Total reviews in repository: $finalCount\n";
    
    // Get app breakdown
    $stmt = $conn->prepare("
        SELECT app_name, COUNT(*) as count 
        FROM review_repository 
        WHERE is_active = TRUE 
        GROUP BY app_name 
        ORDER BY app_name
    ");
    $stmt->execute();
    $appBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n📱 APP BREAKDOWN:\n";
    foreach ($appBreakdown as $app) {
        echo "- {$app['app_name']}: {$app['count']} reviews\n";
    }
    
    echo "\n✨ Migration completed successfully!\n";
    echo "The pagination system is now ready to use.\n";
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
