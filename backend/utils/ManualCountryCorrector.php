<?php
/**
 * Manual Country Corrector
 * Allows manual input of real country data from Shopify review pages
 */

require_once __DIR__ . '/DatabaseManager.php';

class ManualCountryCorrector {
    private $conn;
    
    public function __construct() {
        $dbManager = new DatabaseManager();
        $this->conn = $dbManager->getConnection();
    }
    
    /**
     * Apply manual corrections based on real Shopify data
     */
    public function applyManualCorrections() {
        echo "🌍 APPLYING MANUAL COUNTRY CORRECTIONS\n";
        echo "Based on real Shopify review page data\n";
        echo "====================================\n\n";
        
        // Real country data from actual Shopify review pages
        // ONLY add entries here after manually verifying them on the actual Shopify pages
        $realCountryData = [
            // ✅ VERIFIED from screenshots provided by user
            'Whotex Online Fabric Store' => 'United Kingdom',
            'PrismaFitZone' => 'Canada',
            'AOBH' => 'South Africa',

            // 🔍 ADD MORE REAL DATA HERE AS YOU VERIFY THEM
            // Only add after checking the actual Shopify review pages
            // Format: 'Store Name' => 'Real Country from Shopify',

            // 📋 STORES NEEDING VERIFICATION (check these on actual Shopify pages):
            // 'Advantage Lifts' => 'CHECK: https://apps.shopify.com/storeseo/reviews',
            // 'Amelia Scott' => 'CHECK: https://apps.shopify.com/storeseo/reviews',
            // 'behnacsonlinestore' => 'CHECK: https://apps.shopify.com/storeseo/reviews',
            // 'Timeless Touch Creations' => 'CHECK: https://apps.shopify.com/storeseo/reviews',
            // 'Vape king dxb' => 'CHECK: https://apps.shopify.com/storeseo/reviews',
            // 'VitalityVangard' => 'CHECK: https://apps.shopify.com/storeseo/reviews',
            // 'RawSpiceBar' => 'CHECK: https://apps.shopify.com/storeseo/reviews',
            // 'Oshipt.com' => 'CHECK: https://apps.shopify.com/storeseo/reviews',
        ];
        
        $corrected = 0;
        $notFound = 0;
        
        foreach ($realCountryData as $storeName => $realCountry) {
            $result = $this->updateStoreCountry($storeName, $realCountry);
            if ($result > 0) {
                $corrected += $result;
                echo "✅ {$storeName} -> {$realCountry} ({$result} records updated)\n";
            } else {
                $notFound++;
                echo "⚠️ {$storeName} -> Not found in database\n";
            }
        }
        
        echo "\n🎯 Summary:\n";
        echo "   ✅ Corrected: {$corrected} records\n";
        echo "   ⚠️ Not found: {$notFound} stores\n";
        
        return $corrected;
    }
    
    /**
     * Update country for a specific store
     */
    private function updateStoreCountry($storeName, $realCountry) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE reviews 
                SET country_name = ? 
                WHERE store_name = ?
            ");
            
