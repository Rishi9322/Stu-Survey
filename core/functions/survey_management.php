<?php
// Survey Management Enhancement Functions
// Date: January 28, 2026
// Purpose: Core functions for comprehensive survey management

/**
 * Survey Management Functions
 */

// Create a new survey
function createSurvey($title, $description, $targetRole, $createdBy, $conn) {
    $sql = "INSERT INTO surveys (title, description, target_role, created_by) VALUES (?, ?, ?, ?)";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "sssi", $title, $description, $targetRole, $createdBy);
        if (mysqli_stmt_execute($stmt)) {
            $surveyId = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);
            return $surveyId;
        }
        mysqli_stmt_close($stmt);
    }
    return false;
}

// Get list of surveys with optional filtering
function getSurveyList($conn, $status = null, $targetRole = null) {
    $sql = "SELECT s.*, u.username as created_by_name, 
            COUNT(DISTINCT sq.id) as question_count,
            COUNT(DISTINCT qs.id) as question_set_count
            FROM surveys s 
            LEFT JOIN users u ON s.created_by = u.id
            LEFT JOIN survey_questions sq ON s.id = sq.survey_id
            LEFT JOIN question_sets qs ON s.id = qs.survey_id
            WHERE 1=1";
    
    $params = [];
    $types = "";
    
    if ($status) {
        $sql .= " AND s.status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    if ($targetRole) {
        $sql .= " AND (s.target_role = ? OR s.target_role = 'both')";
        $params[] = $targetRole;
        $types .= "s";
    }
    
    $sql .= " GROUP BY s.id ORDER BY s.updated_at DESC";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $surveys = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $surveys[] = $row;
            }
            mysqli_stmt_close($stmt);
            return $surveys;
        }
        mysqli_stmt_close($stmt);
    }
    return [];
}

// Update survey status
function updateSurveyStatus($surveyId, $status, $conn) {
    $sql = "UPDATE surveys SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "si", $status, $surveyId);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $success;
    }
    return false;
}

// Duplicate/copy survey with all questions
function duplicateSurvey($surveyId, $newTitle, $conn) {
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Get original survey
        $sql = "SELECT * FROM surveys WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $surveyId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $survey = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if (!$survey) throw new Exception("Survey not found");
        
        // Create new survey
        $newSurveyId = createSurvey($newTitle, $survey['description'] . " (Copy)", $survey['target_role'], $survey['created_by'], $conn);
        if (!$newSurveyId) throw new Exception("Failed to create survey copy");
        
        // Copy question sets
        $sql = "SELECT * FROM question_sets WHERE survey_id = ? ORDER BY display_order";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $surveyId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($questionSet = mysqli_fetch_assoc($result)) {
            $newSetId = createQuestionSet($newSurveyId, $questionSet['name'], $questionSet['description'], $questionSet['display_order'], $conn);
            
            // Copy questions in this set
            $questionSql = "SELECT * FROM survey_questions WHERE question_set_id = ? ORDER BY display_order";
            $questionStmt = mysqli_prepare($conn, $questionSql);
            mysqli_stmt_bind_param($questionStmt, "i", $questionSet['id']);
            mysqli_stmt_execute($questionStmt);
            $questionResult = mysqli_stmt_get_result($questionStmt);
            
            while ($question = mysqli_fetch_assoc($questionResult)) {
                addQuestionToSet($newSetId, $question['question'], $question['question_type'], $question['display_order'], $question['target_role'], $conn);
            }
            mysqli_stmt_close($questionStmt);
        }
        mysqli_stmt_close($stmt);
        
        mysqli_commit($conn);
        return $newSurveyId;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        return false;
    }
}

/**
 * Question Set Management Functions
 */

