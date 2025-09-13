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
            mysqli_stmt_bind_param($stmt, "sii", $resolution_notes, $_SESSION["user_id"], $complaint_id);
            
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
    }
}

// Fetch complaints and suggestions with resolver info
$sql = "SELECT sc.*, COALESCE(a.username, 'N/A') as resolver_username
        FROM suggestions_complaints sc
        LEFT JOIN users a ON sc.resolved_by = a.id
        ORDER BY 
            CASE 
                WHEN sc.status = 'pending' THEN 1
                WHEN sc.status = 'in_progress' THEN 2
                WHEN sc.status = 'resolved' THEN 3
            END,
            sc.created_at DESC";
$result = mysqli_query($conn, $sql);
$complaints = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $complaints[] = $row;
    }
}

// Count complaints by type and status
$pending_count = $resolved_count = $suggestions_count = $complaints_count = 0;

foreach ($complaints as $item) {
    if ($item['status'] === 'pending') {
        $pending_count++;
    } elseif ($item['status'] === 'resolved') {
        $resolved_count++;
    }
    
    if ($item['type'] === 'suggestion') {
        $suggestions_count++;
    } elseif ($item['type'] === 'complaint') {
        $complaints_count++;
    }
}

// Set page variables
$pageTitle = "Complaints & Suggestions";
$basePath = "../../";
?>

<?php include '../../core/includes/header.php'; ?>

