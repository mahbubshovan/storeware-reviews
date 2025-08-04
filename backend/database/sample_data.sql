-- Sample data for testing the Shopify Reviews application

USE shopify_reviews;

-- Insert sample reviews for testing
INSERT INTO reviews (store_name, country_name, rating, review_content, review_date) VALUES
('TechStore Pro', 'United States', 5, 'Amazing app! Really helped boost our sales and customer engagement. The interface is intuitive and the features are exactly what we needed.', '2024-01-15'),
('Fashion Forward', 'Canada', 4, 'Good app overall. Some features could be improved but it does what it promises. Customer support is responsive.', '2024-01-14'),
('Global Gadgets', 'United Kingdom', 5, 'Excellent functionality and great value for money. Would definitely recommend to other store owners.', '2024-01-13'),
('Urban Style', 'Australia', 3, 'Decent app but had some issues with setup. Once configured properly, it works fine.', '2024-01-12'),
('Digital Dreams', 'Germany', 5, 'Perfect solution for our needs. The analytics features are particularly useful for tracking performance.', '2024-01-11'),
('Eco Friendly Shop', 'Netherlands', 4, 'Very satisfied with this app. It integrates well with our existing workflow and saves us time.', '2024-01-10'),
('Sports Central', 'France', 2, 'Had some technical difficulties and the documentation could be better. Support helped resolve issues eventually.', '2024-01-09'),
('Beauty Boutique', 'Italy', 5, 'Outstanding app! Easy to use and has all the features we were looking for. Highly recommended.', '2024-01-08'),
('Home Essentials', 'Spain', 4, 'Good value and reliable performance. The user interface is clean and professional.', '2024-01-07'),
('Tech Innovations', 'Sweden', 5, 'Fantastic app that exceeded our expectations. Great ROI and excellent customer service.', '2024-01-06'),
('Vintage Finds', 'Norway', 3, 'Average experience. The app works but feels like it could use some modernization.', '2024-01-05'),
('Modern Living', 'Denmark', 4, 'Solid app with good features. Installation was straightforward and it integrates well.', '2024-01-04'),
('Creative Corner', 'Finland', 5, 'Love this app! It has transformed how we manage our store and interact with customers.', '2024-01-03'),
('Outdoor Adventures', 'Belgium', 4, 'Very useful app with good functionality. Some minor bugs but overall a positive experience.', '2024-01-02'),
('Luxury Lifestyle', 'Switzerland', 5, 'Premium quality app that delivers on its promises. Worth every penny and more.', '2024-01-01'),

-- Recent reviews for current month testing
('Fresh Market', 'United States', 5, 'Just started using this app and already seeing great results. The setup was quick and easy.', CURDATE()),
('Tech Hub', 'Canada', 4, 'Good app with useful features. The analytics dashboard is particularly helpful for our business.', DATE_SUB(CURDATE(), INTERVAL 1 DAY)),
('Style Studio', 'United Kingdom', 5, 'Excellent app that has improved our store performance significantly. Highly recommend!', DATE_SUB(CURDATE(), INTERVAL 2 DAY)),
('Digital Store', 'Australia', 3, 'Decent functionality but could use some improvements in the user interface design.', DATE_SUB(CURDATE(), INTERVAL 3 DAY)),
('Green Garden', 'Germany', 4, 'Very satisfied with the features and performance. Good value for the price point.', DATE_SUB(CURDATE(), INTERVAL 4 DAY));
