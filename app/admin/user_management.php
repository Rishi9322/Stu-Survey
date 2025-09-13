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

<div class="user-management">
    <h2>User Management</h2>
    <p>Add, edit, and manage user accounts.</p>
    
    <?php if (!empty($alertMessage)): ?>
        <div class="alert alert-<?php echo $alertType; ?> alert-dismissible fade show" role="alert">
            <?php echo $alertMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Students</h5>
                    <h2 class="display-4"><?php echo $student_count; ?></h2>
                    <p class="card-text">Total registered students</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Teachers</h5>
                    <h2 class="display-4"><?php echo $teacher_count; ?></h2>
                    <p class="card-text">Total registered teachers</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Administrators</h5>
                    <h2 class="display-4"><?php echo $admin_count; ?></h2>
                    <p class="card-text">Total system administrators</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3>User Accounts</h3>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="fas fa-user-plus"></i> Add New User
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="usersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['display_name']); ?></td>
                                <td>
                                    <?php if ($user['role'] === 'admin'): ?>
                                        <span class="badge badge-info">Admin</span>
                                    <?php elseif ($user['role'] === 'teacher'): ?>
                                        <span class="badge badge-success">Teacher</span>
                                    <?php else: ?>
                                        <span class="badge badge-primary">Student</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#resetPasswordModal" 
                                            data-id="<?php echo $user['id']; ?>" 
                                            data-username="<?php echo htmlspecialchars($user['username']); ?>">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editUserModal" 
                                            data-id="<?php echo $user['id']; ?>" 
                                            data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                            data-role="<?php echo $user['role']; ?>"
                                            data-active="<?php echo $user['is_active']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal" 
                                            data-id="<?php echo $user['id']; ?>" 
                                            data-username="<?php echo htmlspecialchars($user['username']); ?>">
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
<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createUserModalLabel">Create New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="modal-body">
                    <input type="hidden" name="create_user" value="1">
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                        <small class="form-text text-muted">Username must be unique and will be used for login.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="">-- Select Role --</option>
                            <option value="student">Student</option>
                            <option value="teacher">Teacher</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> The user will be created with the default password: <strong>password123</strong>. 
                        They should change it after first login.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    
                    <div class="form-group">
                        <label>Username</label>
                        <p id="edit_username" class="form-control-static font-weight-bold"></p>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_role">Role</label>
                        <select class="form-control" id="edit_role" name="role">
                            <option value="student">Student</option>
                            <option value="teacher">Teacher</option>
                            <option value="admin">Administrator</option>
                        </select>
                        <button type="submit" name="update_user_role" class="btn btn-primary btn-sm mt-2">Update Role</button>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active">
                            <label class="form-check-label" for="edit_is_active">
                                Active Account
                            </label>
                        </div>
                        <button type="submit" name="update_user_status" class="btn btn-primary btn-sm mt-2">Update Status</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetPasswordModalLabel">Reset User Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to reset the password for <strong id="reset_username"></strong>?</p>
                <p>The password will be reset to: <strong>password123</strong></p>
                <p>Please inform the user to change their password after login.</p>
            </div>
            <div class="modal-footer">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="reset_password" value="1">
                    <input type="hidden" name="user_id" id="reset_user_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Reset Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteUserModalLabel">Delete User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the user <strong id="delete_username"></strong>?</p>
                <p><strong>Note:</strong> If the user has survey responses or ratings, their account will be deactivated instead of deleted to preserve data integrity.</p>
            </div>
            <div class="modal-footer">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="delete_user" value="1">
                    <input type="hidden" name="user_id" id="delete_user_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete User</button>
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

