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
$teacherName = $_SESSION["username"] ?? $_SESSION["name"] ?? '';
$teacherProfile = getUserProfileData($teacherId, "teacher", $conn);
$teacherDept = $teacherProfile['department'] ?? '';

// ─── Student completion data for teacher's department ───
// courses contain prefix like "B.Tech Computer Science" while department is "Computer Science"
// so use LIKE to match
$sql = "SELECT COUNT(DISTINCT sr.user_id) as completed_count
        FROM survey_responses sr
        JOIN users u ON sr.user_id = u.id
        JOIN student_profiles sp ON u.id = sp.user_id
        WHERE u.role = 'student' AND sp.course LIKE CONCAT('%', ?, '%')";

$studentsCompleted = 0;

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $teacherDept);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $studentsCompleted = (int)$row['completed_count'];
        }
    }
    
    mysqli_stmt_close($stmt);
}

// ─── Total students in teacher's department ───
$sql = "SELECT COUNT(*) as total_count
        FROM users u
        JOIN student_profiles sp ON u.id = sp.user_id
        WHERE u.role = 'student' AND sp.course LIKE CONCAT('%', ?, '%')";

$totalStudents = 0;

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $teacherDept);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $totalStudents = (int)$row['total_count'];
        }
    }
    
    mysqli_stmt_close($stmt);
}

// ─── Teacher's own ratings (numeric 0-5 scale) ───
// Map: 4-5 = Good, 3 = Neutral, 0-2 = Bad
$sql = "SELECT 
        COUNT(tr.id) as total_ratings,
        SUM(CASE WHEN tr.rating >= 4 THEN 1 ELSE 0 END) as good_ratings,
        SUM(CASE WHEN tr.rating = 3 THEN 1 ELSE 0 END) as neutral_ratings,
        SUM(CASE WHEN tr.rating <= 2 THEN 1 ELSE 0 END) as bad_ratings,
        ROUND(AVG(tr.rating), 1) as avg_rating
        FROM teacher_ratings tr
        WHERE tr.teacher_id = ? AND tr.rating >= 1";

$ratings = [
    'total' => 0,
    'good' => 0,
    'neutral' => 0,
    'bad' => 0,
    'avg' => 0
];

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $teacherId);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $ratings['total'] = (int)$row['total_ratings'];
            $ratings['good'] = (int)$row['good_ratings'];
            $ratings['neutral'] = (int)$row['neutral_ratings'];
            $ratings['bad'] = (int)$row['bad_ratings'];
            $ratings['avg'] = (float)$row['avg_rating'];
        }
    }
    
    mysqli_stmt_close($stmt);
}

// ─── Department performance data ───
$sql = "SELECT tp.department, 
        COUNT(DISTINCT tr.student_id) as students_rated,
        COUNT(tr.id) as total_ratings,
        SUM(CASE WHEN tr.rating >= 4 THEN 1 ELSE 0 END) as good_ratings,
        SUM(CASE WHEN tr.rating = 3 THEN 1 ELSE 0 END) as neutral_ratings,
        SUM(CASE WHEN tr.rating <= 2 THEN 1 ELSE 0 END) as bad_ratings,
        ROUND(AVG(tr.rating), 1) as avg_rating
        FROM teacher_profiles tp
        JOIN users u ON tp.user_id = u.id
        LEFT JOIN teacher_ratings tr ON u.id = tr.teacher_id AND tr.rating >= 1
        WHERE u.role = 'teacher'
        GROUP BY tp.department
        ORDER BY avg_rating DESC, total_ratings DESC";

$departmentData = [];
$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $departmentData[] = $row;
    }
}

