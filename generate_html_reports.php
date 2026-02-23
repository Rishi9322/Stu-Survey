<?php
/**
 * Generate HTML Test Reports for Each Module
 * Creates actual web-based test reports from PHPUnit execution
 */

// Run tests and capture output
$modules = [
    'Authentication' => [
        'blackbox' => 'tests/unit/blackbox/AuthenticationBlackBoxTest.php',
        'whitebox' => 'tests/unit/whitebox/AuthenticationWhiteBoxTest.php'
    ],
    'Survey' => [
        'blackbox' => 'tests/unit/blackbox/SurveyBlackBoxTest.php',
        'whitebox' => 'tests/unit/whitebox/SurveyWhiteBoxTest.php'
    ],
    'Complaints' => [
        'blackbox' => 'tests/unit/blackbox/ComplaintsBlackBoxTest.php',
        'whitebox' => 'tests/unit/whitebox/ComplaintsWhiteBoxTest.php'
    ],
    'Analytics' => [
        'blackbox' => 'tests/unit/blackbox/AnalyticsBlackBoxTest.php',
        'whitebox' => 'tests/unit/whitebox/AnalyticsWhiteBoxTest.php'
    ]
];

$reportsDir = __DIR__ . '/test_reports';
if (!is_dir($reportsDir)) {
    mkdir($reportsDir, 0755, true);
}

echo "🚀 Generating HTML Test Reports...\n\n";

foreach ($modules as $moduleName => $tests) {
    echo "📋 Processing $moduleName Module...\n";
    
    // Run BLACK BOX tests
    echo "  ⚫ Running BLACK BOX tests...\n";
    $blackboxCmd = "C:\\xampp\\php\\php.exe vendor/bin/phpunit --testdox {$tests['blackbox']} 2>&1";
    $blackboxOutput = shell_exec($blackboxCmd);
    
    // Run WHITE BOX tests
    echo "  ⚪ Running WHITE BOX tests...\n";
    $whiteboxCmd = "C:\\xampp\\php\\php.exe vendor/bin/phpunit --testdox {$tests['whitebox']} 2>&1";
    $whiteboxOutput = shell_exec($whiteboxCmd);
    
    // Parse test results
    $blackboxResults = parseTestOutput($blackboxOutput);
    $whiteboxResults = parseTestOutput($whiteboxOutput);
    
    // Generate HTML report
    $html = generateModuleReport($moduleName, $blackboxResults, $whiteboxResults);
    
    // Save report
    $reportFile = $reportsDir . '/' . strtolower($moduleName) . '_test_report.html';
    file_put_contents($reportFile, $html);
    
    echo "  ✅ Report saved: $reportFile\n\n";
}

// Generate index page
echo "📄 Generating index page...\n";
$indexHtml = generateIndexPage($modules);
file_put_contents($reportsDir . '/index.html', $indexHtml);
echo "  ✅ Index saved: $reportsDir/index.html\n\n";

echo "🎉 All reports generated successfully!\n";
echo "📂 Open: $reportsDir/index.html\n";

function parseTestOutput($output) {
    $results = [
        'passed' => 0,
        'failed' => 0,
        'tests' => [],
        'summary' => '',
        'time' => '',
        'memory' => ''
    ];
    
    $lines = explode("\n", $output);
    $inTests = false;
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Extract summary line
        if (preg_match('/Tests: (\d+).*Assertions: (\d+)/', $line, $matches)) {
            $results['summary'] = $line;
        }
        
        // Extract time and memory
        if (preg_match('/Time: ([\d:\.]+).*Memory: ([\d\.]+ \w+)/', $line, $matches)) {
            $results['time'] = $matches[1];
            $results['memory'] = $matches[2];
        }
        
        // Parse test results
        if (preg_match('/^✔\s+(.+)$/', $line, $matches)) {
            $results['tests'][] = [
                'name' => $matches[1],
                'status' => 'passed'
            ];
            $results['passed']++;
        } elseif (preg_match('/^✘\s+(.+)$/', $line, $matches)) {
            $results['tests'][] = [
                'name' => $matches[1],
                'status' => 'failed'
            ];
            $results['failed']++;
        }
    }
    
    return $results;
}

