<?php
/**
 * Secure Environment Configuration Loader
 * Loads API keys and sensitive data from .env file
 */
class SecureConfig {
    private static $config = null;
    
    public static function load() {
        if (self::$config !== null) {
            return self::$config;
        }
        
        // Look for .env in project root directory
        $env_file = dirname(__FILE__) . '/../../.env';
        self::$config = [];
        
        // Load from .env file if exists
        if (file_exists($env_file)) {
            $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                // Skip comments
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                
                // Parse key=value pairs
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    // Remove quotes if present
                    if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                        $value = $matches[2];
                    }
                    
                    self::$config[$key] = $value;
                }
            }
        }
        
        // Fallback to environment variables
        $env_keys = [
            'GROK_API_KEY',
            'OPENROUTER_API_KEY',
            'SITE_URL',
            'SITE_NAME',
            'AI_DEFAULT_MODEL',
            'AI_FALLBACK_MODEL'
        ];
        
        foreach ($env_keys as $key) {
            if (empty(self::$config[$key]) && !empty(getenv($key))) {
                self::$config[$key] = getenv($key);
            }
        }
        
        return self::$config;
    }
    
    public static function get($key, $default = null) {
        $config = self::load();
        return isset($config[$key]) ? $config[$key] : $default;
    }
    
    public static function getGrokApiKey() {
        return self::get('GROK_API_KEY');
    }
    
    public static function getOpenRouterApiKey() {
        return self::get('OPENROUTER_API_KEY');
    }
    
    public static function getSiteUrl() {
        return self::get('SITE_URL', 'http://localhost');
    }
    
    public static function getSiteName() {
        return self::get('SITE_NAME', 'AI System');
    }
    
    public static function isConfigured() {
        return !empty(self::getGrokApiKey()) || !empty(self::getOpenRouterApiKey());
    }
    
    public static function maskKey($key) {
        if (empty($key) || strlen($key) < 8) {
            return '****';
        }
        return substr($key, 0, 4) . str_repeat('*', strlen($key) - 8) . substr($key, -4);
    }
}
?>