<?php
// Include config file
require_once "../../core/includes/config.php";
require_once "../../core/includes/functions.php";
require_once "../../core/classes/UserImporter.php";

// Initialize the session
session_start();

// Check if the user is logged in as admin
if (!isLoggedIn() || !hasRole("admin")) {
    header("location: ../../public/login.php");
    exit;
}

$alertType = $alertMessage = "";
$importResult = null;

// Handle file upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['import_users'])) {
    if (isset($_FILES['import_file'])) {
        $importer = new UserImporter($conn);
        $sendEmails = isset($_POST['send_emails']) ? true : false;

        // Parse the file
        $parseResult = $importer->parseFile($_FILES['import_file']);

        if ($parseResult['success']) {
            // Process the import
            $importResult = $importer->processImport($parseResult['data'], $sendEmails);

            if ($importResult['users_imported'] > 0) {
                $alertType = "success";
                $alertMessage = "Import completed! {$importResult['users_imported']} users imported successfully.";
                if ($importResult['users_skipped'] > 0) {
                    $alertMessage .= " {$importResult['users_skipped']} users skipped (already exist).";
                }
            } elseif ($importResult['users_skipped'] > 0) {
                $alertType = "warning";
                $alertMessage = "All {$importResult['users_skipped']} users already exist in the system. No new users were created.";
            } else {
                $alertType = "danger";
                $alertMessage = "No users could be imported. Please check the error report below.";
            }
        } else {
            $alertType = "danger";
            $alertMessage = $parseResult['error'];
        }
    } else {
        $alertType = "danger";
        $alertMessage = "Please select a file to upload.";
    }
}

// Handle template download (must be before any HTML output)
if (isset($_GET['download_template'])) {
    $format = $_GET['download_template'] === 'xlsx' ? 'xlsx' : 'csv';
    require_once "../../core/classes/UserExporter.php";
    $exporter = new UserExporter($conn);
    $template = $exporter->generateTemplate($format);
    if ($template['success']) {
        UserExporter::downloadFile($template['filepath'], $template['filename'], $format);
        exit;
    }
}

// Handle error report download
if (isset($_GET['download_errors']) && isset($_SESSION['import_errors'])) {
    $importer = new UserImporter($conn);
    $errorFile = $importer->generateErrorReport($_SESSION['import_errors']);
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="import_errors_' . date('Ymd_His') . '.csv"');
    header('Content-Length: ' . filesize($errorFile));
    readfile($errorFile);
    @unlink($errorFile);
    exit;
}

// Store errors in session for potential download
if ($importResult && !empty($importResult['errors'])) {
    $_SESSION['import_errors'] = $importResult['errors'];
}

// Fetch import history
$importHistory = [];
$sql = "SELECT il.*, u.username as admin_name 
        FROM import_logs il 
        LEFT JOIN users u ON il.admin_id = u.id 
        ORDER BY il.created_at DESC 
        LIMIT 20";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $importHistory[] = $row;
    }
}

$pageTitle = "Import Users";
$basePath = "../../";
?>

<?php include '../../core/includes/header.php'; ?>

<style>
:root {
    --admin-primary: #dc2626;
    --admin-secondary: #ef4444;
    --admin-accent: #f87171;
    --radius-xl: 16px;
    --radius-lg: 12px;
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-600: #4b5563;
    --gray-700: #374151;
    --gray-800: #1f2937;
}

.page-header-modern {
    background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
    border-radius: var(--radius-xl);
    padding: 2rem 2.5rem;
    margin-bottom: 2rem;
    color: white;
    box-shadow: var(--shadow-lg);
    position: relative;
    overflow: hidden;
}

.page-header-modern::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
}

.page-header-modern h2 { margin: 0; font-weight: 700; font-size: 1.75rem; }
.page-header-modern p { margin: 0.5rem 0 0; opacity: 0.9; }

.card-modern {
    background: white;
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--gray-100);
    overflow: hidden;
    margin-bottom: 1.5rem;
}

.card-header-modern {
    background: var(--gray-50);
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--gray-200);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.card-header-modern h3 {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--gray-800);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-body-modern { padding: 1.5rem; }

.upload-zone {
    border: 3px dashed var(--gray-200);
    border-radius: var(--radius-xl);
    padding: 3rem 2rem;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
    background: var(--gray-50);
    position: relative;
}

