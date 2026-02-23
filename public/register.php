<?php
// Start output buffering at the very beginning
ob_start();

// Include config file
require_once "../core/includes/config.php";
require_once "../core/includes/functions.php";

// Initialize variables
$name = $email = $password = $confirm_password = $dob = $role = "";
$division = $roll_no = $course = $department = $subjects = $experience = "";
$name_err = $email_err = $password_err = $confirm_password_err = $dob_err = $role_err = "";
$division_err = $roll_no_err = $course_err = $department_err = $subjects_err = $experience_err = "";

// Get course, department and division lists for dropdowns
$courseList = getCourseList($conn);
$departmentList = getDepartmentList($conn);
$divisionList = getDivisionList($conn);

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter your name.";
    } else {
        $name = trim($_POST["name"]);
    }
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE email = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            
            // Set parameters
            $param_email = trim($_POST["email"]);
            
            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Store result
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $email_err = "This email is already taken.";
                } else {
                    $email = trim($_POST["email"]);
                }
            } else {
                // More explicit error for debugging during local testing
                error_log("Error checking email existence: " . mysqli_error($conn));
                $email_err = "Server error while validating email. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        } else {
            error_log("Error preparing email check statement: " . mysqli_error($conn));
            $email_err = "Server error. Please try again later.";
        }
    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";     
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";     
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }
    
    // Validate DOB
    if (empty(trim($_POST["dob"]))) {
        $dob_err = "Please enter your date of birth.";
    } else {
        $dob = trim($_POST["dob"]);
    }
    
    // Validate role
    if (empty(trim($_POST["role"]))) {
        $role_err = "Please select a role.";
    } else {
        $role = trim($_POST["role"]);
        
        // Additional validations based on role
        if ($role === "student") {
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
        } elseif ($role === "teacher") {
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
        }
    }
    
    // Check input errors before inserting in database
    $student_profile_valid = ($role !== "student") || (empty($division_err) && empty($roll_no_err) && empty($course_err));
    $teacher_profile_valid = ($role !== "teacher") || (empty($department_err) && empty($subjects_err));
    
    if (empty($name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err) && empty($dob_err) && empty($role_err) && $student_profile_valid && $teacher_profile_valid) {
        
        // Begin transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Register the user
            $user_id = registerUser($name, $email, $password, $dob, $role, $conn);
            
            if ($user_id) {
                // Create profile based on role
                if ($role === "student") {
                    // Create student profile
                    $sql = "INSERT INTO student_profiles (user_id, division, roll_no, course) VALUES (?, ?, ?, ?)";
                    
                    if ($stmt = mysqli_prepare($conn, $sql)) {
                        mysqli_stmt_bind_param($stmt, "isss", $user_id, $division, $roll_no, $course);
                        
                        if (!mysqli_stmt_execute($stmt)) {
                            throw new Exception(mysqli_error($conn));
                        }
                        
                        mysqli_stmt_close($stmt);
                    } else {
                        throw new Exception(mysqli_error($conn));
                    }
                } elseif ($role === "teacher") {
                    // Create teacher profile
                    $sql = "INSERT INTO teacher_profiles (user_id, department, subjects, experience) VALUES (?, ?, ?, ?)";
                    
                    if ($stmt = mysqli_prepare($conn, $sql)) {
                        mysqli_stmt_bind_param($stmt, "issi", $user_id, $department, $subjects, $experience);
                        
                        if (!mysqli_stmt_execute($stmt)) {
                            throw new Exception(mysqli_error($conn));
                        }
                        
                        mysqli_stmt_close($stmt);
                    } else {
                        throw new Exception(mysqli_error($conn));
                    }
                }
                
                // Commit transaction
                mysqli_commit($conn);
                
                // Login the user
                if (loginUser($email, $password, $role, $conn)) {
                    // Determine the redirect location based on role
                    $targetLocation = "";
                    
                    switch ($role) {
                        case "student":
                            $targetLocation = "student/dashboard.php";
                            break;
                        case "teacher":
                            $targetLocation = "teacher/dashboard.php";
                            break;
                        case "admin":
                            $targetLocation = "admin/dashboard.php";
                            break;
                        default:
                            $targetLocation = "index.php";
                    }
                    
                    // Clean output buffer and redirect
                    if (ob_get_level()) {
                        ob_end_clean();
                    }
                    
                    // Redirect directly
                    header("Location: " . $targetLocation);
                    exit;
                }
            } else {
                throw new Exception("Error registering user.");
            }
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            $alertType = "danger";
            $alertMessage = "Error: " . $e->getMessage();
        }
    }
}

// Set page variables
$pageTitle = "Register - Student Satisfaction Survey";
$basePath = "../";
?>

