<?php
/**
 * Migration script to add earned_by column to reviews table
 * This enables the Access Reviews functionality
 */

require_once __DIR__ . '/../config/database.php';

try {
    echo "🔄 Starting database migration...\n";
    
    $database = new Database();
    $conn = $database->getConnection();
    
    // Check if earned_by column already exists
    $checkStmt = $conn->prepare("
        SELECT COLUMN_NAME 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = 'shopify_reviews' 
        AND TABLE_NAME = 'reviews' 
        AND COLUMN_NAME = 'earned_by'
    ");
    $checkStmt->execute();
    $columnExists = $checkStmt->fetch();
    
    if ($columnExists) {
        echo "✅ earned_by column already exists. Skipping migration.\n";
    } else {
        echo "📝 Adding earned_by column to reviews table...\n";
        
        // Add the earned_by column
        $conn->exec("
            ALTER TABLE reviews 
            ADD COLUMN earned_by VARCHAR(255) NULL AFTER review_date
        ");
        
        echo "✅ earned_by column added successfully.\n";
    }
    
    // Check if is_featured column exists
    $checkFeaturedStmt = $conn->prepare("
        SELECT COLUMN_NAME 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = 'shopify_reviews' 
        AND TABLE_NAME = 'reviews' 
        AND COLUMN_NAME = 'is_featured'
    ");
    $checkFeaturedStmt->execute();
    $featuredExists = $checkFeaturedStmt->fetch();
    
    if (!$featuredExists) {
        echo "📝 Adding is_featured column to reviews table...\n";
        
        $conn->exec("
            ALTER TABLE reviews 
            ADD COLUMN is_featured TINYINT(1) DEFAULT 0 AFTER earned_by
        ");
        
        echo "✅ is_featured column added successfully.\n";
    }
    
    // Check if updated_at column exists
    $checkUpdatedStmt = $conn->prepare("
        SELECT COLUMN_NAME 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = 'shopify_reviews' 
        AND TABLE_NAME = 'reviews' 
        AND COLUMN_NAME = 'updated_at'
    ");
    $checkUpdatedStmt->execute();
    $updatedExists = $checkUpdatedStmt->fetch();
    
    if (!$updatedExists) {
        echo "📝 Adding updated_at column to reviews table...\n";
        
        $conn->exec("
            ALTER TABLE reviews 
            ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at
        ");
        
        echo "✅ updated_at column added successfully.\n";
    }
    
    // Add indexes for performance
    echo "📝 Adding indexes for performance...\n";
    
    try {
        $conn->exec("CREATE INDEX IF NOT EXISTS idx_earned_by ON reviews(earned_by)");
        echo "✅ Index on earned_by created.\n";
    } catch (Exception $e) {
        echo "ℹ️  Index on earned_by already exists or error: " . $e->getMessage() . "\n";
    }
    
    try {
        $conn->exec("CREATE INDEX IF NOT EXISTS idx_is_featured ON reviews(is_featured)");
        echo "✅ Index on is_featured created.\n";
    } catch (Exception $e) {
        echo "ℹ️  Index on is_featured already exists or error: " . $e->getMessage() . "\n";
    }
    
    try {
        $conn->exec("CREATE INDEX IF NOT EXISTS idx_updated_at ON reviews(updated_at)");
        echo "✅ Index on updated_at created.\n";
    } catch (Exception $e) {
        echo "ℹ️  Index on updated_at already exists or error: " . $e->getMessage() . "\n";
    }
    
    // Show final table structure
    echo "\n📋 Final table structure:\n";
    $descStmt = $conn->prepare("DESCRIBE reviews");
    $descStmt->execute();
    $columns = $descStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "  - {$column['Field']}: {$column['Type']} " . 
             ($column['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . 
             ($column['Default'] ? " DEFAULT {$column['Default']}" : '') . "\n";
    }
    
    echo "\n🎉 Migration completed successfully!\n";
    echo "📊 The Access Reviews functionality is now ready to use.\n";
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
