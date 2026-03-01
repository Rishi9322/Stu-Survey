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

<div class="container py-4">
    <!-- Profile Header -->
    <div class="profile-header-modern mb-4">
        <div class="profile-cover"></div>
        <div class="profile-info-container">
            <div class="profile-avatar-large">
                <?php echo strtoupper(substr($name, 0, 1)); ?>
            </div>
            <div class="profile-details-header">
                <h1><?php echo htmlspecialchars($name); ?></h1>
                <p class="text-muted mb-2">
                    <i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($email); ?>
                </p>
                <div class="profile-badges">
                    <span class="badge bg-primary"><i class="fas fa-chalkboard-teacher me-1"></i>Teacher</span>
                    <?php if (!empty($department)): ?>
                    <span class="badge bg-secondary"><i class="fas fa-building me-1"></i><?php echo htmlspecialchars($department); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($experience)): ?>
                    <span class="badge bg-info"><i class="fas fa-briefcase me-1"></i><?php echo htmlspecialchars($experience); ?> Years</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($alertType) && isset($alertMessage)): ?>
    <div class="alert alert-<?php echo $alertType; ?> alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-<?php echo $alertType === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
        <?php echo $alertMessage; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Profile Summary Card -->
        <div class="col-lg-4">
            <div class="card-modern mb-4">
                <div class="card-header-modern">
                    <h4><i class="fas fa-id-card me-2"></i>Profile Summary</h4>
                </div>
                <div class="card-body-modern">
                    <div class="profile-summary-list">
                        <div class="summary-row">
                            <span class="summary-icon"><i class="fas fa-user text-primary"></i></span>
                            <div class="summary-info">
                                <label>Full Name</label>
                                <span><?php echo htmlspecialchars($name ?: 'Not set'); ?></span>
                            </div>
                        </div>
                        <div class="summary-row">
                            <span class="summary-icon"><i class="fas fa-envelope text-info"></i></span>
                            <div class="summary-info">
                                <label>Email</label>
                                <span><?php echo htmlspecialchars($email); ?></span>
                            </div>
                        </div>
                        <div class="summary-row">
                            <span class="summary-icon"><i class="fas fa-calendar text-success"></i></span>
                            <div class="summary-info">
                                <label>Date of Birth</label>
                                <span><?php echo !empty($dob) ? date('F j, Y', strtotime($dob)) : 'Not set'; ?></span>
                            </div>
                        </div>
                        <div class="summary-row">
                            <span class="summary-icon"><i class="fas fa-building text-warning"></i></span>
                            <div class="summary-info">
                                <label>Department</label>
                                <span><?php echo htmlspecialchars($department ?: 'Not set'); ?></span>
                            </div>
                        </div>
                        <div class="summary-row">
                            <span class="summary-icon"><i class="fas fa-book text-danger"></i></span>
                            <div class="summary-info">
                                <label>Subjects</label>
                                <span><?php echo htmlspecialchars($subjects ?: 'Not set'); ?></span>
                            </div>
                        </div>
                        <div class="summary-row">
                            <span class="summary-icon"><i class="fas fa-briefcase text-secondary"></i></span>
                            <div class="summary-info">
                                <label>Experience</label>
                                <span><?php echo htmlspecialchars($experience ?: '0'); ?> years</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links Card -->
            <div class="card-modern">
                <div class="card-header-modern">
                    <h4><i class="fas fa-link me-2"></i>Quick Links</h4>
                </div>
                <div class="card-body-modern p-0">
                    <a href="dashboard.php" class="quick-link-item">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                        <i class="fas fa-chevron-right ms-auto"></i>
                    </a>
                    <a href="survey.php" class="quick-link-item">
                        <i class="fas fa-poll"></i>
                        <span>Take Survey</span>
                        <i class="fas fa-chevron-right ms-auto"></i>
                    </a>
                    <a href="analytics.php" class="quick-link-item">
                        <i class="fas fa-chart-bar"></i>
                        <span>View Analytics</span>
                        <i class="fas fa-chevron-right ms-auto"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Edit Profile Form -->
        <div class="col-lg-8">
            <div class="card-modern">
                <div class="card-header-modern">
                    <h4><i class="fas fa-user-edit me-2"></i>Edit Profile</h4>
                </div>
                <div class="card-body-modern">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="profile-form">
                        <!-- Personal Information Section -->
                        <div class="form-section">
                            <h5 class="form-section-title">
                                <i class="fas fa-user-circle me-2"></i>Personal Information
                            </h5>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" name="name" id="name" 
                                            class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" 
                                            value="<?php echo htmlspecialchars($name); ?>" placeholder="Full Name">
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
                                            value="<?php echo htmlspecialchars($email); ?>" readonly placeholder="Email">
                                        <label for="email"><i class="fas fa-envelope me-2"></i>Email</label>
                                        <small class="text-muted mt-1 d-block">
                                            <i class="fas fa-lock me-1"></i>Email cannot be changed
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="date" name="dob" id="dob" 
                                            class="form-control <?php echo (!empty($dob_err)) ? 'is-invalid' : ''; ?>" 
                                            value="<?php echo htmlspecialchars($dob); ?>" placeholder="Date of Birth">
                                        <label for="dob"><i class="fas fa-calendar me-2"></i>Date of Birth</label>
                                        <?php if (!empty($dob_err)): ?>
                                        <div class="invalid-feedback"><?php echo $dob_err; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Professional Information Section -->
                        <div class="form-section">
                            <h5 class="form-section-title">
                                <i class="fas fa-briefcase me-2"></i>Professional Information
                            </h5>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select name="department" id="department" 
                                            class="form-select <?php echo (!empty($department_err)) ? 'is-invalid' : ''; ?>">
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
                                        <label for="department"><i class="fas fa-building me-2"></i>Department</label>
                                        <?php if (!empty($department_err)): ?>
                                        <div class="invalid-feedback"><?php echo $department_err; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="number" name="experience" id="experience" 
                                            class="form-control <?php echo (!empty($experience_err)) ? 'is-invalid' : ''; ?>" 
                                            value="<?php echo htmlspecialchars($experience); ?>" min="0" placeholder="Experience">
                                        <label for="experience"><i class="fas fa-clock me-2"></i>Years of Experience</label>
                                        <?php if (!empty($experience_err)): ?>
                                        <div class="invalid-feedback"><?php echo $experience_err; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="subjects" class="form-label fw-semibold">
                                        <i class="fas fa-book me-2"></i>Subjects Taught
                                    </label>
                                    <textarea name="subjects" id="subjects" 
                                        class="form-control <?php echo (!empty($subjects_err)) ? 'is-invalid' : ''; ?>" 
                                        rows="3" placeholder="Enter subjects separated by commas (e.g., Mathematics, Physics, Computer Science)"><?php echo htmlspecialchars($subjects); ?></textarea>
                                    <?php if (!empty($subjects_err)): ?>
                                    <div class="invalid-feedback"><?php echo $subjects_err; ?></div>
                                    <?php endif; ?>
                                    <small class="text-muted">Separate multiple subjects with commas</small>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Update Profile
                            </button>
                            <a href="dashboard.php" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Profile Header Modern */
