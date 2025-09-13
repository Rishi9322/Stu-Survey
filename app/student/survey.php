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

// Check if the student has completed the survey
$surveyCompleted = isSurveyCompleted($_SESSION["id"], "student", $conn);

// Get survey questions
$questions = getSurveyQuestions("student", $conn);

// Get all teachers for rating
$teachers = getAllTeachers($conn);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$surveyCompleted) {
    // Begin transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Process survey responses
        if (isset($_POST['question'])) {
            $responses = [];
            foreach ($_POST['question'] as $questionId => $rating) {
                $responses[$questionId] = $rating;
            }
            
            // Submit survey responses
            if (!empty($responses)) {
                if (!submitSurveyResponses($_SESSION["id"], $responses, $conn)) {
                    throw new Exception("Failed to submit survey responses.");
                }
            }
        }
        
        // Process teacher ratings
        if (isset($_POST['teacher'])) {
            foreach ($_POST['teacher'] as $teacherId => $rating) {
                $comment = $_POST['teacher_comment'][$teacherId] ?? '';
                
                $sql = "INSERT INTO teacher_ratings (student_id, teacher_id, rating, comment) VALUES (?, ?, ?, ?)";
                
                if ($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "iiss", $_SESSION["id"], $teacherId, $rating, $comment);
                    
                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception(mysqli_error($conn));
                    }
                    
                    mysqli_stmt_close($stmt);
                } else {
                    throw new Exception(mysqli_error($conn));
                }
            }
        }
        
        // Process suggestions and complaints
        $suggestion = trim($_POST['suggestion'] ?? '');
        $complaint = trim($_POST['complaint'] ?? '');
        
        if (!empty($suggestion)) {
            if (!submitFeedback($_SESSION["id"], "suggestion", $suggestion, $conn)) {
                throw new Exception("Failed to submit suggestion.");
            }
        }
        
        if (!empty($complaint)) {
            if (!submitFeedback($_SESSION["id"], "complaint", $complaint, $conn)) {
                throw new Exception("Failed to submit complaint.");
            }
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        // Set success message
        $alertType = "success";
        $alertMessage = "Your survey has been submitted successfully. Thank you for your feedback!";
        
        // Update survey completion status
        $surveyCompleted = true;
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        $alertType = "danger";
        $alertMessage = "Error: " . $e->getMessage();
    }
}

// Set page variables
$pageTitle = "Student Survey";
$basePath = "../../";
?>

<?php include '../../core/includes/header.php'; ?>

<div class="survey-form">
    <h2>Student Satisfaction Survey</h2>
    
    <?php if ($surveyCompleted): ?>
        <div class="alert alert-info">
            <i class="fas fa-check-circle"></i> You have already completed the survey. Thank you for your feedback!
        </div>
    <?php else: ?>
        <p>Please provide your honest feedback to help us improve the quality of education and facilities.</p>
        <p class="text-muted">All responses are confidential and will be used for improvement purposes only.</p>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="survey-form">
            <h3 class="mt-4 mb-3">Rate Your Experience</h3>
            
            <?php if (!empty($questions)): ?>
                <?php foreach ($questions as $question): ?>
                    <div class="question-item">
                        <p class="question-text"><?php echo htmlspecialchars($question['question']); ?></p>
                        <div class="rating-options">
                            <div class="rating-option">
                                <input type="radio" name="question[<?php echo $question['id']; ?>]" id="q<?php echo $question['id']; ?>_bad" value="bad" required>
                                <label for="q<?php echo $question['id']; ?>_bad">Bad</label>
                            </div>
                            <div class="rating-option">
                                <input type="radio" name="question[<?php echo $question['id']; ?>]" id="q<?php echo $question['id']; ?>_neutral" value="neutral" required>
                                <label for="q<?php echo $question['id']; ?>_neutral">Neutral</label>
                            </div>
                            <div class="rating-option">
                                <input type="radio" name="question[<?php echo $question['id']; ?>]" id="q<?php echo $question['id']; ?>_good" value="good" required>
                                <label for="q<?php echo $question['id']; ?>_good">Good</label>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> No survey questions are available at the moment.
                </div>
            <?php endif; ?>
            
            <h3 class="mt-4 mb-3">Rate Your Teachers</h3>
            
            <?php if (!empty($teachers)): ?>
                <?php foreach ($teachers as $teacher): ?>
                    <div class="question-item">
                        <p class="question-text">
                            <strong><?php echo htmlspecialchars($teacher['name']); ?></strong>
                            <small class="text-muted ml-2">(<?php echo htmlspecialchars($teacher['department']); ?> - <?php echo htmlspecialchars($teacher['subjects']); ?>)</small>
                        </p>
                        <div class="rating-options">
                            <div class="rating-option">
                                <input type="radio" name="teacher[<?php echo $teacher['id']; ?>]" id="t<?php echo $teacher['id']; ?>_bad" value="bad" required>
                                <label for="t<?php echo $teacher['id']; ?>_bad">Bad</label>
                            </div>
                            <div class="rating-option">
                                <input type="radio" name="teacher[<?php echo $teacher['id']; ?>]" id="t<?php echo $teacher['id']; ?>_neutral" value="neutral" required>
                                <label for="t<?php echo $teacher['id']; ?>_neutral">Neutral</label>
                            </div>
                            <div class="rating-option">
                                <input type="radio" name="teacher[<?php echo $teacher['id']; ?>]" id="t<?php echo $teacher['id']; ?>_good" value="good" required>
                                <label for="t<?php echo $teacher['id']; ?>_good">Good</label>
                            </div>
                        </div>
                        <div class="form-group mt-2">
                            <label for="teacher_comment_<?php echo $teacher['id']; ?>">Comments (Optional):</label>
                            <textarea class="form-control" id="teacher_comment_<?php echo $teacher['id']; ?>" name="teacher_comment[<?php echo $teacher['id']; ?>]" rows="2" placeholder="Optional comments about this teacher"></textarea>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> No teachers are available for rating at the moment.
                </div>
            <?php endif; ?>
            
            <h3 class="mt-4 mb-3">Suggestions and Complaints</h3>
            
            <div class="form-group">
                <label for="suggestion">Do you have any suggestions for improvement?</label>
                <textarea class="form-control" id="suggestion" name="suggestion" rows="3" placeholder="Enter your suggestions here"></textarea>
            </div>
            
            <div class="form-group">
                <label for="complaint">Do you have any complaints? (Will be stored anonymously)</label>
                <textarea class="form-control" id="complaint" name="complaint" rows="3" placeholder="Enter your complaints here"></textarea>
                <small class="text-muted">Your identity will be kept confidential for any complaints submitted.</small>
            </div>
            
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary">Submit Survey</button>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include '../../core/includes/footer.php'; ?>

<?php
// Close connection
closeConnection($conn);
?>

