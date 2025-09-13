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

// Get survey statistics
$stats = getSurveyStatistics($conn);

// Get teacher ratings for leaderboard
$sql = "SELECT u.username as name, tp.department, 
        COUNT(tr.id) as total_ratings,
        SUM(CASE WHEN tr.rating = 'good' THEN 1 ELSE 0 END) as good_ratings,
        SUM(CASE WHEN tr.rating = 'neutral' THEN 1 ELSE 0 END) as neutral_ratings,
        SUM(CASE WHEN tr.rating = 'bad' THEN 1 ELSE 0 END) as bad_ratings
        FROM users u 
        JOIN teacher_profiles tp ON u.id = tp.user_id 
        LEFT JOIN teacher_ratings tr ON u.id = tr.teacher_id 
        WHERE u.role = 'teacher'
        GROUP BY u.id
        ORDER BY good_ratings DESC, neutral_ratings DESC";

$teacherRatings = [];
$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $teacherRatings[] = $row;
    }
}

// Set page variables
$pageTitle = "Student Analytics";
$basePath = "../../";
?>

<?php include '../../core/includes/header.php'; ?>

<div class="analytics-dashboard">
    <h2>Survey Analytics</h2>
    <p>View insights from the college satisfaction survey data.</p>
    
    <div class="stats-container">
        <div class="stat-card">
            <h3><?php echo $stats['students_completed']; ?></h3>
            <p>Students Completed</p>
        </div>
        <div class="stat-card">
            <h3><?php echo $stats['total_students']; ?></h3>
            <p>Total Students</p>
        </div>
        <div class="stat-card">
            <h3><?php echo round(($stats['students_completed'] / max(1, $stats['total_students'])) * 100); ?>%</h3>
            <p>Completion Rate</p>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="chart-container">
                <h3>Rating Distribution</h3>
                <canvas id="rating-distribution-chart" 
                    data-ratings='[
                        <?php echo $stats['ratings_distribution']['bad']; ?>,
                        <?php echo $stats['ratings_distribution']['neutral']; ?>,
                        <?php echo $stats['ratings_distribution']['good']; ?>
                    ]'>
                </canvas>
            </div>
        </div>
        <div class="col-md-6">
            <div class="chart-container">
                <h3>Survey Completion Status</h3>
                <canvas id="survey-completion-chart"
                    data-completion='{
                        "students_completed": <?php echo $stats['students_completed']; ?>,
                        "total_students": <?php echo $stats['total_students']; ?>,
                        "teachers_completed": <?php echo $stats['teachers_completed']; ?>,
                        "total_teachers": <?php echo $stats['total_teachers']; ?>
                    }'>
                </canvas>
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h3>Teacher Ratings Leaderboard</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-sortable" id="teacher-leaderboard">
                    <thead>
                        <tr>
                            <th class="sortable">Rank</th>
                            <th class="sortable">Teacher Name</th>
                            <th class="sortable">Department</th>
                            <th class="sortable">Good Ratings</th>
                            <th class="sortable">Neutral Ratings</th>
                            <th class="sortable">Bad Ratings</th>
                            <th class="sortable">Total Ratings</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teacherRatings as $index => $teacher): ?>
                            <tr>
                                <td class="leaderboard-position <?php echo ($index < 3) ? 'top-position' : ''; ?>"><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($teacher['name']); ?></td>
                                <td><?php echo htmlspecialchars($teacher['department']); ?></td>
                                <td class="rating-good"><?php echo $teacher['good_ratings']; ?></td>
                                <td class="rating-neutral"><?php echo $teacher['neutral_ratings']; ?></td>
                                <td class="rating-bad"><?php echo $teacher['bad_ratings']; ?></td>
                                <td><?php echo $teacher['total_ratings']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($teacherRatings)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No teacher ratings available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                <button class="btn btn-secondary" onclick="exportTableToCSV('teacher-leaderboard', 'teacher_ratings.csv')">
                    <i class="fas fa-download"></i> Export to CSV
                </button>
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h3>Division Performance</h3>
        </div>
        <div class="card-body">
            <?php
            // Get division performance data
            $sql = "SELECT sp.division, 
                    COUNT(DISTINCT sr.user_id) as students_completed,
                    (SELECT COUNT(*) FROM student_profiles WHERE division = sp.division) as total_students,
                    SUM(CASE WHEN sr.rating = 'good' THEN 1 ELSE 0 END) as good_ratings,
                    SUM(CASE WHEN sr.rating = 'neutral' THEN 1 ELSE 0 END) as neutral_ratings,
                    SUM(CASE WHEN sr.rating = 'bad' THEN 1 ELSE 0 END) as bad_ratings
                    FROM student_profiles sp
                    JOIN users u ON sp.user_id = u.id
                    LEFT JOIN survey_responses sr ON u.id = sr.user_id
                    GROUP BY sp.division
                    ORDER BY good_ratings DESC";
            
            $divisionData = [];
            $result = mysqli_query($conn, $sql);
            
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $divisionData[] = $row;
                }
            }
            ?>
            
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="division-performance">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Division</th>
                            <th>Completion Rate</th>
                            <th>Good Ratings</th>
                            <th>Neutral Ratings</th>
                            <th>Bad Ratings</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($divisionData as $index => $division): ?>
                            <tr>
                                <td class="leaderboard-position <?php echo ($index < 3) ? 'top-position' : ''; ?>"><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($division['division']); ?></td>
                                <td><?php echo round(($division['students_completed'] / max(1, $division['total_students'])) * 100); ?>%</td>
                                <td class="rating-good"><?php echo $division['good_ratings']; ?></td>
                                <td class="rating-neutral"><?php echo $division['neutral_ratings']; ?></td>
                                <td class="rating-bad"><?php echo $division['bad_ratings']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($divisionData)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No division data available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                <button class="btn btn-secondary" onclick="exportTableToCSV('division-performance', 'division_performance.csv')">
                    <i class="fas fa-download"></i> Export to CSV
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../../core/includes/footer.php'; ?>

<?php
// Close connection
closeConnection($conn);
?>

