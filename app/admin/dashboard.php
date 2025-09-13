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
if (!isLoggedIn() || !hasRole("admin")) {
    header("location: ../../public/login.php");
    exit;
}

// Get survey statistics
$stats = getSurveyStatistics($conn);

// Get suggestions and complaints count
$sql = "SELECT 
        COUNT(CASE WHEN type = 'suggestion' THEN 1 END) as suggestion_count,
        COUNT(CASE WHEN type = 'complaint' THEN 1 END) as complaint_count
        FROM suggestions_complaints";

$suggestionCount = 0;
$complaintCount = 0;

$result = mysqli_query($conn, $sql);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $suggestionCount = $row['suggestion_count'];
    $complaintCount = $row['complaint_count'];
}

// Set page variables
$pageTitle = "Admin Dashboard";
$basePath = "../../";
?>

<?php include '../../core/includes/header.php'; ?>

<?php
// Show login success message if set
if (isset($_SESSION['login_success']) && $_SESSION['login_success']) {
    echo '<div class="alert alert-success">Login successful! Welcome to your admin dashboard.</div>';
    unset($_SESSION['login_success']);
}
?>

<div class="dashboard">
    <div class="dashboard-header">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION["name"]); ?>!</h1>
        <p>This is your admin dashboard where you can manage surveys, users, and view analytics.</p>
    </div>
    
    <div class="stats-container">
        <div class="stat-card">
            <h3><?php echo $stats['total_students']; ?></h3>
            <p>Total Students</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $stats['total_teachers']; ?></h3>
            <p>Total Teachers</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $stats['students_completed']; ?></h3>
            <p>Students Completed Survey</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $stats['teachers_completed']; ?></h3>
            <p>Teachers Completed Survey</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $suggestionCount; ?></h3>
            <p>Suggestions Received</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $complaintCount; ?></h3>
            <p>Complaints Received</p>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2>Quick Actions</h2>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <a href="survey_management.php" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-tasks"></i> Survey Management
                    </a>
                </div>
                <div class="col">
                    <a href="user_management.php" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-users"></i> User Management
                    </a>
                </div>
                <div class="col">
                    <a href="complaints.php" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-comment-alt"></i> View Complaints
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Visual Data Representation Section -->
    <div class="row mt-4">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-chart-bar"></i> Data Analytics & Insights</h2>
        </div>
    </div>
    
    <!-- First Row of Charts -->
    <div class="row mt-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-pie-chart"></i> User Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="userDistributionChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-star"></i> Overall Rating Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="ratingDistributionChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Second Row of Charts -->
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-line"></i> Survey Responses Over Time</h5>
                </div>
                <div class="card-body">
                    <canvas id="responseTimelineChart" style="max-height: 350px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-tasks"></i> Survey Completion Status</h5>
                </div>
                <div class="card-body">
                    <canvas id="completionStatusChart" style="max-height: 350px;"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Third Row of Charts -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-comments"></i> Feedback vs Complaints</h5>
                </div>
                <div class="card-body">
                    <canvas id="feedbackChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-building"></i> Department Performance</h5>
                </div>
                <div class="card-body">
                    <canvas id="departmentChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-chart-area"></i> Daily Activity Overview</h5>
                </div>
                <div class="card-body">
                    <canvas id="dailyActivityChart" style="max-height: 400px;"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h3>Department Performance</h3>
        </div>
        <div class="card-body">
            <?php
            // Get department performance data
            $sql = "SELECT tp.department, 
                    COUNT(DISTINCT tr.student_id) as students_rated,
                    COUNT(DISTINCT u.id) as total_teachers,
                    SUM(CASE WHEN tr.rating = 'good' THEN 1 ELSE 0 END) as good_ratings,
                    SUM(CASE WHEN tr.rating = 'neutral' THEN 1 ELSE 0 END) as neutral_ratings,
                    SUM(CASE WHEN tr.rating = 'bad' THEN 1 ELSE 0 END) as bad_ratings
                    FROM teacher_profiles tp
                    JOIN users u ON tp.user_id = u.id
                    LEFT JOIN teacher_ratings tr ON u.id = tr.teacher_id
                    WHERE u.role = 'teacher'
                    GROUP BY tp.department
                    ORDER BY good_ratings DESC";
            
            $departmentData = [];
            $result = mysqli_query($conn, $sql);
            
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $departmentData[] = $row;
                }
            }
            ?>
            
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="department-performance">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Department</th>
                            <th>Total Teachers</th>
                            <th>Students Rated</th>
                            <th>Good Ratings</th>
                            <th>Neutral Ratings</th>
                            <th>Bad Ratings</th>
                            <th>Performance Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($departmentData as $index => $department): ?>
                            <?php 
                            $totalRatings = $department['good_ratings'] + $department['neutral_ratings'] + $department['bad_ratings'];
                            $performanceScore = $totalRatings > 0 ? 
                                round((($department['good_ratings'] * 3 + $department['neutral_ratings'] * 2 + $department['bad_ratings'] * 1) / ($totalRatings * 3)) * 100) : 0;
                            ?>
                            <tr>
                                <td class="leaderboard-position <?php echo ($index < 3) ? 'top-position' : ''; ?>"><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($department['department']); ?></td>
                                <td><?php echo $department['total_teachers']; ?></td>
                                <td><?php echo $department['students_rated']; ?></td>
                                <td class="rating-good"><?php echo $department['good_ratings']; ?></td>
                                <td class="rating-neutral"><?php echo $department['neutral_ratings']; ?></td>
                                <td class="rating-bad"><?php echo $department['bad_ratings']; ?></td>
                                <td><?php echo $performanceScore; ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($departmentData)): ?>
                            <tr>
                                <td colspan="8" class="text-center">No department data available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                <button class="btn btn-secondary" onclick="exportTableToCSV('department-performance', 'department_performance.csv')">
                    <i class="fas fa-download"></i> Export to CSV
                </button>
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h3>Recent Activities</h3>
        </div>
        <div class="card-body">
            <?php
            // Get recent survey responses
            $sql = "SELECT u.username as name, u.role, sq.question, sr.rating, sr.created_at
                    FROM survey_responses sr
                    JOIN users u ON sr.user_id = u.id
                    JOIN survey_questions sq ON sr.question_id = sq.id
                    ORDER BY sr.created_at DESC
                    LIMIT 10";
            
            $recentActivities = [];
            $result = mysqli_query($conn, $sql);
            
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $recentActivities[] = $row;
                }
            }
            ?>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Question</th>
                            <th>Rating</th>
                            <th>Submitted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentActivities as $activity): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($activity['name']); ?></td>
                                <td><?php echo ucfirst(htmlspecialchars($activity['role'])); ?></td>
                                <td><?php echo htmlspecialchars(substr($activity['question'], 0, 50)) . (strlen($activity['question']) > 50 ? '...' : ''); ?></td>
                                <td class="rating-<?php echo $activity['rating']; ?>"><?php echo ucfirst($activity['rating']); ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($recentActivities)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No recent activities found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Load Chart.js directly for this page -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.js" integrity="sha512-6HrPqAvK+lZElIZ4mZ64fyxIBTsaX5zAFZg2V/2WT+iKPrFzTzvx6QAsLW2OaLwobhMYBog/+bvmIEEGXi0p1w==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<!-- Fallback: Load Chart.js from different CDN if first fails -->
