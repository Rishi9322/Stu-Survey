<?php
// Include config file
require_once "../../core/includes/config.php";
require_once "../../core/includes/functions.php";
require_once "../../core/classes/UserExporter.php";

// Initialize the session
session_start();

// Check if the user is logged in as admin
if (!isLoggedIn() || !hasRole("admin")) {
    header("location: ../../public/login.php");
    exit;
}

$alertType = $alertMessage = "";
$exportResult = null;

// Handle export request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['export_data'])) {
    $exporter = new UserExporter($conn);

    $filters = [];
    if (!empty($_POST['role'])) {
        $filters['role'] = $_POST['role'];
    }
    if (!empty($_POST['status'])) {
        $filters['status'] = $_POST['status'];
    }
    if (!empty($_POST['date_from'])) {
        $filters['date_from'] = $_POST['date_from'];
    }
    if (!empty($_POST['date_to'])) {
        $filters['date_to'] = $_POST['date_to'];
    }

    $format = ($_POST['format'] ?? 'csv') === 'xlsx' ? 'xlsx' : 'csv';

    $result = $exporter->export($filters, $format);

    if ($result['success']) {
        // Log the export
        logExport($conn, $filters, $format, $result['stats']);
        
        // Download the file
        UserExporter::downloadFile($result['filepath'], $result['filename'], $format);
        exit;
    } else {
        $alertType = "danger";
        $alertMessage = "Export failed. Please try again.";
    }
}

// Handle quick export links
if (isset($_GET['quick_export'])) {
    $exporter = new UserExporter($conn);
    $filters = [];
    $format = ($_GET['format'] ?? 'csv') === 'xlsx' ? 'xlsx' : 'csv';
    
    switch ($_GET['quick_export']) {
        case 'all':
            break; // no filters
        case 'students':
            $filters['role'] = 'student';
            break;
        case 'teachers':
            $filters['role'] = 'teacher';
            break;
        case 'admins':
            $filters['role'] = 'admin';
            break;
    }

    $result = $exporter->export($filters, $format);
    if ($result['success']) {
        logExport($conn, $filters, $format, $result['stats']);
        UserExporter::downloadFile($result['filepath'], $result['filename'], $format);
        exit;
    }
}

/**
 * Log export activity
 */
function logExport($conn, $filters, $format, $stats) {
    $createTable = "CREATE TABLE IF NOT EXISTS export_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        filters_json TEXT,
        format VARCHAR(10),
        user_count INT DEFAULT 0,
        survey_count INT DEFAULT 0,
        rating_count INT DEFAULT 0,
        feedback_count INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    mysqli_query($conn, $createTable);

    $adminId = $_SESSION['id'] ?? 0;
    $filtersJson = json_encode($filters);
    $userCount = $stats['users'] ?? 0;
    $surveyCount = $stats['surveys'] ?? 0;
    $ratingCount = $stats['ratings'] ?? 0;
    $feedbackCount = $stats['feedback'] ?? 0;

    $sql = "INSERT INTO export_logs (admin_id, filters_json, format, user_count, survey_count, rating_count, feedback_count) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "issiiii", $adminId, $filtersJson, $format, $userCount, $surveyCount, $ratingCount, $feedbackCount);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// Fetch export history
$exportHistory = [];
$sql = "SELECT el.*, u.username as admin_name 
        FROM export_logs el 
        LEFT JOIN users u ON el.admin_id = u.id 
        ORDER BY el.created_at DESC 
        LIMIT 20";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $exportHistory[] = $row;
    }
}

// Count data for preview
$userCounts = ['total' => 0, 'students' => 0, 'teachers' => 0, 'admins' => 0];
$result = mysqli_query($conn, "SELECT role, COUNT(*) as cnt FROM users GROUP BY role");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $userCounts[$row['role'] . 's'] = $row['cnt'];
        $userCounts['total'] += $row['cnt'];
    }
}

$surveyCount = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM survey_responses");
if ($result && $row = mysqli_fetch_assoc($result)) {
    $surveyCount = $row['cnt'];
}

$ratingCount = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM teacher_ratings");
if ($result && $row = mysqli_fetch_assoc($result)) {
    $ratingCount = $row['cnt'];
}

$feedbackCount = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM suggestions_complaints");
if ($result && $row = mysqli_fetch_assoc($result)) {
    $feedbackCount = $row['cnt'];
}

$pageTitle = "Export Data";
$basePath = "../../";
?>

<?php include '../../core/includes/header.php'; ?>

<style>
:root {
    --admin-primary: #dc2626;
    --admin-secondary: #ef4444;
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
    background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);
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

.stat-card {
    background: white;
    border-radius: var(--radius-xl);
    padding: 1.5rem;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--gray-100);
    transition: all 0.3s ease;
    text-align: center;
    cursor: pointer;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.stat-card .stat-icon {
    width: 56px;
    height: 56px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    margin: 0 auto 0.75rem;
}

