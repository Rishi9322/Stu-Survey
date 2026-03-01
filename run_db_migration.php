<?php
// Standalone Database Migration Script
// Run this file directly via browser: http://localhost/stu/run_db_migration.php

require_once 'core/includes/config.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Migration</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 40px auto; padding: 20px; background: #f5f5f5; }
        .success { color: green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin: 10px 0; }
        .info { color: #004085; padding: 10px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px; margin: 10px 0; }
        .statement { background: white; padding: 8px; margin: 5px 0; border-left: 4px solid #007bff; font-family: monospace; font-size: 12px; }
        h1 { color: #333; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 10px 5px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>🗄️ Survey Management Database Migration</h1>
";

// Read migration file
$migrationFile = __DIR__ . '/database/migrations/002_survey_management_enhancement.sql';

if (!file_exists($migrationFile)) {
    echo "<div class='error'>❌ Migration file not found: $migrationFile</div>";
    echo "</body></html>";
    exit;
}

$sqlContent = file_get_contents($migrationFile);
if ($sqlContent === false) {
    echo "<div class='error'>❌ Could not read migration file</div>";
    echo "</body></html>";
    exit;
}

echo "<div class='info'>📄 Migration file loaded successfully</div>";

// Split SQL statements
$statements = explode(';', $sqlContent);
$success = 0;
$skipped = 0;
$errors = 0;

echo "<h2>Executing Migration...</h2>";

foreach ($statements as $statement) {
    $statement = trim($statement);
    
    // Skip empty statements, comments, and SELECT messages
    if (empty($statement) || 
        strpos($statement, '--') === 0 || 
        strpos($statement, '/*') === 0 ||
        stripos($statement, "SELECT 'Survey Management") !== false) {
        continue;
    }
    
    echo "<div class='statement'>" . htmlspecialchars(substr($statement, 0, 150)) . "...</div>";
    
    if (mysqli_query($conn, $statement)) {
        echo "<div class='success'>✅ Success</div>";
        $success++;
    } else {
        $error = mysqli_error($conn);
        
        // Check if it's a harmless error (table already exists, column already exists, etc.)
        if (stripos($error, "already exists") !== false || 
            stripos($error, "duplicate column") !== false ||
            stripos($error, "duplicate key") !== false) {
            echo "<div class='info'>⚠️ Skipped (already exists)</div>";
            $skipped++;
        } else {
            echo "<div class='error'>❌ Error: " . htmlspecialchars($error) . "</div>";
            $errors++;
        }
    }
}

echo "<hr>";
echo "<h2>Migration Summary</h2>";
echo "<div class='info'>";
echo "✅ Successful operations: $success<br>";
echo "⚠️ Skipped operations: $skipped<br>";
echo "❌ Errors: $errors<br>";
echo "</div>";

if ($errors == 0) {
    echo "<div class='success'><strong>🎉 Migration completed successfully!</strong></div>";
} else {
    echo "<div class='error'><strong>⚠️ Migration completed with some errors. Please review above.</strong></div>";
}

// Verify tables
echo "<h2>Verifying Database Tables</h2>";
$requiredTables = ['surveys', 'question_sets', 'survey_sessions'];
$allExist = true;

foreach ($requiredTables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if ($result && mysqli_num_rows($result) > 0) {
        echo "<div class='success'>✅ Table '$table' exists</div>";
    } else {
        echo "<div class='error'>❌ Table '$table' not found</div>";
        $allExist = false;
    }
}

// Check extended columns in survey_questions
echo "<h3>Checking survey_questions table extensions...</h3>";
$result = mysqli_query($conn, "SHOW COLUMNS FROM survey_questions LIKE 'survey_id'");
if ($result && mysqli_num_rows($result) > 0) {
    echo "<div class='success'>✅ survey_questions.survey_id column exists</div>";
} else {
    echo "<div class='error'>❌ survey_questions.survey_id column missing</div>";
    $allExist = false;
}

// Show sample data
echo "<h2>Sample Survey Data</h2>";
$result = mysqli_query($conn, "SELECT id, title, status, target_role, created_at FROM surveys ORDER BY id LIMIT 5");

if ($result && mysqli_num_rows($result) > 0) {
    echo "<table style='width:100%; border-collapse: collapse; background: white;'>";
    echo "<tr style='background: #007bff; color: white;'>";
    echo "<th style='padding: 10px; border: 1px solid #ddd;'>ID</th>";
    echo "<th style='padding: 10px; border: 1px solid #ddd;'>Title</th>";
    echo "<th style='padding: 10px; border: 1px solid #ddd;'>Status</th>";
    echo "<th style='padding: 10px; border: 1px solid #ddd;'>Target Role</th>";
    echo "<th style='padding: 10px; border: 1px solid #ddd;'>Created</th>";
    echo "</tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . $row['id'] . "</td>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'><span style='padding: 4px 8px; background: " . 
             ($row['status'] == 'active' ? '#28a745' : '#6c757d') . "; color: white; border-radius: 4px;'>" . 
             $row['status'] . "</span></td>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . ucfirst($row['target_role']) . "</td>";
        echo "<td style='padding: 8px; border: 1px solid #ddd;'>" . date('M j, Y', strtotime($row['created_at'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<div class='success' style='margin-top: 20px;'>✅ Found " . mysqli_num_rows($result) . " survey(s) in database</div>";
} else {
    echo "<div class='info'>ℹ️ No surveys found yet (this is normal on first run)</div>";
}

echo "<hr>";
echo "<div style='text-align: center; margin: 30px 0;'>";
if ($allExist) {
    echo "<a href='app/admin/survey_management.php' class='btn' style='background: #28a745;'>🚀 Open Survey Management</a>";
} else {
    echo "<a href='javascript:location.reload()' class='btn' style='background: #dc3545;'>🔄 Retry Migration</a>";
}
echo "<a href='app/admin/dashboard.php' class='btn'>🏠 Dashboard</a>";
echo "</div>";

echo "</body></html>";

closeConnection($conn);
?>