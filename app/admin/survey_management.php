<?php
// Start output buffering to handle any whitespace issues
ob_start();

// Include config file
require_once "../../core/includes/config.php";
require_once "../../core/includes/functions.php";
require_once "../../core/functions/survey_management.php";

// Initialize the session
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Check if the user is logged in, if not then redirect to login page
if (!isLoggedIn() || !hasRole("admin")) {
    header("location: ../../public/login.php");
    exit;
}

// Initialize variables
$question = $target_role = $survey_title = $survey_description = "";
$question_err = $target_role_err = $survey_title_err = $survey_description_err = "";
$alertType = "success";
$alertMessage = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['create_survey'])) {
        // Validate survey title
        if (empty(trim($_POST["survey_title"]))) {
            $survey_title_err = "Please enter a survey title.";
        } else {
            $survey_title = trim($_POST["survey_title"]);
        }
        
        // Validate survey description
        if (empty(trim($_POST["survey_description"]))) {
            $survey_description_err = "Please enter a survey description.";
        } else {
            $survey_description = trim($_POST["survey_description"]);
        }
        
        // Validate target role
        if (empty(trim($_POST["survey_target_role"]))) {
            $target_role_err = "Please select a target role.";
        } else {
            $target_role = trim($_POST["survey_target_role"]);
        }
        
        // Check input errors before creating survey
        if (empty($survey_title_err) && empty($survey_description_err) && empty($target_role_err)) {
            $surveyId = createSurvey($survey_title, $survey_description, $target_role, $_SESSION['id'], $conn);
            
            if ($surveyId) {
                $alertType = "success";
                $alertMessage = "Survey created successfully! You can now add question sets and questions.";
                $survey_title = $survey_description = $target_role = "";
            } else {
                $alertType = "danger";
                $alertMessage = "Error creating survey. Please try again.";
            }
        }
    } elseif (isset($_POST['duplicate_survey'])) {
        $surveyId = $_POST['survey_id'];
        $newTitle = $_POST['new_title'] . " (Copy)";
        
        $newSurveyId = duplicateSurvey($surveyId, $newTitle, $conn);
        if ($newSurveyId) {
            $alertType = "success";
            $alertMessage = "Survey duplicated successfully!";
        } else {
            $alertType = "danger";
            $alertMessage = "Error duplicating survey. Please try again.";
        }
    } elseif (isset($_POST['update_survey_status'])) {
        $surveyId = $_POST['survey_id'];
        $status = $_POST['status'];
        
        // Enforce minimum 10 questions per set when activating
        if ($status === 'active') {
            $minOk = true;
            $sql = "SELECT qs.id, qs.name, COUNT(sq.id) as question_count
                    FROM question_sets qs
                    LEFT JOIN survey_questions sq ON qs.id = sq.question_set_id
                    WHERE qs.survey_id = ?
                    GROUP BY qs.id, qs.name";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $surveyId);
                if (mysqli_stmt_execute($stmt)) {
                    $result = mysqli_stmt_get_result($stmt);
                    $shortSets = [];
                    while ($row = mysqli_fetch_assoc($result)) {
                        if ((int)$row['question_count'] < 10) {
                            $shortSets[] = $row['name'] . " (" . $row['question_count'] . ")";
                        }
                    }
                    if (!empty($shortSets)) {
                        $minOk = false;
                        $alertType = "danger";
                        $alertMessage = "Cannot activate survey. Each question set must have at least 10 questions. Short sets: " . implode(", ", $shortSets);
                    }
                }
                mysqli_stmt_close($stmt);
            }
            if (!$minOk) {
                // Skip activation
            } else {
                if (updateSurveyStatus($surveyId, $status, $conn)) {
                    $alertType = "success";
                    $alertMessage = "Survey status updated successfully!";
                } else {
                    $alertType = "danger";
                    $alertMessage = "Error updating survey status. Please try again.";
                }
            }
        } else {
            if (updateSurveyStatus($surveyId, $status, $conn)) {
                $alertType = "success";
                $alertMessage = "Survey status updated successfully!";
            } else {
                $alertType = "danger";
                $alertMessage = "Error updating survey status. Please try again.";
            }
        }
    } elseif (isset($_POST['restart_survey'])) {
        $surveyId = $_POST['survey_id'];
        $sessionName = $_POST['session_name'];
        
        $sessionId = restartSurvey($surveyId, $sessionName, $conn);
        if ($sessionId) {
            $alertType = "success";
            $alertMessage = "Survey restarted successfully! New session created.";
        } else {
            $alertType = "danger";
            $alertMessage = "Error restarting survey. Please try again.";
        }
    } elseif (isset($_POST['create_question_set'])) {
        $surveyId = $_POST['survey_id'];
        $setName = trim($_POST['set_name']);
        $setDescription = trim($_POST['set_description']);
        $displayOrder = intval($_POST['display_order']);
        
        if (empty($setName)) {
            $alertType = "danger";
            $alertMessage = "Question set name cannot be empty.";
        } else {
            $questionSetId = createQuestionSet($surveyId, $setName, $setDescription, $conn, $displayOrder);
            if ($questionSetId) {
                $alertType = "success";
                $alertMessage = "Question set created successfully!";
            } else {
                $alertType = "danger";
                $alertMessage = "Error creating question set. Please try again.";
            }
        }
    } elseif (isset($_POST['add_question_to_set'])) {
        $questionSetId = $_POST['question_set_id'];
        $question = trim($_POST['question']);
        $questionType = $_POST['question_type'] ?? 'rating';
        $displayOrder = intval($_POST['display_order'] ?? 1);
        $targetRole = $_POST['target_role'] ?? 'student';
        
        if (empty($question)) {
            $alertType = "danger";
            $alertMessage = "Question cannot be empty.";
        } else {
            $questionId = addQuestionToSet($questionSetId, $question, $questionType, $displayOrder, $targetRole, $conn);
            if ($questionId) {
                $alertType = "success";
                $alertMessage = "Question added successfully!";
            } else {
                $alertType = "danger";
                $alertMessage = "Error adding question. Please try again.";
            }
        }
    } elseif (isset($_POST['update_survey_settings'])) {
        $surveyId = intval($_POST['survey_id']);
        $title = trim($_POST['survey_title']);
        $targetRole = $_POST['survey_target_role'];
        $description = trim($_POST['survey_description']);
        $startDate = $_POST['start_date'] ?: null;
        $endDate = $_POST['end_date'] ?: null;
        $isAnonymous = isset($_POST['is_anonymous']) ? 1 : 0;
        $isRequired = isset($_POST['is_required']) ? 1 : 0;
        
        $sql = "UPDATE surveys SET title = ?, description = ?, target_role = ?, start_date = ?, end_date = ?, is_anonymous = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssssii", $title, $description, $targetRole, $startDate, $endDate, $isAnonymous, $surveyId);
            if (mysqli_stmt_execute($stmt)) {
                $alertType = "success";
                $alertMessage = "Survey settings updated successfully!";
            } else {
                $alertType = "danger";
                $alertMessage = "Error updating survey settings.";
            }
            mysqli_stmt_close($stmt);
        }
    } elseif (isset($_POST['delete_question_set'])) {
        $setId = intval($_POST['set_id']);
        
        // First delete all questions in this set
        $sql = "DELETE FROM survey_questions WHERE question_set_id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $setId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        
        // Then delete the set
        $sql = "DELETE FROM question_sets WHERE id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $setId);
            if (mysqli_stmt_execute($stmt)) {
                $alertType = "success";
                $alertMessage = "Question set deleted successfully!";
            } else {
                $alertType = "danger";
                $alertMessage = "Error deleting question set.";
            }
            mysqli_stmt_close($stmt);
        }
    } elseif (isset($_POST['add_question'])) {
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

// Function to get response statistics for a question
function getQuestionStats($conn, $question_id) {
    $stats = [
        'good' => 0,
        'neutral' => 0,
        'bad' => 0,
        'total' => 0
    ];
    
    $sql = "SELECT rating, COUNT(*) as count FROM survey_responses WHERE question_id = ? GROUP BY rating";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $question_id);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) {
                $stats[$row['rating']] = (int)$row['count'];
                $stats['total'] += (int)$row['count'];
            }
        }
        mysqli_stmt_close($stmt);
    }
    
    return $stats;
}

