<?php
/**
 * Bulk Country Corrector
 * Applies real country data corrections in bulk based on actual Shopify review pages
 */

require_once __DIR__ . '/DatabaseManager.php';

class BulkCountryCorrector {
    private $conn;
    
    public function __construct() {
        $dbManager = new DatabaseManager();
        $this->conn = $dbManager->getConnection();
    }
    
    /**
     * Apply all verified real country corrections
     */
    public function applyRealCountryData() {
        echo "🌍 APPLYING REAL COUNTRY DATA FROM SHOPIFY PAGES\n";
        echo "==============================================\n\n";
        
        // REAL country data verified from actual Shopify review pages
        // Add more entries here as you verify them from screenshots/actual pages
        $realCountryMappings = [
            // ✅ VERIFIED from user screenshots
            'Whotex Online Fabric Store' => 'United Kingdom',
            'PrismaFitZone' => 'Canada',
            'AOBH' => 'South Africa',
            'Puff Dady VAPE SHOP' => 'United Arab Emirates',
            'Vape king dxb' => 'United Arab Emirates', // dxb = Dubai
            
            // 🔍 ADD MORE REAL DATA HERE AS YOU VERIFY THEM
            // Only add after seeing the actual country on Shopify review pages
            
            // Common patterns that can be inferred with high confidence:
            'LEDSone UK Ltd' => 'United Kingdom', // "UK Ltd" clearly indicates UK
            
            // 📋 STORES NEEDING VERIFICATION - check these on actual Shopify pages:
            // When you check these stores on the real Shopify pages, add them above
            // 'Advantage Lifts' => 'VERIFY_ON_SHOPIFY',
            // 'Amelia Scott' => 'VERIFY_ON_SHOPIFY',
            // 'behnacsonlinestore' => 'VERIFY_ON_SHOPIFY',
            // 'Timeless Touch Creations' => 'VERIFY_ON_SHOPIFY',
            // 'VitalityVangard' => 'VERIFY_ON_SHOPIFY',
            // 'RawSpiceBar' => 'VERIFY_ON_SHOPIFY',
            // 'Oshipt.com' => 'VERIFY_ON_SHOPIFY',
        ];
        
        $totalCorrected = 0;
        $notFound = 0;
        
        foreach ($realCountryMappings as $storeName => $realCountry) {
            if ($realCountry === 'VERIFY_ON_SHOPIFY') {
                continue; // Skip entries that need verification
            }
            
            $corrected = $this->updateStoreCountry($storeName, $realCountry);
            if ($corrected > 0) {
                $totalCorrected += $corrected;
                echo "✅ {$storeName} -> {$realCountry} ({$corrected} records)\n";
            } else {
                $notFound++;
                echo "⚠️ {$storeName} -> Not found in database\n";
            }
        }
        
        echo "\n🎯 SUMMARY:\n";
        echo "   ✅ Total corrected: {$totalCorrected} records\n";
        echo "   ⚠️ Not found: {$notFound} stores\n\n";
        
        // Show current country distribution
        $this->showCountryDistribution();
        
        return $totalCorrected;
    }
    
