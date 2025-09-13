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
$question = $target_role = "";
$question_err = $target_role_err = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_question'])) {
        // Validate question
        if (empty(trim($_POST["question"]))) {
            $question_err = "Please enter a question.";
        } else {
            $question = trim($_POST["question"]);
        }
        
        // Validate target role
        if (empty(trim($_POST["target_role"]))) {
            $target_role_err = "Please select a target role.";
        } else {
            $target_role = trim($_POST["target_role"]);
        }
        
        // Check input errors before inserting in database
        if (empty($question_err) && empty($target_role_err)) {
            // Prepare an insert statement
            $sql = "INSERT INTO survey_questions (question, target_role) VALUES (?, ?)";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "ss", $question, $target_role);
                
                if (mysqli_stmt_execute($stmt)) {
                    // Set success message
                    $alertType = "success";
                    $alertMessage = "Question added successfully.";
                    
                    // Clear input fields
                    $question = $target_role = "";
                } else {
                    $alertType = "danger";
                    $alertMessage = "Error: " . mysqli_error($conn);
                }
                
                mysqli_stmt_close($stmt);
            }
        }
    } elseif (isset($_POST['update_question'])) {
        $question_id = $_POST['question_id'];
        $question = trim($_POST['question']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($question)) {
            $alertType = "danger";
            $alertMessage = "Question cannot be empty.";
        } else {
            $sql = "UPDATE survey_questions SET question = ?, is_active = ? WHERE id = ?";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "sii", $question, $is_active, $question_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $alertType = "success";
                    $alertMessage = "Question updated successfully.";
                } else {
                    $alertType = "danger";
                    $alertMessage = "Error: " . mysqli_error($conn);
                }
                
                mysqli_stmt_close($stmt);
            }
        }
    } elseif (isset($_POST['delete_question'])) {
        $question_id = $_POST['question_id'];
        
        // Check if the question has survey responses
        $sql = "SELECT COUNT(*) as count FROM survey_responses WHERE question_id = ?";
        $has_responses = false;
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $question_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                
                if ($row = mysqli_fetch_assoc($result)) {
                    $has_responses = ($row['count'] > 0);
                }
            }
            
            mysqli_stmt_close($stmt);
        }
        
        if ($has_responses) {
            // If the question has responses, just deactivate it
            $sql = "UPDATE survey_questions SET is_active = 0 WHERE id = ?";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $question_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $alertType = "warning";
                    $alertMessage = "Question has responses and cannot be deleted. It has been deactivated instead.";
                } else {
                    $alertType = "danger";
                    $alertMessage = "Error: " . mysqli_error($conn);
                }
                
                mysqli_stmt_close($stmt);
            }
        } else {
            // If the question has no responses, delete it
            $sql = "DELETE FROM survey_questions WHERE id = ?";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $question_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $alertType = "success";
                    $alertMessage = "Question deleted successfully.";
                } else {
                    $alertType = "danger";
                    $alertMessage = "Error: " . mysqli_error($conn);
                }
                
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Fetch all student survey questions
$sql = "SELECT * FROM survey_questions WHERE target_role = 'student' ORDER BY id ASC";
$student_questions = [];
$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $student_questions[] = $row;
    }
}

// Fetch all teacher survey questions
$sql = "SELECT * FROM survey_questions WHERE target_role = 'teacher' ORDER BY id ASC";
$teacher_questions = [];
$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $teacher_questions[] = $row;
    }
}

// Set page variables
$pageTitle = "Survey Management";
$basePath = "../../";
?>

<?php include '../../core/includes/header.php'; ?>

