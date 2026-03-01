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
        SUM(CASE WHEN tr.rating >= 4 THEN 1 ELSE 0 END) as good_ratings,
        SUM(CASE WHEN tr.rating = 3 THEN 1 ELSE 0 END) as neutral_ratings,
        SUM(CASE WHEN tr.rating >= 1 AND tr.rating <= 2 THEN 1 ELSE 0 END) as bad_ratings
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

<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header-modern mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap">
            <div class="d-flex align-items-center">
                <div class="header-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div>
                    <h1>Survey Analytics</h1>
                    <p class="mb-0">Explore insights from the college satisfaction survey data</p>
                </div>
            </div>
            <a href="dashboard.php" class="btn btn-light mt-3 mt-md-0">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="stat-card-modern gradient-primary">
                <div class="stat-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['students_completed']; ?></h3>
                    <p>Students Completed</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card-modern gradient-secondary">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_students']; ?></h3>
                    <p>Total Students</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card-modern gradient-accent">
                <div class="stat-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo round(($stats['students_completed'] / max(1, $stats['total_students'])) * 100); ?>%</h3>
                    <p>Completion Rate</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card-modern gradient-success">
                <div class="stat-icon">
                    <i class="fas fa-thumbs-up"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo $stats['ratings_distribution']['good'] ?? 0; ?></h3>
                    <p>Good Ratings</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card-modern">
                <div class="card-header-modern">
                    <h4><i class="fas fa-chart-pie me-2"></i>Rating Distribution</h4>
                </div>
                <div class="card-body-modern">
                    <div class="rating-breakdown">
                        <?php 
                        // Set defaults for ratings distribution to avoid undefined key errors
                        // Map numeric ratings (1-5) to Good/Neutral/Bad categories
                        $badCount = ($stats['ratings_distribution']['1'] ?? 0) + ($stats['ratings_distribution']['2'] ?? 0);
                        $neutralCount = $stats['ratings_distribution']['3'] ?? 0;
                        $goodCount = ($stats['ratings_distribution']['4'] ?? 0) + ($stats['ratings_distribution']['5'] ?? 0);
                        $total = max(1, $badCount + $neutralCount + $goodCount);
                        $goodPct = round(($goodCount / $total) * 100);
                        $neutralPct = round(($neutralCount / $total) * 100);
                        $badPct = round(($badCount / $total) * 100);
                        ?>
                        <div class="rating-item">
                            <div class="rating-label">
                                <span class="rating-dot good"></span>
                                <span>Good</span>
                            </div>
                            <div class="rating-bar-container">
                                <div class="rating-bar good" style="width: <?php echo $goodPct; ?>%"></div>
                            </div>
                            <span class="rating-count"><?php echo $goodCount; ?> (<?php echo $goodPct; ?>%)</span>
                        </div>
                        <div class="rating-item">
                            <div class="rating-label">
                                <span class="rating-dot neutral"></span>
                                <span>Neutral</span>
                            </div>
                            <div class="rating-bar-container">
                                <div class="rating-bar neutral" style="width: <?php echo $neutralPct; ?>%"></div>
                            </div>
                            <span class="rating-count"><?php echo $neutralCount; ?> (<?php echo $neutralPct; ?>%)</span>
                        </div>
                        <div class="rating-item">
                            <div class="rating-label">
                                <span class="rating-dot bad"></span>
                                <span>Bad</span>
                            </div>
                            <div class="rating-bar-container">
                                <div class="rating-bar bad" style="width: <?php echo $badPct; ?>%"></div>
                            </div>
                            <span class="rating-count"><?php echo $badCount; ?> (<?php echo $badPct; ?>%)</span>
                        </div>
                    </div>
                    <canvas id="rating-distribution-chart" class="mt-4" style="max-height: 250px;"
                        data-ratings='[<?php echo $badCount; ?>, <?php echo $neutralCount; ?>, <?php echo $goodCount; ?>]'>
                    </canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card-modern">
                <div class="card-header-modern">
                    <h4><i class="fas fa-tasks me-2"></i>Survey Completion Status</h4>
                </div>
                <div class="card-body-modern">
                    <div class="completion-stats">
                        <div class="completion-item">
                            <div class="completion-icon student">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="completion-details">
                                <h5>Students</h5>
                                <div class="completion-progress">
                                    <div class="progress">
                                        <div class="progress-bar bg-primary" style="width: <?php echo round(($stats['students_completed'] / max(1, $stats['total_students'])) * 100); ?>%"></div>
                                    </div>
                                    <span><?php echo $stats['students_completed']; ?>/<?php echo $stats['total_students']; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="completion-item">
                            <div class="completion-icon teacher">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <div class="completion-details">
                                <h5>Teachers</h5>
                                <div class="completion-progress">
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: <?php echo round(($stats['teachers_completed'] / max(1, $stats['total_teachers'])) * 100); ?>%"></div>
                                    </div>
                                    <span><?php echo $stats['teachers_completed']; ?>/<?php echo $stats['total_teachers']; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <canvas id="survey-completion-chart" class="mt-4" style="max-height: 250px;"
                        data-completion='{"students_completed": <?php echo $stats['students_completed']; ?>, "total_students": <?php echo $stats['total_students']; ?>, "teachers_completed": <?php echo $stats['teachers_completed']; ?>, "total_teachers": <?php echo $stats['total_teachers']; ?>}'>
                    </canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Teacher Leaderboard -->
    <div class="card-modern mb-4">
        <div class="card-header-modern">
            <h4><i class="fas fa-trophy me-2"></i>Teacher Ratings Leaderboard</h4>
            <button class="btn btn-sm btn-outline-primary" onclick="exportTableToCSV('teacher-leaderboard', 'teacher_ratings.csv')">
                <i class="fas fa-download me-1"></i>Export CSV
            </button>
        </div>
        <div class="card-body-modern p-0">
            <div class="table-responsive">
                <table class="table table-modern mb-0" id="teacher-leaderboard">
                    <thead>
                        <tr>
                            <th width="60">Rank</th>
                            <th>Teacher</th>
                            <th>Department</th>
                            <th class="text-center">Good</th>
                            <th class="text-center">Neutral</th>
                            <th class="text-center">Bad</th>
                            <th class="text-center">Total</th>
                            <th width="150">Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teacherRatings as $index => $teacher): 
                            $totalRatings = max(1, $teacher['total_ratings']);
                            $score = round((($teacher['good_ratings'] * 3 + $teacher['neutral_ratings'] * 2 + $teacher['bad_ratings'] * 1) / ($totalRatings * 3)) * 100);
                        ?>
                        <tr>
                            <td>
                                <?php if ($index < 3): ?>
                                    <span class="rank-badge rank-<?php echo $index + 1; ?>">
                                        <?php echo $index + 1; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="rank-number"><?php echo $index + 1; ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="teacher-cell">
                                    <div class="teacher-avatar-sm"><?php echo strtoupper(substr($teacher['name'], 0, 1)); ?></div>
                                    <span><?php echo htmlspecialchars($teacher['name']); ?></span>
                                </div>
                            </td>
                            <td><span class="dept-badge"><?php echo htmlspecialchars($teacher['department']); ?></span></td>
                            <td class="text-center"><span class="rating-badge good"><?php echo $teacher['good_ratings']; ?></span></td>
                            <td class="text-center"><span class="rating-badge neutral"><?php echo $teacher['neutral_ratings']; ?></span></td>
                            <td class="text-center"><span class="rating-badge bad"><?php echo $teacher['bad_ratings']; ?></span></td>
                            <td class="text-center"><strong><?php echo $teacher['total_ratings']; ?></strong></td>
                            <td>
                                <div class="score-bar">
                                    <div class="score-fill" style="width: <?php echo $score; ?>%; background: <?php echo $score >= 70 ? '#10b981' : ($score >= 50 ? '#f59e0b' : '#ef4444'); ?>;"></div>
                                    <span class="score-text"><?php echo $score; ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($teacherRatings)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-chart-bar me-2"></i>No teacher ratings available yet.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Division Performance -->
    <div class="card-modern">
        <div class="card-header-modern">
            <h4><i class="fas fa-layer-group me-2"></i>Division Performance</h4>
            <button class="btn btn-sm btn-outline-primary" onclick="exportTableToCSV('division-performance', 'division_performance.csv')">
                <i class="fas fa-download me-1"></i>Export CSV
            </button>
        </div>
        <div class="card-body-modern p-0">
            <?php
            $sql = "SELECT sp.division, 
                    COUNT(DISTINCT sr.user_id) as students_completed,
                    (SELECT COUNT(*) FROM student_profiles WHERE division = sp.division) as total_students,
                    SUM(CASE WHEN sr.rating >= 4 THEN 1 ELSE 0 END) as good_ratings,
                    SUM(CASE WHEN sr.rating = 3 THEN 1 ELSE 0 END) as neutral_ratings,
                    SUM(CASE WHEN sr.rating >= 1 AND sr.rating <= 2 THEN 1 ELSE 0 END) as bad_ratings
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
                <table class="table table-modern mb-0" id="division-performance">
                    <thead>
                        <tr>
                            <th width="60">Rank</th>
                            <th>Division</th>
                            <th>Completion Rate</th>
                            <th class="text-center">Good</th>
                            <th class="text-center">Neutral</th>
                            <th class="text-center">Bad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($divisionData as $index => $division): 
                            $completionRate = round(($division['students_completed'] / max(1, $division['total_students'])) * 100);
                        ?>
                        <tr>
                            <td>
                                <?php if ($index < 3): ?>
                                    <span class="rank-badge rank-<?php echo $index + 1; ?>"><?php echo $index + 1; ?></span>
                                <?php else: ?>
                                    <span class="rank-number"><?php echo $index + 1; ?></span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($division['division']); ?></strong></td>
                            <td>
                                <div class="completion-bar">
                                    <div class="completion-fill" style="width: <?php echo $completionRate; ?>%"></div>
                                    <span class="completion-text"><?php echo $completionRate; ?>%</span>
                                </div>
                            </td>
                            <td class="text-center"><span class="rating-badge good"><?php echo $division['good_ratings'] ?? 0; ?></span></td>
                            <td class="text-center"><span class="rating-badge neutral"><?php echo $division['neutral_ratings'] ?? 0; ?></span></td>
                            <td class="text-center"><span class="rating-badge bad"><?php echo $division['bad_ratings'] ?? 0; ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($divisionData)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fas fa-layer-group me-2"></i>No division data available yet.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
