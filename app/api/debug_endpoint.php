<?php
// Temporary debug endpoint to see what's wrong
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "=== DEBUGGING ENDPOINT ISSUES ===\n";

// Check if all required files exist
$files_to_check = [
    '../includes/secure_config.php',
    '../includes/config.php',
    'AdvancedAIProvider.php',
    'DatabaseAI.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "✅ Found: $file\n";
    } else {
        echo "❌ Missing: $file\n";
    }
}

echo "\n=== TESTING INCLUDES ===\n";

try {
    require_once '../../core/includes/secure_config.php';
    echo "✅ SecureConfig loaded\n";
    
    $grok_key = SecureConfig::getGrokApiKey();
    echo "✅ Grok key: " . (empty($grok_key) ? "EMPTY" : "FOUND") . "\n";
    
    $openrouter_key = SecureConfig::getOpenRouterApiKey();
    echo "✅ OpenRouter key: " . (empty($openrouter_key) ? "EMPTY" : "FOUND") . "\n";
    
} catch (Exception $e) {
    echo "❌ SecureConfig error: " . $e->getMessage() . "\n";
}

try {
    require_once '../../core/includes/config.php';
    echo "✅ Config loaded\n";
} catch (Exception $e) {
    echo "❌ Config error: " . $e->getMessage() . "\n";
}

try {
    require_once 'AdvancedAIProvider.php';
    echo "✅ AdvancedAIProvider loaded\n";
    
    $ai = new AdvancedAIProvider();
    echo "✅ AdvancedAIProvider instantiated\n";
    
} catch (Exception $e) {
    echo "❌ AdvancedAIProvider error: " . $e->getMessage() . "\n";
}

try {
    require_once 'DatabaseAI.php';
    echo "✅ DatabaseAI loaded\n";
} catch (Exception $e) {
    echo "❌ DatabaseAI error: " . $e->getMessage() . "\n";
}

echo "\n=== TESTING DATABASE ===\n";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=student_satisfaction_survey", "root", "");
    echo "✅ Database connection successful\n";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
?>
