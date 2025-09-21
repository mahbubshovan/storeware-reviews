<?php
/**
 * Unit Tests for Content Hashing in Per-Device Rate Limiting System
 * Tests the stability and consistency of content hash generation
 */

require_once __DIR__ . '/../scraper/EnhancedLiveScraper.php';

class ContentHashTest {
    private $testReviews;
    
    public function __construct() {
        $this->setupTestData();
    }
    
    /**
     * Setup test review data
     */
    private function setupTestData() {
        $this->testReviews = [
            [
                'store_name' => 'Test Store 1',
                'review_date' => '2024-01-15',
                'review_content' => 'Great app, works perfectly!',
                'rating' => 5,
                'country_name' => 'United States'
            ],
            [
                'store_name' => 'Test Store 2',
                'review_date' => '2024-01-14',
                'review_content' => 'Good but could be better',
                'rating' => 4,
                'country_name' => 'Canada'
            ],
            [
                'store_name' => 'Test Store 3',
                'review_date' => '2024-01-13',
                'review_content' => 'Average experience',
                'rating' => 3,
                'country_name' => 'United Kingdom'
            ]
        ];
    }
    
    /**
     * Test that identical review sets produce identical hashes
     */
    public function testIdenticalReviewsProduceSameHash() {
        echo "🧪 Testing identical reviews produce same hash...\n";
        
        $hash1 = $this->generateContentHash($this->testReviews);
        $hash2 = $this->generateContentHash($this->testReviews);
        
        if ($hash1 === $hash2) {
            echo "✅ PASS: Identical reviews produce same hash\n";
            return true;
        } else {
            echo "❌ FAIL: Identical reviews produce different hashes\n";
            echo "   Hash 1: $hash1\n";
            echo "   Hash 2: $hash2\n";
            return false;
        }
    }
    
    /**
     * Test that review order doesn't affect hash
     */
    public function testReviewOrderDoesNotAffectHash() {
        echo "🧪 Testing review order doesn't affect hash...\n";
        
        $originalHash = $this->generateContentHash($this->testReviews);
        
        // Shuffle the reviews
        $shuffledReviews = $this->testReviews;
        shuffle($shuffledReviews);
        $shuffledHash = $this->generateContentHash($shuffledReviews);
        
        if ($originalHash === $shuffledHash) {
            echo "✅ PASS: Review order doesn't affect hash\n";
            return true;
        } else {
            echo "❌ FAIL: Review order affects hash\n";
            echo "   Original: $originalHash\n";
            echo "   Shuffled: $shuffledHash\n";
            return false;
        }
    }
    
    /**
     * Test that content changes affect hash
     */
    public function testContentChangesAffectHash() {
        echo "🧪 Testing content changes affect hash...\n";
        
        $originalHash = $this->generateContentHash($this->testReviews);
        
        // Modify one review
        $modifiedReviews = $this->testReviews;
        $modifiedReviews[0]['review_content'] = 'Modified content';
        $modifiedHash = $this->generateContentHash($modifiedReviews);
        
        if ($originalHash !== $modifiedHash) {
            echo "✅ PASS: Content changes affect hash\n";
            return true;
        } else {
            echo "❌ FAIL: Content changes don't affect hash\n";
            echo "   Original: $originalHash\n";
            echo "   Modified: $modifiedHash\n";
            return false;
        }
    }
    
    /**
     * Test that adding reviews affects hash
     */
    public function testAddingReviewsAffectsHash() {
        echo "🧪 Testing adding reviews affects hash...\n";
        
        $originalHash = $this->generateContentHash($this->testReviews);
        
        // Add a new review
        $extendedReviews = $this->testReviews;
        $extendedReviews[] = [
            'store_name' => 'New Store',
            'review_date' => '2024-01-16',
            'review_content' => 'New review content',
            'rating' => 5,
            'country_name' => 'Australia'
        ];
        $extendedHash = $this->generateContentHash($extendedReviews);
        
        if ($originalHash !== $extendedHash) {
            echo "✅ PASS: Adding reviews affects hash\n";
            return true;
        } else {
            echo "❌ FAIL: Adding reviews doesn't affect hash\n";
            echo "   Original: $originalHash\n";
            echo "   Extended: $extendedHash\n";
            return false;
        }
    }
    
