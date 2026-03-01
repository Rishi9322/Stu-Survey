<?php
// Session handling
function startSecureSession() {
    // Check if session is already started
    if (session_status() == PHP_SESSION_ACTIVE) {
        return;
    }
    
    // Set session cookie parameters for security
    $session_name = 'secure_session';
    $secure = false; // Set to true if using HTTPS
    $httponly = true; // Prevents JavaScript access to session cookie
    
    // Force session to use cookies only
    ini_set('session.use_only_cookies', 1);
    
    // Get current cookie params
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params(
        $cookieParams["lifetime"],
        $cookieParams["path"],
        $cookieParams["domain"],
        $secure,
        $httponly
    );
    
    // Set the session name
    session_name($session_name);
    
    // Start the session
    session_start();
    
    // Regenerate session ID to prevent session fixation attacks
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        // Regenerate session ID every 30 minutes
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

// User authentication functions
function loginUser($email, $password, $role, $conn) {
    // For debugging
    error_log("loginUser called with email: $email, role: $role");
    
    // Make sure session is active
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    // Prepare a select statement
    $sql = "SELECT id, username, email, password, role FROM users WHERE email = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "s", $email);
        
        // Attempt to execute the prepared statement
        if (mysqli_stmt_execute($stmt)) {
            // Store result
            mysqli_stmt_store_result($stmt);
            
            // Check if email exists, if yes then verify password
            if (mysqli_stmt_num_rows($stmt) == 1) {
                // Bind result variables
                mysqli_stmt_bind_result($stmt, $id, $username, $db_email, $hashed_password, $user_role);
                
                if (mysqli_stmt_fetch($stmt)) {
                    // For debugging
                    error_log("User found: $username, role: $user_role, requested role: $role");
                    
                    // Check if the roles match
                    if ($role !== $user_role) {
                        error_log("Role mismatch: requested $role but user is $user_role");
                        mysqli_stmt_close($stmt);
                        return false;
                    }
                    
                    // Verify password
                    if (password_verify($password, $hashed_password)) {
                        // For debugging
                        error_log("Password verified successfully");
                        
                        // Store data in session variables
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["name"] = $username;  // Store username in the name session variable
                        $_SESSION["email"] = $db_email;
                        $_SESSION["role"] = $user_role;
                        
                        // Set login success flag for confirmation message
                        $_SESSION['login_success'] = true;
                        
                        // For debugging
                        error_log("Login successful for: $username, role: $user_role, session_id: " . session_id());
                        error_log("Session data: " . print_r($_SESSION, true));
                        
                        // Remove the JavaScript alert as it may be causing redirection issues
                        // Instead, rely on the session message that will be displayed on the dashboard
                        
                        // Close the statement before returning
                        mysqli_stmt_close($stmt);
                        
                        return true;
                    } else {
                        // Password is not valid
                        return false;
                    }
                }
            } else {
                // Email doesn't exist
                return false;
            }
        } else {
            echo "Oops! Something went wrong. Please try again later.";
            return false;
        }
        
        // Close statement
        mysqli_stmt_close($stmt);
    }
    
    return false;
}

// Register new user
function registerUser($name, $email, $password, $dob, $role, $conn) {
    // Prepare an insert statement
    $sql = "INSERT INTO users (username, email, password, dob, role) VALUES (?, ?, ?, ?, ?)";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $hashed_password, $dob, $role);
        
        // Attempt to execute the prepared statement
        if (mysqli_stmt_execute($stmt)) {
            // Get the user ID for profile creation
            $user_id = mysqli_insert_id($conn);
            
            // Close statement
            mysqli_stmt_close($stmt);
            
            return $user_id;
        } else {
            echo "Error: " . mysqli_error($conn);
            // Close statement
            mysqli_stmt_close($stmt);
            return false;
        }
    }
    
    return false;
}

// Check if user is logged in
function isLoggedIn() {
    // For debugging
    error_log("isLoggedIn called, session_status: " . session_status());
    
    // Make sure session is started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if the session variable exists and is set to true
    $loggedIn = isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true && 
                isset($_SESSION["id"]) && isset($_SESSION["role"]);
    
    error_log("isLoggedIn result: " . ($loggedIn ? "true" : "false") . ", Session data: " . print_r($_SESSION, true));
    
    return $loggedIn;
}

