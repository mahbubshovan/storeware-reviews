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
        // Railway MySQL environment variables (with fallbacks for local development)
        $this->host = $_ENV['MYSQL_HOST'] ?? $_ENV['DB_HOST'] ?? getenv('MYSQL_HOST') ?: getenv('DB_HOST') ?: 'localhost';
        $this->db_name = $_ENV['MYSQL_DATABASE'] ?? $_ENV['DB_NAME'] ?? getenv('MYSQL_DATABASE') ?: getenv('DB_NAME') ?: 'shopify_reviews';
        $this->username = $_ENV['MYSQL_USER'] ?? $_ENV['DB_USER'] ?? getenv('MYSQL_USER') ?: getenv('DB_USER') ?: 'root';
        $this->password = $_ENV['MYSQL_PASSWORD'] ?? $_ENV['DB_PASS'] ?? getenv('MYSQL_PASSWORD') ?: getenv('DB_PASS') ?: '';

        // Railway also provides MYSQL_PORT
        $this->port = $_ENV['MYSQL_PORT'] ?? getenv('MYSQL_PORT') ?: '3306';
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
?>
