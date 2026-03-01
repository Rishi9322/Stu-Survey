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
if (!isLoggedIn() || !hasRole("teacher")) {
    header("location: ../../public/login.php");
    exit;
}

// Get teacher profile data
$teacherProfile = getUserProfileData($_SESSION["id"], "teacher", $conn);

// Check if the teacher has completed the survey
$surveyCompleted = isSurveyCompleted($_SESSION["id"], "teacher", $conn);

// Handle anonymous feedback submission
$feedbackAlert = null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_feedback'])) {
    $feedbackSubject = trim($_POST['feedback_subject'] ?? '');
    $feedbackDescription = trim($_POST['feedback_description'] ?? '');
    $feedbackType = $_POST['feedback_type'] ?? '';
    
    if (empty($feedbackSubject) || empty($feedbackDescription) || empty($feedbackType)) {
        $feedbackAlert = ['type' => 'danger', 'message' => 'Please fill in all fields.'];
    } else {
        $result = submitAnonymousFeedback($feedbackSubject, $feedbackDescription, $feedbackType, 'teacher', $conn);
        $feedbackAlert = ['type' => $result['success'] ? 'success' : 'danger', 'message' => $result['message']];
    }
}

// Get teacher ratings (ignoring 0 ratings)
$sql = "SELECT 
        COUNT(tr.id) as total_ratings,
        SUM(CASE WHEN tr.rating >= 4 THEN 1 ELSE 0 END) as good_ratings,
        SUM(CASE WHEN tr.rating = 3 THEN 1 ELSE 0 END) as neutral_ratings,
        SUM(CASE WHEN tr.rating >= 1 AND tr.rating <= 2 THEN 1 ELSE 0 END) as bad_ratings
        FROM teacher_ratings tr
        WHERE tr.teacher_id = ? AND tr.rating >= 1";

$ratings = [
    'total' => 0,
    'good' => 0,
    'neutral' => 0,
    'bad' => 0
];

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $ratings['total'] = $row['total_ratings'] ?? 0;
            $ratings['good'] = $row['good_ratings'] ?? 0;
            $ratings['neutral'] = $row['neutral_ratings'] ?? 0;
            $ratings['bad'] = $row['bad_ratings'] ?? 0;
        }
    }
    
    mysqli_stmt_close($stmt);
}

// Calculate performance percentage
$performancePercent = $ratings['total'] > 0 ? round(($ratings['good'] / $ratings['total']) * 100) : 0;

// Set page variables
$pageTitle = "Teacher Dashboard";
$basePath = "../../";
?>