// Check if user has specific role
function hasRole($role) {
    // For debugging
    error_log("hasRole called with role: $role");
    
    if (!isLoggedIn()) {
        error_log("hasRole: user is not logged in");
        return false;
    }
    
    $hasRole = $_SESSION["role"] === $role;
    error_log("hasRole result: " . ($hasRole ? "true" : "false") . ", User role: " . $_SESSION["role"]);
    
    return $hasRole;
}

// Redirect to appropriate dashboard based on role
function redirectToDashboard() {
    // For debugging
    error_log("redirectToDashboard called");
    
    // Check if output buffering is active and clean it
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    if (!isLoggedIn()) {
        error_log("Not logged in, redirecting to index.php");
        header("location: index.php");
        exit;
    }
    
    // Debug: Log session info
    error_log("Redirecting user: " . $_SESSION["name"] . " with role: " . $_SESSION["role"] . ", ID: " . $_SESSION["id"]);
    
    // Determine base path (whether we're in a subdirectory or root)
    $current_path = $_SERVER['PHP_SELF'];
    error_log("Current path: " . $current_path);
    
    $is_in_subdirectory = (strpos($current_path, '/admin/') !== false || 
                           strpos($current_path, '/student/') !== false || 
                           strpos($current_path, '/teacher/') !== false);
    
    $base_path = $is_in_subdirectory ? "../" : "";
    error_log("Base path: " . $base_path);
    
    // Redirect based on role
    $targetLocation = "";
    
    switch ($_SESSION["role"]) {
        case "student":
            $targetLocation = "{$base_path}student/dashboard.php";
            break;
        case "teacher":
            $targetLocation = "{$base_path}teacher/dashboard.php";
            break;
        case "admin":
            $targetLocation = "{$base_path}admin/dashboard.php";
            break;
        default:
            $targetLocation = "{$base_path}index.php";
    }
    
    error_log("Final redirect target: " . $targetLocation);
    
    // Perform the redirect
    header("Location: " . $targetLocation);
    exit; // Ensure script execution stops here
}

// Sanitize input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Get user profile data
function getUserProfileData($userId, $role, $conn) {
    $profile = [];
    
    if ($role === 'student') {
        $sql = "SELECT s.division, s.roll_no, s.course, u.username as name, u.email, u.dob 
                FROM student_profiles s 
                JOIN users u ON s.user_id = u.id 
                WHERE s.user_id = ?";
    } else if ($role === 'teacher') {
        $sql = "SELECT t.department, t.subjects, t.experience, u.username as name, u.email, u.dob 
                FROM teacher_profiles t 
                JOIN users u ON t.user_id = u.id 
                WHERE t.user_id = ?";
    } else {
        $sql = "SELECT username as name, email, dob FROM users WHERE id = ?";
    }
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $userId);
        
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            if ($row = mysqli_fetch_assoc($result)) {
                $profile = $row;
            }
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return $profile;
}

