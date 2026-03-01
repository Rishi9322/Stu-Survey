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

<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header-modern mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li class="breadcrumb-item active">Profile</li>
                    </ol>
                </nav>
                <h1 class="page-title"><i class="fas fa-user-circle me-2"></i>My Profile</h1>
            </div>
            <a href="dashboard.php" class="btn btn-outline-primary">
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
                <div class="profile-card-header">
                    <div class="profile-avatar-large">
                        <?php echo strtoupper(substr($name, 0, 1)); ?>
                    </div>
                    <h3 class="profile-name"><?php echo htmlspecialchars($name); ?></h3>
                    <p class="profile-email"><?php echo htmlspecialchars($email); ?></p>
                    <span class="profile-badge">
                        <i class="fas fa-user-graduate me-1"></i>Student
                    </span>
                </div>
                <div class="profile-card-body">
                    <div class="profile-info-item">
                        <div class="info-icon"><i class="fas fa-id-card"></i></div>
                        <div class="info-content">
                            <span class="info-label">Roll Number</span>
                            <span class="info-value"><?php echo htmlspecialchars($roll_no ?: 'Not set'); ?></span>
                        </div>
                    </div>
                    <div class="profile-info-item">
                        <div class="info-icon"><i class="fas fa-book"></i></div>
                        <div class="info-content">
                            <span class="info-label">Course</span>
                            <span class="info-value"><?php echo htmlspecialchars($course ?: 'Not set'); ?></span>
                        </div>
                    </div>
                    <div class="profile-info-item">
                        <div class="info-icon"><i class="fas fa-users"></i></div>
                        <div class="info-content">
                            <span class="info-label">Division</span>
                            <span class="info-value"><?php echo htmlspecialchars($division ?: 'Not set'); ?></span>
                        </div>
                    </div>
                    <div class="profile-info-item">
                        <div class="info-icon"><i class="fas fa-birthday-cake"></i></div>
                        <div class="info-content">
                            <span class="info-label">Date of Birth</span>
                            <span class="info-value"><?php echo $dob ? date('F j, Y', strtotime($dob)) : 'Not set'; ?></span>
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
                        <a href="survey.php" class="quick-link-item">
                            <i class="fas fa-poll"></i>
                            <span>Take Survey</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="analytics.php" class="quick-link-item">
                            <i class="fas fa-chart-bar"></i>
                            <span>View Analytics</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="<?php echo $basePath; ?>public/help.php" class="quick-link-item">
                            <i class="fas fa-question-circle"></i>
                            <span>Help Center</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Profile Form -->
        <div class="col-lg-8">
            <div class="card-modern">
                <div class="card-header-modern">
                    <h4><i class="fas fa-edit me-2"></i>Edit Profile Information</h4>
                </div>
                <div class="card-body-modern">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="profile-form">
                        <div class="row g-4">
                            <!-- Personal Information Section -->
                            <div class="col-12">
                                <h5 class="form-section-title">
                                    <i class="fas fa-user me-2"></i>Personal Information
                                </h5>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="name" id="name" 
                                           class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" 
                                           value="<?php echo htmlspecialchars($name); ?>"
                                           placeholder="Full Name">
                                    <label for="name"><i class="fas fa-user me-2"></i>Full Name</label>
                                    <?php if (!empty($name_err)): ?>
                                        <div class="invalid-feedback"><?php echo $name_err; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" name="email" id="email" 
                                           class="form-control bg-light" 
                                           value="<?php echo htmlspecialchars($email); ?>" 
                                           readonly
                                           placeholder="Email">
                                    <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
                                    <small class="text-muted mt-1 d-block">
                                        <i class="fas fa-lock me-1"></i>Email cannot be changed
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="date" name="dob" id="dob" 
                                           class="form-control <?php echo (!empty($dob_err)) ? 'is-invalid' : ''; ?>" 
                                           value="<?php echo htmlspecialchars($dob); ?>"
                                           placeholder="Date of Birth">
                                    <label for="dob"><i class="fas fa-calendar me-2"></i>Date of Birth</label>
                                    <?php if (!empty($dob_err)): ?>
                                        <div class="invalid-feedback"><?php echo $dob_err; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Academic Information Section -->
                            <div class="col-12 mt-4">
                                <h5 class="form-section-title">
                                    <i class="fas fa-graduation-cap me-2"></i>Academic Information
                                </h5>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="roll_no" id="roll_no" 
                                           class="form-control <?php echo (!empty($roll_no_err)) ? 'is-invalid' : ''; ?>" 
                                           value="<?php echo htmlspecialchars($roll_no); ?>"
                                           placeholder="Roll Number">
                                    <label for="roll_no"><i class="fas fa-id-badge me-2"></i>Roll Number</label>
                                    <?php if (!empty($roll_no_err)): ?>
                                        <div class="invalid-feedback"><?php echo $roll_no_err; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select name="course" id="course" 
                                            class="form-select <?php echo (!empty($course_err)) ? 'is-invalid' : ''; ?>">
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
                                    <label for="course"><i class="fas fa-book me-2"></i>Course</label>
                                    <?php if (!empty($course_err)): ?>
                                        <div class="invalid-feedback"><?php echo $course_err; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select name="division" id="division" 
                                            class="form-select <?php echo (!empty($division_err)) ? 'is-invalid' : ''; ?>">
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
                                    <label for="division"><i class="fas fa-users me-2"></i>Division</label>
                                    <?php if (!empty($division_err)): ?>
                                        <div class="invalid-feedback"><?php echo $division_err; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="form-actions mt-4 pt-4 border-top">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                            <button type="reset" class="btn btn-outline-secondary btn-lg ms-2">
                                <i class="fas fa-undo me-2"></i>Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Account Security Card -->
            <div class="card-modern mt-4">
                <div class="card-header-modern">
                    <h4><i class="fas fa-shield-alt me-2"></i>Account Security</h4>
                </div>
                <div class="card-body-modern">
                    <div class="security-info">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5>Password</h5>
                                <p class="text-muted mb-0">Last changed: Unknown</p>
                            </div>
                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                <button class="btn btn-outline-primary" onclick="alert('Password change feature coming soon!')">
                                    <i class="fas fa-key me-2"></i>Change Password
                                </button>
                            </div>
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
    background: white;
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    border: 1px solid var(--gray-200);
}

