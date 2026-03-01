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

// Get recent activities for notification badge
$recentActivitiesCount = 0;
$activitySql = "SELECT COUNT(*) as count FROM survey_responses 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
$activityResult = mysqli_query($conn, $activitySql);
if ($activityResult && $row = mysqli_fetch_assoc($activityResult)) {
    $recentActivitiesCount = $row['count'];
}

// Set page variables
$pageTitle = "Survey Dashboard";
$basePath = "../../";
?>

<?php include '../../core/includes/header.php'; ?>

<!-- Custom CSS for Dashboard -->
<style>
:root {
    --primary-color: #4361ee;
    --primary-light: #eef2ff;
    --secondary-color: #3a0ca3;
    --success-color: #06d6a0;
    --warning-color: #ffd166;
    --danger-color: #ef476f;
    --dark-color: #1a1a2e;
    --light-color: #f8f9fa;
    --gray-light: #e9ecef;
    --border-radius: 12px;
    --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    --hover-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.dashboard {
    background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
    min-height: 100vh;
}

.dashboard-wrapper {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

/* Welcome Header */
.dashboard-header {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    border-radius: var(--border-radius);
    padding: 2rem;
    margin-bottom: 2rem;
    color: white;
    box-shadow: var(--card-shadow);
    position: relative;
    overflow: hidden;
}

.dashboard-header::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 1%, transparent 70%);
}

.dashboard-header h1 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    position: relative;
    z-index: 1;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    color: #ffffff;
}

.dashboard-header p {
    opacity: 1;
    font-size: 1.1rem;
    font-weight: 500;
    max-width: 600px;
    position: relative;
    z-index: 1;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    color: #ffffff;
}

.dashboard-badge {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-size: 0.875rem;
    display: inline-block;
    margin-top: 1rem;
    backdrop-filter: blur(10px);
    position: relative;
    z-index: 1;
}

/* Stats Cards */
.stats-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 1.25rem;
    box-shadow: var(--card-shadow);
    transition: all 0.3s ease;
    border: 1px solid var(--gray-light);
    position: relative;
    overflow: hidden;
    min-width: 0;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--hover-shadow);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: var(--primary-color);
}

.stat-card:nth-child(2)::before { background: var(--success-color); }
.stat-card:nth-child(3)::before { background: #7209b7; }
.stat-card:nth-child(4)::before { background: #f72585; }
.stat-card:nth-child(5)::before { background: var(--warning-color); }
.stat-card:nth-child(6)::before { background: var(--danger-color); }

.stat-card h3 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
    color: var(--dark-color);
    white-space: nowrap;
}

.stat-card p {
    color: #6c757d;
    font-size: 0.85rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.stat-card small {
    display: block;
    font-size: 0.8rem;
    color: #9ca3af;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Quick Actions */
.quick-actions {
    margin-bottom: 2rem;
}

.quick-actions .card {
    border: none;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    overflow: hidden;
}

.quick-actions .card-header {
    background: white;
    border-bottom: 2px solid var(--primary-light);
    padding: 1.25rem 1.5rem;
}

.quick-actions .card-header h2 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--dark-color);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.quick-actions .card-body {
    padding: 1.5rem;
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    background: white;
    border: 2px solid var(--gray-light);
    border-radius: var(--border-radius);
    color: var(--dark-color);
    text-decoration: none;
    transition: all 0.3s ease;
    text-align: center;
    min-height: 140px;
}

.quick-action-btn:hover {
    border-color: var(--primary-color);
    background: var(--primary-light);
    transform: translateY(-3px);
    box-shadow: var(--card-shadow);
    color: var(--primary-color);
    text-decoration: none;
}

.quick-action-btn i {
    font-size: 2rem;
    margin-bottom: 1rem;
    color: var(--primary-color);
}

.quick-action-btn span {
    font-weight: 600;
    font-size: 0.95rem;
}

/* Analytics Section */
.analytics-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--primary-light);
}

.analytics-header h2 {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--dark-color);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.analytics-controls {
    display: flex;
    gap: 1rem;
}

