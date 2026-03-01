<?php
/**
 * UserExporter - Handles exporting user data with related surveys, reviews, complaints, suggestions
 * Supports CSV and Excel (XLSX) formats with multiple sheets
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class UserExporter
{
    private $conn;
    private $exportDir;
    private $tempDir;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->exportDir = __DIR__ . '/../../storage/exports/users/';
        $this->tempDir = __DIR__ . '/../../storage/exports/temp/';

        foreach ([$this->exportDir, $this->tempDir] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    /**
     * Export users and related data
     */
    public function export($filters = [], $format = 'csv')
    {
        $users = $this->fetchUsers($filters);
        $surveyResponses = $this->fetchSurveyResponses($filters);
        $teacherRatings = $this->fetchTeacherRatings($filters);
        $suggestionsComplaints = $this->fetchSuggestionsComplaints($filters);

        $timestamp = date('Ymd_His');

        if ($format === 'xlsx') {
            return $this->generateExcel($users, $surveyResponses, $teacherRatings, $suggestionsComplaints, $timestamp);
        } else {
            return $this->generateCsv($users, $surveyResponses, $teacherRatings, $suggestionsComplaints, $timestamp);
        }
    }

    /**
     * Fetch users based on filters
     */
    private function fetchUsers($filters)
    {
        $where = "1=1";
        $params = [];
        $types = "";

        if (!empty($filters['role'])) {
            $where .= " AND u.role = ?";
            $params[] = $filters['role'];
            $types .= "s";
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $where .= " AND u.is_active = 1";
            } elseif ($filters['status'] === 'inactive') {
                $where .= " AND u.is_active = 0";
            }
        }

        if (!empty($filters['date_from'])) {
            $where .= " AND u.created_at >= ?";
            $params[] = $filters['date_from'];
            $types .= "s";
        }

        if (!empty($filters['date_to'])) {
            $where .= " AND u.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
            $types .= "s";
        }

        $sql = "SELECT u.id, u.username, u.email, u.role, u.dob, u.is_active, u.created_at,
                       sp.division, sp.roll_no, sp.course,
                       tp.department, tp.subjects, tp.experience
                FROM users u
                LEFT JOIN student_profiles sp ON u.id = sp.user_id AND u.role = 'student'
                LEFT JOIN teacher_profiles tp ON u.id = tp.user_id AND u.role = 'teacher'
                WHERE $where
                ORDER BY u.role, u.username";

        $users = [];
        if (!empty($params)) {
            $stmt = mysqli_prepare($this->conn, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                while ($row = mysqli_fetch_assoc($result)) {
                    $users[] = $row;
                }
                mysqli_stmt_close($stmt);
            }
        } else {
            $result = mysqli_query($this->conn, $sql);
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $users[] = $row;
                }
            }
        }

        return $users;
    }

    /**
     * Fetch survey responses
     */
    private function fetchSurveyResponses($filters)
    {
        $where = "1=1";
        if (!empty($filters['role'])) {
            $where .= " AND u.role = '" . mysqli_real_escape_string($this->conn, $filters['role']) . "'";
        }

        $sql = "SELECT sr.id, u.email, u.username, sq.question, sq.target_role, sr.rating, sr.created_at
                FROM survey_responses sr
                JOIN users u ON sr.user_id = u.id
                JOIN survey_questions sq ON sr.question_id = sq.id
                WHERE $where
                ORDER BY sr.created_at DESC";

        $responses = [];
        $result = mysqli_query($this->conn, $sql);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $responses[] = $row;
            }
        }
        return $responses;
    }

    /**
     * Fetch teacher ratings
     */
    private function fetchTeacherRatings($filters)
    {
        $where = "1=1";
        if (!empty($filters['role']) && $filters['role'] !== 'student') {
            // Only include ratings if we're looking at students or all
            if ($filters['role'] === 'teacher') {
                $where .= " AND 1=1"; // Include all ratings involving teachers
            }
        }

        $sql = "SELECT tr.id, s.email as student_email, s.username as student_name,
                       t.email as teacher_email, t.username as teacher_name,
                       tr.rating, tr.comment, tr.created_at
                FROM teacher_ratings tr
                JOIN users s ON tr.student_id = s.id
                JOIN users t ON tr.teacher_id = t.id
                WHERE $where
                ORDER BY tr.created_at DESC";

        $ratings = [];
        $result = mysqli_query($this->conn, $sql);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $ratings[] = $row;
            }
        }
        return $ratings;
    }

    /**
     * Fetch suggestions and complaints
     */
    private function fetchSuggestionsComplaints($filters)
    {
        $sql = "SELECT sc.id, sc.subject, sc.description, sc.type, sc.submitted_by_role,
                       sc.status, sc.resolution_notes, sc.resolved_at, sc.created_at,
                       u.username as resolved_by_name
                FROM suggestions_complaints sc
                LEFT JOIN users u ON sc.resolved_by = u.id
                ORDER BY sc.created_at DESC";

        $items = [];
        $result = mysqli_query($this->conn, $sql);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $items[] = $row;
            }
        }
        return $items;
    }

    /**
     * Generate CSV export (single file with row_type markers for re-import compatibility)
     */
    private function generateCsv($users, $surveyResponses, $teacherRatings, $suggestionsComplaints, $timestamp)
    {
        $filename = "user_export_{$timestamp}.csv";
        $filepath = $this->tempDir . $filename;

        $handle = fopen($filepath, 'w');

        // Write header row with all possible columns
        $headers = [
            'row_type', 'username', 'email', 'role', 'dob', 'is_active', 'created_at',
            'division', 'roll_no', 'course', 'department', 'subjects', 'experience',
            'question_id', 'rating', 'teacher_email', 'comment',
            'subject', 'description', 'status'
        ];
        fputcsv($handle, $headers);

        // Write user rows
        foreach ($users as $user) {
            fputcsv($handle, [
                'USER',
                $user['username'],
                $user['email'],
                $user['role'],
                $user['dob'] ?? '',
                $user['is_active'],
                $user['created_at'],
                $user['division'] ?? '',
                $user['roll_no'] ?? '',
                $user['course'] ?? '',
                $user['department'] ?? '',
                $user['subjects'] ?? '',
                $user['experience'] ?? '',
                '', '', '', '', '', '', ''
            ]);
        }

        // Write survey response rows
        foreach ($surveyResponses as $sr) {
            fputcsv($handle, [
                'SURVEY',
                '', $sr['email'], '', '', '', $sr['created_at'],
                '', '', '', '', '', '',
                '', $sr['rating'], '', '',
                '', '', ''
            ]);
        }

        // Write teacher rating rows
        foreach ($teacherRatings as $tr) {
            fputcsv($handle, [
                'REVIEW',
                '', $tr['student_email'], '', '', '', $tr['created_at'],
                '', '', '', '', '', '',
                '', $tr['rating'], $tr['teacher_email'], $tr['comment'],
                '', '', ''
            ]);
        }

        // Write complaints and suggestions
        foreach ($suggestionsComplaints as $sc) {
            $rowType = strtoupper($sc['type'] === 'suggestion' ? 'SUGGESTION' : 'COMPLAINT');
            fputcsv($handle, [
                $rowType,
                '', '', $sc['submitted_by_role'], '', '', $sc['created_at'],
                '', '', '', '', '', '',
                '', '', '', '',
                $sc['subject'], $sc['description'], $sc['status']
            ]);
        }

        fclose($handle);

        return [
            'success' => true,
            'filepath' => $filepath,
            'filename' => $filename,
            'format' => 'csv',
            'stats' => [
                'users' => count($users),
                'surveys' => count($surveyResponses),
                'ratings' => count($teacherRatings),
                'feedback' => count($suggestionsComplaints)
            ]
        ];
    }

    /**
     * Generate Excel export with multiple sheets
     */
    private function generateExcel($users, $surveyResponses, $teacherRatings, $suggestionsComplaints, $timestamp)
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('Student Satisfaction Survey System')
            ->setTitle('User Data Export')
            ->setDescription('Exported user data with related surveys, ratings, and feedback');

        // ---- Sheet 1: Users ----
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Users');
        $userHeaders = ['ID', 'Username', 'Email', 'Role', 'DOB', 'Status', 'Created At', 'Division', 'Roll No', 'Course', 'Department', 'Subjects', 'Experience'];
        $this->writeSheetHeader($sheet, $userHeaders);

        $rowIdx = 2;
        foreach ($users as $user) {
            $sheet->fromArray([
                $user['id'],
                $user['username'],
                $user['email'],
                ucfirst($user['role']),
                $user['dob'] ?? '',
                $user['is_active'] ? 'Active' : 'Inactive',
                $user['created_at'],
                $user['division'] ?? '',
                $user['roll_no'] ?? '',
                $user['course'] ?? '',
                $user['department'] ?? '',
                $user['subjects'] ?? '',
                $user['experience'] ?? ''
            ], null, "A{$rowIdx}");
            $rowIdx++;
        }
        $this->autoSizeColumns($sheet, count($userHeaders));

        // ---- Sheet 2: Survey Responses ----
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Survey Responses');
        $surveyHeaders = ['ID', 'User Email', 'Username', 'Question', 'Target Role', 'Rating', 'Submitted At'];
        $this->writeSheetHeader($sheet2, $surveyHeaders);

        $rowIdx = 2;
        foreach ($surveyResponses as $sr) {
            $sheet2->fromArray([
                $sr['id'],
                $sr['email'],
                $sr['username'],
                $sr['question'],
                ucfirst($sr['target_role']),
                $sr['rating'],
                $sr['created_at']
            ], null, "A{$rowIdx}");
            $rowIdx++;
        }
        $this->autoSizeColumns($sheet2, count($surveyHeaders));

        // ---- Sheet 3: Teacher Ratings ----
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Teacher Ratings');
        $ratingHeaders = ['ID', 'Student Email', 'Student Name', 'Teacher Email', 'Teacher Name', 'Rating', 'Comment', 'Created At'];
        $this->writeSheetHeader($sheet3, $ratingHeaders);

        $rowIdx = 2;
        foreach ($teacherRatings as $tr) {
            $sheet3->fromArray([
                $tr['id'],
                $tr['student_email'],
                $tr['student_name'],
                $tr['teacher_email'],
                $tr['teacher_name'],
                $tr['rating'],
                $tr['comment'],
                $tr['created_at']
            ], null, "A{$rowIdx}");
            $rowIdx++;
        }
        $this->autoSizeColumns($sheet3, count($ratingHeaders));

        // ---- Sheet 4: Complaints & Suggestions ----
        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('Complaints & Suggestions');
        $feedbackHeaders = ['ID', 'Type', 'Subject', 'Description', 'Submitted By Role', 'Status', 'Resolution Notes', 'Resolved By', 'Resolved At', 'Created At'];
        $this->writeSheetHeader($sheet4, $feedbackHeaders);

        $rowIdx = 2;
        foreach ($suggestionsComplaints as $sc) {
            $sheet4->fromArray([
                $sc['id'],
                ucfirst($sc['type']),
                $sc['subject'],
                $sc['description'],
                ucfirst($sc['submitted_by_role']),
                ucfirst(str_replace('_', ' ', $sc['status'])),
                $sc['resolution_notes'] ?? '',
                $sc['resolved_by_name'] ?? '',
                $sc['resolved_at'] ?? '',
                $sc['created_at']
            ], null, "A{$rowIdx}");
            $rowIdx++;
        }
        $this->autoSizeColumns($sheet4, count($feedbackHeaders));

        // ---- Sheet 5: Summary ----
        $sheet5 = $spreadsheet->createSheet();
        $sheet5->setTitle('Summary');
        $this->writeSummarySheet($sheet5, $users, $surveyResponses, $teacherRatings, $suggestionsComplaints);

        // Set active sheet back to first
        $spreadsheet->setActiveSheetIndex(0);

        // Save file
        $filename = "user_export_{$timestamp}.xlsx";
        $filepath = $this->tempDir . $filename;
        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);

        return [
            'success' => true,
            'filepath' => $filepath,
            'filename' => $filename,
            'format' => 'xlsx',
            'stats' => [
                'users' => count($users),
                'surveys' => count($surveyResponses),
                'ratings' => count($teacherRatings),
                'feedback' => count($suggestionsComplaints)
            ]
        ];
    }

    /**
     * Write styled header row to a sheet
     */
    private function writeSheetHeader($sheet, $headers)
    {
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DC2626']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '999999']
                    ]
                ]
            ]);
            $col++;
        }
    }

    /**
     * Auto-size columns
     */
    private function autoSizeColumns($sheet, $colCount)
    {
        for ($i = 0; $i < $colCount; $i++) {
            $col = chr(65 + $i);
            if ($i >= 26) {
                $col = chr(64 + intdiv($i, 26)) . chr(65 + ($i % 26));
            }
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    /**
     * Write summary statistics sheet
     */
    private function writeSummarySheet($sheet, $users, $surveyResponses, $teacherRatings, $suggestionsComplaints)
    {
        $sheet->setCellValue('A1', 'Export Summary Report');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'DC2626']]
        ]);
        $sheet->mergeCells('A1:D1');

        $sheet->setCellValue('A2', 'Generated: ' . date('F j, Y g:i A'));
        $sheet->getStyle('A2')->getFont()->setItalic(true);
        $sheet->mergeCells('A2:D2');

        // User statistics
        $row = 4;
        $sheet->setCellValue("A{$row}", 'User Statistics');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(13);
        $row++;

        $studentCount = count(array_filter($users, fn($u) => $u['role'] === 'student'));
        $teacherCount = count(array_filter($users, fn($u) => $u['role'] === 'teacher'));
        $adminCount = count(array_filter($users, fn($u) => $u['role'] === 'admin'));
        $activeCount = count(array_filter($users, fn($u) => $u['is_active']));

        $stats = [
            ['Total Users', count($users)],
            ['Students', $studentCount],
            ['Teachers', $teacherCount],
            ['Admins', $adminCount],
            ['Active Users', $activeCount],
            ['Inactive Users', count($users) - $activeCount],
        ];

        foreach ($stats as $stat) {
            $sheet->setCellValue("A{$row}", $stat[0]);
            $sheet->setCellValue("B{$row}", $stat[1]);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $row++;
        }

        // Survey statistics
        $row += 1;
        $sheet->setCellValue("A{$row}", 'Survey Statistics');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(13);
        $row++;

        $avgRating = !empty($surveyResponses) ? round(array_sum(array_column($surveyResponses, 'rating')) / count($surveyResponses), 2) : 0;
        $surveyStats = [
            ['Total Responses', count($surveyResponses)],
            ['Average Rating', $avgRating . ' / 5'],
        ];

        foreach ($surveyStats as $stat) {
            $sheet->setCellValue("A{$row}", $stat[0]);
            $sheet->setCellValue("B{$row}", $stat[1]);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $row++;
        }

        // Rating statistics
        $row += 1;
        $sheet->setCellValue("A{$row}", 'Teacher Rating Statistics');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(13);
        $row++;

        $avgTeacherRating = !empty($teacherRatings) ? round(array_sum(array_column($teacherRatings, 'rating')) / count($teacherRatings), 2) : 0;
        $ratingStats = [
            ['Total Ratings', count($teacherRatings)],
            ['Average Rating', $avgTeacherRating . ' / 5'],
        ];

        foreach ($ratingStats as $stat) {
            $sheet->setCellValue("A{$row}", $stat[0]);
            $sheet->setCellValue("B{$row}", $stat[1]);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $row++;
        }

        // Feedback statistics
        $row += 1;
        $sheet->setCellValue("A{$row}", 'Feedback Statistics');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(13);
        $row++;

        $complaints = array_filter($suggestionsComplaints, fn($sc) => $sc['type'] === 'complaint');
        $suggestions = array_filter($suggestionsComplaints, fn($sc) => $sc['type'] === 'suggestion');
        $resolved = array_filter($suggestionsComplaints, fn($sc) => $sc['status'] === 'resolved');

        $feedbackStats = [
            ['Total Items', count($suggestionsComplaints)],
            ['Complaints', count($complaints)],
            ['Suggestions', count($suggestions)],
            ['Resolved', count($resolved)],
            ['Pending', count($suggestionsComplaints) - count($resolved)],
        ];

        foreach ($feedbackStats as $stat) {
            $sheet->setCellValue("A{$row}", $stat[0]);
            $sheet->setCellValue("B{$row}", $stat[1]);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(20);
    }

    /**
     * Stream file download to browser
     */
    public static function downloadFile($filepath, $filename, $format)
    {
        if (!file_exists($filepath)) {
            return false;
        }

        $contentType = $format === 'xlsx'
            ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            : 'text/csv';

        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        readfile($filepath);

        // Cleanup temp file after download
        @unlink($filepath);
        exit;
    }

    /**
     * Generate the import template file (CSV or Excel)
     */
    public function generateTemplate($format = 'csv')
    {
        if ($format === 'xlsx') {
            return $this->generateExcelTemplate();
        }
        return $this->generateCsvTemplate();
    }

    /**
     * Generate CSV template
     */
    private function generateCsvTemplate()
    {
        $filename = 'import_template.csv';
        $filepath = __DIR__ . '/../../storage/templates/' . $filename;

        $handle = fopen($filepath, 'w');

        // Header row
        $headers = [
            'row_type', 'username', 'email', 'role', 'dob',
            'division', 'roll_no', 'course', 'department', 'subjects', 'experience',
            'question_id', 'rating', 'teacher_email', 'comment',
            'subject', 'description', 'status'
        ];
        fputcsv($handle, $headers);

        // Sample USER rows
        fputcsv($handle, ['USER', 'john_doe', 'john@example.com', 'student', '2000-05-15', 'Computer Science A', 'CS001', 'B.Tech CS', '', '', '', '', '', '', '', '', '', '']);
        fputcsv($handle, ['USER', 'jane_smith', 'jane@example.com', 'student', '2001-03-20', 'Electronics B', 'EC045', 'B.Tech EC', '', '', '', '', '', '', '', '', '', '']);
        fputcsv($handle, ['USER', 'prof_wilson', 'wilson@example.com', 'teacher', '1980-01-15', '', '', '', 'Computer Science', 'Programming,Data Structures', '10', '', '', '', '', '', '', '']);
        fputcsv($handle, ['USER', 'admin_user', 'admin2@example.com', 'admin', '1990-06-01', '', '', '', '', '', '', '', '', '', '', '', '', '']);

        // Sample SURVEY rows
        fputcsv($handle, ['SURVEY', '', 'john@example.com', '', '', '', '', '', '', '', '', '1', '4', '', '', '', '', '']);
        fputcsv($handle, ['SURVEY', '', 'john@example.com', '', '', '', '', '', '', '', '', '2', '5', '', '', '', '', '']);
        fputcsv($handle, ['SURVEY', '', 'jane@example.com', '', '', '', '', '', '', '', '', '1', '3', '', '', '', '', '']);

        // Sample REVIEW rows
        fputcsv($handle, ['REVIEW', '', 'john@example.com', '', '', '', '', '', '', '', '', '', '5', 'wilson@example.com', 'Excellent teacher!', '', '', '']);
        fputcsv($handle, ['REVIEW', '', 'jane@example.com', '', '', '', '', '', '', '', '', '', '4', 'wilson@example.com', 'Very helpful', '', '', '']);

        // Sample COMPLAINT rows
        fputcsv($handle, ['COMPLAINT', '', 'john@example.com', '', '', '', '', '', '', '', '', '', '', '', '', 'Broken AC', 'The AC in room 301 is not working properly.', 'pending']);

        // Sample SUGGESTION rows
        fputcsv($handle, ['SUGGESTION', '', 'jane@example.com', '', '', '', '', '', '', '', '', '', '', '', '', 'More Lab Hours', 'Please extend lab hours during weekends.', 'pending']);

        fclose($handle);

        return [
            'success' => true,
            'filepath' => $filepath,
            'filename' => $filename
        ];
    }

    /**
     * Generate Excel template with instructions
     */
    private function generateExcelTemplate()
    {
        $spreadsheet = new Spreadsheet();

        // ---- Instructions sheet ----
        $instructions = $spreadsheet->getActiveSheet();
        $instructions->setTitle('Instructions');

        $instructions->setCellValue('A1', 'User Import Template - Instructions');
        $instructions->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'DC2626']]
        ]);
        $instructions->mergeCells('A1:F1');

        $instr = [
            ['', ''],
            ['HOW TO USE THIS TEMPLATE', ''],
            ['', ''],
            ['1. ROW TYPES', 'Each row must start with a row_type: USER, SURVEY, REVIEW, COMPLAINT, or SUGGESTION'],
            ['2. USER ROWS', 'Create new user accounts. Required: username, email, role. Optional: dob, division, roll_no, course (students), department, subjects, experience (teachers)'],
            ['3. SURVEY ROWS', 'Add survey responses. Required: email (of existing/imported user), question_id, rating (1-5)'],
            ['4. REVIEW ROWS', 'Add teacher ratings. Required: email (student), teacher_email, rating (1-5). Optional: comment'],
            ['5. COMPLAINT ROWS', 'Add complaints. Required: email, subject, description. Optional: status (pending/in_progress/resolved)'],
            ['6. SUGGESTION ROWS', 'Add suggestions. Required: email, subject, description. Optional: status'],
            ['', ''],
            ['IMPORTANT NOTES', ''],
            ['- Default Password', 'All imported users will receive the default password: password123'],
            ['- Duplicate Emails', 'Users with existing emails will be skipped (not duplicated)'],
            ['- Related Data', 'SURVEY, REVIEW, COMPLAINT, SUGGESTION rows use the email column to link to users'],
            ['- Email Linking', 'You can reference newly imported users in the same file by their email'],
            ['- Roles', 'Valid roles: student, teacher, admin'],
            ['- Ratings', 'Must be between 1 and 5'],
        ];

        $row = 2;
        foreach ($instr as $line) {
            $instructions->setCellValue("A{$row}", $line[0]);
            $instructions->setCellValue("B{$row}", $line[1]);
            if (!empty($line[0]) && empty($line[1])) {
                $instructions->getStyle("A{$row}")->getFont()->setBold(true)->setSize(13);
            }
            if (!empty($line[0]) && !empty($line[1])) {
                $instructions->getStyle("A{$row}")->getFont()->setBold(true);
            }
            $row++;
        }

        $instructions->getColumnDimension('A')->setWidth(25);
        $instructions->getColumnDimension('B')->setWidth(80);

        // ---- Data sheet ----
        $dataSheet = $spreadsheet->createSheet();
        $dataSheet->setTitle('Import Data');

        $headers = [
            'row_type', 'username', 'email', 'role', 'dob',
            'division', 'roll_no', 'course', 'department', 'subjects', 'experience',
            'question_id', 'rating', 'teacher_email', 'comment',
            'subject', 'description', 'status'
        ];
        $this->writeSheetHeader($dataSheet, $headers);

        // Sample data
        $sampleData = [
            ['USER', 'john_doe', 'john@example.com', 'student', '2000-05-15', 'Computer Science A', 'CS001', 'B.Tech CS', '', '', '', '', '', '', '', '', '', ''],
            ['USER', 'jane_smith', 'jane@example.com', 'student', '2001-03-20', 'Electronics B', 'EC045', 'B.Tech EC', '', '', '', '', '', '', '', '', '', ''],
            ['USER', 'prof_wilson', 'wilson@example.com', 'teacher', '1980-01-15', '', '', '', 'Computer Science', 'Programming, Data Structures', '10', '', '', '', '', '', '', ''],
            ['USER', 'admin_user', 'admin2@example.com', 'admin', '1990-06-01', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['SURVEY', '', 'john@example.com', '', '', '', '', '', '', '', '', '1', '4', '', '', '', '', ''],
            ['SURVEY', '', 'jane@example.com', '', '', '', '', '', '', '', '', '1', '3', '', '', '', '', ''],
            ['REVIEW', '', 'john@example.com', '', '', '', '', '', '', '', '', '', '5', 'wilson@example.com', 'Excellent teacher!', '', '', ''],
            ['COMPLAINT', '', 'john@example.com', '', '', '', '', '', '', '', '', '', '', '', '', 'Broken AC', 'AC in room 301 not working', 'pending'],
            ['SUGGESTION', '', 'jane@example.com', '', '', '', '', '', '', '', '', '', '', '', '', 'More Lab Hours', 'Extend lab hours on weekends', 'pending'],
        ];

        $rowIdx = 2;
        foreach ($sampleData as $data) {
            $dataSheet->fromArray($data, null, "A{$rowIdx}");
            $rowIdx++;
        }

        $this->autoSizeColumns($dataSheet, count($headers));

        // Set active sheet to data
        $spreadsheet->setActiveSheetIndex(1);

        $filename = 'import_template.xlsx';
        $filepath = __DIR__ . '/../../storage/templates/' . $filename;
        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);

        return [
            'success' => true,
            'filepath' => $filepath,
            'filename' => $filename
        ];
    }
}
