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

// Get survey statistics
$stats = getSurveyStatistics($conn);

// Get teacher's data
$teacherId = $_SESSION["id"];
$teacherProfile = getUserProfileData($teacherId, "teacher", $conn);

// Get student completion data for teacher's department
$sql = "SELECT COUNT(DISTINCT sr.user_id) as completed_count
        FROM survey_responses sr
        JOIN users u ON sr.user_id = u.id
        JOIN student_profiles sp ON u.id = sp.user_id
        WHERE u.role = 'student' AND sp.course = ?";

$studentsCompleted = 0;

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $teacherProfile['department']);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $studentsCompleted = $row['completed_count'];
        }
    }
    
    mysqli_stmt_close($stmt);
}

// Get total students in teacher's department
$sql = "SELECT COUNT(*) as total_count
        FROM users u
        JOIN student_profiles sp ON u.id = sp.user_id
        WHERE u.role = 'student' AND sp.course = ?";

$totalStudents = 0;

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $teacherProfile['department']);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $totalStudents = $row['total_count'];
        }
    }
    
    mysqli_stmt_close($stmt);
}

// Get teacher's ratings
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
    mysqli_stmt_bind_param($stmt, "i", $teacherId);
    
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
$pageTitle = "Teacher Analytics";
$basePath = "../../";
?>

<?php include '../../core/includes/header.php'; ?>

<div class="analytics-dashboard">
    <h2>Teacher Analytics</h2>
    <p>View insights from the student and teacher satisfaction survey data.</p>
    
    <div class="stats-container">
        <div class="stat-card">
            <h3><?php echo $studentsCompleted; ?></h3>
            <p>Students Completed Survey</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $totalStudents; ?></h3>
            <p>Total Students</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $totalStudents > 0 ? round(($studentsCompleted / $totalStudents) * 100) : 0; ?>%</h3>
            <p>Completion Rate</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $ratings['total']; ?></h3>
            <p>Your Total Ratings</p>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="chart-container">
                <h3>Your Rating Distribution</h3>
                <?php if ($ratings['total'] > 0): ?>
                    <canvas id="teacher-rating-chart" 
                        data-ratings='[
                            <?php echo $ratings['bad']; ?>,
                            <?php echo $ratings['neutral']; ?>,
                            <?php echo $ratings['good']; ?>
                        ]'>
                    </canvas>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> You have not received any ratings yet.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="chart-container">
                <h3>Survey Completion Status</h3>
                <canvas id="survey-completion-chart"
                    data-completion='{
                        "students_completed": <?php echo $studentsCompleted; ?>,
                        "total_students": <?php echo $totalStudents; ?>,
                        "teachers_completed": <?php echo $stats['teachers_completed']; ?>,
                        "total_teachers": <?php echo $stats['total_teachers']; ?>
                    }'>
                </canvas>
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
                    SUM(CASE WHEN tr.rating = 'good' THEN 1 ELSE 0 END) as good_ratings,
                    SUM(CASE WHEN tr.rating = 'neutral' THEN 1 ELSE 0 END) as neutral_ratings,
                    SUM(CASE WHEN tr.rating = 'bad' THEN 1 ELSE 0 END) as bad_ratings
                    FROM teacher_profiles tp
                    JOIN users u ON tp.user_id = u.id
                    LEFT JOIN teacher_ratings tr ON u.id = tr.teacher_id
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
                            <tr <?php echo ($department['department'] === $teacherProfile['department']) ? 'class="table-primary"' : ''; ?>>
                                <td class="leaderboard-position <?php echo ($index < 3) ? 'top-position' : ''; ?>"><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($department['department']); ?></td>
                                <td><?php echo $department['students_rated']; ?></td>
                                <td class="rating-good"><?php echo $department['good_ratings']; ?></td>
                                <td class="rating-neutral"><?php echo $department['neutral_ratings']; ?></td>
                                <td class="rating-bad"><?php echo $department['bad_ratings']; ?></td>
                                <td><?php echo $performanceScore; ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($departmentData)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No department data available.</td>
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
            <h3>Teacher Performance in Your Department</h3>
        </div>
        <div class="card-body">
            <?php
            // Get teacher performance data in the same department
            $sql = "SELECT u.username as name, 
                    COUNT(tr.id) as total_ratings,
                    SUM(CASE WHEN tr.rating = 'good' THEN 1 ELSE 0 END) as good_ratings,
                    SUM(CASE WHEN tr.rating = 'neutral' THEN 1 ELSE 0 END) as neutral_ratings,
                    SUM(CASE WHEN tr.rating = 'bad' THEN 1 ELSE 0 END) as bad_ratings
                    FROM users u
                    JOIN teacher_profiles tp ON u.id = tp.user_id
                    LEFT JOIN teacher_ratings tr ON u.id = tr.teacher_id
                    WHERE tp.department = ? AND u.role = 'teacher'
                    GROUP BY u.id
                    ORDER BY good_ratings DESC, neutral_ratings DESC";
            
            $teacherData = [];
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "s", $teacherProfile['department']);
                
                if (mysqli_stmt_execute($stmt)) {
                    $result = mysqli_stmt_get_result($stmt);
                    
                    while ($row = mysqli_fetch_assoc($result)) {
                        $teacherData[] = $row;
                    }
                }
                
                mysqli_stmt_close($stmt);
            }
            ?>
            
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="teacher-performance">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Teacher Name</th>
                            <th>Total Ratings</th>
                            <th>Good Ratings</th>
                            <th>Neutral Ratings</th>
                            <th>Bad Ratings</th>
                            <th>Performance Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teacherData as $index => $teacher): ?>
                            <?php 
                            $totalRatings = $teacher['total_ratings'];
                            $performanceScore = $totalRatings > 0 ? 
                                round((($teacher['good_ratings'] * 3 + $teacher['neutral_ratings'] * 2 + $teacher['bad_ratings'] * 1) / ($totalRatings * 3)) * 100) : 0;
                            
                            $isCurrentTeacher = ($teacher['name'] === $_SESSION["name"]);
                            ?>
                            <tr <?php echo $isCurrentTeacher ? 'class="table-primary"' : ''; ?>>
                                <td class="leaderboard-position <?php echo ($index < 3) ? 'top-position' : ''; ?>"><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($teacher['name']); ?> <?php echo $isCurrentTeacher ? '(You)' : ''; ?></td>
                                <td><?php echo $teacher['total_ratings']; ?></td>
                                <td class="rating-good"><?php echo $teacher['good_ratings']; ?></td>
                                <td class="rating-neutral"><?php echo $teacher['neutral_ratings']; ?></td>
                                <td class="rating-bad"><?php echo $teacher['bad_ratings']; ?></td>
                                <td><?php echo $performanceScore; ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($teacherData)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No teacher data available in your department.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                <button class="btn btn-secondary" onclick="exportTableToCSV('teacher-performance', 'teacher_performance.csv')">
                    <i class="fas fa-download"></i> Export to CSV
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../../core/includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Teacher Rating Chart
    const teacherRatingChartElement = document.getElementById('teacher-rating-chart');
    
    if (teacherRatingChartElement) {
        const ratingLabels = ['Bad', 'Neutral', 'Good'];
        const ratingData = JSON.parse(teacherRatingChartElement.getAttribute('data-ratings'));
        
        new Chart(teacherRatingChartElement, {
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

<?php
// Close connection
closeConnection($conn);
?>

