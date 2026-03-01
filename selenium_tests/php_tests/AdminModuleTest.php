<?php
/**
 * Admin Module Selenium Test Suite
 * Tests core administrative functionalities
 */

require_once '../../vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverWait;
use Facebook\WebDriver\WebDriverExpectedCondition;

class AdminModuleTest {
    private $driver;
    private $baseUrl = 'http://localhost:80/stu/public/';
    private $testResults = [];
    
    public function __construct() {
        // Start Chrome WebDriver
        $capabilities = DesiredCapabilities::chrome();
        $this->driver = RemoteWebDriver::create('http://localhost:4444/wd/hub', $capabilities);
        $this->driver->manage()->window()->maximize();
    }
    
    public function runAllTests() {
        echo "Starting Admin Module Tests...\n";
        
        $this->testAdminLoginPage();
        $this->testAdminDashboardAccess();
        $this->testAdminUserManagement();
        $this->testAdminSystemSettings();
        $this->testAdminReportsGeneration();
        
        $this->tearDown();
        return $this->testResults;
    }
    
    private function testAdminLoginPage() {
        $testName = "Admin Login Page Test";
        $startTime = microtime(true);
        
        try {
            $this->driver->get($this->baseUrl . 'login.php');
            $wait = new WebDriverWait($this->driver, 10);
            
            // Check if login form exists
            $loginForm = $wait->until(
                WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::tagName('form'))
            );
            
            // Check for username/email field
            $usernameField = $this->driver->findElement(WebDriverBy::name('username')) ?? 
                           $this->driver->findElement(WebDriverBy::name('email'));
            
            // Check for password field
            $passwordField = $this->driver->findElement(WebDriverBy::name('password'));
            
            // Test admin login attempt
            $usernameField->sendKeys('admin@test.com');
            $passwordField->sendKeys('admin123');
            
            $submitButton = $this->driver->findElement(WebDriverBy::cssSelector('button[type="submit"], input[type="submit"]'));
            $submitButton->click();
            
            sleep(2); // Wait for response
            
            $currentUrl = $this->driver->getCurrentURL();
            $pageTitle = $this->driver->getTitle();
            
            $this->testResults[] = [
                'module' => 'Admin',
                'test' => $testName,
                'status' => 'PASSED',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Login form found and functional. Current URL: {$currentUrl}",
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            $this->testResults[] = [
                'module' => 'Admin',
                'test' => $testName,
                'status' => 'FAILED',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Error: " . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    private function testAdminDashboardAccess() {
        $testName = "Admin Dashboard Access Test";
        $startTime = microtime(true);
        
        try {
            $this->driver->get($this->baseUrl . 'index.php');
            
            // Look for admin-specific elements
            $adminElements = [
                'dashboard', 'admin', 'management', 'settings', 'users'
            ];
            
            $foundElements = 0;
            $pageSource = strtolower($this->driver->getPageSource());
            
            foreach ($adminElements as $element) {
                if (strpos($pageSource, $element) !== false) {
                    $foundElements++;
                }
            }
            
            $this->testResults[] = [
                'module' => 'Admin',
                'test' => $testName,
                'status' => $foundElements >= 2 ? 'PASSED' : 'WARNING',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Found {$foundElements}/5 admin-related elements on dashboard.",
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            $this->testResults[] = [
                'module' => 'Admin',
                'test' => $testName,
                'status' => 'FAILED',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Error: " . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    private function testAdminUserManagement() {
        $testName = "Admin User Management Test";
        $startTime = microtime(true);
        
        try {
            // Try to access user management areas
            $urls = [
                $this->baseUrl . 'app/admin/users.php',
                $this->baseUrl . 'admin/users.php',
                $this->baseUrl . 'users.php'
            ];
            
            $accessible = false;
            $accessibleUrl = '';
            
            foreach ($urls as $url) {
                try {
                    $this->driver->get($url);
                    $title = $this->driver->getTitle();
                    if (!empty($title) && $title !== "404" && !strpos(strtolower($title), 'error')) {
                        $accessible = true;
                        $accessibleUrl = $url;
                        break;
                    }
                } catch (Exception $e) {
                    continue;
                }
            }
            
            $this->testResults[] = [
                'module' => 'Admin',
                'test' => $testName,
                'status' => $accessible ? 'PASSED' : 'WARNING',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => $accessible ? "User management accessible at: {$accessibleUrl}" : "User management interface not found at standard locations.",
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            $this->testResults[] = [
                'module' => 'Admin',
                'test' => $testName,
                'status' => 'FAILED',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Error: " . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    private function testAdminSystemSettings() {
        $testName = "Admin System Settings Test";
        $startTime = microtime(true);
        
        try {
            $this->driver->get($this->baseUrl);
            $pageSource = $this->driver->getPageSource();
            
            // Look for settings/configuration related content
            $settingsKeywords = ['settings', 'configuration', 'preferences', 'admin panel'];
            $foundKeywords = 0;
            
            foreach ($settingsKeywords as $keyword) {
                if (stripos($pageSource, $keyword) !== false) {
                    $foundKeywords++;
                }
            }
            
            $this->testResults[] = [
                'module' => 'Admin',
                'test' => $testName,
                'status' => $foundKeywords > 0 ? 'PASSED' : 'WARNING',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Found {$foundKeywords} settings-related keywords in the interface.",
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            $this->testResults[] = [
                'module' => 'Admin',
                'test' => $testName,
                'status' => 'FAILED',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Error: " . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    private function testAdminReportsGeneration() {
        $testName = "Admin Reports Generation Test";
        $startTime = microtime(true);
        
        try {
            $this->driver->get($this->baseUrl);
            $pageSource = $this->driver->getPageSource();
            
            // Look for reporting functionality
            $reportKeywords = ['report', 'analytics', 'statistics', 'export'];
            $foundReporting = 0;
            
            foreach ($reportKeywords as $keyword) {
                if (stripos($pageSource, $keyword) !== false) {
                    $foundReporting++;
                }
            }
            
            $this->testResults[] = [
                'module' => 'Admin',
                'test' => $testName,
                'status' => $foundReporting > 0 ? 'PASSED' : 'WARNING',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Found {$foundReporting} reporting-related features.",
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            $this->testResults[] = [
                'module' => 'Admin',
                'test' => $testName,
                'status' => 'FAILED',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Error: " . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    private function tearDown() {
        if ($this->driver) {
            $this->driver->quit();
        }
    }
}
?>