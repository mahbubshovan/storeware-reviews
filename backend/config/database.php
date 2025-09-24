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
        // Load environment variables or use defaults
        $env = parse_ini_file(__DIR__.'/.env');
        $this->host = $_ENV['DB_HOST'] ?? $env['DB_HOST'] ?: 'localhost';
        $this->db_name = $_ENV['DB_NAME'] ?? $env['DB_NAME'] ?: 'shopify_reviews';
        $this->username = $_ENV['DB_USER'] ?? $env['DB_USER'] ?: 'root';
        $this->password = $_ENV['DB_PASS'] ?? $env['DB_PASS'] ?: '';
        $this->port = $_ENV['DB_PORT'] ?? $env['DB_PORT'] ?: '3306';
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $this->conn = new PDO(
                $dsn,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch(PDOException $exception) {
            error_log("Database connection error: " . $exception->getMessage());
            throw new Exception("Database connection failed: " . $exception->getMessage());
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