.upload-zone:hover, .upload-zone.dragover {
    border-color: #10b981;
    background: rgba(16, 185, 129, 0.05);
}

.upload-zone .upload-icon {
    font-size: 3rem;
    color: #10b981;
    margin-bottom: 1rem;
}

.upload-zone h4 { color: var(--gray-800); margin-bottom: 0.5rem; }
.upload-zone p { color: var(--gray-600); font-size: 0.875rem; margin: 0; }

.upload-zone input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.file-info {
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    border-radius: var(--radius-lg);
    padding: 1rem;
    margin-top: 1rem;
    display: none;
}

.file-info.visible { display: flex; align-items: center; gap: 1rem; }
.file-info .file-icon { font-size: 2rem; color: #10b981; }
.file-info .file-name { font-weight: 600; color: var(--gray-800); }
.file-info .file-size { color: var(--gray-600); font-size: 0.875rem; }

.btn-modern {
    padding: 0.75rem 1.5rem;
    border-radius: var(--radius-lg);
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-modern:hover { transform: translateY(-2px); }

.btn-import {
    background: linear-gradient(135deg, #10b981, #34d399);
    color: white;
}
.btn-import:hover { box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4); color: white; }

.btn-template {
    background: linear-gradient(135deg, #3b82f6, #60a5fa);
    color: white;
}
.btn-template:hover { box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4); color: white; }

.btn-back {
    background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary));
    color: white;
}
.btn-back:hover { box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4); color: white; }

.result-card {
    border-radius: var(--radius-xl);
    padding: 1.5rem;
    margin-bottom: 1rem;
}