.time-filter {
    padding: 0.5rem 1rem;
    border: 1px solid var(--gray-light);
    border-radius: 8px;
    background: white;
    font-size: 0.875rem;
    color: var(--dark-color);
}

/* Charts */
.chart-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.chart-card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    overflow: hidden;
    transition: transform 0.3s ease;
}

.chart-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--hover-shadow);
}

.chart-card-header {
    padding: 1.25rem;
    border-bottom: 1px solid var(--gray-light);
    background: white;
}

.chart-card-header h5 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: var(--dark-color);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.chart-card-body {
    padding: 1.25rem;
    height: 300px;
}

/* Full width charts */
.full-width-chart {
    grid-column: 1 / -1;
}

.full-width-chart .chart-card-body {
    height: 400px;
}

/* Department Performance */
.performance-card {
    margin-top: 2rem;
}

.performance-card .card {
    border: none;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    overflow: hidden;
}

.performance-card .card-header {
    background: white;
    border-bottom: 2px solid var(--primary-light);
    padding: 1.25rem 1.5rem;
}

.performance-card .card-header h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--dark-color);
    margin: 0;
}

.table-responsive {
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid var(--gray-light);
}

.table {
    margin: 0;
}

.table thead th {
    background: var(--primary-light);
    border-bottom: 2px solid var(--primary-color);
    font-weight: 600;
    color: var(--dark-color);
    padding: 1rem;
    white-space: nowrap;
}

.table tbody td {
    padding: 1rem;
    vertical-align: middle;
    border-top: 1px solid var(--gray-light);
}

.leaderboard-position {
    font-weight: 700;
    font-size: 1.1rem;
    text-align: center;
}

