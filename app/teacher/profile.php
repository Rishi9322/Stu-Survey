<?php
// Include config file
require_once "../../core/includes/config.php";
require_once "../../core/includes/functions.php";

// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isLoggedIn() || !hasRole("teacher")) {
    header("location: ../../public/login.php");
    exit;
}

// Get teacher profile data
$teacherProfile = getUserProfileData($_SESSION["id"], "teacher", $conn);

// Get department list for dropdown
$departmentList = getDepartmentList($conn);

// Initialize variables
$name = $email = $dob = $department = $subjects = $experience = "";
$name_err = $email_err = $dob_err = $department_err = $subjects_err = $experience_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
    
    // Validate department
    if (empty(trim($_POST["department"]))) {
        $department_err = "Please enter your department.";
    } else {
        $department = trim($_POST["department"]);
    }
    
    // Validate subjects
    if (empty(trim($_POST["subjects"]))) {
        $subjects_err = "Please enter subjects taught.";
    } else {
        $subjects = trim($_POST["subjects"]);
    }
    
    // Experience is optional
    $experience = !empty($_POST["experience"]) ? trim($_POST["experience"]) : "0";
    
    // Check input errors before updating the database
    if (empty($name_err) && empty($dob_err) && empty($department_err) && empty($subjects_err)) {
        // Begin transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Update user information
            $sql = "UPDATE users SET username = ?, dob = ? WHERE id = ?";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "ssi", $name, $dob, $_SESSION["id"]);
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception(mysqli_error($conn));
                }
                
                mysqli_stmt_close($stmt);
            } else {
                throw new Exception(mysqli_error($conn));
            }
            
            // Update teacher profile
            $sql = "UPDATE teacher_profiles SET department = ?, subjects = ?, experience = ? WHERE user_id = ?";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "ssii", $department, $subjects, $experience, $_SESSION["id"]);
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception(mysqli_error($conn));
                }
                
                mysqli_stmt_close($stmt);
            } else {
                throw new Exception(mysqli_error($conn));
            }
            
            // Commit transaction
            mysqli_commit($conn);
            
            // Update session variable
            $_SESSION["name"] = $name;
            
            // Redirect to the profile page with success message
            $alertType = "success";
            $alertMessage = "Your profile has been updated successfully.";
            
            // Refresh profile data
            $teacherProfile = getUserProfileData($_SESSION["id"], "teacher", $conn);
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            $alertType = "danger";
            $alertMessage = "Error updating profile: " . $e->getMessage();
        }
    }
}

// Fill form with current values if not from POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $name = $teacherProfile['name'] ?? $_SESSION["name"];
    $email = $teacherProfile['email'] ?? $_SESSION["email"];
    $dob = $teacherProfile['dob'] ?? '';
    $department = $teacherProfile['department'] ?? '';
    $subjects = $teacherProfile['subjects'] ?? '';
    $experience = $teacherProfile['experience'] ?? '';
}

// Set page variables
$pageTitle = "Teacher Profile";
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
            <p>Teacher</p>
        </div>
    </div>
    
    <div class="profile-details">
        <h3>Edit Profile</h3>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation">
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
                <label for="department">Department</label>
                <select name="department" id="department" class="form-control <?php echo (!empty($department_err)) ? 'is-invalid' : ''; ?>">
                    <option value="">Select Department</option>
                    <?php if (empty($departmentList)): ?>
                        <option value="Computer Science" <?php echo ($department === 'Computer Science') ? 'selected' : ''; ?>>Computer Science</option>
                        <option value="Information Technology" <?php echo ($department === 'Information Technology') ? 'selected' : ''; ?>>Information Technology</option>
                        <option value="Electronics" <?php echo ($department === 'Electronics') ? 'selected' : ''; ?>>Electronics</option>
                        <option value="Civil Engineering" <?php echo ($department === 'Civil Engineering') ? 'selected' : ''; ?>>Civil Engineering</option>
                        <option value="Mechanical Engineering" <?php echo ($department === 'Mechanical Engineering') ? 'selected' : ''; ?>>Mechanical Engineering</option>
                    <?php else: ?>
                        <?php foreach ($departmentList as $dept): ?>
                            <option value="<?php echo $dept; ?>" <?php echo ($department === $dept) ? 'selected' : ''; ?>><?php echo $dept; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <div class="invalid-feedback"><?php echo $department_err; ?></div>
            </div>
            
            <div class="form-group">
                <label for="subjects">Subjects Taught</label>
                <textarea name="subjects" id="subjects" class="form-control <?php echo (!empty($subjects_err)) ? 'is-invalid' : ''; ?>" rows="3" placeholder="Enter subjects separated by commas"><?php echo htmlspecialchars($subjects); ?></textarea>
                <div class="invalid-feedback"><?php echo $subjects_err; ?></div>
            </div>
            
            <div class="form-group">
                <label for="experience">Years of Experience</label>
                <input type="number" name="experience" id="experience" class="form-control <?php echo (!empty($experience_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($experience); ?>" min="0">
                <div class="invalid-feedback"><?php echo $experience_err; ?></div>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </div>
        </form>
    </div>
</div>

<?php include '../../core/includes/footer.php'; ?>

<?php
// Close connection
closeConnection($conn);
?>