// Fetch all student survey questions with stats
$sql = "SELECT * FROM survey_questions WHERE target_role = 'student' ORDER BY id ASC";
$student_questions = [];
$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $row['stats'] = getQuestionStats($conn, $row['id']);
        $student_questions[] = $row;
    }
}

// Fetch all teacher survey questions with stats
$sql = "SELECT * FROM survey_questions WHERE target_role = 'teacher' ORDER BY id ASC";
$teacher_questions = [];
$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $row['stats'] = getQuestionStats($conn, $row['id']);
        $teacher_questions[] = $row;
    }
}

// Fetch all surveys for dropdown and management
$allSurveys = getSurveyList($conn, null, null);

// Get current survey stats for enhanced overview
$totalSurveys = count($allSurveys);
$activeSurveys = count(array_filter($allSurveys, function($s) { return $s['status'] == 'active'; }));
$draftSurveys = count(array_filter($allSurveys, function($s) { return $s['status'] == 'draft'; }));
$totalQuestionSets = array_sum(array_column($allSurveys, 'question_set_count'));

// Check if managing a specific survey
$manageSurveyId = isset($_GET['survey_id']) ? intval($_GET['survey_id']) : null;
$manageSurvey = null;
$manageSurveyQuestionSets = [];
$manageSurveyQuestions = [];

if ($manageSurveyId) {
    // Find the survey
    foreach ($allSurveys as $s) {
        if ($s['id'] == $manageSurveyId) {
            $manageSurvey = $s;
            break;
        }
    }
    if (!$manageSurvey) {
        header("location: survey_management.php");
        exit;
    }
    
    // Get question sets for this survey
    $manageSurveyQuestionSets = getQuestionSets($manageSurveyId, $conn) ?? [];
    
    // Get all questions for this survey grouped by set
    $sql = "SELECT sq.*, qs.name as set_name FROM survey_questions sq 
            LEFT JOIN question_sets qs ON sq.question_set_id = qs.id 
            WHERE sq.survey_id = ? ORDER BY qs.display_order, sq.display_order";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $manageSurveyId);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) {
                $row['stats'] = getQuestionStats($conn, $row['id']);
                $manageSurveyQuestions[] = $row;
            }
        }
        mysqli_stmt_close($stmt);
    }
    
    $progress = getSurveyProgress($manageSurveyId, $conn, null);
}

// Set page variables
$pageTitle = $manageSurvey ? "Manage: " . $manageSurvey['title'] : "Survey Management";
$basePath = "../../";
?>

<?php include '../../core/includes/header.php'; ?>

<style>
:root {
    --admin-primary: #dc2626;
    --admin-secondary: #ef4444;
    --admin-accent: #f87171;
    --radius-xl: 16px;
    --radius-lg: 12px;
    --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -4px rgba(0,0,0,0.1);
    --gray-50: #f9fafb; --gray-100: #f3f4f6; --gray-200: #e5e7eb;
    --gray-400: #9ca3af; --gray-500: #6b7280; --gray-600: #4b5563;
    --gray-700: #374151; --gray-800: #1f2937;
}

.page-header-modern {
    background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);
    border-radius: var(--radius-xl); padding: 2rem 2.5rem; margin-bottom: 2rem;
    color: white; box-shadow: var(--shadow-lg); position: relative; overflow: hidden;
}
.page-header-modern::before {
    content:''; position:absolute; top:-50%; right:-10%; width:300px; height:300px;
    background:rgba(255,255,255,0.1); border-radius:50%;
}
.page-header-modern h2 { margin:0; font-weight:700; font-size:1.75rem; }
.page-header-modern p { margin:0.5rem 0 0; opacity:0.9; }