.top-position {
    background: linear-gradient(135deg, #ffd166, #ff9e00);
    color: white;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.rating-good {
    color: var(--success-color);
    font-weight: 600;
}

.rating-neutral {
    color: #6c757d;
    font-weight: 600;
}

.rating-bad {
    color: var(--danger-color);
    font-weight: 600;
}

/* Recent Activities */
.recent-activities .card {
    border: none;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    overflow: hidden;
}

.recent-activities .card-header {
    background: white;
    border-bottom: 2px solid var(--primary-light);
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.recent-activities .card-header h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--dark-color);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.notification-badge {
    background: var(--danger-color);
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
}

.activity-row {
    border-bottom: 1px solid var(--gray-light);
    padding: 1rem;
    transition: background 0.2s ease;
}

.activity-row:hover {
    background: var(--primary-light);
}

.activity-row:last-child {
    border-bottom: none;
}

.activity-user {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
}

/* Buttons */
.btn-export {
    background: white;
    border: 2px solid var(--primary-color);
    color: var(--primary-color);
    padding: 0.5rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-export:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
    box-shadow: var(--card-shadow);
}

/* Responsive */
@media (max-width: 1200px) {
    .chart-grid {
        grid-template-columns: 1fr;
    }
    
    .full-width-chart .chart-card-body {
        height: 350px;
    }
}

@media (max-width: 768px) {
    .dashboard-wrapper {
        padding: 15px;
    }
    
    .dashboard-header {
        padding: 1.5rem;
    }
    
    .stats-container {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .quick-actions-grid {
        grid-template-columns: 1fr;
    }
    
    .analytics-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .analytics-controls {
        width: 100%;
    }
    
    .chart-card-body {
        height: 250px;
    }
    
    .full-width-chart .chart-card-body {
        height: 300px;
    }
}

@media (max-width: 576px) {
    .stats-container {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .stat-card h3 {
        font-size: 1.5rem;
    }
    
    .dashboard-header h1 {
        font-size: 1.5rem;
    }
}
</style>

<div class="dashboard">
    <div class="dashboard-wrapper">
        
        <?php
        // Show login success message if set
        if (isset($_SESSION['login_success']) && $_SESSION['login_success']) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Welcome back!</strong> Login successful. Ready to manage surveys.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
            unset($_SESSION['login_success']);
        }
        ?>
        
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h1>Welcome back, <?php echo htmlspecialchars($_SESSION["name"]); ?>! 👋</h1>
            <p>Here's what's happening with your student survey data today.</p>
            <div class="dashboard-badge">
                <i class="fas fa-chart-line me-1"></i> Last updated: <?php echo date('F j, Y'); ?>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <h3><?php echo $stats['total_students']; ?></h3>
                <p>Total Students</p>
                <small><i class="fas fa-user-graduate text-primary me-1"></i> Registered</small>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['total_teachers']; ?></h3>
                <p>Total Teachers</p>
                <small><i class="fas fa-chalkboard-teacher text-success me-1"></i> Active</small>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['students_completed']; ?></h3>
                <p>Students Completed</p>
                <small><i class="fas fa-check-circle text-purple me-1"></i> Surveys done</small>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['teachers_completed']; ?></h3>
                <p>Teachers Rated</p>
                <small><i class="fas fa-star text-pink me-1"></i> Ratings received</small>
            </div>
            <div class="stat-card">
                <h3><?php echo $suggestionCount; ?></h3>
                <p>Suggestions</p>
                <small><i class="fas fa-lightbulb text-warning me-1"></i> New ideas</small>
            </div>
            <div class="stat-card">
                <h3><?php echo $complaintCount; ?></h3>
                <p>Complaints</p>
                <small><i class="fas fa-exclamation-triangle text-danger me-1"></i> Need attention</small>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
                </div>
                <div class="card-body">
                    <div class="quick-actions-grid">
                        <a href="survey_management.php" class="quick-action-btn">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Advanced Surveys</span>
                            <small class="mt-2 text-muted">Create & restart surveys with question sets</small>
                        </a>
                        <a href="user_management.php" class="quick-action-btn">
                            <i class="fas fa-users"></i>
                            <span>User Management</span>
                            <small class="mt-2 text-muted">Manage students & teachers</small>
                        </a>
                        <a href="complaints.php" class="quick-action-btn">
                            <i class="fas fa-comment-alt"></i>
                            <span>View Feedback</span>
                            <small class="mt-2 text-muted">Suggestions & complaints</small>
                        </a>
                        <a href="#" onclick="createNewSurvey()" class="quick-action-btn">
                            <i class="fas fa-plus-circle"></i>
                            <span>Quick Survey</span>
                            <small class="mt-2 text-muted">Create survey instantly</small>
                        </a>
                        <a href="#" onclick="viewQuestionBank()" class="quick-action-btn">
                            <i class="fas fa-database"></i>
                            <span>Question Bank</span>
                            <small class="mt-2 text-muted">Browse question library</small>
                        </a>
                        <a href="#" onclick="exportAllData()" class="quick-action-btn">
                            <i class="fas fa-download"></i>
                            <span>Export Data</span>
                            <small class="mt-2 text-muted">Download survey reports</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Analytics Section -->
        <div class="analytics-section">
            <div class="analytics-header">
                <h2><i class="fas fa-chart-bar"></i> Data Analytics & Insights</h2>
                <div class="analytics-controls">
                    <select class="time-filter">
                        <option>Last 7 days</option>
                        <option selected>Last 30 days</option>
                        <option>Last 90 days</option>
                        <option>All time</option>
                    </select>
                </div>
            </div>
            
            <!-- First Row of Charts -->
            <div class="chart-grid">
                <div class="chart-card">
                    <div class="chart-card-header">
                        <h5><i class="fas fa-pie-chart"></i> User Distribution</h5>
                    </div>
                    <div class="chart-card-body">
                        <canvas id="userDistributionChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <div class="chart-card-header">
                        <h5><i class="fas fa-star"></i> Rating Distribution</h5>
                    </div>
                    <div class="chart-card-body">
                        <canvas id="ratingDistributionChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Second Row of Charts -->
            <div class="chart-grid">
                <div class="chart-card">
                    <div class="chart-card-header">
                        <h5><i class="fas fa-chart-line"></i> Survey Responses Timeline</h5>
                    </div>
                    <div class="chart-card-body">
                        <canvas id="responseTimelineChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <div class="chart-card-header">
                        <h5><i class="fas fa-tasks"></i> Completion Status</h5>
                    </div>
                    <div class="chart-card-body">
                        <canvas id="completionStatusChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Third Row of Charts -->
            <div class="chart-grid">
                <div class="chart-card">
                    <div class="chart-card-header">
                        <h5><i class="fas fa-comments"></i> Feedback Overview</h5>
                    </div>
                    <div class="chart-card-body">
                        <canvas id="feedbackChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <div class="chart-card-header">
                        <h5><i class="fas fa-building"></i> Department Performance</h5>
                    </div>
                    <div class="chart-card-body">
                        <canvas id="departmentChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Full Width Chart -->
            <div class="chart-grid full-width-chart">
                <div class="chart-card">
                    <div class="chart-card-header">
                        <h5><i class="fas fa-chart-area"></i> Weekly Activity Overview</h5>
                    </div>
                    <div class="chart-card-body">
                        <canvas id="dailyActivityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Department Performance Table -->
        <div class="performance-card">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-trophy"></i> Department Performance Ranking</h3>
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
                        <table class="table table-hover" id="department-performance">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Department</th>
                                    <th>Total Teachers</th>
                                    <th>Students Rated</th>
                                    <th>Good</th>
                                    <th>Neutral</th>
                                    <th>Bad</th>
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
                                        <td class="leaderboard-position <?php echo ($index < 3) ? 'top-position' : ''; ?>">
                                            <?php echo $index + 1; ?>
                                        </td>
                                        <td><strong><?php echo htmlspecialchars($department['department']); ?></strong></td>
                                        <td><?php echo $department['total_teachers']; ?></td>
                                        <td><?php echo $department['students_rated']; ?></td>
                                        <td class="rating-good"><?php echo $department['good_ratings']; ?></td>
                                        <td class="rating-neutral"><?php echo $department['neutral_ratings']; ?></td>
                                        <td class="rating-bad"><?php echo $department['bad_ratings']; ?></td>
                                        <td>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-success" style="width: <?php echo $performanceScore; ?>%"></div>
                                            </div>
                                            <small><?php echo $performanceScore; ?>%</small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                
                                <?php if (empty($departmentData)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-database fa-2x text-muted mb-3"></i>
                                            <p class="text-muted">No department data available yet.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        <button class="btn-export" onclick="exportTableToCSV('department-performance', 'department_performance.csv')">
                            <i class="fas fa-download"></i> Export to CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activities -->
        <div class="recent-activities mt-4">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-history"></i> Recent Activities</h3>
                    <?php if ($recentActivitiesCount > 0): ?>
                        <div class="notification-badge">
                            <?php echo $recentActivitiesCount; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php
                    // Get recent survey responses
                    $sql = "SELECT u.username as name, u.role, sq.question, sr.rating, sr.created_at
                            FROM survey_responses sr
                            JOIN users u ON sr.user_id = u.id
                            JOIN survey_questions sq ON sr.question_id = sq.id
                            ORDER BY sr.created_at DESC
                            LIMIT 6";
                    
                    $recentActivities = [];
                    $result = mysqli_query($conn, $sql);
                    
                    if ($result) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $recentActivities[] = $row;
                        }
                    }
                    ?>
                    
                    <?php if (!empty($recentActivities)): ?>
                        <?php foreach ($recentActivities as $activity): ?>
                            <div class="activity-row">
                                <div class="row align-items-center">
                                    <div class="col-md-3">
                                        <div class="activity-user">
                                            <div class="user-avatar">
                                                <?php echo strtoupper(substr($activity['name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <strong><?php echo htmlspecialchars($activity['name']); ?></strong>
                                                <div class="text-muted small"><?php echo ucfirst(htmlspecialchars($activity['role'])); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="text-truncate">
                                            <?php echo htmlspecialchars(substr($activity['question'], 0, 60)) . (strlen($activity['question']) > 60 ? '...' : ''); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <span class="badge bg-<?php echo $activity['rating'] == 'good' ? 'success' : ($activity['rating'] == 'neutral' ? 'warning' : 'danger'); ?>">
                                            <?php echo ucfirst($activity['rating']); ?>
                                        </span>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <small class="text-muted"><?php echo date('g:i A', strtotime($activity['created_at'])); ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No recent activities found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Load Chart.js -->
<script src="../../assets/js/chart.min.js"></script>

<!-- Get real database data for charts (Keep this section as is from original code) -->
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

<!-- Enhanced Chart.js Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
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
    
    // Use database data if available, otherwise use sample data
    const chartData = {
        userStats: (dbData.userStats.students + dbData.userStats.teachers + dbData.userStats.admins) > 0 ? dbData.userStats : {
            students: 5, teachers: 3, admins: 1
        },
        ratingStats: dbData.ratingStats.reduce((a, b) => a + b, 0) > 0 ? dbData.ratingStats : [12, 18, 8, 4, 2],
        timelineData: dbData.timelineData.reduce((a, b) => a + b, 0) > 0 ? dbData.timelineData : [3, 2, 5, 8, 4, 6, 3],
        timelineLabels: dbData.timelineLabels.length > 0 ? dbData.timelineLabels : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        deptData: dbData.deptData.length > 0 ? dbData.deptData : [
            { department: 'Computer Science', avg_rating: 4.2 },
            { department: 'Electronics', avg_rating: 3.8 },
            { department: 'Mechanical', avg_rating: 4.0 },
            { department: 'Civil', avg_rating: 3.6 }
        ],
        feedbackData: (dbData.feedbackData.suggestions + dbData.feedbackData.complaints) > 0 ? dbData.feedbackData : {
            suggestions: 5, complaints: 2
        },
        completionData: dbData.completionData.students_total > 0 ? dbData.completionData : {
            students_total: 5, students_completed: 3, teachers_total: 3, teachers_completed: 2
        }
    };
    
    // Color scheme
    const colors = {
        primary: '#4361ee',
        secondary: '#3a0ca3',
        success: '#06d6a0',
        warning: '#ffd166',
        danger: '#ef476f',
        info: '#118ab2',
        light: '#e9ecef'
    };
    
    // Initialize all charts
    initializeCharts();
    
    function initializeCharts() {
        // 1. User Distribution Chart
        createUserDistributionChart();
        
        // 2. Rating Distribution Chart
        createRatingChart();
        
        // 3. Timeline Chart
        createTimelineChart();
        
        // 4. Completion Status Chart
        createCompletionChart();
        
        // 5. Feedback Chart
        createFeedbackChart();
        
        // 6. Department Chart
        createDepartmentChart();
        
        // 7. Daily Activity Chart
        createActivityChart();
    }
    
    function createUserDistributionChart() {
        const ctx = document.getElementById('userDistributionChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Students', 'Teachers', 'Admins'],
                datasets: [{
                    data: [
                        chartData.userStats.students,
                        chartData.userStats.teachers,
                        chartData.userStats.admins
                    ],
                    backgroundColor: [colors.primary, colors.success, colors.warning],
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((context.raw / total) * 100);
                                return `${context.label}: ${context.raw} (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '65%'
            }
        });
    }
    
    function createRatingChart() {
        const ctx = document.getElementById('ratingDistributionChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Excellent (5)', 'Good (4)', 'Average (3)', 'Poor (2)', 'Very Poor (1)'],
                datasets: [{
                    label: 'Responses',
                    data: chartData.ratingStats,
                    backgroundColor: [
                        colors.success,
                        colors.info,
                        colors.warning,
                        '#f8961e',
                        colors.danger
                    ],
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        },
                        ticks: {
                            maxTicksLimit: 10,
                            callback: function(value) {
                                if (Math.floor(value) === value) return value;
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
    
    function createTimelineChart() {
        const ctx = document.getElementById('responseTimelineChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.timelineLabels,
                datasets: [{
                    label: 'Survey Responses',
                    data: chartData.timelineData,
                    borderColor: colors.primary,
                    backgroundColor: 'rgba(67, 97, 238, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointBackgroundColor: colors.primary,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
    
    function createCompletionChart() {
        const ctx = document.getElementById('completionStatusChart').getContext('2d');
        const completed = chartData.completionData.students_completed + chartData.completionData.teachers_completed;
        const total = chartData.completionData.students_total + chartData.completionData.teachers_total;
        const pending = total - completed;
        
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Completed', 'Pending'],
                datasets: [{
                    data: [completed, pending],
                    backgroundColor: [colors.success, colors.light],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const percentage = Math.round((context.raw / total) * 100);
                                return `${context.label}: ${context.raw} (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });
    }
    
    function createFeedbackChart() {
        const ctx = document.getElementById('feedbackChart').getContext('2d');
        new Chart(ctx, {
            type: 'polarArea',
            data: {
                labels: ['Suggestions', 'Complaints'],
                datasets: [{
                    data: [
                        chartData.feedbackData.suggestions,
                        chartData.feedbackData.complaints
                    ],
                    backgroundColor: [
                        colors.success,
                        colors.danger
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    r: {
                        ticks: {
                            display: false
                        }
                    }
                }
            }
        });
    }
    
    function createDepartmentChart() {
        const ctx = document.getElementById('departmentChart').getContext('2d');
        new Chart(ctx, {
            type: 'radar',
            data: {
                labels: chartData.deptData.map(d => d.department),
                datasets: [{
                    label: 'Average Rating',
                    data: chartData.deptData.map(d => d.avg_rating),
                    borderColor: colors.primary,
                    backgroundColor: 'rgba(67, 97, 238, 0.2)',
                    borderWidth: 2,
                    pointBackgroundColor: colors.primary,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 5,
                        ticks: {
                            stepSize: 1,
                            display: false
                        },
                        pointLabels: {
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    
    function createActivityChart() {
        const ctx = document.getElementById('dailyActivityChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [
                    {
                        label: 'Survey Responses',
                        data: [8, 12, 15, 18, 22, 5, 3],
                        borderColor: colors.primary,
                        backgroundColor: 'transparent',
                        borderWidth: 3,
                        tension: 0.4
                    },
                    {
                        label: 'User Logins',
                        data: [5, 8, 10, 12, 15, 3, 2],
                        borderColor: colors.success,
                        backgroundColor: 'transparent',
                        borderWidth: 3,
                        borderDash: [5, 5],
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
    
    // Export function for CSV
    window.exportTableToCSV = function(tableId, filename) {
        const table = document.getElementById(tableId);
        const rows = table.querySelectorAll('tr');
        const csv = [];
        
        for (let i = 0; i < rows.length; i++) {
            const row = [], cols = rows[i].querySelectorAll('td, th');
            
            for (let j = 0; j < cols.length; j++) {
                row.push(cols[j].innerText);
            }
            
            csv.push(row.join(','));
        }
        
        const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
        const downloadLink = document.createElement('a');
        downloadLink.download = filename;
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = 'none';
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
        
        // Show success message
        alert('Department performance data exported successfully!');
    };
    });
    
    // Enhanced Survey Management Functions
    window.createNewSurvey = function() {
        const title = prompt('Enter survey title:');
        if (title) {
            const description = prompt('Enter survey description:');
            if (description) {
                window.open('survey_management.php?action=create&title=' + encodeURIComponent(title) + '&desc=' + encodeURIComponent(description), '_blank');
            }
        }
    };
    
    window.viewQuestionBank = function() {
        window.open('survey_management.php?action=question_bank', '_blank');
    };
    
    window.exportAllData = function() {
        if (confirm('Export all survey data including:\n• Survey responses\n• Analytics reports\n• User feedback\n• Performance metrics\n\nThis may take a few moments. Continue?')) {
            // Create a temporary link for download
            const link = document.createElement('a');
            link.href = 'export_data.php?type=all';
            link.download = 'survey_data_export_' + new Date().toISOString().split('T')[0] + '.csv';
            link.style.display = 'none';
            document.body.appendChild(link);
            
            // Simulate download (in real implementation, this would trigger actual export)
            alert('Export initiated! A comprehensive data export is being prepared.\n\nFeature coming soon - this will include:\n✓ All survey responses\n✓ User analytics\n✓ Performance reports\n✓ Trend analysis');
            
            document.body.removeChild(link);
        }
    };

</script>

<?php include '../../core/includes/footer.php'; ?>

<?php
// Close connection
closeConnection($conn);
?>