// Function to check if a survey is completed by a user
// When $surveyId is provided, checks completion for that specific survey only
function isSurveyCompleted($userId, $targetRole, $conn, $surveyId = null) {
    if ($surveyId) {
        // Per-survey completion check
        $sql = "SELECT COUNT(*) AS total_questions FROM survey_questions 
                WHERE survey_id = ? AND is_active = 1 AND (target_role = ? OR target_role = 'both')";
        $totalQuestions = 0;
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "is", $surveyId, $targetRole);
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($result)) {
                    $totalQuestions = $row['total_questions'];
                }
            }
            mysqli_stmt_close($stmt);
        }
        
        $sql = "SELECT COUNT(*) AS answered_questions FROM survey_responses 
                WHERE user_id = ? AND question_id IN 
                (SELECT id FROM survey_questions WHERE survey_id = ? AND is_active = 1 AND (target_role = ? OR target_role = 'both'))";
        $answeredQuestions = 0;
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "iis", $userId, $surveyId, $targetRole);
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($result)) {
                    $answeredQuestions = $row['answered_questions'];
                }
            }
            mysqli_stmt_close($stmt);
        }
        
        return ($totalQuestions > 0 && $answeredQuestions >= $totalQuestions);
    }
    
    // Legacy global completion check (fallback)
    $sql = "SELECT COUNT(*) AS total_questions FROM survey_questions WHERE target_role = ?";
    $totalQuestions = 0;
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $targetRole);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($result)) {
                $totalQuestions = $row['total_questions'];
            }
        }
        mysqli_stmt_close($stmt);
    }
    
    $sql = "SELECT COUNT(*) AS answered_questions FROM survey_responses 
            WHERE user_id = ? AND question_id IN 
            (SELECT id FROM survey_questions WHERE target_role = ?)";
    $answeredQuestions = 0;
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "is", $userId, $targetRole);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($result)) {
                $answeredQuestions = $row['answered_questions'];
            }
        }
        mysqli_stmt_close($stmt);
    }
    
    return ($totalQuestions > 0 && $answeredQuestions >= $totalQuestions);
}

// Get survey questions
// When $surveyId is provided, only returns questions for that specific active survey
function getSurveyQuestions($targetRole, $conn, $surveyId = null) {
    $questions = [];
    
    if ($surveyId) {
        // Get questions for a specific active survey
        $sql = "SELECT sq.id, sq.question, sq.question_type, qs.name as set_name
                FROM survey_questions sq
                JOIN surveys s ON sq.survey_id = s.id
                LEFT JOIN question_sets qs ON sq.question_set_id = qs.id
                WHERE sq.survey_id = ? AND s.status = 'active' 
                AND sq.is_active = 1 AND (sq.target_role = ? OR sq.target_role = 'both')
                ORDER BY qs.display_order ASC, sq.display_order ASC";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "is", $surveyId, $targetRole);
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                while ($row = mysqli_fetch_assoc($result)) {
                    $questions[] = $row;
                }
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        // Legacy: get all active questions for the role (fallback)
        $sql = "SELECT id, question FROM survey_questions WHERE target_role = ? AND is_active = 1";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $targetRole);
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                while ($row = mysqli_fetch_assoc($result)) {
                    $questions[] = $row;
                }
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    return $questions;
}

// Get all active surveys available for a specific role, with completion status per user
function getActiveSurveysForRole($role, $userId, $conn) {
    $surveys = [];
    
    $sql = "SELECT s.id, s.title, s.description, s.target_role, s.created_at,
            COUNT(DISTINCT sq.id) as question_count,
            COUNT(DISTINCT sr.id) as user_response_count
            FROM surveys s
            LEFT JOIN survey_questions sq ON s.id = sq.survey_id AND sq.is_active = 1 
                AND (sq.target_role = ? OR sq.target_role = 'both')
            LEFT JOIN survey_responses sr ON sq.id = sr.question_id AND sr.user_id = ?
            WHERE s.status = 'active' AND (s.target_role = ? OR s.target_role = 'both')
            GROUP BY s.id
            HAVING question_count > 0
            ORDER BY s.created_at DESC";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "sis", $role, $userId, $role);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($result)) {
                $row['is_completed'] = ($row['question_count'] > 0 && $row['user_response_count'] >= $row['question_count']);
                $surveys[] = $row;
            }
        }
        mysqli_stmt_close($stmt);
    }
    
    return $surveys;
}

// Submit survey responses
function submitSurveyResponses($userId, $responses, $conn) {
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        foreach ($responses as $questionId => $rating) {
            $sql = "INSERT INTO survey_responses (user_id, question_id, rating) VALUES (?, ?, ?)";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "iis", $userId, $questionId, $rating);
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception(mysqli_error($conn));
                }
                
                mysqli_stmt_close($stmt);
            } else {
                throw new Exception(mysqli_error($conn));
            }
        }
        
        // Commit transaction
        mysqli_commit($conn);
        return true;
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        echo "Error: " . $e->getMessage();
        return false;
    }
}

