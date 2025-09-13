<?php
require_once '../../core/includes/config.php';

echo "=== Checking suggestions_complaints table structure ===\n";
try {
    global $pdo;
    $stmt = $pdo->query("DESCRIBE suggestions_complaints");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
    echo "\n=== Sample data from suggestions_complaints ===\n";
    $stmt = $pdo->query("SELECT * FROM suggestions_complaints LIMIT 3");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($rows)) {
        echo "No data found in table\n";
    } else {
        foreach ($rows as $row) {
            echo json_encode($row, JSON_PRETTY_PRINT) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
