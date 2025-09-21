-- Add earned_by column to reviews table for Access Reviews functionality
-- This allows tracking which team member "earned" each review

USE shopify_reviews;

-- Add the earned_by column if it doesn't exist
ALTER TABLE reviews 
ADD COLUMN IF NOT EXISTS earned_by VARCHAR(255) NULL AFTER review_date,
ADD COLUMN IF NOT EXISTS is_featured TINYINT(1) DEFAULT 0 AFTER earned_by,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Add indexes for performance
CREATE INDEX IF NOT EXISTS idx_earned_by ON reviews(earned_by);
CREATE INDEX IF NOT EXISTS idx_is_featured ON reviews(is_featured);
CREATE INDEX IF NOT EXISTS idx_updated_at ON reviews(updated_at);

-- Show the updated table structure
DESCRIBE reviews;
