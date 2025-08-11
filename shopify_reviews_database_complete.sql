-- =====================================================
-- SHOPIFY REVIEWS - COMPLETE DATABASE SETUP
-- =====================================================
-- This file contains the complete database structure and data
-- for the Shopify Reviews application
-- 
-- Instructions:
-- 1. Create a MySQL database named 'shopify_reviews'
-- 2. Import this file: mysql -u username -p shopify_reviews < shopify_reviews_database_complete.sql
-- 3. Update backend/config/database.php with your live server credentials
-- =====================================================

-- Create and use database
CREATE DATABASE IF NOT EXISTS shopify_reviews;
USE shopify_reviews;

-- =====================================================
-- MAIN REVIEWS TABLE
-- =====================================================
CREATE TABLE reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  app_name VARCHAR(100),
  store_name VARCHAR(255),
  country_name VARCHAR(100),
  rating INT CHECK (rating BETWEEN 1 AND 5),
  review_content TEXT,
  review_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_app_name (app_name),
  INDEX idx_review_date (review_date),
  INDEX idx_rating (rating),
  INDEX idx_created_at (created_at)
);

-- =====================================================
-- APP METADATA TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS app_metadata (
  id INT AUTO_INCREMENT PRIMARY KEY,
  app_name VARCHAR(100) UNIQUE,
  total_reviews INT DEFAULT 0,
  five_star_total INT DEFAULT 0,
  four_star_total INT DEFAULT 0,
  three_star_total INT DEFAULT 0,
  two_star_total INT DEFAULT 0,
  one_star_total INT DEFAULT 0,
  overall_rating DECIMAL(2,1) DEFAULT 0.0,
  last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_app_name (app_name)
);

-- =====================================================
-- ACCESS REVIEWS TABLE (Last 30 Days Reviews)
-- =====================================================
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
    
    -- Foreign key constraint to main reviews table
    FOREIGN KEY (original_review_id) REFERENCES reviews(id) ON DELETE CASCADE,
    
    -- Unique constraint to prevent duplicates
    UNIQUE KEY unique_review (original_review_id),
    
    -- Index for performance
    INDEX idx_app_name (app_name),
    INDEX idx_review_date (review_date),
    INDEX idx_earned_by (earned_by)
);

-- =====================================================
-- INSERT APP METADATA
-- =====================================================
INSERT INTO app_metadata (app_name, total_reviews, five_star_total, four_star_total, three_star_total, two_star_total, one_star_total, overall_rating) 
VALUES 
('StoreSEO', 521, 509, 9, 3, 0, 4, 5.0),
('StoreFAQ', 156, 140, 12, 3, 1, 0, 4.9),
('Vidify', 89, 78, 8, 2, 1, 0, 4.8),
('TrustSync', 234, 210, 18, 4, 2, 0, 4.9),
('EasyFlow', 67, 58, 7, 2, 0, 0, 4.8),
('BetterDocs FAQ', 123, 108, 12, 2, 1, 0, 4.9)
ON DUPLICATE KEY UPDATE
total_reviews = VALUES(total_reviews),
five_star_total = VALUES(five_star_total),
four_star_total = VALUES(four_star_total),
three_star_total = VALUES(three_star_total),
two_star_total = VALUES(two_star_total),
one_star_total = VALUES(one_star_total),
overall_rating = VALUES(overall_rating);

-- =====================================================
-- SAMPLE REVIEWS DATA
-- =====================================================
INSERT INTO reviews (app_name, store_name, country_name, rating, review_content, review_date) VALUES
-- StoreSEO Reviews
('StoreSEO', 'TechStore Pro', 'United States', 5, 'Amazing SEO app! Really helped boost our search rankings and organic traffic. The interface is intuitive and the features are exactly what we needed.', '2024-01-15'),
('StoreSEO', 'Fashion Forward', 'Canada', 4, 'Good SEO tool overall. Some features could be improved but it does what it promises. Customer support is responsive.', '2024-01-14'),
('StoreSEO', 'Global Gadgets', 'United Kingdom', 5, 'Excellent SEO functionality and great value for money. Would definitely recommend to other store owners.', '2024-01-13'),
('StoreSEO', 'Urban Style', 'Australia', 3, 'Decent SEO app but had some issues with setup. Once configured properly, it works fine.', '2024-01-12'),
('StoreSEO', 'Digital Dreams', 'Germany', 5, 'Perfect SEO solution for our needs. The analytics features are particularly useful for tracking performance.', '2024-01-11'),