// Submit suggestion or complaint
function submitFeedback($userId, $type, $content, $conn) {
    $sql = "INSERT INTO suggestions_complaints (user_id, type, content) VALUES (?, ?, ?)";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "iss", $userId, $type, $content);
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return true;
        } else {
            mysqli_stmt_close($stmt);
            return false;
        }
    }
    
    return false;
}

// Get all teachers for student survey
function getAllTeachers($conn) {
    $teachers = [];
    
    $sql = "SELECT u.id, u.username as name, tp.department, tp.subjects 
            FROM users u 
            JOIN teacher_profiles tp ON u.id = tp.user_id 
            WHERE u.role = 'teacher'";
    
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $teachers[] = $row;
        }
    }
    
    return $teachers;
}

// Get survey statistics
function getSurveyStatistics($conn) {
    $stats = [
        'total_students' => 0,
        'total_teachers' => 0,
        'students_completed' => 0,
        'teachers_completed' => 0,
        'ratings_distribution' => [
            '5' => 0,  // Excellent
            '4' => 0,  // Good
            '3' => 0,  // Average
            '2' => 0,  // Poor
            '1' => 0   // Very Poor
        ]
    ];
    
    // Get total users by role
    $sql = "SELECT role, COUNT(*) as count FROM users WHERE is_active = 1 GROUP BY role";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            if ($row['role'] === 'student') {
                $stats['total_students'] = (int)$row['count'];
            } else if ($row['role'] === 'teacher') {
                $stats['total_teachers'] = (int)$row['count'];
            }
        }
    }
    
    // Get completed surveys for students
    $sql = "SELECT COUNT(DISTINCT user_id) as count FROM survey_responses sr 
            JOIN users u ON sr.user_id = u.id 
            WHERE u.role = 'student' AND u.is_active = 1";
    $result = mysqli_query($conn, $sql);
    
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $stats['students_completed'] = (int)$row['count'];
    }
    
    // Get completed surveys for teachers
    $sql = "SELECT COUNT(DISTINCT user_id) as count FROM survey_responses sr 
            JOIN users u ON sr.user_id = u.id 
            WHERE u.role = 'teacher' AND u.is_active = 1";
    $result = mysqli_query($conn, $sql);
    
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $stats['teachers_completed'] = (int)$row['count'];
    }
    
    // Get ratings distribution (numeric ratings 1-5)
    $sql = "SELECT rating, COUNT(*) as count FROM survey_responses WHERE rating BETWEEN 1 AND 5 GROUP BY rating ORDER BY rating DESC";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rating = (string)$row['rating'];
            $stats['ratings_distribution'][$rating] = (int)$row['count'];
        }
    }
    
    return $stats;
}

// Get course list
function getCourseList($conn) {
    $courses = [];
    
    $sql = "SELECT DISTINCT course FROM student_profiles WHERE course IS NOT NULL AND course != ''";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $courses[] = $row['course'];
        }
    }
    
    return $courses;
}

// Get department list
function getDepartmentList($conn) {
    $departments = [];
    
    $sql = "SELECT DISTINCT department FROM teacher_profiles WHERE department IS NOT NULL AND department != ''";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $departments[] = $row['department'];
        }
    }
    
    return $departments;
}

// Get division list
function getDivisionList($conn) {
    $divisions = [];
    
    $sql = "SELECT DISTINCT division FROM student_profiles WHERE division IS NOT NULL AND division != ''";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $div = $row['division'];
            // Normalize: treat "CS" as "Computer Science" (e.g. "CS-A" => "Computer Science A", "CS-B" => "Computer Science B")
            $div = preg_replace('/^CS[\s\-]*/i', 'Computer Science ', $div);
            $div = trim($div);
            if (!in_array($div, $divisions)) {
                $divisions[] = $div;
            }
        }
    }
    
    // Sort alphabetically for consistent ordering
    sort($divisions, SORT_STRING | SORT_FLAG_CASE);
    
    return $divisions;
}

// ==================== ACCESS CODE FUNCTIONS ====================

