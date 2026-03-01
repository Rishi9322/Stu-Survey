<?php
/**
 * Main Test Runner for All Modules
 * Executes comprehensive Selenium tests for Admin, Student, Teacher, and API modules
 */

require_once '../vendor/autoload.php';
require_once 'AdminModuleTest.php';
require_once 'StudentModuleTest.php';
require_once 'TeacherModuleTest.php';
require_once 'APIModuleTest.php';

class MainTestRunner {
    private $allResults = [];
    private $startTime;
    
    public function __construct() {
        $this->startTime = microtime(true);
        echo "===========================================\n";
        echo "Student Feedback System - Selenium Testing\n";
        echo "===========================================\n";
        echo "Starting comprehensive module testing...\n\n";
    }
    
    public function runAllModuleTests() {
        // Test Admin Module
        echo "1. Testing Admin Module...\n";
        $adminTest = new AdminModuleTest();
        $adminResults = $adminTest->runAllTests();
        $this->allResults = array_merge($this->allResults, $adminResults);
        
        // Test Student Module
        echo "\n2. Testing Student Module...\n";
        $studentTest = new StudentModuleTest();
        $studentResults = $studentTest->runAllTests();
        $this->allResults = array_merge($this->allResults, $studentResults);
        
        // Test Teacher Module
        echo "\n3. Testing Teacher Module...\n";
        $teacherTest = new TeacherModuleTest();
        $teacherResults = $teacherTest->runAllTests();
        $this->allResults = array_merge($this->allResults, $teacherResults);
        
        // Test API Module
        echo "\n4. Testing API Module...\n";
        $apiTest = new APIModuleTest();
        $apiResults = $apiTest->runAllTests();
        $this->allResults = array_merge($this->allResults, $apiResults);
        
        // Generate reports
        $this->generateRawReport();
        $this->generateAcademicReport();
        
        // Display summary
        $this->displayTestSummary();
        
        return $this->allResults;
    }
    
    private function generateRawReport() {
        $rawReportContent = "=== SELENIUM TEST RESULTS - RAW OUTPUT ===\n";
        $rawReportContent .= "Execution Date: " . date('Y-m-d H:i:s') . "\n";
        $rawReportContent .= "Total Duration: " . round(microtime(true) - $this->startTime, 2) . " seconds\n\n";
        
        foreach ($this->allResults as $result) {
            $rawReportContent .= "MODULE: {$result['module']}\n";
            $rawReportContent .= "TEST: {$result['test']}\n";
            $rawReportContent .= "STATUS: {$result['status']}\n";
            $rawReportContent .= "DURATION: {$result['duration']}s\n";
            $rawReportContent .= "TIMESTAMP: {$result['timestamp']}\n";
            $rawReportContent .= "DETAILS: {$result['details']}\n";
            $rawReportContent .= str_repeat("-", 60) . "\n";
        }
        
        // Statistics
        $passed = array_filter($this->allResults, function($r) { return $r['status'] === 'PASSED'; });
        $failed = array_filter($this->allResults, function($r) { return $r['status'] === 'FAILED'; });
        $warnings = array_filter($this->allResults, function($r) { return $r['status'] === 'WARNING'; });
        
        $rawReportContent .= "\n=== TEST STATISTICS ===\n";
        $rawReportContent .= "Total Tests: " . count($this->allResults) . "\n";
        $rawReportContent .= "Passed: " . count($passed) . "\n";
        $rawReportContent .= "Failed: " . count($failed) . "\n";
        $rawReportContent .= "Warnings: " . count($warnings) . "\n";
        $rawReportContent .= "Success Rate: " . round((count($passed) / count($this->allResults)) * 100, 2) . "%\n";
        
        file_put_contents('../test_reports/selenium_raw_results_' . date('Y-m-d_H-i-s') . '.txt', $rawReportContent);
        echo "Raw results report saved to test_reports/\n";
    }
    
    private function generateAcademicReport() {
        $reportContent = $this->generateAcademicReportContent();
        $filename = '../test_reports/academic_test_report_' . date('Y-m-d_H-i-s') . '.html';
        file_put_contents($filename, $reportContent);
        echo "Academic report saved to test_reports/\n";
    }
    
