<?php
// Include config file
require_once "../../core/includes/config.php";
require_once "../../core/includes/functions.php";

// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isLoggedIn() || !hasRole("admin")) {
    header("location: ../../public/login.php");
    exit;
}

// Initialize variables
$alertType = $alertMessage = "";
$newCode = $newRole = $newDescription = "";
$newMaxUses = 100;
$newExpiresAt = "";

// Process form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Generate new access code
    if (isset($_POST['generate_code'])) {
        $newRole = trim($_POST['code_role']);
        $newDescription = trim($_POST['code_description']);
        $newMaxUses = intval($_POST['max_uses']);
        $newExpiresAt = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
        $customCode = trim($_POST['custom_code'] ?? '');
        
        if (empty($newRole) || !in_array($newRole, ['teacher', 'admin'])) {
            $alertType = "danger";
            $alertMessage = "Please select a valid role (Teacher or Admin).";
        } elseif ($newMaxUses < 1) {
            $alertType = "danger";
            $alertMessage = "Maximum uses must be at least 1.";
        } else {
            $createdBy = $_SESSION['id'];
            $result = generateAccessCode($newRole, $newMaxUses, $newDescription, $newExpiresAt, $createdBy, $conn, $customCode);
            
            if ($result['success']) {
                $alertType = "success";
                $alertMessage = "Access code <strong>" . htmlspecialchars($result['code']) . "</strong> has been generated successfully!";
                // Reset form
                $newCode = $newRole = $newDescription = "";
                $newMaxUses = 100;
                $newExpiresAt = "";
            } else {
                $alertType = "danger";
                $alertMessage = $result['message'];
            }
        }
    }
    
    // Toggle code status
    if (isset($_POST['toggle_status'])) {
        $codeId = intval($_POST['code_id']);
        $result = toggleAccessCode($codeId, $conn);
        
        if ($result['success']) {
            $alertType = "success";
            $alertMessage = $result['message'];
        } else {
            $alertType = "danger";
            $alertMessage = $result['message'];
        }
    }
    
    // Delete code
    if (isset($_POST['delete_code'])) {
        $codeId = intval($_POST['code_id']);
        $result = deleteAccessCode($codeId, $conn);
        
        if ($result['success']) {
            $alertType = "success";
            $alertMessage = $result['message'];
        } else {
            $alertType = "danger";
            $alertMessage = $result['message'];
        }
    }
}

// Get all access codes
$accessCodes = getAllAccessCodes($conn);

// Set page variables
$pageTitle = "Access Code Management - Admin Dashboard";
$basePath = "../../";
?>

<?php include '../../core/includes/header.php'; ?>

<style>
:root {
    --primary-color: #6366f1;
    --primary-light: #818cf8;
    --primary-dark: #4f46e5;
    --accent-color: #f59e0b;
    --success-color: #10b981;
    --danger-color: #ef4444;
    --text-primary: #1e293b;
    --text-secondary: #475569;
    --text-muted: #94a3b8;
    --gray-50: #f8fafc;
    --gray-100: #f1f5f9;
    --gray-200: #e2e8f0;
    --radius-sm: 0.375rem;
    --radius-md: 0.5rem;
    --radius-lg: 0.75rem;
    --radius-xl: 1rem;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.page-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    color: white;
    padding: 2rem;
    border-radius: var(--radius-xl);
    margin-bottom: 2rem;
    box-shadow: var(--shadow-lg);
}

