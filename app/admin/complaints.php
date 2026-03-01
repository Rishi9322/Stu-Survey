<?php
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

// ========================================================
// AJAX handler — must be BEFORE any HTML output
// ========================================================
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    header('Content-Type: text/html; charset=utf-8');
    
    $subject = $_GET['subject'] ?? '';
    $type = $_GET['type'] ?? '';
    
    if (empty($subject) || empty($type)) {
        echo '<div class="text-center py-3 text-danger">Invalid request.</div>';
        exit;
    }
    
    $sql = "SELECT sc.*, COALESCE(a.username, 'N/A') as resolver_username
            FROM suggestions_complaints sc
            LEFT JOIN users a ON sc.resolved_by = a.id
            WHERE sc.subject = ? AND sc.type = ?
            ORDER BY sc.created_at DESC
            LIMIT 100";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $subject, $type);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    echo '<table class="detail-table">
            <thead><tr>
                <th>ID</th>
                <th>Description</th>
                <th>Status</th>
                <th>From</th>
                <th>Date</th>
                <th>Actions</th>
            </tr></thead>
            <tbody>';
    
    while ($item = mysqli_fetch_assoc($result)) {
        $statusClass = $item['status'];
        $desc = htmlspecialchars(mb_strimwidth($item['description'], 0, 80, '...'), ENT_QUOTES);
        echo '<tr>
                <td><strong>#' . $item['id'] . '</strong></td>
                <td class="text-muted" style="max-width:300px;">' . $desc . '</td>
                <td><span class="status-badge ' . $statusClass . '">
                    <i class="fas fa-' . ($statusClass === 'resolved' ? 'check-circle' : 'clock') . ' me-1"></i>'
                    . ucfirst(str_replace('_', ' ', $item['status'])) . '</span></td>
                <td>' . ucfirst($item['submitted_by_role']) . '</td>
                <td>' . date('M d, Y', strtotime($item['created_at'])) . '</td>
                <td><div class="d-flex gap-1">
                    <button type="button" class="action-btn view view-details-btn" data-bs-toggle="modal" data-bs-target="#viewDetailsModal"
                        data-id="' . $item['id'] . '"
                        data-type="' . htmlspecialchars($item['type'], ENT_QUOTES) . '"
                        data-subject="' . htmlspecialchars($item['subject'], ENT_QUOTES) . '"
                        data-description="' . htmlspecialchars($item['description'], ENT_QUOTES) . '"
                        data-status="' . $item['status'] . '"
                        data-role="' . $item['submitted_by_role'] . '"
                        data-created-at="' . date('M d, Y H:i', strtotime($item['created_at'])) . '"
                        data-resolved-by="' . ($item['status'] === 'resolved' ? htmlspecialchars($item['resolver_username']) : '') . '"
                        data-resolved-at="' . ($item['resolved_at'] ? date('M d, Y H:i', strtotime($item['resolved_at'])) : '') . '"
                        data-resolution-notes="' . htmlspecialchars($item['resolution_notes'] ?? '', ENT_QUOTES) . '"
                        title="View"><i class="fas fa-eye"></i></button>';
        
        if ($item['status'] !== 'resolved') {
            echo '<button type="button" class="action-btn resolve resolve-btn" data-bs-toggle="modal" data-bs-target="#resolveModal"
                    data-id="' . $item['id'] . '"
                    data-subject="' . htmlspecialchars($item['subject'], ENT_QUOTES) . '"
                    title="Resolve"><i class="fas fa-check"></i></button>';
        } else {
            echo '<button type="button" class="action-btn reopen pending-btn" data-bs-toggle="modal" data-bs-target="#pendingModal"
                    data-id="' . $item['id'] . '"
                    data-subject="' . htmlspecialchars($item['subject'], ENT_QUOTES) . '"
                    title="Reopen"><i class="fas fa-undo"></i></button>';
        }
        
        echo '<button type="button" class="action-btn delete delete-btn" data-bs-toggle="modal" data-bs-target="#deleteModal"
                data-id="' . $item['id'] . '"
                data-subject="' . htmlspecialchars($item['subject'], ENT_QUOTES) . '"
                title="Delete"><i class="fas fa-trash"></i></button>
            </div></td>
            </tr>';
    }
    
    echo '</tbody></table>';
    
    mysqli_stmt_close($stmt);
    closeConnection($conn);
    exit;
}