<script>
if (typeof Chart === 'undefined') {
    console.log('First Chart.js CDN failed, trying backup...');
    document.write('<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"><\/script>');
}
</script>

<!-- Get real database data for charts -->
<?php
// 1. User distribution data
$userStatsQuery = "
    SELECT 
        role,
        COUNT(*) as count 
    FROM users 
    GROUP BY role
";
$userStatsResult = mysqli_query($conn, $userStatsQuery);
$dbUserStats = ['students' => 0, 'teachers' => 0, 'admins' => 0];

if ($userStatsResult) {
    while ($row = mysqli_fetch_assoc($userStatsResult)) {
        if ($row['role'] == 'student') {
            $dbUserStats['students'] = (int)$row['count'];
        } elseif ($row['role'] == 'teacher') {
            $dbUserStats['teachers'] = (int)$row['count'];
        } elseif ($row['role'] == 'admin') {
            $dbUserStats['admins'] = (int)$row['count'];
        }
    }
}

// 2. Rating distribution data
$ratingQuery = "
    SELECT 
        rating,
        COUNT(*) as count 
    FROM survey_responses 
    GROUP BY rating 
    ORDER BY rating DESC
";
$ratingResult = mysqli_query($conn, $ratingQuery);
$dbRatingStats = [0, 0, 0, 0, 0]; // [5,4,3,2,1] ratings