/**
 * Validate an access code for teacher/admin registration
 * @param string $code The access code to validate
 * @param string $role The role (teacher or admin)
 * @param mysqli $conn Database connection
 * @return array ['valid' => bool, 'message' => string, 'code_id' => int|null]
 */
function validateAccessCode($code, $role, $conn) {
    $result = ['valid' => false, 'message' => '', 'code_id' => null];
    
    if (empty($code)) {
        $result['message'] = 'Access code is required for ' . ucfirst($role) . ' registration.';
        return $result;
    }
    
    $sql = "SELECT id, code, role, max_uses, current_uses, is_active, expires_at 
            FROM access_codes 
            WHERE code = ? AND role = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $code, $role);
        
        if (mysqli_stmt_execute($stmt)) {
            $queryResult = mysqli_stmt_get_result($stmt);
            
            if ($row = mysqli_fetch_assoc($queryResult)) {
                // Check if code is active
                if (!$row['is_active']) {
                    $result['message'] = 'This access code has been deactivated.';
                    mysqli_stmt_close($stmt);
                    return $result;
                }
                
                // Check if code has expired
                if ($row['expires_at'] !== null && strtotime($row['expires_at']) < time()) {
                    $result['message'] = 'This access code has expired.';
                    mysqli_stmt_close($stmt);
                    return $result;
                }
                
                // Check if code has reached max uses
                if ($row['max_uses'] > 0 && $row['current_uses'] >= $row['max_uses']) {
                    $result['message'] = 'This access code has reached its maximum usage limit.';
                    mysqli_stmt_close($stmt);
                    return $result;
                }
                
                // Code is valid
                $result['valid'] = true;
                $result['code_id'] = $row['id'];
                $result['message'] = 'Access code validated successfully.';
            } else {
                $result['message'] = 'Invalid access code for ' . ucfirst($role) . ' registration.';
            }
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return $result;
}

/**
 * Use an access code (increment usage count and log)
 * @param int $codeId The access code ID
 * @param int $userId The user ID who used the code
 * @param mysqli $conn Database connection
 * @return bool Success status
 */
function useAccessCode($codeId, $userId, $conn) {
    mysqli_begin_transaction($conn);
    
    try {
        // Increment usage count
        $sql = "UPDATE access_codes SET current_uses = current_uses + 1 WHERE id = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $codeId);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Failed to update code usage count");
            }
            mysqli_stmt_close($stmt);
        }
        
        // Log the usage
        $sql = "INSERT INTO access_code_usage (code_id, user_id) VALUES (?, ?)";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ii", $codeId, $userId);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Failed to log code usage");
            }
            mysqli_stmt_close($stmt);
        }
        
        mysqli_commit($conn);
        return true;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Error using access code: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate a new access code
 * @param string $role The role (teacher or admin)
 * @param int $maxUses Maximum number of uses (0 = unlimited)
 * @param string $description Description of the code
 * @param string|null $expiresAt Expiration date (Y-m-d H:i:s format or null)
 * @param int $createdBy User ID of the admin creating the code
 * @param mysqli $conn Database connection
 * @return array ['success' => bool, 'code' => string|null, 'message' => string]
 */
/**
 * Generate a new access code
 * @param string $role Role this code is for (teacher or admin)
 * @param int $maxUses Maximum number of uses
 * @param string $description Description of the code
 * @param string|null $expiresAt Expiration date
 * @param int $createdBy User ID who created the code
 * @param mysqli $conn Database connection
 * @param string|null $customCode Optional custom code
 * @return array Result with success status, code, and message
 */