// ─── Teacher performance in same department ───
$sql = "SELECT u.id as teacher_user_id, u.username as name, 
        COUNT(tr.id) as total_ratings,
        SUM(CASE WHEN tr.rating >= 4 THEN 1 ELSE 0 END) as good_ratings,
        SUM(CASE WHEN tr.rating = 3 THEN 1 ELSE 0 END) as neutral_ratings,
        SUM(CASE WHEN tr.rating <= 2 THEN 1 ELSE 0 END) as bad_ratings,
        ROUND(AVG(tr.rating), 1) as avg_rating
        FROM users u
        JOIN teacher_profiles tp ON u.id = tp.user_id
        LEFT JOIN teacher_ratings tr ON u.id = tr.teacher_id AND tr.rating >= 1
        WHERE tp.department = ? AND u.role = 'teacher'
        GROUP BY u.id
        ORDER BY avg_rating DESC, good_ratings DESC";

$teacherData = [];

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $teacherDept);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $teacherData[] = $row;
        }
    }
    
    mysqli_stmt_close($stmt);
}

// ─── Recent ratings for the teacher ───
$sql = "SELECT tr.rating, tr.comment, tr.created_at, u.username as student_name
        FROM teacher_ratings tr
        JOIN users u ON tr.student_id = u.id
        WHERE tr.teacher_id = ?
        ORDER BY tr.created_at DESC
        LIMIT 5";

$recentRatings = [];

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $teacherId);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $recentRatings[] = $row;
        }
    }
    
    mysqli_stmt_close($stmt);
}

// Calculate percentages
$goodPercent = $ratings['total'] > 0 ? round(($ratings['good'] / $ratings['total']) * 100) : 0;
$neutralPercent = $ratings['total'] > 0 ? round(($ratings['neutral'] / $ratings['total']) * 100) : 0;
$badPercent = $ratings['total'] > 0 ? round(($ratings['bad'] / $ratings['total']) * 100) : 0;
$completionRate = $totalStudents > 0 ? round(($studentsCompleted / $totalStudents) * 100) : 0;

// Set page variables
$pageTitle = "Teacher Analytics";
$basePath = "../../";
?>

<?php include '../../core/includes/header.php'; ?>

