<?php
// Markdown file reader API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Initialize session for security
session_start();

// Define allowed files for security
$allowedFiles = [
    'REORGANIZATION_GUIDE.md',
    'README.md', 
    'AI_COMPREHENSIVE_REPORT.md',
    'DEPLOYMENT_SUCCESS.md',
    'python_path_fix_guide.md',
    'docs/file-structure.md'
];

function getMarkdownContent($filename) {
    $basePath = '../../'; // From app/api to root
    
    // Security check - only allow predefined files
    global $allowedFiles;
    if (!in_array($filename, $allowedFiles)) {
        return ['error' => 'File not allowed', 'status' => 403];
    }
    
    $filepath = $basePath . $filename;
    
    if (!file_exists($filepath)) {
        return ['error' => 'File not found', 'status' => 404];
    }
    
    $content = file_get_contents($filepath);
    if ($content === false) {
        return ['error' => 'Could not read file', 'status' => 500];
    }
    
    return [
        'filename' => $filename,
        'content' => $content,
        'status' => 200
    ];
}

// Handle GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $filename = $_GET['file'] ?? '';
    
    if (empty($filename)) {
        http_response_code(400);
        echo json_encode(['error' => 'File parameter is required']);
        exit;
    }
    
    $result = getMarkdownContent($filename);
    http_response_code($result['status']);
    echo json_encode($result);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>