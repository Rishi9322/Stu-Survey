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

// Check if the teacher has completed the survey
$surveyCompleted = isSurveyCompleted($_SESSION["id"], "teacher", $conn);

// Get survey questions
$questions = getSurveyQuestions("teacher", $conn);

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
$pageTitle = "Teacher Survey";
$basePath = "../../";
?>

<?php include '../../core/includes/header.php'; ?>

<div class="survey-form">
    <h2>Teacher Satisfaction Survey</h2>
    
    <?php if ($surveyCompleted): ?>
        <div class="alert alert-info">
            <i class="fas fa-check-circle"></i> You have already completed the survey. Thank you for your feedback!
        </div>
    <?php else: ?>
        <p>Please provide your honest feedback to help us improve the quality of teaching environment and facilities.</p>
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

