<?php
/**
 * Universal Platform Configuration
 * Works with ANY hosting platform: xCloud, Railway, cPanel, Heroku, DigitalOcean, AWS, etc.
 */

class PlatformConfig {
    
    /**
     * Detect the current hosting platform
     */
    public static function detectPlatform() {
        // Check environment variables for platform detection
        if (getenv('RAILWAY_ENVIRONMENT')) return 'Railway';
        if (getenv('HEROKU_APP_NAME')) return 'Heroku';
        if (getenv('VERCEL')) return 'Vercel';
        if (getenv('NETLIFY')) return 'Netlify';
        if (getenv('AWS_LAMBDA_FUNCTION_NAME')) return 'AWS Lambda';
        if (getenv('AZURE_FUNCTIONS_ENVIRONMENT')) return 'Azure Functions';
        
        // Check HTTP_HOST for platform-specific domains
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (strpos($host, 'xcloud') !== false) return 'xCloud';
        if (strpos($host, 'railway.app') !== false) return 'Railway';
        if (strpos($host, 'herokuapp.com') !== false) return 'Heroku';
        if (strpos($host, 'vercel.app') !== false) return 'Vercel';
        if (strpos($host, 'netlify.app') !== false) return 'Netlify';
        if (strpos($host, 'cpanel') !== false) return 'cPanel';
        if (strpos($host, 'digitalocean') !== false) return 'DigitalOcean';
        if (strpos($host, 'amazonaws.com') !== false) return 'AWS';
        if (strpos($host, 'azure') !== false) return 'Azure';
        if (strpos($host, 'godaddy') !== false) return 'GoDaddy';
        if (strpos($host, 'hostgator') !== false) return 'HostGator';
        if (strpos($host, 'bluehost') !== false) return 'Bluehost';
        if (strpos($host, 'siteground') !== false) return 'SiteGround';
        if (strpos($host, 'namecheap') !== false) return 'Namecheap';
        
        // Check for localhost
        if (in_array($host, ['localhost:8000', 'localhost:5173', '127.0.0.1:8000']) || 
            strpos($host, 'localhost') !== false) {
            return 'Local Development';
        }
        
        return 'Unknown Platform';
    }
    
    /**
     * Check if running on a live server
     */
    public static function isLiveServer() {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        return !in_array($host, ['localhost:8000', 'localhost:5173', '127.0.0.1:8000']) &&
               !strpos($host, 'localhost');
    }
    
    /**
     * Get the base URL for API calls
     */
    public static function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
        
        if (self::isLiveServer()) {
            return $protocol . '://' . $host;
        } else {
            return 'http://localhost:8000';
        }
    }
    
    /**
     * Get database environment variables based on platform
     */
    public static function getDatabaseConfig() {
        $platform = self::detectPlatform();
        
        // Platform-specific environment variable mappings
        $envMappings = [
            'Railway' => [
                'host' => ['MYSQL_HOST', 'DB_HOST'],
                'database' => ['MYSQL_DATABASE', 'DB_NAME'],
                'username' => ['MYSQL_USER', 'DB_USER'],
                'password' => ['MYSQL_PASSWORD', 'DB_PASS'],
                'port' => ['MYSQL_PORT', 'DB_PORT']
            ],
            'Heroku' => [
                'host' => ['CLEARDB_DATABASE_URL_HOST', 'DB_HOST', 'DATABASE_HOST'],
                'database' => ['CLEARDB_DATABASE_URL_DATABASE', 'DB_NAME', 'DATABASE_NAME'],
                'username' => ['CLEARDB_DATABASE_URL_USERNAME', 'DB_USER', 'DATABASE_USER'],
                'password' => ['CLEARDB_DATABASE_URL_PASSWORD', 'DB_PASS', 'DATABASE_PASSWORD'],
                'port' => ['CLEARDB_DATABASE_URL_PORT', 'DB_PORT', 'DATABASE_PORT']
            ],
            'xCloud' => [
                'host' => ['DB_HOST', 'DATABASE_HOST', 'MYSQL_HOST'],
                'database' => ['DB_NAME', 'DATABASE_NAME', 'MYSQL_DATABASE'],
                'username' => ['DB_USER', 'DATABASE_USER', 'MYSQL_USER'],
                'password' => ['DB_PASS', 'DATABASE_PASSWORD', 'MYSQL_PASSWORD'],
                'port' => ['DB_PORT', 'DATABASE_PORT', 'MYSQL_PORT']
            ],
            'cPanel' => [
                'host' => ['DB_HOST', 'DATABASE_HOST'],
                'database' => ['DB_NAME', 'DATABASE_NAME'],
                'username' => ['DB_USER', 'DATABASE_USER'],
                'password' => ['DB_PASS', 'DATABASE_PASSWORD'],
                'port' => ['DB_PORT', 'DATABASE_PORT']
            ]
        ];
        
        // Default mapping for unknown platforms
        $defaultMapping = [
            'host' => ['DB_HOST', 'DATABASE_HOST', 'MYSQL_HOST'],
            'database' => ['DB_NAME', 'DATABASE_NAME', 'MYSQL_DATABASE'],
            'username' => ['DB_USER', 'DATABASE_USER', 'MYSQL_USER'],
            'password' => ['DB_PASS', 'DATABASE_PASSWORD', 'MYSQL_PASSWORD'],
            'port' => ['DB_PORT', 'DATABASE_PORT', 'MYSQL_PORT']
        ];
        
        $mapping = $envMappings[$platform] ?? $defaultMapping;
        
        return [
            'platform' => $platform,
            'mapping' => $mapping,
            'is_live' => self::isLiveServer()
        ];
    }
    
    /**
     * Get environment value from multiple possible variable names
     */
    public static function getEnvValue($varNames, $envFile = [], $default = '') {
        foreach ($varNames as $varName) {
            // Check $_ENV superglobal
            if (isset($_ENV[$varName]) && $_ENV[$varName] !== '') {
                return $_ENV[$varName];
            }
            
            // Check getenv()
            $value = getenv($varName);
            if ($value !== false && $value !== '') {
                return $value;
            }
            
            // Check .env file
            if (isset($envFile[$varName]) && $envFile[$varName] !== '') {
                return $envFile[$varName];
            }
        }
        
        return $default;
    }
    
    /**
     * Get platform-specific configuration
     */
    public static function getPlatformInfo() {
        return [
            'platform' => self::detectPlatform(),
            'is_live_server' => self::isLiveServer(),
            'base_url' => self::getBaseUrl(),
            'host' => $_SERVER['HTTP_HOST'] ?? 'unknown',
            'protocol' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http',
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown'
        ];
    }
}
?>
