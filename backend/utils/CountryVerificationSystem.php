<?php
/**
 * Country Verification System
 * Helps systematically verify and correct country data for all stores
 */

require_once __DIR__ . '/DatabaseManager.php';

class CountryVerificationSystem {
    private $conn;
    
    public function __construct() {
        $dbManager = new DatabaseManager();
        $this->conn = $dbManager->getConnection();
    }
    
    /**
     * Generate verification report for all apps
     */
    public function generateVerificationReport() {
        echo "🔍 COUNTRY VERIFICATION REPORT\n";
        echo "============================\n\n";
        
        $apps = ['StoreSEO', 'StoreFAQ', 'EasyFlow', 'BetterDocs FAQ Knowledge Base', 'Smart SEO Schema Rich Snippets', 'SEO King'];
        
        foreach ($apps as $app) {
            $this->generateAppReport($app);
            echo "\n" . str_repeat("=", 80) . "\n\n";
        }
    }
    
    /**
     * Generate verification report for a specific app
     */
    public function generateAppReport($appName) {
        echo "📱 APP: $appName\n";
        echo str_repeat("-", 50) . "\n";
        
        $appSlug = $this->getAppSlug($appName);
        $reviewUrl = "https://apps.shopify.com/$appSlug/reviews";
        
        echo "🔗 Review Page: $reviewUrl\n\n";
        
        // Get recent stores for this app
        $stmt = $this->conn->prepare("
            SELECT store_name, country_name, review_date, review_content
            FROM reviews 
            WHERE app_name = ? 
            AND review_date >= DATE_SUB(NOW(), INTERVAL 90 DAY)
            ORDER BY review_date DESC
            LIMIT 20
        ");
        
        $stmt->execute([$appName]);
        $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($stores)) {
            echo "⚠️ No recent reviews found for $appName\n";
            return;
        }
        
        echo "📋 STORES TO VERIFY (Last 90 days):\n";
        echo "Please check each store on the actual Shopify review page\n\n";
        
        foreach ($stores as $i => $store) {
            echo ($i + 1) . ". Store: {$store['store_name']}\n";
            echo "   Current Country: {$store['country_name']}\n";
            echo "   Review Date: {$store['review_date']}\n";
            echo "   Content Preview: " . substr($store['review_content'], 0, 60) . "...\n";
            echo "   ✅ Verify on: $reviewUrl\n";
            echo "\n";
        }
        
        // Show country distribution for this app
        $this->showAppCountryStats($appName);
    }
    
    /**
     * Show country statistics for an app
     */
    private function showAppCountryStats($appName) {
        $stmt = $this->conn->prepare("
            SELECT country_name, COUNT(*) as count,
                   ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM reviews WHERE app_name = ?), 1) as percentage
            FROM reviews 
            WHERE app_name = ?
            GROUP BY country_name
            ORDER BY count DESC
        ");
        
