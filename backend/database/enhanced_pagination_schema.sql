-- Enhanced Database Schema for Comprehensive Review Pagination and Filtering System
-- This schema supports permanent storage, pagination, and filtering while preserving existing functionality

USE shopify_reviews;

-- =====================================================
-- ENHANCED REVIEWS TABLE
-- =====================================================
-- Add unique constraint to prevent duplicates and support better data management
ALTER TABLE reviews 
ADD CONSTRAINT unique_review UNIQUE KEY (app_name, store_name, review_content(100), review_date);

-- Add additional indexes for pagination and filtering performance
ALTER TABLE reviews 
ADD INDEX idx_pagination (review_date DESC, created_at DESC, id DESC),
ADD INDEX idx_app_date (app_name, review_date DESC),
ADD INDEX idx_combined_filter (app_name, review_date, rating);

-- =====================================================
-- ENHANCED ACCESS REVIEWS TABLE
-- =====================================================
-- Modify access_reviews to support permanent storage and better filtering
ALTER TABLE access_reviews 
ADD COLUMN rating INT CHECK (rating BETWEEN 1 AND 5) AFTER review_content,
ADD COLUMN store_name VARCHAR(255) AFTER country_name,
ADD COLUMN is_archived BOOLEAN DEFAULT FALSE AFTER earned_by,
ADD COLUMN display_priority INT DEFAULT 0 AFTER is_archived;

-- Add indexes for enhanced access reviews functionality
ALTER TABLE access_reviews 
ADD INDEX idx_display_priority (display_priority DESC, review_date DESC),
ADD INDEX idx_archived (is_archived, app_name, review_date DESC),
ADD INDEX idx_rating_filter (app_name, rating, review_date DESC);

-- =====================================================
-- NEW REVIEW REPOSITORY TABLE
-- =====================================================
-- Master repository for all reviews - never deletes, only archives
CREATE TABLE IF NOT EXISTS review_repository (
    id INT AUTO_INCREMENT PRIMARY KEY,
    app_name VARCHAR(100) NOT NULL,
    store_name VARCHAR(255) NOT NULL,
    country_name VARCHAR(100),
    rating INT CHECK (rating BETWEEN 1 AND 5) NOT NULL,
    review_content TEXT NOT NULL,
    review_date DATE NOT NULL,
    
    -- Metadata for filtering and pagination
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    source_type ENUM('live_scrape', 'manual_entry', 'import') DEFAULT 'live_scrape',
    
    -- Assignment tracking
    earned_by VARCHAR(255) NULL,
    assigned_at TIMESTAMP NULL,
    assigned_by VARCHAR(100) NULL,
    
    -- Audit trail
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    scraped_at TIMESTAMP NULL,
    
    -- Original review reference (for migration)
    original_review_id INT NULL,
    
    -- Unique constraint to prevent duplicates
    UNIQUE KEY unique_repository_review (app_name, store_name, review_content(100), review_date),
    
    -- Indexes for performance
    INDEX idx_app_active (app_name, is_active, review_date DESC),
    INDEX idx_pagination_main (is_active, review_date DESC, created_at DESC, id DESC),
    INDEX idx_filtering (app_name, rating, review_date DESC, is_active),
    INDEX idx_assignments (earned_by, assigned_at DESC),
    INDEX idx_featured (is_featured, review_date DESC),
    INDEX idx_source (source_type, scraped_at DESC)
);

-- =====================================================
-- PAGINATION METADATA TABLE
-- =====================================================
-- Track pagination state and filtering preferences
CREATE TABLE IF NOT EXISTS pagination_metadata (
    id INT AUTO_INCREMENT PRIMARY KEY,
    context_type ENUM('homepage', 'access_reviews', 'admin') NOT NULL,
    app_filter VARCHAR(100) NULL,
    rating_filter INT NULL,
    date_from DATE NULL,
    date_to DATE NULL,
    sort_order ENUM('newest', 'oldest', 'rating_high', 'rating_low') DEFAULT 'newest',
    items_per_page INT DEFAULT 10,
    total_items INT DEFAULT 0,
    total_pages INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_context (context_type, last_updated DESC)
);

-- =====================================================
-- REVIEW DISPLAY SETTINGS TABLE
-- =====================================================
-- Configure how reviews are displayed in different contexts
CREATE TABLE IF NOT EXISTS review_display_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    context_name VARCHAR(50) NOT NULL UNIQUE,
    show_all_apps BOOLEAN DEFAULT TRUE,
    default_items_per_page INT DEFAULT 10,
    max_items_per_page INT DEFAULT 50,
    enable_filtering BOOLEAN DEFAULT TRUE,
    enable_sorting BOOLEAN DEFAULT TRUE,
    default_sort_order ENUM('newest', 'oldest', 'rating_high', 'rating_low') DEFAULT 'newest',
    show_archived BOOLEAN DEFAULT FALSE,
    auto_refresh_minutes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default display settings
