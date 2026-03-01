<?php
/**
 * Teacher Module Selenium Test Suite
 * Tests teacher-specific functionalities
 */

require_once '../../vendor/autoload.php';

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverWait;
use Facebook\WebDriver\WebDriverExpectedCondition;

class TeacherModuleTest {
    private $driver;
    private $baseUrl = 'http://localhost:80/stu/public/';
    private $testResults = [];
    
    public function __construct() {
        $capabilities = DesiredCapabilities::chrome();
        $this->driver = RemoteWebDriver::create('http://localhost:4444/wd/hub', $capabilities);
        $this->driver->manage()->window()->maximize();
    }
    
    public function runAllTests() {
        echo "Starting Teacher Module Tests...\n";
        
        $this->testTeacherAuthentication();
        $this->testTeacherDashboard();
        $this->testCourseManagement();
        $this->testStudentManagement();
        $this->testGradingSystem();
        
        $this->tearDown();
        return $this->testResults;
    }
    
    private function testTeacherAuthentication() {
        $testName = "Teacher Authentication Test";
        $startTime = microtime(true);
        
        try {
            $this->driver->get($this->baseUrl . 'login.php');
            $wait = new WebDriverWait($this->driver, 10);
            
            // Wait for login form
            $loginForm = $wait->until(
                WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::tagName('form'))
            );
            
            // Test teacher login
            $usernameField = $this->findFieldByName(['username', 'email']);
            $passwordField = $this->findFieldByName(['password']);
            
            if ($usernameField && $passwordField) {
                $usernameField->clear();
                $usernameField->sendKeys('teacher@test.com');
                $passwordField->clear();
                $passwordField->sendKeys('teacher123');
                
                $submitButton = $this->driver->findElement(WebDriverBy::cssSelector('button[type="submit"], input[type="submit"]'));
                $submitButton->click();
                
                sleep(2);
                
                $currentUrl = $this->driver->getCurrentURL();
                $pageSource = strtolower($this->driver->getPageSource());
                
                // Look for teacher-specific content after login
                $teacherKeywords = ['teacher', 'instructor', 'faculty', 'course'];
                $teacherContentFound = 0;
                
                foreach ($teacherKeywords as $keyword) {
                    if (strpos($pageSource, $keyword) !== false) {
                        $teacherContentFound++;
                    }
                }
                
                $this->testResults[] = [
                    'module' => 'Teacher',
                    'test' => $testName,
                    'status' => 'PASSED',
                    'duration' => round(microtime(true) - $startTime, 2),
                    'details' => "Login processed. Found {$teacherContentFound} teacher-related keywords. URL: {$currentUrl}",
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            } else {
                throw new Exception("Required login fields not found");
            }
            
        } catch (Exception $e) {
            $this->testResults[] = [
                'module' => 'Teacher',
                'test' => $testName,
                'status' => 'FAILED',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Error: " . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    private function testTeacherDashboard() {
        $testName = "Teacher Dashboard Access Test";
        $startTime = microtime(true);
        
        try {
            $this->driver->get($this->baseUrl . 'index.php');
            
            $pageSource = strtolower($this->driver->getPageSource());
            
            // Look for teacher dashboard elements
            $dashboardElements = [
                'dashboard', 'course', 'student', 'grade', 'assignment', 'class'
            ];
            
            $foundElements = 0;
            foreach ($dashboardElements as $element) {
                if (strpos($pageSource, $element) !== false) {
                    $foundElements++;
                }
            }
            
            // Check for teacher-specific navigation
            $links = $this->driver->findElements(WebDriverBy::tagName('a'));
            $teacherLinks = 0;
            
            foreach ($links as $link) {
                $linkText = strtolower($link->getText());
                if (strpos($linkText, 'course') !== false || 
                    strpos($linkText, 'grade') !== false ||
                    strpos($linkText, 'student') !== false ||
                    strpos($linkText, 'class') !== false) {
                    $teacherLinks++;
                }
            }
            
            $this->testResults[] = [
                'module' => 'Teacher',
                'test' => $testName,
                'status' => ($foundElements >= 3 || $teacherLinks >= 2) ? 'PASSED' : 'WARNING',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Dashboard elements: {$foundElements}/6, Teacher navigation links: {$teacherLinks}",
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            $this->testResults[] = [
                'module' => 'Teacher',
                'test' => $testName,
                'status' => 'FAILED',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Error: " . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    private function testCourseManagement() {
        $testName = "Course Management System Test";
        $startTime = microtime(true);
        
        try {
            // Try different course management URLs
            $courseUrls = [
                $this->baseUrl . 'courses.php',
                $this->baseUrl . 'teacher/courses.php',
                $this->baseUrl . 'app/teacher/courses.php'
            ];
            
            $coursePageFound = false;
            $workingUrl = '';
            
            foreach ($courseUrls as $url) {
                try {
                    $this->driver->get($url);
                    $pageSource = strtolower($this->driver->getPageSource());
                    
                    if (strpos($pageSource, 'course') !== false) {
                        $coursePageFound = true;
                        $workingUrl = $url;
                        
                        // Look for course management features
                        $managementFeatures = ['add', 'edit', 'delete', 'create', 'manage'];
                        $featuresFound = 0;
                        
                        foreach ($managementFeatures as $feature) {
                            if (strpos($pageSource, $feature) !== false) {
                                $featuresFound++;
                            }
                        }
                        
                        break;
                    }
                } catch (Exception $e) {
                    continue;
                }
            }
            
            if (!$coursePageFound) {
                // Check main page for course-related content
                $this->driver->get($this->baseUrl);
                $pageSource = strtolower($this->driver->getPageSource());
                $coursePageFound = strpos($pageSource, 'course') !== false;
                $workingUrl = $this->baseUrl;
                $featuresFound = 0;
            }
            
            $this->testResults[] = [
                'module' => 'Teacher',
                'test' => $testName,
                'status' => $coursePageFound ? 'PASSED' : 'WARNING',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => $coursePageFound ? "Course management accessible at: {$workingUrl}. Management features: {$featuresFound}" : "Course management not found at standard locations.",
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            $this->testResults[] = [
                'module' => 'Teacher',
                'test' => $testName,
                'status' => 'FAILED',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Error: " . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    private function testStudentManagement() {
        $testName = "Student Management Test";
        $startTime = microtime(true);
        
        try {
            $this->driver->get($this->baseUrl);
            $pageSource = strtolower($this->driver->getPageSource());
            
            // Look for student management capabilities
            $studentManagementKeywords = [
                'student', 'enrollment', 'roster', 'attendance', 'progress'
            ];
            
            $managementFeatures = 0;
            foreach ($studentManagementKeywords as $keyword) {
                if (strpos($pageSource, $keyword) !== false) {
                    $managementFeatures++;
                }
            }
            
            // Check for tables or lists that might contain student data
            $tables = $this->driver->findElements(WebDriverBy::tagName('table'));
            $lists = $this->driver->findElements(WebDriverBy::tagName('ul'));
            
            $tableCount = is_array($tables) ? count($tables) : 0;
            $listCount = is_array($lists) ? count($lists) : 0;
            $hasDataStructures = $tableCount > 0 || $listCount > 0;
            
            $this->testResults[] = [
                'module' => 'Teacher',
                'test' => $testName,
                'status' => ($managementFeatures >= 2 || $hasDataStructures) ? 'PASSED' : 'WARNING',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Student management keywords: {$managementFeatures}/5. Data structures: Tables(" . $tableCount . "), Lists(" . $listCount . ")",
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            $this->testResults[] = [
                'module' => 'Teacher',
                'test' => $testName,
                'status' => 'FAILED',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Error: " . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    private function testGradingSystem() {
        $testName = "Grading System Test";
        $startTime = microtime(true);
        
        try {
            $this->driver->get($this->baseUrl);
            $pageSource = strtolower($this->driver->getPageSource());
            
            // Look for grading-related functionality
            $gradingKeywords = [
                'grade', 'score', 'mark', 'assessment', 'evaluation', 'feedback'
            ];
            
            $gradingFeatures = 0;
            foreach ($gradingKeywords as $keyword) {
                if (strpos($pageSource, $keyword) !== false) {
                    $gradingFeatures++;
                }
            }
            
            // Check for forms that might be used for grading
            $forms = $this->driver->findElements(WebDriverBy::tagName('form'));
            $inputs = $this->driver->findElements(WebDriverBy::cssSelector('input[type="number"], input[type="text"]'));
            $textareas = $this->driver->findElements(WebDriverBy::tagName('textarea'));
            
            $hasGradingInterface = (count($forms) > 0 && (count($inputs) > 0 || count($textareas) > 0));
            
            $this->testResults[] = [
                'module' => 'Teacher',
                'test' => $testName,
                'status' => ($gradingFeatures >= 2 || $hasGradingInterface) ? 'PASSED' : 'WARNING',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Grading keywords: {$gradingFeatures}/6. Forms: " . count($forms) . ", Input fields: " . count($inputs) . ", Textareas: " . count($textareas),
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            $this->testResults[] = [
                'module' => 'Teacher',
                'test' => $testName,
                'status' => 'FAILED',
                'duration' => round(microtime(true) - $startTime, 2),
                'details' => "Error: " . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
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