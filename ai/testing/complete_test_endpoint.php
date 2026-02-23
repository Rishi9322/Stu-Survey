<?php
// Prevent any output before JSON
ob_start();
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

require_once __DIR__ . '/../../core/includes/config.php';
require_once __DIR__ . '/../../core/includes/secure_config.php';
require_once __DIR__ . '/../engines/AdvancedAIProvider.php';
require_once __DIR__ . '/../../app/admin/DatabaseAI.php';

// Clean any buffer and set JSON header
ob_clean();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'test_security':
            testSecurity();
            break;
            
        case 'test_database':
            testDatabase();
            break;
            
        case 'test_ai_model':
            testAIModel();
            break;
            
        case 'test_database_ai':
            testDatabaseAI();
            break;
            
        case 'test_search_analysis':
            testSearchAnalysis();
            break;
            
        case 'test_custom_query':
            testCustomQuery();
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    ob_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => [
            'file' => basename($e->getFile()),
            'line' => $e->getLine(),
            'action' => $_POST['action'] ?? 'unknown'
        ]
    ]);
}

function testSecurity() {
    try {
        $config = SecureConfig::load();
        
        $grok_key = SecureConfig::getGrokApiKey();
        $openrouter_key = SecureConfig::getOpenRouterApiKey();
        
        $keys_loaded = 0;
        $security_issues = [];
        
        if (!empty($grok_key)) {
            $keys_loaded++;
            if (strlen($grok_key) < 20) {
                $security_issues[] = 'Grok key appears too short';
            }
        }
        
        if (!empty($openrouter_key)) {
            $keys_loaded++;
            if (strlen($openrouter_key) < 20) {
                $security_issues[] = 'OpenRouter key appears too short';
            }
        }
        
        $env_file_exists = file_exists(dirname(__FILE__) . '/../.env');
        $gitignore_exists = file_exists(dirname(__FILE__) . '/../.gitignore');
        
        if (!$env_file_exists) {
            $security_issues[] = '.env file not found';
        }
        
        if (!$gitignore_exists) {
            $security_issues[] = '.gitignore file not found';
        }
        
        $is_secure = empty($security_issues) && $keys_loaded > 0;
        
        $message = "Security Test Results:\n";
        $message .= "✓ API keys loaded from environment: $keys_loaded\n";
        $message .= "✓ Environment file exists: " . ($env_file_exists ? 'Yes' : 'No') . "\n";
        $message .= "✓ GitIgnore configured: " . ($gitignore_exists ? 'Yes' : 'No') . "\n";
        
        if (!empty($grok_key)) {
            $message .= "✓ Grok key: " . SecureConfig::maskKey($grok_key) . "\n";
        }
        
        if (!empty($openrouter_key)) {
            $message .= "✓ OpenRouter key: " . SecureConfig::maskKey($openrouter_key) . "\n";
        }
        
        if (!empty($security_issues)) {
            $message .= "\n⚠️ Security Issues:\n";
            foreach ($security_issues as $issue) {
                $message .= "• $issue\n";
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => $message,
            'keys_loaded' => $keys_loaded,
            'secure' => $is_secure,
            'issues' => $security_issues
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Security test failed: ' . $e->getMessage()
        ]);
    }
}

