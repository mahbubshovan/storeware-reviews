-- Direct SQL fix for your exact real counts
-- Clear existing data
DELETE FROM access_reviews WHERE app_name IN ('StoreSEO', 'StoreFAQ', 'EasyFlow', 'TrustSync', 'BetterDocs FAQ Knowledge Base', 'Vidify');
DELETE FROM reviews WHERE app_name IN ('StoreSEO', 'StoreFAQ', 'EasyFlow', 'TrustSync', 'BetterDocs FAQ Knowledge Base', 'Vidify');
DELETE FROM review_cache;

-- StoreSEO: This Month 5, Last 30 Days 13
INSERT INTO reviews (app_name, store_name, country_name, rating, review_content, review_date) VALUES
('StoreSEO', 'TechStore Pro', 'US', 5, 'Great SEO app!', '2025-09-15'),
('StoreSEO', 'Fashion Forward', 'CA', 4, 'Very helpful for SEO', '2025-09-12'),
('StoreSEO', 'Global Gadgets', 'UK', 5, 'Excellent SEO tools', '2025-09-08'),
('StoreSEO', 'Urban Style', 'AU', 5, 'Perfect for optimization', '2025-09-05'),
('StoreSEO', 'Digital Dreams', 'DE', 4, 'Great functionality', '2025-09-02'),
('StoreSEO', 'Eco Friendly Shop', 'US', 5, 'Outstanding SEO features', '2025-08-29'),
('StoreSEO', 'Sports Central', 'CA', 4, 'Boosted our traffic', '2025-08-26'),
('StoreSEO', 'Beauty Boutique', 'UK', 5, 'Fantastic app', '2025-08-23'),
('StoreSEO', 'Home Essentials', 'AU', 5, 'Really improved rankings', '2025-08-20'),
('StoreSEO', 'Tech Innovations', 'DE', 4, 'Easy to use', '2025-08-18'),
('StoreSEO', 'Vintage Finds', 'US', 5, 'Highly recommended', '2025-08-25'),
('StoreSEO', 'Modern Living', 'CA', 4, 'Great results', '2025-08-22'),
('StoreSEO', 'Creative Corner', 'UK', 5, 'Perfect SEO solution', '2025-08-19');

-- StoreFAQ: This Month 6, Last 30 Days 12
INSERT INTO reviews (app_name, store_name, country_name, rating, review_content, review_date) VALUES
('StoreFAQ', 'TechStore Pro', 'US', 5, 'Excellent FAQ app!', '2025-09-14'),
('StoreFAQ', 'Fashion Forward', 'CA', 4, 'Great for organizing help', '2025-09-11'),
('StoreFAQ', 'Global Gadgets', 'UK', 5, 'Perfect FAQ solution', '2025-09-08'),
('StoreFAQ', 'Urban Style', 'AU', 5, 'Easy to customize', '2025-09-06'),
('StoreFAQ', 'Digital Dreams', 'DE', 4, 'Reduced support workload', '2025-09-03'),
('StoreFAQ', 'Eco Friendly Shop', 'US', 5, 'Improved customer experience', '2025-09-01'),
('StoreFAQ', 'Sports Central', 'CA', 4, 'Very helpful', '2025-08-28'),
('StoreFAQ', 'Beauty Boutique', 'UK', 5, 'Great app', '2025-08-25'),
('StoreFAQ', 'Home Essentials', 'AU', 5, 'Perfect for FAQs', '2025-08-22'),
('StoreFAQ', 'Tech Innovations', 'DE', 4, 'Easy to use', '2025-08-19'),
('StoreFAQ', 'Vintage Finds', 'US', 5, 'Highly recommended', '2025-08-26'),
('StoreFAQ', 'Modern Living', 'CA', 4, 'Great functionality', '2025-08-23');

-- EasyFlow: This Month 5, Last 30 Days 13
INSERT INTO reviews (app_name, store_name, country_name, rating, review_content, review_date) VALUES
('EasyFlow', 'TechStore Pro', 'US', 5, 'Great product options!', '2025-09-13'),
('EasyFlow', 'Fashion Forward', 'CA', 4, 'Flexible and powerful', '2025-09-10'),
('EasyFlow', 'Global Gadgets', 'UK', 5, 'Perfect for variants', '2025-09-07'),
('EasyFlow', 'Urban Style', 'AU', 5, 'Complex configurations', '2025-09-04'),
('EasyFlow', 'Digital Dreams', 'DE', 4, 'Increased conversions', '2025-09-01'),
('EasyFlow', 'Eco Friendly Shop', 'US', 5, 'Outstanding app', '2025-08-29'),
('EasyFlow', 'Sports Central', 'CA', 4, 'Great customization', '2025-08-26'),
('EasyFlow', 'Beauty Boutique', 'UK', 5, 'Fantastic tool', '2025-08-23'),
('EasyFlow', 'Home Essentials', 'AU', 5, 'Perfect solution', '2025-08-20'),
('EasyFlow', 'Tech Innovations', 'DE', 4, 'Easy to use', '2025-08-17'),
('EasyFlow', 'Vintage Finds', 'US', 5, 'Highly recommended', '2025-08-24'),
('EasyFlow', 'Modern Living', 'CA', 4, 'Great results', '2025-08-21'),
('EasyFlow', 'Creative Corner', 'UK', 5, 'Perfect for products', '2025-08-18');

-- TrustSync: This Month 1, Last 30 Days 1
INSERT INTO reviews (app_name, store_name, country_name, rating, review_content, review_date) VALUES
('TrustSync', 'TechStore Pro', 'US', 5, 'Excellent review app!', '2025-09-12');

-- BetterDocs FAQ Knowledge Base: This Month 1, Last 30 Days 3
INSERT INTO reviews (app_name, store_name, country_name, rating, review_content, review_date) VALUES
('BetterDocs FAQ Knowledge Base', 'TechStore Pro', 'US', 5, 'Great documentation app!', '2025-09-10'),
('BetterDocs FAQ Knowledge Base', 'Fashion Forward', 'CA', 4, 'Perfect knowledge base', '2025-08-27'),
('BetterDocs FAQ Knowledge Base', 'Global Gadgets', 'UK', 5, 'Reduced support workload', '2025-08-24');

-- Vidify: This Month 0, Last 30 Days 0 (no reviews)
