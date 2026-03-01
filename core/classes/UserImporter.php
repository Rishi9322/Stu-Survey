<?php
/**
 * UserImporter - Handles bulk user import from CSV/Excel files
 * Supports importing users with pre-filled surveys, reviews, complaints, and suggestions
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

class UserImporter
{
    private $conn;
    private $uploadDir;
    private $archiveDir;
    private $allowedTypes = ['csv', 'xlsx', 'xls'];
    private $maxFileSize = 10 * 1024 * 1024; // 10MB
    private $defaultPassword = 'password123';
    private $errors = [];
    private $warnings = [];
    private $successCount = 0;
    private $skippedCount = 0;
    private $importedUsers = [];
    private $emailToUserId = []; // Maps email -> user_id for related data linking

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->uploadDir = __DIR__ . '/../../uploads/imports/users/';
        $this->archiveDir = __DIR__ . '/../../uploads/imports/archive/';

        // Ensure directories exist
        foreach ([$this->uploadDir, $this->archiveDir] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    /**
     * Validate uploaded file
     */
    public function validateFile($file)
    {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds the upload_max_filesize directive.',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds the MAX_FILE_SIZE directive.',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
            ];
            $code = $file['error'] ?? UPLOAD_ERR_NO_FILE;
            return ['valid' => false, 'error' => $errorMessages[$code] ?? 'Unknown upload error.'];
        }

        // Validate extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedTypes)) {
            return ['valid' => false, 'error' => 'Invalid file type. Allowed: ' . implode(', ', $this->allowedTypes)];
        }

        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            return ['valid' => false, 'error' => 'File too large. Maximum size: 10MB'];
        }

        // Check for path traversal
        $basename = basename($file['name']);
        if (strpos($basename, '..') !== false) {
            return ['valid' => false, 'error' => 'Invalid filename.'];
        }

        return ['valid' => true, 'extension' => $extension];
    }

    /**
     * Parse uploaded file (CSV or Excel)
     */
    public function parseFile($file)
    {
        $validation = $this->validateFile($file);
        if (!$validation['valid']) {
            return ['success' => false, 'error' => $validation['error']];
        }

        $extension = $validation['extension'];
        $filename = 'import_' . date('Ymd_His') . '_' . mt_rand(1000, 9999) . '.' . $extension;
        $filepath = $this->uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => false, 'error' => 'Failed to move uploaded file.'];
        }

        try {
            if ($extension === 'csv') {
                $data = $this->parseCsvFile($filepath);
            } else {
                $data = $this->parseExcelFile($filepath);
            }

            // Archive the file
            $archivePath = $this->archiveDir . $filename;
            rename($filepath, $archivePath);

            return ['success' => true, 'data' => $data, 'filename' => $filename];
        } catch (Exception $e) {
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            return ['success' => false, 'error' => 'Error parsing file: ' . $e->getMessage()];
        }
    }

    /**
     * Parse CSV file
     */
    private function parseCsvFile($filepath)
    {
        $data = [];
        $headers = [];

        if (($handle = fopen($filepath, 'r')) !== false) {
            $rowNum = 0;
            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $rowNum++;
                if ($rowNum === 1) {
                    $headers = array_map('trim', array_map('strtolower', $row));
                    continue;
                }
                // Skip empty rows
                if (count(array_filter($row, function ($v) { return trim($v) !== ''; })) === 0) {
                    continue;
                }
                $rowData = ['_row_num' => $rowNum];
                foreach ($row as $index => $value) {
                    $header = isset($headers[$index]) ? $headers[$index] : "column_$index";
                    $rowData[$header] = trim($value);
                }
                $data[] = $rowData;
            }
            fclose($handle);
        }

        return ['headers' => $headers, 'rows' => $data];
    }

    /**
     * Parse Excel file using PHPSpreadsheet
     */
    private function parseExcelFile($filepath)
    {
        $spreadsheet = IOFactory::load($filepath);
        $sheet = $spreadsheet->getActiveSheet();
        $data = [];
        $headers = [];
        $rowNum = 0;

        foreach ($sheet->getRowIterator() as $row) {
            $rowNum++;
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $rowValues = [];
            foreach ($cellIterator as $cell) {
                $rowValues[] = trim((string)$cell->getValue());
            }

            if ($rowNum === 1) {
                $headers = array_map('strtolower', $rowValues);
                continue;
            }

            // Skip empty rows
            if (count(array_filter($rowValues, function ($v) { return $v !== ''; })) === 0) {
                continue;
            }

            $rowData = ['_row_num' => $rowNum];
            foreach ($rowValues as $index => $value) {
                $header = isset($headers[$index]) ? $headers[$index] : "column_$index";
                $rowData[$header] = $value;
            }
            $data[] = $rowData;
        }

        return ['headers' => $headers, 'rows' => $data];
    }

    /**
     * Process the full import - users + related data
     */
    public function processImport($parsedData, $sendEmails = false)
    {
        $this->errors = [];
        $this->warnings = [];
        $this->successCount = 0;
        $this->skippedCount = 0;
        $this->importedUsers = [];
        $this->emailToUserId = [];

        $rows = $parsedData['rows'];
        $headers = $parsedData['headers'];

        // Pre-load existing emails for duplicate checking
        $existingEmails = $this->getExistingEmails();
        $existingUsernames = $this->getExistingUsernames();

        // Categorize rows by type
        $userRows = [];
        $questionRows = [];
        $surveyRows = [];
        $reviewRows = [];
        $complaintRows = [];
        $suggestionRows = [];

        foreach ($rows as $row) {
            $rowType = strtoupper(trim($row['row_type'] ?? 'USER'));
            switch ($rowType) {
                case 'USER':
                    $userRows[] = $row;
                    break;
                case 'QUESTION':
                    $questionRows[] = $row;
                    break;
                case 'SURVEY':
                    $surveyRows[] = $row;
                    break;
                case 'REVIEW':
                    $reviewRows[] = $row;
                    break;
                case 'COMPLAINT':
                    $complaintRows[] = $row;
                    break;
                case 'SUGGESTION':
                    $suggestionRows[] = $row;
                    break;
                default:
                    $this->errors[] = [
                        'row' => $row['_row_num'],
                        'type' => 'row_type',
                        'field' => 'row_type',
                        'message' => "Unknown row type: '$rowType'. Valid: USER, QUESTION, SURVEY, REVIEW, COMPLAINT, SUGGESTION",
                        'data' => implode(', ', array_slice($row, 0, 5))
                    ];
            }
        }

        // Phase 1: Import users
        $this->importUsers($userRows, $existingEmails, $existingUsernames);

        // Build email-to-id mapping including existing users (for related data linking)
        $this->buildEmailToUserIdMap();

        // Phase 2: Import questions (before surveys, since surveys may reference them)
        $this->importQuestions($questionRows);

        // Phase 3: Import related data
        $this->importSurveyResponses($surveyRows);
        $this->importReviews($reviewRows);
        $this->importComplaintsAndSuggestions($complaintRows, 'complaint');
        $this->importComplaintsAndSuggestions($suggestionRows, 'suggestion');

        // Phase 3: Send emails if requested
        $emailsSent = 0;
        if ($sendEmails && !empty($this->importedUsers)) {
            $emailsSent = $this->sendCredentialEmails();
        }

        // Log the import
        $this->logImport(count($rows), $sendEmails);

        return [
            'success' => true,
            'total_rows' => count($rows),
            'users_imported' => $this->successCount,
            'users_skipped' => $this->skippedCount,
            'questions_imported' => count($questionRows) - count(array_filter($this->errors, function ($e) { return $e['type'] === 'question'; })),
            'surveys_imported' => count($surveyRows) - count(array_filter($this->errors, function ($e) { return $e['type'] === 'survey'; })),
            'reviews_imported' => count($reviewRows) - count(array_filter($this->errors, function ($e) { return $e['type'] === 'review'; })),
            'complaints_imported' => count($complaintRows) - count(array_filter($this->errors, function ($e) { return $e['type'] === 'complaint'; })),
            'suggestions_imported' => count($suggestionRows) - count(array_filter($this->errors, function ($e) { return $e['type'] === 'suggestion'; })),
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'emails_sent' => $emailsSent,
            'imported_users' => $this->importedUsers
        ];
    }

    /**
     * Import user rows
     */
    private function importUsers($rows, $existingEmails, $existingUsernames)
    {
        $hashedPassword = password_hash($this->defaultPassword, PASSWORD_DEFAULT);

        foreach ($rows as $row) {
            $rowNum = $row['_row_num'];
            
            // Extract fields
            $username = trim($row['username'] ?? '');
            $email = trim($row['email'] ?? '');
            $role = strtolower(trim($row['role'] ?? 'student'));
            $dob = trim($row['dob'] ?? '');

            // Validate required fields
            if (empty($username)) {
                $this->errors[] = [
                    'row' => $rowNum,
                    'type' => 'user',
                    'field' => 'username',
                    'message' => 'Username is required.',
                    'data' => $email
                ];
                continue;
            }

            if (empty($email)) {
                $this->errors[] = [
                    'row' => $rowNum,
                    'type' => 'user',
                    'field' => 'email',
                    'message' => 'Email is required.',
                    'data' => $username
                ];
                continue;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->errors[] = [
                    'row' => $rowNum,
                    'type' => 'user',
                    'field' => 'email',
                    'message' => 'Invalid email format.',
                    'data' => $email
                ];
                continue;
            }

            if (!in_array($role, ['student', 'teacher', 'admin'])) {
                $this->errors[] = [
                    'row' => $rowNum,
                    'type' => 'user',
                    'field' => 'role',
                    'message' => "Invalid role: '$role'. Must be student, teacher, or admin.",
                    'data' => "$username ($email)"
                ];
                continue;
            }

            // Check for duplicates
            if (in_array(strtolower($email), array_map('strtolower', $existingEmails))) {
                $this->warnings[] = [
                    'row' => $rowNum,
                    'message' => "Email '$email' already exists. Skipping user creation.",
                    'data' => "$username ($email)"
                ];
                $this->skippedCount++;
                continue;
            }

            if (in_array(strtolower($username), array_map('strtolower', $existingUsernames))) {
                $this->errors[] = [
                    'row' => $rowNum,
                    'type' => 'user',
                    'field' => 'username',
                    'message' => "Username '$username' already taken.",
                    'data' => "$username ($email)"
                ];
                continue;
            }

            // Insert user with transaction
            mysqli_begin_transaction($this->conn);
            try {
                // Insert into users table
                $sql = "INSERT INTO users (username, email, password, dob, role, is_active) VALUES (?, ?, ?, ?, ?, 1)";
                $stmt = mysqli_prepare($this->conn, $sql);
                if (!$stmt) {
                    throw new Exception(mysqli_error($this->conn));
                }
                $dobValue = !empty($dob) ? $dob : null;
                mysqli_stmt_bind_param($stmt, "sssss", $username, $email, $hashedPassword, $dobValue, $role);

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception(mysqli_error($this->conn));
                }

                $userId = mysqli_insert_id($this->conn);
                mysqli_stmt_close($stmt);

                // Insert profile based on role
                if ($role === 'student') {
                    $division = trim($row['division'] ?? '');
                    $rollNo = trim($row['roll_no'] ?? '');
                    $course = trim($row['course'] ?? '');

                    $sql = "INSERT INTO student_profiles (user_id, division, roll_no, course) VALUES (?, ?, ?, ?)";
                    $stmt = mysqli_prepare($this->conn, $sql);
                    if ($stmt) {
                        mysqli_stmt_bind_param($stmt, "isss", $userId, $division, $rollNo, $course);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);
                    }
                } elseif ($role === 'teacher') {
                    $department = trim($row['department'] ?? '');
                    $subjects = trim($row['subjects'] ?? '');
                    $experience = intval($row['experience'] ?? 0);

                    $sql = "INSERT INTO teacher_profiles (user_id, department, subjects, experience) VALUES (?, ?, ?, ?)";
                    $stmt = mysqli_prepare($this->conn, $sql);
                    if ($stmt) {
                        mysqli_stmt_bind_param($stmt, "issi", $userId, $department, $subjects, $experience);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);
                    }
                }

                mysqli_commit($this->conn);

                // Track success
                $this->successCount++;
                $existingEmails[] = $email;
                $existingUsernames[] = $username;

                $this->importedUsers[] = [
                    'id' => $userId,
                    'username' => $username,
                    'email' => $email,
                    'role' => $role,
                    'row' => $rowNum
                ];

            } catch (Exception $e) {
                mysqli_rollback($this->conn);
                $this->errors[] = [
                    'row' => $rowNum,
                    'type' => 'user',
                    'field' => 'database',
                    'message' => 'Database error: ' . $e->getMessage(),
                    'data' => "$username ($email)"
                ];
            }
        }
    }

    /**
     * Build a mapping of email -> user_id (including existing users)
     */
    private function buildEmailToUserIdMap()
    {
        $sql = "SELECT id, email FROM users";
        $result = mysqli_query($this->conn, $sql);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $this->emailToUserId[strtolower($row['email'])] = $row['id'];
            }
        }
    }

    /**
     * Import survey questions
     */
    private function importQuestions($rows)
    {
        foreach ($rows as $row) {
            $rowNum = $row['_row_num'];
            $question = trim($row['description'] ?? $row['subject'] ?? '');
            $targetRole = strtolower(trim($row['role'] ?? 'student'));
            $isActive = (strtolower(trim($row['status'] ?? 'active')) === 'active') ? 1 : 0;

            if (empty($question)) {
                $this->errors[] = [
                    'row' => $rowNum,
                    'type' => 'question',
                    'field' => 'description',
                    'message' => 'Question text (description) is required.',
                    'data' => "Subject: " . ($row['subject'] ?? '')
                ];
                continue;
            }

            if (!in_array($targetRole, ['student', 'teacher', 'both'])) {
                $targetRole = 'student';
            }

            $sql = "INSERT INTO survey_questions (question, target_role, is_active) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($this->conn, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssi", $question, $targetRole, $isActive);
                if (!mysqli_stmt_execute($stmt)) {
                    $this->errors[] = [
                        'row' => $rowNum,
                        'type' => 'question',
                        'field' => 'database',
                        'message' => 'DB error: ' . mysqli_error($this->conn),
                        'data' => "Question: $question"
                    ];
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    /**
     * Import survey responses
     */
    private function importSurveyResponses($rows)
    {
        foreach ($rows as $row) {
            $rowNum = $row['_row_num'];
            $email = strtolower(trim($row['email'] ?? ''));
            $questionId = intval($row['question_id'] ?? 0);
            $rating = intval($row['rating'] ?? 0);

            if (empty($email) || !isset($this->emailToUserId[$email])) {
                $this->errors[] = [
                    'row' => $rowNum,
                    'type' => 'survey',
                    'field' => 'email',
                    'message' => "User with email '$email' not found.",
                    'data' => "Question: $questionId, Rating: $rating"
                ];
                continue;
            }

            if ($questionId <= 0) {
                $this->errors[] = [
                    'row' => $rowNum,
                    'type' => 'survey',
                    'field' => 'question_id',
                    'message' => 'Invalid question_id.',
                    'data' => "Email: $email"
                ];
                continue;
            }

            if ($rating < 1 || $rating > 5) {
                $this->errors[] = [
                    'row' => $rowNum,
                    'type' => 'survey',
                    'field' => 'rating',
                    'message' => "Rating must be between 1 and 5. Got: $rating",
                    'data' => "Email: $email, Question: $questionId"
                ];
                continue;
            }

            $userId = $this->emailToUserId[$email];

            $sql = "INSERT INTO survey_responses (user_id, question_id, rating) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($this->conn, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "iii", $userId, $questionId, $rating);
                if (!mysqli_stmt_execute($stmt)) {
                    $this->errors[] = [
                        'row' => $rowNum,
                        'type' => 'survey',
                        'field' => 'database',
                        'message' => 'DB error: ' . mysqli_error($this->conn),
                        'data' => "Email: $email"
                    ];
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    /**
     * Import teacher reviews/ratings
     */
    private function importReviews($rows)
    {
        foreach ($rows as $row) {
            $rowNum = $row['_row_num'];
            $studentEmail = strtolower(trim($row['email'] ?? ''));
            $teacherEmail = strtolower(trim($row['teacher_email'] ?? ''));
            $rating = intval($row['rating'] ?? 0);
            $comment = trim($row['comment'] ?? '');

            if (empty($studentEmail) || !isset($this->emailToUserId[$studentEmail])) {
                $this->errors[] = [
                    'row' => $rowNum,
                    'type' => 'review',
                    'field' => 'email',
                    'message' => "Student email '$studentEmail' not found.",
                    'data' => "Teacher: $teacherEmail"
                ];
                continue;
            }

            if (empty($teacherEmail) || !isset($this->emailToUserId[$teacherEmail])) {
                $this->errors[] = [
                    'row' => $rowNum,
                    'type' => 'review',
                    'field' => 'teacher_email',
                    'message' => "Teacher email '$teacherEmail' not found.",
                    'data' => "Student: $studentEmail"
                ];
                continue;
            }

            if ($rating < 1 || $rating > 5) {
                $this->errors[] = [
                    'row' => $rowNum,
                    'type' => 'review',
                    'field' => 'rating',
                    'message' => "Rating must be between 1 and 5. Got: $rating",
                    'data' => "Student: $studentEmail, Teacher: $teacherEmail"
                ];
                continue;
            }

            $studentId = $this->emailToUserId[$studentEmail];
            $teacherId = $this->emailToUserId[$teacherEmail];

            $sql = "INSERT INTO teacher_ratings (student_id, teacher_id, rating, comment) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($this->conn, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "iiis", $studentId, $teacherId, $rating, $comment);
                if (!mysqli_stmt_execute($stmt)) {
                    $this->errors[] = [
                        'row' => $rowNum,
                        'type' => 'review',
                        'field' => 'database',
                        'message' => 'DB error: ' . mysqli_error($this->conn),
                        'data' => "Student: $studentEmail, Teacher: $teacherEmail"
                    ];
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    /**
     * Import complaints and suggestions
     */
    private function importComplaintsAndSuggestions($rows, $type)
    {
        foreach ($rows as $row) {
            $rowNum = $row['_row_num'];
            $email = strtolower(trim($row['email'] ?? ''));
            $subject = trim($row['subject'] ?? '');
            $description = trim($row['description'] ?? '');
            $status = strtolower(trim($row['status'] ?? 'pending'));

            // Identify submitter role
            $submittedByRole = 'student';
            if (!empty($email) && isset($this->emailToUserId[$email])) {
                $userId = $this->emailToUserId[$email];
                $roleResult = mysqli_query($this->conn, "SELECT role FROM users WHERE id = $userId");
                if ($roleResult && $roleRow = mysqli_fetch_assoc($roleResult)) {
                    $submittedByRole = $roleRow['role'];
                }
            }

            if (empty($subject)) {
                $this->errors[] = [
                    'row' => $rowNum,
                    'type' => $type,
                    'field' => 'subject',
                    'message' => 'Subject is required.',
                    'data' => "Email: $email"
                ];
                continue;
            }

            if (empty($description)) {
                $this->errors[] = [
                    'row' => $rowNum,
                    'type' => $type,
                    'field' => 'description',
                    'message' => 'Description is required.',
                    'data' => "Email: $email, Subject: $subject"
                ];
                continue;
            }

            $validStatuses = ['pending', 'in_progress', 'resolved'];
            if (!in_array($status, $validStatuses)) {
                $status = 'pending';
            }

            $sql = "INSERT INTO suggestions_complaints (subject, description, type, submitted_by_role, status) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($this->conn, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sssss", $subject, $description, $type, $submittedByRole, $status);
                if (!mysqli_stmt_execute($stmt)) {
                    $this->errors[] = [
                        'row' => $rowNum,
                        'type' => $type,
                        'field' => 'database',
                        'message' => 'DB error: ' . mysqli_error($this->conn),
                        'data' => "Subject: $subject"
                    ];
                }
                mysqli_stmt_close($stmt);
            }
        }
    }

    /**
     * Send credential emails to imported users
     */
    private function sendCredentialEmails()
    {
        $sent = 0;
        foreach ($this->importedUsers as $user) {
            $to = $user['email'];
            $subject = "Your Account Has Been Created - Student Satisfaction Survey System";
            $message = "Dear {$user['username']},\n\n";
            $message .= "An account has been created for you on the Student Satisfaction Survey System.\n\n";
            $message .= "Your login credentials:\n";
            $message .= "Email: {$user['email']}\n";
            $message .= "Password: {$this->defaultPassword}\n\n";
            $message .= "Please change your password after your first login.\n\n";
            $message .= "Login at: " . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/stu/public/login.php\n\n";
            $message .= "Best regards,\nAdmin Team";

            $headers = "From: noreply@" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            if (@mail($to, $subject, $message, $headers)) {
                $sent++;
            } else {
                $this->warnings[] = [
                    'row' => $user['row'],
                    'message' => "Failed to send email to {$user['email']}. User was created successfully.",
                    'data' => $user['email']
                ];
            }
        }
        return $sent;
    }

    /**
     * Log import activity to database
     */
    private function logImport($totalRows, $notificationSent)
    {
        // Create import_logs table if it doesn't exist
        $createTable = "CREATE TABLE IF NOT EXISTS import_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT NOT NULL,
            filename VARCHAR(255) NOT NULL DEFAULT '',
            original_filename VARCHAR(255) NOT NULL DEFAULT '',
            total_rows INT DEFAULT 0,
            successful INT DEFAULT 0,
            failed INT DEFAULT 0,
            warnings INT DEFAULT 0,
            error_log TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        mysqli_query($this->conn, $createTable);

        $adminId = $_SESSION['id'] ?? 0;
        $errorLog = json_encode($this->errors);
        $errorCount = count($this->errors);
        $warningCount = count($this->warnings);

        $sql = "INSERT INTO import_logs (admin_id, filename, original_filename, total_rows, successful, failed, warnings, error_log) 
                VALUES (?, '', '', ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "iiiiis", $adminId, $totalRows, $this->successCount, $errorCount, $warningCount, $errorLog);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    /**
     * Get existing emails from database
     */
    private function getExistingEmails()
    {
        $emails = [];
        $result = mysqli_query($this->conn, "SELECT email FROM users");
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $emails[] = $row['email'];
            }
        }
        return $emails;
    }

    /**
     * Get existing usernames from database
     */
    private function getExistingUsernames()
    {
        $usernames = [];
        $result = mysqli_query($this->conn, "SELECT username FROM users");
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $usernames[] = $row['username'];
            }
        }
        return $usernames;
    }

    /**
     * Generate a downloadable error report CSV
     */
    public function generateErrorReport($errors)
    {
        $filename = 'import_errors_' . date('Ymd_His') . '.csv';
        $filepath = __DIR__ . '/../../storage/exports/temp/' . $filename;

        $handle = fopen($filepath, 'w');
        fputcsv($handle, ['Row Number', 'Type', 'Field', 'Error Message', 'Row Data']);

        foreach ($errors as $error) {
            fputcsv($handle, [
                $error['row'] ?? '',
                $error['type'] ?? '',
                $error['field'] ?? '',
                $error['message'] ?? '',
                $error['data'] ?? ''
            ]);
        }

        fclose($handle);
        return $filepath;
    }
}