-- StoreFAQ Reviews
('StoreFAQ', 'Eco Friendly Shop', 'Netherlands', 4, 'Very satisfied with this FAQ app. It integrates well with our existing workflow and saves us time.', '2024-01-10'),
('StoreFAQ', 'Sports Central', 'France', 2, 'Had some technical difficulties with FAQ setup and the documentation could be better. Support helped resolve issues eventually.', '2024-01-09'),
('StoreFAQ', 'Beauty Boutique', 'Italy', 5, 'Outstanding FAQ app! Easy to use and has all the features we were looking for. Highly recommended.', '2024-01-08'),
('StoreFAQ', 'Home Essentials', 'Spain', 4, 'Good FAQ value and reliable performance. The user interface is clean and professional.', '2024-01-07'),
('StoreFAQ', 'Tech Innovations', 'Sweden', 5, 'Fantastic FAQ app that exceeded our expectations. Great ROI and excellent customer service.', '2024-01-06'),

-- Vidify Reviews
('Vidify', 'Vintage Finds', 'Norway', 3, 'Average video experience. The app works but feels like it could use some modernization.', '2024-01-05'),
('Vidify', 'Modern Living', 'Denmark', 4, 'Solid video app with good features. Installation was straightforward and it integrates well.', '2024-01-04'),
('Vidify', 'Creative Corner', 'Finland', 5, 'Love this video app! It has transformed how we showcase our products to customers.', '2024-01-03'),
('Vidify', 'Outdoor Adventures', 'Belgium', 4, 'Very useful video app with good functionality. Some minor bugs but overall a positive experience.', '2024-01-02'),
('Vidify', 'Luxury Lifestyle', 'Switzerland', 5, 'Premium quality video app that delivers on its promises. Worth every penny and more.', '2024-01-01'),

-- TrustSync Reviews
('TrustSync', 'Fresh Market', 'United States', 5, 'Just started using TrustSync and already seeing great trust-building results. The setup was quick and easy.', '2024-01-20'),
('TrustSync', 'Tech Hub', 'Canada', 4, 'Good trust app with useful features. The analytics dashboard is particularly helpful for our business.', '2024-01-19'),
('TrustSync', 'Style Studio', 'United Kingdom', 5, 'Excellent trust app that has improved our store credibility significantly. Highly recommend!', '2024-01-18'),

-- EasyFlow Reviews
('EasyFlow', 'Digital Store', 'Australia', 3, 'Decent workflow functionality but could use some improvements in the user interface design.', '2024-01-17'),
('EasyFlow', 'Green Garden', 'Germany', 4, 'Very satisfied with the workflow features and performance. Good value for the price point.', '2024-01-16'),

-- BetterDocs FAQ Reviews
('BetterDocs FAQ', 'Smart Solutions', 'France', 5, 'Excellent documentation app! Makes creating and managing FAQs so much easier.', '2024-01-25'),
('BetterDocs FAQ', 'Pro Services', 'Italy', 4, 'Good documentation tool with solid features. Customer support is very helpful.', '2024-01-24');

-- =====================================================
-- RECENT REVIEWS FOR TESTING (Current Month)
-- =====================================================
INSERT INTO reviews (app_name, store_name, country_name, rating, review_content, review_date) VALUES
('StoreSEO', 'Fresh Market Today', 'United States', 5, 'Just started using StoreSEO and already seeing great SEO results. The setup was quick and easy.', CURDATE()),
('StoreFAQ', 'Tech Hub Pro', 'Canada', 4, 'Good FAQ app with useful features. The knowledge base dashboard is particularly helpful for our business.', DATE_SUB(CURDATE(), INTERVAL 1 DAY)),
('Vidify', 'Style Studio Plus', 'United Kingdom', 5, 'Excellent video app that has improved our product showcase significantly. Highly recommend!', DATE_SUB(CURDATE(), INTERVAL 2 DAY)),
('TrustSync', 'Digital Store Pro', 'Australia', 3, 'Decent trust functionality but could use some improvements in the user interface design.', DATE_SUB(CURDATE(), INTERVAL 3 DAY)),
('EasyFlow', 'Green Garden Plus', 'Germany', 4, 'Very satisfied with the workflow features and performance. Good value for the price point.', DATE_SUB(CURDATE(), INTERVAL 4 DAY)),
('BetterDocs FAQ', 'Modern Docs', 'Netherlands', 5, 'Amazing documentation app! Really streamlined our FAQ management process.', DATE_SUB(CURDATE(), INTERVAL 5 DAY));

-- =====================================================
-- DATABASE SETUP COMPLETE
-- =====================================================
-- Your database is now ready!
-- 
-- Next steps:
-- 1. Update backend/config/database.php with your live server credentials
-- 2. Test the connection by accessing your backend API endpoints
-- 3. The frontend should now be able to fetch data from these tables
-- 
-- Tables created:
-- - reviews: Main reviews data
-- - app_metadata: App statistics and metadata  
-- - access_reviews: Last 30 days reviews with editable fields
-- =====================================================
