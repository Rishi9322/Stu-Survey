<?php
require_once '../includes/config.php';

/**
 * Training Data Integration System
 * Import data from CSV/Excel/Google Sheets for AI training enhancement
 */
class TrainingDataIntegrator {
    private $pdo;
    private $upload_dir;
    private $allowed_types = ['csv', 'xlsx', 'xls'];
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        $this->upload_dir = dirname(__FILE__) . '/../uploads/training/';
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->upload_dir)) {
            mkdir($this->upload_dir, 0755, true);
        }
    }
    
    /**
     * Process uploaded training data file
     */
    public function processUploadedFile($file) {
        // Validate file
        $validation = $this->validateFile($file);
        if (!$validation['valid']) {
            return ['success' => false, 'error' => $validation['error']];
        }
        
        // Move uploaded file
        $filename = 'training_' . time() . '_' . basename($file['name']);
        $filepath = $this->upload_dir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => false, 'error' => 'Failed to move uploaded file'];
        }
        
        // Process based on file type
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        try {
            switch ($extension) {
                case 'csv':
                    $data = $this->processCsvFile($filepath);
                    break;
                case 'xlsx':
                case 'xls':
                    $data = $this->processExcelFile($filepath);
                    break;
                default:
                    throw new Exception('Unsupported file type');
            }
            
            $result = $this->importTrainingData($data, $filename);
            
            // Clean up file
            unlink($filepath);
            
            return $result;
            
        } catch (Exception $e) {
            // Clean up file on error
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Process Google Sheets data via CSV export
     */
    public function processGoogleSheetsUrl($url) {
        // Convert Google Sheets URL to CSV export URL
        $csv_url = $this->convertToGoogleSheetsCsvUrl($url);
        
        if (!$csv_url) {
            return ['success' => false, 'error' => 'Invalid Google Sheets URL'];
        }
        
        // Download CSV data
        $csv_content = file_get_contents($csv_url);
        
        if ($csv_content === false) {
            return ['success' => false, 'error' => 'Failed to download Google Sheets data'];
        }
        
        // Save to temporary file
        $temp_file = $this->upload_dir . 'google_sheets_' . time() . '.csv';
        file_put_contents($temp_file, $csv_content);
        
        try {
            $data = $this->processCsvFile($temp_file);
            $result = $this->importTrainingData($data, 'google_sheets_import');
            
            // Clean up
            unlink($temp_file);
            
            return $result;
            
        } catch (Exception $e) {
            if (file_exists($temp_file)) {
                unlink($temp_file);
            }
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Validate uploaded file
     */
    private function validateFile($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => 'File upload error: ' . $file['error']];
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $this->allowed_types)) {
            return ['valid' => false, 'error' => 'Invalid file type. Allowed: ' . implode(', ', $this->allowed_types)];
        }
        
        if ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
            return ['valid' => false, 'error' => 'File too large. Maximum size: 10MB'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Process CSV file
     */
    private function processCsvFile($filepath) {
        $data = [];
        $headers = [];
        
        if (($handle = fopen($filepath, "r")) !== FALSE) {
            $row_count = 0;
            
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($row_count === 0) {
                    // First row as headers
                    $headers = array_map('trim', $row);
                } else {
                    // Data rows
                    $row_data = [];
                    foreach ($row as $index => $value) {
                        $header = isset($headers[$index]) ? $headers[$index] : "column_$index";
                        $row_data[$header] = trim($value);
                    }
                    $data[] = $row_data;
                }
                $row_count++;
            }
            fclose($handle);
        }
        
        return ['headers' => $headers, 'data' => $data];
    }
    
    /**
     * Process Excel file (basic implementation)
     */
    private function processExcelFile($filepath) {
        // For Excel files, we'll convert to CSV first
        // This is a simplified implementation - in production you might use PHPSpreadsheet
        
        return ['headers' => [], 'data' => [], 'note' => 'Excel processing not fully implemented. Please use CSV format.'];
    }
    
    /**
     * Convert Google Sheets URL to CSV export URL
     */
    private function convertToGoogleSheetsCsvUrl($url) {
        // Extract sheet ID from various Google Sheets URL formats
        $patterns = [
            '/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/',
            '/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)\/edit/',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                $sheet_id = $matches[1];
                return "https://docs.google.com/spreadsheets/d/{$sheet_id}/export?format=csv";
            }
        }
        
        return false;
    }
    
    /**
     * Import training data into database
     */
    private function importTrainingData($processed_data, $source) {
        if (empty($processed_data['data'])) {
            return ['success' => false, 'error' => 'No data found to import'];
        }
        
        // Create training_data table if it doesn't exist
        $this->createTrainingTable();
        
        $imported_count = 0;
        $errors = [];
        
        foreach ($processed_data['data'] as $index => $row) {
            try {
                // Detect data type and extract relevant fields
                $training_record = $this->parseTrainingRecord($row, $index);
                
                if ($training_record) {
                    $this->insertTrainingRecord($training_record, $source);
                    $imported_count++;
                }
                
            } catch (Exception $e) {
                $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
            }
        }
        
        return [
            'success' => true,
            'imported_count' => $imported_count,
            'total_rows' => count($processed_data['data']),
            'errors' => $errors,
            'headers' => $processed_data['headers'] ?? []
        ];
    }
    
    /**
     * Create training data table
     */
    private function createTrainingTable() {
        $sql = "CREATE TABLE IF NOT EXISTS training_data (
            id INT PRIMARY KEY AUTO_INCREMENT,
            category VARCHAR(100),
            subject VARCHAR(255),
            content TEXT,
            sentiment VARCHAR(50),
            priority VARCHAR(20),
            tags TEXT,
            source VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_category (category),
            INDEX idx_sentiment (sentiment),
            INDEX idx_priority (priority)
        )";
        
        $this->pdo->exec($sql);
    }
    
    /**
     * Parse training record from row data
     */
    private function parseTrainingRecord($row, $index) {
        // Flexible parsing - try to detect common column names
        $record = [
            'category' => '',
            'subject' => '',
            'content' => '',
            'sentiment' => '',
            'priority' => '',
            'tags' => ''
        ];
        
        foreach ($row as $key => $value) {
            $key_lower = strtolower($key);
            
            // Category detection
            if (in_array($key_lower, ['category', 'type', 'classification', 'class'])) {
                $record['category'] = $value;
            }
            // Subject detection
            elseif (in_array($key_lower, ['subject', 'title', 'heading', 'topic'])) {
                $record['subject'] = $value;
            }
            // Content detection
            elseif (in_array($key_lower, ['content', 'description', 'text', 'message', 'feedback', 'comment'])) {
                $record['content'] = $value;
            }
            // Sentiment detection
            elseif (in_array($key_lower, ['sentiment', 'mood', 'emotion', 'feeling'])) {
                $record['sentiment'] = $value;
            }
            // Priority detection
            elseif (in_array($key_lower, ['priority', 'urgency', 'importance', 'level'])) {
                $record['priority'] = $value;
            }
            // Tags detection
            elseif (in_array($key_lower, ['tags', 'keywords', 'labels'])) {
                $record['tags'] = $value;
            }
        }
        
        // Require at least content
        if (empty($record['content']) && empty($record['subject'])) {
            return null;
        }
        
        return $record;
    }
    
    /**
     * Insert training record
     */
    private function insertTrainingRecord($record, $source) {
        $sql = "INSERT INTO training_data (category, subject, content, sentiment, priority, tags, source) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $record['category'],
            $record['subject'],
            $record['content'],
            $record['sentiment'],
            $record['priority'],
            $record['tags'],
            $source
        ]);
    }
    
    /**
     * Get training data statistics
     */
    public function getTrainingStats() {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) as total_records FROM training_data");
            $total = $stmt->fetchColumn();
            
            $stmt = $this->pdo->query("SELECT 
                category,
                COUNT(*) as count
            FROM training_data 
            WHERE category != ''
            GROUP BY category 
            ORDER BY count DESC
            LIMIT 10");
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt = $this->pdo->query("SELECT 
                source,
                COUNT(*) as count,
                MAX(created_at) as last_import
            FROM training_data 
            GROUP BY source 
            ORDER BY last_import DESC");
            $sources = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'total_records' => $total,
                'categories' => $categories,
                'sources' => $sources
            ];
            
        } catch (Exception $e) {
            return [
                'total_records' => 0,
                'categories' => [],
                'sources' => [],
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Export training data as JSON for AI model training
     */
    public function exportForAI($category = null) {
        try {
            $sql = "SELECT category, subject, content, sentiment, priority, tags 
                    FROM training_data";
            $params = [];
            
            if ($category) {
                $sql .= " WHERE category = ?";
                $params[] = $category;
            }
            
            $sql .= " ORDER BY created_at DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => $data,
                'count' => count($data)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
?>