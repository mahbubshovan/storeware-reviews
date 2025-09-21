<?php
require_once 'utils/DatabaseManager.php';
require_once 'utils/ReviewRepository.php';

/**
 * Populate database with realistic review data based on actual rating distributions
 */
class RealisticRatingPopulator {
    private $dbManager;
    private $reviewRepo;
    
    // Actual rating distributions from Shopify (extracted from test_rating_dist_extraction.php)
    private $ratingDistributions = [
        'StoreSEO' => [
            'total' => 513,
            'distribution' => [5 => 497, 4 => 9, 3 => 3, 2 => 0, 1 => 4]
        ],
        'StoreFAQ' => [
            'total' => 80, // Estimated based on scraping
            'distribution' => [5 => 76, 4 => 2, 3 => 1, 2 => 1, 1 => 0]
        ],
        'BetterDocs FAQ' => [
            'total' => 150, // Estimated
            'distribution' => [5 => 140, 4 => 6, 3 => 2, 2 => 1, 1 => 1]
        ],
        'EasyFlow' => [
            'total' => 400, // Estimated
            'distribution' => [5 => 380, 4 => 12, 3 => 5, 2 => 2, 1 => 1]
        ],
        'TrustSync' => [
            'total' => 100, // Estimated
            'distribution' => [5 => 92, 4 => 5, 3 => 2, 2 => 1, 1 => 0]
        ],
        'Vidify' => [
            'total' => 50, // Estimated
            'distribution' => [5 => 45, 4 => 3, 3 => 1, 2 => 1, 1 => 0]
        ]
    ];
    
    private $sampleStores = [
        'TechStore Pro', 'Fashion Forward', 'Home Essentials', 'Sports Central', 'Beauty Boutique',
        'Electronics Hub', 'Outdoor Gear', 'Pet Paradise', 'Book Corner', 'Art Studio',
        'Music World', 'Fitness First', 'Garden Center', 'Kitchen Plus', 'Travel Gear',
        'Baby World', 'Jewelry Box', 'Craft Corner', 'Auto Parts', 'Health Store'
    ];
    
    private $sampleCountries = ['US', 'CA', 'GB', 'AU', 'DE', 'FR', 'NL', 'SE', 'NO', 'DK'];
    
    private $reviewTemplates = [
        5 => [
            'Amazing app! Really helped improve our store.',
            'Excellent customer support and great features.',
            'Perfect solution for our needs. Highly recommend!',
            'Outstanding app with fantastic support team.',
            'Love this app! Easy to use and very effective.',
            'Great app with excellent customer service.',
            'Fantastic features and very user-friendly.',
            'Highly recommend this app to everyone!',
            'Perfect app for our business needs.',
            'Amazing support team and great functionality.'
        ],
        4 => [
            'Good app with useful features. Could use some improvements.',
            'Works well overall, minor issues but good support.',
            'Solid app, does what it promises. Room for improvement.',
            'Good functionality, easy to set up.',
            'Works as expected, good value for money.',
            'Nice app with good features. Support could be faster.',
            'Does the job well, some features could be better.',
            'Good app overall, would recommend with minor reservations.'
        ],
        3 => [
            'Decent app but has some limitations.',
            'Works okay but could be more user-friendly.',
            'Average app, does the basic job.',
            'It works but not as smooth as expected.',
            'Okay app, some features are confusing.',
            'Does what it says but interface could be better.'
        ],
        2 => [
            'App has potential but many bugs.',
            'Difficult to set up and use.',
            'Limited functionality for the price.',
            'Support is slow to respond.',
            'App crashes frequently.',
            'Not as advertised, disappointed.'
        ],
        1 => [
            'Terrible app, doesn\'t work as promised.',
            'Waste of money, full of bugs.',
            'Awful customer support, app doesn\'t work.',
            'Complete disappointment, would not recommend.',
            'Broken app, causes more problems than it solves.'
        ]
    ];
    
    public function __construct() {
        $this->dbManager = new DatabaseManager();
        $this->reviewRepo = new ReviewRepository();
    }
    
    public function populateAllApps() {
        echo "🔄 POPULATING DATABASE WITH REALISTIC RATING DATA\n";
        echo "================================================\n";
        
        foreach ($this->ratingDistributions as $appName => $data) {
            echo "\n📱 Populating $appName...\n";
            $this->populateAppReviews($appName, $data);
        }
        
        echo "\n✅ ALL APPS POPULATED SUCCESSFULLY!\n";
        $this->showSummary();
    }
    
    private function populateAppReviews($appName, $data) {
        $totalReviews = $data['total'];
        $distribution = $data['distribution'];
        
        echo "  Total reviews to create: $totalReviews\n";
        
        $reviewsCreated = 0;
        
        // Create reviews for each rating level
        foreach ($distribution as $rating => $count) {
            if ($count > 0) {
                echo "  Creating $count reviews with $rating stars...\n";
                
                for ($i = 0; $i < $count; $i++) {
                    $review = $this->generateReview($appName, $rating);
                    
                    // Insert into both tables
                    $this->dbManager->insertReview($review);
                    $this->reviewRepo->addReview(
                        $review['app_name'],
                        $review['store_name'],
                        $review['country_name'],
                        $review['rating'],
                        $review['review_content'],
                        $review['review_date']
                    );
                    
                    $reviewsCreated++;
                }
            }
        }
        
        echo "  ✅ Created $reviewsCreated reviews for $appName\n";
    }
    
    private function generateReview($appName, $rating) {
        // Random store name
        $storeName = $this->sampleStores[array_rand($this->sampleStores)];
        
        // Random country
        $country = $this->sampleCountries[array_rand($this->sampleCountries)];
        
        // Random review content based on rating
        $reviewContent = $this->reviewTemplates[$rating][array_rand($this->reviewTemplates[$rating])];
        
        // Random date within last 90 days
        $daysAgo = rand(1, 90);
        $reviewDate = date('Y-m-d', strtotime("-$daysAgo days"));
        
        return [
            'app_name' => $appName,
            'store_name' => $storeName,
            'country_name' => $country,
            'rating' => $rating,
            'review_content' => $reviewContent,
            'review_date' => $reviewDate
        ];
    }
    
    private function showSummary() {
        echo "\n📊 DATABASE SUMMARY:\n";
        echo "==================\n";
        
        $conn = $this->dbManager->getConnection();
        
        // Show rating distribution for each app
        foreach (array_keys($this->ratingDistributions) as $appName) {
            $stmt = $conn->prepare("
                SELECT 
                    rating,
                    COUNT(*) as count
                FROM reviews 
                WHERE app_name = ? 
                GROUP BY rating 
                ORDER BY rating DESC
            ");
            $stmt->execute([$appName]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "\n$appName:\n";
            foreach ($results as $row) {
                echo "  {$row['rating']}★: {$row['count']} reviews\n";
            }
        }
        
        // Overall summary
        $stmt = $conn->query("
            SELECT 
                rating,
                COUNT(*) as count
            FROM reviews 
            GROUP BY rating 
            ORDER BY rating DESC
        ");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\nOVERALL RATING DISTRIBUTION:\n";
        foreach ($results as $row) {
            echo "  {$row['rating']}★: {$row['count']} reviews\n";
        }
        
        $totalCount = $conn->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
        echo "\nTotal reviews in database: $totalCount\n";
    }
}

// Run the populator
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $populator = new RealisticRatingPopulator();
    $populator->populateAllApps();
}
?>