INSERT INTO review_display_settings (context_name, show_all_apps, default_items_per_page, enable_filtering, enable_sorting) VALUES
('homepage_latest', TRUE, 10, TRUE, TRUE),
('access_reviews', TRUE, 20, TRUE, TRUE),
('admin_panel', TRUE, 25, TRUE, TRUE)
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- =====================================================
-- DATA MIGRATION PROCEDURES
-- =====================================================

-- Procedure to migrate existing reviews to repository
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS MigrateToRepository()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_id INT;
    DECLARE v_app_name VARCHAR(100);
    DECLARE v_store_name VARCHAR(255);
    DECLARE v_country_name VARCHAR(100);
    DECLARE v_rating INT;
    DECLARE v_review_content TEXT;
    DECLARE v_review_date DATE;
    DECLARE v_created_at TIMESTAMP;
    
    DECLARE cur CURSOR FOR 
        SELECT id, app_name, store_name, country_name, rating, review_content, review_date, created_at 
        FROM reviews 
        WHERE id NOT IN (SELECT COALESCE(original_review_id, 0) FROM review_repository WHERE original_review_id IS NOT NULL);
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN cur;
    
    read_loop: LOOP
        FETCH cur INTO v_id, v_app_name, v_store_name, v_country_name, v_rating, v_review_content, v_review_date, v_created_at;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        INSERT IGNORE INTO review_repository 
        (app_name, store_name, country_name, rating, review_content, review_date, original_review_id, created_at, scraped_at, source_type)
        VALUES 
        (v_app_name, v_store_name, v_country_name, v_rating, v_review_content, v_review_date, v_id, v_created_at, v_created_at, 'live_scrape');
        
    END LOOP;
    
    CLOSE cur;
END//
DELIMITER ;

-- =====================================================
-- INDEXES FOR OPTIMAL PERFORMANCE
-- =====================================================

-- Composite indexes for common query patterns
ALTER TABLE review_repository 
ADD INDEX idx_homepage_pagination (is_active, review_date DESC, id DESC),
ADD INDEX idx_app_pagination (app_name, is_active, review_date DESC, id DESC),
ADD INDEX idx_rating_pagination (rating, is_active, review_date DESC, id DESC),
ADD INDEX idx_assignment_tracking (earned_by, assigned_at DESC, is_active);

-- Full-text search index for review content (for future search functionality)
ALTER TABLE review_repository 
ADD FULLTEXT INDEX idx_content_search (review_content, store_name);

-- =====================================================
-- VIEWS FOR EASY DATA ACCESS
-- =====================================================

-- View for homepage latest reviews with pagination support
CREATE OR REPLACE VIEW homepage_reviews AS
SELECT 
    id,
    app_name,
    store_name,
    country_name,
    rating,
    review_content,
    review_date,
    earned_by,
    is_featured,
    created_at,
    ROW_NUMBER() OVER (ORDER BY review_date DESC, created_at DESC, id DESC) as row_num
FROM review_repository 
WHERE is_active = TRUE
ORDER BY review_date DESC, created_at DESC, id DESC;

-- View for access reviews with assignment tracking
CREATE OR REPLACE VIEW access_reviews_enhanced AS
SELECT 
    rr.*,
    CASE 
        WHEN rr.review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 'recent'
        WHEN rr.review_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN 'current_month'
        ELSE 'older'
    END as time_category
FROM review_repository rr
WHERE rr.is_active = TRUE
ORDER BY rr.review_date DESC, rr.created_at DESC;

-- View for statistics and counting
CREATE OR REPLACE VIEW review_statistics AS
SELECT 
    app_name,
    COUNT(*) as total_reviews,
    COUNT(CASE WHEN review_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as last_30_days,
    COUNT(CASE WHEN review_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') THEN 1 END) as this_month,
    AVG(rating) as average_rating,
    COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
    COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star,
    COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star,
    COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star,
    COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star,
    COUNT(CASE WHEN earned_by IS NOT NULL THEN 1 END) as assigned_reviews,
    MAX(review_date) as latest_review_date,
    MAX(updated_at) as last_updated
FROM review_repository 
WHERE is_active = TRUE
GROUP BY app_name;
