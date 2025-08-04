<?php
require_once __DIR__ . '/utils/DatabaseManager.php';

if ($argc < 2) {
    echo "Usage: php clear_app_data.php [APP_NAME]\n";
    echo "Example: php clear_app_data.php StoreSEO\n";
    exit(1);
}

$appName = $argv[1];

try {
    $dbManager = new DatabaseManager();
    
    // Get database connection using reflection to access private property
    $reflection = new ReflectionClass($dbManager);
    $connProperty = $reflection->getProperty('conn');
    $connProperty->setAccessible(true);
    $conn = $connProperty->getValue($dbManager);
    
    echo "Clearing all data for app: $appName\n";

    // Delete all reviews for the specific app
    $query = "DELETE FROM reviews WHERE app_name = :app_name";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":app_name", $appName);
    $stmt->execute();
    $deletedReviews = $stmt->rowCount();
    echo "✅ Deleted $deletedReviews reviews\n";

    // Clear metadata
    $stmt = $conn->prepare("DELETE FROM app_metadata WHERE app_name = :app_name");
    $stmt->bindParam(":app_name", $appName);
    $stmt->execute();
    $deletedMeta = $stmt->rowCount();
    echo "✅ Deleted $deletedMeta metadata entries\n";

    echo "✅ All data cleared for $appName\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
