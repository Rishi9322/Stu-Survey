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

// Check if the user is logged in, if not then redirect to login page
if (!isLoggedIn() || !hasRole("teacher")) {
    header("location: ../../public/login.php");
    exit;
}

// Get teacher profile data
$teacherProfile = getUserProfileData($_SESSION["id"], "teacher", $conn);

// Check if the teacher has completed the survey
$surveyCompleted = isSurveyCompleted($_SESSION["id"], "teacher", $conn);

// Get teacher ratings
$sql = "SELECT 
        COUNT(tr.id) as total_ratings,
        SUM(CASE WHEN tr.rating = 'good' THEN 1 ELSE 0 END) as good_ratings,
        SUM(CASE WHEN tr.rating = 'neutral' THEN 1 ELSE 0 END) as neutral_ratings,
        SUM(CASE WHEN tr.rating = 'bad' THEN 1 ELSE 0 END) as bad_ratings
        FROM teacher_ratings tr
        WHERE tr.teacher_id = ?";

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
            $ratings['total'] = $row['total_ratings'];
            $ratings['good'] = $row['good_ratings'];
            $ratings['neutral'] = $row['neutral_ratings'];
            $ratings['bad'] = $row['bad_ratings'];
        }
    }
    
    mysqli_stmt_close($stmt);
}

// Set page variables
$pageTitle = "Teacher Dashboard";
$basePath = "../../";
?>

<?php include '../../core/includes/header.php'; ?>

<?php
// Show login success message if set
if (isset($_SESSION['login_success']) && $_SESSION['login_success']) {
    echo '<div class="alert alert-success">Login successful! Welcome to your teacher dashboard.</div>';
    unset($_SESSION['login_success']);
}
?>

<div class="dashboard">
    <div class="dashboard-header">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION["name"]); ?>!</h1>
        <p>This is your teacher dashboard where you can access surveys, view analytics, and update your profile.</p>
    </div>
    
    <div class="stats-container">
        <div class="stat-card">
            <h3><?php echo $teacherProfile['department'] ?? 'N/A'; ?></h3>
            <p>Your Department</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $surveyCompleted ? 'Completed' : 'Pending'; ?></h3>
            <p>Survey Status</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $ratings['total']; ?></h3>
            <p>Student Ratings</p>
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
            <h2>Your Ratings</h2>
        </div>
        <div class="card-body">
            <?php if ($ratings['total'] > 0): ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="chart-container">
                            <canvas id="teacher-rating-chart" 
                                data-ratings='[
                                    <?php echo $ratings['bad']; ?>,
                                    <?php echo $ratings['neutral']; ?>,
                                    <?php echo $ratings['good']; ?>
                                ]'>
                            </canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Rating</th>
                                        <th>Count</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="rating-good">Good</td>
                                        <td><?php echo $ratings['good']; ?></td>
                                        <td><?php echo round(($ratings['good'] / $ratings['total']) * 100); ?>%</td>
                                    </tr>
                                    <tr>
                                        <td class="rating-neutral">Neutral</td>
                                        <td><?php echo $ratings['neutral']; ?></td>
                                        <td><?php echo round(($ratings['neutral'] / $ratings['total']) * 100); ?>%</td>
                                    </tr>
                                    <tr>
                                        <td class="rating-bad">Bad</td>
                                        <td><?php echo $ratings['bad']; ?></td>
                                        <td><?php echo round(($ratings['bad'] / $ratings['total']) * 100); ?>%</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Total</strong></td>
                                        <td><strong><?php echo $ratings['total']; ?></strong></td>
                                        <td><strong>100%</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ratingChartElement = document.getElementById('teacher-rating-chart');
                    
                    if (ratingChartElement) {
                        const ratingLabels = ['Bad', 'Neutral', 'Good'];
                        const ratingData = JSON.parse(ratingChartElement.getAttribute('data-ratings'));
                        
                        new Chart(ratingChartElement, {
                            type: 'pie',
                            data: {
                                labels: ratingLabels,
                                datasets: [{
                                    data: ratingData,
                                    backgroundColor: ['#e74c3c', '#f39c12', '#2ecc71']
                                }]
                            },
                            options: {
                                responsive: true,
                                legend: {
                                    position: 'bottom'
                                },
                                title: {
                                    display: true,
                                    text: 'Your Rating Distribution'
                                }
                            }
                        });
                    }
                });
                </script>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> You have not received any ratings yet.
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h2>Recent Announcements</h2>
        </div>
        <div class="card-body">
            <div class="announcement-item">
                <h4>Teacher Survey Now Available</h4>
                <p class="text-muted">Posted on <?php echo date('F j, Y'); ?></p>
                <p>The latest satisfaction survey for teachers is now available. Please complete it at your earliest convenience.</p>
            </div>
            
            <div class="announcement-item">
                <h4>Faculty Development Program</h4>
                <p class="text-muted">Posted on <?php echo date('F j, Y', strtotime('-5 days')); ?></p>
                <p>A new faculty development program is scheduled for next month. Details will be shared soon.</p>
            </div>
        </div>
    </div>
</div>

<?php include '../../core/includes/footer.php'; ?>

<?php
// Close connection
closeConnection($conn);
?>