.stat-card .stat-icon.blue { background: linear-gradient(135deg, #3b82f6, #60a5fa); }
.stat-card .stat-icon.green { background: linear-gradient(135deg, #10b981, #34d399); }
.stat-card .stat-icon.red { background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary)); }
.stat-card .stat-icon.purple { background: linear-gradient(135deg, #8b5cf6, #a78bfa); }
.stat-card .stat-icon.yellow { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
.stat-card .stat-icon.teal { background: linear-gradient(135deg, #14b8a6, #2dd4bf); }

.stat-card .stat-value { font-size: 1.75rem; font-weight: 700; color: var(--gray-800); }
.stat-card .stat-label { color: var(--gray-600); font-size: 0.8rem; margin-top: 0.25rem; }

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

.btn-export {
    background: linear-gradient(135deg, #8b5cf6, #a78bfa);
    color: white;
}
.btn-export:hover { box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4); color: white; }

.btn-csv {
    background: linear-gradient(135deg, #10b981, #34d399);
    color: white;
}
.btn-csv:hover { box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4); color: white; }

.btn-excel {
    background: linear-gradient(135deg, #3b82f6, #60a5fa);
    color: white;
}
.btn-excel:hover { box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4); color: white; }

.quick-export-card {
    border: 2px solid var(--gray-200);
    border-radius: var(--radius-xl);
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    display: block;
}

.quick-export-card:hover {
    border-color: #8b5cf6;
    background: rgba(139, 92, 246, 0.05);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    color: inherit;
    text-decoration: none;
}

.quick-export-card i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.quick-export-card h5 { margin: 0.5rem 0 0.25rem; font-size: 1rem; }
.quick-export-card p { margin: 0; font-size: 0.8rem; color: var(--gray-600); }

.form-section {
    background: var(--gray-50);
    border-radius: var(--radius-lg);
    padding: 1.25rem;
    margin-bottom: 1rem;
}

.form-section h5 {
    color: var(--gray-800);
    margin-bottom: 0.75rem;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.form-section label {
    font-weight: 500;
    color: var(--gray-700);
    font-size: 0.875rem;
}

.form-section select,
.form-section input[type="date"] {
    border: 2px solid var(--gray-200);
    border-radius: var(--radius-lg);
    padding: 0.625rem 1rem;
    transition: all 0.3s ease;
}

.form-section select:focus,
.form-section input[type="date"]:focus {
    border-color: #8b5cf6;
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
    outline: none;
}

.format-selector {
    display: flex;
    gap: 1rem;
    margin-top: 0.5rem;
}

.format-option {
    flex: 1;
    border: 2px solid var(--gray-200);
    border-radius: var(--radius-lg);
    padding: 1rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.format-option:hover {
    border-color: #8b5cf6;
}

.format-option.selected {
    border-color: #8b5cf6;
    background: rgba(139, 92, 246, 0.05);
}

.format-option input { display: none; }
.format-option i { font-size: 1.5rem; display: block; margin-bottom: 0.25rem; }
.format-option .label { font-weight: 600; font-size: 0.875rem; }

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
</style>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="page-header-modern">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-file-export me-2"></i>Export Data</h2>
                <p>Download user data with surveys, ratings, complaints & suggestions in CSV or Excel format</p>
            </div>
            <div class="d-flex gap-2">
                <a href="user_import.php" class="btn btn-light">
                    <i class="fas fa-file-import me-2"></i>Import Users
                </a>
                <a href="user_management.php" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Users
                </a>
            </div>
        </div>
    </div>

    <?php if (!empty($alertMessage)): ?>
        <div class="alert alert-<?php echo $alertType; ?> alert-dismissible fade show" role="alert" style="border-radius: var(--radius-lg);">
            <i class="fas fa-<?php echo $alertType === 'success' ? 'check-circle' : 'times-circle'; ?> me-2"></i>
            <?php echo htmlspecialchars($alertMessage); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Data Preview Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-users"></i></div>
                <div class="stat-value"><?php echo $userCounts['total']; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-user-graduate"></i></div>
                <div class="stat-value"><?php echo $userCounts['students']; ?></div>
                <div class="stat-label">Students</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-chalkboard-teacher"></i></div>
                <div class="stat-value"><?php echo $userCounts['teachers']; ?></div>
                <div class="stat-label">Teachers</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <div class="stat-icon yellow"><i class="fas fa-poll"></i></div>
                <div class="stat-value"><?php echo $surveyCount; ?></div>
                <div class="stat-label">Survey Responses</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <div class="stat-icon teal"><i class="fas fa-star"></i></div>
                <div class="stat-value"><?php echo $ratingCount; ?></div>
                <div class="stat-label">Teacher Ratings</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-comment-dots"></i></div>
                <div class="stat-value"><?php echo $feedbackCount; ?></div>
                <div class="stat-label">Feedback Items</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Quick Export Section -->
        <div class="col-lg-4">
            <div class="card-modern">
                <div class="card-header-modern">
                    <h3><i class="fas fa-bolt me-2"></i>Quick Export</h3>
                </div>
                <div class="card-body-modern">
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="?quick_export=all&format=csv" class="quick-export-card">
                                <i class="fas fa-file-csv text-success"></i>
                                <h5>All Data (CSV)</h5>
                                <p>Export everything</p>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="?quick_export=all&format=xlsx" class="quick-export-card">
                                <i class="fas fa-file-excel text-primary"></i>
                                <h5>All Data (Excel)</h5>
                                <p>Multi-sheet export</p>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="?quick_export=students&format=xlsx" class="quick-export-card">
                                <i class="fas fa-user-graduate text-info"></i>
                                <h5>Students</h5>
                                <p>Students only</p>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="?quick_export=teachers&format=xlsx" class="quick-export-card">
                                <i class="fas fa-chalkboard-teacher text-success"></i>
                                <h5>Teachers</h5>
                                <p>Teachers only</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Info -->
            <div class="card-modern">
                <div class="card-header-modern">
                    <h3><i class="fas fa-info-circle me-2"></i>Export Info</h3>
                </div>
                <div class="card-body-modern">
                    <div class="alert alert-info mb-3" style="border-radius: var(--radius-lg); font-size: 0.875rem;">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>CSV format</strong> creates a single file with row_type markers, compatible with the import feature for data migration.
                    </div>
                    <div class="alert alert-primary mb-0" style="border-radius: var(--radius-lg); font-size: 0.875rem;">
                        <i class="fas fa-file-excel me-2"></i>
                        <strong>Excel format</strong> creates a multi-sheet workbook with Users, Surveys, Ratings, Feedback, and a Summary sheet with statistics.
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Export Form -->
        <div class="col-lg-8">
            <div class="card-modern">
                <div class="card-header-modern">
                    <h3><i class="fas fa-filter me-2"></i>Advanced Export</h3>
                </div>
                <div class="card-body-modern">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <input type="hidden" name="export_data" value="1">

                        <div class="form-section">
                            <h5><i class="fas fa-sliders-h me-2"></i>Filters</h5>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="role" class="form-label">User Role</label>
                                    <select class="form-select" id="role" name="role">
                                        <option value="">All Roles</option>
                                        <option value="student">Students</option>
                                        <option value="teacher">Teachers</option>
                                        <option value="admin">Admins</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">All Status</option>
                                        <option value="active">Active Only</option>
                                        <option value="inactive">Inactive Only</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="date_from" class="form-label">From Date</label>
                                    <input type="date" class="form-control" id="date_from" name="date_from">
                                </div>
                                <div class="col-md-3">
                                    <label for="date_to" class="form-label">To Date</label>
                                    <input type="date" class="form-control" id="date_to" name="date_to">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h5><i class="fas fa-file me-2"></i>File Format</h5>
                            <div class="format-selector">
                                <label class="format-option selected" id="csvOption">
                                    <input type="radio" name="format" value="csv" checked>
                                    <i class="fas fa-file-csv text-success"></i>
                                    <div class="label">CSV</div>
                                    <small class="text-muted">Single file, import-compatible</small>
                                </label>
                                <label class="format-option" id="xlsxOption">
                                    <input type="radio" name="format" value="xlsx">
                                    <i class="fas fa-file-excel text-primary"></i>
                                    <div class="label">Excel (XLSX)</div>
                                    <small class="text-muted">Multi-sheet with summary</small>
                                </label>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn-modern btn-export">
                                <i class="fas fa-download"></i> Export Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Export History -->
            <?php if (!empty($exportHistory)): ?>
            <div class="card-modern">
                <div class="card-header-modern">
                    <h3><i class="fas fa-history me-2"></i>Export History</h3>
                </div>
                <div class="card-body-modern">
                    <div class="table-responsive">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Admin</th>
                                    <th>Format</th>
                                    <th>Users</th>
                                    <th>Surveys</th>
                                    <th>Ratings</th>
                                    <th>Feedback</th>
                                    <th>Filters</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($exportHistory as $log): ?>
                                <tr>
                                    <td><?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?></td>
                                    <td><strong><?php echo htmlspecialchars($log['admin_name'] ?? 'Unknown'); ?></strong></td>
                                    <td><span class="badge bg-<?php echo $log['format'] === 'xlsx' ? 'primary' : 'success'; ?>"><?php echo strtoupper($log['format']); ?></span></td>
                                    <td><?php echo $log['user_count']; ?></td>
                                    <td><?php echo $log['survey_count']; ?></td>
                                    <td><?php echo $log['rating_count']; ?></td>
                                    <td><?php echo $log['feedback_count']; ?></td>
                                    <td>
                                        <?php 
                                        $f = json_decode($log['filters_json'], true);
                                        echo !empty($f) ? htmlspecialchars(implode(', ', array_map(function($k, $v) { return "$k: $v"; }, array_keys($f), $f))) : '<span class="text-muted">None</span>';
                                        ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../core/includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Format selector toggle
    const formatOptions = document.querySelectorAll('.format-option');
    formatOptions.forEach(option => {
        option.addEventListener('click', function() {
            formatOptions.forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
            this.querySelector('input').checked = true;
        });
    });
});
</script>

<?php
closeConnection($conn);
?>