<?php include '../../core/includes/header.php'; ?>

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
                            <i class="fas fa-building me-2"></i><?php echo htmlspecialchars($teacherProfile['department'] ?? 'Department'); ?>
                            <?php if (!empty($teacherProfile['experience'])): ?>
                                <span class="mx-2">|</span>
                                <i class="fas fa-briefcase me-2"></i><?php echo htmlspecialchars($teacherProfile['experience']); ?> Years Experience
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

    <?php if (isset($_SESSION['login_success']) && $_SESSION['login_success']): ?>
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i>Login successful! Welcome to your teacher dashboard.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['login_success']); endif; ?>

    <!-- Stats Cards Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="stat-card-modern gradient-primary">
                <div class="stat-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo htmlspecialchars($teacherProfile['department'] ?? 'N/A'); ?></h3>
                    <p>Department</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card-modern gradient-secondary">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $ratings['total']; ?></h3>
                    <p>Total Ratings</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card-modern gradient-accent">
                <div class="stat-icon">
                    <i class="fas fa-thumbs-up"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $ratings['good']; ?></h3>
                    <p>Good Ratings</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card-modern <?php echo $performancePercent >= 70 ? 'gradient-success' : ($performancePercent >= 40 ? 'gradient-warning' : 'gradient-danger'); ?>">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $performancePercent; ?>%</h3>
                    <p>Performance</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Survey Alert -->
    <?php if (!$surveyCompleted): ?>
    <div class="alert alert-survey mb-4">
        <div class="d-flex align-items-center flex-wrap">
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
                    <p>Update your professional information</p>
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
                    <p>Explore your ratings and insights</p>
                </div>
                <i class="fas fa-chevron-right action-arrow"></i>
            </a>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="row g-4">
        <!-- Ratings Chart -->
        <div class="col-lg-8">
            <div class="card-modern">
                <div class="card-header-modern">
                    <h4><i class="fas fa-star me-2"></i>Your Ratings Overview</h4>
                </div>
                <div class="card-body-modern">
                    <?php if ($ratings['total'] > 0): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="chart-container-modern">
                                <canvas id="teacher-rating-chart" 
                                    data-ratings='[<?php echo $ratings['bad']; ?>, <?php echo $ratings['neutral']; ?>, <?php echo $ratings['good']; ?>]'>
                                </canvas>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="rating-breakdown">
                                <div class="rating-item">
                                    <div class="rating-label">
                                        <span class="rating-dot good"></span>
                                        Good
                                    </div>
                                    <div class="rating-bar-container">
                                        <div class="rating-bar good" style="width: <?php echo $ratings['total'] > 0 ? round(($ratings['good'] / $ratings['total']) * 100) : 0; ?>%"></div>
                                    </div>
                                    <div class="rating-value"><?php echo $ratings['good']; ?></div>
                                </div>
                                <div class="rating-item">
                                    <div class="rating-label">
                                        <span class="rating-dot neutral"></span>
                                        Neutral
                                    </div>
                                    <div class="rating-bar-container">
                                        <div class="rating-bar neutral" style="width: <?php echo $ratings['total'] > 0 ? round(($ratings['neutral'] / $ratings['total']) * 100) : 0; ?>%"></div>
                                    </div>
                                    <div class="rating-value"><?php echo $ratings['neutral']; ?></div>
                                </div>
                                <div class="rating-item">
                                    <div class="rating-label">
                                        <span class="rating-dot bad"></span>
                                        Bad
                                    </div>
                                    <div class="rating-bar-container">
                                        <div class="rating-bar bad" style="width: <?php echo $ratings['total'] > 0 ? round(($ratings['bad'] / $ratings['total']) * 100) : 0; ?>%"></div>
                                    </div>
                                    <div class="rating-value"><?php echo $ratings['bad']; ?></div>
                                </div>
                            </div>
                            <div class="rating-summary mt-4">
                                <div class="summary-item">
                                    <span class="summary-label">Total Ratings</span>
                                    <span class="summary-value"><?php echo $ratings['total']; ?></span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">Approval Rate</span>
                                    <span class="summary-value text-success"><?php echo $performancePercent; ?>%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-star"></i>
                        <h5>No Ratings Yet</h5>
                        <p>You haven't received any student ratings yet. They will appear here once students rate you.</p>
                    </div>
                    <?php endif; ?>
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
                            <span class="label">Department</span>
                            <span class="value"><?php echo htmlspecialchars($teacherProfile['department'] ?? 'Not set'); ?></span>
                        </div>
                        <div class="profile-summary-item">
                            <span class="label">Subjects</span>
                            <span class="value"><?php echo htmlspecialchars($teacherProfile['subjects'] ?? 'Not set'); ?></span>
                        </div>
                        <div class="profile-summary-item">
                            <span class="label">Experience</span>
                            <span class="value"><?php echo htmlspecialchars($teacherProfile['experience'] ?? '0'); ?> years</span>
                        </div>
                    </div>
                    <a href="profile.php" class="btn btn-outline-primary w-100 mt-3">
                        <i class="fas fa-edit me-2"></i>Edit Profile
                    </a>
                </div>
            </div>

            <!-- Announcements -->
            <div class="card-modern">
                <div class="card-header-modern">
                    <h4><i class="fas fa-bullhorn me-2"></i>Announcements</h4>
                </div>
                <div class="card-body-modern p-0">
                    <div class="announcement-list-compact">
                        <div class="announcement-item-compact">
                            <div class="announcement-icon">
                                <i class="fas fa-poll text-primary"></i>
                            </div>
                            <div class="announcement-content">
                                <h6>Teacher Survey Available</h6>
                                <p class="text-muted mb-0"><?php echo date('M d, Y'); ?></p>
                            </div>
                        </div>
                        <div class="announcement-item-compact">
                            <div class="announcement-icon">
                                <i class="fas fa-chalkboard-teacher text-success"></i>
                            </div>
                            <div class="announcement-content">
                                <h6>Faculty Development Program</h6>
                                <p class="text-muted mb-0"><?php echo date('M d, Y', strtotime('-5 days')); ?></p>
                            </div>
                        </div>
                    </div>
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
                                    <li><i class="fas fa-check text-success me-2"></i>Only role (teacher) is recorded</li>
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
    background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);
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
    border-radius: var(--radius-xl);
    padding: 1.5rem;
    color: white;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: var(--shadow-lg);
    transition: transform 0.3s ease;
}

.stat-card-modern:hover {
    transform: translateY(-5px);
}

.stat-card-modern .stat-icon {
    width: 50px;
    height: 50px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.stat-card-modern .stat-content h3 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
}

.stat-card-modern .stat-content p {
    margin: 0;
    opacity: 0.9;
    font-size: 0.875rem;
}