/* Page Header */
.page-header-modern {
    background: linear-gradient(135deg, #6366f1 0%, #818cf8 100%);
    border-radius: var(--radius-xl);
    padding: 2rem;
    color: white;
}

.page-header-modern h1 { color: white; font-size: 1.75rem; font-weight: 700; margin: 0 0 0.25rem 0; }
.page-header-modern p { color: rgba(255, 255, 255, 0.85); }

.header-icon {
    width: 60px; height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: var(--radius-lg);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; margin-right: 1.25rem;
}

/* Stat Cards */
.stat-card-modern {
    border-radius: var(--radius-xl);
    padding: 1.5rem;
    color: white;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: var(--shadow-md);
    transition: transform 0.3s ease;
}

.stat-card-modern:hover { transform: translateY(-5px); }

.gradient-primary { background: linear-gradient(135deg, #6366f1 0%, #818cf8 100%); }
.gradient-secondary { background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%); }
.gradient-accent { background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); }
.gradient-success { background: linear-gradient(135deg, #10b981 0%, #34d399 100%); }

.stat-card-modern .stat-icon {
    width: 50px; height: 50px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: var(--radius-lg);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem;
}

.stat-card-modern .stat-content h3 { margin: 0; font-size: 1.75rem; font-weight: 700; }
.stat-card-modern .stat-content p { margin: 0; opacity: 0.85; font-size: 0.875rem; }

/* Card Modern */
.card-modern { background: white; border-radius: var(--radius-xl); box-shadow: var(--shadow-md); overflow: hidden; }

.card-header-modern {
    padding: 1.25rem 1.5rem;
    background: var(--gray-50);
    border-bottom: 1px solid var(--gray-100);
    display: flex; justify-content: space-between; align-items: center;
}

.card-header-modern h4 { margin: 0; font-size: 1.1rem; font-weight: 600; }
.card-body-modern { padding: 1.5rem; }

/* Rating Breakdown */
.rating-breakdown { display: flex; flex-direction: column; gap: 1rem; }

.rating-item {
    display: grid;
    grid-template-columns: 100px 1fr 100px;
    align-items: center;
    gap: 1rem;
}

.rating-label { display: flex; align-items: center; gap: 0.5rem; font-weight: 500; }

.rating-dot {
    width: 12px; height: 12px; border-radius: 50%;
}

.rating-dot.good { background: #10b981; }
.rating-dot.neutral { background: #f59e0b; }
.rating-dot.bad { background: #ef4444; }

.rating-bar-container {
    height: 10px; background: var(--gray-200); border-radius: 5px; overflow: hidden;
}

.rating-bar {
    height: 100%; border-radius: 5px; transition: width 0.5s ease;
}

.rating-bar.good { background: linear-gradient(90deg, #10b981, #34d399); }
.rating-bar.neutral { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.rating-bar.bad { background: linear-gradient(90deg, #ef4444, #f87171); }

.rating-count { text-align: right; font-weight: 600; color: var(--text-secondary); }

/* Completion Stats */
.completion-stats { display: flex; flex-direction: column; gap: 1.5rem; }

.completion-item { display: flex; align-items: center; gap: 1rem; }

.completion-icon {
    width: 50px; height: 50px; border-radius: var(--radius-lg);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem; color: white;
}

.completion-icon.student { background: linear-gradient(135deg, #6366f1, #818cf8); }
.completion-icon.teacher { background: linear-gradient(135deg, #10b981, #34d399); }

.completion-details { flex: 1; }
.completion-details h5 { margin: 0 0 0.5rem 0; font-weight: 600; }

.completion-progress { display: flex; align-items: center; gap: 1rem; }
.completion-progress .progress { flex: 1; height: 8px; border-radius: 4px; }
.completion-progress span { font-weight: 600; color: var(--text-secondary); min-width: 60px; text-align: right; }

/* Table Modern */
.table-modern { margin: 0; }
.table-modern thead th {
    background: var(--gray-50);
    padding: 1rem 1.25rem;
    font-weight: 600;
    color: var(--text-secondary);
    border-bottom: 2px solid var(--gray-200);
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table-modern tbody td {
    padding: 1rem 1.25rem;
    vertical-align: middle;
    border-bottom: 1px solid var(--gray-100);
}

.table-modern tbody tr:hover { background: var(--gray-50); }

/* Rank Badge */
.rank-badge {
    width: 32px; height: 32px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 0.875rem; color: white;
}

.rank-badge.rank-1 { background: linear-gradient(135deg, #fbbf24, #f59e0b); }
.rank-badge.rank-2 { background: linear-gradient(135deg, #94a3b8, #64748b); }
.rank-badge.rank-3 { background: linear-gradient(135deg, #f97316, #ea580c); }

.rank-number { font-weight: 600; color: var(--text-muted); }

/* Teacher Cell */
.teacher-cell { display: flex; align-items: center; gap: 0.75rem; }

.teacher-avatar-sm {
    width: 36px; height: 36px;
    background: linear-gradient(135deg, #6366f1, #818cf8);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 600; font-size: 0.875rem; color: white;
}

.dept-badge {
    background: var(--gray-100);
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-sm);
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--text-secondary);
}

/* Rating Badges */
.rating-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    padding: 0.25rem 0.5rem;
    border-radius: var(--radius-sm);
    font-weight: 600;
    font-size: 0.875rem;
}

.rating-badge.good { background: rgba(16, 185, 129, 0.15); color: #059669; }
.rating-badge.neutral { background: rgba(245, 158, 11, 0.15); color: #d97706; }
.rating-badge.bad { background: rgba(239, 68, 68, 0.15); color: #dc2626; }

/* Score Bar */
.score-bar {
    position: relative;
    height: 24px;
    background: var(--gray-200);
    border-radius: var(--radius-sm);
    overflow: hidden;
}

.score-fill { height: 100%; border-radius: var(--radius-sm); transition: width 0.5s ease; }

.score-text {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    font-weight: 600;
    font-size: 0.75rem;
    color: var(--text-primary);
}

/* Completion Bar */
.completion-bar {
    position: relative;
    height: 24px;
    background: var(--gray-200);
    border-radius: var(--radius-sm);
    overflow: hidden;
    min-width: 120px;
}

.completion-fill {
    height: 100%;
    background: linear-gradient(90deg, #6366f1, #818cf8);
    border-radius: var(--radius-sm);
    transition: width 0.5s ease;
}

.completion-text {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    font-weight: 600;
    font-size: 0.75rem;
    color: var(--text-primary);
}

/* Responsive */
@media (max-width: 768px) {
    .page-header-modern { padding: 1.5rem; }
    .page-header-modern h1 { font-size: 1.25rem; }
    .header-icon { width: 50px; height: 50px; font-size: 1.25rem; }
    .rating-item { grid-template-columns: 1fr; gap: 0.5rem; }
}
</style>

<?php include '../../core/includes/footer.php'; ?>

<?php
// Close connection
closeConnection($conn);
?>

