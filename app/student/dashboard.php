<?php
// Start output buffering
if (ob_get_level() == 0) {
    ob_start();
}

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

// Check if the student has completed the survey
$surveyCompleted = isSurveyCompleted($_SESSION["id"], "student", $conn);

// Get some stats for the dashboard
$totalSurveys = 1; // Current active survey
$pendingSurveys = $surveyCompleted ? 0 : 1;

// Handle anonymous feedback submission
$feedbackAlert = null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_feedback'])) {
    $feedbackSubject = trim($_POST['feedback_subject'] ?? '');
    $feedbackDescription = trim($_POST['feedback_description'] ?? '');
    $feedbackType = $_POST['feedback_type'] ?? '';
    
    if (empty($feedbackSubject) || empty($feedbackDescription) || empty($feedbackType)) {
        $feedbackAlert = ['type' => 'danger', 'message' => 'Please fill in all fields.'];
    } else {
        $result = submitAnonymousFeedback($feedbackSubject, $feedbackDescription, $feedbackType, 'student', $conn);
        $feedbackAlert = ['type' => $result['success'] ? 'success' : 'danger', 'message' => $result['message']];
    }
}

// Set page variables
$pageTitle = "Student Dashboard";
$basePath = "../../";
?>

<?php include '../../core/includes/header.php'; ?>

<?php
// Show login success message if set
if (isset($_SESSION['login_success']) && $_SESSION['login_success']) {
    echo '<div class="container"><div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
        <i class="fas fa-check-circle me-2"></i>Login successful! Welcome to your student dashboard.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div></div>';
    unset($_SESSION['login_success']);
}
?>