function generateAccessCode($role, $maxUses, $description, $expiresAt, $createdBy, $conn, $customCode = null) {
    $result = ['success' => false, 'code' => null, 'message' => ''];
    
    // Use custom code or generate a unique one
    if (!empty($customCode)) {
        $code = strtoupper(preg_replace('/[^A-Z0-9_-]/i', '', $customCode));
        
        // Check if custom code already exists
        $checkSql = "SELECT id FROM access_codes WHERE code = ?";
        if ($checkStmt = mysqli_prepare($conn, $checkSql)) {
            mysqli_stmt_bind_param($checkStmt, "s", $code);
            mysqli_stmt_execute($checkStmt);
            mysqli_stmt_store_result($checkStmt);
            
            if (mysqli_stmt_num_rows($checkStmt) > 0) {
                mysqli_stmt_close($checkStmt);
                $result['message'] = 'This access code already exists. Please choose a different one.';
                return $result;
            }
            mysqli_stmt_close($checkStmt);
        }
    } else {
        $code = strtoupper(substr($role, 0, 3)) . '_' . bin2hex(random_bytes(4)) . '_' . date('Y');
    }
    
    $sql = "INSERT INTO access_codes (code, role, description, max_uses, expires_at, created_by, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, 1)";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "sssisi", $code, $role, $description, $maxUses, $expiresAt, $createdBy);
        
        if (mysqli_stmt_execute($stmt)) {
            $result['success'] = true;
            $result['code'] = $code;
            $result['message'] = 'Access code generated successfully.';
        } else {
            $result['message'] = 'Failed to generate access code: ' . mysqli_error($conn);
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return $result;
}

/**
 * Get all access codes (for admin management)
 * @param mysqli $conn Database connection
 * @return array List of access codes
 */
function getAllAccessCodes($conn) {
    $codes = [];
    
    $sql = "SELECT ac.*, u.username as created_by_name,
            (SELECT COUNT(*) FROM access_code_usage WHERE code_id = ac.id) as total_uses
            FROM access_codes ac
            LEFT JOIN users u ON ac.created_by = u.id
            ORDER BY ac.created_at DESC";
    
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $codes[] = $row;
        }
    }
    
    return $codes;
}

/**
 * Toggle access code active status
 * @param int $codeId The access code ID
 * @param mysqli $conn Database connection
 * @return array Result with success status and message
 */
function toggleAccessCode($codeId, $conn) {
    $result = ['success' => false, 'message' => ''];
    
    $sql = "UPDATE access_codes SET is_active = NOT is_active WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $codeId);
        if (mysqli_stmt_execute($stmt)) {
            $result['success'] = true;
            $result['message'] = 'Access code status updated successfully.';
        } else {
            $result['message'] = 'Failed to update access code status.';
        }
        mysqli_stmt_close($stmt);
    } else {
        $result['message'] = 'Database error occurred.';
    }
    
    return $result;
}

/**
 * Delete an access code
 * @param int $codeId The access code ID
 * @param mysqli $conn Database connection
 * @return array Result with success status and message
 */
function deleteAccessCode($codeId, $conn) {
    $result = ['success' => false, 'message' => ''];
    
    $sql = "DELETE FROM access_codes WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $codeId);
        if (mysqli_stmt_execute($stmt)) {
            $result['success'] = true;
            $result['message'] = 'Access code deleted successfully.';
        } else {
            $result['message'] = 'Failed to delete access code.';
        }
        mysqli_stmt_close($stmt);
    } else {
        $result['message'] = 'Database error occurred.';
    }
    
    return $result;
}

/**
 * Submit an anonymous complaint or suggestion
 * @param string $subject The subject/title
 * @param string $description The description
 * @param string $type Either 'complaint' or 'suggestion'
 * @param string $submittedByRole Role of submitter (student/teacher) - only role, no user ID
 * @param mysqli $conn Database connection
 * @return array Result with success status and message
 */
function submitAnonymousFeedback($subject, $description, $type, $submittedByRole, $conn) {
    $result = ['success' => false, 'message' => ''];
    
    // Validate type
    if (!in_array($type, ['complaint', 'suggestion'])) {
        $result['message'] = 'Invalid feedback type.';
        return $result;
    }
    
    // Validate role
    if (!in_array($submittedByRole, ['student', 'teacher'])) {
        $result['message'] = 'Invalid submitter role.';
        return $result;
    }
    
    $sql = "INSERT INTO suggestions_complaints (subject, description, type, submitted_by_role, status) 
            VALUES (?, ?, ?, ?, 'pending')";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssss", $subject, $description, $type, $submittedByRole);
        
        if (mysqli_stmt_execute($stmt)) {
            $feedbackId = mysqli_insert_id($conn);
            $result['success'] = true;
            $result['message'] = 'Your ' . $type . ' has been submitted anonymously. Thank you for your feedback!';
            $result['feedback_id'] = $feedbackId;
            
            // Create notification for admins
            createAdminNotification(
                'New ' . ucfirst($type) . ' Received',
                'A new anonymous ' . $type . ' has been submitted by a ' . $submittedByRole . '.',
                'feedback',
                $feedbackId,
                $conn
            );
        } else {
            $result['message'] = 'Failed to submit feedback: ' . mysqli_error($conn);
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $result['message'] = 'Database error occurred.';
    }
    
    return $result;
}