<div class="container py-4">
    <!-- Page Header -->
    <div class="analytics-header mb-4">
        <div class="d-flex align-items-center">
            <div class="header-icon-box">
                <i class="fas fa-chart-line"></i>
            </div>
            <div>
                <h1 class="mb-1">Analytics Dashboard</h1>
                <p class="mb-0">Your performance metrics · <?php echo htmlspecialchars($teacherDept ?: 'Department'); ?></p>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="metric-card metric-indigo">
                <div class="metric-icon"><i class="fas fa-user-graduate"></i></div>
                <div class="metric-value"><?php echo $studentsCompleted; ?></div>
                <div class="metric-label">Students Completed</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card metric-violet">
                <div class="metric-icon"><i class="fas fa-users"></i></div>
                <div class="metric-value"><?php echo $totalStudents; ?></div>
                <div class="metric-label">Total Students</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card metric-amber">
                <div class="metric-icon"><i class="fas fa-percentage"></i></div>
                <div class="metric-value"><?php echo $completionRate; ?>%</div>
                <div class="metric-label">Completion Rate</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="metric-card metric-emerald">
                <div class="metric-icon"><i class="fas fa-star"></i></div>
                <div class="metric-value"><?php echo $ratings['total']; ?></div>
                <div class="metric-label">Your Ratings</div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Rating Distribution -->
        <div class="col-lg-5">
            <div class="analytics-card h-100">
                <div class="analytics-card-header">
                    <h5><i class="fas fa-star me-2 text-warning"></i>Your Rating Distribution</h5>
                    <?php if ($ratings['total'] > 0): ?>
                    <span class="avg-badge"><?php echo $ratings['avg']; ?> / 5</span>
                    <?php endif; ?>
                </div>
                <div class="analytics-card-body">
                    <?php if ($ratings['total'] > 0): ?>
                    <div class="d-flex align-items-center gap-4">
                        <div class="chart-wrap">
                            <canvas id="ratingChart"></canvas>
                            <div class="chart-center-text">
                                <span class="chart-center-value"><?php echo $ratings['total']; ?></span>
                                <span class="chart-center-label">Total</span>
                            </div>
                        </div>
                        <div class="rating-breakdown flex-grow-1">
                            <div class="breakdown-item">
                                <div class="breakdown-header">
                                    <span class="breakdown-dot good"></span>
                                    <span class="breakdown-label">Good (4-5)</span>
                                    <span class="breakdown-count"><?php echo $ratings['good']; ?></span>
                                </div>
                                <div class="breakdown-bar">
                                    <div class="breakdown-fill good" style="width: <?php echo $goodPercent; ?>%"></div>
                                </div>
                                <span class="breakdown-pct"><?php echo $goodPercent; ?>%</span>
                            </div>
                            <div class="breakdown-item">
                                <div class="breakdown-header">
                                    <span class="breakdown-dot neutral"></span>
                                    <span class="breakdown-label">Neutral (3)</span>
                                    <span class="breakdown-count"><?php echo $ratings['neutral']; ?></span>
                                </div>
                                <div class="breakdown-bar">
                                    <div class="breakdown-fill neutral" style="width: <?php echo $neutralPercent; ?>%"></div>
                                </div>
                                <span class="breakdown-pct"><?php echo $neutralPercent; ?>%</span>
                            </div>
                            <div class="breakdown-item">
                                <div class="breakdown-header">
                                    <span class="breakdown-dot bad"></span>
                                    <span class="breakdown-label">Bad (1-2)</span>
                                    <span class="breakdown-count"><?php echo $ratings['bad']; ?></span>
                                </div>
                                <div class="breakdown-bar">
                                    <div class="breakdown-fill bad" style="width: <?php echo $badPercent; ?>%"></div>
                                </div>
                                <span class="breakdown-pct"><?php echo $badPercent; ?>%</span>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="empty-state-inline">
                        <i class="fas fa-star"></i>
                        <p>No ratings received yet</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Completion Chart -->
        <div class="col-lg-4">
            <div class="analytics-card h-100">
                <div class="analytics-card-header">
                    <h5><i class="fas fa-chart-pie me-2 text-primary"></i>Survey Completion</h5>
                </div>
                <div class="analytics-card-body d-flex flex-column justify-content-center">
                    <div class="chart-wrap-sm mx-auto">
                        <canvas id="completionChart"></canvas>
                    </div>
                    <div class="completion-legend mt-3">
                        <div class="legend-item">
                            <span class="legend-dot" style="background:#10b981;"></span>
                            Students: <?php echo $studentsCompleted; ?>/<?php echo $totalStudents; ?>
                        </div>
                        <div class="legend-item">
                            <span class="legend-dot" style="background:#8b5cf6;"></span>
                            Teachers: <?php echo $stats['teachers_completed']; ?>/<?php echo $stats['total_teachers']; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Ratings -->
        <div class="col-lg-3">
            <div class="analytics-card h-100">
                <div class="analytics-card-header">
                    <h5><i class="fas fa-clock me-2 text-info"></i>Recent Ratings</h5>
                </div>
                <div class="analytics-card-body p-0">
                    <?php if (!empty($recentRatings)): ?>
                    <div class="recent-list">
                        <?php foreach ($recentRatings as $r): ?>
                        <div class="recent-item">
                            <div class="recent-top">
                                <span class="recent-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $r['rating'] ? 'filled' : ''; ?>"></i>
                                    <?php endfor; ?>
                                </span>
                                <span class="recent-time"><?php echo date('M j', strtotime($r['created_at'])); ?></span>
                            </div>
                            <?php if (!empty($r['comment'])): ?>
                            <p class="recent-comment"><?php echo htmlspecialchars(substr($r['comment'], 0, 60)); ?><?php echo strlen($r['comment']) > 60 ? '…' : ''; ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state-inline small-padding">
                        <i class="fas fa-inbox"></i>
                        <p>No ratings yet</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Performance Table -->
    <div class="analytics-card mb-4">
        <div class="analytics-card-header d-flex justify-content-between align-items-center">
            <h5><i class="fas fa-building me-2"></i>Department Performance</h5>
            <button class="btn btn-sm btn-outline-secondary" onclick="exportTableToCSV('dept-table', 'department_performance.csv')">
                <i class="fas fa-download me-1"></i>Export CSV
            </button>
        </div>
        <div class="table-responsive">
            <table class="analytics-table" id="dept-table">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Department</th>
                        <th class="text-center">Avg Rating</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Good</th>
                        <th class="text-center">Neutral</th>
                        <th class="text-center">Bad</th>
                        <th class="text-center" width="160">Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departmentData as $i => $dept): ?>
                        <?php 
                        $deptTotal = (int)$dept['total_ratings'];
                        $deptScore = $deptTotal > 0 ? 
                            round((($dept['good_ratings'] * 3 + $dept['neutral_ratings'] * 2 + $dept['bad_ratings'] * 1) / ($deptTotal * 3)) * 100) : 0;
                        $isMyDept = ($dept['department'] === $teacherDept);
                        ?>
                        <tr class="<?php echo $isMyDept ? 'row-highlight' : ''; ?>">
                            <td><span class="rank-num <?php echo $i < 3 ? 'rank-top' : ''; ?>"><?php echo $i + 1; ?></span></td>
                            <td>
                                <strong><?php echo htmlspecialchars($dept['department']); ?></strong>
                                <?php if ($isMyDept): ?><span class="badge bg-primary ms-1">Your Dept</span><?php endif; ?>
                            </td>
                            <td class="text-center fw-semibold"><?php echo $dept['avg_rating'] ?: '—'; ?></td>
                            <td class="text-center"><?php echo $deptTotal; ?></td>
                            <td class="text-center"><span class="pill good"><?php echo (int)$dept['good_ratings']; ?></span></td>
                            <td class="text-center"><span class="pill neutral"><?php echo (int)$dept['neutral_ratings']; ?></span></td>
                            <td class="text-center"><span class="pill bad"><?php echo (int)$dept['bad_ratings']; ?></span></td>
                            <td>
                                <div class="score-row">
                                    <div class="score-track">
                                        <div class="score-bar-fill <?php echo $deptScore >= 70 ? 'high' : ($deptScore >= 40 ? 'mid' : 'low'); ?>" style="width:<?php echo $deptScore; ?>%"></div>
                                    </div>
                                    <span class="score-num"><?php echo $deptScore; ?>%</span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($departmentData)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">No department data available.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Teacher Performance Table -->
    <div class="analytics-card">
        <div class="analytics-card-header d-flex justify-content-between align-items-center">
            <h5><i class="fas fa-chalkboard-teacher me-2"></i>Teacher Performance in <?php echo htmlspecialchars($teacherDept ?: 'Your Department'); ?></h5>
            <button class="btn btn-sm btn-outline-secondary" onclick="exportTableToCSV('teacher-table', 'teacher_performance.csv')">
                <i class="fas fa-download me-1"></i>Export CSV
            </button>
        </div>
        <div class="table-responsive">
            <table class="analytics-table" id="teacher-table">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Teacher</th>
                        <th class="text-center">Avg Rating</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Good</th>
                        <th class="text-center">Neutral</th>
                        <th class="text-center">Bad</th>
                        <th class="text-center" width="160">Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teacherData as $i => $t): ?>
                        <?php 
                        $tTotal = (int)$t['total_ratings'];
                        $tScore = $tTotal > 0 ? 
                            round((($t['good_ratings'] * 3 + $t['neutral_ratings'] * 2 + $t['bad_ratings'] * 1) / ($tTotal * 3)) * 100) : 0;
                        $isMe = ((int)$t['teacher_user_id'] === (int)$teacherId);
                        ?>
                        <tr class="<?php echo $isMe ? 'row-highlight' : ''; ?>">
                            <td><span class="rank-num <?php echo $i < 3 ? 'rank-top' : ''; ?>"><?php echo $i + 1; ?></span></td>
                            <td>
                                <div class="teacher-cell">
                                    <div class="teacher-avatar"><?php echo strtoupper(substr($t['name'], 0, 1)); ?></div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($t['name']); ?></strong>
                                        <?php if ($isMe): ?><span class="badge bg-success ms-1">You</span><?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center fw-semibold"><?php echo $t['avg_rating'] ?: '—'; ?></td>
                            <td class="text-center"><?php echo $tTotal; ?></td>
                            <td class="text-center"><span class="pill good"><?php echo (int)$t['good_ratings']; ?></span></td>
                            <td class="text-center"><span class="pill neutral"><?php echo (int)$t['neutral_ratings']; ?></span></td>
                            <td class="text-center"><span class="pill bad"><?php echo (int)$t['bad_ratings']; ?></span></td>
                            <td>
                                <div class="score-row">
                                    <div class="score-track">
                                        <div class="score-bar-fill <?php echo $tScore >= 70 ? 'high' : ($tScore >= 40 ? 'mid' : 'low'); ?>" style="width:<?php echo $tScore; ?>%"></div>
                                    </div>
                                    <span class="score-num"><?php echo $tScore; ?>%</span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($teacherData)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">No teacher data available in your department.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
