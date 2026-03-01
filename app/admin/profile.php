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

// Get admin profile data
$adminProfile = getUserProfileData($_SESSION["id"], "admin", $conn);

// Initialize variables
$name = $email = $dob = "";
$name_err = $email_err = $dob_err = "";
$current_password = $new_password = $confirm_password = "";
$current_password_err = $new_password_err = $confirm_password_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_profile'])) {
        // Validate name
        if (empty(trim($_POST["name"]))) {
            $name_err = "Please enter your name.";
        } else {
            $name = trim($_POST["name"]);
        }
        
        // Email cannot be changed, so we just set it to the current value
        $email = $_SESSION["email"];
        
        // Validate DOB
        if (empty(trim($_POST["dob"]))) {
            $dob_err = "Please enter your date of birth.";
        } else {
            $dob = trim($_POST["dob"]);
        }
        
        // Check input errors before updating the database
        if (empty($name_err) && empty($dob_err)) {
            // Update user information
            $sql = "UPDATE users SET username = ?, dob = ? WHERE id = ?";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "ssi", $name, $dob, $_SESSION["id"]);
                
                if (mysqli_stmt_execute($stmt)) {
                    // Update session variable
                    $_SESSION["name"] = $name;
                    
                    // Set success message
                    $alertType = "success";
                    $alertMessage = "Your profile has been updated successfully.";
                    
                    // Refresh profile data
                    $adminProfile = getUserProfileData($_SESSION["id"], "admin", $conn);
                } else {
                    $alertType = "danger";
                    $alertMessage = "Error updating profile: " . mysqli_error($conn);
                }
                
                mysqli_stmt_close($stmt);
            }
        }
    } elseif (isset($_POST['change_password'])) {
        // Validate current password
        if (empty(trim($_POST["current_password"]))) {
            $current_password_err = "Please enter your current password.";
        } else {
            $current_password = trim($_POST["current_password"]);
            
            // Check if the current password is correct
            $sql = "SELECT password FROM users WHERE id = ?";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
                
                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_store_result($stmt);
                    
                    if (mysqli_stmt_num_rows($stmt) == 1) {
                        mysqli_stmt_bind_result($stmt, $hashed_password);
                        if (mysqli_stmt_fetch($stmt)) {
                            if (!password_verify($current_password, $hashed_password)) {
                                $current_password_err = "The current password is incorrect.";
                            }
                        }
                    } else {
                        $alertType = "danger";
                        $alertMessage = "Something went wrong. Please try again later.";
                    }
                } else {
                    $alertType = "danger";
                    $alertMessage = "Error: " . mysqli_error($conn);
                }
                
                mysqli_stmt_close($stmt);
            }
        }
        
        // Validate new password
        if (empty(trim($_POST["new_password"]))) {
            $new_password_err = "Please enter a new password.";     
        } elseif (strlen(trim($_POST["new_password"])) < 6) {
            $new_password_err = "Password must have at least 6 characters.";
        } else {
            $new_password = trim($_POST["new_password"]);
        }
        
        // Validate confirm password
        if (empty(trim($_POST["confirm_password"]))) {
            $confirm_password_err = "Please confirm the password.";     
        } else {
            $confirm_password = trim($_POST["confirm_password"]);
            if (empty($new_password_err) && ($new_password != $confirm_password)) {
                $confirm_password_err = "Password did not match.";
            }
        }
        
        // Check input errors before updating the database
        if (empty($current_password_err) && empty($new_password_err) && empty($confirm_password_err)) {
            // Update password
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                mysqli_stmt_bind_param($stmt, "si", $hashed_password, $_SESSION["id"]);
                
                if (mysqli_stmt_execute($stmt)) {
                    // Set success message
                    $alertType = "success";
                    $alertMessage = "Your password has been changed successfully.";
                    
                    // Clear the password fields
                    $current_password = $new_password = $confirm_password = "";
                } else {
                    $alertType = "danger";
                    $alertMessage = "Error changing password: " . mysqli_error($conn);
                }
                
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Fill form with current values if not from POST
if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_POST['update_profile'])) {
    $name = $adminProfile['name'] ?? $_SESSION["name"];
    $email = $adminProfile['email'] ?? $_SESSION["email"];
    $dob = $adminProfile['dob'] ?? '';
}

// Set page variables
$pageTitle = "Admin Profile";
$basePath = "../../";
?>