<div class="container py-4">
    <!-- Welcome Header Section -->
    <div class="dashboard-welcome mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-flex align-items-center">
                    <div class="avatar-circle me-3">
                        <?php echo strtoupper(substr($_SESSION["name"], 0, 1)); ?>
                    </div>
                    <div>
                        <h1 class="mb-1">Welcome back, <?php echo htmlspecialchars($_SESSION["name"]); ?>!</h1>
                        <p class="text-muted mb-0">
                            <i class="fas fa-graduation-cap me-2"></i><?php echo htmlspecialchars($studentProfile['course'] ?? 'Student'); ?>
                            <?php if (!empty($studentProfile['division'])): ?>
                                <span class="mx-2">|</span>
                                <i class="fas fa-users me-2"></i>Division: <?php echo htmlspecialchars($studentProfile['division']); ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <span class="badge bg-<?php echo $surveyCompleted ? 'success' : 'warning'; ?> fs-6 px-3 py-2">
                    <i class="fas fa-<?php echo $surveyCompleted ? 'check-circle' : 'clock'; ?> me-2"></i>
                    Survey <?php echo $surveyCompleted ? 'Completed' : 'Pending'; ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Stats Cards Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="stat-card-modern gradient-primary">
                <div class="stat-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo htmlspecialchars($studentProfile['course'] ?? 'N/A'); ?></h3>
                    <p>Your Course</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card-modern gradient-secondary">
                <div class="stat-icon">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo htmlspecialchars($studentProfile['division'] ?? 'N/A'); ?></h3>
                    <p>Your Division</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card-modern gradient-accent">
                <div class="stat-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $totalSurveys; ?></h3>
                    <p>Total Surveys</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card-modern <?php echo $surveyCompleted ? 'gradient-success' : 'gradient-warning'; ?>">
                <div class="stat-icon">
                    <i class="fas fa-<?php echo $surveyCompleted ? 'check-double' : 'hourglass-half'; ?>"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $pendingSurveys; ?></h3>
                    <p>Pending Surveys</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Survey Alert -->
    <?php if (!$surveyCompleted): ?>
    <div class="alert alert-survey mb-4">
        <div class="d-flex align-items-center">
            <div class="alert-icon me-3">
                <i class="fas fa-bell"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="mb-1">Complete Your Survey</h5>
                <p class="mb-0">You haven't completed the satisfaction survey yet. Your feedback helps us improve!</p>
            </div>
            <a href="survey.php" class="btn btn-light btn-lg ms-3">
                <i class="fas fa-arrow-right me-2"></i>Take Survey
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quick Actions Grid -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <h4 class="section-title mb-3">
                <i class="fas fa-bolt me-2"></i>Quick Actions
            </h4>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="profile.php" class="action-card">
                <div class="action-icon bg-primary">
                    <i class="fas fa-user-edit"></i>
                </div>
                <div class="action-content">
                    <h5>View/Edit Profile</h5>
                    <p>Update your personal information and preferences</p>
                </div>
                <i class="fas fa-chevron-right action-arrow"></i>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="survey.php" class="action-card <?php echo $surveyCompleted ? '' : 'action-highlight'; ?>">
                <div class="action-icon bg-<?php echo $surveyCompleted ? 'success' : 'warning'; ?>">
                    <i class="fas fa-poll"></i>
                </div>
                <div class="action-content">
                    <h5><?php echo $surveyCompleted ? 'View Submitted Survey' : 'Take Survey'; ?></h5>
                    <p><?php echo $surveyCompleted ? 'Review your submitted responses' : 'Share your valuable feedback'; ?></p>
                </div>
                <i class="fas fa-chevron-right action-arrow"></i>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="analytics.php" class="action-card">
                <div class="action-icon bg-info">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="action-content">
                    <h5>View Analytics</h5>
                    <p>Explore survey insights and statistics</p>
                </div>
                <i class="fas fa-chevron-right action-arrow"></i>
            </a>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="row g-4">
        <!-- Announcements -->
        <div class="col-lg-8">
            <div class="card-modern">
                <div class="card-header-modern">
                    <h4><i class="fas fa-bullhorn me-2"></i>Recent Announcements</h4>
                </div>
                <div class="card-body-modern">
                    <div class="announcement-list">
                        <div class="announcement-item-modern">
                            <div class="announcement-date">
                                <span class="day"><?php echo date('d'); ?></span>
                                <span class="month"><?php echo date('M'); ?></span>
                            </div>
                            <div class="announcement-content">
                                <h5>New Survey Available</h5>
                                <p>The latest satisfaction survey is now available. Please complete it at your earliest convenience to help us improve.</p>
                                <span class="announcement-badge badge bg-primary">New</span>
                            </div>
                        </div>
                        <div class="announcement-item-modern">
                            <div class="announcement-date">
                                <span class="day"><?php echo date('d', strtotime('-3 days')); ?></span>
                                <span class="month"><?php echo date('M', strtotime('-3 days')); ?></span>
                            </div>
                            <div class="announcement-content">
                                <h5>Improvements Based on Your Feedback</h5>
                                <p>We've made several improvements to our facilities based on the feedback received in the previous survey. Thank you for your input!</p>
                            </div>
                        </div>
                        <div class="announcement-item-modern">
                            <div class="announcement-date">
                                <span class="day"><?php echo date('d', strtotime('-7 days')); ?></span>
                                <span class="month"><?php echo date('M', strtotime('-7 days')); ?></span>
                            </div>
                            <div class="announcement-content">
                                <h5>Welcome to the New Semester</h5>
                                <p>We welcome all students to the new academic semester. Make sure to check your profile information is up to date.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Summary & Quick Links -->
        <div class="col-lg-4">
            <div class="card-modern mb-4">
                <div class="card-header-modern">
                    <h4><i class="fas fa-id-card me-2"></i>Profile Summary</h4>
                </div>
                <div class="card-body-modern">
                    <div class="profile-summary">
                        <div class="profile-summary-item">
                            <span class="label">Name</span>
                            <span class="value"><?php echo htmlspecialchars($_SESSION["name"]); ?></span>
                        </div>
                        <div class="profile-summary-item">
                            <span class="label">Email</span>
                            <span class="value"><?php echo htmlspecialchars($_SESSION["email"]); ?></span>
                        </div>
                        <div class="profile-summary-item">
                            <span class="label">Roll No</span>
                            <span class="value"><?php echo htmlspecialchars($studentProfile['roll_no'] ?? 'Not set'); ?></span>
                        </div>
                        <div class="profile-summary-item">
                            <span class="label">Course</span>
                            <span class="value"><?php echo htmlspecialchars($studentProfile['course'] ?? 'Not set'); ?></span>
                        </div>
                        <div class="profile-summary-item">
                            <span class="label">Division</span>
                            <span class="value"><?php echo htmlspecialchars($studentProfile['division'] ?? 'Not set'); ?></span>
                        </div>
                    </div>
                    <a href="profile.php" class="btn btn-outline-primary w-100 mt-3">
                        <i class="fas fa-edit me-2"></i>Edit Profile
                    </a>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card-modern help-card">
                <div class="card-body-modern text-center py-4">
                    <div class="help-icon mb-3">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <h5>Need Help?</h5>
                    <p class="text-muted">If you have any questions or need assistance, feel free to reach out.</p>
                    <a href="/stu/public/help.php" class="btn btn-primary">
                        <i class="fas fa-life-ring me-2"></i>Get Help
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Anonymous Feedback Section -->
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="card-modern feedback-card">
                <div class="card-header-modern">
                    <h4><i class="fas fa-comment-alt me-2"></i>Anonymous Feedback</h4>
                    <span class="badge bg-success"><i class="fas fa-user-secret me-1"></i>100% Anonymous</span>
                </div>
                <div class="card-body-modern">
                    <?php if ($feedbackAlert): ?>
                    <div class="alert alert-<?php echo $feedbackAlert['type']; ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-<?php echo $feedbackAlert['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                        <?php echo htmlspecialchars($feedbackAlert['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-lg-4 mb-4 mb-lg-0">
                            <div class="anonymous-info">
                                <div class="anonymous-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <h5>Your Privacy is Protected</h5>
                                <ul class="privacy-list">
                                    <li><i class="fas fa-check text-success me-2"></i>No personal data stored</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Cannot be traced back to you</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Only role (student) is recorded</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Helps improve the institution</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <form method="post" action="" class="feedback-form">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Type of Feedback *</label>
                                    <div class="feedback-type-cards">
                                        <label class="feedback-type-card">
                                            <input type="radio" name="feedback_type" value="suggestion" required>
                                            <div class="card-content">
                                                <i class="fas fa-lightbulb"></i>
                                                <span>Suggestion</span>
                                            </div>
                                        </label>
                                        <label class="feedback-type-card">
                                            <input type="radio" name="feedback_type" value="complaint" required>
                                            <div class="card-content">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <span>Complaint</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="feedback_subject" class="form-label fw-semibold">Subject *</label>
                                    <input type="text" class="form-control" id="feedback_subject" name="feedback_subject" 
                                           placeholder="Brief title for your feedback" required maxlength="255">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="feedback_description" class="form-label fw-semibold">Description *</label>
                                    <textarea class="form-control" id="feedback_description" name="feedback_description" 
                                              rows="4" placeholder="Describe your feedback in detail..." required></textarea>
                                </div>
                                
                                <button type="submit" name="submit_feedback" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Anonymously
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Dashboard Welcome Section */
.dashboard-welcome {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
    border-radius: var(--radius-xl);
    padding: 2rem;
    color: white;
}

