-- Shopify Reviews Database Schema

CREATE DATABASE IF NOT EXISTS shopify_reviews;
USE shopify_reviews;

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