// Create a new question set
function createQuestionSet($surveyId, $name, $description, $conn, $displayOrder = 0) {
    $sql = "INSERT INTO question_sets (survey_id, name, description, display_order) VALUES (?, ?, ?, ?)";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "issi", $surveyId, $name, $description, $displayOrder);
        if (mysqli_stmt_execute($stmt)) {
            $questionSetId = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);
            return $questionSetId;
        }
        mysqli_stmt_close($stmt);
    }
    return false;
}

// Get question sets for a survey
function getQuestionSets($surveyId, $conn) {
    $sql = "SELECT qs.*, COUNT(sq.id) as question_count 
            FROM question_sets qs 
            LEFT JOIN survey_questions sq ON qs.id = sq.question_set_id 
            WHERE qs.survey_id = ? 
            GROUP BY qs.id 
            ORDER BY qs.display_order";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $surveyId);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $questionSets = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $questionSets[] = $row;
            }
            mysqli_stmt_close($stmt);
            return $questionSets;
        }
        mysqli_stmt_close($stmt);
    }
    return [];
}

// Get all question sets with question counts (for all surveys)
function getQuestionSetsWithCounts($conn) {
    $sql = "SELECT qs.*, COUNT(sq.id) as question_count 
            FROM question_sets qs 
            LEFT JOIN survey_questions sq ON qs.id = sq.question_set_id 
            GROUP BY qs.id 
            ORDER BY qs.survey_id, qs.display_order";
    
    $result = mysqli_query($conn, $sql);
    if ($result) {
        $questionSets = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $questionSets[] = $row;
        }
        return $questionSets;
    }
    return [];
}

// Add question to a question set
function addQuestionToSet($questionSetId, $question, $questionType, $displayOrder, $targetRole, $conn) {
    // Get survey_id from question_set_id
    $sql = "SELECT survey_id FROM question_sets WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $questionSetId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $surveyId = $row['survey_id'];
    mysqli_stmt_close($stmt);
    
    $sql = "INSERT INTO survey_questions (survey_id, question_set_id, question, target_role, question_type, display_order) VALUES (?, ?, ?, ?, ?, ?)";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "iisssi", $surveyId, $questionSetId, $question, $targetRole, $questionType, $displayOrder);
        if (mysqli_stmt_execute($stmt)) {
            $questionId = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);
            return $questionId;
        }
        mysqli_stmt_close($stmt);
    }
    return false;
}

// Get questions by set
function getQuestionsBySet($questionSetId, $conn) {
    $sql = "SELECT * FROM survey_questions WHERE question_set_id = ? ORDER BY display_order";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $questionSetId);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $questions = [];
            while ($row = mysqli_fetch_assoc($result)) {
                // Get response statistics
                $row['stats'] = getQuestionStats($conn, $row['id']);
                $questions[] = $row;
            }
            mysqli_stmt_close($stmt);
            return $questions;
        }
        mysqli_stmt_close($stmt);
    }
    return [];
}

/**
 * Survey Session Management Functions (for restart functionality)
 */

// Create a new survey session
function createSurveySession($surveyId, $sessionName, $startDate, $conn, $endDate = null) {
    $sql = "INSERT INTO survey_sessions (survey_id, session_name, start_date, end_date, status) VALUES (?, ?, ?, ?, 'scheduled')";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "isss", $surveyId, $sessionName, $startDate, $endDate);
        if (mysqli_stmt_execute($stmt)) {
            $sessionId = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);
            return $sessionId;
        }
        mysqli_stmt_close($stmt);
    }
    return false;
}

// Restart survey by creating new session
function restartSurvey($surveyId, $sessionName, $conn) {
    // Archive current active session if exists
    $sql = "UPDATE survey_sessions SET status = 'completed' WHERE survey_id = ? AND status = 'active'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $surveyId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    // Create new session
    $startDate = date('Y-m-d');
    return createSurveySession($surveyId, $sessionName, $startDate, $conn, null);
}

