<?php
try {
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    echo "=== Available Databases ===\n";
    $stmt = $pdo->query('SHOW DATABASES');
    while($row = $stmt->fetch()) {
        echo "- " . $row[0] . "\n";
    }
    
    echo "\n=== Checking for student/feedback related databases ===\n";
    $stmt = $pdo->query('SHOW DATABASES');
    while($row = $stmt->fetch()) {
        if (stripos($row[0], 'student') !== false || stripos($row[0], 'feedback') !== false || stripos($row[0], 'stu') !== false) {
            echo "✅ Found: " . $row[0] . "\n";
        }
    }
} catch(Exception $e) {
    echo 'Database connection error: ' . $e->getMessage() . "\n";
}
?>