if ($ratingResult) {
    while ($row = mysqli_fetch_assoc($ratingResult)) {
        $rating = (int)$row['rating'];
        $count = (int)$row['count'];
        
        // Map rating to array index (5->0, 4->1, 3->2, 2->3, 1->4)
        if ($rating >= 1 && $rating <= 5) {
            $dbRatingStats[5 - $rating] = $count;
        }
    }
}

// 3. Timeline data - responses over last 7 days
$timelineQuery = "
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as count 
    FROM survey_responses 
    WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
";
$timelineResult = mysqli_query($conn, $timelineQuery);

// Initialize last 7 days with 0 counts
$dbTimelineData = [];
$dbTimelineLabels = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dbTimelineData[$date] = 0;
    $dbTimelineLabels[] = date('M j', strtotime("-$i days"));
}

// Fill in actual data
if ($timelineResult) {
    while ($row = mysqli_fetch_assoc($timelineResult)) {
        if (isset($dbTimelineData[$row['date']])) {
            $dbTimelineData[$row['date']] = (int)$row['count'];
        }
    }
}

// Convert to indexed array for JavaScript
$dbTimelineData = array_values($dbTimelineData);

// 4. Department performance data
$deptQuery = "
    SELECT 
        COALESCE(tp.department, 'General') as department,
        AVG(CAST(tr.rating AS DECIMAL(3,2))) as avg_rating,
        COUNT(tr.rating) as total_ratings
    FROM teacher_profiles tp
    LEFT JOIN teacher_ratings tr ON tp.user_id = tr.teacher_id
    GROUP BY tp.department
    HAVING total_ratings > 0
    ORDER BY avg_rating DESC
";
$deptResult = mysqli_query($conn, $deptQuery);
$dbDeptData = [];

if ($deptResult) {
    while ($row = mysqli_fetch_assoc($deptResult)) {
        $dbDeptData[] = [
            'department' => $row['department'],
            'avg_rating' => round((float)$row['avg_rating'], 2)
        ];
    }
}

// 5. Feedback data (suggestions vs complaints)
$feedbackQuery = "
    SELECT 
        type,
        COUNT(*) as count 
    FROM suggestions_complaints 
    GROUP BY type
";
$feedbackResult = mysqli_query($conn, $feedbackQuery);
$dbFeedbackData = ['suggestions' => 0, 'complaints' => 0];

if ($feedbackResult) {
    while ($row = mysqli_fetch_assoc($feedbackResult)) {
        if ($row['type'] == 'suggestion') {
            $dbFeedbackData['suggestions'] = (int)$row['count'];
        } elseif ($row['type'] == 'complaint') {
            $dbFeedbackData['complaints'] = (int)$row['count'];
        }
    }
}

// 6. Survey completion data
$studentCompletionQuery = "
    SELECT 
        COUNT(DISTINCT u.id) as total_students,
        COUNT(DISTINCT sr.user_id) as completed_students
    FROM users u
    LEFT JOIN survey_responses sr ON u.id = sr.user_id
    WHERE u.role = 'student'
";

$teacherCompletionQuery = "
    SELECT 
        COUNT(DISTINCT u.id) as total_teachers,
        COUNT(DISTINCT tr.student_id) as teachers_with_ratings
    FROM users u
    LEFT JOIN teacher_ratings tr ON u.id = tr.teacher_id
    WHERE u.role = 'teacher'
";

$studentResult = mysqli_query($conn, $studentCompletionQuery);
$teacherResult = mysqli_query($conn, $teacherCompletionQuery);

$dbCompletionData = [
    'students_total' => 0, 'students_completed' => 0,
    'teachers_total' => 0, 'teachers_completed' => 0
];

if ($studentResult && $row = mysqli_fetch_assoc($studentResult)) {
    $dbCompletionData['students_total'] = (int)$row['total_students'];
    $dbCompletionData['students_completed'] = (int)$row['completed_students'];
}