.gradient-primary { background: linear-gradient(135deg, #6366f1 0%, #818cf8 100%); }
.gradient-secondary { background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%); }
.gradient-accent { background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); }
.gradient-success { background: linear-gradient(135deg, #10b981 0%, #34d399 100%); }
.gradient-warning { background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); }
.gradient-danger { background: linear-gradient(135deg, #ef4444 0%, #f87171 100%); }

/* Survey Alert */
.alert-survey {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border: none;
    border-radius: var(--radius-xl);
    padding: 1.5rem;
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

.alert-survey h5 { color: #92400e; margin: 0; }
.alert-survey p { color: #a16207; }

/* Action Cards */
.action-card {
    display: flex;
    align-items: center;
    padding: 1.25rem;
    background: white;
    border-radius: var(--radius-xl);
    text-decoration: none;
    color: inherit;
    box-shadow: var(--shadow-md);
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.action-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-xl);
    border-color: var(--primary-color);
    color: inherit;
}

.action-card.action-highlight {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
}

.action-icon {
    width: 50px;
    height: 50px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.action-content {
    flex-grow: 1;
    margin-left: 1rem;
}

.action-content h5 { margin: 0 0 0.25rem 0; font-weight: 600; }
.action-content p { margin: 0; font-size: 0.875rem; color: var(--text-muted); }

.action-arrow {
    color: var(--text-muted);
    transition: transform 0.3s ease;
}

.action-card:hover .action-arrow {
    transform: translateX(5px);
    color: var(--primary-color);
}

/* Card Modern */
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
    display: flex;
    justify-content: space-between;
    align-items: center;
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

/* Chart Container */
.chart-container-modern {
    max-width: 250px;
    margin: 0 auto;
}

/* Rating Breakdown */
.rating-breakdown {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.rating-item {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.rating-label {
    width: 80px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
}

.rating-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.rating-dot.good { background: #10b981; }
.rating-dot.neutral { background: #f59e0b; }
.rating-dot.bad { background: #ef4444; }

.rating-bar-container {
    flex-grow: 1;
    height: 8px;
    background: var(--gray-200);
    border-radius: 4px;
    overflow: hidden;
}

.rating-bar {
    height: 100%;
    border-radius: 4px;
    transition: width 0.5s ease;
}

.rating-bar.good { background: #10b981; }
.rating-bar.neutral { background: #f59e0b; }
.rating-bar.bad { background: #ef4444; }

.rating-value {
    width: 40px;
    text-align: right;
    font-weight: 600;
}

.rating-summary {
    display: flex;
    gap: 2rem;
    padding-top: 1rem;
    border-top: 1px solid var(--gray-100);
}

.summary-item {
    display: flex;
    flex-direction: column;
}

.summary-label {
    font-size: 0.75rem;
    color: var(--text-muted);
}

.summary-value {
    font-size: 1.25rem;
    font-weight: 700;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem;
    color: var(--text-muted);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state h5 { color: var(--text-secondary); margin-bottom: 0.5rem; }

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

.profile-summary-item:last-child { border-bottom: none; }
.profile-summary-item .label { font-size: 0.875rem; color: var(--text-muted); }
.profile-summary-item .value { font-size: 0.875rem; font-weight: 500; color: var(--text-primary); }

/* Announcement List Compact */
.announcement-list-compact {
    display: flex;
    flex-direction: column;
}

.announcement-item-compact {
    display: flex;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--gray-100);
}

.announcement-item-compact:last-child { border-bottom: none; }

.announcement-icon {
    width: 40px;
    height: 40px;
    background: var(--gray-100);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
}

.announcement-content h6 { margin: 0 0 0.25rem 0; font-size: 0.9rem; }
.announcement-content p { font-size: 0.75rem; }

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

.anonymous-info h5 { color: #166534; margin-bottom: 1rem; }

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

.feedback-type-card input { display: none; }

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

.feedback-type-card .card-content span { font-weight: 500; color: var(--text-secondary); }

.feedback-type-card input:checked + .card-content {
    border-color: var(--primary-color);
    background: rgba(99, 102, 241, 0.05);
}

.feedback-type-card input:checked + .card-content i,
.feedback-type-card input:checked + .card-content span {
    color: var(--primary-color);
}

.feedback-type-card:hover .card-content { border-color: var(--primary-light); }

/* Section Title */
.section-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary);
}

/* Responsive */
@media (max-width: 768px) {
    .dashboard-welcome { padding: 1.5rem; }
    .dashboard-welcome h1 { font-size: 1.25rem; }
    .avatar-circle { width: 50px; height: 50px; font-size: 1.25rem; }
    .alert-survey { flex-direction: column; text-align: center; }
    .alert-survey .btn { margin-top: 1rem; margin-left: 0 !important; }
    .feedback-type-cards { flex-direction: column; }
}
</style>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ratingChartElement = document.getElementById('teacher-rating-chart');
    
    if (ratingChartElement && typeof Chart !== 'undefined') {
        const ratingLabels = ['Bad', 'Neutral', 'Good'];
        const ratingData = JSON.parse(ratingChartElement.getAttribute('data-ratings'));
        
        new Chart(ratingChartElement, {
            type: 'doughnut',
            data: {
                labels: ratingLabels,
                datasets: [{
                    data: ratingData,
                    backgroundColor: ['#ef4444', '#f59e0b', '#10b981'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 15, usePointStyle: true }
                    }
                }
            }
        });
    }
});
</script>

<?php include '../../core/includes/footer.php'; ?>

<?php
// Close connection
closeConnection($conn);
?>

