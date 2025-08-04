<?php
require_once __DIR__ . '/utils/DatabaseManager.php';

echo "=== CLEARING ALL DATABASE DATA ===\n";

try {
    $db = new DatabaseManager();
    $reflection = new ReflectionClass($db);
    $conn = $reflection->getProperty('conn');
    $conn->setAccessible(true);
    $pdo = $conn->getValue($db);
    
    // Clear reviews table
    $stmt = $pdo->prepare('DELETE FROM reviews');
    $stmt->execute();
    $reviewsCleared = $stmt->rowCount();
    echo "✅ Cleared $reviewsCleared reviews\n";
    
    // Clear app_metadata table
    $stmt = $pdo->prepare('DELETE FROM app_metadata');
    $stmt->execute();
    $metadataCleared = $stmt->rowCount();
    echo "✅ Cleared $metadataCleared metadata entries\n";
    
    echo "\n🎯 Database completely cleared! Ready for real-time scraping.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
