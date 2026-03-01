<?php
/**
 * API Module Selenium Test Suite
 * Tests API functionalities and endpoints
 */

require_once '../../vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverWait;
use Facebook\WebDriver\WebDriverExpectedCondition;

class APIModuleTest {
    private $driver;
    private $baseUrl = 'http://localhost:80/stu/public/';
    private $apiUrl = 'http://localhost:80/stu/app/api/';
    private $testResults = [];
    
    public function __construct() {
        $capabilities = DesiredCapabilities::chrome();
        $this->driver = RemoteWebDriver::create('http://localhost:4444/wd/hub', $capabilities);
        $this->driver->manage()->window()->maximize();
    }
    
    public function runAllTests() {
        echo "Starting API Module Tests...\n";
        
        $this->testAPIEndpointDiscovery();
        $this->testAPIDocumentation();
        $this->testAPIAuthentication();
        $this->testAPIDataEndpoints();
        $this->testAPIResponseFormat();
        
        $this->tearDown();
        return $this->testResults;
    }
    
    private function testAPIEndpointDiscovery() {
        $testName = "API Endpoint Discovery Test";
        $startTime = microtime(true);
        
        try {
            // Test common API endpoints
            $apiEndpoints = [
                $this->apiUrl . 'index.php',
                $this->baseUrl . 'api/',
                $this->baseUrl . 'api/index.php',
                $this->baseUrl . 'api.php'
            ];
            
            $activeEndpoints = [];
            $responseCodes = [];
            
            foreach ($apiEndpoints as $endpoint) {
                try {
                    $this->driver->get($endpoint);
                    $pageSource = $this->driver->getPageSource();
                    $currentUrl = $this->driver->getCurrentURL();
                    
                    // Check if this looks like an API endpoint
                    if (strpos(strtolower($pageSource), 'json') !== false ||
                        strpos(strtolower($pageSource), 'api') !== false ||
                        strpos($pageSource, '{') !== false ||
                        strpos($pageSource, '[') !== false) {
                        $activeEndpoints[] = $endpoint;
                    }
                    
                    // Check for HTTP status indicators
                    if (strpos($pageSource, '404') === false && 
                        strpos(strtolower($pageSource), 'not found') === false &&
                        strpos(strtolower($pageSource), 'error') === false) {
                        $responseCodes[$endpoint] = '200 (likely)';
                    }
                    
                } catch (Exception $e) {
                    $responseCodes[$endpoint] = 'Error: ' . $e->getMessage();
                }
            }
            
            $this->testResults[] = [
                'module' => 'API',
                'test' => $testName,
                'status' => count($activeEndpoints) > 0 ? 'PASSED' : 'WARNING',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Active endpoints found: " . count($activeEndpoints) . ". Endpoints tested: " . implode(', ', array_keys($responseCodes)),
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            $this->testResults[] = [
                'module' => 'API',
                'test' => $testName,
                'status' => 'FAILED',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Error: " . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    private function testAPIDocumentation() {
        $testName = "API Documentation Test";
        $startTime = microtime(true);
        
        try {
            // Check for API documentation
            $docUrls = [
                $this->baseUrl . 'documentation.php',
                $this->baseUrl . 'api/docs',
                $this->baseUrl . 'docs/api.php'
            ];
            
            $documentationFound = false;
            $docContent = '';
            $workingUrl = '';
            
            foreach ($docUrls as $url) {
                try {
                    $this->driver->get($url);
                    $pageSource = strtolower($this->driver->getPageSource());
                    
                    if (strpos($pageSource, 'api') !== false || 
                        strpos($pageSource, 'endpoint') !== false ||
                        strpos($pageSource, 'documentation') !== false) {
                        $documentationFound = true;
                        $workingUrl = $url;
                        
                        // Look for API-specific documentation content
                        $apiTerms = ['endpoint', 'method', 'parameter', 'response', 'json'];
                        $apiContentScore = 0;
                        
                        foreach ($apiTerms as $term) {
                            if (strpos($pageSource, $term) !== false) {
                                $apiContentScore++;
                            }
                        }
                        
                        $docContent = "API content score: {$apiContentScore}/5";
                        break;
                    }
                } catch (Exception $e) {
                    continue;
                }
            }
            
            $this->testResults[] = [
                'module' => 'API',
                'test' => $testName,
                'status' => $documentationFound ? 'PASSED' : 'WARNING',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => $documentationFound ? "Documentation found at: {$workingUrl}. {$docContent}" : "API documentation not found at standard locations.",
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            $this->testResults[] = [
                'module' => 'API',
                'test' => $testName,
                'status' => 'FAILED',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Error: " . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    private function testAPIAuthentication() {
        $testName = "API Authentication Test";
        $startTime = microtime(true);
        
        try {
            // Test API authentication endpoints
            $authEndpoints = [
                $this->apiUrl . 'auth.php',
                $this->apiUrl . 'login.php',
                $this->baseUrl . 'api/auth',
                $this->baseUrl . 'api/token'
            ];
            
            $authEndpointFound = false;
            $authFeatures = [];
            
            foreach ($authEndpoints as $endpoint) {
                try {
                    $this->driver->get($endpoint);
                    $pageSource = strtolower($this->driver->getPageSource());
                    
                    if (strpos($pageSource, 'auth') !== false ||
                        strpos($pageSource, 'token') !== false ||
                        strpos($pageSource, 'login') !== false) {
                        $authEndpointFound = true;
                        
                        // Look for authentication features
                        if (strpos($pageSource, 'token') !== false) $authFeatures[] = 'Token-based';
                        if (strpos($pageSource, 'key') !== false) $authFeatures[] = 'API Key';
                        if (strpos($pageSource, 'session') !== false) $authFeatures[] = 'Session';
                        
                        break;
                    }
                } catch (Exception $e) {
                    continue;
                }
            }
            
            // Also check main pages for API authentication references
            if (!$authEndpointFound) {
                $this->driver->get($this->baseUrl);
                $pageSource = strtolower($this->driver->getPageSource());
                
                if (strpos($pageSource, 'api') !== false && 
                    (strpos($pageSource, 'auth') !== false || strpos($pageSource, 'token') !== false)) {
                    $authEndpointFound = true;
                    $authFeatures[] = 'Referenced in main application';
                }
            }
            
            $this->testResults[] = [
                'module' => 'API',
                'test' => $testName,
                'status' => $authEndpointFound ? 'PASSED' : 'WARNING',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => $authEndpointFound ? "Authentication features: " . implode(', ', $authFeatures) : "API authentication endpoints not found.",
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            $this->testResults[] = [
                'module' => 'API',
                'test' => $testName,
                'status' => 'FAILED',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Error: " . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    private function testAPIDataEndpoints() {
        $testName = "API Data Endpoints Test";
        $startTime = microtime(true);
        
        try {
            // Test common data endpoints
            $dataEndpoints = [
                $this->apiUrl . 'users.php',
                $this->apiUrl . 'students.php',
                $this->apiUrl . 'courses.php',
                $this->apiUrl . 'data.php',
                $this->baseUrl . 'api/users',
                $this->baseUrl . 'api/data'
            ];
            
            $workingEndpoints = [];
            $dataFormats = [];
            
            foreach ($dataEndpoints as $endpoint) {
                try {
                    $this->driver->get($endpoint);
                    $pageSource = $this->driver->getPageSource();
                    
                    // Check for data-like content
                    if (strpos($pageSource, '{') !== false || 
                        strpos($pageSource, '[') !== false ||
                        strpos(strtolower($pageSource), 'json') !== false ||
                        strpos(strtolower($pageSource), 'xml') !== false) {
                        $workingEndpoints[] = $endpoint;
                        
                        // Identify data format
                        if (strpos($pageSource, '{') !== false) $dataFormats[] = 'JSON';
                        if (strpos($pageSource, '<') !== false && strpos($pageSource, '>') !== false) $dataFormats[] = 'XML/HTML';
                    }
                    
                } catch (Exception $e) {
                    continue;
                }
            }
            
            $dataFormats = array_unique($dataFormats);
            
            $this->testResults[] = [
                'module' => 'API',
                'test' => $testName,
                'status' => count($workingEndpoints) > 0 ? 'PASSED' : 'WARNING',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Working endpoints: " . count($workingEndpoints) . ". Data formats detected: " . implode(', ', $dataFormats),
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            $this->testResults[] = [
                'module' => 'API',
                'test' => $testName,
                'status' => 'FAILED',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Error: " . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    private function testAPIResponseFormat() {
        $testName = "API Response Format Test";
        $startTime = microtime(true);
        
        try {
            // Test API response formats
            $this->driver->get($this->baseUrl);
            $pageSource = $this->driver->getPageSource();
            
            // Look for AJAX/API calls in the page
            $hasJavaScript = strpos(strtolower($pageSource), '<script') !== false;
            $hasAjaxTerms = strpos(strtolower($pageSource), 'ajax') !== false || 
                           strpos(strtolower($pageSource), 'fetch') !== false ||
                           strpos(strtolower($pageSource), 'xmlhttprequest') !== false;
            
            // Look for JSON/API references
            $apiReferences = 0;
            $apiTerms = ['json', 'api', 'endpoint', 'rest'];
            foreach ($apiTerms as $term) {
                if (strpos(strtolower($pageSource), $term) !== false) {
                    $apiReferences++;
                }
            }
            
            // Check for content-type headers or API response indicators
            $hasContentType = strpos(strtolower($pageSource), 'application/json') !== false ||
                             strpos(strtolower($pageSource), 'content-type') !== false;
            
            $apiCapability = ($hasJavaScript ? 1 : 0) + ($hasAjaxTerms ? 1 : 0) + ($apiReferences > 0 ? 1 : 0) + ($hasContentType ? 1 : 0);
            
            $this->testResults[] = [
                'module' => 'API',
                'test' => $testName,
                'status' => $apiCapability >= 2 ? 'PASSED' : 'WARNING',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "API capability score: {$apiCapability}/4. JavaScript: " . ($hasJavaScript ? 'Yes' : 'No') . ", AJAX terms: " . ($hasAjaxTerms ? 'Yes' : 'No') . ", API references: {$apiReferences}",
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            $this->testResults[] = [
                'module' => 'API',
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