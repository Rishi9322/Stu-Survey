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

// Process user activation/deactivation or role change
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_user_status'])) {
        $user_id = $_POST['user_id'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $sql = "UPDATE users SET is_active = ? WHERE id = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $is_active, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $alertType = "success";
                $alertMessage = "User status updated successfully.";
            } else {
                $alertType = "danger";
                $alertMessage = "Error: " . mysqli_error($conn);
            }
            
            mysqli_stmt_close($stmt);
        }
    } elseif (isset($_POST['update_user_role'])) {
        $user_id = $_POST['user_id'];
        $role = $_POST['role'];
        
        $sql = "UPDATE users SET role = ? WHERE id = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "si", $role, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $alertType = "success";
                $alertMessage = "User role updated successfully.";
            } else {
                $alertType = "danger";
                $alertMessage = "Error: " . mysqli_error($conn);
            }
            
            mysqli_stmt_close($stmt);
        }
    } elseif (isset($_POST['reset_password'])) {
        $user_id = $_POST['user_id'];
        $default_password = "password123"; // Default password for reset
        $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
        
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $alertType = "success";
                $alertMessage = "User password has been reset to '$default_password'. Please inform the user to change their password after login.";
            } else {
                $alertType = "danger";
                $alertMessage = "Error: " . mysqli_error($conn);
            }
            
            mysqli_stmt_close($stmt);
        }
    } elseif (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        
        // Check if the user has any survey responses or other important data
        $hasResponses = false;
        $sql = "SELECT COUNT(*) as count FROM survey_responses WHERE user_id = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($result)) {
                    $hasResponses = ($row['count'] > 0);
                }
            }
            
            mysqli_stmt_close($stmt);
        }
        
        // Check for teacher ratings if the user is a teacher
        $hasRatings = false;
        $sql = "SELECT u.role FROM users u WHERE u.id = ?";
        $isTeacher = false;
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($result)) {
                    $isTeacher = ($row['role'] === 'teacher');
                }
            }
            
            mysqli_stmt_close($stmt);
        }
        
        if ($isTeacher) {
            $sql = "SELECT COUNT(*) as count FROM teacher_ratings WHERE teacher_id = ?";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $user_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $result = mysqli_stmt_get_result($stmt);
                    if ($row = mysqli_fetch_assoc($result)) {
                        $hasRatings = ($row['count'] > 0);
                    }
                }
                
                mysqli_stmt_close($stmt);
            }
        }
        
        if ($hasResponses || $hasRatings) {
            // If the user has related data, just deactivate the account
            $sql = "UPDATE users SET is_active = 0 WHERE id = ?";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $user_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $alertType = "warning";
                    $alertMessage = "User has related data and cannot be deleted. The account has been deactivated instead.";
                } else {
                    $alertType = "danger";
                    $alertMessage = "Error: " . mysqli_error($conn);
                }
                
                mysqli_stmt_close($stmt);
            }
        } else {
            // If the user has no related data, delete the account
            $sql = "DELETE FROM users WHERE id = ?";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $user_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    // Also delete profile data based on role
                    if ($isTeacher) {
                        $sql = "DELETE FROM teacher_profiles WHERE user_id = ?";
                    } else {
                        $sql = "DELETE FROM student_profiles WHERE user_id = ?";
                    }
                    
                    if ($stmt2 = mysqli_prepare($conn, $sql)) {
                        mysqli_stmt_bind_param($stmt2, "i", $user_id);
                        mysqli_stmt_execute($stmt2);
                        mysqli_stmt_close($stmt2);
                    }
                    
                    $alertType = "success";
                    $alertMessage = "User deleted successfully.";
                } else {
                    $alertType = "danger";
                    $alertMessage = "Error: " . mysqli_error($conn);
                }
                
                mysqli_stmt_close($stmt);
            }
        }
    } elseif (isset($_POST['create_user'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $role = trim($_POST['role']);
        $name = trim($_POST['name']);
        $default_password = "password123"; // Default password for new users
        $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
        
        // Validate inputs
        $username_err = $email_err = $role_err = $name_err = "";
        
        // Validate username
        if (empty($username)) {
            $username_err = "Please enter a username.";
        } else {
            // Check if username exists
            $sql = "SELECT id FROM users WHERE username = ?";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "s", $username);
                
                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_store_result($stmt);
                    
                    if (mysqli_stmt_num_rows($stmt) > 0) {
                        $username_err = "This username is already taken.";
                    }
                }
                
                mysqli_stmt_close($stmt);
            }
        }
        
        // Validate email
        if (empty($email)) {
            $email_err = "Please enter an email.";
        } else {
            // Check if email exists
            $sql = "SELECT id FROM users WHERE email = ?";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "s", $email);
                
                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_store_result($stmt);
                    
                    if (mysqli_stmt_num_rows($stmt) > 0) {
                        $email_err = "This email is already registered.";
                    }
                }
                
                mysqli_stmt_close($stmt);
            }
        }
        
        // Validate role
        if (empty($role) || !in_array($role, ['student', 'teacher', 'admin'])) {
            $role_err = "Please select a valid role.";
        }
        
        // Validate name
        if (empty($name)) {
            $name_err = "Please enter a name.";
        }
        
        // Check input errors before creating user
        if (empty($username_err) && empty($email_err) && empty($role_err) && empty($name_err)) {
            // Begin transaction
            mysqli_begin_transaction($conn);
            
            try {
                // Insert user
                $sql = "INSERT INTO users (username, email, password, role, is_active) VALUES (?, ?, ?, ?, 1)";
                
                if ($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $hashed_password, $role);
                    
                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception(mysqli_error($conn));
                    }
                    
                    $user_id = mysqli_insert_id($conn);
                    mysqli_stmt_close($stmt);
                    
                    // Insert profile data based on role
                    if ($role === 'student') {
                        $sql = "INSERT INTO student_profiles (user_id, name) VALUES (?, ?)";
                    } else {
                        $sql = "INSERT INTO teacher_profiles (user_id, name) VALUES (?, ?)";
                    }
                    
                    if ($stmt = mysqli_prepare($conn, $sql)) {
                        mysqli_stmt_bind_param($stmt, "is", $user_id, $name);
                        
                        if (!mysqli_stmt_execute($stmt)) {
                            throw new Exception(mysqli_error($conn));
                        }
                        
                        mysqli_stmt_close($stmt);
                    }
                    
                    // Commit transaction
                    mysqli_commit($conn);
                    
                    $alertType = "success";
                    $alertMessage = "User created successfully with password '$default_password'. Please inform the user to change their password after login.";
                }
            } catch (Exception $e) {
                // Rollback transaction on error
                mysqli_rollback($conn);
                
                $alertType = "danger";
                $alertMessage = "Error: " . $e->getMessage();
            }
        } else {
            $alertType = "danger";
            $alertMessage = "Please check the form for errors.";
        }
    }
}

