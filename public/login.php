<?php
// Start output buffering at the very beginning
ob_start();

// Start session
session_start();

// Unset any existing session variables if arriving from logout
if (isset($_SESSION['logout_success'])) {
    // Keep only the logout success message
    $logout_message = $_SESSION['logout_success'];
    // Start with a clean session
    session_unset();
    // Set back just the logout message
    $_SESSION['logout_success'] = $logout_message;
}

// Include config file
require_once "../core/includes/config.php";
require_once "../core/includes/functions.php";

// Initialize variables
$email = $password = $role = "student"; // Default role to student
$email_err = $password_err = $role_err = $login_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Enable output buffering
    ob_start();
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate role
    if (empty(trim($_POST["role"]))) {
        $role_err = "Please select a role.";
    } else {
        $role = trim($_POST["role"]);
    }
    
    // For debugging - log the submitted values
    error_log("Login attempt - Email: " . $email . ", Role: " . $role);
    
    // Check input errors before attempting to login
    if (empty($email_err) && empty($password_err) && empty($role_err)) {
        if (loginUser($email, $password, $role, $conn)) {
            // Log success
            error_log("Login successful, redirecting to dashboard");
            
            // Make sure session is active and has all required data
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            
            // Double-check session data is set correctly
            if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
                error_log("Session loggedin status not set properly after login");
                $_SESSION["loggedin"] = true;
            }
            
            // Set a success flag in the session
            $_SESSION['login_success'] = true;
            
            // Log session data before redirect
            error_log("Session data before redirect: " . print_r($_SESSION, true));
            
            // Handle redirection directly here instead of using redirectToDashboard
            $targetLocation = "";
            
            switch ($_SESSION["role"]) {
                case "student":
                    $targetLocation = "../app/student/dashboard.php";
                    break;
                case "teacher":
                    $targetLocation = "../app/teacher/dashboard.php";
                    break;
                case "admin":
                    $targetLocation = "../app/admin/dashboard.php";
                    break;
                default:
                    $targetLocation = "index.php";
            }
            
            error_log("Direct redirect to: " . $targetLocation);
            
            // Clean output buffer and redirect
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            // Perform direct redirect
            header("Location: " . $targetLocation);
            exit; // Ensure script stops here
        } else {
            // Login failed
            $login_err = "Invalid email, password, or role combination.";
        }
    }
}

// Set page variables
$pageTitle = "Login - Student Satisfaction Survey";
$basePath = "../";
?>

<?php include '../core/includes/header.php'; ?>

<div class="auth-container">
    <h2>Login</h2>
    
    <?php if (!empty($login_err)): ?>
        <div class="alert alert-danger"><?php echo $login_err; ?></div>
    <?php endif; ?>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
            <div class="invalid-feedback"><?php echo $email_err; ?></div>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-group">
                <input type="password" name="password" id="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                <div class="input-group-append">
                    <button type="button" id="toggle-password" class="btn btn-secondary" onclick="togglePasswordVisibility('password', 'toggle-password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="invalid-feedback"><?php echo $password_err; ?></div>
            </div>
        </div>
        
        <div class="form-group">
            <label>Select Your Role</label>
            <div class="role-selector">
                <div class="role-option <?php echo ($role === 'student') ? 'active' : ''; ?>" data-role="student" onclick="selectRole('student')">
                    <i class="fas fa-user-graduate"></i>
                    <div>Student</div>
                </div>
                <div class="role-option <?php echo ($role === 'teacher') ? 'active' : ''; ?>" data-role="teacher" onclick="selectRole('teacher')">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <div>Teacher</div>
                </div>
                <div class="role-option <?php echo ($role === 'admin') ? 'active' : ''; ?>" data-role="admin" onclick="selectRole('admin')">
                    <i class="fas fa-user-shield"></i>
                    <div>Admin/HOD</div>
                </div>
            </div>
            <input type="hidden" name="role" id="role" value="<?php echo $role; ?>">
            <div class="invalid-feedback"><?php echo $role_err; ?></div>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </div>
        
        <p class="text-center">Don't have an account? <a href="register.php">Register here</a></p>
    </form>
</div>

<?php include '../core/includes/footer.php'; ?>

<script>
// Direct role selection function
function selectRole(role) {
    // Update the hidden input value
    document.getElementById('role').value = role;
    
    // Update visual state
    const roleOptions = document.querySelectorAll('.role-option');
    roleOptions.forEach(option => {
        if (option.getAttribute('data-role') === role) {
            option.classList.add('active');
        } else {
            option.classList.remove('active');
        }
    });
    
    console.log("Role selected:", role);
}

// Initialize default role
document.addEventListener('DOMContentLoaded', function() {
    // Set default role if not already set
    if (!document.getElementById('role').value) {
        document.getElementById('role').value = 'student';
    }
});
</script>

<?php
// Close connection
closeConnection($conn);
?>
