<?php
/**
 * End-to-End Tests for Per-Device Rate Limiting System
 * Tests the complete flow from client registration to rate limiting
 */

require_once __DIR__ . '/../config/database.php';

class RateLimitingE2ETest {
    private $pdo;
    private $baseUrl;
    private $testClientId;
    
    public function __construct() {
        $this->pdo = getDbConnection();
        $this->baseUrl = 'http://localhost'; // Adjust for your test environment
        $this->testClientId = $this->generateTestClientId();
    }
    
    /**
     * Generate a test client ID
     */
    private function generateTestClientId() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    
    /**
     * Clean up test data
     */
    private function cleanupTestData() {
        try {
            // Clean up test client data
            $stmt = $this->pdo->prepare("DELETE FROM clients WHERE client_id = ?");
            $stmt->execute([$this->testClientId]);
            
            $stmt = $this->pdo->prepare("DELETE FROM scrape_schedule WHERE client_id = ?");
            $stmt->execute([$this->testClientId]);
            
            $stmt = $this->pdo->prepare("DELETE FROM snapshot_pointer WHERE client_id = ?");
            $stmt->execute([$this->testClientId]);
            
            echo "🧹 Cleaned up test data for client: {$this->testClientId}\n";
        } catch (Exception $e) {
            echo "⚠️ Cleanup warning: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Make HTTP request to API
     */
    private function makeRequest($method, $endpoint, $data = null) {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 30,
        ]);
        
        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'status' => $httpCode,
            'body' => $response ? json_decode($response, true) : null
        ];
    }
    
    /**
     * Test first-time client registration and immediate scrape allowance
     */
    public function testFirstTimeClientAllowsImmediateScrape() {
        echo "🧪 Testing first-time client allows immediate scrape...\n";
        
        // First request should allow immediate scraping
        $response = $this->makeRequest('GET', "/api/reviews/storeseo?client_id={$this->testClientId}");
        
        if ($response['status'] !== 200) {
            echo "❌ FAIL: API request failed with status {$response['status']}\n";
            return false;
        }
        
        $data = $response['body'];
        if ($data['scrape']['allowed_now'] !== true) {
            echo "❌ FAIL: First-time client should allow immediate scrape\n";
            echo "   allowed_now: " . ($data['scrape']['allowed_now'] ? 'true' : 'false') . "\n";
            return false;
        }
        
        echo "✅ PASS: First-time client allows immediate scrape\n";
        return true;
    }
    
    /**
     * Test successful scrape trigger
     */
    public function testSuccessfulScrapeTrigger() {
        echo "🧪 Testing successful scrape trigger...\n";
        
        $response = $this->makeRequest('POST', '/api/scrape/storeseo/trigger', [
            'client_id' => $this->testClientId
        ]);
        
        if ($response['status'] !== 200) {
            echo "❌ FAIL: Scrape trigger failed with status {$response['status']}\n";
            if ($response['body']) {
                echo "   Error: " . ($response['body']['error'] ?? 'Unknown') . "\n";
            }
            return false;
        }
        
        $data = $response['body'];
        if (!$data['success']) {
            echo "❌ FAIL: Scrape trigger returned success=false\n";
            return false;
        }
        
        echo "✅ PASS: Scrape trigger successful\n";
        echo "   Scraped count: " . ($data['scraped_count'] ?? 0) . "\n";
        return true;
    }
    
    /**
     * Test rate limiting after successful scrape
     */
    public function testRateLimitingAfterScrape() {
        echo "🧪 Testing rate limiting after scrape...\n";
        
        // Check that next scrape is now rate limited
        $response = $this->makeRequest('GET', "/api/reviews/storeseo?client_id={$this->testClientId}");
        
        if ($response['status'] !== 200) {
            echo "❌ FAIL: API request failed with status {$response['status']}\n";
            return false;
        }
        
        $data = $response['body'];
        if ($data['scrape']['allowed_now'] !== false) {
            echo "❌ FAIL: Client should be rate limited after scrape\n";
            return false;
        }
        
        if ($data['scrape']['remaining_seconds'] <= 0) {
            echo "❌ FAIL: Should have remaining seconds > 0\n";
            return false;
        }
        
        echo "✅ PASS: Client is properly rate limited\n";
        echo "   Remaining seconds: " . $data['scrape']['remaining_seconds'] . "\n";
        return true;
    }
    
    /**
     * Test rate limited scrape trigger returns 429
     */
    public function testRateLimitedScrapeTriggerReturns429() {
        echo "🧪 Testing rate limited scrape trigger returns 429...\n";
        
        $response = $this->makeRequest('POST', '/api/scrape/storeseo/trigger', [
            'client_id' => $this->testClientId
        ]);
        
        if ($response['status'] !== 429) {
            echo "❌ FAIL: Expected 429 status, got {$response['status']}\n";
            return false;
        }
        
        $data = $response['body'];
        if (!isset($data['remaining_seconds']) || $data['remaining_seconds'] <= 0) {
            echo "❌ FAIL: Should return remaining_seconds > 0\n";
            return false;
        }
        
        echo "✅ PASS: Rate limited trigger returns 429\n";
        echo "   Remaining seconds: " . $data['remaining_seconds'] . "\n";
        return true;
    }
    
    /**
     * Test scrape status endpoint
     */
    public function testScrapeStatusEndpoint() {
        echo "🧪 Testing scrape status endpoint...\n";
        
        $response = $this->makeRequest('GET', "/api/scrape/storeseo/status?client_id={$this->testClientId}");
        
        if ($response['status'] !== 200) {
            echo "❌ FAIL: Status endpoint failed with status {$response['status']}\n";
            return false;
        }
        
        $data = $response['body'];
        if (!isset($data['schedule']) || !isset($data['last_snapshot'])) {
            echo "❌ FAIL: Status response missing required fields\n";
            return false;
        }
        
        echo "✅ PASS: Scrape status endpoint working\n";
        echo "   Status: " . ($data['status'] ?? 'unknown') . "\n";
        return true;
    }
    
    /**
     * Test client isolation (different clients have independent rate limits)
     */
    public function testClientIsolation() {
        echo "🧪 Testing client isolation...\n";
        
        $secondClientId = $this->generateTestClientId();
        
        // Second client should be allowed to scrape immediately
        $response = $this->makeRequest('GET', "/api/reviews/storeseo?client_id={$secondClientId}");
        
        if ($response['status'] !== 200) {
            echo "❌ FAIL: Second client API request failed\n";
            return false;
        }
        
        $data = $response['body'];
        if ($data['scrape']['allowed_now'] !== true) {
            echo "❌ FAIL: Second client should allow immediate scrape\n";
            return false;
        }
        
        // Clean up second client
        $stmt = $this->pdo->prepare("DELETE FROM clients WHERE client_id = ?");
        $stmt->execute([$secondClientId]);
        
        echo "✅ PASS: Clients have independent rate limits\n";
        return true;
    }
    
    /**
     * Test invalid client ID format
     */
    public function testInvalidClientIdFormat() {
        echo "🧪 Testing invalid client ID format...\n";
        
        $response = $this->makeRequest('GET', '/api/reviews/storeseo?client_id=invalid-uuid');
        
        if ($response['status'] !== 400) {
            echo "❌ FAIL: Expected 400 status for invalid UUID, got {$response['status']}\n";
            return false;
        }
        
        echo "✅ PASS: Invalid client ID format rejected\n";
        return true;
    }
    
    /**
     * Run all E2E tests
     */
    public function runAllTests() {
        echo "🚀 Starting Rate Limiting E2E Tests\n";
        echo "====================================\n";
        echo "Test Client ID: {$this->testClientId}\n\n";
        
        // Clean up any existing test data
        $this->cleanupTestData();
        
        $tests = [
            'testFirstTimeClientAllowsImmediateScrape',
            'testSuccessfulScrapeTrigger',
            'testRateLimitingAfterScrape',
            'testRateLimitedScrapeTriggerReturns429',
            'testScrapeStatusEndpoint',
            'testClientIsolation',
            'testInvalidClientIdFormat'
        ];
        
        $passed = 0;
        $total = count($tests);
        
        foreach ($tests as $test) {
            if ($this->$test()) {
                $passed++;
            }
            echo "\n";
        }
        
        // Clean up test data
        $this->cleanupTestData();
        
        echo "====================================\n";
        echo "📊 Test Results: $passed/$total tests passed\n";
        
        if ($passed === $total) {
            echo "🎉 All E2E tests passed!\n";
            return true;
        } else {
            echo "⚠️ Some tests failed. Please review the implementation.\n";
            return false;
        }
    }
}

// Run tests if called directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $tester = new RateLimitingE2ETest();
    $success = $tester->runAllTests();
    exit($success ? 0 : 1);
}
