<?php
/**
 * Fix Country Data API
 * Updates "Unknown" country entries with proper country information
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Get all reviews with "Unknown" country
    $stmt = $conn->prepare("
        SELECT id, app_name, store_name, country_name, review_date 
        FROM reviews 
        WHERE country_name = 'Unknown' AND is_active = TRUE
        ORDER BY review_date DESC
        LIMIT 50
    ");
    $stmt->execute();
    $unknownReviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $updated = 0;
    $countryMapping = [];

    // Manual mappings for specific stores (based on research or common patterns)
    $manualMappings = [
        'Rockin Cushions' => 'United States',
        'Printed Coffee Cup Sleeves' => 'United States',
        'The Real Cookiemix Network' => 'United States',
        'Olive Branch Farmhouse' => 'United States'
    ];

    // Common country mappings based on store name patterns
    $storeCountryPatterns = [
        // US patterns
        '/\b(LLC|Inc|Corp|USA|US|America)\b/i' => 'United States',
        '/\b(New York|California|Texas|Florida|Chicago|Miami|Seattle|Boston|Denver|Atlanta)\b/i' => 'United States',
        
        // UK patterns  
        '/\b(Ltd|Limited|UK|Britain|England|Scotland|Wales)\b/i' => 'United Kingdom',
        '/\b(London|Manchester|Birmingham|Liverpool|Edinburgh|Glasgow)\b/i' => 'United Kingdom',
        
        // Canada patterns
        '/\b(Canada|Canadian|Toronto|Vancouver|Montreal|Calgary|Ottawa)\b/i' => 'Canada',
        
        // Australia patterns
        '/\b(Australia|Australian|Sydney|Melbourne|Brisbane|Perth|Adelaide)\b/i' => 'Australia',
        
        // Germany patterns
        '/\b(Germany|German|Deutschland|Berlin|Munich|Hamburg|Frankfurt)\b/i' => 'Germany',
        
        // France patterns
        '/\b(France|French|Paris|Lyon|Marseille|Toulouse|Nice)\b/i' => 'France',
        
        // Netherlands patterns
        '/\b(Netherlands|Dutch|Holland|Amsterdam|Rotterdam|Utrecht)\b/i' => 'Netherlands',
        
        // India patterns
        '/\b(India|Indian|Mumbai|Delhi|Bangalore|Chennai|Kolkata|Hyderabad)\b/i' => 'India',
        
        // Brazil patterns
        '/\b(Brazil|Brazilian|Brasil|São Paulo|Rio de Janeiro|Brasília)\b/i' => 'Brazil',
        
        // Spain patterns
        '/\b(Spain|Spanish|España|Madrid|Barcelona|Valencia|Seville)\b/i' => 'Spain',
        
        // Italy patterns
        '/\b(Italy|Italian|Italia|Rome|Milan|Naples|Turin|Florence)\b/i' => 'Italy'
    ];
    
    foreach ($unknownReviews as $review) {
        $storeName = $review['store_name'];
        $inferredCountry = 'Unknown';

        // First check manual mappings
        if (isset($manualMappings[$storeName])) {
            $inferredCountry = $manualMappings[$storeName];
        } else {
            // Try to infer country from store name patterns
            foreach ($storeCountryPatterns as $pattern => $country) {
                if (preg_match($pattern, $storeName)) {
                    $inferredCountry = $country;
                    break;
                }
            }
        }
        
        // If we found a country, update the database
        if ($inferredCountry !== 'Unknown') {
            $updateStmt = $conn->prepare("
                UPDATE reviews 
                SET country_name = ? 
                WHERE id = ?
            ");
            
            if ($updateStmt->execute([$inferredCountry, $review['id']])) {
                $updated++;
                $countryMapping[$review['store_name']] = $inferredCountry;
            }
        }
    }
    
    // Get updated statistics
    $statsStmt = $conn->prepare("
        SELECT 
            country_name,
            COUNT(*) as count
        FROM reviews 
        WHERE is_active = TRUE
        GROUP BY country_name
        ORDER BY count DESC
    ");
    $statsStmt->execute();
    $countryStats = $statsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => "Updated $updated reviews with country information",
        'updated_count' => $updated,
        'total_unknown_remaining' => count($unknownReviews) - $updated,
        'country_mappings' => $countryMapping,
        'country_statistics' => $countryStats,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
