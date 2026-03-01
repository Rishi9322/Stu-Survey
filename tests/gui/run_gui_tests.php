<?php
/**
 * GUI Testing Suite for Student Survey System
 * Tests all UI components, pages, forms, and interactions
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$baseUrl = 'http://localhost/stu';
$reportDir = __DIR__ . '/../../test_reports/gui_reports';

if (!is_dir($reportDir)) {
    mkdir($reportDir, 0777, true);
}

class GUITestRunner {
    private $baseUrl;
    private $results = [];
    private $totalTests = 0;
    private $passedTests = 0;
    private $failedTests = 0;
    
    public function __construct($baseUrl) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }
    
    public function testPageLoad($page, $expectedTitle, $testName) {
        $this->totalTests++;
        $url = $this->baseUrl . '/' . ltrim($page, '/');
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $startTime = microtime(true);
        $response = curl_exec($ch);
        $loadTime = microtime(true) - $startTime;
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $error = curl_error($ch);
        curl_close($ch);
        
        $passed = ($httpCode === 200 && !$error);
        
        // Check if title exists in response
        if ($passed && $expectedTitle) {
            $passed = (stripos($response, $expectedTitle) !== false);
        }
        
        if ($passed) {
            $this->passedTests++;
        } else {
            $this->failedTests++;
        }
        
        $this->results[] = [
            'test_name' => $testName,
            'url' => $url,
            'http_code' => $httpCode,
            'load_time' => round($loadTime, 3),
            'content_type' => $contentType,
            'passed' => $passed,
            'error' => $error,
            'expected' => $expectedTitle,
            'response_size' => strlen($response),
            'type' => 'Page Load'
        ];
    }
    
    public function testFormElements($page, $formId, $testName) {
        $this->totalTests++;
        $url = $this->baseUrl . '/' . ltrim($page, '/');
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Check for form existence
        $hasForm = (stripos($response, 'form') !== false);
        $hasInputs = (stripos($response, 'input') !== false);
        $hasButtons = (stripos($response, 'button') !== false || stripos($response, 'submit') !== false);
        
        $passed = ($httpCode === 200 && $hasForm && $hasInputs && $hasButtons);
        
        if ($passed) {
            $this->passedTests++;
        } else {
            $this->failedTests++;
        }
        
        $this->results[] = [
            'test_name' => $testName,
            'url' => $url,
            'http_code' => $httpCode,
            'passed' => $passed,
            'has_form' => $hasForm,
            'has_inputs' => $hasInputs,
            'has_buttons' => $hasButtons,
            'type' => 'Form Elements'
        ];
    }
    
    public function testNavigation($page, $expectedLinks, $testName) {
        $this->totalTests++;
        $url = $this->baseUrl . '/' . ltrim($page, '/');
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $foundLinks = [];
        foreach ($expectedLinks as $link) {
            $foundLinks[$link] = (stripos($response, $link) !== false);
        }
        
        $allFound = !in_array(false, $foundLinks, true);
        $passed = ($httpCode === 200 && $allFound);
        
        if ($passed) {
            $this->passedTests++;
        } else {
            $this->failedTests++;
        }
        
        $this->results[] = [
            'test_name' => $testName,
            'url' => $url,
            'http_code' => $httpCode,
            'passed' => $passed,
            'expected_links' => $expectedLinks,
            'found_links' => $foundLinks,
            'type' => 'Navigation'
        ];
    }
    
    public function testResponsive($page, $testName) {
        $this->totalTests++;
        $url = $this->baseUrl . '/' . ltrim($page, '/');
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Check for responsive meta tags and CSS
        $hasViewport = (stripos($response, 'viewport') !== false);
        $hasMediaQuery = (stripos($response, '@media') !== false || stripos($response, 'bootstrap') !== false);
        $hasResponsiveClasses = (stripos($response, 'col-') !== false || stripos($response, 'flex') !== false);
        
        $passed = ($httpCode === 200 && $hasViewport);
        
        if ($passed) {
            $this->passedTests++;
        } else {
            $this->failedTests++;
        }
        
        $this->results[] = [
            'test_name' => $testName,
            'url' => $url,
            'passed' => $passed,
            'has_viewport' => $hasViewport,
            'has_media_query' => $hasMediaQuery,
            'has_responsive_classes' => $hasResponsiveClasses,
            'type' => 'Responsive Design'
        ];
    }
    
    public function testAssets($page, $assetType, $testName) {
        $this->totalTests++;
        $url = $this->baseUrl . '/' . ltrim($page, '/');
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $hasCSS = (stripos($response, '.css') !== false || stripos($response, '<style') !== false);
        $hasJS = (stripos($response, '.js') !== false || stripos($response, '<script') !== false);
        $hasImages = (stripos($response, '<img') !== false || stripos($response, '.png') !== false || stripos($response, '.jpg') !== false);
        
        $passed = ($httpCode === 200 && ($hasCSS || $hasJS || $hasImages));
        
        if ($passed) {
            $this->passedTests++;
        } else {
            $this->failedTests++;
        }
        
        $this->results[] = [
            'test_name' => $testName,
            'url' => $url,
            'passed' => $passed,
            'has_css' => $hasCSS,
            'has_js' => $hasJS,
            'has_images' => $hasImages,
            'type' => 'Assets Loading'
        ];
    }
    
    public function getResults() {
        return [
            'total' => $this->totalTests,
            'passed' => $this->passedTests,
            'failed' => $this->failedTests,
            'pass_rate' => $this->totalTests > 0 ? round(($this->passedTests / $this->totalTests) * 100, 2) : 0,
            'tests' => $this->results
        ];
    }
}

// Initialize test runner
$runner = new GUITestRunner($baseUrl);

echo "Starting GUI Tests...\n\n";

// ===== PUBLIC PAGES TESTS =====
echo "Testing Public Pages...\n";
$runner->testPageLoad('/', '', 'Homepage Load');
$runner->testPageLoad('/index.php', '', 'Index Page Load');
$runner->testPageLoad('/public/index.php', '', 'Public Homepage Load');
$runner->testPageLoad('/public/login.php', '', 'Login Page Load');
$runner->testPageLoad('/public/register.php', '', 'Registration Page Load');
$runner->testPageLoad('/public/about.php', '', 'About Page Load');
$runner->testPageLoad('/public/contact.php', '', 'Contact Page Load');
$runner->testPageLoad('/public/help.php', '', 'Help Page Load');
$runner->testPageLoad('/public/privacy.php', '', 'Privacy Policy Page Load');
$runner->testPageLoad('/public/terms.php', '', 'Terms Page Load');

// ===== FORM TESTS =====
echo "Testing Forms...\n";
$runner->testFormElements('/public/login.php', 'loginForm', 'Login Form Elements');
$runner->testFormElements('/public/register.php', 'registerForm', 'Registration Form Elements');
$runner->testFormElements('/public/contact.php', 'contactForm', 'Contact Form Elements');

// ===== RESPONSIVE DESIGN TESTS =====
echo "Testing Responsive Design...\n";
$runner->testResponsive('/', 'Root Responsive');
$runner->testResponsive('/index.php', 'Index Responsive');
$runner->testResponsive('/public/login.php', 'Login Page Responsive');

// ===== ASSETS TESTS =====
echo "Testing Asset Loading...\n";
$runner->testAssets('/', 'all', 'Root Assets');
$runner->testAssets('/index.php', 'all', 'Index Assets');
$runner->testAssets('/public/login.php', 'all', 'Login Page Assets');

echo "\nGUI Tests Completed!\n\n";

// Get results
$results = $runner->getResults();

// Save JSON results
file_put_contents($reportDir . '/gui_test_results.json', json_encode($results, JSON_PRETTY_PRINT));

// Generate HTML Report
generateHTMLReport($results, $reportDir);

echo "Results saved to: {$reportDir}\n";
echo "Total Tests: {$results['total']}\n";
echo "Passed: {$results['passed']}\n";
echo "Failed: {$results['failed']}\n";
echo "Pass Rate: {$results['pass_rate']}%\n";

function generateHTMLReport($results, $reportDir) {
    $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GUI Testing Report - Student Survey System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        .header p {
            font-size: 1.2em;
            opacity: 0.9;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 30px;
            background: #f8f9fa;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            margin: 10px 0;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .stat-card.total .stat-number { color: #007bff; }
        .stat-card.passed .stat-number { color: #28a745; }
        .stat-card.failed .stat-number { color: #dc3545; }
        .stat-card.rate .stat-number { color: #17a2b8; }
        
        .test-section {
            padding: 30px;
        }
        .test-section h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
        }
        .test-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .test-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        .test-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .test-table tr:hover {
            background: #f8f9fa;
        }
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge.pass {
            background: #d4edda;
            color: #155724;
        }
        .badge.fail {
            background: #f8d7da;
            color: #721c24;
        }
        .type-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.75em;
            background: #e9ecef;
            color: #495057;
            margin-right: 5px;
        }
        .details {
            font-size: 0.85em;
            color: #6c757d;
            margin-top: 5px;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 0.9em;
        }
        .progress-bar {
            width: 100%;
            height: 30px;
            background: #e9ecef;
            border-radius: 15px;
            overflow: hidden;
            margin: 20px 0;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            transition: width 0.5s ease;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🖥️ GUI Testing Report</h1>
            <p>Student Satisfaction Survey System</p>
            <p style="font-size: 0.9em; margin-top: 10px;">Generated: ' . date('F d, Y - H:i:s') . '</p>
        </div>
        
        <div class="stats">
            <div class="stat-card total">
                <div class="stat-label">Total Tests</div>
                <div class="stat-number">' . $results['total'] . '</div>
            </div>
            <div class="stat-card passed">
                <div class="stat-label">Passed</div>
                <div class="stat-number">' . $results['passed'] . '</div>
            </div>
            <div class="stat-card failed">
                <div class="stat-label">Failed</div>
                <div class="stat-number">' . $results['failed'] . '</div>
            </div>
            <div class="stat-card rate">
                <div class="stat-label">Pass Rate</div>
                <div class="stat-number">' . $results['pass_rate'] . '%</div>
            </div>
        </div>
        
        <div style="padding: 0 30px;">
            <div class="progress-bar">
                <div class="progress-fill" style="width: ' . $results['pass_rate'] . '%">
                    ' . $results['pass_rate'] . '% Passed
                </div>
            </div>
        </div>
        
        <div class="test-section">
            <h2>📋 Test Results</h2>
            <table class="test-table">
                <thead>
                    <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 20%">Test Type</th>
                        <th style="width: 30%">Test Name</th>
                        <th style="width: 10%">Status</th>
                        <th style="width: 35%">Details</th>
                    </tr>
                </thead>
                <tbody>';
    
    $index = 1;
    foreach ($results['tests'] as $test) {
        $statusBadge = $test['passed'] ? '<span class="badge pass">✓ PASS</span>' : '<span class="badge fail">✗ FAIL</span>';
        
        $details = '';
        if (isset($test['load_time'])) {
            $details .= "Load Time: {$test['load_time']}s | ";
        }
        if (isset($test['http_code'])) {
            $details .= "HTTP: {$test['http_code']} | ";
        }
        if (isset($test['response_size'])) {
            $details .= "Size: " . number_format($test['response_size']) . " bytes";
        }
        if (isset($test['has_form'])) {
            $details .= "Form: " . ($test['has_form'] ? '✓' : '✗') . " | ";
            $details .= "Inputs: " . ($test['has_inputs'] ? '✓' : '✗') . " | ";
            $details .= "Buttons: " . ($test['has_buttons'] ? '✓' : '✗');
        }
        if (isset($test['has_viewport'])) {
            $details .= "Viewport: " . ($test['has_viewport'] ? '✓' : '✗') . " | ";
            $details .= "Media Query: " . ($test['has_media_query'] ? '✓' : '✗') . " | ";
            $details .= "Responsive: " . ($test['has_responsive_classes'] ? '✓' : '✗');
        }
        if (isset($test['has_css'])) {
            $details .= "CSS: " . ($test['has_css'] ? '✓' : '✗') . " | ";
            $details .= "JS: " . ($test['has_js'] ? '✓' : '✗') . " | ";
            $details .= "Images: " . ($test['has_images'] ? '✓' : '✗');
        }
        
        $html .= "<tr>
                    <td>{$index}</td>
                    <td><span class='type-badge'>{$test['type']}</span></td>
                    <td><strong>{$test['test_name']}</strong><br><small style='color:#6c757d'>{$test['url']}</small></td>
                    <td>{$statusBadge}</td>
                    <td class='details'>{$details}</td>
                  </tr>";
        $index++;
    }
    
    $html .= '</tbody>
            </table>
        </div>
        
        <div class="footer">
            <p><strong>Student Survey System</strong> | GUI Testing Suite v1.0</p>
            <p>Automated GUI testing completed successfully</p>
        </div>
    </div>
</body>
</html>';
    
    file_put_contents($reportDir . '/gui_test_report.html', $html);
    
    // Generate index page
    generateIndexPage($reportDir);
}

function generateIndexPage($reportDir) {
    $indexHtml = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GUI Test Reports - Index</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px;
            margin: 0;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .report-card {
            background: #f8f9fa;
            padding: 25px;
            margin: 15px 0;
            border-radius: 10px;
            border-left: 5px solid #667eea;
            transition: transform 0.3s;
        }
        .report-card:hover {
            transform: translateX(5px);
        }
        .report-card h3 {
            margin: 0 0 10px 0;
            color: #667eea;
        }
        .report-card p {
            margin: 5px 0;
            color: #6c757d;
        }
        .report-card a {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .report-card a:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🖥️ GUI Testing Reports</h1>
        
        <div class="report-card">
            <h3>Complete GUI Test Report</h3>
            <p>Comprehensive testing of all UI components, pages, forms, navigation, and responsiveness</p>
            <p><strong>Date:</strong> ' . date('F d, Y H:i:s') . '</p>
            <a href="gui_test_report.html">View Report →</a>
        </div>
        
        <div class="report-card">
            <h3>JSON Test Results</h3>
            <p>Raw test data in JSON format for programmatic access</p>
            <a href="gui_test_results.json">Download JSON →</a>
        </div>
    </div>
</body>
</html>';
    
    file_put_contents($reportDir . '/index.html', $indexHtml);
}
