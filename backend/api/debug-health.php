<?php
/**
 * Debug Health Check - Comprehensive diagnostics for live server issues
 * This endpoint helps identify why data isn't being stored on live servers
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    require_once __DIR__ . '/../config/database.php';
    
    $health = [
        'timestamp' => date('Y-m-d H:i:s'),
        'server_info' => [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
            'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'Unknown'
        ],
        'environment_variables' => [
            'railway_mysql' => [
                'MYSQL_HOST' => getenv('MYSQL_HOST') ? 'SET (' . getenv('MYSQL_HOST') . ')' : 'NOT_SET',
                'MYSQL_DATABASE' => getenv('MYSQL_DATABASE') ? 'SET (' . getenv('MYSQL_DATABASE') . ')' : 'NOT_SET',
                'MYSQL_USER' => getenv('MYSQL_USER') ? 'SET (' . getenv('MYSQL_USER') . ')' : 'NOT_SET',
                'MYSQL_PASSWORD' => getenv('MYSQL_PASSWORD') ? 'SET (****)' : 'NOT_SET',
                'MYSQL_PORT' => getenv('MYSQL_PORT') ? 'SET (' . getenv('MYSQL_PORT') . ')' : 'NOT_SET'
            ],
            'standard_db' => [
                'DB_HOST' => getenv('DB_HOST') ? 'SET (' . getenv('DB_HOST') . ')' : 'NOT_SET',
                'DB_NAME' => getenv('DB_NAME') ? 'SET (' . getenv('DB_NAME') . ')' : 'NOT_SET',
                'DB_USER' => getenv('DB_USER') ? 'SET (' . getenv('DB_USER') . ')' : 'NOT_SET',
                'DB_PASS' => getenv('DB_PASS') ? 'SET (****)' : 'NOT_SET'
            ],
            'env_superglobal' => [
                'MYSQL_HOST' => $_ENV['MYSQL_HOST'] ?? 'NOT_SET',
                'MYSQL_DATABASE' => $_ENV['MYSQL_DATABASE'] ?? 'NOT_SET',
                'MYSQL_USER' => $_ENV['MYSQL_USER'] ?? 'NOT_SET',
                'MYSQL_PASSWORD' => isset($_ENV['MYSQL_PASSWORD']) ? 'SET (****)' : 'NOT_SET'
            ]
        ],
        'database_connection' => 'attempting...',
        'tables' => [],
        'sample_data' => [],
        'file_permissions' => [
            'config_dir' => is_readable(__DIR__ . '/../config/') ? 'readable' : 'not_readable',
            'database_php' => is_readable(__DIR__ . '/../config/database.php') ? 'readable' : 'not_readable',
            'env_file' => file_exists(__DIR__ . '/../config/.env') ? 'exists' : 'not_exists'
        ]
    ];
    
    // Test database connection
    try {
        $database = new Database();
        $conn = $database->getConnection();
        $health['database_connection'] = 'connected';
        
        // Test basic tables
        $tables = ['reviews', 'access_reviews', 'app_metadata'];
        foreach ($tables as $table) {
            try {
                $stmt = $conn->query("SELECT COUNT(*) as count FROM $table");
                $result = $stmt->fetch();
                $health['tables'][$table] = [
                    'status' => 'exists',
                    'count' => (int)$result['count']
                ];
            } catch (Exception $e) {
                $health['tables'][$table] = [
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        // Get sample data from reviews table
        try {
            $stmt = $conn->query("SELECT app_name, COUNT(*) as count FROM reviews GROUP BY app_name ORDER BY count DESC LIMIT 5");
            $health['sample_data']['app_counts'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $health['sample_data']['app_counts'] = 'error: ' . $e->getMessage();
        }
        
        // Test write capability
        try {
            $testTable = "test_write_" . time();
            $conn->exec("CREATE TEMPORARY TABLE $testTable (id INT, test_data VARCHAR(50))");
            $conn->exec("INSERT INTO $testTable (id, test_data) VALUES (1, 'test')");
            $stmt = $conn->query("SELECT COUNT(*) FROM $testTable");
            $health['write_test'] = 'success';
            $conn->exec("DROP TEMPORARY TABLE $testTable");
        } catch (Exception $e) {
            $health['write_test'] = 'failed: ' . $e->getMessage();
        }
        
    } catch (Exception $e) {
        $health['database_connection'] = 'failed';
        $health['database_error'] = $e->getMessage();
    }
    
    // Check if we're on Railway
    $health['platform_detection'] = [
        'is_railway' => getenv('RAILWAY_ENVIRONMENT') ? 'yes' : 'no',
        'railway_env' => getenv('RAILWAY_ENVIRONMENT') ?? 'not_set',
        'port' => getenv('PORT') ?? 'not_set'
    ];
    
    echo json_encode($health, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Health check failed',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}
?>
