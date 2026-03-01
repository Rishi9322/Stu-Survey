<?php
// Direct Database Migration - Runs SQL statements in correct order
// Run via browser: http://localhost/stu/run_direct_migration.php

require_once 'core/includes/config.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Direct Database Migration</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 40px auto; padding: 20px; background: #f5f5f5; }
        .success { color: green; padding: 8px 12px; background: #d4edda; border-left: 4px solid #28a745; margin: 8px 0; }
        .error { color: red; padding: 8px 12px; background: #f8d7da; border-left: 4px solid #dc3545; margin: 8px 0; }
        .info { color: #004085; padding: 8px 12px; background: #d1ecf1; border-left: 4px solid #17a2b8; margin: 8px 0; }
        .warn { color: #856404; padding: 8px 12px; background: #fff3cd; border-left: 4px solid #ffc107; margin: 8px 0; }
        h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; }
        .btn { display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 10px 5px; }
        .btn-success { background: #28a745; }
        .btn:hover { opacity: 0.9; }
        pre { background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 12px; }
    </style>
</head>
<body>
    <h1>🗄️ Survey Management Database Migration</h1>
";

$results = [];

function runSQL($conn, $description, $sql) {
    global $results;
    echo "<div><strong>$description</strong></div>";
    
    if (mysqli_query($conn, $sql)) {
        echo "<div class='success'>✅ Success</div>";
        $results[] = ['desc' => $description, 'status' => 'success'];
        return true;
    } else {
        $error = mysqli_error($conn);
        if (stripos($error, 'already exists') !== false || 
            stripos($error, 'Duplicate') !== false) {
            echo "<div class='warn'>⚠️ Already exists - skipped</div>";
            $results[] = ['desc' => $description, 'status' => 'skipped'];
            return true;
        } else {
            echo "<div class='error'>❌ Error: " . htmlspecialchars($error) . "</div>";
            $results[] = ['desc' => $description, 'status' => 'error', 'error' => $error];
            return false;
        }
    }
}

// Step 1: Create surveys table
echo "<h2>Step 1: Create Core Tables</h2>";

runSQL($conn, "Creating 'surveys' table", "
CREATE TABLE IF NOT EXISTS surveys (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('draft', 'active', 'paused', 'completed', 'archived') DEFAULT 'draft',
    target_role ENUM('student', 'teacher', 'both') DEFAULT 'student',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

runSQL($conn, "Creating 'question_sets' table", "
CREATE TABLE IF NOT EXISTS question_sets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    survey_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

runSQL($conn, "Creating 'survey_sessions' table", "
CREATE TABLE IF NOT EXISTS survey_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    survey_id INT NOT NULL,
    session_name VARCHAR(255) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    status ENUM('scheduled', 'active', 'completed', 'cancelled') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Step 2: Add foreign keys
echo "<h2>Step 2: Add Foreign Keys</h2>";

runSQL($conn, "Adding FK: question_sets → surveys", "
ALTER TABLE question_sets 
ADD CONSTRAINT fk_question_sets_survey 
FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE");

runSQL($conn, "Adding FK: survey_sessions → surveys", "
ALTER TABLE survey_sessions 
ADD CONSTRAINT fk_survey_sessions_survey 
FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE");

// Step 3: Extend survey_questions table
echo "<h2>Step 3: Extend survey_questions Table</h2>";

// Check if columns exist before adding
$columnsToAdd = [
    ['survey_id', 'INT NULL', 'Adding survey_id column'],
    ['question_set_id', 'INT NULL', 'Adding question_set_id column'],
    ['display_order', 'INT DEFAULT 0', 'Adding display_order column'],
    ['question_type', "ENUM('rating', 'text', 'multiple_choice') DEFAULT 'rating'", 'Adding question_type column'],
    ['is_template', 'BOOLEAN DEFAULT FALSE', 'Adding is_template column']
];

foreach ($columnsToAdd as $col) {
    $checkResult = mysqli_query($conn, "SHOW COLUMNS FROM survey_questions LIKE '{$col[0]}'");
    if ($checkResult && mysqli_num_rows($checkResult) == 0) {
        runSQL($conn, $col[2], "ALTER TABLE survey_questions ADD COLUMN {$col[0]} {$col[1]}");
    } else {
        echo "<div class='warn'>⚠️ Column '{$col[0]}' already exists - skipped</div>";
    }
}

// Step 4: Add FK constraints to survey_questions (if not exist)
echo "<h2>Step 4: Add FK Constraints to survey_questions</h2>";

// These might fail if already exist, that's OK
runSQL($conn, "Adding FK: survey_questions.survey_id → surveys", "
ALTER TABLE survey_questions
ADD CONSTRAINT fk_questions_survey FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE SET NULL");

runSQL($conn, "Adding FK: survey_questions.question_set_id → question_sets", "
ALTER TABLE survey_questions
ADD CONSTRAINT fk_questions_question_set FOREIGN KEY (question_set_id) REFERENCES question_sets(id) ON DELETE SET NULL");

// Step 5: Extend survey_responses table
echo "<h2>Step 5: Extend survey_responses Table</h2>";

$checkResult = mysqli_query($conn, "SHOW COLUMNS FROM survey_responses LIKE 'survey_session_id'");
if ($checkResult && mysqli_num_rows($checkResult) == 0) {
    runSQL($conn, "Adding survey_session_id column", "ALTER TABLE survey_responses ADD COLUMN survey_session_id INT NULL");
    runSQL($conn, "Adding FK: survey_responses → survey_sessions", "
    ALTER TABLE survey_responses
    ADD CONSTRAINT fk_responses_session FOREIGN KEY (survey_session_id) REFERENCES survey_sessions(id) ON DELETE SET NULL");
} else {
    echo "<div class='warn'>⚠️ Column 'survey_session_id' already exists - skipped</div>";
}

// Step 6: Create indexes
echo "<h2>Step 6: Create Indexes</h2>";

$indexes = [
    ['surveys', 'idx_surveys_status', 'status'],
    ['surveys', 'idx_surveys_target_role', 'target_role'],
    ['surveys', 'idx_surveys_created_by', 'created_by'],
    ['question_sets', 'idx_question_sets_survey_id', 'survey_id'],
    ['survey_sessions', 'idx_survey_sessions_survey_id', 'survey_id'],
    ['survey_sessions', 'idx_survey_sessions_status', 'status']
];

foreach ($indexes as $idx) {
    runSQL($conn, "Creating index {$idx[1]} on {$idx[0]}", "CREATE INDEX {$idx[1]} ON {$idx[0]}({$idx[2]})");
}

// Step 7: Insert sample data
echo "<h2>Step 7: Insert Sample Data</h2>";

// Get admin user ID
$adminResult = mysqli_query($conn, "SELECT id FROM users WHERE role = 'admin' LIMIT 1");
$adminId = 1; // Default
if ($adminResult && $row = mysqli_fetch_assoc($adminResult)) {
    $adminId = $row['id'];
}

// Check if legacy survey exists
$checkLegacy = mysqli_query($conn, "SELECT id FROM surveys WHERE title = 'Legacy Questions Collection' LIMIT 1");
if (!$checkLegacy || mysqli_num_rows($checkLegacy) == 0) {
    runSQL($conn, "Creating 'Legacy Questions Collection' survey", "
    INSERT INTO surveys (title, description, status, target_role, created_by)
    VALUES ('Legacy Questions Collection', 'Automatically created collection of existing individual questions.', 'active', 'both', $adminId)");
}

$checkStudent = mysqli_query($conn, "SELECT id FROM surveys WHERE title = 'Student Satisfaction Survey 2026' LIMIT 1");
if (!$checkStudent || mysqli_num_rows($checkStudent) == 0) {
    runSQL($conn, "Creating 'Student Satisfaction Survey 2026'", "
    INSERT INTO surveys (title, description, status, target_role, created_by)
    VALUES ('Student Satisfaction Survey 2026', 'Comprehensive survey to assess student satisfaction with academic programs.', 'draft', 'student', $adminId)");
}

$checkTeacher = mysqli_query($conn, "SELECT id FROM surveys WHERE title = 'Faculty Evaluation Survey 2026' LIMIT 1");
if (!$checkTeacher || mysqli_num_rows($checkTeacher) == 0) {
    runSQL($conn, "Creating 'Faculty Evaluation Survey 2026'", "
    INSERT INTO surveys (title, description, status, target_role, created_by)
    VALUES ('Faculty Evaluation Survey 2026', 'Annual survey to evaluate faculty performance.', 'draft', 'teacher', $adminId)");
}

// Summary
echo "<h2>📊 Migration Summary</h2>";

$successCount = count(array_filter($results, fn($r) => $r['status'] === 'success'));
$skippedCount = count(array_filter($results, fn($r) => $r['status'] === 'skipped'));
$errorCount = count(array_filter($results, fn($r) => $r['status'] === 'error'));

echo "<div class='info'>";
echo "✅ Successful: $successCount<br>";
echo "⚠️ Skipped (already exist): $skippedCount<br>";
echo "❌ Errors: $errorCount";
echo "</div>";

if ($errorCount == 0) {
    echo "<div class='success'><strong>🎉 Migration completed successfully!</strong></div>";
} else {
    echo "<div class='error'><strong>⚠️ Migration completed with some errors. Review above.</strong></div>";
}

// Verify
echo "<h2>🔍 Verification</h2>";

$tables = ['surveys', 'question_sets', 'survey_sessions'];
foreach ($tables as $table) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM $table");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "<div class='success'>✅ Table '$table' exists with {$row['cnt']} rows</div>";
    } else {
        echo "<div class='error'>❌ Table '$table' verification failed</div>";
    }
}

echo "<hr style='margin: 30px 0;'>";
echo "<div style='text-align: center;'>";
echo "<a href='app/admin/survey_management.php' class='btn btn-success'>🚀 Open Survey Management</a>";
echo "<a href='app/admin/dashboard.php' class='btn'>🏠 Dashboard</a>";
echo "</div>";

echo "</body></html>";

closeConnection($conn);
?>