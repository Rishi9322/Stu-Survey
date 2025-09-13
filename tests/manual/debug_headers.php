<?php
// Start output buffering
ob_start();

// Function to check if headers can be sent
function can_send_headers() {
    return !headers_sent($file, $line);
}

// Function to get current output buffer level
function get_ob_level() {
    return ob_get_level();
}

// Include config file
require_once "../../core/includes/config.php";

echo "<h1>Header Debugging Tool</h1>";

// Check if headers have already been sent
if (!can_send_headers()) {
    echo "<div style='color: red; font-weight: bold;'>";
    echo "Headers already sent in $file on line $line";
    echo "</div>";
} else {
    echo "<div style='color: green; font-weight: bold;'>";
    echo "Headers can still be sent";
    echo "</div>";
}

echo "<h2>Output Buffer Information</h2>";
echo "<p>Current OB Level: " . get_ob_level() . "</p>";

// Session information
session_start();
echo "<h2>Session Information</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// PHP Configuration
echo "<h2>PHP Configuration</h2>";
echo "<table border='1'>";
echo "<tr><th>Setting</th><th>Value</th></tr>";
echo "<tr><td>output_buffering</td><td>" . ini_get('output_buffering') . "</td></tr>";
echo "<tr><td>implicit_flush</td><td>" . ini_get('implicit_flush') . "</td></tr>";
echo "<tr><td>display_errors</td><td>" . ini_get('display_errors') . "</td></tr>";
echo "<tr><td>error_reporting</td><td>" . ini_get('error_reporting') . "</td></tr>";
echo "<tr><td>session.use_trans_sid</td><td>" . ini_get('session.use_trans_sid') . "</td></tr>";
echo "</table>";

// Server Information
echo "<h2>Server Information</h2>";
echo "<pre>";
print_r($_SERVER);
echo "</pre>";

?>

