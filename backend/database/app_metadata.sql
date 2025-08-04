-- Create table to store app metadata like total review counts

USE shopify_reviews;

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

-- Insert the expected data for StoreSEO
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
