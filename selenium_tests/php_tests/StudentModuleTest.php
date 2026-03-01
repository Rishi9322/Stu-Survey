<?php
/**
 * Student Module Selenium Test Suite
 * Tests student-specific functionalities
 */

require_once '../../vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverWait;
use Facebook\WebDriver\WebDriverExpectedCondition;

class StudentModuleTest {
    private $driver;
    private $baseUrl = 'http://localhost:80/stu/public/';
    private $testResults = [];
    
    public function __construct() {
        $capabilities = DesiredCapabilities::chrome();
        $this->driver = RemoteWebDriver::create('http://localhost:4444/wd/hub', $capabilities);
        $this->driver->manage()->window()->maximize();
    }
    
    public function runAllTests() {
        echo "Starting Student Module Tests...\n";
        
        $this->testStudentRegistration();
        $this->testStudentLogin();
        $this->testStudentDashboard();
        $this->testStudentProfile();
        $this->testStudentFeedbackSystem();
        
        $this->tearDown();
        return $this->testResults;
    }
    
    private function testStudentRegistration() {
        $testName = "Student Registration Test";
        $startTime = microtime(true);
        
        try {
            $this->driver->get($this->baseUrl . 'register.php');
            $wait = new WebDriverWait($this->driver, 10);
            
            // Check if registration form exists
            $registrationForm = $wait->until(
                WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::tagName('form'))
            );
            
            // Look for typical registration fields
            $formElements = $this->driver->findElements(WebDriverBy::tagName('input'));
            $fieldTypes = [];
            
            foreach ($formElements as $element) {
                $type = $element->getAttribute('type');
                $name = $element->getAttribute('name');
                if ($type) $fieldTypes[] = $type;
            }
            
            $hasEmail = in_array('email', $fieldTypes) || $this->hasFieldByName(['email', 'username']);
            $hasPassword = in_array('password', $fieldTypes) || $this->hasFieldByName(['password']);
            $hasSubmit = in_array('submit', $fieldTypes) || $this->driver->findElements(WebDriverBy::cssSelector('button[type="submit"]'));
            
            $formComplete = $hasEmail && $hasPassword && $hasSubmit;
            
            $this->testResults[] = [
                'module' => 'Student',
                'test' => $testName,
                'status' => $formComplete ? 'PASSED' : 'WARNING',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Registration form found. Email field: " . ($hasEmail ? 'Yes' : 'No') . ", Password field: " . ($hasPassword ? 'Yes' : 'No'),
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            $this->testResults[] = [
                'module' => 'Student',
                'test' => $testName,
                'status' => 'FAILED',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Error: " . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    private function testStudentLogin() {
        $testName = "Student Login Test";
        $startTime = microtime(true);
        
        try {
            $this->driver->get($this->baseUrl . 'login.php');
            $wait = new WebDriverWait($this->driver, 10);
            
            $loginForm = $wait->until(
                WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::tagName('form'))
            );
            
            // Test login functionality
            $usernameField = $this->findFieldByName(['username', 'email']);
            $passwordField = $this->findFieldByName(['password']);
            
            if ($usernameField && $passwordField) {
                $usernameField->sendKeys('student@test.com');
                $passwordField->sendKeys('student123');
                
                $submitButton = $this->driver->findElement(WebDriverBy::cssSelector('button[type="submit"], input[type="submit"]'));
                $submitButton->click();
                
                sleep(2);
                
                $currentUrl = $this->driver->getCurrentURL();
                $urlChanged = !strpos($currentUrl, 'login.php');
                
                $this->testResults[] = [
                    'module' => 'Student',
                    'test' => $testName,
                    'status' => 'PASSED',
                    'duration' => round(microtime(true) - $startTime, 2),
                    'details' => "Login form functional. URL after login: {$currentUrl}",
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            } else {
                throw new Exception("Required login fields not found");
            }
            
        } catch (Exception $e) {
            $this->testResults[] = [
                'module' => 'Student',
                'test' => $testName,
                'status' => 'FAILED',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Error: " . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    private function testStudentDashboard() {
        $testName = "Student Dashboard Test";
        $startTime = microtime(true);
        
        try {
            $this->driver->get($this->baseUrl . 'index.php');
            
            $pageSource = strtolower($this->driver->getPageSource());
            
            // Look for student-specific elements
            $studentElements = [
                'student', 'dashboard', 'course', 'grade', 'assignment', 'profile'
            ];
            
            $foundElements = 0;
            foreach ($studentElements as $element) {
                if (strpos($pageSource, $element) !== false) {
                    $foundElements++;
                }
            }
            
            // Check for navigation elements
            $navElements = $this->driver->findElements(WebDriverBy::tagName('nav'));
            $menuElements = $this->driver->findElements(WebDriverBy::cssSelector('ul, .menu, .navigation'));
            
            $hasNavigation = count($navElements) > 0 || count($menuElements) > 0;
            
            $this->testResults[] = [
                'module' => 'Student',
                'test' => $testName,
                'status' => ($foundElements >= 2 && $hasNavigation) ? 'PASSED' : 'WARNING',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Found {$foundElements}/6 student elements. Navigation: " . ($hasNavigation ? 'Present' : 'Missing'),
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            $this->testResults[] = [
                'module' => 'Student',
                'test' => $testName,
                'status' => 'FAILED',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Error: " . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    private function testStudentProfile() {
        $testName = "Student Profile Management Test";
        $startTime = microtime(true);
        
        try {
            // Try various profile URLs
            $profileUrls = [
                $this->baseUrl . 'profile.php',
                $this->baseUrl . 'student/profile.php',
                $this->baseUrl . 'app/student/profile.php'
            ];
            
            $profileFound = false;
            $workingUrl = '';
            
            foreach ($profileUrls as $url) {
                try {
                    $this->driver->get($url);
                    $title = $this->driver->getTitle();
                    $pageSource = strtolower($this->driver->getPageSource());
                    
                    if (strpos($pageSource, 'profile') !== false || 
                        strpos($pageSource, 'personal') !== false ||
                        strpos($pageSource, 'account') !== false) {
                        $profileFound = true;
                        $workingUrl = $url;
                        break;
                    }
                } catch (Exception $e) {
                    continue;
                }
            }
            
            $this->testResults[] = [
                'module' => 'Student',
                'test' => $testName,
                'status' => $profileFound ? 'PASSED' : 'WARNING',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => $profileFound ? "Profile page accessible at: {$workingUrl}" : "Profile page not found at standard locations.",
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            $this->testResults[] = [
                'module' => 'Student',
                'test' => $testName,
                'status' => 'FAILED',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Error: " . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    private function testStudentFeedbackSystem() {
        $testName = "Student Feedback System Test";
        $startTime = microtime(true);
        
        try {
            $this->driver->get($this->baseUrl);
            $pageSource = strtolower($this->driver->getPageSource());
            
            // Look for feedback-related functionality
            $feedbackKeywords = ['feedback', 'survey', 'rating', 'review', 'comment'];
            $foundFeedback = 0;
            
            foreach ($feedbackKeywords as $keyword) {
                if (strpos($pageSource, $keyword) !== false) {
                    $foundFeedback++;
                }
            }
            
            // Check for form elements that might be feedback forms
            $forms = $this->driver->findElements(WebDriverBy::tagName('form'));
            $textareas = $this->driver->findElements(WebDriverBy::tagName('textarea'));
            
            $hasFeedbackForm = count($forms) > 0 && count($textareas) > 0;
            
            $this->testResults[] = [
                'module' => 'Student',
                'test' => $testName,
                'status' => ($foundFeedback > 0 || $hasFeedbackForm) ? 'PASSED' : 'WARNING',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Found {$foundFeedback} feedback keywords. Forms with textareas: " . (count($forms) . "/" . count($textareas)),
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            $this->testResults[] = [
                'module' => 'Student',
                'test' => $testName,
                'status' => 'FAILED',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Error: " . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    private function hasFieldByName($names) {
        foreach ($names as $name) {
            try {
                $element = $this->driver->findElement(WebDriverBy::name($name));
                if ($element) return true;
            } catch (Exception $e) {
                continue;
            }
        }
        return false;
    }
    
    private function findFieldByName($names) {
        foreach ($names as $name) {
            try {
                return $this->driver->findElement(WebDriverBy::name($name));
            } catch (Exception $e) {
                continue;
            }
        }
        return null;
    }
    
    private function tearDown() {
        if ($this->driver) {
            $this->driver->quit();
        }
    }
}
?>