.stat-card-modern {
    background:white; border-radius:var(--radius-xl); padding:1.5rem;
    box-shadow:var(--shadow-md); border:1px solid var(--gray-100);
    transition:all 0.3s ease; height:100%;
}
.stat-card-modern:hover { transform:translateY(-4px); box-shadow:var(--shadow-lg); }
.stat-icon {
    width:56px; height:56px; border-radius:var(--radius-lg);
    display:flex; align-items:center; justify-content:center; font-size:1.5rem; color:white;
}
.stat-icon.red { background:linear-gradient(135deg,var(--admin-primary),var(--admin-secondary)); }
.stat-icon.blue { background:linear-gradient(135deg,#3b82f6,#60a5fa); }
.stat-icon.green { background:linear-gradient(135deg,#10b981,#34d399); }
.stat-icon.purple { background:linear-gradient(135deg,#8b5cf6,#a78bfa); }
.stat-value { font-size:2rem; font-weight:700; color:var(--gray-800); margin:0; line-height:1.2; }
.stat-label { color:var(--gray-600); font-size:0.875rem; margin:0; }

/* Survey Cards Grid */
.survey-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(340px,1fr)); gap:1.5rem; }
.survey-card {
    background:white; border-radius:var(--radius-xl); box-shadow:var(--shadow-md);
    border:2px solid transparent; overflow:hidden; transition:all 0.3s ease;
    display:flex; flex-direction:column;
}
.survey-card:hover { transform:translateY(-4px); box-shadow:var(--shadow-lg); border-color:var(--admin-accent); }
.survey-card.status-active { border-left:4px solid #10b981; }
.survey-card.status-draft { border-left:4px solid #f59e0b; }
.survey-card.status-archived { border-left:4px solid #94a3b8; }

.survey-card-top {
    padding:1.25rem 1.25rem 0.75rem; display:flex;
    justify-content:space-between; align-items:flex-start;
}
.survey-card-body { padding:0 1.25rem 1rem; flex-grow:1; }
.survey-card-body h3 { font-size:1.1rem; font-weight:700; color:var(--gray-800); margin:0 0 0.5rem; }
.survey-card-body p { font-size:0.85rem; color:var(--gray-500); margin:0; line-height:1.5;
    display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }

.survey-card-stats {
    padding:0.75rem 1.25rem; background:var(--gray-50); border-top:1px solid var(--gray-100);
    display:flex; gap:1.25rem; font-size:0.8rem; color:var(--gray-600);
}
.survey-card-stats .stat-item { display:flex; align-items:center; gap:0.375rem; }
.survey-card-footer {
    padding:0.75rem 1.25rem; border-top:1px solid var(--gray-100);
    display:flex; justify-content:space-between; align-items:center;
}

.status-pill {
    display:inline-flex; align-items:center; gap:0.375rem;
    padding:0.25rem 0.75rem; border-radius:9999px; font-size:0.7rem; font-weight:600; text-transform:uppercase;
}
.status-pill.active { background:rgba(16,185,129,0.1); color:#059669; }
.status-pill.draft { background:rgba(245,158,11,0.1); color:#b45309; }
.status-pill.archived { background:rgba(148,163,184,0.1); color:#64748b; }

.role-pill {
    display:inline-flex; align-items:center; gap:0.375rem;
    padding:0.25rem 0.75rem; border-radius:9999px; font-size:0.7rem; font-weight:600;
}
.role-pill.student { background:rgba(59,130,246,0.1); color:#2563eb; }
.role-pill.teacher { background:rgba(139,92,246,0.1); color:#7c3aed; }
.role-pill.both { background:rgba(6,182,212,0.1); color:#0891b2; }

/* Create Survey Inline */
.create-survey-card {
    background:white; border-radius:var(--radius-xl); box-shadow:var(--shadow-md);
    border:2px dashed var(--gray-200); overflow:hidden; margin-bottom:2rem;
}
.create-survey-card .card-header {
    padding:1.25rem 1.5rem; background:var(--gray-50); border-bottom:1px solid var(--gray-200);
    display:flex; align-items:center; justify-content:space-between; cursor:pointer;
}
.create-survey-card .card-body { padding:1.5rem; }
.create-survey-card .role-selector { display:flex; gap:1rem; margin-top:0.5rem; }
.create-survey-card .role-option { flex:1; position:relative; }
.create-survey-card .role-option input[type="radio"] { position:absolute; opacity:0; width:100%; height:100%; cursor:pointer; z-index:2; }
.create-survey-card .role-option .role-card-select {
    padding:1.25rem; border:2px solid var(--gray-200); border-radius:var(--radius-lg);
    text-align:center; transition:all 0.3s ease; cursor:pointer; background:var(--gray-50);
}
.create-survey-card .role-option input:checked + .role-card-select { border-color:var(--admin-primary); background:rgba(220,38,38,0.05); }
.create-survey-card .role-option .role-card-select i { font-size:2rem; margin-bottom:0.5rem; color:var(--gray-600); transition:color 0.3s ease; }
.create-survey-card .role-option input:checked + .role-card-select i { color:var(--admin-primary); }
.create-survey-card .role-option .role-card-select span { display:block; font-weight:600; color:var(--gray-700); }

/* Detail View */
.detail-header {
    background:white; border-radius:var(--radius-xl); box-shadow:var(--shadow-md);
    padding:1.5rem; margin-bottom:1.5rem; display:flex; align-items:center;
    justify-content:space-between; flex-wrap:wrap; gap:1rem;
}
.detail-header .info h3 { margin:0 0 0.25rem; font-weight:700; font-size:1.25rem; color:var(--gray-800); }
.detail-header .info p { margin:0; color:var(--gray-500); font-size:0.9rem; }
.detail-header .actions { display:flex; gap:0.5rem; flex-wrap:wrap; }

.section-card {
    background:white; border-radius:var(--radius-xl); box-shadow:var(--shadow-md);
    overflow:hidden; margin-bottom:1.5rem;
}
.section-card .section-header {
    padding:1rem 1.5rem; background:var(--gray-50); border-bottom:1px solid var(--gray-200);
    display:flex; align-items:center; justify-content:space-between;
}
.section-card .section-header h4 { margin:0; font-size:1rem; font-weight:600; color:var(--gray-800); }
.section-card .section-body { padding:1.25rem 1.5rem; }

/* Question Set Accordion */
.qs-item {
    border:1px solid var(--gray-200); border-radius:var(--radius-lg);
    margin-bottom:0.75rem; overflow:hidden;
}
.qs-item-header {
    padding:1rem 1.25rem; background:var(--gray-50); display:flex;
    align-items:center; justify-content:space-between; cursor:pointer;
    transition:background 0.2s ease;
}
.qs-item-header:hover { background:var(--gray-100); }
.qs-item-header .qs-info { display:flex; align-items:center; gap:0.75rem; }
.qs-item-header .qs-info h5 { margin:0; font-size:0.95rem; font-weight:600; color:var(--gray-700); }
.qs-item-body { padding:1rem 1.25rem; border-top:1px solid var(--gray-200); }

.q-row {
    display:flex; align-items:center; gap:1rem; padding:0.75rem 0;
    border-bottom:1px solid var(--gray-100);
}
.q-row:last-child { border-bottom:none; }
.q-num {
    width:28px; height:28px; border-radius:50%;
    background:linear-gradient(135deg,var(--admin-primary),var(--admin-secondary));
    color:white; display:flex; align-items:center; justify-content:center;
    font-size:0.75rem; font-weight:600; flex-shrink:0;
}
.q-text { flex-grow:1; font-size:0.9rem; color:var(--gray-700); }
.q-actions { display:flex; gap:0.25rem; flex-shrink:0; }
.q-actions .btn { padding:0.25rem 0.5rem; font-size:0.75rem; }

/* Inline Add Forms */
.inline-form {
    background:linear-gradient(135deg,#fef2f2,#fff1f2); border:1px solid #fecaca;
    border-radius:var(--radius-lg); padding:1.25rem; margin-top:0.75rem;
}
.inline-form label { font-weight:500; color:var(--gray-700); font-size:0.85rem; }

.empty-state { text-align:center; padding:3rem; color:var(--gray-500); }
.empty-state i { font-size:3rem; margin-bottom:1rem; color:var(--gray-400); }

/* Stats Modal */
.stats-modal-lg { max-width:750px; margin:2rem auto; }
.stats-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.chart-container { position:relative; height:250px; margin:1rem 0; }
.sentiment-analysis-card { background:linear-gradient(135deg,#f8fafc,#e2e8f0); border-radius:var(--radius-lg); padding:1.5rem; margin-top:1.5rem; border:1px solid #e2e8f0; }
.sentiment-header { display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem; }
.sentiment-header i { font-size:1.5rem; color:#667eea; }
.sentiment-header h6 { margin:0; font-weight:600; color:#1e293b; }
.sentiment-meter { position:relative; height:24px; background:linear-gradient(90deg,#ef4444 0%,#f59e0b 50%,#10b981 100%); border-radius:12px; margin:1rem 0; overflow:hidden; }
.sentiment-indicator { position:absolute; top:50%; transform:translate(-50%,-50%); width:20px; height:20px; background:white; border-radius:50%; box-shadow:0 2px 8px rgba(0,0,0,0.3); border:3px solid #1e293b; transition:left 0.5s ease; }
.sentiment-labels { display:flex; justify-content:space-between; font-size:0.75rem; color:#64748b; margin-top:0.5rem; }
.sentiment-score { display:flex; align-items:center; justify-content:center; gap:1rem; margin-top:1rem; }
.sentiment-badge { padding:0.5rem 1.25rem; border-radius:2rem; font-weight:600; font-size:1rem; display:inline-flex; align-items:center; gap:0.5rem; }
.sentiment-badge.positive { background:linear-gradient(135deg,#10b981,#34d399); color:white; }
.sentiment-badge.neutral { background:linear-gradient(135deg,#f59e0b,#fbbf24); color:white; }
.sentiment-badge.negative { background:linear-gradient(135deg,#ef4444,#f87171); color:white; }
.sentiment-description { font-size:0.875rem; color:#64748b; text-align:center; margin-top:0.75rem; }
.stats-summary { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:1.5rem; }
.stats-card { text-align:center; padding:1.25rem; border-radius:var(--radius-lg); transition:transform 0.2s ease; }
.stats-card:hover { transform:translateY(-2px); }
.stats-card.good { background:rgba(16,185,129,0.1); border:2px solid rgba(16,185,129,0.2); }
.stats-card.neutral { background:rgba(245,158,11,0.1); border:2px solid rgba(245,158,11,0.2); }
.stats-card.bad { background:rgba(239,68,68,0.1); border:2px solid rgba(239,68,68,0.2); }
.stats-card .icon { font-size:2rem; margin-bottom:0.5rem; }
.stats-card.good .icon { color:#10b981; }
.stats-card.neutral .icon { color:#f59e0b; }
.stats-card.bad .icon { color:#ef4444; }
.stats-card .count { font-size:1.75rem; font-weight:700; color:var(--gray-800); }
.stats-card .label { font-size:0.875rem; color:var(--gray-600); text-transform:uppercase; letter-spacing:0.05em; }
.stats-card .percentage { font-size:0.75rem; color:var(--gray-500); margin-top:0.25rem; }
.no-data-message { text-align:center; padding:3rem; color:var(--gray-500); }
.no-data-message i { font-size:3rem; margin-bottom:1rem; color:var(--gray-400); }

.modal-modern .modal-content { border-radius:16px; border:none; overflow:hidden; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); }
.modal-modern .modal-header { background:linear-gradient(135deg,var(--admin-primary),var(--admin-secondary)); color:white; border:none; padding:1.25rem 1.5rem; }
.modal-modern .modal-header .btn-close { filter:brightness(0) invert(1); opacity:0.8; }
.modal-modern .modal-title { font-weight:600; }
.modal-modern .modal-body { padding:1.5rem; }
.modal-modern .modal-footer { border-top:1px solid var(--gray-200); padding:1rem 1.5rem; background:#f8fafc; }

.action-btn {
    width:36px; height:36px; border-radius:var(--radius-lg); border:none;
    display:inline-flex; align-items:center; justify-content:center; cursor:pointer;
    transition:all 0.2s ease; font-size:0.875rem;
}
.action-btn.edit { background:rgba(59,130,246,0.1); color:#3b82f6; }
.action-btn.edit:hover { background:#3b82f6; color:white; }
.action-btn.delete { background:rgba(239,68,68,0.1); color:#ef4444; }
.action-btn.delete:hover { background:#ef4444; color:white; }
.action-btn.stats { background:rgba(16,185,129,0.1); color:#10b981; }
.action-btn.stats:hover { background:#10b981; color:white; }

@media (max-width:768px) {
    .survey-grid { grid-template-columns:1fr; }
    .detail-header { flex-direction:column; text-align:center; }
    .detail-header .actions { justify-content:center; }
    .page-header-modern { padding:1.5rem; }
    .page-header-modern h2 { font-size:1.25rem; }
    .stats-summary { grid-template-columns:1fr; }
}
</style>

<div class="container-fluid py-4">

    <?php if (!$manageSurveyId): ?>
    <!-- ==================== SURVEY LIST VIEW ==================== -->
    
    <!-- Page Header -->
    <div class="page-header-modern">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-clipboard-list me-2"></i>Survey Management</h2>
                <p>Create, manage, and analyze your surveys</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-light" onclick="document.getElementById('createSection').style.display = document.getElementById('createSection').style.display === 'none' ? 'block' : 'none'">
                    <i class="fas fa-plus me-2"></i>Create Survey
                </button>
                <a href="dashboard.php" class="btn btn-light">
                    <i class="fas fa-arrow-left me-1"></i>Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card-modern">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon blue"><i class="fas fa-poll"></i></div>
                    <div><p class="stat-value"><?php echo $totalSurveys; ?></p><p class="stat-label">Total Surveys</p></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-modern">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon green"><i class="fas fa-play-circle"></i></div>
                    <div><p class="stat-value"><?php echo $activeSurveys; ?></p><p class="stat-label">Active Surveys</p></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-modern">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon purple"><i class="fas fa-edit"></i></div>
                    <div><p class="stat-value"><?php echo $draftSurveys; ?></p><p class="stat-label">Draft Surveys</p></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-modern">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon red"><i class="fas fa-layer-group"></i></div>
                    <div><p class="stat-value"><?php echo $totalQuestionSets; ?></p><p class="stat-label">Question Sets</p></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inline Create Survey -->
    <div id="createSection" class="create-survey-card" style="display:none;">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-plus-circle text-danger me-2"></i>Create New Survey</h5>
            <button class="btn btn-sm btn-outline-secondary" onclick="this.closest('.create-survey-card').style.display='none'">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="card-body">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="create_survey" value="1">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold"><i class="fas fa-heading me-1"></i>Survey Title *</label>
                        <input type="text" class="form-control" name="survey_title" placeholder="e.g., Student Satisfaction Survey 2026" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold"><i class="fas fa-users me-1"></i>Target Audience *</label>
                        <div class="role-selector">
                            <label class="role-option">
                                <input type="radio" name="survey_target_role" value="student" required>
                                <div class="role-card-select"><i class="fas fa-user-graduate"></i><span>Students</span></div>
                            </label>
                            <label class="role-option">
                                <input type="radio" name="survey_target_role" value="teacher" required>
                                <div class="role-card-select"><i class="fas fa-chalkboard-teacher"></i><span>Teachers</span></div>
                            </label>
                            <label class="role-option">
                                <input type="radio" name="survey_target_role" value="both" required>
                                <div class="role-card-select"><i class="fas fa-users"></i><span>Both</span></div>
                            </label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold"><i class="fas fa-align-left me-1"></i>Description *</label>
                        <textarea class="form-control" name="survey_description" rows="2" placeholder="Describe the purpose of this survey..." required></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-danger"><i class="fas fa-plus me-2"></i>Create Survey</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Survey Cards -->
    <?php if (empty($allSurveys)): ?>
    <div class="empty-state">
        <i class="fas fa-poll"></i>
        <h4>No Surveys Yet</h4>
        <p>Create your first survey to get started!</p>
        <button class="btn btn-danger mt-2" onclick="document.getElementById('createSection').style.display='block'">
            <i class="fas fa-plus me-1"></i>Create First Survey
        </button>
    </div>
    <?php else: ?>
    <div class="survey-grid">
        <?php foreach ($allSurveys as $survey): ?>
        <?php 
            $progress = getSurveyProgress($survey['id'], $conn, null);
            $progressPercent = $progress['total_users'] > 0 ? 
                round(($progress['participants'] / $progress['total_users']) * 100) : 0;
        ?>
        <div class="survey-card status-<?php echo $survey['status']; ?>">
            <div class="survey-card-top">
                <div class="d-flex gap-2">
                    <span class="status-pill <?php echo $survey['status']; ?>">
                        <i class="fas fa-circle" style="font-size:0.5rem"></i>
                        <?php echo ucfirst($survey['status']); ?>
                    </span>
                    <span class="role-pill <?php echo $survey['target_role']; ?>">
                        <?php echo ucfirst($survey['target_role']); ?>
                    </span>
                </div>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" onclick="duplicateSurvey(<?php echo $survey['id']; ?>,'<?php echo addslashes($survey['title']); ?>')"><i class="fas fa-copy me-2"></i>Duplicate</a></li>
                        <?php if ($survey['status'] == 'draft'): ?>
                        <li><a class="dropdown-item text-success" href="#" onclick="updateSurveyStatus(<?php echo $survey['id']; ?>,'active')"><i class="fas fa-play me-2"></i>Activate</a></li>
                        <?php elseif ($survey['status'] == 'active'): ?>
                        <li><a class="dropdown-item text-warning" href="#" onclick="updateSurveyStatus(<?php echo $survey['id']; ?>,'draft')"><i class="fas fa-pause me-2"></i>Set to Draft</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-secondary" href="#" onclick="updateSurveyStatus(<?php echo $survey['id']; ?>,'archived')"><i class="fas fa-archive me-2"></i>Archive</a></li>
                    </ul>
                </div>
            </div>
            <div class="survey-card-body">
                <h3><?php echo htmlspecialchars($survey['title']); ?></h3>
                <p><?php echo htmlspecialchars($survey['description'] ?? 'No description'); ?></p>
            </div>
            <div class="survey-card-stats">
                <div class="stat-item"><i class="fas fa-question-circle"></i> <?php echo $survey['question_count']; ?> questions</div>
                <div class="stat-item"><i class="fas fa-layer-group"></i> <?php echo $survey['question_set_count']; ?> sets</div>
                <div class="stat-item"><i class="fas fa-users"></i> <?php echo $progressPercent; ?>% done</div>
            </div>
            <div class="survey-card-footer">
                <small class="text-muted"><i class="fas fa-calendar me-1"></i><?php echo date('M d, Y', strtotime($survey['created_at'])); ?></small>
                <a href="survey_management.php?survey_id=<?php echo $survey['id']; ?>" class="btn btn-sm btn-danger">
                    <i class="fas fa-cog me-1"></i>Manage
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- ==================== SURVEY DETAIL VIEW ==================== -->
    
    <!-- Breadcrumb -->
    <a href="survey_management.php" class="btn btn-outline-secondary mb-3">
        <i class="fas fa-arrow-left me-2"></i>Back to All Surveys
    </a>

    <!-- Detail Header -->
    <div class="detail-header">
        <div class="info">
            <div class="d-flex align-items-center gap-2 mb-1">
                <h3><?php echo htmlspecialchars($manageSurvey['title']); ?></h3>
                <span class="status-pill <?php echo $manageSurvey['status']; ?>"><?php echo ucfirst($manageSurvey['status']); ?></span>
                <span class="role-pill <?php echo $manageSurvey['target_role']; ?>"><?php echo ucfirst($manageSurvey['target_role']); ?></span>
            </div>
            <p><?php echo htmlspecialchars($manageSurvey['description'] ?? ''); ?></p>
        </div>
        <div class="actions">
            <?php if ($manageSurvey['status'] == 'draft'): ?>
            <button class="btn btn-success btn-sm" onclick="updateSurveyStatus(<?php echo $manageSurveyId; ?>,'active')"><i class="fas fa-play me-1"></i>Activate</button>
            <?php elseif ($manageSurvey['status'] == 'active'): ?>
            <button class="btn btn-warning btn-sm" onclick="updateSurveyStatus(<?php echo $manageSurveyId; ?>,'draft')"><i class="fas fa-pause me-1"></i>Set to Draft</button>
            <?php endif; ?>
            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editSurveySettingsModal"><i class="fas fa-cog me-1"></i>Settings</button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card-modern">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon blue"><i class="fas fa-question-circle"></i></div>
                    <div><p class="stat-value"><?php echo count($manageSurveyQuestions); ?></p><p class="stat-label">Questions</p></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-modern">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon purple"><i class="fas fa-layer-group"></i></div>
                    <div><p class="stat-value"><?php echo count($manageSurveyQuestionSets); ?></p><p class="stat-label">Question Sets</p></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-modern">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon green"><i class="fas fa-users"></i></div>
                    <div><p class="stat-value"><?php echo $progress['participants'] ?? 0; ?></p><p class="stat-label">Responses</p></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-modern">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon red"><i class="fas fa-percentage"></i></div>
                    <div><p class="stat-value"><?php echo $progress['total_users'] > 0 ? round(($progress['participants']/$progress['total_users'])*100) : 0; ?>%</p><p class="stat-label">Completion</p></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Question Sets Section -->
    <div class="section-card">
        <div class="section-header">
            <h4><i class="fas fa-layer-group text-primary me-2"></i>Question Sets & Questions</h4>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary" onclick="document.getElementById('addSetForm').style.display = document.getElementById('addSetForm').style.display === 'none' ? 'block' : 'none'">
                    <i class="fas fa-plus me-1"></i>Add Question Set
                </button>
            </div>
        </div>
        <div class="section-body">
            <!-- Add Question Set Form -->
            <div id="addSetForm" class="inline-form mb-3" style="display:none;">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?survey_id=<?php echo $manageSurveyId; ?>" method="post">
                    <input type="hidden" name="create_question_set" value="1">
                    <input type="hidden" name="survey_id" value="<?php echo $manageSurveyId; ?>">
                    <div class="row g-2">
                        <div class="col-md-5">
                            <label class="form-label" for="new_set_name">Set Name *</label>
                            <input type="text" class="form-control form-control-sm" id="new_set_name" name="set_name" placeholder="e.g., Teaching Quality" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="new_set_desc">Description</label>
                            <input type="text" class="form-control form-control-sm" id="new_set_desc" name="set_description" placeholder="Optional description">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label" for="new_set_order">Order</label>
                            <input type="number" class="form-control form-control-sm" id="new_set_order" name="display_order" value="1" min="1">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-success btn-sm w-100"><i class="fas fa-save me-1"></i>Save</button>
                        </div>
                    </div>
                </form>
            </div>

            <?php if (empty($manageSurveyQuestionSets)): ?>
            <div class="empty-state py-4">
                <i class="fas fa-layer-group"></i>
                <h5>No Question Sets</h5>
                <p>Create a question set first, then add questions to it.</p>
            </div>
            <?php else: ?>
                <?php foreach ($manageSurveyQuestionSets as $qs): ?>
                <div class="qs-item">
                    <div class="qs-item-header" onclick="this.parentElement.querySelector('.qs-item-body').style.display = this.parentElement.querySelector('.qs-item-body').style.display === 'none' ? 'block' : 'none'">
                        <div class="qs-info">
                            <i class="fas fa-layer-group text-primary"></i>
                            <h5><?php echo htmlspecialchars($qs['name']); ?></h5>
                            <?php 
                                $qCount = 0;
                                foreach($manageSurveyQuestions as $q) { if ($q['question_set_id'] == $qs['id']) $qCount++; }
                            ?>
                            <span class="badge bg-<?php echo $qCount > 0 ? 'primary' : 'secondary'; ?>"><?php echo $qCount; ?> questions</span>
                        </div>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation(); toggleAddQ('addQ_<?php echo $qs['id']; ?>')"><i class="fas fa-plus"></i></button>
                            <button class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation(); if(confirm('Delete this question set?')) { submitAction('delete_question_set','set_id',<?php echo $qs['id']; ?>); }"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                    <div class="qs-item-body" style="display:none;">
                        <!-- Add Question to Set -->
                        <div id="addQ_<?php echo $qs['id']; ?>" class="inline-form mb-3" style="display:none;">
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?survey_id=<?php echo $manageSurveyId; ?>" method="post">
                                <input type="hidden" name="add_question_to_set" value="1">
                                <input type="hidden" name="survey_id" value="<?php echo $manageSurveyId; ?>">
                                <input type="hidden" name="question_set_id" value="<?php echo $qs['id']; ?>">
                                <input type="hidden" name="question_type" value="rating">
                                <div class="row g-2">
                                    <div class="col-md-8">
                                        <label class="form-label" for="q_text_<?php echo $qs['id']; ?>">Question Text *</label>
                                        <input type="text" class="form-control form-control-sm" id="q_text_<?php echo $qs['id']; ?>" name="question" placeholder="Enter your question..." required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label" for="q_order_<?php echo $qs['id']; ?>">Order</label>
                                        <input type="number" class="form-control form-control-sm" id="q_order_<?php echo $qs['id']; ?>" name="display_order" value="1" min="1">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-success btn-sm w-100"><i class="fas fa-plus me-1"></i>Add</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Questions in this set -->
                        <?php 
                            $setQuestions = array_filter($manageSurveyQuestions, function($q) use ($qs) { return $q['question_set_id'] == $qs['id']; });
                            $qNum = 1;
                        ?>
                        <?php if (empty($setQuestions)): ?>
                        <p class="text-muted text-center py-2 mb-0"><i class="fas fa-info-circle me-1"></i>No questions yet. Click + to add one.</p>
                        <?php else: ?>
                            <?php foreach ($setQuestions as $q): ?>
                            <div class="q-row">
                                <div class="q-num"><?php echo $qNum++; ?></div>
                                <div class="q-text"><?php echo htmlspecialchars($q['question']); ?></div>
                                <div class="q-actions">
                                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editQuestionModal"
                                        data-id="<?php echo $q['id']; ?>" data-question="<?php echo htmlspecialchars($q['question']); ?>" data-active="<?php echo $q['is_active']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#viewStatsModal"
                                        data-question="<?php echo htmlspecialchars($q['question']); ?>"
                                        data-good="<?php echo $q['stats']['good']; ?>" data-neutral="<?php echo $q['stats']['neutral']; ?>"
                                        data-bad="<?php echo $q['stats']['bad']; ?>" data-total="<?php echo $q['stats']['total']; ?>"
                                        data-role="<?php echo $q['target_role']; ?>">
                                        <i class="fas fa-chart-pie"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" onclick="if(confirm('Delete this question?')) { submitAction('delete_question','question_id',<?php echo $q['id']; ?>); }">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php endif; ?>
</div>

<!-- Edit Question Modal -->
<div class="modal fade modal-modern" id="editQuestionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?><?php echo $manageSurveyId ? '?survey_id='.$manageSurveyId : ''; ?>" method="post">
                <div class="modal-body">
                    <input type="hidden" name="update_question" value="1">
                    <input type="hidden" name="question_id" id="edit_question_id">
                    <div class="mb-3">
                        <label for="edit_question_text" class="form-label fw-semibold">Question Text</label>
                        <textarea class="form-control" id="edit_question_text" name="question" rows="3" required></textarea>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" style="width:3em;height:1.5em;">
                        <label class="form-check-label ms-2" for="edit_is_active"><strong>Active</strong> - Question will appear in surveys</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-save me-2"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Stats Modal -->
<div class="modal fade modal-modern" id="viewStatsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered stats-modal-lg">
        <div class="modal-content">
            <div class="modal-header stats-header">
                <h5 class="modal-title"><i class="fas fa-chart-pie me-2"></i>Question Statistics</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <span id="stats_role_badge" class="badge mb-2"></span>
                    <h6 id="stats_question_text" class="fw-bold text-dark"></h6>
                </div>
                <div id="stats_content">
                    <div class="stats-summary">
                        <div class="stats-card good">
                            <div class="icon"><i class="fas fa-smile"></i></div>
                            <div class="count" id="stats_good_count">0</div>
                            <div class="label">Good</div>
                            <div class="percentage" id="stats_good_percent">0%</div>
                        </div>
                        <div class="stats-card neutral">
                            <div class="icon"><i class="fas fa-meh"></i></div>
                            <div class="count" id="stats_neutral_count">0</div>
                            <div class="label">Neutral</div>
                            <div class="percentage" id="stats_neutral_percent">0%</div>
                        </div>
                        <div class="stats-card bad">
                            <div class="icon"><i class="fas fa-frown"></i></div>
                            <div class="count" id="stats_bad_count">0</div>
                            <div class="label">Bad</div>
                            <div class="percentage" id="stats_bad_percent">0%</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm"><div class="card-body">
                                <h6 class="text-center text-muted mb-3"><i class="fas fa-chart-pie me-2"></i>Distribution</h6>
                                <div class="chart-container"><canvas id="pieChart"></canvas></div>
                            </div></div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm"><div class="card-body">
                                <h6 class="text-center text-muted mb-3"><i class="fas fa-chart-bar me-2"></i>Comparison</h6>
                                <div class="chart-container"><canvas id="barChart"></canvas></div>
                            </div></div>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <span class="badge bg-secondary fs-6 px-3 py-2">
                            <i class="fas fa-users me-2"></i>Total Responses: <strong id="stats_total_count">0</strong>
                        </span>
                    </div>
                    <div class="sentiment-analysis-card">
                        <div class="sentiment-header"><i class="fas fa-brain"></i><h6>AI Sentiment Analysis</h6></div>
                        <div class="sentiment-meter"><div class="sentiment-indicator" id="sentiment_indicator" style="left:50%;"></div></div>
                        <div class="sentiment-labels"><span>😞 Negative</span><span>😐 Neutral</span><span>😊 Positive</span></div>
                        <div class="sentiment-score">
                            <span class="sentiment-badge" id="sentiment_badge"><i class="fas fa-chart-line"></i><span id="sentiment_label">Analyzing...</span></span>
                            <span class="text-muted">Score: <strong id="sentiment_score_value">0</strong>/100</span>
                        </div>
                        <p class="sentiment-description" id="sentiment_description">Based on response distribution analysis</p>
                    </div>
                </div>
                <div id="no_data_content" class="no-data-message" style="display:none;">
                    <i class="fas fa-chart-bar"></i><h5>No Responses Yet</h5>
                    <p class="text-muted">This question hasn't received any feedback yet.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Survey Settings Modal -->
<div class="modal fade modal-modern" id="editSurveySettingsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-cog me-2"></i>Survey Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?><?php echo $manageSurveyId ? '?survey_id='.$manageSurveyId : ''; ?>" method="post">
                <div class="modal-body">
                    <input type="hidden" name="update_survey_settings" value="1">
                    <input type="hidden" name="survey_id" value="<?php echo $manageSurveyId; ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Survey Title</label>
                            <input type="text" class="form-control" name="survey_title" value="<?php echo htmlspecialchars($manageSurvey['title'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Target Audience</label>
                            <select class="form-select" name="survey_target_role">
                                <option value="student" <?php echo ($manageSurvey['target_role'] ?? '') == 'student' ? 'selected':''; ?>>Students</option>
                                <option value="teacher" <?php echo ($manageSurvey['target_role'] ?? '') == 'teacher' ? 'selected':''; ?>>Teachers</option>
                                <option value="both" <?php echo ($manageSurvey['target_role'] ?? '') == 'both' ? 'selected':''; ?>>Both</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea class="form-control" name="survey_description" rows="3"><?php echo htmlspecialchars($manageSurvey['description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-save me-1"></i>Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../core/includes/footer.php'; ?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
let pieChart = null, barChart = null;

document.addEventListener('DOMContentLoaded', function() {
    // Edit Question Modal
    const editModal = document.getElementById('editQuestionModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            if (!btn) return;
            document.getElementById('edit_question_id').value = btn.dataset.id;
            document.getElementById('edit_question_text').value = btn.dataset.question;
            document.getElementById('edit_is_active').checked = btn.dataset.active == 1;
        });
    }
    
    // View Stats Modal
    const statsModal = document.getElementById('viewStatsModal');
    if (statsModal) {
        statsModal.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            if (!btn) return;
            const good = parseInt(btn.dataset.good) || 0;
            const neutral = parseInt(btn.dataset.neutral) || 0;
            const bad = parseInt(btn.dataset.bad) || 0;
            const total = parseInt(btn.dataset.total) || 0;
            const role = btn.dataset.role;
            
            document.getElementById('stats_question_text').textContent = btn.dataset.question;
            const badge = document.getElementById('stats_role_badge');
            badge.className = 'badge mb-2 ' + (role === 'student' ? 'bg-primary' : 'bg-info');
            badge.innerHTML = '<i class="fas fa-' + (role === 'student' ? 'user-graduate' : 'chalkboard-teacher') + ' me-1"></i>' + (role === 'student' ? 'Student' : 'Teacher') + ' Survey';
            
            if (total === 0) {
                document.getElementById('stats_content').style.display = 'none';
                document.getElementById('no_data_content').style.display = 'block';
            } else {
                document.getElementById('stats_content').style.display = 'block';
                document.getElementById('no_data_content').style.display = 'none';
                
                const gp = ((good/total)*100).toFixed(1);
                const np = ((neutral/total)*100).toFixed(1);
                const bp = ((bad/total)*100).toFixed(1);
                
                document.getElementById('stats_good_count').textContent = good;
                document.getElementById('stats_neutral_count').textContent = neutral;
                document.getElementById('stats_bad_count').textContent = bad;
                document.getElementById('stats_total_count').textContent = total;
                document.getElementById('stats_good_percent').textContent = gp + '%';
                document.getElementById('stats_neutral_percent').textContent = np + '%';
                document.getElementById('stats_bad_percent').textContent = bp + '%';
                
                if (pieChart) { pieChart.destroy(); pieChart = null; }
                if (barChart) { barChart.destroy(); barChart = null; }
                
                pieChart = new Chart(document.getElementById('pieChart').getContext('2d'), {
                    type: 'doughnut',
                    data: { labels: ['Good 😊', 'Neutral 😐', 'Bad 😞'],
                        datasets: [{ data: [good, neutral, bad],
                            backgroundColor: ['rgba(16,185,129,0.8)', 'rgba(245,158,11,0.8)', 'rgba(239,68,68,0.8)'],
                            borderColor: ['rgba(16,185,129,1)', 'rgba(245,158,11,1)', 'rgba(239,68,68,1)'],
                            borderWidth: 2, hoverOffset: 10 }] },
                    options: { responsive: true, maintainAspectRatio: false, cutout: '60%',
                        plugins: { legend: { position: 'bottom', labels: { padding: 15, usePointStyle: true } } } }
                });
                
                barChart = new Chart(document.getElementById('barChart').getContext('2d'), {
                    type: 'bar',
                    data: { labels: ['Good', 'Neutral', 'Bad'],
                        datasets: [{ label: 'Responses', data: [good, neutral, bad],
                            backgroundColor: ['rgba(16,185,129,0.8)', 'rgba(245,158,11,0.8)', 'rgba(239,68,68,0.8)'],
                            borderColor: ['rgba(16,185,129,1)', 'rgba(245,158,11,1)', 'rgba(239,68,68,1)'],
                            borderWidth: 2, borderRadius: 8 }] },
                    options: { responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true }, x: { grid: { display: false } } } }
                });
                
                updateSentiment(good, neutral, bad, total);
            }
        });
        
        statsModal.addEventListener('hidden.bs.modal', function() {
            if (pieChart) { pieChart.destroy(); pieChart = null; }
            if (barChart) { barChart.destroy(); barChart = null; }
        });
    }
});

function updateSentiment(good, neutral, bad, total) {
    if (total === 0) return;
    const score = Math.round(((good * 100) + (neutral * 50)) / total);
    document.getElementById('sentiment_indicator').style.left = score + '%';
    document.getElementById('sentiment_score_value').textContent = score;
    const badge = document.getElementById('sentiment_badge');
    const label = document.getElementById('sentiment_label');
    const desc = document.getElementById('sentiment_description');
    badge.classList.remove('positive','neutral','negative');
    if (score >= 70) { badge.classList.add('positive'); label.innerHTML = '<i class="fas fa-smile me-1"></i> Highly Positive';
        desc.textContent = 'Excellent! ' + Math.round((good/total)*100) + '% gave positive feedback.'; }
    else if (score >= 55) { badge.classList.add('positive'); label.innerHTML = '<i class="fas fa-smile me-1"></i> Positive';
        desc.textContent = 'Good overall sentiment with room for improvement.'; }
    else if (score >= 45) { badge.classList.add('neutral'); label.innerHTML = '<i class="fas fa-meh me-1"></i> Neutral';
        desc.textContent = 'Mixed feedback. Consider gathering more specific input.'; }
    else { badge.classList.add('negative'); label.innerHTML = '<i class="fas fa-frown me-1"></i> Negative';
        desc.textContent = 'Attention needed. ' + Math.round((bad/total)*100) + '% gave negative feedback.'; }
}

function toggleAddQ(id) {
    const el = document.getElementById(id);
    if (el) {
        const isHidden = el.style.display === 'none';
        el.style.display = isHidden ? 'block' : 'none';
        // Also expand the parent accordion body
        if (isHidden) {
            const body = el.closest('.qs-item-body');
            if (body) body.style.display = 'block';
        }
    }
}

function submitAction(action, paramName, paramValue) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = window.location.href;
    const i1 = document.createElement('input'); i1.type='hidden'; i1.name=action; i1.value='1'; form.appendChild(i1);
    const i2 = document.createElement('input'); i2.type='hidden'; i2.name=paramName; i2.value=paramValue; form.appendChild(i2);
    document.body.appendChild(form);
    form.submit();
}

function updateSurveyStatus(surveyId, status) {
    const msg = status === 'archived' ? 'Archive this survey?' : 'Change status to ' + status + '?';
    if (!confirm(msg)) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = window.location.href;
    const fields = { 'update_survey_status': '1', 'survey_id': surveyId, 'status': status };
    for (const [k, v] of Object.entries(fields)) {
        const inp = document.createElement('input'); inp.type='hidden'; inp.name=k; inp.value=v; form.appendChild(inp);
    }
    document.body.appendChild(form);
    form.submit();
}

function duplicateSurvey(surveyId, title) {
    const newTitle = prompt('Enter title for the duplicate:', title + ' (Copy)');
    if (!newTitle) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = window.location.href;
    const fields = { 'duplicate_survey': '1', 'survey_id': surveyId, 'new_title': newTitle };
    for (const [k, v] of Object.entries(fields)) {
        const inp = document.createElement('input'); inp.type='hidden'; inp.name=k; inp.value=v; form.appendChild(inp);
    }
    document.body.appendChild(form);
    form.submit();
}
</script>

<?php
// Close connection
closeConnection($conn);
?>
