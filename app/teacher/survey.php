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

// Determine if a specific survey is selected
$selectedSurveyId = isset($_GET['survey_id']) ? intval($_GET['survey_id']) : null;
$surveyCompleted = false;
$questions = [];
$selectedSurvey = null;

if ($selectedSurveyId) {
    // Check completion for this specific survey
    $surveyCompleted = isSurveyCompleted($_SESSION["id"], "teacher", $conn, $selectedSurveyId);
    
    // Get questions for this specific survey
    $questions = getSurveyQuestions("teacher", $conn, $selectedSurveyId);
    
    // Get survey details
    $sql = "SELECT id, title, description FROM surveys WHERE id = ? AND status = 'active'";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $selectedSurveyId);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $selectedSurvey = mysqli_fetch_assoc($result);
        }
        mysqli_stmt_close($stmt);
    }
    
    // If survey doesn't exist or isn't active, redirect back
    if (!$selectedSurvey) {
        header("location: survey.php");
        exit;
    }
}

// Get all active surveys for listing
$activeSurveys = getActiveSurveysForRole("teacher", $_SESSION["id"], $conn);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && $selectedSurveyId && !$surveyCompleted) {
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

<div class="container py-4">
    <!-- Page Header -->
    <div class="page-header-modern mb-4">
        <div class="d-flex align-items-center">
            <div class="header-icon">
                <i class="fas fa-poll"></i>
            </div>
            <div>
                <h1><?php echo $selectedSurvey ? htmlspecialchars($selectedSurvey['title']) : 'Teacher Surveys'; ?></h1>
                <p class="mb-0"><?php echo $selectedSurvey ? htmlspecialchars($selectedSurvey['description']) : 'Choose a survey to share your feedback'; ?></p>
            </div>
        </div>
    </div>

    <?php if (isset($alertType) && isset($alertMessage)): ?>
    <div class="alert alert-<?php echo $alertType; ?> alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-<?php echo $alertType === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
        <?php echo $alertMessage; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if (!$selectedSurveyId): ?>
    <!-- ==================== SURVEY LIST VIEW ==================== -->
    
    <?php if (empty($activeSurveys)): ?>
    <div class="card-modern text-center">
        <div class="card-body-modern py-5">
            <div class="empty-icon mb-4">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <h2 class="mb-3">No Surveys Available</h2>
            <p class="text-muted mb-4">There are no active surveys at the moment.<br>Please check back later.</p>
            <a href="dashboard.php" class="btn btn-primary">
                <i class="fas fa-home me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>
    <?php else: ?>
    <div class="survey-list-grid">
        <?php foreach ($activeSurveys as $survey): ?>
        <div class="survey-card <?php echo $survey['is_completed'] ? 'completed' : ''; ?>">
            <div class="survey-card-header">
                <div class="survey-status-badge <?php echo $survey['is_completed'] ? 'completed' : 'active'; ?>">
                    <i class="fas fa-<?php echo $survey['is_completed'] ? 'check-circle' : 'circle'; ?>"></i>
                    <?php echo $survey['is_completed'] ? 'Completed' : 'Active'; ?>
                </div>
                <div class="survey-question-count">
                    <i class="fas fa-list-ol me-1"></i><?php echo $survey['question_count']; ?> questions
                </div>
            </div>
            <div class="survey-card-body">
                <h3 class="survey-card-title"><?php echo htmlspecialchars($survey['title']); ?></h3>
                <p class="survey-card-desc"><?php echo htmlspecialchars($survey['description'] ?? 'No description provided.'); ?></p>
            </div>
            <div class="survey-card-footer">
                <span class="survey-date">
                    <i class="fas fa-calendar-alt me-1"></i>
                    <?php echo date('M d, Y', strtotime($survey['created_at'])); ?>
                </span>
                <?php if ($survey['is_completed']): ?>
                    <a href="analytics.php" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-chart-bar me-1"></i>View Analytics
                    </a>
                <?php else: ?>
                    <a href="survey.php?survey_id=<?php echo $survey['id']; ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-pen me-1"></i>Take Survey
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php elseif ($surveyCompleted): ?>
    <!-- ==================== SURVEY COMPLETED STATE ==================== -->
    <div class="card-modern text-center">
        <div class="card-body-modern py-5">
            <div class="completed-icon mb-4">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2 class="mb-3">Survey Completed!</h2>
            <p class="text-muted mb-4">You have already completed this survey.<br>Thank you for your valuable feedback!</p>
            <div class="d-flex gap-3 justify-content-center">
                <a href="survey.php" class="btn btn-primary">
                    <i class="fas fa-list me-2"></i>Back to Surveys
                </a>
                <a href="analytics.php" class="btn btn-outline-primary">
                    <i class="fas fa-chart-bar me-2"></i>View Analytics
                </a>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- ==================== SURVEY FORM VIEW ==================== -->
    
    <!-- Back Button -->
    <a href="survey.php" class="btn btn-outline-secondary mb-3">
        <i class="fas fa-arrow-left me-2"></i>Back to All Surveys
    </a>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?survey_id=' . $selectedSurveyId; ?>" method="post" id="survey-form">
        <!-- Progress Indicator -->
        <div class="survey-progress mb-4">
            <div class="progress-steps">
                <div class="step active">
                    <span class="step-number">1</span>
                    <span class="step-label">Rate Experience</span>
                </div>
                <div class="step-connector"></div>
                <div class="step">
                    <span class="step-number">2</span>
                    <span class="step-label">Feedback</span>
                </div>
                <div class="step-connector"></div>
                <div class="step">
                    <span class="step-number">3</span>
                    <span class="step-label">Submit</span>
                </div>
            </div>
        </div>

        <!-- Privacy Notice -->
        <div class="privacy-notice mb-4">
            <i class="fas fa-shield-alt me-2"></i>
            <span>All responses are confidential and will be used for improvement purposes only.</span>
        </div>

        <!-- Questions Section -->
        <div class="card-modern mb-4">
            <div class="card-header-modern">
                <h4><i class="fas fa-star me-2"></i>Rate Your Experience</h4>
                <span class="badge bg-primary">Required</span>
            </div>
            <div class="card-body-modern">
                <?php if (!empty($questions)): ?>
                    <?php 
                    $currentSet = null;
                    foreach ($questions as $index => $question): 
                        if (isset($question['set_name']) && $question['set_name'] !== $currentSet):
                            $currentSet = $question['set_name'];
                    ?>
                    <div class="question-set-header">
                        <i class="fas fa-layer-group me-2"></i><?php echo htmlspecialchars($currentSet); ?>
                    </div>
                    <?php endif; ?>
                    <div class="question-card" data-question="<?php echo $index + 1; ?>">
                        <div class="question-number"><?php echo $index + 1; ?></div>
                        <div class="question-content">
                            <p class="question-text"><?php echo htmlspecialchars($question['question']); ?></p>
                            <div class="rating-cards">
                                <label class="rating-card bad">
                                    <input type="radio" name="question[<?php echo $question['id']; ?>]" value="bad" required>
                                    <div class="card-inner">
                                        <i class="fas fa-frown"></i>
                                        <span>Bad</span>
                                    </div>
                                </label>
                                <label class="rating-card neutral">
                                    <input type="radio" name="question[<?php echo $question['id']; ?>]" value="neutral" required>
                                    <div class="card-inner">
                                        <i class="fas fa-meh"></i>
                                        <span>Neutral</span>
                                    </div>
                                </label>
                                <label class="rating-card good">
                                    <input type="radio" name="question[<?php echo $question['id']; ?>]" value="good" required>
                                    <div class="card-inner">
                                        <i class="fas fa-smile"></i>
                                        <span>Good</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-question-circle"></i>
                        <h5>No Questions Available</h5>
                        <p>Survey questions are not available at the moment. Please check back later.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Feedback Section -->
        <div class="card-modern mb-4">
            <div class="card-header-modern">
                <h4><i class="fas fa-comment-dots me-2"></i>Additional Feedback</h4>
                <span class="badge bg-secondary">Optional</span>
            </div>
            <div class="card-body-modern">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="feedback-box suggestion">
                            <div class="feedback-icon">
                                <i class="fas fa-lightbulb"></i>
                            </div>
                            <label for="suggestion" class="feedback-label">Suggestions for Improvement</label>
                            <textarea class="form-control" id="suggestion" name="suggestion" rows="4" 
                                placeholder="Share your ideas to improve the teaching environment..."></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="feedback-box complaint">
                            <div class="feedback-icon">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <label for="complaint" class="feedback-label">Complaints</label>
                            <textarea class="form-control" id="complaint" name="complaint" rows="4" 
                                placeholder="Report any issues or concerns..."></textarea>
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-user-secret me-1"></i>Your identity will be kept confidential
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Section -->
        <div class="card-modern submit-section">
            <div class="card-body-modern">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="submit-info">
                        <h5 class="mb-1"><i class="fas fa-check-circle text-success me-2"></i>Ready to Submit?</h5>
                        <p class="text-muted mb-0">Please review your responses before submitting.</p>
                    </div>
                    <div class="submit-actions">
                        <a href="survey.php" class="btn btn-outline-secondary btn-lg me-2">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Submit Survey
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php endif; ?>
</div>

<style>
/* Page Header */
.page-header-modern {
    background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);
    border-radius: var(--radius-xl);
    padding: 2rem;
    color: white;
}

