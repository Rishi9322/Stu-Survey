<?php
// Link validation and fixing script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Link Validation and Fixing Tool</h1>";

// Define the correct structure mapping
$linkMappings = [
    // Public pages - accessed from root or any folder
    'index.php' => [
        'from_public' => 'index.php',
        'from_app' => '../public/index.php', 
        'from_core' => '../public/index.php',
        'from_root' => 'public/index.php'
    ],
    'login.php' => [
        'from_public' => 'login.php',
        'from_app' => '../public/login.php',
        'from_core' => '../public/login.php', 
        'from_root' => 'public/login.php'
    ],
    'register.php' => [
        'from_public' => 'register.php',
        'from_app' => '../public/register.php',
        'from_core' => '../public/register.php',
        'from_root' => 'public/register.php'
    ]
];

// Function to check if a file exists
function checkFileExists($basePath, $relativePath) {
    $fullPath = $basePath . $relativePath;
    return file_exists($fullPath);
}

// Function to scan directory for PHP files
function scanForPHPFiles($dir, $relativePath = '') {
    $files = [];
    if (is_dir($dir)) {
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $fullPath = $dir . DIRECTORY_SEPARATOR . $item;
            $currentRelative = $relativePath ? $relativePath . '/' . $item : $item;
            
            if (is_dir($fullPath)) {
                $files = array_merge($files, scanForPHPFiles($fullPath, $currentRelative));
            } elseif (pathinfo($item, PATHINFO_EXTENSION) === 'php') {
                $files[] = [
                    'path' => $fullPath,
                    'relative' => $currentRelative,
                    'dir' => $relativePath
                ];
            }
        }
    }
    return $files;
}

// Get all PHP files in the project
$projectRoot = dirname(__FILE__) . '/../..';
$phpFiles = scanForPHPFiles($projectRoot);

echo "<h2>Scanning " . count($phpFiles) . " PHP files for broken links...</h2>";

$brokenLinks = [];
$fixedLinks = [];

foreach ($phpFiles as $fileInfo) {
    $content = file_get_contents($fileInfo['path']);
    $lines = explode("\n", $content);
    
    foreach ($lines as $lineNum => $line) {
        // Check for href links to PHP files
        if (preg_match_all('/href=["\']([^"\']*\.php[^"\']*)["\']/', $line, $matches)) {
            foreach ($matches[1] as $link) {
                // Skip external links and anchor-only links
                if (strpos($link, 'http') === 0 || strpos($link, '#') === 0) {
                    continue;
                }
                
                // Remove fragments for file existence check
                $linkFile = preg_replace('/#.*$/', '', $link);
                
                // Determine the context (which folder the file is in)
                $context = 'root';
                if (strpos($fileInfo['relative'], 'public/') === 0) {
                    $context = 'public';
                } elseif (strpos($fileInfo['relative'], 'app/') === 0) {
                    $context = 'app';
                } elseif (strpos($fileInfo['relative'], 'core/') === 0) {
                    $context = 'core';
                }
                
                // Check if the link works from the current context
                $basePath = dirname($fileInfo['path']) . '/';
                $targetExists = checkFileExists($basePath, $linkFile);
                
                if (!$targetExists) {
                    $brokenLinks[] = [
                        'file' => $fileInfo['relative'],
                        'line' => $lineNum + 1,
                        'link' => $link,
                        'context' => $context,
                        'full_line' => trim($line)
                    ];
                }
            }
        }
    }
}

// Display results
if (empty($brokenLinks)) {
    echo "<div style='color: green; font-weight: bold;'>✅ All links appear to be working correctly!</div>";
} else {
    echo "<h3 style='color: red;'>❌ Found " . count($brokenLinks) . " potentially broken links:</h3>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>File</th><th>Line</th><th>Broken Link</th><th>Context</th><th>Line Content</th></tr>";
    
    foreach ($brokenLinks as $broken) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($broken['file']) . "</td>";
        echo "<td>" . $broken['line'] . "</td>";
        echo "<td style='color: red;'>" . htmlspecialchars($broken['link']) . "</td>";
        echo "<td>" . $broken['context'] . "</td>";
        echo "<td><code>" . htmlspecialchars($broken['full_line']) . "</code></td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h3>Quick Fix Suggestions:</h3>";
echo "<ul>";
echo "<li><strong>For public pages:</strong> Use relative paths within public folder (e.g., 'login.php' not '../public/login.php')</li>";
echo "<li><strong>For app pages:</strong> Use '../public/' prefix for public pages</li>";
echo "<li><strong>For breadcrumbs:</strong> Use 'index.php' when already in public folder</li>";
echo "<li><strong>For footers/headers:</strong> Use basePath variable with proper folder structure</li>";
echo "</ul>";

?>
<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; width: 100%; }
    th { background-color: #f0f0f0; }
    code { background-color: #f5f5f5; padding: 2px 4px; }
    h1, h2, h3 { color: #333; }
</style>