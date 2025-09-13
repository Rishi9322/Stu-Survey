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
function isSurveyCompleted($userId, $targetRole, $conn) {
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
function getSurveyQuestions($targetRole, $conn) {
    $questions = [];
    
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
    
    return $questions;
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
            $divisions[] = $row['division'];
        }
    }
    
    return $divisions;
}
?>