    /**
     * Generate list of stores that need verification
     */
    public function generateVerificationList() {
        echo "📋 STORES NEEDING COUNTRY VERIFICATION\n";
        echo "====================================\n\n";
        
        echo "Please check these stores on the actual Shopify review pages:\n\n";
        
        // Get stores from recent reviews that might have incorrect countries
        $stmt = $this->conn->prepare("
            SELECT DISTINCT store_name, country_name, app_name, 
                   COUNT(*) as review_count,
                   MAX(review_date) as latest_review
            FROM reviews 
            WHERE review_date >= DATE_SUB(NOW(), INTERVAL 90 DAY)
            AND country_name IN ('United States', 'United Kingdom', 'Canada', 'Australia', 'Germany', 'France')
            GROUP BY store_name, country_name, app_name
            ORDER BY latest_review DESC
            LIMIT 100
        ");
        
        $stmt->execute();
        $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $appSlugs = [
            'StoreSEO' => 'storeseo',
            'StoreFAQ' => 'storefaq',
            'EasyFlow' => 'easyflow',
            'BetterDocs FAQ Knowledge Base' => 'betterdocs-faq-knowledge-base',
            'Smart SEO Schema Rich Snippets' => 'smart-seo-schema-rich-snippets',
            'SEO King' => 'seo-king'
        ];
        
        foreach ($stores as $i => $store) {
            $appSlug = $appSlugs[$store['app_name']] ?? 'unknown';
            
            echo ($i + 1) . ". STORE: {$store['store_name']}\n";
            echo "   Current: {$store['country_name']}\n";
            echo "   App: {$store['app_name']}\n";
            echo "   Reviews: {$store['review_count']}\n";
            echo "   Latest: {$store['latest_review']}\n";
            echo "   🔗 Check: https://apps.shopify.com/{$appSlug}/reviews\n";
            echo "   📝 Add to BulkCountryCorrector.php: '{$store['store_name']}' => 'REAL_COUNTRY',\n";
            echo "\n";
        }
        
        echo "💡 INSTRUCTIONS:\n";
        echo "1. Visit each Shopify review page above\n";
        echo "2. Find the store name in the reviews\n";
        echo "3. Note the REAL country shown under the store name\n";
        echo "4. Add the real country to BulkCountryCorrector.php\n";
        echo "5. Run: php BulkCountryCorrector.php apply\n\n";
    }
    
    /**
     * Update country for a specific store
     */
    private function updateStoreCountry($storeName, $realCountry) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE reviews 
                SET country_name = ? 
                WHERE store_name = ? AND country_name != ?
            ");
            
            $stmt->execute([$realCountry, $storeName, $realCountry]);
            return $stmt->rowCount();
            
        } catch (Exception $e) {
            echo "❌ Error updating {$storeName}: " . $e->getMessage() . "\n";
            return 0;
        }
    }
    
    /**
     * Show current country distribution
     */
    private function showCountryDistribution() {
        echo "📊 CURRENT COUNTRY DISTRIBUTION:\n";
        echo "===============================\n";
        
        $stmt = $this->conn->prepare("
            SELECT country_name, COUNT(*) as count,
                   ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM reviews), 1) as percentage
            FROM reviews 
            GROUP BY country_name
            ORDER BY count DESC
            LIMIT 20
        ");
        
        $stmt->execute();
        $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($stats as $stat) {
            $flag = $this->getCountryFlag($stat['country_name']);
            echo "   {$flag} {$stat['country_name']}: {$stat['count']} ({$stat['percentage']}%)\n";
        }
        
        echo "\n";
    }
    
    /**
     * Get country flag emoji
     */
    private function getCountryFlag($country) {
        $flags = [
            'United States' => '🇺🇸',
            'United Kingdom' => '🇬🇧',
            'Canada' => '🇨🇦',
            'Australia' => '🇦🇺',
            'Germany' => '🇩🇪',
            'France' => '🇫🇷',
            'Netherlands' => '🇳🇱',
            'Sweden' => '🇸🇪',
            'Norway' => '🇳🇴',
            'Denmark' => '🇩🇰',
            'Finland' => '🇫🇮',
            'Belgium' => '🇧🇪',
            'Switzerland' => '🇨🇭',
            'Austria' => '🇦🇹',
            'Ireland' => '🇮🇪',
            'Italy' => '🇮🇹',
            'Spain' => '🇪🇸',
            'Portugal' => '🇵🇹',
            'Poland' => '🇵🇱',
            'India' => '🇮🇳',
            'Japan' => '🇯🇵',
            'Brazil' => '🇧🇷',
            'Mexico' => '🇲🇽',
            'South Africa' => '🇿🇦',
            'New Zealand' => '🇳🇿',
            'Singapore' => '🇸🇬',
            'United Arab Emirates' => '🇦🇪'
        ];
        
        return $flags[$country] ?? '🌍';
    }
    
    /**
     * Find suspicious assignments that likely need correction
     */
    public function findSuspiciousAssignments() {
        echo "🚨 SUSPICIOUS COUNTRY ASSIGNMENTS\n";
        echo "================================\n\n";
        
        echo "These stores have suspicious country assignments that should be verified:\n\n";
        
        // Find stores with names that suggest different countries
        $suspiciousPatterns = [
            'UK' => 'United Kingdom',
            'Ltd' => 'United Kingdom', 
            'Limited' => 'United Kingdom',
            'LLC' => 'United States',
            'Inc' => 'United States',
            'Corp' => 'United States',
            'GmbH' => 'Germany',
            'Pty' => 'Australia',
            'dxb' => 'United Arab Emirates', // Dubai
            'UAE' => 'United Arab Emirates'
        ];
        
        foreach ($suspiciousPatterns as $pattern => $expectedCountry) {
            $stmt = $this->conn->prepare("
                SELECT store_name, country_name, app_name
                FROM reviews 
                WHERE store_name LIKE ? AND country_name != ?
                GROUP BY store_name, country_name, app_name
                LIMIT 10
            ");
            
            $stmt->execute(["%{$pattern}%", $expectedCountry]);
            $suspicious = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($suspicious)) {
                echo "🔍 Stores with '{$pattern}' pattern (should likely be {$expectedCountry}):\n";
                foreach ($suspicious as $store) {
                    echo "   ⚠️ {$store['store_name']} -> Currently: {$store['country_name']}\n";
                }
                echo "\n";
            }
        }
    }
    
    /**
     * Reset specific countries for re-verification
     */
    public function resetSuspiciousCountries() {
        echo "⚠️ RESET SUSPICIOUS COUNTRIES FOR RE-VERIFICATION\n";
        echo "================================================\n\n";
        
        echo "This will reset countries that were likely assigned incorrectly.\n";
        echo "Are you sure? (y/N): ";
        
        $confirm = trim(fgets(STDIN));
        if (strtolower($confirm) !== 'y') {
            echo "Operation cancelled.\n";
            return;
        }
        
        // Reset countries that were likely assigned by the statistical method
        $stmt = $this->conn->prepare("
            UPDATE reviews 
            SET country_name = 'Unknown' 
            WHERE country_name IN ('United States', 'United Kingdom', 'Canada', 'Australia', 'Germany', 'France', 'Brazil', 'India', 'Netherlands')
            AND store_name NOT IN (
                'Whotex Online Fabric Store', 'PrismaFitZone', 'AOBH', 'Puff Dady VAPE SHOP', 'Vape king dxb', 'LEDSone UK Ltd'
            )
        ");
        
        if ($stmt->execute()) {
            $reset = $stmt->rowCount();
            echo "✅ Reset {$reset} suspicious country assignments to 'Unknown'\n";
            echo "Now you can systematically verify and correct each store.\n";
        } else {
            echo "❌ Failed to reset countries\n";
        }
    }
}

// CLI usage
if (php_sapi_name() === 'cli') {
    $corrector = new BulkCountryCorrector();
    
    if (isset($argv[1])) {
        switch ($argv[1]) {
            case 'apply':
                $corrector->applyRealCountryData();
                break;
                
            case 'list':
                $corrector->generateVerificationList();
                break;
                
            case 'suspicious':
                $corrector->findSuspiciousAssignments();
                break;
                
            case 'reset':
                $corrector->resetSuspiciousCountries();
                break;
                
            default:
                echo "Usage:\n";
                echo "  php BulkCountryCorrector.php apply      - Apply real country corrections\n";
                echo "  php BulkCountryCorrector.php list       - List stores needing verification\n";
                echo "  php BulkCountryCorrector.php suspicious - Find suspicious assignments\n";
                echo "  php BulkCountryCorrector.php reset      - Reset suspicious countries\n";
        }
    } else {
        $corrector->applyRealCountryData();
    }
}
?>