<div class="complaints-management">
    <h2>Complaints & Suggestions Management</h2>
    <p>View and manage anonymous complaints and suggestions from students and teachers.</p>
    
    <?php if (!empty($alertMessage)): ?>
        <div class="alert alert-<?php echo $alertType; ?> alert-dismissible fade show" role="alert">
            <?php echo $alertMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Submissions</h5>
                    <h2 class="display-4"><?php echo count($complaints); ?></h2>
                    <p class="card-text">Total complaints & suggestions</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Pending</h5>
                    <h2 class="display-4"><?php echo $pending_count; ?></h2>
                    <p class="card-text">Items awaiting action</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Resolved</h5>
                    <h2 class="display-4"><?php echo $resolved_count; ?></h2>
                    <p class="card-text">Items marked as resolved</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Suggestions</h5>
                    <h2 class="display-4"><?php echo $suggestions_count; ?></h2>
                    <p class="card-text">Improvement suggestions</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="complaintsTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="all-tab" data-toggle="tab" href="#all" role="tab" aria-controls="all" aria-selected="true">All</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pending-tab" data-toggle="tab" href="#pending" role="tab" aria-controls="pending" aria-selected="false">Pending</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="resolved-tab" data-toggle="tab" href="#resolved" role="tab" aria-controls="resolved" aria-selected="false">Resolved</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="suggestions-tab" data-toggle="tab" href="#suggestions" role="tab" aria-controls="suggestions" aria-selected="false">Suggestions</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="complaints-tab" data-toggle="tab" href="#complaints" role="tab" aria-controls="complaints" aria-selected="false">Complaints</a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="complaintsTabContent">
                <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                    <?php if (!empty($complaints)): ?>
                        <?php displayComplaintsTable($complaints); ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No complaints or suggestions found.
                        </div>
                    <?php endif; ?>
                </div>
                <div class="tab-pane fade" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                    <?php
                    $filtered = array_filter($complaints, function($item) {
                        return $item['status'] === 'pending';
                    });
                    
                    if (!empty($filtered)) {
                        displayComplaintsTable($filtered);
                    } else {
                        echo '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No pending items found.</div>';
                    }
                    ?>
                </div>
                <div class="tab-pane fade" id="resolved" role="tabpanel" aria-labelledby="resolved-tab">
                    <?php
                    $filtered = array_filter($complaints, function($item) {
                        return $item['status'] === 'resolved';
                    });
                    
                    if (!empty($filtered)) {
                        displayComplaintsTable($filtered);
                    } else {
                        echo '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No resolved items found.</div>';
                    }
                    ?>
                </div>
                <div class="tab-pane fade" id="suggestions" role="tabpanel" aria-labelledby="suggestions-tab">
                    <?php
                    $filtered = array_filter($complaints, function($item) {
                        return $item['type'] === 'suggestion';
                    });
                    
                    if (!empty($filtered)) {
                        displayComplaintsTable($filtered);
                    } else {
                        echo '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No suggestions found.</div>';
                    }
                    ?>
                </div>
                <div class="tab-pane fade" id="complaints" role="tabpanel" aria-labelledby="complaints-tab">
                    <?php
                    $filtered = array_filter($complaints, function($item) {
                        return $item['type'] === 'complaint';
                    });
                    
                    if (!empty($filtered)) {
                        displayComplaintsTable($filtered);
                    } else {
                        echo '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No complaints found.</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewDetailsModal" tabindex="-1"  aria-labelledby="viewDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" >
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewDetailsModalLabel">Complaint/Suggestion Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Submission Details</h6>
                        <table class="table table-bordered">
                            <tr>
                                <th>ID:</th>
                                <td id="view_id"></td>
                            </tr>
                            <tr>
                                <th>Type:</th>
                                <td id="view_type"></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td id="view_status"></td>
                            </tr>
                            <tr>
                                <th>Submitted By:</th>
                                <td id="view_role"></td>
                            </tr>
                            <tr>
                                <th>Submitted On:</th>
                                <td id="view_created_at"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6" id="resolution_details_section">
                        <h6>Resolution Details</h6>
                        <table class="table table-bordered">
                            <tr>
                                <th>Resolved By:</th>
                                <td id="view_resolved_by"></td>
                            </tr>
                            <tr>
                                <th>Resolved On:</th>
                                <td id="view_resolved_at"></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <h6>Subject</h6>
                <p id="view_subject" class="font-weight-bold"></p>
                
                <h6>Description</h6>
                <div id="view_description" class="p-3 bg-light rounded"></div>
                
                <div id="resolution_notes_section" class="mt-3">
                    <h6>Resolution Notes</h6>
                    <div id="view_resolution_notes" class="p-3 bg-light rounded"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Resolve Modal -->
<div class="modal fade" id="resolveModal" tabindex="-1"  aria-labelledby="resolveModalLabel" aria-hidden="true">
    <div class="modal-dialog" >
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resolveModalLabel">Resolve Complaint/Suggestion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="modal-body">
                    <input type="hidden" name="mark_resolved" value="1">
                    <input type="hidden" name="complaint_id" id="resolve_complaint_id">
                    
                    <p>You are about to mark the following item as resolved:</p>
                    <p id="resolve_subject" class="font-weight-bold"></p>
                    
                    <div class="form-group">
                        <label for="resolution_notes">Resolution Notes</label>
                        <textarea class="form-control" id="resolution_notes" name="resolution_notes" rows="5" required></textarea>
                        <small class="form-text text-muted">Provide details on how this issue was resolved or what actions were taken.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Mark as Resolved</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Mark as Pending Modal -->
<div class="modal fade" id="pendingModal" tabindex="-1"  aria-labelledby="pendingModalLabel" aria-hidden="true">
    <div class="modal-dialog" >
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pendingModalLabel">Mark as Pending</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to mark this item as pending again?</p>
                <p>This will clear all resolution data including notes and timestamp.</p>
                <p id="pending_subject" class="font-weight-bold"></p>
            </div>
            <div class="modal-footer">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="mark_pending" value="1">
                    <input type="hidden" name="complaint_id" id="pending_complaint_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Mark as Pending</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1"  aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" >
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete Complaint/Suggestion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to permanently delete this item?</p>
                <p>This action cannot be undone.</p>
                <p id="delete_subject" class="font-weight-bold"></p>
            </div>
            <div class="modal-footer">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="delete_complaint" value="1">
                    <input type="hidden" name="complaint_id" id="delete_complaint_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Permanently</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../core/includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTables
    $('.complaints-table').DataTable({
        order: [[0, 'desc']],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]]
    });
    
    // View Details Modal
    $(document).on('click', '.view-details-btn', function() {
        const id = $(this).data('id');
        const type = $(this).data('type');
        const subject = $(this).data('subject');
        const description = $(this).data('description');
        const status = $(this).data('status');
        const role = $(this).data('role');
        const created_at = $(this).data('created-at');
        const resolved_by = $(this).data('resolved-by');
        const resolved_at = $(this).data('resolved-at');
        const resolution_notes = $(this).data('resolution-notes');
        
        $('#view_id').text(id);
        $('#view_type').text(type.charAt(0).toUpperCase() + type.slice(1));
        $('#view_subject').text(subject);
        $('#view_description').text(description);
        $('#view_status').text(status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' '));
        $('#view_role').text(role.charAt(0).toUpperCase() + role.slice(1));
        $('#view_created_at').text(created_at);
        
        if (status === 'resolved') {
            $('#resolution_details_section').show();
            $('#resolution_notes_section').show();
            $('#view_resolved_by').text(resolved_by);
            $('#view_resolved_at').text(resolved_at);
            $('#view_resolution_notes').text(resolution_notes || 'No notes provided');
        } else {
            $('#resolution_details_section').hide();
            $('#resolution_notes_section').hide();
        }
    });
    
    // Resolve Modal
    $(document).on('click', '.resolve-btn', function() {
        const id = $(this).data('id');
        const subject = $(this).data('subject');
        
        $('#resolve_complaint_id').val(id);
        $('#resolve_subject').text(subject);
    });
    
    // Mark as Pending Modal
    $(document).on('click', '.pending-btn', function() {
        const id = $(this).data('id');
        const subject = $(this).data('subject');
        
        $('#pending_complaint_id').val(id);
        $('#pending_subject').text(subject);
    });
    
    // Delete Modal
    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        const subject = $(this).data('subject');
        
        $('#delete_complaint_id').val(id);
        $('#delete_subject').text(subject);
    });
});
</script>