<?php include '../core/includes/header.php'; ?>

<div class="auth-container">
    <h2>Register</h2>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" name="name" id="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
            <div class="invalid-feedback"><?php echo $name_err; ?></div>
        </div>
        
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
            <small class="form-text text-muted">Password must be at least 6 characters long.</small>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
            <div class="invalid-feedback"><?php echo $confirm_password_err; ?></div>
        </div>
        
        <div class="form-group">
            <label for="dob">Date of Birth</label>
            <input type="date" name="dob" id="dob" class="form-control <?php echo (!empty($dob_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $dob; ?>">
            <div class="invalid-feedback"><?php echo $dob_err; ?></div>
        </div>
        
        <div class="form-group">
            <label>Select Your Role</label>
            <div class="role-selector">
                <div class="role-option <?php echo ($role === 'student') ? 'active' : ''; ?>" data-role="student">
                    <i class="fas fa-user-graduate"></i>
                    <div>Student</div>
                </div>
                <div class="role-option <?php echo ($role === 'teacher') ? 'active' : ''; ?>" data-role="teacher">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <div>Teacher</div>
                </div>
                <div class="role-option <?php echo ($role === 'admin') ? 'active' : ''; ?>" data-role="admin">
                    <i class="fas fa-user-shield"></i>
                    <div>Admin/HOD</div>
                </div>
            </div>
            <input type="hidden" name="role" id="role" value="<?php echo $role; ?>">
            <div class="invalid-feedback"><?php echo $role_err; ?></div>
        </div>
        
        <!-- Student-specific fields -->
        <div id="student-fields" class="additional-fields <?php echo ($role === 'student') ? 'active' : ''; ?>">
            <h3>Student Details</h3>
            
            <div class="form-group">
                <label for="division">Division</label>
                <select name="division" id="division" class="form-control <?php echo (!empty($division_err)) ? 'is-invalid' : ''; ?>">
                    <option value="">Select Division</option>
                    <?php if (empty($divisionList)): ?>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
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
                <input type="text" name="roll_no" id="roll_no" class="form-control <?php echo (!empty($roll_no_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $roll_no; ?>">
                <div class="invalid-feedback"><?php echo $roll_no_err; ?></div>
            </div>
            
            <div class="form-group">
                <label for="course">Course</label>
                <select name="course" id="course" class="form-control <?php echo (!empty($course_err)) ? 'is-invalid' : ''; ?>">
                    <option value="">Select Course</option>
                    <?php if (empty($courseList)): ?>
                        <option value="Computer Science">Computer Science</option>
                        <option value="Information Technology">Information Technology</option>
                        <option value="Electronics">Electronics</option>
                        <option value="Civil Engineering">Civil Engineering</option>
                        <option value="Mechanical Engineering">Mechanical Engineering</option>
                    <?php else: ?>
                        <?php foreach ($courseList as $c): ?>
                            <option value="<?php echo $c; ?>" <?php echo ($course === $c) ? 'selected' : ''; ?>><?php echo $c; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <div class="invalid-feedback"><?php echo $course_err; ?></div>
            </div>
        </div>
        
        <!-- Teacher-specific fields -->
        <div id="teacher-fields" class="additional-fields <?php echo ($role === 'teacher') ? 'active' : ''; ?>">
            <h3>Teacher Details</h3>
            
            <div class="form-group">
                <label for="department">Department</label>
                <select name="department" id="department" class="form-control <?php echo (!empty($department_err)) ? 'is-invalid' : ''; ?>">
                    <option value="">Select Department</option>
                    <?php if (empty($departmentList)): ?>
                        <option value="Computer Science">Computer Science</option>
                        <option value="Information Technology">Information Technology</option>
                        <option value="Electronics">Electronics</option>
                        <option value="Civil Engineering">Civil Engineering</option>
                        <option value="Mechanical Engineering">Mechanical Engineering</option>
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
                <textarea name="subjects" id="subjects" class="form-control <?php echo (!empty($subjects_err)) ? 'is-invalid' : ''; ?>" rows="3" placeholder="Enter subjects separated by commas"><?php echo $subjects; ?></textarea>
                <div class="invalid-feedback"><?php echo $subjects_err; ?></div>
            </div>
            
            <div class="form-group">
                <label for="experience">Years of Experience</label>
                <input type="number" name="experience" id="experience" class="form-control <?php echo (!empty($experience_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $experience; ?>" min="0">
                <div class="invalid-feedback"><?php echo $experience_err; ?></div>
            </div>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </div>
        
        <p class="text-center">Already have an account? <a href="login.php">Login here</a></p>
    </form>
</div>

<?php include '../core/includes/footer.php'; ?>

<?php
// Close connection
closeConnection($conn);
?>