// Get survey sessions
function getSurveySessions($surveyId, $conn) {
    $sql = "SELECT ss.*, COUNT(DISTINCT sr.user_id) as participant_count,
            COUNT(sr.id) as response_count
            FROM survey_sessions ss
            LEFT JOIN survey_responses sr ON ss.id = sr.survey_session_id
            WHERE ss.survey_id = ?
            GROUP BY ss.id
            ORDER BY ss.created_at DESC";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $surveyId);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $sessions = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $sessions[] = $row;
            }
            mysqli_stmt_close($stmt);
            return $sessions;
        }
        mysqli_stmt_close($stmt);
    }
    return [];
}

// Get survey progress for a session
function getSurveyProgress($surveyId, $conn, $sessionId = null) {
    $sql = "SELECT 
                COUNT(DISTINCT u.id) as total_users,
                COUNT(DISTINCT sr.user_id) as participants,
                COUNT(sr.id) as total_responses,
                s.target_role
            FROM surveys s
            LEFT JOIN users u ON (
                (s.target_role = 'both' AND u.role IN ('student','teacher'))
                OR (s.target_role <> 'both' AND s.target_role = u.role)
            )
            LEFT JOIN survey_responses sr ON u.id = sr.user_id 
                AND sr.question_id IN (SELECT id FROM survey_questions WHERE survey_id = ?)";
    
    $params = [$surveyId];
    $types = "i";
    
    if ($sessionId) {
        $sql .= " AND sr.survey_session_id = ?";
        $params[] = $sessionId;
        $types .= "i";
    }
    
    $sql .= " WHERE s.id = ?";
    $params[] = $surveyId;
    $types .= "i";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $progress = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            return $progress;
        }
        mysqli_stmt_close($stmt);
    }
    return ['total_users' => 0, 'participants' => 0, 'total_responses' => 0];
}

/**
 * Enhanced Statistics Functions
 */

// Get comprehensive survey statistics
function getAdvancedSurveyStatistics($conn, $surveyId = null, $sessionId = null) {
    $whereClause = "WHERE 1=1";
    $params = [];
    $types = "";
    
    if ($surveyId) {
        $whereClause .= " AND sq.survey_id = ?";
        $params[] = $surveyId;
        $types .= "i";
    }
    
    if ($sessionId) {
        $whereClause .= " AND sr.survey_session_id = ?";
        $params[] = $sessionId;
        $types .= "i";
    }
    
    $sql = "SELECT 
                COUNT(DISTINCT sr.user_id) as total_participants,
                COUNT(sr.id) as total_responses,
                AVG(CASE 
                    WHEN sr.rating = 'good' THEN 5
                    WHEN sr.rating = 'neutral' THEN 3
                    WHEN sr.rating = 'bad' THEN 1
                    ELSE 3
                END) as average_rating,
                COUNT(CASE WHEN sr.rating = 'good' THEN 1 END) as good_responses,
                COUNT(CASE WHEN sr.rating = 'neutral' THEN 1 END) as neutral_responses,
                COUNT(CASE WHEN sr.rating = 'bad' THEN 1 END) as bad_responses
            FROM survey_responses sr
            JOIN survey_questions sq ON sr.question_id = sq.id
            $whereClause";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $stats = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            return $stats;
        }
        mysqli_stmt_close($stmt);
    }
    return [
        'total_participants' => 0,
        'total_responses' => 0,
        'average_rating' => 0,
        'good_responses' => 0,
        'neutral_responses' => 0,
        'bad_responses' => 0
    ];
}

// Get template questions for reuse
function getTemplateQuestions($conn, $targetRole = null) {
    $sql = "SELECT * FROM survey_questions WHERE is_template = 1";
    $params = [];
    $types = "";
    
    if ($targetRole) {
        $sql .= " AND (target_role = ? OR target_role = 'both')";
        $params[] = $targetRole;
        $types .= "s";
    }
    
    $sql .= " ORDER BY question";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $templates = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $templates[] = $row;
            }
            mysqli_stmt_close($stmt);
            return $templates;
        }
        mysqli_stmt_close($stmt);
    }
    return [];
}
?>