        $stmt->execute([$appName, $appName]);
        $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "📊 Current Country Distribution:\n";
        foreach ($stats as $stat) {
            echo "   {$stat['country_name']}: {$stat['count']} ({$stat['percentage']}%)\n";
        }
    }
    
    /**
     * Create verification checklist
     */
    public function createVerificationChecklist() {
        echo "📝 COUNTRY VERIFICATION CHECKLIST\n";
        echo "=================================\n\n";
        
        echo "INSTRUCTIONS:\n";
        echo "1. Visit each Shopify review page listed below\n";
        echo "2. Find the store name in the reviews\n";
        echo "3. Note the country shown under the store name\n";
        echo "4. Use the correction command to fix any mismatches\n\n";
        
        // Get all unique stores from recent reviews
        $stmt = $this->conn->prepare("
            SELECT DISTINCT r.store_name, r.country_name, r.app_name,
                   MAX(r.review_date) as latest_review
            FROM reviews r
            WHERE r.review_date >= DATE_SUB(NOW(), INTERVAL 60 DAY)
            GROUP BY r.store_name, r.country_name, r.app_name
            ORDER BY latest_review DESC
            LIMIT 50
        ");
        
        $stmt->execute();
        $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "🎯 TOP PRIORITY STORES TO VERIFY:\n\n";
        
        foreach ($stores as $i => $store) {
            $appSlug = $this->getAppSlug($store['app_name']);
            echo ($i + 1) . ". STORE: {$store['store_name']}\n";
            echo "   Current: {$store['country_name']}\n";
            echo "   App: {$store['app_name']}\n";
            echo "   Check: https://apps.shopify.com/$appSlug/reviews\n";
            echo "   Fix: php ManualCountryCorrector.php add \"{$store['store_name']}\" \"REAL_COUNTRY\"\n";
            echo "\n";
        }
        
        echo "💡 CORRECTION EXAMPLES:\n";
        echo "php ManualCountryCorrector.php add \"Store Name\" \"United Kingdom\"\n";
        echo "php ManualCountryCorrector.php add \"Store Name\" \"Canada\"\n";
        echo "php ManualCountryCorrector.php add \"Store Name\" \"South Africa\"\n\n";
    }
    
    /**
     * Find suspicious country assignments
     */
    public function findSuspiciousAssignments() {
        echo "🚨 SUSPICIOUS COUNTRY ASSIGNMENTS\n";
        echo "================================\n\n";
        
        echo "These stores might have incorrect country assignments:\n\n";
        
        // Find stores with very common countries that might be wrong
        $stmt = $this->conn->prepare("
            SELECT store_name, country_name, app_name, review_date
            FROM reviews 
            WHERE country_name IN ('United States', 'United Kingdom', 'Canada', 'Australia')
            AND review_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ORDER BY review_date DESC
            LIMIT 30
        ");
        
        $stmt->execute();
        $suspicious = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($suspicious as $i => $store) {
            $appSlug = $this->getAppSlug($store['app_name']);
            echo ($i + 1) . ". {$store['store_name']} -> {$store['country_name']}\n";
            echo "   App: {$store['app_name']}\n";
            echo "   Date: {$store['review_date']}\n";
            echo "   Verify: https://apps.shopify.com/$appSlug/reviews\n\n";
        }
        
        echo "⚠️ Please manually verify these on the actual Shopify review pages!\n";
    }
    
    /**
     * Apply verified corrections
     */
    public function applyVerifiedCorrections() {
        echo "✅ APPLYING VERIFIED CORRECTIONS\n";
        echo "===============================\n\n";
        
        // Include the manual corrector
        require_once __DIR__ . '/ManualCountryCorrector.php';
        $corrector = new ManualCountryCorrector();
        
        return $corrector->applyManualCorrections();
    }
    
    /**
     * Get app slug from app name
     */
    private function getAppSlug($appName) {
        $slugs = [
            'StoreSEO' => 'storeseo',
            'StoreFAQ' => 'storefaq', 
            'EasyFlow' => 'easyflow',
            'BetterDocs FAQ Knowledge Base' => 'betterdocs-faq-knowledge-base',
            'Smart SEO Schema Rich Snippets' => 'smart-seo-schema-rich-snippets',
            'SEO King' => 'seo-king'
        ];
        
        return $slugs[$appName] ?? strtolower(str_replace(' ', '-', $appName));
    }
    
    /**
     * Reset all countries to Unknown for fresh verification
     */
    public function resetAllCountries() {
        echo "⚠️ RESET ALL COUNTRIES TO UNKNOWN\n";
        echo "=================================\n\n";
        
        echo "This will reset ALL country data to 'Unknown' for fresh verification.\n";
        echo "Are you sure? This cannot be undone! (y/N): ";
        
        $confirm = trim(fgets(STDIN));
        if (strtolower($confirm) !== 'y') {
            echo "Operation cancelled.\n";
            return;
        }
        
        $stmt = $this->conn->prepare("UPDATE reviews SET country_name = 'Unknown'");
        if ($stmt->execute()) {
            $updated = $stmt->rowCount();
            echo "✅ Reset $updated records to 'Unknown'\n";
            echo "Now you can systematically verify and correct each store.\n";
        } else {
            echo "❌ Failed to reset countries\n";
        }
    }
}

// CLI usage
if (php_sapi_name() === 'cli') {
    $verifier = new CountryVerificationSystem();
    
    if (isset($argv[1])) {
        switch ($argv[1]) {
            case 'report':
                $verifier->generateVerificationReport();
                break;
                
            case 'checklist':
                $verifier->createVerificationChecklist();
                break;
                
            case 'suspicious':
                $verifier->findSuspiciousAssignments();
                break;
                
            case 'apply':
                $verifier->applyVerifiedCorrections();
                break;
                
            case 'reset':
                $verifier->resetAllCountries();
                break;
                
            case 'app':
                if (isset($argv[2])) {
                    $verifier->generateAppReport($argv[2]);
                } else {
                    echo "Usage: php CountryVerificationSystem.php app <AppName>\n";
                }
                break;
                
            default:
                echo "Usage:\n";
                echo "  php CountryVerificationSystem.php report\n";
                echo "  php CountryVerificationSystem.php checklist\n";
                echo "  php CountryVerificationSystem.php suspicious\n";
                echo "  php CountryVerificationSystem.php apply\n";
                echo "  php CountryVerificationSystem.php app <AppName>\n";
                echo "  php CountryVerificationSystem.php reset\n";
        }
    } else {
        $verifier->createVerificationChecklist();
    }
}
?>