            $stmt->execute([$realCountry, $storeName]);
            return $stmt->rowCount();
            
        } catch (Exception $e) {
            echo "❌ Error updating {$storeName}: " . $e->getMessage() . "\n";
            return 0;
        }
    }
    
    /**
     * Get stores that might need manual correction
     */
    public function getStoresNeedingCorrection($limit = 50) {
        echo "📋 STORES THAT MIGHT NEED MANUAL CORRECTION\n";
        echo "Check these stores on the actual Shopify review pages\n";
        echo "=================================================\n\n";
        
        // Get recent reviews that might have incorrect countries
        $stmt = $this->conn->prepare("
            SELECT DISTINCT store_name, country_name, app_name, 
                   COUNT(*) as review_count,
                   MAX(review_date) as latest_review
            FROM reviews 
            WHERE review_date >= DATE_SUB(NOW(), INTERVAL 60 DAY)
            GROUP BY store_name, country_name, app_name
            ORDER BY latest_review DESC
            LIMIT ?
        ");
        
        $stmt->execute([$limit]);
        $stores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Recent stores (check these on Shopify review pages):\n\n";
        
        foreach ($stores as $store) {
            echo "Store: {$store['store_name']}\n";
            echo "Current Country: {$store['country_name']}\n";
            echo "App: {$store['app_name']}\n";
            echo "Reviews: {$store['review_count']}\n";
            echo "Latest: {$store['latest_review']}\n";
            echo "Check: https://apps.shopify.com/" . $this->getAppSlug($store['app_name']) . "/reviews\n";
            echo str_repeat("-", 50) . "\n\n";
        }
        
        return $stores;
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
     * Add a single correction
     */
    public function addCorrection($storeName, $realCountry) {
        $updated = $this->updateStoreCountry($storeName, $realCountry);
        if ($updated > 0) {
            echo "✅ Added correction: {$storeName} -> {$realCountry} ({$updated} records)\n";
            return true;
        } else {
            echo "❌ Failed to add correction for: {$storeName}\n";
            return false;
        }
    }
    
    /**
     * Validate country name
     */
    public function validateCountry($country) {
        $validCountries = [
            'United States', 'United Kingdom', 'Canada', 'Australia', 'Germany', 'France',
            'Netherlands', 'Sweden', 'Norway', 'Denmark', 'Finland', 'Belgium', 'Switzerland',
            'Austria', 'Ireland', 'Italy', 'Spain', 'Portugal', 'Poland', 'Czech Republic',
            'Slovakia', 'Hungary', 'Romania', 'Bulgaria', 'Greece', 'Turkey', 'Russia',
            'Ukraine', 'India', 'China', 'Japan', 'South Korea', 'Thailand', 'Vietnam',
            'Indonesia', 'Malaysia', 'Philippines', 'Singapore', 'New Zealand', 'South Africa',
            'Nigeria', 'Kenya', 'Egypt', 'Morocco', 'Brazil', 'Argentina', 'Chile', 'Colombia',
            'Peru', 'Venezuela', 'Mexico', 'Costa Rica', 'Panama', 'Guatemala', 'Honduras',
            'El Salvador', 'Nicaragua', 'Jamaica', 'Cuba', 'Dominican Republic', 'Haiti',
            'Trinidad and Tobago', 'Barbados', 'Bahamas', 'Puerto Rico'
        ];
        
        return in_array($country, $validCountries);
    }
    
    /**
     * Interactive correction mode
     */
    public function interactiveMode() {
        echo "🔧 INTERACTIVE COUNTRY CORRECTION MODE\n";
        echo "=====================================\n\n";
        
        while (true) {
            echo "Options:\n";
            echo "1. Add a correction (store_name,country)\n";
            echo "2. Show stores needing correction\n";
            echo "3. Apply all manual corrections\n";
            echo "4. Exit\n\n";
            
            echo "Enter choice (1-4): ";
            $choice = trim(fgets(STDIN));
            
            switch ($choice) {
                case '1':
                    echo "Enter store name: ";
                    $storeName = trim(fgets(STDIN));
                    echo "Enter real country: ";
                    $country = trim(fgets(STDIN));
                    
                    if ($this->validateCountry($country)) {
                        $this->addCorrection($storeName, $country);
                    } else {
                        echo "❌ Invalid country name\n";
                    }
                    break;
                    
                case '2':
                    $this->getStoresNeedingCorrection(20);
                    break;
                    
                case '3':
                    $this->applyManualCorrections();
                    break;
                    
                case '4':
                    echo "Goodbye!\n";
                    return;
                    
                default:
                    echo "Invalid choice\n";
            }
            
            echo "\n";
        }
    }
}

// CLI usage
if (php_sapi_name() === 'cli') {
    $corrector = new ManualCountryCorrector();
    
    if (isset($argv[1])) {
        switch ($argv[1]) {
            case 'apply':
                $corrector->applyManualCorrections();
                break;
                
            case 'list':
                $limit = isset($argv[2]) ? intval($argv[2]) : 50;
                $corrector->getStoresNeedingCorrection($limit);
                break;
                
            case 'add':
                if (isset($argv[2]) && isset($argv[3])) {
                    $corrector->addCorrection($argv[2], $argv[3]);
                } else {
                    echo "Usage: php ManualCountryCorrector.php add \"Store Name\" \"Country\"\n";
                }
                break;
                
            case 'interactive':
                $corrector->interactiveMode();
                break;
                
            default:
                echo "Usage:\n";
                echo "  php ManualCountryCorrector.php apply\n";
                echo "  php ManualCountryCorrector.php list [limit]\n";
                echo "  php ManualCountryCorrector.php add \"Store Name\" \"Country\"\n";
                echo "  php ManualCountryCorrector.php interactive\n";
        }
    } else {
        $corrector->applyManualCorrections();
    }
}
?>