<div class="survey-management">
    <h2>Survey Management</h2>
    <p>Add, edit, or delete survey questions for students and teachers.</p>
    
    <div class="card">
        <div class="card-header">
            <h3>Add New Question</h3>
        </div>
        <div class="card-body">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation">
                <input type="hidden" name="add_question" value="1">
                
                <div class="form-group">
                    <label for="question">Question</label>
                    <input type="text" name="question" id="question" class="form-control <?php echo (!empty($question_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($question); ?>" placeholder="Enter survey question">
                    <div class="invalid-feedback"><?php echo $question_err; ?></div>
                </div>
                
                <div class="form-group">
                    <label>Target Role</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="target_role" id="target_role_student" value="student" <?php echo ($target_role === 'student') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="target_role_student">
                            Student
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="target_role" id="target_role_teacher" value="teacher" <?php echo ($target_role === 'teacher') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="target_role_teacher">
                            Teacher
                        </label>
                    </div>
                    <div class="invalid-feedback"><?php echo $target_role_err; ?></div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Add Question</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h3>Student Survey Questions</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($student_questions)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Question</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($student_questions as $question): ?>
                                <tr>
                                    <td><?php echo $question['id']; ?></td>
                                    <td><?php echo htmlspecialchars($question['question']); ?></td>
                                    <td>
                                        <?php if ($question['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editQuestionModal" 
                                            data-id="<?php echo $question['id']; ?>" 
                                            data-question="<?php echo htmlspecialchars($question['question']); ?>" 
                                            data-active="<?php echo $question['is_active']; ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteQuestionModal" 
                                            data-id="<?php echo $question['id']; ?>" 
                                            data-question="<?php echo htmlspecialchars($question['question']); ?>">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No student survey questions found.
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h3>Teacher Survey Questions</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($teacher_questions)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Question</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($teacher_questions as $question): ?>
                                <tr>
                                    <td><?php echo $question['id']; ?></td>
                                    <td><?php echo htmlspecialchars($question['question']); ?></td>
                                    <td>
                                        <?php if ($question['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editQuestionModal" 
                                            data-id="<?php echo $question['id']; ?>" 
                                            data-question="<?php echo htmlspecialchars($question['question']); ?>" 
                                            data-active="<?php echo $question['is_active']; ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteQuestionModal" 
                                            data-id="<?php echo $question['id']; ?>" 
                                            data-question="<?php echo htmlspecialchars($question['question']); ?>">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No teacher survey questions found.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit Question Modal -->
<div class="modal fade" id="editQuestionModal" tabindex="-1"  aria-labelledby="editQuestionModalLabel" aria-hidden="true">
    <div class="modal-dialog" >
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editQuestionModalLabel">Edit Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="modal-body">
                    <input type="hidden" name="update_question" value="1">
                    <input type="hidden" name="question_id" id="edit_question_id">
                    
                    <div class="form-group">
                        <label for="edit_question_text">Question</label>
                        <input type="text" class="form-control" id="edit_question_text" name="question" required>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active">
                            <label class="form-check-label" for="edit_is_active">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Question Modal -->
<div class="modal fade" id="deleteQuestionModal" tabindex="-1"  aria-labelledby="deleteQuestionModalLabel" aria-hidden="true">
    <div class="modal-dialog" >
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteQuestionModalLabel">Delete Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this question?</p>
                <p><strong>Note:</strong> If the question has been answered in any surveys, it will be deactivated instead of deleted.</p>
                <p id="delete_question_text" class="text-danger"></p>
            </div>
            <div class="modal-footer">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="delete_question" value="1">
                    <input type="hidden" name="question_id" id="delete_question_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../core/includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit Question Modal
    $('#editQuestionModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const id = button.data('id');
        const question = button.data('question');
        const active = button.data('active');
        
        const modal = $(this);
        modal.find('#edit_question_id').val(id);
        modal.find('#edit_question_text').val(question);
        modal.find('#edit_is_active').prop('checked', active === 1);
    });
    
    // Delete Question Modal
    $('#deleteQuestionModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const id = button.data('id');
        const question = button.data('question');
        
        const modal = $(this);
        modal.find('#delete_question_id').val(id);
        modal.find('#delete_question_text').text(question);
    });
});
</script>

<?php
// Close connection
closeConnection($conn);
?>

