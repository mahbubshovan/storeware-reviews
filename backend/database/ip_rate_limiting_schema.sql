-- IP-based Rate Limiting Schema
-- This table tracks scraping activity by IP address to prevent over-scraping

CREATE TABLE IF NOT EXISTS ip_scrape_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    last_scrape_timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    cooldown_expiry TIMESTAMP NOT NULL,
    scrape_count INT DEFAULT 1,
    app_name VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_ip_app (ip_address, app_name),
    INDEX idx_ip_cooldown (ip_address, cooldown_expiry),
    INDEX idx_cooldown_expiry (cooldown_expiry),
    INDEX idx_last_scrape (last_scrape_timestamp DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Scraping activity log for monitoring and debugging
CREATE TABLE IF NOT EXISTS scrape_activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    app_name VARCHAR(100) NOT NULL,
    action ENUM('scrape_allowed', 'scrape_blocked', 'rate_limit_applied', 'cooldown_expired') NOT NULL,
    message TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_ip_timestamp (ip_address, timestamp DESC),
    INDEX idx_app_timestamp (app_name, timestamp DESC),
    INDEX idx_action_timestamp (action, timestamp DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Insert initial data or update existing records
INSERT INTO ip_scrape_limits (ip_address, last_scrape_timestamp, cooldown_expiry, app_name) 
VALUES ('127.0.0.1', NOW() - INTERVAL 7 HOUR, NOW() - INTERVAL 1 HOUR, 'StoreSEO')
ON DUPLICATE KEY UPDATE 
    last_scrape_timestamp = VALUES(last_scrape_timestamp),
    cooldown_expiry = VALUES(cooldown_expiry);