.dashboard-welcome h1 {
    color: white;
    font-size: 1.75rem;
    font-weight: 700;
}

.dashboard-welcome .text-muted {
    color: rgba(255, 255, 255, 0.85) !important;
}

.avatar-circle {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
    color: white;
    border: 3px solid rgba(255, 255, 255, 0.3);
}

/* Modern Stat Cards */
.stat-card-modern {
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    color: white;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    height: 100%;
}

.stat-card-modern:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-xl);
}

.stat-card-modern .stat-icon {
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.stat-card-modern .stat-content h3 {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
    color: white;
}

.stat-card-modern .stat-content p {
    margin: 0;
    font-size: 0.875rem;
    opacity: 0.9;
}

.gradient-primary { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); }
.gradient-secondary { background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%); }
.gradient-accent { background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%); }
.gradient-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
.gradient-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }

/* Survey Alert */
.alert-survey {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border: none;
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    border-left: 4px solid #f59e0b;
}

.alert-survey .alert-icon {
    width: 50px;
    height: 50px;
    background: #f59e0b;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

.alert-survey h5 {
    color: #92400e;
    font-weight: 600;
}

.alert-survey p {
    color: #a16207;
}

/* Section Title */
.section-title {
    color: var(--text-primary);
    font-weight: 600;
    font-size: 1.25rem;
}

/* Action Cards */
.action-card {
    display: flex;
    align-items: center;
    background: white;
    border-radius: var(--radius-lg);
    padding: 1.25rem;
    text-decoration: none;
    color: var(--text-primary);
    border: 1px solid var(--gray-200);
    transition: all 0.3s ease;
    height: 100%;
}

.action-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-lg);
    border-color: var(--primary-color);
    color: var(--text-primary);
    text-decoration: none;
}

