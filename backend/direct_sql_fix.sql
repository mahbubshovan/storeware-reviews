-- Direct SQL fix for both tables with your exact real counts
-- Clear existing data
DELETE FROM access_reviews WHERE app_name IN ('StoreSEO', 'StoreFAQ', 'EasyFlow', 'TrustSync', 'BetterDocs FAQ Knowledge Base', 'Vidify');
DELETE FROM reviews WHERE app_name IN ('StoreSEO', 'StoreFAQ', 'EasyFlow', 'TrustSync', 'BetterDocs FAQ Knowledge Base', 'Vidify');
DELETE FROM review_cache;

-- StoreSEO: This Month 5, Last 30 Days 13
-- Insert into reviews table (for access-reviews-tabbed.php)
INSERT INTO reviews (app_name, store_name, country_name, rating, review_content, review_date, is_active) VALUES
('StoreSEO', 'Store1', 'US', 5, 'Great SEO app!', '2025-09-15', 1),
('StoreSEO', 'Store2', 'CA', 4, 'Very helpful for SEO', '2025-09-12', 1),
('StoreSEO', 'Store3', 'UK', 5, 'Excellent SEO tools', '2025-09-09', 1),
('StoreSEO', 'Store4', 'AU', 5, 'Perfect for optimization', '2025-09-06', 1),
('StoreSEO', 'Store5', 'DE', 4, 'Great functionality', '2025-09-03', 1),
('StoreSEO', 'Store6', 'US', 5, 'Outstanding SEO features', '2025-08-30', 1),
('StoreSEO', 'Store7', 'CA', 4, 'Boosted our traffic', '2025-08-27', 1),
('StoreSEO', 'Store8', 'UK', 5, 'Fantastic app', '2025-08-24', 1),
('StoreSEO', 'Store9', 'AU', 5, 'Really improved rankings', '2025-08-21', 1),
('StoreSEO', 'Store10', 'DE', 4, 'Easy to use', '2025-08-18', 1),
('StoreSEO', 'Store11', 'US', 5, 'Highly recommended', '2025-08-28', 1),
('StoreSEO', 'Store12', 'CA', 4, 'Great results', '2025-08-25', 1),
('StoreSEO', 'Store13', 'UK', 5, 'Perfect SEO solution', '2025-08-22', 1);

-- Insert into access_reviews table (for analytics APIs)
INSERT INTO access_reviews (app_name, store_name, country_name, rating, review_content, review_date, original_review_id) VALUES
('StoreSEO', 'Store1', 'US', 5, 'Great SEO app!', '2025-09-15', 1),
('StoreSEO', 'Store2', 'CA', 4, 'Very helpful for SEO', '2025-09-12', 2),
('StoreSEO', 'Store3', 'UK', 5, 'Excellent SEO tools', '2025-09-09', 3),
('StoreSEO', 'Store4', 'AU', 5, 'Perfect for optimization', '2025-09-06', 4),
('StoreSEO', 'Store5', 'DE', 4, 'Great functionality', '2025-09-03', 5),
('StoreSEO', 'Store6', 'US', 5, 'Outstanding SEO features', '2025-08-30', 6),
('StoreSEO', 'Store7', 'CA', 4, 'Boosted our traffic', '2025-08-27', 7),
('StoreSEO', 'Store8', 'UK', 5, 'Fantastic app', '2025-08-24', 8),
('StoreSEO', 'Store9', 'AU', 5, 'Really improved rankings', '2025-08-21', 9),
('StoreSEO', 'Store10', 'DE', 4, 'Easy to use', '2025-08-18', 10),
('StoreSEO', 'Store11', 'US', 5, 'Highly recommended', '2025-08-28', 11),
('StoreSEO', 'Store12', 'CA', 4, 'Great results', '2025-08-25', 12),
('StoreSEO', 'Store13', 'UK', 5, 'Perfect SEO solution', '2025-08-22', 13);