// Fetch all users
$sql = "SELECT u.*, u.username as full_name, u.username as display_name
    FROM users u
    ORDER BY u.role, u.username";
$result = mysqli_query($conn, $sql);
$users = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
}

// Count users by role
$student_count = $teacher_count = $admin_count = 0;
foreach ($users as $user) {
    if ($user['role'] === 'student') {
        $student_count++;
    } elseif ($user['role'] === 'teacher') {
        $teacher_count++;
    } elseif ($user['role'] === 'admin') {
        $admin_count++;
    }
}

// Set page variables
$pageTitle = "User Management";
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

.admin-gradient {
    background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);
}

.page-header-modern {
    background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);
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

.page-header-modern h2 {
    margin: 0;
    font-weight: 700;
    font-size: 1.75rem;
}

.page-header-modern p {
    margin: 0.5rem 0 0;
    opacity: 0.9;
}

.stat-card-modern {
    background: white;
    border-radius: var(--radius-xl);
    padding: 1.5rem;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--gray-100);
    transition: all 0.3s ease;
    height: 100%;
}

.stat-card-modern:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.stat-icon.blue { background: linear-gradient(135deg, #3b82f6, #60a5fa); }
.stat-icon.green { background: linear-gradient(135deg, #10b981, #34d399); }
.stat-icon.red { background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary)); }
.stat-icon.purple { background: linear-gradient(135deg, #8b5cf6, #a78bfa); }

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--gray-800);
    margin: 0;
    line-height: 1.2;
}

.stat-label {
    color: var(--gray-600);
    font-size: 0.875rem;
    margin: 0;
}

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

.card-body-modern {
    padding: 1.5rem;
}

.table-modern {
    margin: 0;
    width: 100%;
}

.table-modern thead th {
    background: var(--gray-50);
    border-bottom: 2px solid var(--gray-200);
    padding: 1rem;
    font-weight: 600;
    color: var(--gray-700);
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    white-space: nowrap;
}

.table-modern tbody td {
    padding: 1rem;
    vertical-align: middle;
    border-bottom: 1px solid var(--gray-100);
}

.table-modern tbody tr:hover {
    background: var(--gray-50);
}

.role-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.role-badge.admin {
    background: rgba(220, 38, 38, 0.1);
    color: #dc2626;
}

.role-badge.teacher {
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
}

.role-badge.student {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-badge.active {
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
}

.status-badge.inactive {
    background: rgba(239, 68, 68, 0.1);
    color: #dc2626;
}

.action-btn {
    width: 36px;
    height: 36px;
    border-radius: var(--radius-lg);
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.875rem;
}

.action-btn.edit {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.action-btn.edit:hover {
    background: #3b82f6;
    color: white;
}

.action-btn.reset {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}

.action-btn.reset:hover {
    background: #f59e0b;
    color: white;
}

.action-btn.delete {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.action-btn.delete:hover {
    background: #ef4444;
    color: white;
}

.btn-add-user {
    background: linear-gradient(135deg, #10b981, #34d399);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: var(--radius-lg);
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-add-user:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    color: white;
}

.modal-modern .modal-content {
    border-radius: var(--radius-xl);
    border: none;
    overflow: hidden;
}

.modal-modern .modal-header {
    background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary));
    color: white;
    border: none;
    padding: 1.25rem 1.5rem;
}

.modal-modern .modal-header .btn-close {
    filter: brightness(0) invert(1);
    opacity: 0.8;
}

.modal-modern .modal-title {
    font-weight: 600;
}

.modal-modern .modal-body {
    padding: 1.5rem;
}

.modal-modern .modal-footer {
    border-top: 1px solid var(--gray-200);
    padding: 1rem 1.5rem;
}

.modal-modern.green .modal-header {
    background: linear-gradient(135deg, #10b981, #34d399);
}

.modal-modern.orange .modal-header {
    background: linear-gradient(135deg, #f59e0b, #fbbf24);
}

.modal-modern.danger .modal-header {
    background: linear-gradient(135deg, #ef4444, #f87171);
}

.form-floating-modern {
    position: relative;
    margin-bottom: 1.25rem;
}

.form-floating-modern label {
    font-weight: 500;
    color: var(--gray-700);
    display: block;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.form-floating-modern input,
.form-floating-modern select {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid var(--gray-200);
    border-radius: var(--radius-lg);
    font-size: 1rem;
    transition: all 0.3s ease;
    background: var(--gray-50);
}

.form-floating-modern input:focus,
.form-floating-modern select:focus {
    outline: none;
    border-color: var(--admin-primary);
    background: white;
    box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.1);
}

.form-floating-modern small {
    color: var(--gray-600);
    font-size: 0.75rem;
    margin-top: 0.25rem;
    display: block;
}

.btn-modern {
    padding: 0.75rem 1.5rem;
    border-radius: var(--radius-lg);
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-modern.btn-primary-modern {
    background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary));
    color: white;
}

.btn-modern.btn-primary-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
}

.btn-modern.btn-success-modern {
    background: linear-gradient(135deg, #10b981, #34d399);
    color: white;
}

.btn-modern.btn-success-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

.btn-modern.btn-warning-modern {
    background: linear-gradient(135deg, #f59e0b, #fbbf24);
    color: white;
}

.btn-modern.btn-warning-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
}

.btn-modern.btn-danger-modern {
    background: linear-gradient(135deg, #ef4444, #f87171);
    color: white;
}

.btn-modern.btn-danger-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
    color: white;
    margin-right: 0.75rem;
}

.user-avatar.admin { background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary)); }
.user-avatar.teacher { background: linear-gradient(135deg, #10b981, #34d399); }
.user-avatar.student { background: linear-gradient(135deg, #3b82f6, #60a5fa); }

.dataTables_wrapper .dataTables_filter input {
    border: 2px solid var(--gray-200);
    border-radius: var(--radius-lg);
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
}

.dataTables_wrapper .dataTables_filter input:focus {
    outline: none;
    border-color: var(--admin-primary);
    box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.1);
}

.dataTables_wrapper .dataTables_length select {
    border: 2px solid var(--gray-200);
    border-radius: var(--radius-lg);
    padding: 0.375rem 2rem 0.375rem 0.75rem;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
    border-radius: var(--radius-lg) !important;
    margin: 0 2px;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary)) !important;
    color: white !important;
    border: none !important;
}
</style>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="page-header-modern">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-users-cog me-2"></i>User Management</h2>
                <p>Add, edit, and manage user accounts across the system</p>
            </div>
            <div>
                <a href="dashboard.php" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <?php if (!empty($alertMessage)): ?>
        <div class="alert alert-<?php echo $alertType; ?> alert-dismissible fade show" role="alert" style="border-radius: var(--radius-lg);">
            <i class="fas fa-<?php echo $alertType === 'success' ? 'check-circle' : ($alertType === 'warning' ? 'exclamation-triangle' : 'times-circle'); ?> me-2"></i>
            <?php echo $alertMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card-modern">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon blue">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div>
                        <p class="stat-value"><?php echo $student_count; ?></p>
                        <p class="stat-label">Students</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-modern">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon green">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div>
                        <p class="stat-value"><?php echo $teacher_count; ?></p>
                        <p class="stat-label">Teachers</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-modern">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon red">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div>
                        <p class="stat-value"><?php echo $admin_count; ?></p>
                        <p class="stat-label">Administrators</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-modern">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon purple">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <p class="stat-value"><?php echo count($users); ?></p>
                        <p class="stat-label">Total Users</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card-modern">
        <div class="card-header-modern">
            <h3><i class="fas fa-table me-2"></i>User Accounts</h3>
            <div class="d-flex gap-2 align-items-center">
                <a href="user_import.php" class="btn btn-success btn-sm" style="border-radius: 8px; font-weight: 600;">
                    <i class="fas fa-file-import me-1"></i>Import Users
                </a>
                <a href="user_export.php" class="btn btn-info btn-sm text-white" style="border-radius: 8px; font-weight: 600;">
                    <i class="fas fa-file-export me-1"></i>Export Data
                </a>
                <button type="button" class="btn-add-user" data-bs-toggle="modal" data-bs-target="#createUserModal">
                    <i class="fas fa-user-plus"></i>Add New User
                </button>
            </div>
        </div>
        <div class="card-body-modern">
            <div class="table-responsive">
                <table class="table table-modern" id="usersTable">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th width="140">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar <?php echo $user['role']; ?>">
                                            <?php echo strtoupper(substr($user['username'], 0, 2)); ?>
                                        </div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                            <div class="text-muted small">#<?php echo $user['id']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted"><?php echo htmlspecialchars($user['email']); ?></span>
                                </td>
                                <td>
                                    <span class="role-badge <?php echo $user['role']; ?>">
                                        <i class="fas fa-<?php echo $user['role'] === 'admin' ? 'shield-alt' : ($user['role'] === 'teacher' ? 'chalkboard-teacher' : 'user-graduate'); ?>"></i>
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['is_active']): ?>
                                        <span class="status-badge active"><i class="fas fa-check-circle"></i>Active</span>
                                    <?php else: ?>
                                        <span class="status-badge inactive"><i class="fas fa-times-circle"></i>Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-muted"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="action-btn reset" data-bs-toggle="modal" data-bs-target="#resetPasswordModal" 
                                            data-id="<?php echo $user['id']; ?>" 
                                            data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                            title="Reset Password">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        <button type="button" class="action-btn edit" data-bs-toggle="modal" data-bs-target="#editUserModal" 
                                            data-id="<?php echo $user['id']; ?>" 
                                            data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                            data-role="<?php echo $user['role']; ?>"
                                            data-active="<?php echo $user['is_active']; ?>"
                                            title="Edit User">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="action-btn delete" data-bs-toggle="modal" data-bs-target="#deleteUserModal" 
                                            data-id="<?php echo $user['id']; ?>" 
                                            data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                            title="Delete User">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade modal-modern green" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createUserModalLabel"><i class="fas fa-user-plus me-2"></i>Create New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="modal-body">
                    <input type="hidden" name="create_user" value="1">
                    
                    <div class="form-floating-modern">
                        <label for="username"><i class="fas fa-user me-2"></i>Username</label>
                        <input type="text" id="username" name="username" placeholder="Enter username" required>
                        <small>Username must be unique and will be used for login</small>
                    </div>
                    
                    <div class="form-floating-modern">
                        <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter email" required>
                    </div>
                    
                    <div class="form-floating-modern">
                        <label for="name"><i class="fas fa-id-card me-2"></i>Full Name</label>
                        <input type="text" id="name" name="name" placeholder="Enter full name" required>
                    </div>
                    
                    <div class="form-floating-modern">
                        <label for="role"><i class="fas fa-user-tag me-2"></i>Role</label>
                        <select id="role" name="role" required>
                            <option value="">-- Select Role --</option>
                            <option value="student">Student</option>
                            <option value="teacher">Teacher</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-info" style="border-radius: var(--radius-lg);">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> User will be created with default password: <code>password123</code>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-modern btn-success-modern">
                        <i class="fas fa-user-plus me-2"></i>Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade modal-modern" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel"><i class="fas fa-edit me-2"></i>Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    
                    <div class="text-center mb-4">
                        <div class="user-avatar admin mx-auto mb-2" style="width: 60px; height: 60px; font-size: 1.25rem;" id="edit_avatar">
                            AA
                        </div>
                        <h5 id="edit_username" class="mb-0"></h5>
                    </div>
                    
                    <div class="form-floating-modern">
                        <label for="edit_role"><i class="fas fa-user-tag me-2"></i>User Role</label>
                        <select id="edit_role" name="role">
                            <option value="student">Student</option>
                            <option value="teacher">Teacher</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>
                            <button type="submit" name="update_user_role" class="btn-modern btn-primary-modern btn-sm">
                                <i class="fas fa-save me-1"></i>Update Role
                            </button>
                        </span>
                    </div>
                    
                    <hr>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" style="width: 3em; height: 1.5em;">
                        <label class="form-check-label ms-2" for="edit_is_active">
                            <strong>Active Account</strong>
                        </label>
                    </div>
                    
                    <button type="submit" name="update_user_status" class="btn-modern btn-primary-modern btn-sm">
                        <i class="fas fa-save me-1"></i>Update Status
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade modal-modern orange" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetPasswordModalLabel"><i class="fas fa-key me-2"></i>Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-4">
                    <i class="fas fa-key fa-3x text-warning mb-3"></i>
                </div>
                <p>Reset password for <strong id="reset_username" class="text-primary"></strong>?</p>
                <div class="alert alert-warning" style="border-radius: var(--radius-lg);">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Password will be reset to: <code>password123</code>
                </div>
                <p class="text-muted small">User should change their password after first login</p>
            </div>
            <div class="modal-footer">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="reset_password" value="1">
                    <input type="hidden" name="user_id" id="reset_user_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-modern btn-warning-modern">
                        <i class="fas fa-key me-2"></i>Reset Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade modal-modern danger" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteUserModalLabel"><i class="fas fa-exclamation-triangle me-2"></i>Delete User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-4">
                    <i class="fas fa-user-times fa-3x text-danger mb-3"></i>
                </div>
                <p>Are you sure you want to delete <strong id="delete_username" class="text-danger"></strong>?</p>
                <div class="alert alert-warning" style="border-radius: var(--radius-lg);">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> If the user has survey responses or ratings, their account will be deactivated instead to preserve data integrity.
                </div>
            </div>
            <div class="modal-footer">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="delete_user" value="1">
                    <input type="hidden" name="user_id" id="delete_user_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-modern btn-danger-modern">
                        <i class="fas fa-trash me-2"></i>Delete User
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../core/includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    $('#usersTable').DataTable({
        order: [[0, 'asc']],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]]
    });
    
    // Edit User Modal
    $('#editUserModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const id = button.data('id');
        const username = button.data('username');
        const role = button.data('role');
        const active = button.data('active');
        
        const modal = $(this);
        modal.find('#edit_user_id').val(id);
        modal.find('#edit_username').text(username);
        modal.find('#edit_role').val(role);
        modal.find('#edit_is_active').prop('checked', active === 1);
    });
    
    // Reset Password Modal
    $('#resetPasswordModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const id = button.data('id');
        const username = button.data('username');
        
        const modal = $(this);
        modal.find('#reset_user_id').val(id);
        modal.find('#reset_username').text(username);
    });
    
    // Delete User Modal
    $('#deleteUserModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const id = button.data('id');
        const username = button.data('username');
        
        const modal = $(this);
        modal.find('#delete_user_id').val(id);
        modal.find('#delete_username').text(username);
    });
});
</script>

<?php
// Close connection
closeConnection($conn);
?>