.result-card.success { background: #ecfdf5; border: 1px solid #a7f3d0; }
.result-card.error { background: #fef2f2; border: 1px solid #fecaca; }
.result-card.warning { background: #fffbeb; border: 1px solid #fde68a; }
.result-card.info { background: #eff6ff; border: 1px solid #bfdbfe; }

.stat-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 9999px;
    font-weight: 600;
    font-size: 0.875rem;
    margin: 0.25rem;
}

.stat-pill.green { background: #d1fae5; color: #065f46; }
.stat-pill.blue { background: #dbeafe; color: #1e40af; }
.stat-pill.red { background: #fee2e2; color: #991b1b; }
.stat-pill.yellow { background: #fef3c7; color: #92400e; }
.stat-pill.purple { background: #ede9fe; color: #5b21b6; }

.error-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.error-table thead th {
    background: #fee2e2;
    padding: 0.75rem 1rem;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    color: #991b1b;
    border-bottom: 2px solid #fecaca;
}

.error-table tbody td {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--gray-100);
    font-size: 0.875rem;
}

.error-table tbody tr:hover { background: #fef2f2; }

.format-guide {
    background: var(--gray-50);
    border-radius: var(--radius-lg);
    padding: 1.25rem;
    margin-top: 1rem;
}

.format-guide h5 {
    color: var(--gray-800);
    margin-bottom: 0.75rem;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.format-guide code {
    display: block;
    background: var(--gray-800);
    color: #10b981;
    padding: 1rem;
    border-radius: var(--radius-lg);
    font-size: 0.8rem;
    overflow-x: auto;
    white-space: pre;
}

.history-table {
    width: 100%;
    margin: 0;
}

.history-table thead th {
    background: var(--gray-50);
    border-bottom: 2px solid var(--gray-200);
    padding: 0.75rem 1rem;
    font-weight: 600;
    color: var(--gray-700);
    text-transform: uppercase;
    font-size: 0.75rem;
}

.history-table tbody td {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--gray-100);
    font-size: 0.875rem;
}

.history-table tbody tr:hover { background: var(--gray-50); }

.email-option {
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: var(--radius-lg);
    padding: 1rem 1.25rem;
    margin-top: 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.email-option .form-check-input {
    width: 1.5em;
    height: 1.5em;
}
</style>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="page-header-modern">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-file-import me-2"></i>Import Users</h2>
                <p>Upload CSV or Excel files to bulk import users with surveys, reviews, complaints & suggestions</p>
            </div>
            <div class="d-flex gap-2">
                <a href="user_export.php" class="btn btn-light">
                    <i class="fas fa-file-export me-2"></i>Export Data
                </a>
                <a href="user_management.php" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Users
                </a>
            </div>
        </div>
    </div>

    <?php if (!empty($alertMessage)): ?>
        <div class="alert alert-<?php echo $alertType; ?> alert-dismissible fade show" role="alert" style="border-radius: var(--radius-lg);">
            <i class="fas fa-<?php echo $alertType === 'success' ? 'check-circle' : ($alertType === 'warning' ? 'exclamation-triangle' : 'times-circle'); ?> me-2"></i>
            <?php echo htmlspecialchars($alertMessage); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Upload Section -->
        <div class="col-lg-8">
            <div class="card-modern">
                <div class="card-header-modern">
                    <h3><i class="fas fa-cloud-upload-alt me-2"></i>Upload File</h3>
                    <div class="d-flex gap-2">
                        <a href="?download_template=csv" class="btn-modern btn-template btn-sm">
                            <i class="fas fa-file-csv"></i> CSV Template
                        </a>
                        <a href="?download_template=xlsx" class="btn-modern btn-template btn-sm">
                            <i class="fas fa-file-excel"></i> Excel Template
                        </a>
                    </div>
                </div>
                <div class="card-body-modern">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data" id="importForm">
                        <input type="hidden" name="import_users" value="1">

                        <div class="upload-zone" id="uploadZone">
                            <input type="file" name="import_file" id="importFile" accept=".csv,.xlsx,.xls" required>
                            <div class="upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <h4>Drag & Drop your file here</h4>
                            <p>or click to browse. Supported formats: CSV, XLSX, XLS (Max 10MB)</p>
                        </div>

                        <div class="file-info" id="fileInfo">
                            <div class="file-icon">
                                <i class="fas fa-file-spreadsheet" id="fileIcon"></i>
                            </div>
                            <div>
                                <div class="file-name" id="fileName">filename.csv</div>
                                <div class="file-size" id="fileSize">0 KB</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-auto" id="removeFile">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="email-option">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="sendEmails" name="send_emails">
                                <label class="form-check-label" for="sendEmails">
                                    <strong>Send email notifications</strong>
                                    <br><small class="text-muted">Send login credentials to newly created users via email</small>
                                </label>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn-modern btn-import" id="importBtn" disabled>
                                <i class="fas fa-file-import"></i> Import Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($importResult): ?>
            <!-- Import Results -->
            <div class="card-modern">
                <div class="card-header-modern">
                    <h3><i class="fas fa-chart-bar me-2"></i>Import Results</h3>
                    <?php if (!empty($importResult['errors'])): ?>
                        <a href="?download_errors=1" class="btn-modern btn-sm" style="background: #ef4444; color: white;">
                            <i class="fas fa-download"></i> Download Error Report
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body-modern">
                    <!-- Stats -->
                    <div class="mb-4">
                        <span class="stat-pill green">
                            <i class="fas fa-user-plus"></i> <?php echo $importResult['users_imported']; ?> Users Imported
                        </span>
                        <span class="stat-pill yellow">
                            <i class="fas fa-forward"></i> <?php echo $importResult['users_skipped']; ?> Skipped
                        </span>
                        <span class="stat-pill blue">
                            <i class="fas fa-question-circle"></i> <?php echo $importResult['questions_imported']; ?> Questions
                        </span>
                        <span class="stat-pill blue">
                            <i class="fas fa-poll"></i> <?php echo $importResult['surveys_imported']; ?> Surveys
                        </span>
                        <span class="stat-pill purple">
                            <i class="fas fa-star"></i> <?php echo $importResult['reviews_imported']; ?> Reviews
                        </span>
                        <span class="stat-pill blue">
                            <i class="fas fa-comment-dots"></i> <?php echo $importResult['complaints_imported']; ?> Complaints
                        </span>
                        <span class="stat-pill green">
                            <i class="fas fa-lightbulb"></i> <?php echo $importResult['suggestions_imported']; ?> Suggestions
                        </span>
                        <?php if ($importResult['emails_sent'] > 0): ?>
                        <span class="stat-pill yellow">
                            <i class="fas fa-envelope"></i> <?php echo $importResult['emails_sent']; ?> Emails Sent
                        </span>
                        <?php endif; ?>
                        <?php if (!empty($importResult['errors'])): ?>
                        <span class="stat-pill red">
                            <i class="fas fa-exclamation-circle"></i> <?php echo count($importResult['errors']); ?> Errors
                        </span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($importResult['imported_users'])): ?>
                    <!-- Imported Users List -->
                    <div class="result-card success mb-3">
                        <h5 class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Successfully Imported Users</h5>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Password</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($importResult['imported_users'] as $user): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'teacher' ? 'success' : 'primary'); ?>"><?php echo ucfirst($user['role']); ?></span></td>
                                        <td><code>password123</code></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($importResult['warnings'])): ?>
                    <!-- Warnings -->
                    <div class="result-card warning mb-3">
                        <h5 class="mb-2"><i class="fas fa-exclamation-triangle text-warning me-2"></i>Warnings</h5>
                        <ul class="mb-0">
                            <?php foreach ($importResult['warnings'] as $warn): ?>
                            <li>Row <?php echo $warn['row']; ?>: <?php echo htmlspecialchars($warn['message']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($importResult['errors'])): ?>
                    <!-- Errors -->
                    <div class="result-card error">
                        <h5 class="mb-2"><i class="fas fa-times-circle text-danger me-2"></i>Errors (<?php echo count($importResult['errors']); ?>)</h5>
                        <div class="table-responsive">
                            <table class="error-table">
                                <thead>
                                    <tr>
                                        <th>Row</th>
                                        <th>Type</th>
                                        <th>Field</th>
                                        <th>Error</th>
                                        <th>Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($importResult['errors'] as $error): ?>
                                    <tr>
                                        <td><strong>#<?php echo $error['row']; ?></strong></td>
                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($error['type']); ?></span></td>
                                        <td><?php echo htmlspecialchars($error['field']); ?></td>
                                        <td><?php echo htmlspecialchars($error['message']); ?></td>
                                        <td class="text-muted"><?php echo htmlspecialchars($error['data'] ?? ''); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Import History -->
            <?php if (!empty($importHistory)): ?>
            <div class="card-modern">
                <div class="card-header-modern">
                    <h3><i class="fas fa-history me-2"></i>Import History</h3>
                </div>
                <div class="card-body-modern">
                    <div class="table-responsive">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Admin</th>
                                    <th>Total Rows</th>
                                    <th>Imported</th>
                                    <th>Errors</th>
                                    <th>Skipped</th>
                                    <th>Emails</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($importHistory as $log): ?>
                                <tr>
                                    <td><?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?></td>
                                    <td><strong><?php echo htmlspecialchars($log['admin_name'] ?? 'Unknown'); ?></strong></td>
                                    <td><?php echo $log['total_rows']; ?></td>
                                    <td><span class="text-success fw-bold"><?php echo $log['successful'] ?? 0; ?></span></td>
                                    <td><span class="text-danger fw-bold"><?php echo $log['failed'] ?? 0; ?></span></td>
                                    <td><?php echo $log['warnings'] ?? 0; ?></td>
                                    <td>-</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar - Format Guide -->
        <div class="col-lg-4">
            <div class="card-modern">
                <div class="card-header-modern">
                    <h3><i class="fas fa-info-circle me-2"></i>File Format Guide</h3>
                </div>
                <div class="card-body-modern">
                    <p class="text-muted mb-3">Your CSV/Excel file should have a <strong>row_type</strong> column as the first column to identify each row's purpose.</p>

                    <div class="format-guide">
                        <h5><i class="fas fa-user me-2 text-primary"></i>USER Rows</h5>
                        <p class="small text-muted mb-2">Creates new user accounts with default password: <code>password123</code></p>
                        <code>row_type,username,email,role,dob,division,roll_no,course,department,subjects,experience
USER,john_doe,john@example.com,student,2000-05-15,CS-A,CS001,B.Tech CS,,,,
USER,prof_wilson,wilson@ex.com,teacher,1980-01-15,,,,CS,Programming,10</code>
                    </div>

                    <div class="format-guide mt-3">
                        <h5><i class="fas fa-question-circle me-2 text-info"></i>QUESTION Rows</h5>
                        <p class="small text-muted mb-2">Add survey questions to the question bank</p>
                        <code>QUESTION,,,student,,,,,,,,,,,,Teaching Quality,How effective is the teaching?,active</code>
                        <small class="d-block mt-1 text-muted">Fields: role (student/teacher), subject, description, status</small>
                    </div>

                    <div class="format-guide mt-3">
                        <h5><i class="fas fa-poll me-2 text-success"></i>SURVEY Rows</h5>
                        <p class="small text-muted mb-2">Pre-fill survey responses linked by email</p>
                        <code>SURVEY,,john@example.com,,,,,,,,1,4,,,,,</code>
                        <small class="d-block mt-1 text-muted">Fields: email, question_id, rating (1-5)</small>
                    </div>

                    <div class="format-guide mt-3">
                        <h5><i class="fas fa-star me-2 text-warning"></i>REVIEW Rows</h5>
                        <p class="small text-muted mb-2">Add teacher ratings by students</p>
                        <code>REVIEW,,john@example.com,,,,,,,,,5,wilson@ex.com,Great!,,,</code>
                        <small class="d-block mt-1 text-muted">Fields: student email, rating, teacher_email, comment</small>
                    </div>

                    <div class="format-guide mt-3">
                        <h5><i class="fas fa-exclamation-circle me-2 text-danger"></i>COMPLAINT Rows</h5>
                        <p class="small text-muted mb-2">Add complaints linked to users</p>
                        <code>COMPLAINT,,john@example.com,,,,,,,,,,,,Broken AC,AC not working,pending</code>
                        <small class="d-block mt-1 text-muted">Fields: email, subject, description, status</small>
                    </div>

                    <div class="format-guide mt-3">
                        <h5><i class="fas fa-lightbulb me-2 text-info"></i>SUGGESTION Rows</h5>
                        <p class="small text-muted mb-2">Add suggestions linked to users</p>
                        <code>SUGGESTION,,jane@example.com,,,,,,,,,,,,More Labs,Need more practice labs,pending</code>
                        <small class="d-block mt-1 text-muted">Fields: email, subject, description, status</small>
                    </div>

                    <hr>
                    <div class="alert alert-info mb-0" style="border-radius: var(--radius-lg); font-size: 0.875rem;">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Tip:</strong> Download a template to see the exact format. You can mix USER, QUESTION, SURVEY, REVIEW, COMPLAINT, and SUGGESTION rows in the same file!
                    </div>
                </div>
            </div>

            <div class="card-modern">
                <div class="card-header-modern">
                    <h3><i class="fas fa-shield-alt me-2"></i>Default Credentials</h3>
                </div>
                <div class="card-body-modern">
                    <div class="alert alert-warning mb-0" style="border-radius: var(--radius-lg);">
                        <h6><i class="fas fa-key me-2"></i>Default Password</h6>
                        <p class="mb-1">All imported users will be assigned:</p>
                        <code class="d-block p-2 bg-white rounded">password123</code>
                        <small class="text-muted mt-1 d-block">Users should change their password after first login.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadZone = document.getElementById('uploadZone');
    const importFile = document.getElementById('importFile');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const fileIcon = document.getElementById('fileIcon');
    const removeBtn = document.getElementById('removeFile');
    const importBtn = document.getElementById('importBtn');

    if (!uploadZone || !importFile || !importBtn) {
        console.error('Import page: Required elements not found');
        return;
    }

    // File selection handler
    importFile.addEventListener('change', function() {
        if (this.files.length > 0) {
            showFileInfo(this.files[0]);
        }
    });

    // Drag and drop
    uploadZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });

    uploadZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });

    uploadZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        if (e.dataTransfer.files.length > 0) {
            importFile.files = e.dataTransfer.files;
            showFileInfo(e.dataTransfer.files[0]);
        }
    });

    // Remove file
    removeBtn.addEventListener('click', function() {
        importFile.value = '';
        fileInfo.classList.remove('visible');
        uploadZone.style.display = 'block';
        importBtn.disabled = true;
    });

    function showFileInfo(file) {
        const ext = file.name.split('.').pop().toLowerCase();
        const icons = {
            'csv': 'fas fa-file-csv text-success',
            'xlsx': 'fas fa-file-excel text-success',
            'xls': 'fas fa-file-excel text-success'
        };

        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        fileIcon.className = icons[ext] || 'fas fa-file';
        fileInfo.classList.add('visible');
        uploadZone.style.display = 'none';
        importBtn.disabled = false;
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
});
</script>

<?php include '../../core/includes/footer.php'; ?>

<?php
closeConnection($conn);
?>