.page-header-modern h1 {
    color: white;
    font-size: 1.75rem;
    font-weight: 700;
    margin: 0 0 0.25rem 0;
}

.page-header-modern p { color: rgba(255, 255, 255, 0.85); }

.header-icon {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-right: 1.25rem;
}

/* ==================== SURVEY LIST STYLES ==================== */
.survey-list-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 1.5rem;
}

.survey-card {
    background: white;
    border-radius: var(--radius-xl, 16px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    display: flex;
    flex-direction: column;
}

.survey-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    border-color: #a78bfa;
}

.survey-card.completed { border-color: #bbf7d0; opacity: 0.85; }
.survey-card.completed:hover { opacity: 1; border-color: #10b981; }

.survey-card-header {
    padding: 1rem 1.25rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 1px solid #e2e8f0;
}

.survey-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.survey-status-badge.active { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
.survey-status-badge.completed { background: rgba(16, 185, 129, 0.1); color: #059669; }

.survey-question-count { font-size: 0.8rem; color: #64748b; font-weight: 500; }

.survey-card-body { padding: 1.25rem; flex-grow: 1; }

.survey-card-title { font-size: 1.1rem; font-weight: 700; color: #1e293b; margin: 0 0 0.5rem 0; }

.survey-card-desc {
    font-size: 0.875rem; color: #64748b; margin: 0;
    display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
}

.survey-card-footer {
    padding: 1rem 1.25rem; border-top: 1px solid #f1f5f9;
    display: flex; justify-content: space-between; align-items: center;
}

.survey-date { font-size: 0.8rem; color: #94a3b8; }

.empty-icon {
    width: 100px; height: 100px;
    background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    font-size: 3rem; color: #94a3b8; margin: 0 auto;
}

/* ==================== SURVEY FORM STYLES ==================== */

.question-set-header {
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, #f3e8ff 0%, #ede9fe 100%);
    color: #6d28d9;
    font-weight: 600; font-size: 0.9rem;
    border-bottom: 1px solid #ddd6fe;
    margin-top: 0.5rem;
}
.question-set-header:first-child { margin-top: 0; }

.survey-progress {
    background: white; border-radius: var(--radius-xl);
    padding: 1.5rem 2rem; box-shadow: var(--shadow-md);
}

.progress-steps { display: flex; align-items: center; justify-content: center; }

.step { display: flex; flex-direction: column; align-items: center; gap: 0.5rem; }

.step-number {
    width: 40px; height: 40px; background: var(--gray-200); border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 600; color: var(--text-muted); transition: all 0.3s ease;
}

.step.active .step-number {
    background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%); color: white;
}

.step-label { font-size: 0.875rem; color: var(--text-muted); font-weight: 500; }
.step.active .step-label { color: var(--primary-color); }

.step-connector {
    width: 80px; height: 2px; background: var(--gray-200);
    margin: 0 1rem; margin-bottom: 1.5rem;
}

.privacy-notice {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border: 1px solid #bbf7d0; border-radius: var(--radius-lg);
    padding: 1rem 1.25rem; color: #166534; font-weight: 500;
    display: flex; align-items: center;
}

.card-modern {
    background: white; border-radius: var(--radius-xl);
    box-shadow: var(--shadow-md); overflow: hidden;
}

.card-header-modern {
    padding: 1.25rem 1.5rem; background: var(--gray-50);
    border-bottom: 1px solid var(--gray-100);
    display: flex; justify-content: space-between; align-items: center;
}

.card-header-modern h4 { margin: 0; font-size: 1.1rem; font-weight: 600; }
.card-body-modern { padding: 1.5rem; }

.question-card {
    display: flex; gap: 1.25rem; padding: 1.5rem;
    border-bottom: 1px solid var(--gray-100); transition: background 0.3s ease;
}
.question-card:last-child { border-bottom: none; }
.question-card:hover { background: var(--gray-50); }

.question-number {
    width: 36px; height: 36px;
    background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    font-weight: 600; font-size: 0.875rem; color: white; flex-shrink: 0;
}

.question-content { flex-grow: 1; }
.question-text { font-weight: 500; font-size: 1rem; color: var(--text-primary); margin-bottom: 1rem; }

.rating-cards { display: flex; gap: 1rem; }
.rating-card { flex: 1; cursor: pointer; }
.rating-card input { display: none; }

.rating-card .card-inner {
    display: flex; flex-direction: column; align-items: center; gap: 0.5rem;
    padding: 1rem; border: 2px solid var(--gray-200);
    border-radius: var(--radius-lg); transition: all 0.3s ease;
}

.rating-card .card-inner i { font-size: 1.75rem; color: var(--text-muted); }
.rating-card .card-inner span { font-weight: 500; color: var(--text-secondary); font-size: 0.875rem; }
.rating-card:hover .card-inner { border-color: var(--primary-light); }

.rating-card.bad input:checked + .card-inner { border-color: #ef4444; background: rgba(239, 68, 68, 0.1); }
.rating-card.bad input:checked + .card-inner i, .rating-card.bad input:checked + .card-inner span { color: #ef4444; }

.rating-card.neutral input:checked + .card-inner { border-color: #f59e0b; background: rgba(245, 158, 11, 0.1); }
.rating-card.neutral input:checked + .card-inner i, .rating-card.neutral input:checked + .card-inner span { color: #f59e0b; }

.rating-card.good input:checked + .card-inner { border-color: #10b981; background: rgba(16, 185, 129, 0.1); }
.rating-card.good input:checked + .card-inner i, .rating-card.good input:checked + .card-inner span { color: #10b981; }

.feedback-box {
    height: 100%; padding: 1.5rem; border-radius: var(--radius-lg);
    border: 2px solid var(--gray-200); transition: border-color 0.3s ease;
}
.feedback-box:focus-within { border-color: var(--primary-color); }
.feedback-box.suggestion { background: linear-gradient(135deg, #fefce8 0%, #fef9c3 100%); border-color: #fde047; }
.feedback-box.complaint { background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border-color: #fca5a5; }

.feedback-icon {
    width: 50px; height: 50px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem; margin-bottom: 1rem;
}
.feedback-box.suggestion .feedback-icon { background: #fef08a; color: #ca8a04; }
.feedback-box.complaint .feedback-icon { background: #fecaca; color: #dc2626; }

.feedback-label { font-weight: 600; color: var(--text-primary); margin-bottom: 0.75rem; display: block; }
.feedback-box .form-control { background: white; border: 1px solid var(--gray-300); }

.submit-section { background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); }
.submit-actions .btn { min-width: 150px; }

.completed-icon {
    width: 100px; height: 100px;
    background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    font-size: 3rem; color: white; margin: 0 auto;
}

.empty-state { text-align: center; padding: 3rem; color: var(--text-muted); }
.empty-state i { font-size: 3rem; margin-bottom: 1rem; opacity: 0.5; }
.empty-state h5 { color: var(--text-secondary); margin-bottom: 0.5rem; }

@media (max-width: 768px) {
    .page-header-modern { padding: 1.5rem; }
    .page-header-modern h1 { font-size: 1.25rem; }
    .header-icon { width: 50px; height: 50px; font-size: 1.25rem; }
    .survey-list-grid { grid-template-columns: 1fr; }
    .progress-steps { flex-direction: column; gap: 0.5rem; }
    .step-connector { width: 2px; height: 20px; margin: 0; }
    .question-card { flex-direction: column; gap: 1rem; }
    .rating-cards { flex-direction: column; }
    .submit-section .d-flex { flex-direction: column; text-align: center; }
    .submit-actions { width: 100%; }
    .submit-actions .btn { width: 100%; margin-bottom: 0.5rem; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('survey-form');
    if (!form) return;
    
    const questions = form.querySelectorAll('input[type="radio"]');
    const feedbackAreas = form.querySelectorAll('textarea');
    const steps = document.querySelectorAll('.step');
    
    function updateProgress() {
        const allQuestionsAnswered = Array.from(document.querySelectorAll('.question-card')).every(card => {
            return card.querySelector('input[type="radio"]:checked') !== null;
        });
        
        const hasFeedback = Array.from(feedbackAreas).some(area => area.value.trim() !== '');
        
        if (allQuestionsAnswered) steps[1].classList.add('active');
        if (allQuestionsAnswered && hasFeedback) steps[2].classList.add('active');
    }
    
    questions.forEach(q => q.addEventListener('change', updateProgress));
    feedbackAreas.forEach(t => t.addEventListener('input', updateProgress));
});
</script>

<?php include '../../core/includes/footer.php'; ?>

<?php
// Close connection
closeConnection($conn);
?>