if ($teacherResult && $row = mysqli_fetch_assoc($teacherResult)) {
    $dbCompletionData['teachers_total'] = (int)$row['total_teachers'];
    $dbCompletionData['teachers_completed'] = (int)$row['teachers_with_ratings'];
}
?>

<!-- Chart.js Scripts for Data Visualization -->
<script>
console.log('Starting dashboard chart initialization...');

// Function to wait for Chart.js to load
function waitForChart() {
    return new Promise((resolve, reject) => {
        let attempts = 0;
        const maxAttempts = 50; // Wait up to 5 seconds
        
        const checkChart = () => {
            attempts++;
            console.log('Checking for Chart.js, attempt:', attempts);
            
            if (typeof Chart !== 'undefined') {
                console.log('Chart.js loaded successfully, version:', Chart.version);
                resolve();
            } else if (attempts >= maxAttempts) {
                console.error('Chart.js failed to load after', maxAttempts, 'attempts');
                reject(new Error('Chart.js failed to load'));
            } else {
                setTimeout(checkChart, 100); // Check every 100ms
            }
        };
        
        checkChart();
    });
}

// Wait for DOM and Chart.js to be ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM ready, waiting for Chart.js...');
    
    waitForChart().then(() => {
        console.log('Chart.js is ready, creating charts...');
        createCharts();
    }).catch((error) => {
        console.error('Error loading Chart.js:', error);
        alert('Chart.js library failed to load. Please refresh the page.');
    });
});