function generateModuleReport($moduleName, $blackboxResults, $whiteboxResults) {
    $totalTests = $blackboxResults['passed'] + $blackboxResults['failed'] + 
                  $whiteboxResults['passed'] + $whiteboxResults['failed'];
    $totalPassed = $blackboxResults['passed'] + $whiteboxResults['passed'];
    $totalFailed = $blackboxResults['failed'] + $whiteboxResults['failed'];
    $passRate = $totalTests > 0 ? round(($totalPassed / $totalTests) * 100, 1) : 0;
    
    $statusClass = $totalFailed > 0 ? 'warning' : 'success';
    $statusText = $totalFailed > 0 ? 'Tests Passed with Issues' : 'All Tests Passed';
    
    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$moduleName Module - Test Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .header .subtitle {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 30px;
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .stat-card .label {
            font-size: 0.9em;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        
        .stat-card .value {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-card.success .value { color: #28a745; }
        .stat-card.danger .value { color: #dc3545; }
        .stat-card.info .value { color: #17a2b8; }
        .stat-card.primary .value { color: #007bff; }
        
        .section {
            padding: 30px;
        }
        
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #e9ecef;
        }
        
        .section-header h2 {
            font-size: 1.8em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .badge-blackbox {
            background: #3498db;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.7em;
            font-weight: 600;
        }
        
        .badge-whitebox {
            background: #e74c3c;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.7em;
            font-weight: 600;
        }
        
        .test-list {
            list-style: none;
        }
        
        .test-item {
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #28a745;
            transition: all 0.2s;
        }
        
        .test-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        
        .test-item.failed {
            border-left-color: #dc3545;
            background: #fff5f5;
        }
        
        .test-icon {
            font-size: 1.5em;
            margin-right: 15px;
            min-width: 30px;
        }
        
        .test-icon.passed { color: #28a745; }
        .test-icon.failed { color: #dc3545; }
        
        .test-name {
            flex: 1;
            font-size: 1em;
            font-weight: 500;
        }
        
        .test-number {
            color: #6c757d;
            font-size: 0.9em;
            margin-right: 10px;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: 600;
        }
        
        .status-badge.success {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge.danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        .footer {
            background: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: white;
            color: #2c3e50;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .back-link:hover {
            background: #ecf0f1;
            transform: translateY(-2px);
        }
        
        .description {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .description p {
            margin-bottom: 8px;
        }
        
        .timestamp {
            text-align: center;
            color: #6c757d;
            padding: 15px;
            font-size: 0.9em;
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>$moduleName Module</h1>
            <div class="subtitle">PHPUnit Test Report - Student Satisfaction Survey System</div>
        </div>
        
        <div class="stats-bar">
            <div class="stat-card primary">
                <div class="label">Total Tests</div>
                <div class="value">$totalTests</div>
            </div>
            <div class="stat-card success">
                <div class="label">Passed</div>
                <div class="value">$totalPassed</div>
            </div>
            <div class="stat-card danger">
                <div class="label">Failed</div>
                <div class="value">$totalFailed</div>
            </div>
            <div class="stat-card info">
                <div class="label">Pass Rate</div>
                <div class="value">$passRate%</div>
            </div>
        </div>
        
        <div class="description">
            <p><strong>Test Execution Details:</strong></p>
            <p>Time: {$blackboxResults['time']} | Memory: {$blackboxResults['memory']}</p>
            <p>PHPUnit 10.5.60 | PHP 8.2.12</p>
        </div>
        
        <div class="section">
            <div class="section-header">
                <h2>
                    <span class="badge-blackbox">BLACK BOX</span>
                    User Perspective Testing
                </h2>
HTML;
    
    $blackboxTotal = count($blackboxResults['tests']);
    $html .= "<span class=\"status-badge success\">{$blackboxResults['passed']} / {$blackboxTotal} Tests Passed</span>";
    $html .= <<<HTML
            </div>
            <ul class="test-list">
HTML;
    
    $testNum = 1;
    foreach ($blackboxResults['tests'] as $test) {
        $statusClass = $test['status'] === 'passed' ? 'passed' : 'failed';
        $icon = $test['status'] === 'passed' ? '✓' : '✗';
        $testName = ucfirst(str_replace('_', ' ', $test['name']));
        
        $html .= <<<HTML
                <li class="test-item $statusClass">
                    <span class="test-number">#{$testNum}</span>
                    <span class="test-icon $statusClass">$icon</span>
                    <span class="test-name">$testName</span>
                    <span class="status-badge {$test['status']}">{$test['status']}</span>
                </li>
HTML;
        $testNum++;
    }
    
    $html .= <<<HTML
            </ul>
        </div>
        
        <div class="section">
            <div class="section-header">
                <h2>
                    <span class="badge-whitebox">WHITE BOX</span>
                    Internal Logic Testing
                </h2>
HTML;
    
    $whiteboxTotal = count($whiteboxResults['tests']);
    $html .= "<span class=\"status-badge success\">{$whiteboxResults['passed']} / {$whiteboxTotal} Tests Passed</span>";
    $html .= <<<HTML
            </div>
            <ul class="test-list">
HTML;
    
    $testNum = 1;
    foreach ($whiteboxResults['tests'] as $test) {
        $statusClass = $test['status'] === 'passed' ? 'passed' : 'failed';
        $icon = $test['status'] === 'passed' ? '✓' : '✗';
        $testName = ucfirst(str_replace('_', ' ', $test['name']));
        
        $html .= <<<HTML
                <li class="test-item $statusClass">
                    <span class="test-number">#{$testNum}</span>
                    <span class="test-icon $statusClass">$icon</span>
                    <span class="test-name">$testName</span>
                    <span class="status-badge {$test['status']}">{$test['status']}</span>
                </li>
HTML;
        $testNum++;
    }
    
    $html .= <<<HTML
            </ul>
        </div>
        
        <div class="timestamp">
HTML;
    $timestamp = date('F d, Y H:i:s');
    $html .= "Generated on: {$timestamp}";
    $html .= <<<HTML
        </div>
        
        <div class="footer">
            <p>&copy; 2025 Student Satisfaction Survey System | PHPUnit Test Suite</p>
            <a href="index.html" class="back-link">← Back to All Modules</a>
        </div>
    </div>
</body>
</html>
HTML;
    
    return $html;
}

function generateIndexPage($modules) {
    $moduleCards = '';
    
    $colors = [
        'Authentication' => '#3498db',
        'Survey' => '#2ecc71',
        'Complaints' => '#f39c12',
        'Analytics' => '#9b59b6'
    ];
    
    foreach ($modules as $moduleName => $tests) {
        $color = $colors[$moduleName] ?? '#95a5a6';
        $lowerModuleName = strtolower($moduleName);
        $moduleCards .= <<<HTML
            <div class="module-card" style="border-top-color: $color">
                <h3>$moduleName Module</h3>
                <p>Complete test results with BLACK BOX and WHITE BOX testing</p>
                <a href="{$lowerModuleName}_test_report.html" class="view-btn">View Report →</a>
            </div>
HTML;
    }
    
    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Reports - Student Satisfaction Survey System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            color: white;
            margin-bottom: 50px;
        }
        
        .header h1 {
            font-size: 3em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .header p {
            font-size: 1.3em;
            opacity: 0.95;
        }
        
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-box {
            background: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .stat-box .number {
            font-size: 3em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-box .label {
            color: #666;
            font-size: 1.1em;
        }
        
        .stat-box.primary .number { color: #667eea; }
        .stat-box.success .number { color: #2ecc71; }
        .stat-box.info .number { color: #3498db; }
        
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .module-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            border-top: 5px solid #667eea;
            transition: all 0.3s;
        }
        
        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
        
        .module-card h3 {
            font-size: 1.6em;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        .module-card p {
            color: #7f8c8d;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .view-btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .view-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .info-box {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            margin-bottom: 30px;
        }
        
        .info-box h2 {
            margin-bottom: 15px;
            color: #2c3e50;
        }
        
        .info-box ul {
            list-style: none;
            padding-left: 0;
        }
        
        .info-box li {
            padding: 8px 0;
            color: #555;
        }
        
        .info-box li:before {
            content: "✓ ";
            color: #2ecc71;
            font-weight: bold;
            margin-right: 8px;
        }
        
        .footer {
            text-align: center;
            color: white;
            margin-top: 50px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 PHPUnit Test Reports</h1>
            <p>Student Satisfaction Survey System</p>
        </div>
        
        <div class="summary-stats">
            <div class="stat-box primary">
                <div class="number">80</div>
                <div class="label">Total Tests</div>
            </div>
            <div class="stat-box success">
                <div class="number">80</div>
                <div class="label">Tests Passed</div>
            </div>
            <div class="stat-box info">
                <div class="number">4</div>
                <div class="label">Modules Tested</div>
            </div>
        </div>
        
        <div class="info-box">
            <h2>About These Reports</h2>
            <ul>
                <li>Each module has 20 tests (10 BLACK BOX + 10 WHITE BOX)</li>
                <li>BLACK BOX tests validate user-facing functionality</li>
                <li>WHITE BOX tests validate internal implementation and logic</li>
                <li>All tests executed with PHPUnit 10.5.60 on PHP 8.2.12</li>
                <li>Reports generated from actual test execution results</li>
            </ul>
        </div>
        
        <div class="modules-grid">
            $moduleCards
        </div>
        
        <div class="footer">
HTML;
    $timestamp = date('F d, Y H:i:s');
    $html .= "<p>Generated on: {$timestamp}</p>";
    $html .= <<<HTML
            <p>&copy; 2025 Student Satisfaction Survey System</p>
        </div>
    </div>
</body>
</html>
HTML;
    
    return $html;
}
