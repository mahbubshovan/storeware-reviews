-- Create access_reviews table for managing last 30 days reviews with editable "Earned By" field
CREATE TABLE IF NOT EXISTS access_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    app_name VARCHAR(100) NOT NULL,
    review_date DATE NOT NULL,
    review_content TEXT NOT NULL,
    country_name VARCHAR(100),
    earned_by VARCHAR(255) NULL,
    original_review_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key constraint to main reviews table (NO CASCADE to preserve assignments)
    FOREIGN KEY (original_review_id) REFERENCES reviews(id),
    
    -- Unique constraint to prevent duplicates
    UNIQUE KEY unique_review (original_review_id),
    
    -- Index for performance
    INDEX idx_app_name (app_name),
    INDEX idx_review_date (review_date),
    INDEX idx_earned_by (earned_by)
);
