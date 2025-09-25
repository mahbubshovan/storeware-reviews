<?php
/**
 * Database Configuration
 */

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    private $conn;

    public function __construct() {
        // Load environment variables from multiple sources
        $env = file_exists(__DIR__.'/.env') ? parse_ini_file(__DIR__.'/.env') : [];

        // Priority: Railway MYSQL_* > System ENV > .env file > defaults
        // Railway uses MYSQL_* variables, local development uses DB_* or .env
        $this->host = $_ENV['MYSQL_HOST'] ?? $_ENV['DB_HOST'] ?? $env['DB_HOST'] ?? getenv('MYSQL_HOST') ?: getenv('DB_HOST') ?: 'localhost';
        $this->db_name = $_ENV['MYSQL_DATABASE'] ?? $_ENV['DB_NAME'] ?? $env['DB_NAME'] ?? getenv('MYSQL_DATABASE') ?: getenv('DB_NAME') ?: 'shopify_reviews';
        $this->username = $_ENV['MYSQL_USER'] ?? $_ENV['DB_USER'] ?? $env['DB_USER'] ?? getenv('MYSQL_USER') ?: getenv('DB_USER') ?: 'root';
        $this->password = $_ENV['MYSQL_PASSWORD'] ?? $_ENV['DB_PASS'] ?? $env['DB_PASS'] ?? getenv('MYSQL_PASSWORD') ?: getenv('DB_PASS') ?: '';
        $this->port = $_ENV['MYSQL_PORT'] ?? $_ENV['DB_PORT'] ?? $env['DB_PORT'] ?? getenv('MYSQL_PORT') ?: getenv('DB_PORT') ?: '3306';

        // Debug logging for live server (remove after testing)
        if (isset($_ENV['MYSQL_HOST']) || getenv('MYSQL_HOST')) {
            error_log("Railway DB Config - Host: {$this->host}, DB: {$this->db_name}, User: {$this->username}");
        }
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8mb4";

            // Enhanced logging for debugging live server issues
            error_log("Attempting database connection to: {$this->host}:{$this->port}/{$this->db_name} as {$this->username}");

            $this->conn = new PDO(
                $dsn,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 30, // 30 second timeout
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );

            // Test the connection with a simple query
            $testStmt = $this->conn->query("SELECT 1");
            if ($testStmt) {
                error_log("Database connection successful to {$this->host}/{$this->db_name}");
            }

        } catch(PDOException $exception) {
            $errorMsg = "Database connection failed to {$this->host}:{$this->port}/{$this->db_name} as {$this->username} - " . $exception->getMessage();
            error_log($errorMsg);

            // Log environment variables for debugging (without password)
            error_log("Environment check - MYSQL_HOST: " . (getenv('MYSQL_HOST') ? 'SET' : 'NOT_SET') .
                     ", MYSQL_DATABASE: " . (getenv('MYSQL_DATABASE') ? 'SET' : 'NOT_SET') .
                     ", MYSQL_USER: " . (getenv('MYSQL_USER') ? 'SET' : 'NOT_SET') .
                     ", MYSQL_PASSWORD: " . (getenv('MYSQL_PASSWORD') ? 'SET' : 'NOT_SET'));

            throw new Exception($errorMsg);
        }

        return $this->conn;
    }
}

/**
 * Global function for backward compatibility
 * Returns a PDO connection using the centralized Database class
 */
function getDbConnection() {
    $database = new Database();
    return $database->getConnection();
}
?>
