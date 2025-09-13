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

// Set page variables
$pageTitle = "Student Dashboard";
$basePath = "../../";
?>

<?php include '../../core/includes/header.php'; ?>

<?php
// Show login success message if set
if (isset($_SESSION['login_success']) && $_SESSION['login_success']) {
    echo '<div class="alert alert-success">Login successful! Welcome to your student dashboard.</div>';
    unset($_SESSION['login_success']);
}
?>

<div class="dashboard">
    <div class="dashboard-header">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION["name"]); ?>!</h1>
        <p>This is your student dashboard where you can access surveys, view analytics, and update your profile.</p>
    </div>
    
    <div class="stats-container">
        <div class="stat-card">
            <h3><?php echo $studentProfile['course'] ?? 'N/A'; ?></h3>
            <p>Your Course</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $studentProfile['division'] ?? 'N/A'; ?></h3>
            <p>Your Division</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $surveyCompleted ? 'Completed' : 'Pending'; ?></h3>
            <p>Survey Status</p>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2>Quick Actions</h2>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <a href="profile.php" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-user"></i> View/Edit Profile
                    </a>
                </div>
                <div class="col">
                    <a href="survey.php" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-poll"></i> 
                        <?php echo $surveyCompleted ? 'View Submitted Survey' : 'Take Survey'; ?>
                    </a>
                </div>
                <div class="col">
                    <a href="analytics.php" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-chart-bar"></i> View Analytics
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!$surveyCompleted): ?>
    <div class="alert alert-info mt-4">
        <i class="fas fa-info-circle"></i> You haven't completed the satisfaction survey yet. Your feedback is valuable to us!
        <a href="survey.php" class="btn btn-sm btn-info ml-2">Take Survey Now</a>
    </div>
    <?php endif; ?>
    
    <div class="card mt-4">
        <div class="card-header">
            <h2>Recent Announcements</h2>
        </div>
        <div class="card-body">
            <div class="announcement-item">
                <h4>New Survey Available</h4>
                <p class="text-muted">Posted on <?php echo date('F j, Y'); ?></p>
                <p>The latest satisfaction survey is now available. Please complete it at your earliest convenience.</p>
            </div>
            
            <div class="announcement-item">
                <h4>Improvements Based on Your Feedback</h4>
                <p class="text-muted">Posted on <?php echo date('F j, Y', strtotime('-3 days')); ?></p>
                <p>We've made several improvements to our facilities based on the feedback received in the previous survey. Thank you for your valuable input!</p>
            </div>
        </div>
    </div>
</div>

<?php include '../../core/includes/footer.php'; ?>

<?php
// Close connection
closeConnection($conn);
?>