/**
 * Create a notification for all admins
 * @param string $title Notification title
 * @param string $message Notification message
 * @param string $type Type of notification (feedback, system, etc.)
 * @param int|null $relatedId ID of related record
 * @param mysqli $conn Database connection
 * @return bool Success status
 */
function createAdminNotification($title, $message, $type, $relatedId, $conn) {
    // First ensure notifications table exists
    $createTableSql = "CREATE TABLE IF NOT EXISTS admin_notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        type VARCHAR(50) NOT NULL DEFAULT 'system',
        related_id INT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    mysqli_query($conn, $createTableSql);
    
    $sql = "INSERT INTO admin_notifications (title, message, type, related_id) VALUES (?, ?, ?, ?)";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "sssi", $title, $message, $type, $relatedId);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $success;
    }
    
    return false;
}

/**
 * Get unread notifications count for admin
 * @param mysqli $conn Database connection
 * @return int Count of unread notifications
 */
function getUnreadNotificationCount($conn) {
    $sql = "SELECT COUNT(*) as count FROM admin_notifications WHERE is_read = 0";
    $result = mysqli_query($conn, $sql);
    
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row['count'];
    }
    
    return 0;
}

/**
 * Get all admin notifications
 * @param mysqli $conn Database connection
 * @param int $limit Maximum number of notifications to return
 * @return array List of notifications
 */
function getAdminNotifications($conn, $limit = 50) {
    $notifications = [];
    
    $sql = "SELECT * FROM admin_notifications ORDER BY created_at DESC LIMIT ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $notifications[] = $row;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return $notifications;
}

/**
 * Mark notification as read
 * @param int $notificationId Notification ID
 * @param mysqli $conn Database connection
 * @return bool Success status
 */
function markNotificationRead($notificationId, $conn) {
    $sql = "UPDATE admin_notifications SET is_read = 1 WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $notificationId);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $success;
    }
    
    return false;
}

/**
 * Mark all notifications as read
 * @param mysqli $conn Database connection
 * @return bool Success status
 */
function markAllNotificationsRead($conn) {
    $sql = "UPDATE admin_notifications SET is_read = 1 WHERE is_read = 0";
    return mysqli_query($conn, $sql);
}

/**
 * Get pending feedback count for dashboard stats
 * @param mysqli $conn Database connection
 * @return int Count of pending feedback
 */
function getPendingFeedbackCount($conn) {
    $sql = "SELECT COUNT(*) as count FROM suggestions_complaints WHERE status = 'pending'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row['count'];
    }
    
    return 0;
}

/**
 * Get all feedback for AI analysis
 * @param mysqli $conn Database connection
 * @param string|null $type Filter by type (complaint/suggestion/null for all)
 * @param string|null $status Filter by status (pending/in_progress/resolved/null for all)
 * @return array List of feedback for analysis
 */
function getFeedbackForAnalysis($conn, $type = null, $status = null) {
    $feedback = [];
    
    $sql = "SELECT id, subject, description, type, submitted_by_role, status, created_at, 
            resolution_notes, resolved_at
            FROM suggestions_complaints WHERE 1=1";
    
    $params = [];
    $types = "";
    
    if ($type) {
        $sql .= " AND type = ?";
        $params[] = $type;
        $types .= "s";
    }
    
    if ($status) {
        $sql .= " AND status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $feedback[] = $row;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return $feedback;
}
?>