// ========================================================
// Normal page load
// ========================================================

// Initialize variables
$alertType = $alertMessage = "";

// Process complaint actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['mark_resolved'])) {
        $complaint_id = $_POST['complaint_id'];
        $resolution_notes = trim($_POST['resolution_notes']);
        
        $sql = "UPDATE suggestions_complaints SET 
                status = 'resolved', 
                resolution_notes = ?, 
                resolved_at = NOW(), 
                resolved_by = ? 
                WHERE id = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "sii", $resolution_notes, $_SESSION["id"], $complaint_id);
            if (mysqli_stmt_execute($stmt)) {
                $alertType = "success";
                $alertMessage = "Complaint/suggestion marked as resolved.";
            } else {
                $alertType = "danger";
                $alertMessage = "Error: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    } elseif (isset($_POST['mark_pending'])) {
        $complaint_id = $_POST['complaint_id'];
        
        $sql = "UPDATE suggestions_complaints SET 
                status = 'pending', 
                resolution_notes = NULL, 
                resolved_at = NULL, 
                resolved_by = NULL 
                WHERE id = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $complaint_id);
            if (mysqli_stmt_execute($stmt)) {
                $alertType = "success";
                $alertMessage = "Complaint/suggestion marked as pending.";
            } else {
                $alertType = "danger";
                $alertMessage = "Error: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    } elseif (isset($_POST['delete_complaint'])) {
        $complaint_id = $_POST['complaint_id'];
        
        $sql = "DELETE FROM suggestions_complaints WHERE id = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $complaint_id);
            if (mysqli_stmt_execute($stmt)) {
                $alertType = "success";
                $alertMessage = "Complaint/suggestion deleted successfully.";
            } else {
                $alertType = "danger";
                $alertMessage = "Error: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    } elseif (isset($_POST['bulk_resolve'])) {
        $subject = $_POST['bulk_subject'];
        $type = $_POST['bulk_type'];
        $resolution_notes = "Bulk resolved by admin.";
        
        $sql = "UPDATE suggestions_complaints SET 
                status = 'resolved', resolution_notes = ?, resolved_at = NOW(), resolved_by = ? 
                WHERE subject = ? AND type = ? AND status != 'resolved'";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "siss", $resolution_notes, $_SESSION["id"], $subject, $type);
            if (mysqli_stmt_execute($stmt)) {
                $affected = mysqli_stmt_affected_rows($stmt);
                $alertType = "success";
                $alertMessage = "Bulk resolved $affected item(s) for \"" . htmlspecialchars($subject) . "\".";
            } else {
                $alertType = "danger";
                $alertMessage = "Error: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    } elseif (isset($_POST['bulk_delete'])) {
        $subject = $_POST['bulk_subject'];
        $type = $_POST['bulk_type'];
        
        $sql = "DELETE FROM suggestions_complaints WHERE subject = ? AND type = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ss", $subject, $type);
            if (mysqli_stmt_execute($stmt)) {
                $affected = mysqli_stmt_affected_rows($stmt);
                $alertType = "success";
                $alertMessage = "Deleted $affected item(s) for \"" . htmlspecialchars($subject) . "\".";
            } else {
                $alertType = "danger";
                $alertMessage = "Error: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Mark all feedback notifications as read
markAllNotificationsRead($conn);

// SQL-level aggregate counts (fast — no loading all rows)
$countSql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
    SUM(CASE WHEN type = 'suggestion' THEN 1 ELSE 0 END) as suggestions,
    SUM(CASE WHEN type = 'complaint' THEN 1 ELSE 0 END) as complaints
    FROM suggestions_complaints";
$countResult = mysqli_query($conn, $countSql);
$counts = mysqli_fetch_assoc($countResult);
$total_count = (int)$counts['total'];
$pending_count = (int)$counts['pending'];
$resolved_count = (int)$counts['resolved'];
$suggestions_count = (int)$counts['suggestions'];
$complaints_count = (int)$counts['complaints'];

// Current filter tab
$currentTab = isset($_GET['tab']) ? $_GET['tab'] : 'all';

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 30;
$offset = ($page - 1) * $perPage;

// Build WHERE clause based on active tab
$whereClause = "1=1";
switch ($currentTab) {
    case 'pending':     $whereClause = "sc.status = 'pending'"; break;
    case 'resolved':    $whereClause = "sc.status = 'resolved'"; break;
    case 'suggestions': $whereClause = "sc.type = 'suggestion'"; break;
    case 'complaints':  $whereClause = "sc.type = 'complaint'"; break;
}

// Grouped query — aggregate by subject + type
$groupSql = "SELECT 
    sc.subject, sc.type,
    COUNT(*) as item_count,
    SUM(CASE WHEN sc.status = 'pending' THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN sc.status = 'resolved' THEN 1 ELSE 0 END) as resolved_count,
    GROUP_CONCAT(DISTINCT sc.submitted_by_role ORDER BY sc.submitted_by_role) as roles,
    MAX(sc.created_at) as latest_date,
    MIN(sc.id) as first_id
    FROM suggestions_complaints sc
    WHERE $whereClause
    GROUP BY sc.subject, sc.type
    ORDER BY MAX(sc.created_at) DESC
    LIMIT $perPage OFFSET $offset";
$groupResult = mysqli_query($conn, $groupSql);
$groups = [];
if ($groupResult) {
    while ($row = mysqli_fetch_assoc($groupResult)) {
        $groups[] = $row;
    }
}

// Total groups for pagination
$totalGroupsSql = "SELECT COUNT(*) as cnt FROM (
    SELECT sc.subject, sc.type FROM suggestions_complaints sc
    WHERE $whereClause GROUP BY sc.subject, sc.type
) AS grouped";
$totalGroupsResult = mysqli_query($conn, $totalGroupsSql);
$totalGroups = mysqli_fetch_assoc($totalGroupsResult)['cnt'];
$totalPages = max(1, ceil($totalGroups / $perPage));

// Set page variables
$pageTitle = "Complaints & Suggestions";
$basePath = "../../";
?>

<?php include '../../core/includes/header.php'; ?>

<!-- Stitch-inspired modern CSS -->
<style>
/* ===== Page Header ===== */
.feedback-hero {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #991b1b 100%);
    border-radius: 20px;
    padding: 2.5rem 2.5rem 2rem;
    color: white;
    position: relative;
    overflow: hidden;
    margin-bottom: 2rem;
}

.feedback-hero::before {
    content: '';
    position: absolute;
    top: -60px;
    right: -60px;
    width: 200px;
    height: 200px;
    background: rgba(255,255,255,0.08);
    border-radius: 50%;
}

.feedback-hero::after {
    content: '';
    position: absolute;
    bottom: -40px;
    left: 30%;
    width: 150px;
    height: 150px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
}

.feedback-hero h1 {
    color: white;
    font-size: 2rem;
    font-weight: 800;
    margin: 0 0 0.5rem;
    position: relative;
    z-index: 1;
}

.feedback-hero p {
    color: rgba(255,255,255,0.85);
    font-size: 1rem;
    margin: 0;
    position: relative;
    z-index: 1;
}

.hero-icon {
    width: 64px;
    height: 64px;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-right: 1.5rem;
    flex-shrink: 0;
}

/* ===== Stat Cards ===== */
.stat-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
    margin-bottom: 2rem;
}

.stat-card-v2 {
    border-radius: 16px;
    padding: 1.5rem;
    color: white;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card-v2::before {
    content: '';
    position: absolute;
    top: -30px;
    right: -30px;
    width: 80px;
    height: 80px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
}

.stat-card-v2:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.15);
}

