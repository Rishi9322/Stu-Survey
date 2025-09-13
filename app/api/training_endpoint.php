<?php
require_once '../../core/includes/config.php';
require_once 'TrainingDataIntegrator.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $action = $_POST['action'] ?? '';
    $integrator = new TrainingDataIntegrator();
    
    switch ($action) {
        case 'upload_file':
            handleFileUpload($integrator);
            break;
            
        case 'import_sheets':
            handleSheetsImport($integrator);
            break;
            
        case 'get_stats':
            handleGetStats($integrator);
            break;
            
        case 'preview_data':
            handlePreviewData($integrator);
            break;
            
        case 'export_data':
            handleExportData($integrator);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

function handleFileUpload($integrator) {
    if (!isset($_FILES['training_file'])) {
        throw new Exception('No file uploaded');
    }
    
    $result = $integrator->processUploadedFile($_FILES['training_file']);
    echo json_encode($result);
}

function handleSheetsImport($integrator) {
    $sheets_url = $_POST['sheets_url'] ?? '';
    
    if (empty($sheets_url)) {
        throw new Exception('Google Sheets URL required');
    }
    
    $result = $integrator->processGoogleSheetsUrl($sheets_url);
    echo json_encode($result);
}

function handleGetStats($integrator) {
    $stats = $integrator->getTrainingStats();
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
}

function handlePreviewData($integrator) {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT category, subject, content, sentiment, priority, tags, source, created_at 
                            FROM training_data 
                            ORDER BY created_at DESC 
                            LIMIT 20");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

function handleExportData($integrator) {
    $category = $_POST['category'] ?? null;
    $result = $integrator->exportForAI($category);
    echo json_encode($result);
}
?>
