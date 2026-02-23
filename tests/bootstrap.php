<?php
/**
 * PHPUnit Bootstrap File
 * Loads necessary files and sets up test environment
 */

namespace Tests;

// Define application root
define('APP_ROOT', __DIR__ . '/..');
define('TEST_ROOT', __DIR__);

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load composer autoloader
require_once APP_ROOT . '/vendor/autoload.php';

// Load environment variables if exists
if (file_exists(APP_ROOT . '/.env')) {
    $env_file = file_get_contents(APP_ROOT . '/.env');
    $lines = explode("\n", $env_file);
    foreach ($lines as $line) {
        if (!empty($line) && strpos($line, '=') !== false && $line[0] !== '#') {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

// Set test database configuration
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'student_satisfaction_survey_test');
}

// Create test database connection helper
class TestDatabase {
    private static $connection = null;
    
    public static function getConnection() {
        if (self::$connection === null) {
            try {
                self::$connection = new \PDO(
                    'mysql:host=' . DB_HOST,
                    DB_USER,
                    DB_PASS,
                    [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
                    ]
                );
            } catch (\PDOException $e) {
                echo "Database connection failed: " . $e->getMessage();
                exit(1);
            }
        }
        return self::$connection;
    }
    
    public static function setupTestDatabase() {
        $pdo = self::getConnection();
        try {
            // Create test database
            $pdo->exec('DROP DATABASE IF EXISTS ' . DB_NAME);
            $pdo->exec('CREATE DATABASE ' . DB_NAME);
            
            // Use test database
            $pdo->exec('USE ' . DB_NAME);
            
            // Load schema
            $schemaPath = APP_ROOT . '/database/schema.sql';
            if (file_exists($schemaPath)) {
                $schema = file_get_contents($schemaPath);
                $pdo->exec($schema);
            }
            
            echo "✓ Test database setup complete\n";
        } catch (\PDOException $e) {
            echo "Test database setup failed: " . $e->getMessage() . "\n";
        }
    }
    
    public static function truncateAllTables() {
        $pdo = self::getConnection();
        $pdo->exec('USE ' . DB_NAME);
        
        // Get all tables
        $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
        
        // Disable foreign key checks
        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
        
        // Truncate each table
        foreach ($tables as $table) {
            $pdo->exec('TRUNCATE TABLE ' . $table);
        }
        
        // Re-enable foreign key checks
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
    }
}

// Setup test database
if (php_sapi_name() === 'cli') {
    TestDatabase::setupTestDatabase();
}

TestDatabase::setupTestDatabase();