.page-header h1 {
    margin: 0;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.page-header p {
    margin: 0.5rem 0 0 0;
    opacity: 0.9;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    box-shadow: var(--shadow-md);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: white;
}

.stat-icon.primary { background: linear-gradient(135deg, var(--primary-color), var(--primary-light)); }
.stat-icon.success { background: linear-gradient(135deg, #10b981, #34d399); }
.stat-icon.warning { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
.stat-icon.danger { background: linear-gradient(135deg, #ef4444, #f87171); }

.stat-content h3 {
    margin: 0;
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text-primary);
}

.stat-content p {
    margin: 0;
    color: var(--text-muted);
    font-size: 0.875rem;
}

.card-modern {
    background: white;
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-md);
    overflow: hidden;
    margin-bottom: 2rem;
}

.card-header-modern {
    background: var(--gray-50);
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--gray-200);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.card-header-modern h5 {
    margin: 0;
    font-weight: 600;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-body-modern {
    padding: 1.5rem;
}

.form-floating > label {
    color: var(--text-muted);
}

.form-control:focus,
.form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.15);
}

.btn-generate {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
    border: none;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-generate:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.code-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.code-table thead th {
    background: var(--gray-50);
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: var(--text-secondary);
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 2px solid var(--gray-200);
}

.code-table tbody td {
    padding: 1rem;
    border-bottom: 1px solid var(--gray-100);
    vertical-align: middle;
}

.code-table tbody tr:hover {
    background: var(--gray-50);
}

.code-badge {
    font-family: 'Fira Code', 'Consolas', monospace;
    font-size: 0.9rem;
    font-weight: 600;
    padding: 0.5rem 0.75rem;
    background: linear-gradient(135deg, var(--gray-100), var(--gray-200));
    border-radius: var(--radius-md);
    letter-spacing: 1px;
    display: inline-block;
}

.role-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.375rem 0.75rem;
    border-radius: var(--radius-md);
    font-size: 0.8rem;
    font-weight: 600;
}

.role-badge.teacher {
    background: #dbeafe;
    color: #1d4ed8;
}

.role-badge.admin {
    background: #fae8ff;
    color: #a21caf;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-badge.active {
    background: #d1fae5;
    color: #059669;
}

.status-badge.inactive {
    background: #fee2e2;
    color: #dc2626;
}

.status-badge.expired {
    background: #fef3c7;
    color: #d97706;
}

.usage-bar {
    width: 100px;
    height: 8px;
    background: var(--gray-200);
    border-radius: 4px;
    overflow: hidden;
    display: inline-block;
    margin-right: 0.5rem;
}

.usage-fill {
    height: 100%;
    background: var(--success-color);
    border-radius: 4px;
    transition: width 0.3s ease;
}

.usage-fill.warning { background: var(--accent-color); }
.usage-fill.danger { background: var(--danger-color); }

.action-btns {
    display: flex;
    gap: 0.5rem;
}

.btn-action {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-md);
    border: 1px solid var(--gray-200);
    background: white;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-action:hover {
    background: var(--gray-50);
    border-color: var(--gray-300);
}

.btn-action.toggle:hover {
    background: #dbeafe;
    border-color: #3b82f6;
    color: #1d4ed8;
}

.btn-action.delete:hover {
    background: #fee2e2;
    border-color: #ef4444;
    color: #dc2626;
}

.btn-action.copy:hover {
    background: #d1fae5;
    border-color: #10b981;
    color: #059669;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: var(--text-muted);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state h5 {
    color: var(--text-secondary);
    margin-bottom: 0.5rem;
}

@media (max-width: 992px) {
    .code-table {
        display: block;
        overflow-x: auto;
    }
}

/* Copy Animation */
.copy-success {
    position: fixed;
    top: 20px;
    right: 20px;
    background: var(--success-color);
    color: white;
    padding: 0.75rem 1.25rem;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-lg);
    z-index: 9999;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
</style>

<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="fas fa-key"></i> Access Code Management</h1>
        <p>Generate and manage access codes for teacher and admin registrations</p>
    </div>

    <!-- Alert -->
    <?php if (!empty($alertMessage)): ?>
    <div class="alert alert-<?php echo $alertType; ?> alert-dismissible fade show" role="alert">
        <i class="fas fa-<?php echo $alertType === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
        <?php echo $alertMessage; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Statistics -->
    <?php
    $totalCodes = count($accessCodes);
    $activeCodes = count(array_filter($accessCodes, fn($c) => $c['is_active'] && (!$c['expires_at'] || strtotime($c['expires_at']) > time())));
    $totalUsage = array_sum(array_column($accessCodes, 'current_uses'));
    $teacherCodes = count(array_filter($accessCodes, fn($c) => $c['role'] === 'teacher'));
    ?>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-key"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $totalCodes; ?></h3>
                <p>Total Codes</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $activeCodes; ?></h3>
                <p>Active Codes</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $totalUsage; ?></h3>
                <p>Total Registrations</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon danger">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $teacherCodes; ?> / <?php echo $totalCodes - $teacherCodes; ?></h3>
                <p>Teacher / Admin</p>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Generate New Code -->
        <div class="col-lg-4">
            <div class="card-modern">
                <div class="card-header-modern">
                    <h5><i class="fas fa-plus-circle text-primary"></i> Generate New Code</h5>
                </div>
                <div class="card-body-modern">
                    <form method="post" action="">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Role *</label>
                            <select name="code_role" class="form-select" required>
                                <option value="">Select Role</option>
                                <option value="teacher" <?php echo ($newRole === 'teacher') ? 'selected' : ''; ?>>Teacher</option>
                                <option value="admin" <?php echo ($newRole === 'admin') ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Custom Code (Optional)</label>
                            <input type="text" name="custom_code" class="form-control" 
                                   placeholder="Leave empty for auto-generated"
                                   style="text-transform: uppercase; letter-spacing: 1px;">
                            <small class="text-muted">If empty, a random code will be generated</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Maximum Uses *</label>
                            <input type="number" name="max_uses" class="form-control" 
                                   value="<?php echo $newMaxUses; ?>" min="1" max="10000" required>
                            <small class="text-muted">How many times this code can be used</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Expiration Date (Optional)</label>
                            <input type="datetime-local" name="expires_at" class="form-control" 
                                   value="<?php echo $newExpiresAt; ?>">
                            <small class="text-muted">Leave empty for no expiration</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="code_description" class="form-control" rows="2" 
                                      placeholder="e.g., 'For Computer Science Department'"><?php echo htmlspecialchars($newDescription); ?></textarea>
                        </div>
                        
                        <button type="submit" name="generate_code" class="btn btn-primary btn-generate w-100">
                            <i class="fas fa-magic me-2"></i>Generate Access Code
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Access Codes List -->
        <div class="col-lg-8">
            <div class="card-modern">
                <div class="card-header-modern">
                    <h5><i class="fas fa-list text-primary"></i> Access Codes</h5>
                    <span class="badge bg-primary"><?php echo count($accessCodes); ?> codes</span>
                </div>
                <div class="card-body-modern p-0">
                    <?php if (empty($accessCodes)): ?>
                    <div class="empty-state">
                        <i class="fas fa-key"></i>
                        <h5>No Access Codes Yet</h5>
                        <p>Generate your first access code to get started</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="code-table">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Role</th>
                                    <th>Usage</th>
                                    <th>Status</th>
                                    <th>Expires</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($accessCodes as $code): ?>
                                <?php
                                    $isExpired = $code['expires_at'] && strtotime($code['expires_at']) < time();
                                    $usagePercent = $code['max_uses'] > 0 ? ($code['current_uses'] / $code['max_uses']) * 100 : 0;
                                    $usageClass = $usagePercent >= 90 ? 'danger' : ($usagePercent >= 70 ? 'warning' : '');
                                    
                                    if ($isExpired) {
                                        $statusClass = 'expired';
                                        $statusText = 'Expired';
                                    } elseif (!$code['is_active']) {
                                        $statusClass = 'inactive';
                                        $statusText = 'Inactive';
                                    } else {
                                        $statusClass = 'active';
                                        $statusText = 'Active';
                                    }
                                ?>
                                <tr>
                                    <td>
                                        <span class="code-badge" id="code-<?php echo $code['id']; ?>"><?php echo htmlspecialchars($code['code']); ?></span>
                                        <?php if (!empty($code['description'])): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($code['description']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="role-badge <?php echo $code['role']; ?>">
                                            <i class="fas fa-<?php echo $code['role'] === 'teacher' ? 'chalkboard-teacher' : 'user-shield'; ?>"></i>
                                            <?php echo ucfirst($code['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="usage-bar">
                                                <div class="usage-fill <?php echo $usageClass; ?>" style="width: <?php echo min(100, $usagePercent); ?>%"></div>
                                            </div>
                                            <small><?php echo $code['current_uses']; ?>/<?php echo $code['max_uses']; ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $statusClass; ?>">
                                            <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                                            <?php echo $statusText; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($code['expires_at']): ?>
                                            <small class="<?php echo $isExpired ? 'text-danger' : ''; ?>">
                                                <?php echo date('M d, Y', strtotime($code['expires_at'])); ?>
                                            </small>
                                        <?php else: ?>
                                            <small class="text-muted">Never</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-btns">
                                            <button type="button" class="btn-action copy" 
                                                    onclick="copyCode('<?php echo htmlspecialchars($code['code']); ?>')"
                                                    title="Copy Code">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="code_id" value="<?php echo $code['id']; ?>">
                                                <button type="submit" name="toggle_status" class="btn-action toggle"
                                                        title="<?php echo $code['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                                    <i class="fas fa-<?php echo $code['is_active'] ? 'pause' : 'play'; ?>"></i>
                                                </button>
                                            </form>
                                            <form method="post" style="display: inline;" 
                                                  onsubmit="return confirm('Are you sure you want to delete this access code?');">
                                                <input type="hidden" name="code_id" value="<?php echo $code['id']; ?>">
                                                <button type="submit" name="delete_code" class="btn-action delete" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyCode(code) {
    navigator.clipboard.writeText(code).then(function() {
        // Show success message
        const toast = document.createElement('div');
        toast.className = 'copy-success';
        toast.innerHTML = '<i class="fas fa-check me-2"></i>Code copied to clipboard!';
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 2000);
    }).catch(function(err) {
        alert('Failed to copy code. Please copy manually.');
    });
}
</script>

<?php include '../../core/includes/footer.php'; ?>

<?php
// Close connection
closeConnection($conn);
?>