/* ─── Analytics Header ─── */
.analytics-header {
    background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 50%, #4f46e5 100%);
    border-radius: 16px;
    padding: 2rem 2.5rem;
    color: white;
    position: relative;
    overflow: hidden;
}
.analytics-header::before {
    content: '';
    position: absolute;
    top: -50%; right: -30%;
    width: 60%; height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
    pointer-events: none;
}
.analytics-header h1 { font-size: 1.6rem; font-weight: 700; margin: 0; color: white; }
.analytics-header p { opacity: 0.85; font-size: 0.95rem; }
.header-icon-box {
    width: 56px; height: 56px;
    background: rgba(255,255,255,0.18);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; margin-right: 1.25rem;
    backdrop-filter: blur(4px);
}

/* ─── Metric Cards ─── */
.metric-card {
    border-radius: 14px; padding: 1.25rem 1.5rem;
    color: white; position: relative; overflow: hidden;
    transition: transform 0.2s; cursor: default;
}
.metric-card:hover { transform: translateY(-3px); }
.metric-icon { font-size: 1.1rem; opacity: 0.8; margin-bottom: 0.5rem; }
.metric-value { font-size: 1.75rem; font-weight: 800; line-height: 1.2; }
.metric-label { font-size: 0.8rem; opacity: 0.85; margin-top: 2px; }
.metric-indigo { background: linear-gradient(135deg, #6366f1, #818cf8); box-shadow: 0 4px 14px rgba(99,102,241,0.35); }
.metric-violet { background: linear-gradient(135deg, #8b5cf6, #a78bfa); box-shadow: 0 4px 14px rgba(139,92,246,0.35); }
.metric-amber  { background: linear-gradient(135deg, #f59e0b, #fbbf24); box-shadow: 0 4px 14px rgba(245,158,11,0.35); }
.metric-emerald { background: linear-gradient(135deg, #10b981, #34d399); box-shadow: 0 4px 14px rgba(16,185,129,0.35); }

/* ─── Analytics Card ─── */
.analytics-card {
    background: white; border-radius: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 4px 16px rgba(0,0,0,0.04);
    overflow: hidden;
}
.analytics-card-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--gray-100, #f3f4f6);
}
.analytics-card-header h5 { margin: 0; font-size: 1rem; font-weight: 600; }
.analytics-card-body { padding: 1.5rem; }
.avg-badge {
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    color: #92400e; font-weight: 700; font-size: 0.85rem;
    padding: 4px 12px; border-radius: 20px;
}

/* ─── Doughnut Chart ─── */
.chart-wrap {
    width: 140px; height: 140px;
    position: relative; flex-shrink: 0;
}
.chart-wrap canvas { position: relative; z-index: 1; }
.chart-center-text {
    position: absolute; top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    text-align: center; z-index: 0;
}
.chart-center-value { display: block; font-size: 1.5rem; font-weight: 800; color: #1f2937; }
.chart-center-label { display: block; font-size: 0.7rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }

.chart-wrap-sm { width: 180px; height: 180px; }

/* ─── Rating Breakdown ─── */
.rating-breakdown { display: flex; flex-direction: column; gap: 1rem; }
.breakdown-item { display: flex; flex-direction: column; gap: 4px; }
.breakdown-header { display: flex; align-items: center; gap: 6px; }
.breakdown-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.breakdown-dot.good { background: #10b981; }
.breakdown-dot.neutral { background: #f59e0b; }
.breakdown-dot.bad { background: #ef4444; }
.breakdown-label { flex-grow: 1; font-size: 0.85rem; font-weight: 500; }
.breakdown-count { font-weight: 700; font-size: 0.85rem; }
.breakdown-bar { height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden; }
.breakdown-fill { height: 100%; border-radius: 3px; transition: width 0.6s ease; }
.breakdown-fill.good { background: #10b981; }
.breakdown-fill.neutral { background: #f59e0b; }
.breakdown-fill.bad { background: #ef4444; }
.breakdown-pct { font-size: 0.75rem; color: #6b7280; text-align: right; }

/* ─── Completion Legend ─── */
.completion-legend { display: flex; justify-content: center; gap: 1.5rem; }
.legend-item { display: flex; align-items: center; gap: 6px; font-size: 0.82rem; color: #374151; }
.legend-dot { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }

/* ─── Recent Ratings ─── */
.recent-list { max-height: 300px; overflow-y: auto; }
.recent-item { padding: 0.75rem 1.25rem; border-bottom: 1px solid #f3f4f6; }
.recent-item:last-child { border-bottom: none; }
.recent-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
.recent-stars i { font-size: 0.7rem; color: #d1d5db; }
.recent-stars i.filled { color: #f59e0b; }
.recent-time { font-size: 0.75rem; color: #9ca3af; }
.recent-comment { font-size: 0.8rem; color: #6b7280; margin: 0; line-height: 1.4; }

/* ─── Empty State ─── */
.empty-state-inline { text-align: center; padding: 2.5rem 1rem; color: #9ca3af; }
.empty-state-inline.small-padding { padding: 2rem 1rem; }
.empty-state-inline i { font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.4; display: block; }
.empty-state-inline p { margin: 0; font-size: 0.85rem; }

/* ─── Analytics Table ─── */
.analytics-table { width: 100%; border-collapse: collapse; }
.analytics-table thead th {
    padding: 0.85rem 1.25rem; font-size: 0.78rem; font-weight: 600;
    color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px;
    border-bottom: 2px solid #e5e7eb; background: #f9fafb;
}
.analytics-table tbody td {
    padding: 0.85rem 1.25rem; border-bottom: 1px solid #f3f4f6; vertical-align: middle;
}
.analytics-table tbody tr:hover { background: #f9fafb; }
.row-highlight { background: rgba(99,102,241,0.06) !important; }
.row-highlight:hover { background: rgba(99,102,241,0.1) !important; }

/* ─── Score / Rank / Pill ─── */
.rank-num {
    display: inline-flex; align-items: center; justify-content: center;
    width: 30px; height: 30px; border-radius: 50%;
    background: #e5e7eb; font-weight: 700; font-size: 0.8rem;
}
.rank-num.rank-top { background: linear-gradient(135deg, #f59e0b, #fbbf24); color: white; }

.pill {
    display: inline-block; padding: 2px 10px;
    border-radius: 6px; font-weight: 600; font-size: 0.82rem;
}
.pill.good { background: rgba(16,185,129,0.12); color: #059669; }
.pill.neutral { background: rgba(245,158,11,0.12); color: #d97706; }
.pill.bad { background: rgba(239,68,68,0.12); color: #dc2626; }

.score-row { display: flex; align-items: center; gap: 8px; }
.score-track { flex-grow: 1; height: 7px; background: #e5e7eb; border-radius: 4px; overflow: hidden; min-width: 60px; }
.score-bar-fill { height: 100%; border-radius: 4px; transition: width 0.5s ease; }
.score-bar-fill.high { background: #10b981; }
.score-bar-fill.mid { background: #f59e0b; }
.score-bar-fill.low { background: #ef4444; }
.score-num { font-weight: 700; font-size: 0.82rem; min-width: 36px; }

/* ─── Teacher Cell ─── */
.teacher-cell { display: flex; align-items: center; gap: 10px; }
.teacher-avatar {
    width: 34px; height: 34px; border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #818cf8);
    display: flex; align-items: center; justify-content: center;
    color: white; font-weight: 700; font-size: 0.8rem; flex-shrink: 0;
}

/* ─── Responsive ─── */
@media (max-width: 991px) {
    .analytics-card-body .d-flex.align-items-center.gap-4 { flex-direction: column; }
    .chart-wrap { margin: 0 auto; }
}
@media (max-width: 768px) {
    .analytics-header { padding: 1.5rem; }
    .analytics-header h1 { font-size: 1.25rem; }
    .completion-legend { flex-direction: column; align-items: center; gap: 0.5rem; }
}
</style>

<?php include '../../core/includes/footer.php'; ?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Rating Doughnut Chart ──
    const ratingCanvas = document.getElementById('ratingChart');
    if (ratingCanvas && typeof Chart !== 'undefined') {
        const good = <?php echo $ratings['good']; ?>;
        const neutral = <?php echo $ratings['neutral']; ?>;
        const bad = <?php echo $ratings['bad']; ?>;

        if (good + neutral + bad > 0) {
            new Chart(ratingCanvas, {
                type: 'doughnut',
                data: {
                    labels: ['Good (4-5)', 'Neutral (3)', 'Bad (1-2)'],
                    datasets: [{
                        data: [good, neutral, bad],
                        backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                        borderWidth: 0,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '65%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                    const pct = total > 0 ? Math.round((ctx.parsed / total) * 100) : 0;
                                    return ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    // ── Completion Bar Chart ──
    const completionCanvas = document.getElementById('completionChart');
    if (completionCanvas && typeof Chart !== 'undefined') {
        const studentsComp = <?php echo $studentsCompleted; ?>;
        const totalStud = <?php echo $totalStudents; ?>;
        const teachersComp = <?php echo $stats['teachers_completed']; ?>;
        const totalTeach = <?php echo $stats['total_teachers']; ?>;

        new Chart(completionCanvas, {
            type: 'bar',
            data: {
                labels: ['Students', 'Teachers'],
                datasets: [{
                    label: 'Completed',
                    data: [studentsComp, teachersComp],
                    backgroundColor: ['#10b981', '#8b5cf6'],
                    borderRadius: 6
                }, {
                    label: 'Remaining',
                    data: [
                        Math.max(0, totalStud - studentsComp),
                        Math.max(0, totalTeach - teachersComp)
                    ],
                    backgroundColor: ['rgba(16,185,129,0.15)', 'rgba(139,92,246,0.15)'],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    x: { stacked: true, grid: { display: false } },
                    y: { stacked: true, beginAtZero: true, ticks: { maxTicksLimit: 6 } }
                },
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16, font: { size: 11 } } }
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