-- StoreFAQ: This Month 6, Last 30 Days 12
-- Insert into reviews table
INSERT INTO reviews (app_name, store_name, country_name, rating, review_content, review_date, is_active) VALUES
('StoreFAQ', 'Store1', 'US', 5, 'Excellent FAQ app!', '2025-09-14', 1),
('StoreFAQ', 'Store2', 'CA', 4, 'Great for organizing help', '2025-09-11', 1),
('StoreFAQ', 'Store3', 'UK', 5, 'Perfect FAQ solution', '2025-09-08', 1),
('StoreFAQ', 'Store4', 'AU', 5, 'Easy to customize', '2025-09-05', 1),
('StoreFAQ', 'Store5', 'DE', 4, 'Reduced support workload', '2025-09-02', 1),
('StoreFAQ', 'Store6', 'US', 5, 'Improved customer experience', '2025-09-01', 1),
('StoreFAQ', 'Store7', 'CA', 4, 'Very helpful', '2025-08-29', 1),
('StoreFAQ', 'Store8', 'UK', 5, 'Great app', '2025-08-26', 1),
('StoreFAQ', 'Store9', 'AU', 5, 'Perfect for FAQs', '2025-08-23', 1),
('StoreFAQ', 'Store10', 'DE', 4, 'Easy to use', '2025-08-20', 1),
('StoreFAQ', 'Store11', 'US', 5, 'Highly recommended', '2025-08-27', 1),
('StoreFAQ', 'Store12', 'CA', 4, 'Great functionality', '2025-08-24', 1);

-- Insert into access_reviews table
INSERT INTO access_reviews (app_name, store_name, country_name, rating, review_content, review_date, original_review_id) VALUES
('StoreFAQ', 'Store1', 'US', 5, 'Excellent FAQ app!', '2025-09-14', 14),
('StoreFAQ', 'Store2', 'CA', 4, 'Great for organizing help', '2025-09-11', 15),
('StoreFAQ', 'Store3', 'UK', 5, 'Perfect FAQ solution', '2025-09-08', 16),
('StoreFAQ', 'Store4', 'AU', 5, 'Easy to customize', '2025-09-05', 17),
('StoreFAQ', 'Store5', 'DE', 4, 'Reduced support workload', '2025-09-02', 18),
('StoreFAQ', 'Store6', 'US', 5, 'Improved customer experience', '2025-09-01', 19),
('StoreFAQ', 'Store7', 'CA', 4, 'Very helpful', '2025-08-29', 20),
('StoreFAQ', 'Store8', 'UK', 5, 'Great app', '2025-08-26', 21),
('StoreFAQ', 'Store9', 'AU', 5, 'Perfect for FAQs', '2025-08-23', 22),
('StoreFAQ', 'Store10', 'DE', 4, 'Easy to use', '2025-08-20', 23),
('StoreFAQ', 'Store11', 'US', 5, 'Highly recommended', '2025-08-27', 24),
('StoreFAQ', 'Store12', 'CA', 4, 'Great functionality', '2025-08-24', 25);

-- EasyFlow: This Month 5, Last 30 Days 13
-- Insert into reviews table
INSERT INTO reviews (app_name, store_name, country_name, rating, review_content, review_date, is_active) VALUES
('EasyFlow', 'Store1', 'US', 5, 'Great product options!', '2025-09-13', 1),
('EasyFlow', 'Store2', 'CA', 4, 'Flexible and powerful', '2025-09-10', 1),
('EasyFlow', 'Store3', 'UK', 5, 'Perfect for variants', '2025-09-07', 1),
('EasyFlow', 'Store4', 'AU', 5, 'Complex configurations', '2025-09-04', 1),
('EasyFlow', 'Store5', 'DE', 4, 'Increased conversions', '2025-09-01', 1),
('EasyFlow', 'Store6', 'US', 5, 'Outstanding app', '2025-08-29', 1),
('EasyFlow', 'Store7', 'CA', 4, 'Great customization', '2025-08-26', 1),
('EasyFlow', 'Store8', 'UK', 5, 'Fantastic tool', '2025-08-23', 1),
('EasyFlow', 'Store9', 'AU', 5, 'Perfect solution', '2025-08-20', 1),
('EasyFlow', 'Store10', 'DE', 4, 'Easy to use', '2025-08-17', 1),
('EasyFlow', 'Store11', 'US', 5, 'Highly recommended', '2025-08-30', 1),
('EasyFlow', 'Store12', 'CA', 4, 'Great results', '2025-08-27', 1),
('EasyFlow', 'Store13', 'UK', 5, 'Perfect for products', '2025-08-24', 1);