<?php include '../../core/includes/header.php'; ?>

<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header-modern mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap">
            <div class="d-flex align-items-center">
                <div class="header-icon">
                    <i class="fas fa-user-cog"></i>
                </div>
                <div>
                    <h1>Admin Profile</h1>
                    <p class="mb-0">Manage your account settings and preferences</p>
                </div>
            </div>
            <a href="dashboard.php" class="btn btn-light mt-3 mt-md-0">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <?php if (isset($alertType) && isset($alertMessage)): ?>
    <div class="alert alert-<?php echo $alertType; ?> alert-dismissible fade show" role="alert">
        <i class="fas fa-<?php echo $alertType === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
        <?php echo $alertMessage; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Profile Card -->
        <div class="col-lg-4">
            <div class="profile-card-modern">
                <div class="profile-card-header admin-gradient">
                    <div class="profile-avatar-large">
                        <?php echo strtoupper(substr($name, 0, 1)); ?>
                    </div>
                    <h3 class="profile-name"><?php echo htmlspecialchars($name); ?></h3>
                    <p class="profile-email"><?php echo htmlspecialchars($email); ?></p>
                    <span class="profile-badge admin">
                        <i class="fas fa-shield-alt me-1"></i>Administrator
                    </span>
                </div>
                <div class="profile-card-body">
                    <div class="profile-info-item">
                        <div class="info-icon"><i class="fas fa-envelope"></i></div>
                        <div class="info-content">
                            <span class="info-label">Email Address</span>
                            <span class="info-value"><?php echo htmlspecialchars($email); ?></span>
                        </div>
                    </div>
                    <div class="profile-info-item">
                        <div class="info-icon"><i class="fas fa-birthday-cake"></i></div>
                        <div class="info-content">
                            <span class="info-label">Date of Birth</span>
                            <span class="info-value"><?php echo $dob ? date('F j, Y', strtotime($dob)) : 'Not set'; ?></span>
                        </div>
                    </div>
                    <div class="profile-info-item">
                        <div class="info-icon"><i class="fas fa-clock"></i></div>
                        <div class="info-content">
                            <span class="info-label">Account Status</span>
                            <span class="info-value"><span class="badge bg-success">Active</span></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="card-modern mt-4">
                <div class="card-header-modern">
                    <h5><i class="fas fa-link me-2"></i>Quick Links</h5>
                </div>
                <div class="card-body-modern">
                    <div class="quick-links-list">
                        <a href="user_management.php" class="quick-link-item">
                            <i class="fas fa-users-cog"></i>
                            <span>User Management</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="complaints.php" class="quick-link-item">
                            <i class="fas fa-comment-alt"></i>
                            <span>View Complaints</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="access_codes.php" class="quick-link-item">
                            <i class="fas fa-key"></i>
                            <span>Access Codes</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Tabs -->
        <div class="col-lg-8">
            <div class="card-modern">
                <div class="card-header-modern">
                    <ul class="nav nav-tabs-modern" id="profileTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link-modern active" id="profile-tab" data-tab="profile" href="#profile">
                                <i class="fas fa-user me-2"></i>Edit Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link-modern" id="password-tab" data-tab="password" href="#password">
                                <i class="fas fa-lock me-2"></i>Change Password
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body-modern">
                    <div class="tab-content" id="profileTabsContent">
                        <!-- Profile Tab -->
                        <div class="tab-pane-modern active" id="profile">
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                <input type="hidden" name="update_profile" value="1">
                                
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="form-floating-modern">
                                            <input type="text" name="name" id="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" 
                                                value="<?php echo htmlspecialchars($name); ?>" placeholder="Full Name">
                                            <label for="name"><i class="fas fa-user me-2"></i>Full Name</label>
                                            <?php if (!empty($name_err)): ?>
                                                <div class="invalid-feedback"><?php echo $name_err; ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating-modern">
                                            <input type="email" name="email" id="email" class="form-control" 
                                                value="<?php echo htmlspecialchars($email); ?>" placeholder="Email" readonly>
                                            <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
                                            <small class="form-hint">Email cannot be changed</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating-modern">
                                            <input type="date" name="dob" id="dob" class="form-control <?php echo (!empty($dob_err)) ? 'is-invalid' : ''; ?>" 
                                                value="<?php echo htmlspecialchars($dob); ?>" placeholder="Date of Birth">
                                            <label for="dob"><i class="fas fa-calendar me-2"></i>Date of Birth</label>
                                            <?php if (!empty($dob_err)): ?>
                                                <div class="invalid-feedback"><?php echo $dob_err; ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-actions mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save me-2"></i>Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Password Tab -->
                        <div class="tab-pane-modern" id="password">
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                <input type="hidden" name="change_password" value="1">
                                
                                <div class="password-requirements mb-4">
                                    <h6><i class="fas fa-info-circle me-2"></i>Password Requirements</h6>
                                    <ul>
                                        <li>At least 6 characters long</li>
                                        <li>Mix of letters and numbers recommended</li>
                                    </ul>
                                </div>

                                <div class="row g-4">
                                    <div class="col-12">
                                        <div class="form-floating-modern">
                                            <div class="password-input-group">
                                                <input type="password" name="current_password" id="current_password" 
                                                    class="form-control <?php echo (!empty($current_password_err)) ? 'is-invalid' : ''; ?>" 
                                                    placeholder="Current Password">
                                                <button type="button" class="password-toggle" onclick="togglePassword('current_password', this)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            <label for="current_password"><i class="fas fa-lock me-2"></i>Current Password</label>
                                            <?php if (!empty($current_password_err)): ?>
                                                <div class="invalid-feedback d-block"><?php echo $current_password_err; ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating-modern">
                                            <div class="password-input-group">
                                                <input type="password" name="new_password" id="new_password" 
                                                    class="form-control <?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>" 
                                                    placeholder="New Password">
                                                <button type="button" class="password-toggle" onclick="togglePassword('new_password', this)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            <label for="new_password"><i class="fas fa-key me-2"></i>New Password</label>
                                            <?php if (!empty($new_password_err)): ?>
                                                <div class="invalid-feedback d-block"><?php echo $new_password_err; ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating-modern">
                                            <div class="password-input-group">
                                                <input type="password" name="confirm_password" id="confirm_password" 
                                                    class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" 
                                                    placeholder="Confirm Password">
                                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', this)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            <label for="confirm_password"><i class="fas fa-check-circle me-2"></i>Confirm New Password</label>
                                            <?php if (!empty($confirm_password_err)): ?>
                                                <div class="invalid-feedback d-block"><?php echo $confirm_password_err; ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-actions mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-key me-2"></i>Change Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Page Header */
