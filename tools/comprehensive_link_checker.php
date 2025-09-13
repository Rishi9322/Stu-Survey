<?php
/**
 * Comprehensive Link Checker
 * Scans all PHP files to identify broken links and missing files
 */

function scanDirectory($dir, &$files = []) {
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            scanDirectory($path, $files);
        } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $files[] = $path;
        }
    }
    return $files;
}

function checkLinksInFile($filePath, $basePath) {
    $content = file_get_contents($filePath);
    $issues = [];
    
    // Find all href attributes
    if (preg_match_all('/href\s*=\s*[\'"]([^\'"]+)[\'"]/i', $content, $matches)) {
        foreach ($matches[1] as $link) {
            // Skip external links and javascript
            if (strpos($link, 'http') === 0 || strpos($link, 'javascript:') === 0 || strpos($link, 'mailto:') === 0 || strpos($link, '#') === 0) {
                continue;
            }
            
            // Convert relative path to absolute
            $linkPath = $basePath;
            if (strpos($link, '../') === 0) {
                $linkPath = dirname($filePath);
                $linkParts = explode('/', $link);
                foreach ($linkParts as $part) {
                    if ($part === '..') {
                        $linkPath = dirname($linkPath);
                    } elseif ($part !== '' && $part !== '.') {
                        $linkPath .= '/' . $part;
                    }
                }
            } else {
                $linkPath .= '/' . ltrim($link, '/');
            }
            
            // Check if file exists
            if (!file_exists($linkPath) && !is_dir($linkPath)) {
                $issues[] = [
                    'type' => 'broken_link',
                    'link' => $link,
                    'resolved_path' => $linkPath,
                    'line' => substr_count(substr($content, 0, strpos($content, $link)), "\n") + 1
                ];
            }
        }
    }
    
    // Find all src attributes (for images, scripts, etc.)
    if (preg_match_all('/src\s*=\s*[\'"]([^\'"]+)[\'"]/i', $content, $matches)) {
        foreach ($matches[1] as $src) {
            // Skip external sources and data URLs
            if (strpos($src, 'http') === 0 || strpos($src, 'data:') === 0) {
                continue;
            }
            
            // Convert relative path to absolute
            $srcPath = $basePath;
            if (strpos($src, '../') === 0) {
                $srcPath = dirname($filePath);
                $srcParts = explode('/', $src);
                foreach ($srcParts as $part) {
                    if ($part === '..') {
                        $srcPath = dirname($srcPath);
                    } elseif ($part !== '' && $part !== '.') {
                        $srcPath .= '/' . $part;
                    }
                }
            } else {
                $srcPath .= '/' . ltrim($src, '/');
            }
            
            // Check if file exists
            if (!file_exists($srcPath)) {
                $issues[] = [
                    'type' => 'missing_resource',
                    'resource' => $src,
                    'resolved_path' => $srcPath,
                    'line' => substr_count(substr($content, 0, strpos($content, $src)), "\n") + 1
                ];
            }
        }
    }
    
    // Check for include/require statements
    if (preg_match_all('/(include|require)(_once)?\s*\(?[\'"]([^\'"]+)[\'"]\)?/i', $content, $matches)) {
        foreach ($matches[3] as $includePath) {
            // Convert relative path to absolute
            $fullIncludePath = $basePath;
            if (strpos($includePath, '../') === 0) {
                $fullIncludePath = dirname($filePath);
                $includeParts = explode('/', $includePath);
                foreach ($includeParts as $part) {
                    if ($part === '..') {
                        $fullIncludePath = dirname($fullIncludePath);
                    } elseif ($part !== '' && $part !== '.') {
                        $fullIncludePath .= '/' . $part;
                    }
                }
            } else {
                $fullIncludePath .= '/' . ltrim($includePath, '/');
            }
            
            // Check if file exists
            if (!file_exists($fullIncludePath)) {
                $issues[] = [
                    'type' => 'missing_include',
                    'include' => $includePath,
                    'resolved_path' => $fullIncludePath,
                    'line' => substr_count(substr($content, 0, strpos($content, $includePath)), "\n") + 1
                ];
            }
        }
    }
    
    return $issues;
}

// Main execution
echo "<h1>Comprehensive Link Checker Report</h1>\n";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;}h1,h2{color:#333;}table{border-collapse:collapse;width:100%;margin:10px 0;}th,td{border:1px solid #ddd;padding:8px;text-align:left;}th{background:#f4f4f4;}.error{color:red;}.warning{color:orange;}.success{color:green;}</style>\n";

$basePath = dirname(__DIR__);
$files = scanDirectory($basePath);

$totalIssues = 0;
$fileIssues = [];

foreach ($files as $file) {
    $issues = checkLinksInFile($file, $basePath);
    if (!empty($issues)) {
        $fileIssues[$file] = $issues;
        $totalIssues += count($issues);
    }
}

if ($totalIssues === 0) {
    echo "<div class='success'><h2>✅ No broken links found!</h2></div>\n";
} else {
    echo "<div class='error'><h2>❌ Found {$totalIssues} issues across " . count($fileIssues) . " files</h2></div>\n";
    
    foreach ($fileIssues as $file => $issues) {
        $relativePath = str_replace($basePath . '/', '', $file);
        echo "<h3>File: {$relativePath}</h3>\n";
        echo "<table>\n";
        echo "<tr><th>Issue Type</th><th>Resource</th><th>Resolved Path</th><th>Line</th></tr>\n";
        
        foreach ($issues as $issue) {
            $typeClass = $issue['type'] === 'broken_link' ? 'error' : 'warning';
            $typeLabel = ucwords(str_replace('_', ' ', $issue['type']));
            $resource = $issue['link'] ?? $issue['resource'] ?? $issue['include'];
            
            echo "<tr class='{$typeClass}'>";
            echo "<td>{$typeLabel}</td>";
            echo "<td>{$resource}</td>";
            echo "<td>{$issue['resolved_path']}</td>";
            echo "<td>{$issue['line']}</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    }
}

echo "\n<hr>\n";
echo "<p><strong>Scan completed:</strong> " . count($files) . " PHP files checked</p>\n";
echo "<p><strong>Total issues found:</strong> {$totalIssues}</p>\n";
echo "<p><strong>Generated at:</strong> " . date('Y-m-d H:i:s') . "</p>\n";
?>