<?php
/**
 * Enhanced Test Report Generator
 * Generates comprehensive HTML and JSON reports from Codeception test results
 */

// Create reports directory if it doesn't exist
$reportsDir = __DIR__ . '/test_reports';
if (!is_dir($reportsDir)) {
    mkdir($reportsDir, 0755, true);
}

// Run tests and capture output
$testCommand = 'C:\\xampp\\php\\php.exe vendor/bin/codecept run --json --html --xml --steps';
$output = [];
$returnVar = 0;
exec($testCommand . ' 2>&1', $output, $returnVar);

// Parse the output to extract test results
$testResults = parseTestResults($output);

// Generate comprehensive HTML report
generateHTMLReport($testResults, $reportsDir . '/comprehensive_test_report.html');

// Generate JSON summary
generateJSONReport($testResults, $reportsDir . '/test_results.json');

// Generate CSV for data analysis
generateCSVReport($testResults, $reportsDir . '/test_results.csv');

// Copy existing reports
if (file_exists('tests/_output/report.html')) {
    copy('tests/_output/report.html', $reportsDir . '/codeception_report.html');
}
if (file_exists('tests/_output/report.xml')) {
    copy('tests/_output/report.xml', $reportsDir . '/codeception_report.xml');
}

echo "✅ Test reports generated successfully!\n";
echo "📁 Reports location: $reportsDir\n";
echo "📊 Available reports:\n";
echo "  - comprehensive_test_report.html (Custom detailed report)\n";
echo "  - codeception_report.html (Standard Codeception HTML)\n";
echo "  - codeception_report.xml (Standard Codeception XML)\n";
echo "  - test_results.json (JSON summary)\n";
echo "  - test_results.csv (CSV for data analysis)\n";