.page-header-modern {
    background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
    border-radius: var(--radius-xl);
    padding: 2rem;
    color: white;
}

.page-header-modern h1 { color: white; font-size: 1.75rem; font-weight: 700; margin: 0 0 0.25rem 0; }
.page-header-modern p { color: rgba(255, 255, 255, 0.85); }

.header-icon {
    width: 60px; height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: var(--radius-lg);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; margin-right: 1.25rem;
}

/* Profile Card */
.profile-card-modern {
    background: white;
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-lg);
    overflow: hidden;
}

.profile-card-header {
    padding: 2rem;
    text-align: center;
    color: white;
}

.profile-card-header.admin-gradient {
    background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
}

.profile-avatar-large {
    width: 100px; height: 100px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 2.5rem; font-weight: 700;
    margin: 0 auto 1rem;
    border: 4px solid rgba(255, 255, 255, 0.3);
}

.profile-name { margin: 0 0 0.25rem 0; font-size: 1.5rem; font-weight: 700; }
.profile-email { margin: 0 0 1rem 0; opacity: 0.85; }

.profile-badge {
    display: inline-flex; align-items: center;
    padding: 0.5rem 1rem; border-radius: 50px;
    font-size: 0.875rem; font-weight: 600;
}

.profile-badge.admin { background: rgba(255, 255, 255, 0.2); }

.profile-card-body { padding: 1.5rem; }

.profile-info-item {
    display: flex; align-items: center; gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid var(--gray-100);
}

.profile-info-item:last-child { border-bottom: none; }

.info-icon {
    width: 40px; height: 40px;
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    border-radius: var(--radius-md);
    display: flex; align-items: center; justify-content: center;
    color: #dc2626;
}