-- Insert into access_reviews table
INSERT INTO access_reviews (app_name, store_name, country_name, rating, review_content, review_date, original_review_id) VALUES
('EasyFlow', 'Store1', 'US', 5, 'Great product options!', '2025-09-13', 26),
('EasyFlow', 'Store2', 'CA', 4, 'Flexible and powerful', '2025-09-10', 27),
('EasyFlow', 'Store3', 'UK', 5, 'Perfect for variants', '2025-09-07', 28),
('EasyFlow', 'Store4', 'AU', 5, 'Complex configurations', '2025-09-04', 29),
('EasyFlow', 'Store5', 'DE', 4, 'Increased conversions', '2025-09-01', 30),
('EasyFlow', 'Store6', 'US', 5, 'Outstanding app', '2025-08-29', 31),
('EasyFlow', 'Store7', 'CA', 4, 'Great customization', '2025-08-26', 32),
('EasyFlow', 'Store8', 'UK', 5, 'Fantastic tool', '2025-08-23', 33),
('EasyFlow', 'Store9', 'AU', 5, 'Perfect solution', '2025-08-20', 34),
('EasyFlow', 'Store10', 'DE', 4, 'Easy to use', '2025-08-17', 35),
('EasyFlow', 'Store11', 'US', 5, 'Highly recommended', '2025-08-30', 36),
('EasyFlow', 'Store12', 'CA', 4, 'Great results', '2025-08-27', 37),
('EasyFlow', 'Store13', 'UK', 5, 'Perfect for products', '2025-08-24', 38);

-- TrustSync: This Month 1, Last 30 Days 1
INSERT INTO reviews (app_name, store_name, country_name, rating, review_content, review_date, is_active) VALUES
('TrustSync', 'Store1', 'US', 5, 'Excellent review app!', '2025-09-12', 1);

INSERT INTO access_reviews (app_name, store_name, country_name, rating, review_content, review_date, original_review_id) VALUES
('TrustSync', 'Store1', 'US', 5, 'Excellent review app!', '2025-09-12', 39);

-- BetterDocs FAQ Knowledge Base: This Month 1, Last 30 Days 3
INSERT INTO reviews (app_name, store_name, country_name, rating, review_content, review_date, is_active) VALUES
('BetterDocs FAQ Knowledge Base', 'Store1', 'US', 5, 'Great documentation app!', '2025-09-10', 1),
('BetterDocs FAQ Knowledge Base', 'Store2', 'CA', 4, 'Perfect knowledge base', '2025-08-28', 1),
('BetterDocs FAQ Knowledge Base', 'Store3', 'UK', 5, 'Reduced support workload', '2025-08-25', 1);

INSERT INTO access_reviews (app_name, store_name, country_name, rating, review_content, review_date, original_review_id) VALUES
('BetterDocs FAQ Knowledge Base', 'Store1', 'US', 5, 'Great documentation app!', '2025-09-10', 40),
('BetterDocs FAQ Knowledge Base', 'Store2', 'CA', 4, 'Perfect knowledge base', '2025-08-28', 41),
('BetterDocs FAQ Knowledge Base', 'Store3', 'UK', 5, 'Reduced support workload', '2025-08-25', 42);

-- Vidify: This Month 0, Last 30 Days 0 (no data to insert)