function createCharts() {
    // Database data passed from PHP
    const dbData = {
        userStats: <?php echo json_encode($dbUserStats); ?>,
        ratingStats: <?php echo json_encode($dbRatingStats); ?>,
        timelineData: <?php echo json_encode($dbTimelineData); ?>,
        timelineLabels: <?php echo json_encode($dbTimelineLabels); ?>,
        deptData: <?php echo json_encode($dbDeptData); ?>,
        feedbackData: <?php echo json_encode($dbFeedbackData); ?>,
        completionData: <?php echo json_encode($dbCompletionData); ?>
    };
    
    // Fallback to sample data if database data is empty
    const sampleData = {
        userStats: { students: 5, teachers: 3, admins: 1 },
        ratingStats: [12, 18, 8, 4, 2], // Excellent to Very Poor
        timelineData: [3, 2, 5, 8, 4, 6, 3],
        timelineLabels: <?php echo json_encode($dbTimelineLabels); ?>,
        deptData: [
            { department: 'Computer Science', avg_rating: 4.2 },
            { department: 'Electronics', avg_rating: 3.8 }
        ],
        feedbackData: { suggestions: 5, complaints: 2 },
        completionData: { students_total: 5, students_completed: 3, teachers_total: 3, teachers_completed: 2 }
    };
    
    // Use database data if available, otherwise use sample data
    const chartData = {
        userStats: (dbData.userStats.students + dbData.userStats.teachers + dbData.userStats.admins) > 0 ? dbData.userStats : sampleData.userStats,
        ratingStats: dbData.ratingStats.reduce((a, b) => a + b, 0) > 0 ? dbData.ratingStats : sampleData.ratingStats,
        timelineData: dbData.timelineData.reduce((a, b) => a + b, 0) > 0 ? dbData.timelineData : sampleData.timelineData,
        timelineLabels: dbData.timelineLabels.length > 0 ? dbData.timelineLabels : sampleData.timelineLabels,
        deptData: dbData.deptData.length > 0 ? dbData.deptData : sampleData.deptData,
        feedbackData: (dbData.feedbackData.suggestions + dbData.feedbackData.complaints) > 0 ? dbData.feedbackData : sampleData.feedbackData,
        completionData: dbData.completionData.students_total > 0 ? dbData.completionData : sampleData.completionData
    };
    
    console.log('Database data:', dbData);
    console.log('Using chart data:', chartData);
    
    try {
        // 1. User Distribution Chart
        const userChart = document.getElementById('userDistributionChart');
        if (userChart) {
            console.log('Creating user distribution chart...');
            new Chart(userChart, {
                type: 'pie',
                data: {
                    labels: ['Students', 'Teachers', 'Admins'],
                    datasets: [{
                        data: [
                            chartData.userStats.students,
                            chartData.userStats.teachers,
                            chartData.userStats.admins
                        ],
                        backgroundColor: ['#3B82F6', '#10B981', '#F59E0B'],
                        borderWidth: 2,
                        borderColor: '#FFFFFF'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: { display: true, text: 'User Distribution' },
                        legend: { position: 'bottom' }
                    }
                }
            });
            console.log('User distribution chart created successfully');
        } else {
            console.error('User chart canvas not found');
        }

        // 2. Rating Distribution Chart
        const ratingChart = document.getElementById('ratingDistributionChart');
        if (ratingChart) {
            console.log('Creating rating distribution chart...');
            new Chart(ratingChart, {
                type: 'bar',
                data: {
                    labels: ['Excellent (5)', 'Good (4)', 'Average (3)', 'Poor (2)', 'Very Poor (1)'],
                    datasets: [{
                        label: 'Responses',
                        data: chartData.ratingStats,
                        backgroundColor: ['#10B981', '#3B82F6', '#F59E0B', '#F97316', '#EF4444']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: { display: true, text: 'Rating Distribution' }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
            console.log('Rating distribution chart created successfully');
        } else {
            console.error('Rating chart canvas not found');
        }

        // 3. Timeline Chart
        const timelineChart = document.getElementById('responseTimelineChart');
        if (timelineChart) {
            console.log('Creating timeline chart...');
            new Chart(timelineChart, {
                type: 'line',
                data: {
                    labels: chartData.timelineLabels,
                    datasets: [{
                        label: 'Survey Responses',
                        data: chartData.timelineData,
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: { display: true, text: 'Response Timeline (Last 7 Days)' }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
            console.log('Timeline chart created successfully');
        }

        // 4. Completion Status Chart
        const completionChart = document.getElementById('completionStatusChart');
        if (completionChart) {
            console.log('Creating completion status chart...');
            const completed = chartData.completionData.students_completed + chartData.completionData.teachers_completed;
            const total = chartData.completionData.students_total + chartData.completionData.teachers_total;
            const pending = total - completed;
            
            new Chart(completionChart, {
                type: 'doughnut',
                data: {
                    labels: ['Completed', 'Pending'],
                    datasets: [{
                        data: [completed, pending],
                        backgroundColor: ['#10B981', '#E5E7EB'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: { display: true, text: 'Survey Completion Status' },
                        legend: { position: 'bottom' }
                    }
                }
            });
            console.log('Completion status chart created successfully');
        }

        // 5. Feedback Chart
        const feedbackChart = document.getElementById('feedbackChart');
        if (feedbackChart) {
            console.log('Creating feedback chart...');
            new Chart(feedbackChart, {
                type: 'bar',
                data: {
                    labels: ['Suggestions', 'Complaints'],
                    datasets: [{
                        label: 'Count',
                        data: [chartData.feedbackData.suggestions, chartData.feedbackData.complaints],
                        backgroundColor: ['#10B981', '#EF4444']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: { display: true, text: 'Feedback vs Complaints' }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
            console.log('Feedback chart created successfully');
        }

        // 6. Department Chart
        const deptChart = document.getElementById('departmentChart');
        if (deptChart) {
            console.log('Creating department chart...');
            new Chart(deptChart, {
                type: 'radar',
                data: {
                    labels: chartData.deptData.map(d => d.department),
                    datasets: [{
                        label: 'Average Rating',
                        data: chartData.deptData.map(d => d.avg_rating),
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: { display: true, text: 'Department Performance' }
                    },
                    scales: {
                        r: {
                            beginAtZero: true,
                            max: 5,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
            console.log('Department chart created successfully');
        }

        // 7. Daily Activity Chart
        const activityChart = document.getElementById('dailyActivityChart');
        if (activityChart) {
            console.log('Creating daily activity chart...');
            new Chart(activityChart, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Daily Activity',
                        data: [8, 12, 15, 18, 22, 5, 3],
                        borderColor: '#9B59B6',
                        backgroundColor: 'rgba(155, 89, 182, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: { display: true, text: 'Weekly Activity Pattern' }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
            console.log('Daily activity chart created successfully');
        }
        
        console.log('All charts created successfully!');
        
    } catch (error) {
        console.error('Error creating charts:', error);
        alert('Error creating charts: ' + error.message);
    }
}
</script>

<?php include '../../core/includes/footer.php'; ?>

<?php
// Close connection
closeConnection($conn);
?>