.action-card.action-highlight {
    border: 2px solid #f59e0b;
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
}

.action-card .action-icon {
    width: 50px;
    height: 50px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.action-card .action-content {
    flex-grow: 1;
    margin-left: 1rem;
}

.action-card .action-content h5 {
    margin: 0 0 0.25rem 0;
    font-weight: 600;
    font-size: 1rem;
}

.action-card .action-content p {
    margin: 0;
    font-size: 0.875rem;
    color: var(--text-muted);
}

.action-card .action-arrow {
    color: var(--gray-400);
    transition: transform 0.3s ease;
}

.action-card:hover .action-arrow {
    transform: translateX(5px);
    color: var(--primary-color);
}

/* Modern Cards */
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

.card-header-modern h4 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary);
}

.card-body-modern {
    padding: 1.25rem;
}

/* Announcement List */
.announcement-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.announcement-item-modern {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    background: var(--gray-50);
    border-radius: var(--radius-md);
    transition: background 0.3s ease;
}

.announcement-item-modern:hover {
    background: var(--gray-100);
}

.announcement-date {
    width: 50px;
    text-align: center;
    flex-shrink: 0;
}

.announcement-date .day {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-color);
    line-height: 1;
}

.announcement-date .month {
    display: block;
    font-size: 0.75rem;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-top: 0.25rem;
}

.announcement-content h5 {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
    font-weight: 600;
}

.announcement-content p {
    margin: 0;
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.announcement-badge {
    margin-top: 0.5rem;
}

/* Profile Summary */
.profile-summary {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.profile-summary-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--gray-100);
}

.profile-summary-item:last-child {
    border-bottom: none;
}

.profile-summary-item .label {
    font-size: 0.875rem;
    color: var(--text-muted);
}

.profile-summary-item .value {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-primary);
}

/* Help Card */
.help-card {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border-color: #bae6fd;
}

.help-card .help-icon {
    width: 60px;
    height: 60px;
    background: var(--info-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    color: white;
    font-size: 1.5rem;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .dashboard-welcome {
        padding: 1.5rem;
    }
    
    .dashboard-welcome h1 {
        font-size: 1.25rem;
    }
    
    .avatar-circle {
        width: 50px;
        height: 50px;
        font-size: 1.25rem;
    }
    
    .alert-survey {
        flex-direction: column;
        text-align: center;
    }
    
    .alert-survey .btn {
        margin-top: 1rem;
        margin-left: 0 !important;
    }
}

/* Feedback Card Styles */
.feedback-card .card-header-modern {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.anonymous-info {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    text-align: center;
    height: 100%;
}

.anonymous-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    color: white;
    font-size: 1.75rem;
}

.anonymous-info h5 {
    color: #166534;
    margin-bottom: 1rem;
}

.privacy-list {
    list-style: none;
    padding: 0;
    margin: 0;
    text-align: left;
}

.privacy-list li {
    padding: 0.5rem 0;
    font-size: 0.9rem;
    color: #15803d;
}

.feedback-type-cards {
    display: flex;
    gap: 1rem;
    margin-bottom: 0.5rem;
}

.feedback-type-card {
    flex: 1;
    cursor: pointer;
}

.feedback-type-card input {
    display: none;
}

.feedback-type-card .card-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1.25rem;
    border: 2px solid var(--gray-200);
    border-radius: var(--radius-lg);
    transition: all 0.3s ease;
}

.feedback-type-card .card-content i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    color: var(--text-muted);
}

.feedback-type-card .card-content span {
    font-weight: 500;
    color: var(--text-secondary);
}

.feedback-type-card input:checked + .card-content {
    border-color: var(--primary-color);
    background: rgba(99, 102, 241, 0.05);
}

.feedback-type-card input:checked + .card-content i,
.feedback-type-card input:checked + .card-content span {
    color: var(--primary-color);
}

.feedback-type-card:hover .card-content {
    border-color: var(--primary-light);
}

@media (max-width: 576px) {
    .feedback-type-cards {
        flex-direction: column;
    }
}
</style>

<?php include '../../core/includes/footer.php'; ?>

<?php
// Close connection
closeConnection($conn);
?>

