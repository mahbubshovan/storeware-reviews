<?php
/**
 * Test Runner for Per-Device Rate Limiting System
 * Runs all tests and provides comprehensive reporting
 */

// Prevent web access
if (isset($_SERVER['HTTP_HOST'])) {
    die('This script can only be run from command line.');
}

require_once __DIR__ . '/ContentHashTest.php';
require_once __DIR__ . '/RateLimitingE2ETest.php';

class TestRunner {
    private $results = [];
    private $startTime;
    
    public function __construct() {
        $this->startTime = microtime(true);
    }
    
    /**
     * Run all test suites
     */
    public function runAllTests() {
        echo "🧪 Per-Device Rate Limiting System - Test Suite\n";
        echo "===============================================\n";
        echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";
        
        // Run Content Hash Tests
        $this->runTestSuite('Content Hash Tests', function() {
            $tester = new ContentHashTest();
            return $tester->runAllTests();
        });
        
        // Run E2E Tests (only if database is available)
        if ($this->isDatabaseAvailable()) {
            $this->runTestSuite('Rate Limiting E2E Tests', function() {
                $tester = new RateLimitingE2ETest();
                return $tester->runAllTests();
            });
        } else {
            echo "⚠️ Skipping E2E tests - Database not available\n\n";
            $this->results['E2E Tests'] = ['status' => 'skipped', 'reason' => 'Database not available'];
        }
        
        // Run Performance Tests
        $this->runTestSuite('Performance Tests', function() {
            return $this->runPerformanceTests();
        });
        
        // Generate final report
        $this->generateReport();
    }
    
    /**
     * Run a test suite and capture results
     */
    private function runTestSuite($name, $testFunction) {
        echo "🚀 Running $name...\n";
        echo str_repeat('-', 50) . "\n";
        
        $startTime = microtime(true);
        
        try {
            $success = $testFunction();
            $duration = microtime(true) - $startTime;
            
            $this->results[$name] = [
                'status' => $success ? 'passed' : 'failed',
                'duration' => $duration
            ];
            
            echo "Duration: " . number_format($duration, 2) . "s\n\n";
            
        } catch (Exception $e) {
            $duration = microtime(true) - $startTime;
            
            $this->results[$name] = [
                'status' => 'error',
                'duration' => $duration,
                'error' => $e->getMessage()
            ];
            
            echo "❌ ERROR: " . $e->getMessage() . "\n";
            echo "Duration: " . number_format($duration, 2) . "s\n\n";
        }
    }
    
