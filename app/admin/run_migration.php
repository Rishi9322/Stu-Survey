<?php
// Survey Management Migration Runner
// Execute the database migration for enhanced survey management

require_once '../../core/includes/config.php';

// Read the migration file
$migrationFile = 'database/migrations/002_survey_management_enhancement.sql';
$sqlContent = file_get_contents($migrationFile);

if ($sqlContent === false) {
    die("Error: Could not read migration file: $migrationFile");
}

// Split SQL statements by semicolon and execute each one
$statements = array_filter(array_map('trim', explode(';', $sqlContent)));

$success = 0;
$errors = 0;

echo "<h2>Survey Management Database Migration</h2>\n";
echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px; border-radius: 8px;'>\n";

foreach ($statements as $statement) {
    // Skip empty statements and comments
    if (empty($statement) || strpos(trim($statement), '--') === 0 || strpos(trim($statement), 'SELECT \'') !== false) {
        continue;
    }
    
    echo "<div style='margin: 10px 0; padding: 8px; background: white; border-left: 4px solid #007bff;'>";
    echo "<strong>Executing:</strong> " . substr($statement, 0, 100) . "...<br>";
    
    if (mysqli_query($conn, $statement)) {
        echo "<span style='color: green;'>✓ SUCCESS</span>";
        $success++;
    } else {
        $error = mysqli_error($conn);
        // Some errors are expected (like DROP IF EXISTS)
        if (strpos($error, "doesn't exist") !== false || strpos($error, "duplicate column") !== false) {
            echo "<span style='color: orange;'>⚠ SKIPPED (already exists)</span>";
        } else {
            echo "<span style='color: red;'>✗ ERROR: " . $error . "</span>";
            $errors++;
        }
    }
    echo "</div>\n";
}

echo "<div style='margin-top: 20px; padding: 15px; background: " . ($errors > 0 ? "#fff3cd" : "#d4edda") . "; border-radius: 5px;'>";
echo "<strong>Migration Summary:</strong><br>";
echo "Successful operations: $success<br>";
echo "Errors: $errors<br>";
if ($errors == 0) {
    echo "<span style='color: green; font-weight: bold;'>✅ Migration completed successfully!</span>";
} else {
    echo "<span style='color: orange; font-weight: bold;'>⚠ Migration completed with warnings</span>";
}
echo "</div>";

// Verify the new tables exist
echo "<h3>Verifying New Tables</h3>";
echo "<div style='font-family: monospace; background: #f5f5f5; padding: 15px; border-radius: 8px;'>";

$tables = ['surveys', 'question_sets', 'survey_sessions'];
foreach ($tables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        echo "<div style='color: green;'>✓ Table '$table' created successfully</div>";
    } else {
        echo "<div style='color: red;'>✗ Table '$table' not found</div>";
    }
}

echo "</div>";

// Show sample data
echo "<h3>Sample Survey Data</h3>";
echo "<div style='font-family: monospace; background: #f5f5f5; padding: 15px; border-radius: 8px;'>";

$result = mysqli_query($conn, "SELECT id, title, status, target_role FROM surveys LIMIT 5");
if ($result && mysqli_num_rows($result) > 0) {
    echo "<table style='width: 100%; border-collapse: collapse;'>";
    echo "<tr style='background: #007bff; color: white;'><th style='padding: 8px; border: 1px solid #ddd;'>ID</th><th style='padding: 8px; border: 1px solid #ddd;'>Title</th><th style='padding: 8px; border: 1px solid #ddd;'>Status</th><th style='padding: 8px; border: 1px solid #ddd;'>Target Role</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$row['id']}</td>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$row['title']}</td>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$row['status']}</td>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>{$row['target_role']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div style='color: orange;'>No survey data found (this is expected on first run)</div>";
}

echo "</div>";

// Close connection
closeConnection($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Survey Management Migration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 20px; background: #f8f9fa; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .btn { display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 6px; margin: 10px 5px; font-weight: 600; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; } .btn-success:hover { background: #218838; }
    </style>
</head>
<body>
    <div class="container">
        <div style="text-align: center; margin: 30px 0;">
            <a href="survey_management.php" class="btn btn-success">
                📊 Open Enhanced Survey Management
            </a>
            <a href="dashboard.php" class="btn">
                🏠 Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>