.page-title {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
}

.breadcrumb {
    background: none;
    padding: 0;
    margin: 0;
    font-size: 0.875rem;
}

.breadcrumb-item a {
    color: var(--primary-color);
    text-decoration: none;
}

.breadcrumb-item.active {
    color: var(--text-muted);
}

/* Profile Card */
.profile-card-modern {
    background: white;
    border-radius: var(--radius-lg);
    border: 1px solid var(--gray-200);
    overflow: hidden;
}

.profile-card-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
    padding: 2rem;
    text-align: center;
    color: white;
}

.profile-avatar-large {
    width: 100px;
    height: 100px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: 700;
    color: white;
    margin: 0 auto 1rem;
    border: 4px solid rgba(255, 255, 255, 0.3);
}

.profile-name {
    color: white;
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.profile-email {
    color: rgba(255, 255, 255, 0.85);
    font-size: 0.9rem;
    margin-bottom: 0.75rem;
}

.profile-badge {
    display: inline-block;
    background: rgba(255, 255, 255, 0.2);
    padding: 0.35rem 1rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 500;
}

.profile-card-body {
    padding: 1.5rem;
}

.profile-info-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--gray-100);
}

.profile-info-item:last-child {
    border-bottom: none;
}

.profile-info-item .info-icon {
    width: 40px;
    height: 40px;
    background: var(--gray-100);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-color);
    margin-right: 1rem;
    flex-shrink: 0;
}

.profile-info-item .info-content {
    flex-grow: 1;
}

.profile-info-item .info-label {
    display: block;
    font-size: 0.75rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.profile-info-item .info-value {
    display: block;
    font-size: 0.95rem;
    font-weight: 500;
    color: var(--text-primary);
}

/* Quick Links */
.quick-links-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.quick-link-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    background: var(--gray-50);
    border-radius: var(--radius-md);
    text-decoration: none;
    color: var(--text-primary);
    transition: all 0.3s ease;
}

.quick-link-item:hover {
    background: var(--primary-color);
    color: white;
    transform: translateX(5px);
}

.quick-link-item i:first-child {
    width: 24px;
    margin-right: 0.75rem;
}

.quick-link-item span {
    flex-grow: 1;
    font-weight: 500;
}

.quick-link-item i:last-child {
    opacity: 0.5;
}

/* Form Styles */
.form-section-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--primary-color);
    margin-bottom: 0;
}

.form-floating > label {
    color: var(--text-muted);
}

.form-floating > .form-control:focus ~ label,
.form-floating > .form-control:not(:placeholder-shown) ~ label,
.form-floating > .form-select ~ label {
    color: var(--primary-color);
}

.form-control:focus,
.form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.15);
}

.form-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

/* Security Info */
.security-info {
    padding: 1rem;
    background: var(--gray-50);
    border-radius: var(--radius-md);
}

.security-info h5 {
    margin-bottom: 0.25rem;
    font-weight: 600;
}

/* Card Modern (reuse from dashboard) */
.card-modern {
    background: white;
    border-radius: var(--radius-lg);
    border: 1px solid var(--gray-200);
    overflow: hidden;
}

.card-header-modern {
    background: var(--gray-50);
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--gray-200);
}

.card-header-modern h4,
.card-header-modern h5 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary);
}

.card-body-modern {
    padding: 1.5rem;
}

/* Responsive */
@media (max-width: 768px) {
    .page-header-modern {
        text-align: center;
    }
    
    .page-header-modern .btn {
        margin-top: 1rem;
    }
    
    .profile-avatar-large {
        width: 80px;
        height: 80px;
        font-size: 2rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
        margin-left: 0 !important;
    }
}
</style>

<?php include '../../core/includes/footer.php'; ?>

<?php
// Close connection
closeConnection($conn);
?>