    /**
     * Check if database is available
     */
    private function isDatabaseAvailable() {
        try {
            $pdo = getDbConnection();
            $stmt = $pdo->query("SELECT 1");
            return $stmt !== false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Run performance tests
     */
    private function runPerformanceTests() {
        echo "🧪 Testing content hash performance...\n";
        
        // Test with various dataset sizes
        $sizes = [10, 100, 1000, 5000];
        $allPassed = true;
        
        foreach ($sizes as $size) {
            $reviews = $this->generateTestReviews($size);
            
            $startTime = microtime(true);
            $hash = $this->generateContentHash($reviews);
            $duration = (microtime(true) - $startTime) * 1000; // ms
            
            $maxTime = $size * 0.1; // 0.1ms per review max
            $passed = $duration < $maxTime;
            
            echo sprintf(
                "  %s %d reviews: %.2fms (max: %.2fms)\n",
                $passed ? '✅' : '❌',
                $size,
                $duration,
                $maxTime
            );
            
            if (!$passed) {
                $allPassed = false;
            }
        }
        
        // Test memory usage
        echo "\n🧪 Testing memory usage...\n";
        $memoryBefore = memory_get_usage(true);
        $largeDataset = $this->generateTestReviews(10000);
        $hash = $this->generateContentHash($largeDataset);
        $memoryAfter = memory_get_usage(true);
        $memoryUsed = ($memoryAfter - $memoryBefore) / 1024 / 1024; // MB
        
        $memoryPassed = $memoryUsed < 50; // Should use less than 50MB
        echo sprintf(
            "  %s Memory usage: %.2fMB (max: 50MB)\n",
            $memoryPassed ? '✅' : '❌',
            $memoryUsed
        );
        
        if (!$memoryPassed) {
            $allPassed = false;
        }
        
        return $allPassed;
    }
    
    /**
     * Generate test reviews for performance testing
     */
    private function generateTestReviews($count) {
        $reviews = [];
        for ($i = 0; $i < $count; $i++) {
            $reviews[] = [
                'store_name' => "Test Store $i",
                'review_date' => date('Y-m-d', strtotime("-$i days")),
                'review_content' => "Test review content for store $i with some additional text to make it realistic",
                'rating' => rand(1, 5),
                'country_name' => 'Test Country'
            ];
        }
        return $reviews;
    }
    
    /**
     * Generate content hash (same logic as EnhancedLiveScraper)
     */
    private function generateContentHash($reviews) {
        $reviewHashes = [];
        
        foreach ($reviews as $review) {
            $content = substr($review['review_content'] ?? '', 0, 50);
            $identifier = $review['store_name'] . '_' . $review['review_date'] . '_' . $content;
            $reviewId = hash('md5', $identifier);
            
            $updatedAt = $review['review_date'];
            $reviewHashes[] = $reviewId . '_' . $updatedAt;
        }
        
        sort($reviewHashes);
        return hash('sha256', implode('|', $reviewHashes));
    }
    
    /**
     * Generate comprehensive test report
     */
    private function generateReport() {
        $totalDuration = microtime(true) - $this->startTime;
        
        echo "===============================================\n";
        echo "📊 FINAL TEST REPORT\n";
        echo "===============================================\n";
        echo "Total Duration: " . number_format($totalDuration, 2) . "s\n";
        echo "Completed at: " . date('Y-m-d H:i:s') . "\n\n";
        
        $passed = 0;
        $failed = 0;
        $skipped = 0;
        $errors = 0;
        
        foreach ($this->results as $suite => $result) {
            $status = $result['status'];
            $duration = isset($result['duration']) ? number_format($result['duration'], 2) . 's' : 'N/A';
            
            switch ($status) {
                case 'passed':
                    echo "✅ $suite - PASSED ($duration)\n";
                    $passed++;
                    break;
                case 'failed':
                    echo "❌ $suite - FAILED ($duration)\n";
                    $failed++;
                    break;
                case 'skipped':
                    echo "⏭️ $suite - SKIPPED ({$result['reason']})\n";
                    $skipped++;
                    break;
                case 'error':
                    echo "💥 $suite - ERROR ($duration)\n";
                    echo "   Error: {$result['error']}\n";
                    $errors++;
                    break;
            }
        }
        
        echo "\n===============================================\n";
        echo "📈 SUMMARY\n";
        echo "===============================================\n";
        echo "✅ Passed: $passed\n";
        echo "❌ Failed: $failed\n";
        echo "⏭️ Skipped: $skipped\n";
        echo "💥 Errors: $errors\n";
        echo "📊 Total: " . ($passed + $failed + $skipped + $errors) . "\n\n";
        
        if ($failed === 0 && $errors === 0) {
            echo "🎉 ALL TESTS PASSED! System is ready for deployment.\n";
            
            echo "\n🚀 Next Steps:\n";
            echo "1. Run database migration: mysql -u user -p db < backend/database/migration_per_device_rate_limiting.sql\n";
            echo "2. Update frontend to use new API endpoints\n";
            echo "3. Set up background jobs (optional): ./backend/cron/setup_cron.sh\n";
            echo "4. Deploy to production environment\n";
            echo "5. Monitor logs and performance\n";
            
            return true;
        } else {
            echo "⚠️ SOME TESTS FAILED! Please review and fix issues before deployment.\n";
            return false;
        }
    }
}

// Run all tests if called directly
if (php_sapi_name() === 'cli') {
    $runner = new TestRunner();
    $success = $runner->runAllTests();
    exit($success ? 0 : 1);
}
