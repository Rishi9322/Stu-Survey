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

<div class="profile-section">
    <div class="profile-header">
        <div class="profile-avatar">
            <?php echo strtoupper(substr($name, 0, 1)); ?>
        </div>
        <div class="profile-info">
            <h2><?php echo htmlspecialchars($name); ?></h2>
            <p><?php echo htmlspecialchars($email); ?></p>
            <p>Administrator</p>
        </div>
    </div>
    
    <div class="profile-details">
        <ul class="nav nav-tabs" id="profileTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="true">Edit Profile</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="password-tab" data-toggle="tab" href="#password" role="tab" aria-controls="password" aria-selected="false">Change Password</a>
            </li>
        </ul>
        
        <div class="tab-content" id="profileTabsContent">
            <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                <h3 class="mt-4">Edit Profile</h3>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation">
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" name="name" id="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($name); ?>">
                        <div class="invalid-feedback"><?php echo $name_err; ?></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" readonly>
                        <small class="form-text text-muted">Email cannot be changed.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="dob">Date of Birth</label>
                        <input type="date" name="dob" id="dob" class="form-control <?php echo (!empty($dob_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($dob); ?>">
                        <div class="invalid-feedback"><?php echo $dob_err; ?></div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>
            
            <div class="tab-pane fade" id="password" role="tabpanel" aria-labelledby="password-tab">
                <h3 class="mt-4">Change Password</h3>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation">
                    <input type="hidden" name="change_password" value="1">
                    
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <div class="input-group">
                            <input type="password" name="current_password" id="current_password" class="form-control <?php echo (!empty($current_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $current_password; ?>">
                            <div class="input-group-append">
                                <button type="button" id="toggle-current-password" class="btn btn-secondary" onclick="togglePasswordVisibility('current_password', 'toggle-current-password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback"><?php echo $current_password_err; ?></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <div class="input-group">
                            <input type="password" name="new_password" id="new_password" class="form-control <?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $new_password; ?>">
                            <div class="input-group-append">
                                <button type="button" id="toggle-new-password" class="btn btn-secondary" onclick="togglePasswordVisibility('new_password', 'toggle-new-password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback"><?php echo $new_password_err; ?></div>
                        </div>
                        <small class="form-text text-muted">Password must be at least 6 characters long.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <div class="input-group">
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                            <div class="input-group-append">
                                <button type="button" id="toggle-confirm-password" class="btn btn-secondary" onclick="togglePasswordVisibility('confirm_password', 'toggle-confirm-password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback"><?php echo $confirm_password_err; ?></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../core/includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle tab switching
    const tabLinks = document.querySelectorAll('.nav-link');
    const tabContents = document.querySelectorAll('.tab-pane');
    
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all tabs and contents
            tabLinks.forEach(tab => tab.classList.remove('active'));
            tabContents.forEach(content => {
                content.classList.remove('show', 'active');
            });
            
            // Add active class to current tab and content
            this.classList.add('active');
            const target = this.getAttribute('href').substring(1);
            document.getElementById(target).classList.add('show', 'active');
        });
    });
});
</script>

<?php
// Close connection
closeConnection($conn);
?>

