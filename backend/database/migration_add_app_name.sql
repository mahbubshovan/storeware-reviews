-- Migration to add app_name column to reviews table

USE shopify_reviews;

-- Add app_name column to existing reviews table
ALTER TABLE reviews ADD COLUMN app_name VARCHAR(100) AFTER id;

-- Add index for better performance on app_name queries
ALTER TABLE reviews ADD INDEX idx_app_name (app_name);

-- Update existing sample data with app names (optional)
UPDATE reviews SET app_name = 'StoreSEO' WHERE id <= 10;
UPDATE reviews SET app_name = 'StoreFAQ' WHERE id > 10 AND id <= 15;
UPDATE reviews SET app_name = 'Vidify' WHERE id > 15;