function parseTestResults($output) {
    $results = [
        'summary' => [
            'total' => 0,
            'passed' => 0,
            'failed' => 0,
            'errors' => 0,
            'skipped' => 0,
            'assertions' => 0
        ],
        'tests' => [],
        'failures' => [],
        'errors' => [],
        'execution_time' => 0,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    $currentTest = null;
    $inFailure = false;
    $inError = false;
    $failureText = '';
    $errorText = '';
    
    foreach ($output as $line) {
        // Parse summary line
        if (preg_match('/Tests: (\d+), Assertions: (\d+), Errors: (\d+), Failures: (\d+)/', $line, $matches)) {
            $results['summary']['total'] = (int)$matches[1];
            $results['summary']['assertions'] = (int)$matches[2];
            $results['summary']['errors'] = (int)$matches[3];
            $results['summary']['failed'] = (int)$matches[4];
            $results['summary']['passed'] = $results['summary']['total'] - $results['summary']['failed'] - $results['summary']['errors'];
        }
        
        // Parse test lines
        if (preg_match('/^\d+\) (.+): (.+)$/', $line, $matches)) {
            $currentTest = [
                'class' => $matches[1],
                'method' => $matches[2],
                'status' => 'unknown',
                'message' => ''
            ];
            $results['tests'][] = $currentTest;
        }
        
        // Parse test file paths
        if (preg_match('/Test\s+(.+\.php):(.+)$/', $line, $matches)) {
            if ($currentTest) {
                $results['tests'][count($results['tests'])-1]['file'] = $matches[1];
            }
        }
        
        // Detect failures
        if (strpos($line, 'Failed asserting') !== false || strpos($line, 'Password validation failed') !== false) {
            if ($currentTest) {
                $results['tests'][count($results['tests'])-1]['status'] = 'failed';
                $results['failures'][] = $currentTest;
            }
        }
        
        // Detect errors  
        if (strpos($line, '[Error]') !== false || strpos($line, '[InjectionException]') !== false) {
            if ($currentTest) {
                $results['tests'][count($results['tests'])-1]['status'] = 'error';
                $results['errors'][] = $currentTest;
            }
        }
    }
    
    return $results;
}

function generateHTMLReport($results, $filename) {
    $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Codeception Test Report - Student Feedback System</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: #2c3e50; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .metric { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .metric h3 { margin: 0 0 10px 0; color: #34495e; }
        .metric .value { font-size: 2em; font-weight: bold; }
        .passed .value { color: #27ae60; }
        .failed .value { color: #e74c3c; }
        .errors .value { color: #f39c12; }
        .total .value { color: #3498db; }
        
        .section { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .section h2 { margin-top: 0; color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        
        .test-grid { display: grid; gap: 10px; }
        .test-item { padding: 15px; border-left: 4px solid #bdc3c7; background: #ecf0f1; border-radius: 4px; }
        .test-item.passed { border-left-color: #27ae60; background: #d5f4e6; }
        .test-item.failed { border-left-color: #e74c3c; background: #fdf2f2; }
        .test-item.error { border-left-color: #f39c12; background: #fef9e7; }
        .test-name { font-weight: bold; margin-bottom: 5px; }
        .test-file { font-size: 0.9em; color: #7f8c8d; }
        
        .chart-container { height: 300px; margin: 20px 0; }
        .progress-bar { background: #ecf0f1; height: 20px; border-radius: 10px; overflow: hidden; margin: 10px 0; }
        .progress-fill { height: 100%; transition: width 0.3s ease; }
        .progress-passed { background: #27ae60; }
        .progress-failed { background: #e74c3c; }
        .progress-errors { background: #f39c12; }
        
        .footer { text-align: center; margin-top: 40px; padding: 20px; color: #7f8c8d; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Codeception Test Report</h1>
            <p>Student Feedback System - Generated: ' . $results['timestamp'] . '</p>
        </div>
        
        <div class="summary">
            <div class="metric total">
                <h3>Total Tests</h3>
                <div class="value">' . $results['summary']['total'] . '</div>
            </div>
            <div class="metric passed">
                <h3>Passed</h3>
                <div class="value">' . $results['summary']['passed'] . '</div>
            </div>
            <div class="metric failed">
                <h3>Failed</h3>
                <div class="value">' . $results['summary']['failed'] . '</div>
            </div>
            <div class="metric errors">
                <h3>Errors</h3>
                <div class="value">' . $results['summary']['errors'] . '</div>
            </div>
        </div>
        
        <div class="section">
            <h2>📊 Test Progress Overview</h2>
            <div style="margin: 20px 0;">
                <strong>Overall Progress:</strong>
                <div class="progress-bar">
                    <div class="progress-fill progress-passed" style="width: ' . ($results['summary']['total'] > 0 ? ($results['summary']['passed'] / $results['summary']['total'] * 100) : 0) . '%"></div>
                </div>
                <p>✅ Passed: ' . $results['summary']['passed'] . ' (' . ($results['summary']['total'] > 0 ? round($results['summary']['passed'] / $results['summary']['total'] * 100, 1) : 0) . '%)</p>
                <p>❌ Failed: ' . $results['summary']['failed'] . ' (' . ($results['summary']['total'] > 0 ? round($results['summary']['failed'] / $results['summary']['total'] * 100, 1) : 0) . '%)</p>
                <p>⚠️  Errors: ' . $results['summary']['errors'] . ' (' . ($results['summary']['total'] > 0 ? round($results['summary']['errors'] / $results['summary']['total'] * 100, 1) : 0) . '%)</p>
            </div>
        </div>';
        
    if (!empty($results['failures'])) {
        $html .= '<div class="section">
            <h2>❌ Failed Tests</h2>
            <div class="test-grid">';
        foreach ($results['failures'] as $test) {
            $html .= '<div class="test-item failed">
                <div class="test-name">' . htmlspecialchars($test['class'] . '::' . $test['method']) . '</div>
                <div class="test-file">' . htmlspecialchars($test['file'] ?? 'Unknown file') . '</div>
            </div>';
        }
        $html .= '</div></div>';
    }
    
    if (!empty($results['errors'])) {
        $html .= '<div class="section">
            <h2>⚠️ Errors</h2>
            <div class="test-grid">';
        foreach ($results['errors'] as $test) {
            $html .= '<div class="test-item error">
                <div class="test-name">' . htmlspecialchars($test['class'] . '::' . $test['method']) . '</div>
                <div class="test-file">' . htmlspecialchars($test['file'] ?? 'Unknown file') . '</div>
            </div>';
        }
        $html .= '</div></div>';
    }
    
    $html .= '
        <div class="section">
            <h2>🔧 Recommendations</h2>
            <ul>
                <li><strong>Fix Dependency Injection:</strong> Update test suite configurations to resolve missing Tester classes</li>
                <li><strong>Database Setup:</strong> Ensure TestDatabase class is properly loaded for unit tests</li>
                <li><strong>Environment Configuration:</strong> Verify test database and server configurations</li>
                <li><strong>Code Coverage:</strong> Add code coverage analysis for better testing insights</li>
                <li><strong>Test Data:</strong> Set up proper test fixtures and sample data</li>
            </ul>
        </div>
        
        <div class="footer">
            <p>Generated by Enhanced Codeception Test Runner | Student Feedback System</p>
        </div>
    </div>
</body>
</html>';

    file_put_contents($filename, $html);
}

function generateJSONReport($results, $filename) {
    $jsonData = [
        'generated_at' => date('c'),
        'system' => 'Student Feedback System',
        'framework' => 'Codeception',
        'summary' => $results['summary'],
        'test_details' => $results['tests'],
        'analysis' => [
            'success_rate' => $results['summary']['total'] > 0 ? round($results['summary']['passed'] / $results['summary']['total'] * 100, 2) : 0,
            'main_issues' => [
                'Dependency injection errors',
                'Missing TestDatabase class',
                'Configuration issues'
            ],
            'recommendations' => [
                'Fix namespace configurations',
                'Setup test database',
                'Update tester class paths',
                'Add proper test fixtures'
            ]
        ]
    ];
    
    file_put_contents($filename, json_encode($jsonData, JSON_PRETTY_PRINT));
}

function generateCSVReport($results, $filename) {
    $csv = fopen($filename, 'w');
    
    // Write header
    fputcsv($csv, ['Test Class', 'Test Method', 'Status', 'File']);
    
    // Write test data
    foreach ($results['tests'] as $test) {
        fputcsv($csv, [
            $test['class'] ?? 'Unknown',
            $test['method'] ?? 'Unknown',
            $test['status'] ?? 'Unknown',
            $test['file'] ?? 'Unknown'
        ]);
    }
    
    fclose($csv);
}
?>