.profile-header-modern {
    background: white;
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-md);
    overflow: hidden;
}

.profile-cover {
    height: 120px;
    background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);
}

.profile-info-container {
    display: flex;
    align-items: flex-end;
    padding: 0 2rem 1.5rem;
    margin-top: -50px;
}

.profile-avatar-large {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #6366f1 0%, #818cf8 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: 700;
    color: white;
    border: 4px solid white;
    box-shadow: var(--shadow-lg);
    flex-shrink: 0;
}

.profile-details-header {
    margin-left: 1.5rem;
    padding-bottom: 0.5rem;
}

.profile-details-header h1 {
    font-size: 1.75rem;
    font-weight: 700;
    margin: 0 0 0.25rem 0;
    color: var(--text-primary);
}

.profile-badges {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.profile-badges .badge {
    font-weight: 500;
    font-size: 0.8rem;
    padding: 0.4rem 0.75rem;
}

/* Card Modern (reused) */
.card-modern {
    background: white;
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-md);
    overflow: hidden;
}

.card-header-modern {
    padding: 1.25rem 1.5rem;
    background: var(--gray-50);
    border-bottom: 1px solid var(--gray-100);
}

.card-header-modern h4 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary);
}

.card-body-modern {
    padding: 1.5rem;
}

/* Profile Summary List */
.profile-summary-list {
    display: flex;
    flex-direction: column;
}

.summary-row {
    display: flex;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid var(--gray-100);
}

.summary-row:last-child { border-bottom: none; }

.summary-icon {
    width: 40px;
    height: 40px;
    background: var(--gray-100);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    font-size: 1rem;
}

.summary-info {
    flex-grow: 1;
}

.summary-info label {
    display: block;
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-bottom: 0.15rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.summary-info span {
    font-weight: 500;
    color: var(--text-primary);
}

/* Quick Links */
.quick-link-item {
    display: flex;
    align-items: center;
    padding: 1rem 1.5rem;
    text-decoration: none;
    color: var(--text-primary);
    border-bottom: 1px solid var(--gray-100);
    transition: all 0.2s ease;
}

.quick-link-item:last-child { border-bottom: none; }

.quick-link-item:hover {
    background: var(--gray-50);
    color: var(--primary-color);
}

.quick-link-item i:first-child {
    width: 20px;
    text-align: center;
    margin-right: 1rem;
    color: var(--primary-color);
}

.quick-link-item span { flex-grow: 1; font-weight: 500; }

/* Form Section */
.form-section {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--gray-100);
}

.form-section:last-of-type {
    border-bottom: none;
    margin-bottom: 0;
}

.form-section-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 1.25rem;
}

/* Form Floating Labels */
.form-floating > .form-control,
.form-floating > .form-select {
    border-radius: var(--radius-md);
    border: 2px solid var(--gray-200);
    padding: 1rem 0.75rem;
}

.form-floating > .form-control:focus,
.form-floating > .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
}

.form-floating > label {
    color: var(--text-muted);
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 1rem;
    padding-top: 1rem;
}

.form-actions .btn {
    padding: 0.75rem 2rem;
}

/* Responsive */
@media (max-width: 768px) {
    .profile-info-container {
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 0 1.5rem 1.5rem;
    }
    
    .profile-avatar-large {
        margin-top: -50px;
    }
    
    .profile-details-header {
        margin-left: 0;
        margin-top: 1rem;
    }
    
    .profile-badges {
        justify-content: center;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions .btn {
        width: 100%;
    }
}
</style>

<?php include '../../core/includes/footer.php'; ?>

<?php
// Close connection
closeConnection($conn);
?>