<?php
// Function to display complaints table
function displayComplaintsTable($items) {
    echo '<div class="table-responsive">
            <table class="table table-hover complaints-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Subject</th>
                        <th>Submitted By</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>';
    
    foreach ($items as $item) {
        $statusClass = '';
        switch ($item['status']) {
            case 'pending':
                $statusClass = 'badge-warning';
                break;
            case 'in_progress':
                $statusClass = 'badge-info';
                break;
            case 'resolved':
                $statusClass = 'badge-success';
                break;
        }
        
        $typeClass = ($item['type'] === 'complaint') ? 'badge-danger' : 'badge-info';
        
        echo '<tr>
                <td>' . $item['id'] . '</td>
                <td><span class="badge ' . $typeClass . '">' . ucfirst($item['type']) . '</span></td>
                <td>' . htmlspecialchars($item['subject']) . '</td>
                <td>' . ucfirst($item['submitted_by_role']) . '</td>
                <td><span class="badge ' . $statusClass . '">' . ucfirst(str_replace('_', ' ', $item['status'])) . '</span></td>
                <td>' . date('M d, Y', strtotime($item['created_at'])) . '</td>
                <td>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-info view-details-btn" data-bs-toggle="modal" data-bs-target="#viewDetailsModal"
                            data-id="' . $item['id'] . '"
                            data-type="' . $item['type'] . '"
                            data-subject="' . htmlspecialchars($item['subject']) . '"
                            data-description="' . htmlspecialchars($item['description']) . '"
                            data-status="' . $item['status'] . '"
                            data-role="' . $item['submitted_by_role'] . '"
                            data-created-at="' . date('M d, Y H:i', strtotime($item['created_at'])) . '"
                            data-resolved-by="' . ($item['status'] === 'resolved' ? htmlspecialchars($item['resolver_username']) : '') . '"
                            data-resolved-at="' . ($item['resolved_at'] ? date('M d, Y H:i', strtotime($item['resolved_at'])) : '') . '"
                            data-resolution-notes="' . htmlspecialchars($item['resolution_notes'] ?? '') . '">
                            <i class="fas fa-eye"></i>
                        </button>';
        
        if ($item['status'] !== 'resolved') {
            echo '<button type="button" class="btn btn-sm btn-success resolve-btn" data-bs-toggle="modal" data-bs-target="#resolveModal"
                    data-id="' . $item['id'] . '"
                    data-subject="' . htmlspecialchars($item['subject']) . '">
                    <i class="fas fa-check"></i>
                </button>';
        } else {
            echo '<button type="button" class="btn btn-sm btn-warning pending-btn" data-bs-toggle="modal" data-bs-target="#pendingModal"
                    data-id="' . $item['id'] . '"
                    data-subject="' . htmlspecialchars($item['subject']) . '">
                    <i class="fas fa-undo"></i>
                </button>';
        }
        
        echo '<button type="button" class="btn btn-sm btn-danger delete-btn" data-bs-toggle="modal" data-bs-target="#deleteModal"
                data-id="' . $item['id'] . '"
                data-subject="' . htmlspecialchars($item['subject']) . '">
                <i class="fas fa-trash"></i>
            </button>
            </div>
        </td>
    </tr>';
    }
    
    echo '</tbody>
        </table>
    </div>';
}

// Close connection
closeConnection($conn);
?>

