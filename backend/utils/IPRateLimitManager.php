<?php

/**
 * IP-based Rate Limiting Manager
 * Prevents over-scraping from the same IP address with 6-hour cooldown periods
 */
class IPRateLimitManager {
    private $conn;
    private $cooldownHours = 6;
    
    public function __construct($dbConnection = null) {
        if ($dbConnection) {
            $this->conn = $dbConnection;
        } else {
            require_once __DIR__ . '/../config/database.php';
            $db = new Database();
            $this->conn = $db->getConnection();
        }
    }
    
    /**
     * Get client IP address (handles proxies and load balancers)
     */
    public function getClientIP() {
        // Check for various headers that might contain the real IP
        $headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standard
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                // Handle comma-separated IPs (X-Forwarded-For can contain multiple IPs)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                // Validate IP address
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        // Fallback to localhost for development
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    
    /**
     * Check if IP is allowed to scrape (not in cooldown period)
     */
    public function canScrape($appName = null, $ipAddress = null) {
        if (!$ipAddress) {
            $ipAddress = $this->getClientIP();
        }
        
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    last_scrape_timestamp,
                    cooldown_expiry,
                    scrape_count
                FROM ip_scrape_limits 
                WHERE ip_address = ? AND (app_name = ? OR app_name IS NULL)
                ORDER BY last_scrape_timestamp DESC
                LIMIT 1
            ");
            $stmt->execute([$ipAddress, $appName]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                // No previous scraping record - allow scraping
                $this->logActivity($ipAddress, $appName, 'scrape_allowed', 'No previous scraping record found');
                return true;
            }
            
            $now = new DateTime();
            $cooldownExpiry = new DateTime($result['cooldown_expiry']);
            
            if ($now < $cooldownExpiry) {
                // Still in cooldown period
                $remainingTime = $cooldownExpiry->diff($now);
                $message = "IP in cooldown. Remaining: " . $remainingTime->format('%h hours %i minutes');
                $this->logActivity($ipAddress, $appName, 'scrape_blocked', $message);
                return false;
            }
            
            // Cooldown expired - allow scraping
            $this->logActivity($ipAddress, $appName, 'cooldown_expired', 'Cooldown period has expired, allowing scrape');
            return true;
            
        } catch (Exception $e) {
            error_log("Rate limit check error: " . $e->getMessage());
            // On error, allow scraping to prevent blocking legitimate requests
            return true;
        }
    }
    
    /**
     * Record a scraping attempt and set new cooldown period
     */
    public function recordScrape($appName = null, $ipAddress = null) {
        if (!$ipAddress) {
            $ipAddress = $this->getClientIP();
        }
        
        try {
            $now = new DateTime();
            $cooldownExpiry = clone $now;
            $cooldownExpiry->add(new DateInterval('PT' . $this->cooldownHours . 'H'));
            
            $stmt = $this->conn->prepare("
                INSERT INTO ip_scrape_limits 
                (ip_address, app_name, last_scrape_timestamp, cooldown_expiry, scrape_count) 
                VALUES (?, ?, ?, ?, 1)
                ON DUPLICATE KEY UPDATE
                    last_scrape_timestamp = VALUES(last_scrape_timestamp),
                    cooldown_expiry = VALUES(cooldown_expiry),
                    scrape_count = scrape_count + 1,
                    updated_at = CURRENT_TIMESTAMP
            ");
            
            $stmt->execute([
                $ipAddress,
                $appName,
                $now->format('Y-m-d H:i:s'),
                $cooldownExpiry->format('Y-m-d H:i:s')
            ]);
            
            $message = "Scrape recorded. Next allowed at: " . $cooldownExpiry->format('Y-m-d H:i:s');
            $this->logActivity($ipAddress, $appName, 'rate_limit_applied', $message);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Record scrape error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get remaining cooldown time for an IP
     */
    public function getRemainingCooldown($appName = null, $ipAddress = null) {
        if (!$ipAddress) {
            $ipAddress = $this->getClientIP();
        }
        
        try {
            $stmt = $this->conn->prepare("
                SELECT cooldown_expiry 
                FROM ip_scrape_limits 
                WHERE ip_address = ? AND (app_name = ? OR app_name IS NULL)
                ORDER BY last_scrape_timestamp DESC
                LIMIT 1
            ");
            $stmt->execute([$ipAddress, $appName]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return 0; // No cooldown
            }
            
            $now = new DateTime();
            $cooldownExpiry = new DateTime($result['cooldown_expiry']);
            
            if ($now >= $cooldownExpiry) {
                return 0; // Cooldown expired
            }
            
            return $cooldownExpiry->getTimestamp() - $now->getTimestamp();
            
        } catch (Exception $e) {
            error_log("Get cooldown error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Log scraping activity for monitoring
     */
    private function logActivity($ipAddress, $appName, $action, $message = null) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO scrape_activity_log (ip_address, app_name, action, message) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$ipAddress, $appName ?? 'ALL', $action, $message]);
        } catch (Exception $e) {
            error_log("Activity log error: " . $e->getMessage());
        }
    }
    
    /**
     * Clear all rate limits (for emergency fixes)
     */
    public function clearAllRateLimits() {
        try {
            $stmt = $this->conn->prepare("DELETE FROM ip_scrape_limits");
            $stmt->execute();
            error_log("All rate limits cleared");
            return true;
        } catch (Exception $e) {
            error_log("Clear rate limits error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clean up old rate limit records (older than 7 days)
     */
    public function cleanup() {
        try {
            $stmt = $this->conn->prepare("
                DELETE FROM ip_scrape_limits
                WHERE cooldown_expiry < DATE_SUB(NOW(), INTERVAL 7 DAY)
            ");
            $stmt->execute();

            $stmt = $this->conn->prepare("
                DELETE FROM scrape_activity_log
                WHERE timestamp < DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stmt->execute();

        } catch (Exception $e) {
            error_log("Cleanup error: " . $e->getMessage());
        }
    }
}
?>
