<?php
// Simple API test runner - generates JSON and HTML reports
// Usage: php tests/api/run_api_tests.php

$baseUrl = getenv('BASE_URL') ?: 'http://localhost/stu/api/';
$outDir = __DIR__ . '/../../phpmd_reports/api_reports';

if (!is_dir($outDir)) {
    mkdir($outDir, 0777, true);
}

$endpoints = [
    ['path' => 'api.php', 'method' => 'GET', 'name' => 'API Index'],
    ['path' => 'ai_chat_api.php', 'method' => 'POST', 'name' => 'AI Chat API', 'payload' => ['message' => 'Hello from API test runner']],
    ['path' => 'ai_insights.php', 'method' => 'GET', 'name' => 'AI Insights'],
    ['path' => 'ai_insights_new.php', 'method' => 'GET', 'name' => 'AI Insights (new)'],
    ['path' => 'debug_endpoint.php', 'method' => 'GET', 'name' => 'Debug Endpoint'],
    ['path' => 'markdown_reader.php', 'method' => 'GET', 'name' => 'Markdown Reader'],
    ['path' => 'training_endpoint.php', 'method' => 'POST', 'name' => 'Training Endpoint', 'payload' => ['action' => 'status']],
];

$results = [];
foreach ($endpoints as $ep) {
    $url = rtrim($baseUrl, '/') . '/' . $ep['path'];
    $method = strtoupper($ep['method']);
    $payload = isset($ep['payload']) ? $ep['payload'] : null;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $headers = [
        'Accept: */*',
    ];

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($payload !== null) {
            $body = json_encode($payload);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            $headers[] = 'Content-Type: application/json';
        }
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $start = microtime(true);
    $resp = curl_exec($ch);
    $time = microtime(true) - $start;

    if ($resp === false) {
        $error = curl_error($ch);
        $status = 0;
        $body = '';
    } else {
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $body = substr($resp, $header_size);
        $error = '';
    }

    curl_close($ch);

    $result = [
        'name' => $ep['name'],
        'path' => $ep['path'],
        'url' => $url,
        'method' => $method,
        'status' => $status,
        'ok' => ($status >= 200 && $status < 300),
        'time' => round($time, 3),
        'length' => strlen($body),
        'snippet' => mb_substr($body, 0, 800),
        'error' => $error,
    ];

    $results[] = $result;

    // Write per-endpoint HTML report
    $safe = preg_replace('/[^a-z0-9_-]+/i', '_', $ep['path']);
    $html = "<html><head><meta charset=\"utf-8\"><title>API Test - {$ep['name']}</title>
    <style>body{font-family:Arial,Helvetica,sans-serif} .ok{color:green}.fail{color:red};pre{white-space:pre-wrap;background:#f9f9f9;padding:12px;border:1px solid #ddd}</style>
    </head><body>
    <h1>API Test: {$ep['name']}</h1>
    <p><strong>URL:</strong> <a href=\"{$url}\">{$url}</a></p>
    <p><strong>Method:</strong> {$method}</p>
    <p><strong>Status:</strong> <span class=\"" . ($result['ok'] ? 'ok' : 'fail') . "\">{$status}</span></p>
    <p><strong>Time (s):</strong> {$result['time']}</p>
    <p><strong>Response size (bytes):</strong> {$result['length']}</p>
    <h2>Response snippet</h2>
    <pre>" . htmlspecialchars($result['snippet']) . "</pre>
    ";

    if ($result['error']) {
        $html .= "<h3 style=\"color:red\">Client error</h3><pre>" . htmlspecialchars($result['error']) . "</pre>";
    }

    $html .= "</body></html>";
    file_put_contents($outDir . "/api_{$safe}.html", $html);
}

// Write JSON summary
file_put_contents($outDir . '/api_results.json', json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// Generate index HTML
$indexHtml = "<html><head><meta charset=\"utf-8\"><title>API Test Reports</title>
<style>body{font-family:Arial,Helvetica,sans-serif} table{border-collapse:collapse;width:100%}th,td{padding:8px;border:1px solid #ddd}th{background:#f4f4f4}</style>
</head><body><h1>API Testing Report</h1>
<p>Base URL: <code>" . htmlspecialchars($baseUrl) . "</code></p>
<table><thead><tr><th>Endpoint</th><th>Method</th><th>Status</th><th>Time (s)</th><th>Size</th><th>Report</th></tr></thead><tbody>";

foreach ($results as $r) {
    $safe = preg_replace('/[^a-z0-9_-]+/i', '_', $r['path']);
    $indexHtml .= "<tr><td>" . htmlspecialchars($r['name']) . "<br><small>" . htmlspecialchars($r['path']) . "</small></td><td>" . $r['method'] . "</td><td>" . ($r['ok'] ? '<span style=\"color:green\">' . $r['status'] . '</span>' : '<span style=\"color:red\">' . $r['status'] . '</span>') . "</td><td>" . $r['time'] . "</td><td>" . $r['length'] . "</td><td><a href=\"api_{$safe}.html\">View</a></td></tr>";
}

$indexHtml .= "</tbody></table>
<p>Generated: " . date('c') . "</p>
</body></html>";

file_put_contents($outDir . '/index.html', $indexHtml);

echo "API tests completed. Reports written to: {$outDir}\n";

?>
