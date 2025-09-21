-- =====================================================
-- PER-DEVICE RATE LIMITING MIGRATION
-- =====================================================
-- This migration adds support for per-device 6-hour rate limiting
-- Run this on existing databases to add the new tables
-- =====================================================

USE shopify_reviews;

-- Track unique client devices
CREATE TABLE IF NOT EXISTS clients (
  client_id CHAR(36) PRIMARY KEY,
  first_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
  last_seen DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_last_seen (last_seen)
);

-- Per-device scraping schedule (6-hour rate limiting)
CREATE TABLE IF NOT EXISTS scrape_schedule (
  id INT AUTO_INCREMENT PRIMARY KEY,
  app_slug VARCHAR(64) NOT NULL,
  client_id CHAR(36) NOT NULL,
  next_run_at DATETIME NOT NULL,
  last_run_at DATETIME NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_app_client (app_slug, client_id),
  FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE CASCADE,
  INDEX idx_next_run (next_run_at),
  INDEX idx_app_slug (app_slug)
);

-- Immutable snapshots of scraped data
CREATE TABLE IF NOT EXISTS snapshots (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  app_slug VARCHAR(64) NOT NULL,
  scraped_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  source_url TEXT NOT NULL,
  etag VARCHAR(255) NULL,
  last_modified VARCHAR(255) NULL,
  content_hash CHAR(64) NOT NULL,
  totals JSON NOT NULL,
  last30Days JSON NOT NULL,
  thisMonth JSON NOT NULL,
  ratingDistribution JSON NOT NULL,
  latestReviews JSON NOT NULL,
  INDEX idx_app_scraped (app_slug, scraped_at),
  INDEX idx_content_hash (content_hash)
);

-- Per-device pointers to current snapshot
CREATE TABLE IF NOT EXISTS snapshot_pointer (
  id INT AUTO_INCREMENT PRIMARY KEY,
  app_slug VARCHAR(64) NOT NULL,
  client_id CHAR(36) NOT NULL,
  snapshot_id BIGINT NOT NULL,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_ptr (app_slug, client_id),
  FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE CASCADE,
  FOREIGN KEY (snapshot_id) REFERENCES snapshots(id) ON DELETE CASCADE,
  INDEX idx_app_client (app_slug, client_id)
);

-- Track upstream changes for change detection
CREATE TABLE IF NOT EXISTS upstream_state (
  app_slug VARCHAR(64) PRIMARY KEY,
  last_content_hash CHAR(64) NOT NULL,
  last_seen_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_last_seen (last_seen_at)
);

-- Initialize upstream_state for existing apps
INSERT IGNORE INTO upstream_state (app_slug, last_content_hash) VALUES
('storeseo', ''),
('storefaq', ''),
('vidify', ''),
('customer-review-app', ''),
('product-options-4', ''),
('betterdocs-knowledgebase', '');

-- Add cleanup procedure for old snapshots (optional)
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS CleanupOldSnapshots()
BEGIN
    -- Keep only last 10 snapshots per app to prevent table bloat
    DELETE s1 FROM snapshots s1
    INNER JOIN (
        SELECT app_slug, id
        FROM snapshots s2
        WHERE s2.app_slug = s1.app_slug
        ORDER BY scraped_at DESC
        LIMIT 10, 18446744073709551615
    ) s2 ON s1.id = s2.id;
    
    -- Clean up orphaned snapshot_pointers
    DELETE sp FROM snapshot_pointer sp
    LEFT JOIN snapshots s ON sp.snapshot_id = s.id
    WHERE s.id IS NULL;
END //
DELIMITER ;

-- Create event scheduler for cleanup (runs daily at 2 AM)
-- Note: Requires EVENT_SCHEDULER to be ON
CREATE EVENT IF NOT EXISTS cleanup_old_snapshots
ON SCHEDULE EVERY 1 DAY
STARTS TIMESTAMP(CURDATE() + INTERVAL 1 DAY, '02:00:00')
DO CALL CleanupOldSnapshots();

COMMIT;