    private function generateAcademicReportContent() {
        // Statistics
        $passed = array_filter($this->allResults, function($r) { return $r['status'] === 'PASSED'; });
        $failed = array_filter($this->allResults, function($r) { return $r['status'] === 'FAILED'; });
        $warnings = array_filter($this->allResults, function($r) { return $r['status'] === 'WARNING'; });
        
        $moduleStats = [];
        foreach (['Admin', 'Student', 'Teacher', 'API'] as $module) {
            $moduleResults = array_filter($this->allResults, function($r) use ($module) { return $r['module'] === $module; });
            $modulePassed = array_filter($moduleResults, function($r) { return $r['status'] === 'PASSED'; });
            $moduleStats[$module] = [
                'total' => count($moduleResults),
                'passed' => count($modulePassed),
                'rate' => count($moduleResults) > 0 ? round((count($modulePassed) / count($moduleResults)) * 100, 2) : 0
            ];
        }
        
        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Feedback System - Automated Testing Report</title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            line-height: 1.6;
            margin: 0;
            padding: 40px;
            background-color: #f9f9f9;
            color: #333;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 60px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #2c3e50;
            margin-bottom: 40px;
            padding-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
            color: #2c3e50;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header .subtitle {
            font-size: 16px;
            color: #7f8c8d;
            margin-top: 10px;
            font-style: italic;
        }
        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            font-size: 12px;
            color: #666;
        }
        .abstract {
            background: #ecf0f1;
            padding: 20px;
            margin-bottom: 30px;
            border-left: 4px solid #3498db;
            font-style: italic;
        }
        .abstract h3 {
            margin-top: 0;
            color: #2c3e50;
            font-size: 14px;
            text-transform: uppercase;
        }
        h2 {
            color: #2c3e50;
            border-bottom: 1px solid #bdc3c7;
            padding-bottom: 5px;
            margin-top: 40px;
            font-size: 18px;
        }
        h3 {
            color: #34495e;
            font-size: 14px;
            margin-top: 25px;
        }
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 12px;
        }
        .results-table th, .results-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .results-table th {
            background-color: #34495e;
            color: white;
            font-weight: bold;
        }
        .status-passed { color: #27ae60; font-weight: bold; }
        .status-failed { color: #e74c3c; font-weight: bold; }
        .status-warning { color: #f39c12; font-weight: bold; }
        .statistics {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 30px 0;
        }
        .stat-card {
            background: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-label {
            font-size: 12px;
            color: #7f8c8d;
            text-transform: uppercase;
        }
        .conclusion {
            background: #e8f5e8;
            border: 1px solid #27ae60;
            padding: 20px;
            margin-top: 30px;
            border-radius: 5px;
        }
        .methodology {
            background: #f8f9fa;
            border-left: 4px solid #6c757d;
            padding: 20px;
            margin: 20px 0;
            font-size: 13px;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #bdc3c7;
            text-align: center;
            font-size: 11px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Student Feedback System</h1>
            <div class="subtitle">Comprehensive Automated Testing Report</div>
        </div>
        
        <div class="meta-info">
            <div><strong>Report Date:</strong> ' . date('F d, Y') . '</div>
            <div><strong>Execution Time:</strong> ' . date('H:i:s T') . '</div>
            <div><strong>Duration:</strong> ' . round(microtime(true) - $this->startTime, 2) . ' seconds</div>
        </div>
        
        <div class="abstract">
            <h3>Abstract</h3>
            <p>This report presents the results of comprehensive automated testing conducted on the Student Feedback System platform. 
            The testing methodology employed Selenium WebDriver to evaluate four core modules: Administrative Interface, 
            Student Portal, Teacher Management System, and API Services. The testing framework was designed to assess 
            functional capability, user interface responsiveness, and system integration across all major components.</p>
        </div>
        
        <div class="methodology">
            <h3>Testing Methodology</h3>
            <p><strong>Framework:</strong> Selenium WebDriver with PHP Facebook WebDriver Library<br>
            <strong>Browser:</strong> Google Chrome (Headless Mode)<br>
            <strong>Test Approach:</strong> Black-box functional testing with automated UI interaction<br>
            <strong>Coverage:</strong> Module-specific functionality, authentication systems, data management interfaces</p>
        </div>
        
        <h2>1. Executive Summary</h2>
        <div class="statistics">
            <div class="stat-card">
                <div class="stat-number">' . count($this->allResults) . '</div>
                <div class="stat-label">Total Tests Executed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number status-passed">' . count($passed) . '</div>
                <div class="stat-label">Tests Passed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number status-failed">' . count($failed) . '</div>
                <div class="stat-label">Tests Failed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number status-warning">' . count($warnings) . '</div>
                <div class="stat-label">Warnings</div>
            </div>
        </div>
        
        <h2>2. Module Performance Analysis</h2>
        <table class="results-table">
            <thead>
                <tr>
                    <th>Module</th>
                    <th>Tests Executed</th>
                    <th>Success Rate</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>';
            
        foreach ($moduleStats as $module => $stats) {
            $statusClass = $stats['rate'] >= 80 ? 'status-passed' : ($stats['rate'] >= 60 ? 'status-warning' : 'status-failed');
            $reportContent .= "
                <tr>
                    <td><strong>{$module} Module</strong></td>
                    <td>{$stats['total']}</td>
                    <td class='{$statusClass}'>{$stats['rate']}%</td>
                    <td class='{$statusClass}'>" . ($stats['rate'] >= 80 ? 'Excellent' : ($stats['rate'] >= 60 ? 'Satisfactory' : 'Needs Attention')) . "</td>
                </tr>";
        }
        
        $reportContent .= '
            </tbody>
        </table>
        
        <h2>3. Detailed Test Results</h2>';
        
        foreach (['Admin', 'Student', 'Teacher', 'API'] as $module) {
            $moduleResults = array_filter($this->allResults, function($r) use ($module) { return $r['module'] === $module; });
            
            $reportContent .= "
            <h3>3." . (array_search($module, ['Admin', 'Student', 'Teacher', 'API']) + 1) . " {$module} Module</h3>
            <table class='results-table'>
                <thead>
                    <tr>
                        <th>Test Case</th>
                        <th>Status</th>
                        <th>Duration (s)</th>
                        <th>Observations</th>
                    </tr>
                </thead>
                <tbody>";
                
            foreach ($moduleResults as $result) {
                $statusClass = 'status-' . strtolower($result['status']);
                $reportContent .= "
                    <tr>
                        <td>{$result['test']}</td>
                        <td class='{$statusClass}'>{$result['status']}</td>
                        <td>{$result['duration']}</td>
                        <td style='font-size: 11px;'>{$result['details']}</td>
                    </tr>";
            }
            
            $reportContent .= "</tbody></table>";
        }
        
        $successRate = round((count($passed) / count($this->allResults)) * 100, 2);
        $reportContent .= '
        
        <h2>4. Conclusions and Recommendations</h2>
        <div class="conclusion">
            <h3>Overall Assessment</h3>
            <p>The Student Feedback System demonstrates <strong>' . ($successRate >= 80 ? 'excellent' : ($successRate >= 60 ? 'satisfactory' : 'concerning')) . ' functionality</strong> across all tested modules with an overall success rate of <strong>' . $successRate . '%</strong>. 
            The automated testing revealed that the system\'s core components are operational and accessible through standard web interfaces.</p>
            
            <h3>Key Findings</h3>
            <ul>
                <li><strong>Administrative Module:</strong> ' . ($moduleStats['Admin']['rate'] >= 70 ? 'Core administrative functions are accessible and responsive.' : 'Administrative interface requires attention for optimal functionality.') . '</li>
                <li><strong>Student Portal:</strong> ' . ($moduleStats['Student']['rate'] >= 70 ? 'Student-facing features demonstrate good usability and access patterns.' : 'Student interface shows areas for improvement in accessibility.') . '</li>
                <li><strong>Teacher Interface:</strong> ' . ($moduleStats['Teacher']['rate'] >= 70 ? 'Teacher management capabilities are well-implemented and functional.' : 'Teacher module requires enhancement for better performance.') . '</li>
                <li><strong>API Services:</strong> ' . ($moduleStats['API']['rate'] >= 70 ? 'RESTful services and data endpoints show robust implementation.' : 'API infrastructure needs development for comprehensive data access.') . '</li>
            </ul>
        </div>
        
        <div class="footer">
            <p>Generated by Selenium WebDriver Automated Testing Framework<br>
            Student Feedback System Quality Assurance Report<br>
            ' . date('Y') . ' - Confidential Testing Documentation</p>
        </div>
    </div>
</body>
</html>';
        
        return $reportContent;
    }
    
    private function displayTestSummary() {
        $passed = array_filter($this->allResults, function($r) { return $r['status'] === 'PASSED'; });
        $failed = array_filter($this->allResults, function($r) { return $r['status'] === 'FAILED'; });
        $warnings = array_filter($this->allResults, function($r) { return $r['status'] === 'WARNING'; });
        
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "TEST EXECUTION SUMMARY\n";
        echo str_repeat("=", 50) . "\n";
        echo "Total Tests: " . count($this->allResults) . "\n";
        echo "Passed: " . count($passed) . " (" . round((count($passed) / count($this->allResults)) * 100, 2) . "%)\n";
        echo "Failed: " . count($failed) . " (" . round((count($failed) / count($this->allResults)) * 100, 2) . "%)\n";
        echo "Warnings: " . count($warnings) . " (" . round((count($warnings) / count($this->allResults)) * 100, 2) . "%)\n";
        echo "Total Duration: " . round(microtime(true) - $this->startTime, 2) . " seconds\n";
        echo str_repeat("=", 50) . "\n";
    }
}

// Execute tests if run directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $runner = new MainTestRunner();
    $results = $runner->runAllModuleTests();
}
?>