.stat-card-v2.purple  { background: linear-gradient(135deg, #7c3aed, #8b5cf6); }
.stat-card-v2.amber   { background: linear-gradient(135deg, #d97706, #f59e0b); }
.stat-card-v2.emerald { background: linear-gradient(135deg, #059669, #10b981); }
.stat-card-v2.sky     { background: linear-gradient(135deg, #0284c7, #0ea5e9); }

.stat-card-v2 .stat-icon-v2 {
    width: 48px;
    height: 48px;
    background: rgba(255,255,255,0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.stat-card-v2 h3 { margin: 0; font-size: 1.75rem; font-weight: 800; }
.stat-card-v2 p  { margin: 0; opacity: 0.85; font-size: 0.8rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }

/* ===== Main Card ===== */
.feedback-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.06);
    border: 1px solid #f1f5f9;
    overflow: hidden;
}

/* ===== Tab Navigation ===== */
.tab-nav {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    background: #fafbfc;
    border-bottom: 1px solid #e2e8f0;
    overflow-x: auto;
}

.tab-nav li { flex-shrink: 0; }

.tab-nav a {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 1.5rem;
    color: #64748b;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.875rem;
    border-bottom: 3px solid transparent;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.tab-nav a:hover { color: #dc2626; background: #fff5f5; }
.tab-nav a.active { color: #dc2626; border-bottom-color: #dc2626; background: white; }

.tab-count {
    background: #e2e8f0;
    color: #475569;
    padding: 0.15rem 0.6rem;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 700;
}

.tab-nav a.active .tab-count { background: #fecaca; color: #dc2626; }

/* ===== Table ===== */
.feedback-table {
    width: 100%;
    margin: 0;
    border-collapse: collapse;
}

.feedback-table thead th {
    padding: 0.875rem 1.25rem;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #94a3b8;
    border-bottom: 1px solid #e2e8f0;
    background: white;
}

.feedback-table tbody td {
    padding: 1rem 1.25rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.9rem;
}

.feedback-table tbody tr.group-row {
    transition: background 0.2s ease;
}

.feedback-table tbody tr.group-row:hover {
    background: #f8fafc;
}

/* ===== Badges ===== */
.badge-type {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.3rem 0.8rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-type.complaint { background: #fef2f2; color: #dc2626; }
.badge-type.suggestion { background: #eff6ff; color: #2563eb; }

.badge-status {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.2rem 0.6rem;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
    margin-right: 0.25rem;
}

.badge-status.pending  { background: #fffbeb; color: #d97706; }
.badge-status.resolved { background: #ecfdf5; color: #059669; }

.badge-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #7c3aed, #a78bfa);
    color: white;
    border-radius: 50px;
    padding: 0.25rem 0.75rem;
    font-weight: 800;
    font-size: 0.85rem;
    min-width: 42px;
    box-shadow: 0 2px 8px rgba(124,58,237,0.25);
}

/* ===== Action Buttons ===== */
.action-btn {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.85rem;
}

.action-btn.view   { background: #eff6ff; color: #2563eb; }
.action-btn.resolve { background: #ecfdf5; color: #059669; }
.action-btn.reopen { background: #fffbeb; color: #d97706; }
.action-btn.delete  { background: #fef2f2; color: #dc2626; }
.action-btn:hover   { transform: scale(1.15); box-shadow: 0 2px 8px rgba(0,0,0,0.1); }

/* ===== Detail Expansion ===== */
.detail-container {
    background: #f8fafc;
    border-top: 2px solid #e2e8f0;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from { opacity: 0; max-height: 0; }
    to   { opacity: 1; max-height: 2000px; }
}

.detail-table {
    width: 100%;
    margin: 0;
    border-collapse: collapse;
}

.detail-table thead th {
    background: #eef2ff;
    padding: 0.6rem 1.25rem;
    font-size: 0.7rem;
    font-weight: 700;
    color: #6366f1;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-table tbody td {
    padding: 0.65rem 1.25rem;
    font-size: 0.85rem;
    border-bottom: 1px solid #e2e8f0;
}

.detail-table tbody tr:hover { background: #eef2ff; }

/* ===== Pagination ===== */
.pagination-wrapper {
    padding: 1.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    border-top: 1px solid #f1f5f9;
}

.page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    background: white;
    color: #475569;
    font-weight: 600;
    font-size: 0.85rem;
    text-decoration: none;
    transition: all 0.2s ease;
    padding: 0 0.5rem;
}

.page-btn:hover { border-color: #7c3aed; color: #7c3aed; background: #f5f3ff; }
.page-btn.active { background: #7c3aed; color: white; border-color: #7c3aed; }
.page-btn.disabled { opacity: 0.4; pointer-events: none; }

/* ===== Empty State ===== */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #94a3b8;
}

.empty-state i { font-size: 3.5rem; margin-bottom: 1rem; opacity: 0.4; }
.empty-state h5 { color: #475569; font-weight: 700; }

/* ===== Responsive ===== */
@media (max-width: 992px) {
    .stat-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
    .feedback-hero { padding: 1.5rem; border-radius: 16px; }
    .feedback-hero h1 { font-size: 1.5rem; }
    .hero-icon { width: 50px; height: 50px; }
    .stat-grid { grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
    .stat-card-v2 { padding: 1rem; }
    .stat-card-v2 h3 { font-size: 1.25rem; }
}

@media (max-width: 576px) {
    .stat-grid { grid-template-columns: 1fr 1fr; }
}
</style>

<div class="container py-4">
    <!-- Hero Header -->
    <div class="feedback-hero">
        <div class="d-flex align-items-center justify-content-between flex-wrap">
            <div class="d-flex align-items-center">
                <div class="hero-icon">
                    <i class="fas fa-comment-dots"></i>
                </div>
                <div>
                    <h1>Complaints & Suggestions</h1>
                    <p>Centralized hub for managing student voices and improving campus life.</p>
                </div>
            </div>
            <a href="dashboard.php" class="btn btn-light mt-3 mt-md-0" style="border-radius:12px;font-weight:600;">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <?php if (!empty($alertMessage)): ?>
    <div class="alert alert-<?php echo $alertType; ?> alert-dismissible fade show" role="alert" style="border-radius:14px;">
        <i class="fas fa-<?php echo $alertType === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
        <?php echo $alertMessage; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Stats Row -->
    <div class="stat-grid">
        <div class="stat-card-v2 purple">
            <div class="stat-icon-v2"><i class="fas fa-inbox"></i></div>
            <div><h3><?php echo number_format($total_count); ?></h3><p>Total Submissions</p></div>
        </div>
        <div class="stat-card-v2 amber">
            <div class="stat-icon-v2"><i class="fas fa-clock"></i></div>
            <div><h3><?php echo number_format($pending_count); ?></h3><p>Pending Review</p></div>
        </div>
        <div class="stat-card-v2 emerald">
            <div class="stat-icon-v2"><i class="fas fa-check-circle"></i></div>
            <div><h3><?php echo number_format($resolved_count); ?></h3><p>Resolved Cases</p></div>
        </div>
        <div class="stat-card-v2 sky">
            <div class="stat-icon-v2"><i class="fas fa-lightbulb"></i></div>
            <div><h3><?php echo number_format($suggestions_count); ?></h3><p>Suggestions</p></div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="feedback-card">
        <!-- Tabs -->
        <ul class="tab-nav">
            <?php
            $tabs = [
                'all'         => ['icon' => 'layer-group', 'label' => 'All',         'count' => $total_count],
                'pending'     => ['icon' => 'clock',       'label' => 'Pending',     'count' => $pending_count],
                'resolved'    => ['icon' => 'check-double','label' => 'Resolved',    'count' => $resolved_count],
                'suggestions' => ['icon' => 'lightbulb',   'label' => 'Suggestions', 'count' => $suggestions_count],
                'complaints'  => ['icon' => 'flag',        'label' => 'Complaints',  'count' => $complaints_count],
            ];
            foreach ($tabs as $tabKey => $tab): ?>
            <li>
                <a class="<?php echo $currentTab === $tabKey ? 'active' : ''; ?>" 
                   href="?tab=<?php echo $tabKey; ?>">
                    <i class="fas fa-<?php echo $tab['icon']; ?>"></i>
                    <?php echo $tab['label']; ?>
                    <span class="tab-count"><?php echo number_format($tab['count']); ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>

        <!-- Table -->
        <?php if (!empty($groups)): ?>
        <div class="table-responsive">
            <table class="feedback-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Type</th>
                        <th>Count</th>
                        <th>Status</th>
                        <th>From</th>
                        <th>Latest</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($groups as $group): 
                    $typeClass = $group['type'];
                    $safeSubject = htmlspecialchars($group['subject'], ENT_QUOTES);
                    $safeType = htmlspecialchars($group['type'], ENT_QUOTES);
                ?>
                    <tr class="group-row">
                        <td><strong><?php echo $safeSubject; ?></strong></td>
                        <td>
                            <span class="badge-type <?php echo $typeClass; ?>">
                                <i class="fas fa-<?php echo $typeClass === 'complaint' ? 'flag' : 'lightbulb'; ?>"></i>
                                <?php echo ucfirst($group['type']); ?>
                            </span>
                        </td>
                        <td><span class="badge-count">&times;<?php echo $group['item_count']; ?></span></td>
                        <td>
                            <?php if ($group['pending_count'] > 0): ?>
                                <span class="badge-status pending"><i class="fas fa-clock"></i> <?php echo $group['pending_count']; ?> pending</span>
                            <?php endif; ?>
                            <?php if ($group['resolved_count'] > 0): ?>
                                <span class="badge-status resolved"><i class="fas fa-check-circle"></i> <?php echo $group['resolved_count']; ?> resolved</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="text-muted"><?php echo ucfirst(str_replace(',', ', ', $group['roles'])); ?></span></td>
                        <td><span class="text-muted"><?php echo date('M d, Y', strtotime($group['latest_date'])); ?></span></td>
                        <td>
                            <div class="d-flex gap-1 flex-nowrap">
                                <button type="button" class="action-btn view expand-btn" 
                                        data-subject="<?php echo $safeSubject; ?>" 
                                        data-type="<?php echo $safeType; ?>"
                                        title="View Items">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                <?php if ($group['pending_count'] > 0): ?>
                                <form method="post" style="display:inline;" onsubmit="return confirm('Resolve all <?php echo $group['pending_count']; ?> pending items?');">
                                    <input type="hidden" name="bulk_resolve" value="1">
                                    <input type="hidden" name="bulk_subject" value="<?php echo $safeSubject; ?>">
                                    <input type="hidden" name="bulk_type" value="<?php echo $safeType; ?>">
                                    <button type="submit" class="action-btn resolve" title="Resolve All"><i class="fas fa-check-double"></i></button>
                                </form>
                                <?php endif; ?>
                                <form method="post" style="display:inline;" onsubmit="return confirm('DELETE all <?php echo $group['item_count']; ?> items? This cannot be undone!');">
                                    <input type="hidden" name="bulk_delete" value="1">
                                    <input type="hidden" name="bulk_subject" value="<?php echo $safeSubject; ?>">
                                    <input type="hidden" name="bulk_type" value="<?php echo $safeType; ?>">
                                    <button type="submit" class="action-btn delete" title="Delete All"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <tr class="detail-row" data-subject="<?php echo $safeSubject; ?>" data-type="<?php echo $safeType; ?>" style="display:none;">
                        <td colspan="7" class="p-0">
                            <div class="detail-container">
                                <div class="detail-loading text-center py-3 text-muted">
                                    <i class="fas fa-spinner fa-spin me-2"></i>Loading items...
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination-wrapper">
            <a class="page-btn <?php echo $page <= 1 ? 'disabled' : ''; ?>" href="?tab=<?php echo $currentTab; ?>&page=<?php echo $page - 1; ?>">
                <i class="fas fa-chevron-left"></i>
            </a>
            <?php 
            $startP = max(1, $page - 2);
            $endP = min($totalPages, $page + 2);
            if ($startP > 1): ?>
                <a class="page-btn" href="?tab=<?php echo $currentTab; ?>&page=1">1</a>
                <?php if ($startP > 2): ?><span class="page-btn disabled">…</span><?php endif; ?>
            <?php endif;
            for ($i = $startP; $i <= $endP; $i++): ?>
                <a class="page-btn <?php echo $i === $page ? 'active' : ''; ?>" href="?tab=<?php echo $currentTab; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
            <?php endfor;
            if ($endP < $totalPages): ?>
                <?php if ($endP < $totalPages - 1): ?><span class="page-btn disabled">…</span><?php endif; ?>
                <a class="page-btn" href="?tab=<?php echo $currentTab; ?>&page=<?php echo $totalPages; ?>"><?php echo $totalPages; ?></a>
            <?php endif; ?>
            <a class="page-btn <?php echo $page >= $totalPages ? 'disabled' : ''; ?>" href="?tab=<?php echo $currentTab; ?>&page=<?php echo $page + 1; ?>">
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>
        <p class="text-center text-muted small pb-2">Showing page <?php echo $page; ?> of <?php echo $totalPages; ?> &middot; <?php echo number_format($totalGroups); ?> grouped items</p>
        <?php endif; ?>

        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h5>No Feedback Found</h5>
            <p>There are no complaints or suggestions matching this filter.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:20px;overflow:hidden;">
            <div class="modal-header" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                <h5 class="modal-title"><i class="fas fa-eye me-2" style="color:#6366f1;"></i>Feedback Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded" style="background:#f8fafc;">
                            <h6 class="text-muted small mb-2">SUBMISSION INFO</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr><td class="text-muted">ID</td><td class="fw-bold" id="view_id"></td></tr>
                                <tr><td class="text-muted">Type</td><td id="view_type"></td></tr>
                                <tr><td class="text-muted">Status</td><td id="view_status"></td></tr>
                                <tr><td class="text-muted">From</td><td id="view_role"></td></tr>
                                <tr><td class="text-muted">Date</td><td id="view_created_at"></td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6" id="resolution_details_section">
                        <div class="p-3 rounded" style="background:#ecfdf5;">
                            <h6 class="text-muted small mb-2">RESOLUTION</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr><td class="text-muted">By</td><td class="fw-bold" id="view_resolved_by"></td></tr>
                                <tr><td class="text-muted">On</td><td id="view_resolved_at"></td></tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <h6 class="text-muted small">SUBJECT</h6>
                    <p id="view_subject" class="fw-bold fs-5"></p>
                </div>
                <div class="mt-2">
                    <h6 class="text-muted small">DESCRIPTION</h6>
                    <div id="view_description" class="p-3 rounded" style="background:#f8fafc;min-height:60px;"></div>
                </div>
                <div id="resolution_notes_section" class="mt-3">
                    <h6 class="text-muted small">RESOLUTION NOTES</h6>
                    <div id="view_resolution_notes" class="p-3 rounded" style="background:#ecfdf5;"></div>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #e2e8f0;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius:10px;">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Resolve Modal -->
<div class="modal fade" id="resolveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:20px;overflow:hidden;">
            <div class="modal-header" style="background:#ecfdf5;">
                <h5 class="modal-title"><i class="fas fa-check-circle me-2" style="color:#059669;"></i>Resolve Feedback</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="modal-body">
                    <input type="hidden" name="mark_resolved" value="1">
                    <input type="hidden" name="complaint_id" id="resolve_complaint_id">
                    <p>Resolving: <strong id="resolve_subject"></strong></p>
                    <div class="mb-3">
                        <label for="resolution_notes" class="form-label fw-bold">Resolution Notes</label>
                        <textarea class="form-control" id="resolution_notes" name="resolution_notes" rows="4" required style="border-radius:12px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                    <button type="submit" class="btn btn-success" style="border-radius:10px;">Mark Resolved</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Mark as Pending Modal -->
<div class="modal fade" id="pendingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:20px;overflow:hidden;">
            <div class="modal-header" style="background:#fffbeb;">
                <h5 class="modal-title"><i class="fas fa-undo me-2" style="color:#d97706;"></i>Reopen Feedback</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Reopen this item and clear resolution data?</p>
                <p class="fw-bold" id="pending_subject"></p>
            </div>
            <div class="modal-footer">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="mark_pending" value="1">
                    <input type="hidden" name="complaint_id" id="pending_complaint_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                    <button type="submit" class="btn btn-warning" style="border-radius:10px;">Reopen</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:20px;overflow:hidden;">
            <div class="modal-header" style="background:#fef2f2;">
                <h5 class="modal-title"><i class="fas fa-trash me-2" style="color:#dc2626;"></i>Delete Feedback</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Permanently delete this item? <strong>This cannot be undone.</strong></p>
                <p class="fw-bold" id="delete_subject"></p>
            </div>
            <div class="modal-footer">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="delete_complaint" value="1">
                    <input type="hidden" name="complaint_id" id="delete_complaint_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                    <button type="submit" class="btn btn-danger" style="border-radius:10px;">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../core/includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ---- Expand/Collapse group details ----
    document.querySelectorAll('.expand-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var subject = this.getAttribute('data-subject');
            var type = this.getAttribute('data-type');
            var icon = this.querySelector('i');
            var detailRow = document.querySelector('.detail-row[data-subject="' + CSS.escape(subject) + '"][data-type="' + CSS.escape(type) + '"]');
            
            if (!detailRow) return;
            
            if (detailRow.style.display === 'none') {
                detailRow.style.display = '';
                icon.className = 'fas fa-chevron-up';
                
                var container = detailRow.querySelector('.detail-container');
                if (container.querySelector('.detail-loading')) {
                    loadGroupDetails(subject, type, container);
                }
            } else {
                detailRow.style.display = 'none';
                icon.className = 'fas fa-chevron-down';
            }
        });
    });
    
    function loadGroupDetails(subject, type, container) {
        var params = new URLSearchParams({ ajax: '1', subject: subject, type: type });
        
        fetch('complaints.php?' + params.toString())
            .then(function(r) { return r.text(); })
            .then(function(html) {
                container.innerHTML = html;
                if (typeof jQuery !== 'undefined') bindModalButtons(container);
            })
            .catch(function() {
                container.innerHTML = '<div class="text-center py-3 text-danger"><i class="fas fa-exclamation-circle me-2"></i>Failed to load.</div>';
            });
    }
    
    // ---- Modal button binding ----
    function bindModalButtons(scope) {
        var $ = jQuery;
        
        $(scope).find('.view-details-btn').on('click', function() {
            var b = $(this);
            $('#view_id').text(b.data('id'));
            $('#view_type').text(b.data('type'));
            $('#view_subject').text(b.data('subject'));
            $('#view_description').text(b.data('description'));
            $('#view_status').text(b.data('status'));
            $('#view_role').text(b.data('role'));
            $('#view_created_at').text(b.data('created-at'));
            
            if (b.data('status') === 'resolved') {
                $('#resolution_details_section').show();
                $('#resolution_notes_section').show();
                $('#view_resolved_by').text(b.data('resolved-by'));
                $('#view_resolved_at').text(b.data('resolved-at'));
                $('#view_resolution_notes').text(b.data('resolution-notes') || 'No notes');
            } else {
                $('#resolution_details_section').hide();
                $('#resolution_notes_section').hide();
            }
        });
        
        $(scope).find('.resolve-btn').on('click', function() {
            $('#resolve_complaint_id').val($(this).data('id'));
            $('#resolve_subject').text($(this).data('subject'));
        });
        
        $(scope).find('.pending-btn').on('click', function() {
            $('#pending_complaint_id').val($(this).data('id'));
            $('#pending_subject').text($(this).data('subject'));
        });
        
        $(scope).find('.delete-btn').on('click', function() {
            $('#delete_complaint_id').val($(this).data('id'));
            $('#delete_subject').text($(this).data('subject'));
        });
    }
    
    if (typeof jQuery !== 'undefined') bindModalButtons(document);
});
</script>

<?php closeConnection($conn); ?>