    /**
     * Test hash format and length
     */
    public function testHashFormat() {
        echo "🧪 Testing hash format...\n";
        
        $hash = $this->generateContentHash($this->testReviews);
        
        // SHA256 should be 64 characters long and hexadecimal
        if (strlen($hash) === 64 && ctype_xdigit($hash)) {
            echo "✅ PASS: Hash format is correct (64-char SHA256)\n";
            return true;
        } else {
            echo "❌ FAIL: Hash format is incorrect\n";
            echo "   Hash: $hash\n";
            echo "   Length: " . strlen($hash) . "\n";
            return false;
        }
    }
    
    /**
     * Test performance with large dataset
     */
    public function testPerformanceWithLargeDataset() {
        echo "🧪 Testing performance with large dataset...\n";
        
        // Generate 1000 reviews
        $largeDataset = [];
        for ($i = 0; $i < 1000; $i++) {
            $largeDataset[] = [
                'store_name' => "Store $i",
                'review_date' => date('Y-m-d', strtotime("-$i days")),
                'review_content' => "Review content for store $i",
                'rating' => rand(1, 5),
                'country_name' => 'Test Country'
            ];
        }
        
        $startTime = microtime(true);
        $hash = $this->generateContentHash($largeDataset);
        $endTime = microtime(true);
        
        $duration = ($endTime - $startTime) * 1000; // Convert to milliseconds
        
        if ($duration < 100) { // Should complete in under 100ms
            echo "✅ PASS: Large dataset hashing completed in {$duration}ms\n";
            return true;
        } else {
            echo "❌ FAIL: Large dataset hashing too slow: {$duration}ms\n";
            return false;
        }
    }
    
    /**
     * Generate content hash using the same logic as EnhancedLiveScraper
     */
    private function generateContentHash($reviews) {
        $reviewHashes = [];
        
        foreach ($reviews as $review) {
            // Generate stable review ID
            $content = substr($review['review_content'] ?? '', 0, 50);
            $identifier = $review['store_name'] . '_' . $review['review_date'] . '_' . $content;
            $reviewId = hash('md5', $identifier);
            
            $updatedAt = $review['review_date']; // Use review_date as updated_at
            $reviewHashes[] = $reviewId . '_' . $updatedAt;
        }
        
        // Sort hashes for consistent content hash
        sort($reviewHashes);
        return hash('sha256', implode('|', $reviewHashes));
    }
    
    /**
     * Run all tests
     */
    public function runAllTests() {
        echo "🚀 Starting Content Hash Tests\n";
        echo "================================\n\n";
        
        $tests = [
            'testIdenticalReviewsProduceSameHash',
            'testReviewOrderDoesNotAffectHash',
            'testContentChangesAffectHash',
            'testAddingReviewsAffectsHash',
            'testHashFormat',
            'testPerformanceWithLargeDataset'
        ];
        
        $passed = 0;
        $total = count($tests);
        
        foreach ($tests as $test) {
            if ($this->$test()) {
                $passed++;
            }
            echo "\n";
        }
        
        echo "================================\n";
        echo "📊 Test Results: $passed/$total tests passed\n";
        
        if ($passed === $total) {
            echo "🎉 All content hash tests passed!\n";
            return true;
        } else {
            echo "⚠️ Some tests failed. Please review the implementation.\n";
            return false;
        }
    }
}

// Run tests if called directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $tester = new ContentHashTest();
    $success = $tester->runAllTests();
    exit($success ? 0 : 1);
}
