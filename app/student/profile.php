<?php
// Include config file
require_once "../../core/includes/config.php";
require_once "../../core/includes/functions.php";

// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isLoggedIn() || !hasRole("student")) {
    header("location: ../../public/login.php");
    exit;
}

// Get student profile data
$studentProfile = getUserProfileData($_SESSION["id"], "student", $conn);

// Get course and division lists for dropdowns
$courseList = getCourseList($conn);
$divisionList = getDivisionList($conn);

// Initialize variables
$name = $email = $dob = $division = $roll_no = $course = "";
$name_err = $email_err = $dob_err = $division_err = $roll_no_err = $course_err = "";

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
    
    // Validate division
    if (empty(trim($_POST["division"]))) {
        $division_err = "Please enter your division.";
    } else {
        $division = trim($_POST["division"]);
    }
    
    // Validate roll no
    if (empty(trim($_POST["roll_no"]))) {
        $roll_no_err = "Please enter your roll number.";
    } else {
        $roll_no = trim($_POST["roll_no"]);
    }
    
    // Validate course
    if (empty(trim($_POST["course"]))) {
        $course_err = "Please select your course.";
    } else {
        $course = trim($_POST["course"]);
    }
    
    // Check input errors before updating the database
    if (empty($name_err) && empty($dob_err) && empty($division_err) && empty($roll_no_err) && empty($course_err)) {
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
            
            // Update student profile
            $sql = "UPDATE student_profiles SET division = ?, roll_no = ?, course = ? WHERE user_id = ?";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "sssi", $division, $roll_no, $course, $_SESSION["id"]);
                
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
            $studentProfile = getUserProfileData($_SESSION["id"], "student", $conn);
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
    $name = $studentProfile['name'] ?? $_SESSION["name"];
    $email = $studentProfile['email'] ?? $_SESSION["email"];
    $dob = $studentProfile['dob'] ?? '';
    $division = $studentProfile['division'] ?? '';
    $roll_no = $studentProfile['roll_no'] ?? '';
    $course = $studentProfile['course'] ?? '';
}

// Set page variables
$pageTitle = "Student Profile";
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
            <p>Student</p>
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
                <label for="division">Division</label>
                <select name="division" id="division" class="form-control <?php echo (!empty($division_err)) ? 'is-invalid' : ''; ?>">
                    <option value="">Select Division</option>
                    <?php if (empty($divisionList)): ?>
                        <option value="A" <?php echo ($division === 'A') ? 'selected' : ''; ?>>A</option>
                        <option value="B" <?php echo ($division === 'B') ? 'selected' : ''; ?>>B</option>
                        <option value="C" <?php echo ($division === 'C') ? 'selected' : ''; ?>>C</option>
                    <?php else: ?>
                        <?php foreach ($divisionList as $div): ?>
                            <option value="<?php echo $div; ?>" <?php echo ($division === $div) ? 'selected' : ''; ?>><?php echo $div; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <div class="invalid-feedback"><?php echo $division_err; ?></div>
            </div>
            
            <div class="form-group">
                <label for="roll_no">Roll Number</label>
                <input type="text" name="roll_no" id="roll_no" class="form-control <?php echo (!empty($roll_no_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($roll_no); ?>">
                <div class="invalid-feedback"><?php echo $roll_no_err; ?></div>
            </div>
            
            <div class="form-group">
                <label for="course">Course</label>
                <select name="course" id="course" class="form-control <?php echo (!empty($course_err)) ? 'is-invalid' : ''; ?>">
                    <option value="">Select Course</option>
                    <?php if (empty($courseList)): ?>
                        <option value="Computer Science" <?php echo ($course === 'Computer Science') ? 'selected' : ''; ?>>Computer Science</option>
                        <option value="Information Technology" <?php echo ($course === 'Information Technology') ? 'selected' : ''; ?>>Information Technology</option>
                        <option value="Electronics" <?php echo ($course === 'Electronics') ? 'selected' : ''; ?>>Electronics</option>
                        <option value="Civil Engineering" <?php echo ($course === 'Civil Engineering') ? 'selected' : ''; ?>>Civil Engineering</option>
                        <option value="Mechanical Engineering" <?php echo ($course === 'Mechanical Engineering') ? 'selected' : ''; ?>>Mechanical Engineering</option>
                    <?php else: ?>
                        <?php foreach ($courseList as $c): ?>
                            <option value="<?php echo $c; ?>" <?php echo ($course === $c) ? 'selected' : ''; ?>><?php echo $c; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <div class="invalid-feedback"><?php echo $course_err; ?></div>
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