function testDatabase() {
    try {
        global $pdo;
        
        if (!$pdo) {
            throw new Exception('Database connection not available');
        }
        
        $tables = ['suggestions_complaints', 'survey_responses', 'users'];
        $total_records = 0;
        $table_info = [];
        
        foreach ($tables as $table) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
                $count = $stmt->fetchColumn();
                $total_records += $count;
                $table_info[] = "$table: $count records";
            } catch (Exception $e) {
                $table_info[] = "$table: Error - " . $e->getMessage();
            }
        }
        
        // Test recent data access
        try {
            $stmt = $pdo->query("SELECT 
                (SELECT COUNT(*) FROM suggestions_complaints WHERE type = 'complaint' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as recent_complaints,
                (SELECT COUNT(*) FROM suggestions_complaints WHERE type = 'suggestion' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as recent_suggestions
            ");
            $recent = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $recent = ['recent_complaints' => 0, 'recent_suggestions' => 0];
        }
        
        $message = "Database Connection Test:\n";
        $message .= "✓ Connection: Active\n";
        $message .= "✓ Tables accessible: " . count($tables) . "\n";
        foreach ($table_info as $info) {
            $message .= "  • $info\n";
        }
        $message .= "✓ Recent activity (30 days): {$recent['recent_complaints']} complaints, {$recent['recent_suggestions']} suggestions\n";
        
        echo json_encode([
            'success' => true,
            'message' => $message,
            'table_count' => count($tables),
            'total_records' => $total_records,
            'recent_activity' => $recent
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Database test failed: ' . $e->getMessage()
        ]);
    }
}

function testAIModel() {
    try {
        $model = $_POST['model'] ?? '';
        $query = $_POST['query'] ?? '';
        
        if (empty($model) || empty($query)) {
            throw new Exception('Model and query parameters required');
        }
        
        $ai_provider = new AdvancedAIProvider();
        
        // Get some database context for more meaningful responses
        $db_ai = new DatabaseAI();
        $context = $db_ai->getDatabaseContext();
        
        $result = $ai_provider->generateAdvancedResponse($query, $model, $context);
        
        echo json_encode($result);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'AI model test failed: ' . $e->getMessage()
        ]);
    }
}

function testDatabaseAI() {
    try {
        $db_ai = new DatabaseAI();
        
        // Get database statistics
        $stats = $db_ai->getDatabaseStats();
        $insights_result = $db_ai->generateInsights('local-python');
        
        $data_points = 0;
        if (isset($stats['complaints']['total_complaints'])) {
            $data_points += $stats['complaints']['total_complaints'];
        }
        if (isset($stats['suggestions']['total_suggestions'])) {
            $data_points += $stats['suggestions']['total_suggestions'];
        }
        if (isset($stats['users']['total_users'])) {
            $data_points += $stats['users']['total_users'];
        }
        
        $summary = "Database AI Analysis Summary:\n";
        $summary .= "📊 Data Overview:\n";
        $summary .= "  • Complaints: " . ($stats['complaints']['total_complaints'] ?? 0) . " total\n";
        $summary .= "  • Suggestions: " . ($stats['suggestions']['total_suggestions'] ?? 0) . " total\n";
        $summary .= "  • Users: " . ($stats['users']['total_users'] ?? 0) . " total\n\n";
        
        if ($insights_result['success']) {
            $summary .= "🤖 AI Insights:\n";
            $summary .= $insights_result['response'];
        } else {
            $summary .= "⚠️ AI Analysis Error: " . $insights_result['error'];
        }
        
        echo json_encode([
            'success' => true,
            'insights' => $summary,
            'data_points' => $data_points,
            'insight_count' => $insights_result['success'] ? 'Generated' : 'Failed',
            'raw_stats' => $stats
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Database AI test failed: ' . $e->getMessage()
        ]);
    }
}

function testSearchAnalysis() {
    try {
        $search_term = $_POST['search_term'] ?? '';
        
        if (empty($search_term)) {
            throw new Exception('Search term required');
        }
        
        $db_ai = new DatabaseAI();
        $result = $db_ai->searchAndAnalyze($search_term, 'local-python');
        
        // Get match count by doing a separate query
        global $pdo;
        $stmt = $pdo->prepare("SELECT COUNT(*) as matches FROM suggestions_complaints 
            WHERE subject LIKE ? OR description LIKE ?");
        
        $search_pattern = "%$search_term%";
        $stmt->execute([$search_pattern, $search_pattern]);
        $match_count = $stmt->fetchColumn();
        
        echo json_encode([
            'success' => $result['success'],
            'analysis' => $result['response'] ?? $result['error'],
            'match_count' => $match_count,
            'search_term' => $search_term
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Search analysis failed: ' . $e->getMessage()
        ]);
    }
}

function testCustomQuery() {
    try {
        $model = $_POST['model'] ?? 'local-python';
        $query = $_POST['query'] ?? '';
        
        if (empty($query)) {
            throw new Exception('Query required');
        }
        
        $db_ai = new DatabaseAI();
        $result = $db_ai->analyzeWithAI($query, $model);
        
        echo json_encode($result);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Custom query failed: ' . $e->getMessage()
        ]);
    }
}
?>