.info-content { flex: 1; }
.info-label { display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
.info-value { display: block; font-weight: 600; color: var(--text-primary); margin-top: 0.25rem; }

/* Card Modern */
.card-modern { background: white; border-radius: var(--radius-xl); box-shadow: var(--shadow-md); overflow: hidden; }

.card-header-modern {
    padding: 0;
    background: var(--gray-50);
    border-bottom: 1px solid var(--gray-100);
}

.card-header-modern h5 {
    margin: 0; padding: 1.25rem 1.5rem;
    font-size: 1rem; font-weight: 600;
}

.card-body-modern { padding: 1.5rem; }

/* Nav Tabs Modern */
.nav-tabs-modern { display: flex; list-style: none; margin: 0; padding: 0; }

.nav-tabs-modern .nav-item { flex: 1; }

.nav-link-modern {
    display: flex; align-items: center; justify-content: center;
    padding: 1rem 1.5rem;
    color: var(--text-secondary);
    text-decoration: none;
    font-weight: 500;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
    cursor: pointer;
}

.nav-link-modern:hover { color: var(--primary-color); background: var(--gray-100); }
.nav-link-modern.active { color: #dc2626; border-bottom-color: #dc2626; background: white; }

/* Tab Panes */
.tab-pane-modern { display: none; }
.tab-pane-modern.active { display: block; }

/* Form Floating Modern */
.form-floating-modern { position: relative; margin-bottom: 0.5rem; }
.form-floating-modern label {
    position: absolute; top: 0; left: 0;
    padding: 1rem 1.25rem;
    color: var(--text-muted);
    pointer-events: none;
    transition: all 0.2s ease;
    font-size: 0.875rem;
}

.form-floating-modern .form-control {
    padding: 1.5rem 1.25rem 0.75rem;
    height: auto;
    border: 2px solid var(--gray-200);
    border-radius: var(--radius-lg);
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.form-floating-modern .form-control:focus { border-color: #dc2626; box-shadow: 0 0 0 0.2rem rgba(220, 38, 38, 0.15); }

.form-floating-modern .form-control:focus + label,
.form-floating-modern .form-control:not(:placeholder-shown) + label {
    transform: translateY(-0.5rem);
    font-size: 0.75rem;
    color: #dc2626;
}

.form-hint { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem; }

/* Password Input Group */
.password-input-group { position: relative; }
.password-input-group .form-control { padding-right: 3rem; }

.password-toggle {
    position: absolute; right: 1rem; top: 50%; transform: translateY(-50%);
    background: none; border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 0.25rem;
}

.password-toggle:hover { color: var(--text-primary); }

/* Password Requirements */
.password-requirements {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border: 1px solid #fbbf24;
    border-radius: var(--radius-lg);
    padding: 1rem 1.25rem;
}

.password-requirements h6 { margin: 0 0 0.5rem 0; color: #92400e; }
.password-requirements ul { margin: 0; padding-left: 1.25rem; color: #a16207; font-size: 0.875rem; }
.password-requirements li { margin-bottom: 0.25rem; }

/* Quick Links */
.quick-links-list { display: flex; flex-direction: column; gap: 0.5rem; }

.quick-link-item {
    display: flex; align-items: center; gap: 1rem;
    padding: 1rem;
    background: var(--gray-50);
    border-radius: var(--radius-lg);
    text-decoration: none;
    color: var(--text-primary);
    transition: all 0.3s ease;
}

.quick-link-item:hover { background: var(--gray-100); transform: translateX(5px); }
.quick-link-item i:first-child { color: #dc2626; width: 20px; text-align: center; }
.quick-link-item span { flex: 1; font-weight: 500; }
.quick-link-item i:last-child { color: var(--text-muted); }

/* Form Actions */
.form-actions { padding-top: 1rem; border-top: 1px solid var(--gray-100); }

/* Responsive */
@media (max-width: 768px) {
    .page-header-modern { padding: 1.5rem; }
    .page-header-modern h1 { font-size: 1.25rem; }
    .header-icon { width: 50px; height: 50px; font-size: 1.25rem; }
    .nav-link-modern { padding: 0.75rem; font-size: 0.875rem; }
    .nav-link-modern i { margin-right: 0.5rem !important; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabLinks = document.querySelectorAll('.nav-link-modern');
    const tabPanes = document.querySelectorAll('.tab-pane-modern');
    
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = this.getAttribute('data-tab');
            
            tabLinks.forEach(l => l.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));
            
            this.classList.add('active');
            document.getElementById(target).classList.add('active');
        });
    });
});

function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

<?php include '../../core/includes/footer.php'; ?>

<?php
// Close connection
closeConnection($conn);
?>

