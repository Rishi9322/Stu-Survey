<?php
// Start output buffering at the very beginning
ob_start();

// Include config file
require_once "../core/includes/config.php";
require_once "../core/includes/functions.php";

// Initialize variables
$name = $email = $password = $confirm_password = $dob = $role = "";
$division = $roll_no = $course = $department = $subjects = $experience = "";
$access_code = "";
$name_err = $email_err = $password_err = $confirm_password_err = $dob_err = $role_err = "";
$division_err = $roll_no_err = $course_err = $department_err = $subjects_err = $experience_err = "";
$access_code_err = "";
$validated_code_id = null;

// Get course, department and division lists for dropdowns
$courseList = getCourseList($conn);
$departmentList = getDepartmentList($conn);
$divisionList = getDivisionList($conn);

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter your name.";
    } else {
        $name = trim($_POST["name"]);
    }
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE email = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            
            // Set parameters
            $param_email = trim($_POST["email"]);
            
            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Store result
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $email_err = "This email is already taken.";
                } else {
                    $email = trim($_POST["email"]);
                }
            } else {
                // More explicit error for debugging during local testing
                error_log("Error checking email existence: " . mysqli_error($conn));
                $email_err = "Server error while validating email. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        } else {
            error_log("Error preparing email check statement: " . mysqli_error($conn));
            $email_err = "Server error. Please try again later.";
        }
    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";     
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";     
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }
    
    // Validate DOB
    if (empty(trim($_POST["dob"]))) {
        $dob_err = "Please enter your date of birth.";
    } else {
        $dob = trim($_POST["dob"]);
    }
    
    // Validate role
    if (empty(trim($_POST["role"]))) {
        $role_err = "Please select a role.";
    } else {
        $role = trim($_POST["role"]);
        
        // Validate access code for teacher and admin roles
        if ($role === "teacher" || $role === "admin") {
            $access_code = trim($_POST["access_code"] ?? "");
            $codeValidation = validateAccessCode($access_code, $role, $conn);
            
            if (!$codeValidation['valid']) {
                $access_code_err = $codeValidation['message'];
            } else {
                $validated_code_id = $codeValidation['code_id'];
            }
        }
        
        // Additional validations based on role
        if ($role === "student") {
            // Validate division
            if (empty(trim($_POST["division"]))) {
                $division_err = "Please enter your division.";
            } else {
                $division = trim($_POST["division"]);
            }
            
            // Validate roll no
            if (empty(trim($_POST["roll_no"]))) {
                $roll_no_err = "Please enter your roll number.";
            } else {
                $roll_no = trim($_POST["roll_no"]);
            }
            
            // Validate course
            if (empty(trim($_POST["course"]))) {
                $course_err = "Please select your course.";
            } else {
                $course = trim($_POST["course"]);
            }
        } elseif ($role === "teacher") {
            // Validate department
            if (empty(trim($_POST["department"]))) {
                $department_err = "Please enter your department.";
            } else {
                $department = trim($_POST["department"]);
            }
            
            // Validate subjects
            if (empty(trim($_POST["subjects"]))) {
                $subjects_err = "Please enter subjects taught.";
            } else {
                $subjects = trim($_POST["subjects"]);
            }
            
            // Experience is optional
            $experience = !empty($_POST["experience"]) ? trim($_POST["experience"]) : "0";
        }
    }
    
    // Check input errors before inserting in database
    $student_profile_valid = ($role !== "student") || (empty($division_err) && empty($roll_no_err) && empty($course_err));
    $teacher_profile_valid = ($role !== "teacher") || (empty($department_err) && empty($subjects_err));
    $access_code_valid = ($role === "student") || empty($access_code_err);
    
    if (empty($name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err) && empty($dob_err) && empty($role_err) && $student_profile_valid && $teacher_profile_valid && $access_code_valid) {
        
        // Begin transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Register the user
            $user_id = registerUser($name, $email, $password, $dob, $role, $conn);
            
            if ($user_id) {
                // Create profile based on role
                if ($role === "student") {
                    // Create student profile
                    $sql = "INSERT INTO student_profiles (user_id, division, roll_no, course) VALUES (?, ?, ?, ?)";
                    
                    if ($stmt = mysqli_prepare($conn, $sql)) {
                        mysqli_stmt_bind_param($stmt, "isss", $user_id, $division, $roll_no, $course);
                        
                        if (!mysqli_stmt_execute($stmt)) {
                            throw new Exception(mysqli_error($conn));
                        }
                        
                        mysqli_stmt_close($stmt);
                    } else {
                        throw new Exception(mysqli_error($conn));
                    }
                } elseif ($role === "teacher") {
                    // Create teacher profile
                    $sql = "INSERT INTO teacher_profiles (user_id, department, subjects, experience) VALUES (?, ?, ?, ?)";
                    
                    if ($stmt = mysqli_prepare($conn, $sql)) {
                        mysqli_stmt_bind_param($stmt, "issi", $user_id, $department, $subjects, $experience);
                        
                        if (!mysqli_stmt_execute($stmt)) {
                            throw new Exception(mysqli_error($conn));
                        }
                        
                        mysqli_stmt_close($stmt);
                    } else {
                        throw new Exception(mysqli_error($conn));
                    }
                }
                
                // Log access code usage for teacher/admin
                if (($role === "teacher" || $role === "admin") && $validated_code_id) {
                    useAccessCode($validated_code_id, $user_id, $conn);
                }
                
                // Commit transaction
                mysqli_commit($conn);
                
                // Login the user
                if (loginUser($email, $password, $role, $conn)) {
                    // Determine the redirect location based on role
                    $targetLocation = "";
                    
                    switch ($role) {
                        case "student":
                            $targetLocation = "/stu/app/student/dashboard.php";
                            break;
                        case "teacher":
                            $targetLocation = "/stu/app/teacher/dashboard.php";
                            break;
                        case "admin":
                            $targetLocation = "/stu/app/admin/dashboard.php";
                            break;
                        default:
                            $targetLocation = "/stu/public/index.php";
                    }
                    
                    // Clean output buffer and redirect
                    if (ob_get_level()) {
                        ob_end_clean();
                    }
                    
                    // Redirect directly
                    header("Location: " . $targetLocation);
                    exit;
                }
            } else {
                throw new Exception("Error registering user.");
            }
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            $alertType = "danger";
            $alertMessage = "Error: " . $e->getMessage();
        }
    }
}

// Set page variables
$pageTitle = "Register - Student Satisfaction Survey";
$basePath = "../";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --accent: #06b6d4;
            --success: #10b981;
            --error: #ef4444;
            --warning: #f59e0b;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            min-height: 100dvh; /* Dynamic viewport height for mobile */
            padding: 20px;
            position: relative;
            overflow-x: hidden;
            margin: 0;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
            animation: backgroundMove 20s ease-in-out infinite;
            pointer-events: none;
            z-index: 0;
        }
        
        @keyframes backgroundMove {
            0%, 100% { opacity: 0.5; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.1); }
        }
        
        /* Navbar */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            animation: slideDown 0.5s ease-out;
        }
        
        @keyframes slideDown {
            from { transform: translateY(-100%); }
            to { transform: translateY(0); }
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 24px;
            font-weight: 800;
            color: var(--primary);
            text-decoration: none;
        }
        
        .navbar-brand svg {
            width: 32px;
            height: 32px;
        }
        
        .navbar-cta {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 10px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
        }
        
        .navbar-cta:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }
        
        /* Main Container */
        .register-container {
            max-width: 900px;
            margin: 100px auto 40px;
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.6s ease-out;
            padding: 0 20px;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Register Card */
        .register-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .register-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            padding: 48px 40px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .register-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: rotate 30s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .register-header > * {
            position: relative;
            z-index: 1;
        }
        
        .register-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 32px;
        }
        
        .register-header h1 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 8px;
        }
        
        .register-header p {
            font-size: 16px;
            opacity: 0.95;
        }
        
        /* Alert Messages */
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin: 24px 40px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            font-weight: 500;
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid var(--error);
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid var(--success);
        }
        
        .alert svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
        
        /* Form Body */
        .register-body {
            padding: 40px;
        }
        
        /* Form sections container - controls all spacing between sections */
        .register-body form {
            display: flex;
            flex-direction: column;
            gap: 0;
        }
        
        /* Form Sections */
        .form-section {
            padding: 32px 0;
            border-bottom: 2px solid var(--gray-100);
        }
        
        .form-section:first-child {
            padding-top: 0;
        }
        
        .form-section:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        
        .section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .step-badge {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--gray-900);
            margin: 0;
        }
        
        .section-subtitle {
            color: var(--gray-500);
            font-size: 14px;
            margin: -12px 0 16px 44px;
        }
        
        /* Role Cards */
        .role-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }
        
        .role-card {
            background: var(--gray-50);
            border: 2px solid var(--gray-200);
            border-radius: 16px;
            padding: 24px 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .role-card:hover {
            background: var(--gray-100);
            border-color: var(--gray-300);
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }
        
        .role-card.selected {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
            border-color: var(--primary);
            box-shadow: 0 4px 16px rgba(99, 102, 241, 0.2);
        }
        
        .role-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            font-size: 24px;
            color: white;
            transition: transform 0.3s ease;
        }
        
        .role-card:hover .role-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .role-card.selected .role-icon {
            transform: scale(1.15);
        }
        
        .role-icon.student {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
        }
        
        .role-icon.teacher {
            background: linear-gradient(135deg, #06b6d4, #3b82f6);
        }
        
        .role-icon.admin {
            background: linear-gradient(135deg, #f59e0b, #ef4444);
        }
        
        .role-name {
            font-size: 16px;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 4px;
        }
        
        .role-desc {
            font-size: 13px;
            color: var(--gray-500);
        }
        
        .role-checkmark {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 28px;
            height: 28px;
            background: var(--primary);
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
        }
        
        .role-card.selected .role-checkmark {
            display: flex;
            animation: checkPop 0.3s ease-out;
        }
        
        @keyframes checkPop {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        
        /* Access Code Section */
        .access-code-box {
            display: none;
            margin-top: 20px;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid var(--warning);
            border-radius: 16px;
            padding: 20px;
            animation: slideDown 0.3s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                max-height: 0;
                padding-top: 0;
                padding-bottom: 0;
            }
            to {
                opacity: 1;
                max-height: 300px;
                padding-top: 20px;
                padding-bottom: 20px;
            }
        }
        
        .access-code-box.active {
            display: block;
        }
        
        .access-code-header {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 16px;
        }
        
        .access-code-header svg {
            width: 32px;
            height: 32px;
            color: #d97706;
            flex-shrink: 0;
        }
        
        .access-code-info strong {
            color: #92400e;
            display: block;
            font-size: 15px;
            margin-bottom: 4px;
        }
        
        .access-code-info p {
            color: #78350f;
            font-size: 13px;
            margin: 0;
            line-height: 1.5;
        }
        
        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
        }
        
        .form-grid.full {
            grid-template-columns: 1fr;
        }
        
        /* Form Group */
        .form-group {
            position: relative;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 8px;
        }
        
        .form-group label svg {
            width: 16px;
            height: 16px;
            margin-right: 6px;
            vertical-align: -3px;
        }
        
        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.3s ease;
            background: var(--gray-50);
        }
        
        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }
        
        .form-input.is-invalid,
        .form-select.is-invalid,
        .form-textarea.is-invalid {
            border-color: var(--error);
            background: #fef2f2;
        }
        
        .form-input.is-invalid:focus,
        .form-select.is-invalid:focus,
        .form-textarea.is-invalid:focus {
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        }
        
        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-hint {
            display: block;
            font-size: 13px;
            color: var(--gray-500);
            margin-top: 6px;
        }
        
        .invalid-feedback {
            display: block;
            font-size: 13px;
            color: var(--error);
            margin-top: 6px;
            font-weight: 500;
        }
        
        /* Password Field */
        .password-wrapper {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--gray-400);
            transition: color 0.3s ease;
            padding: 4px;
        }
        
        .password-toggle:hover {
            color: var(--primary);
        }
        
        .password-toggle svg {
            width: 20px;
            height: 20px;
        }
        
        /* Password Strength */
        .password-strength {
            display: none;
            margin-top: 12px;
            padding: 12px;
            background: var(--gray-50);
            border-radius: 8px;
        }
        
        .password-strength.active {
            display: block;
        }
        
        .strength-bar {
            height: 6px;
            background: var(--gray-200);
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 8px;
        }
        
        .strength-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 3px;
        }
        
        .strength-fill.weak { width: 25%; background: var(--error); }
        .strength-fill.fair { width: 50%; background: var(--warning); }
        .strength-fill.good { width: 75%; background: var(--success); }
        .strength-fill.strong { width: 100%; background: #059669; }
        
        .strength-text {
            font-size: 13px;
            color: var(--gray-600);
            font-weight: 500;
        }
        
        /* Role-specific fields - hidden by default, no space taken */
        .role-fields {
            display: none !important;
            padding: 0 !important;
            border: none !important;
        }
        
        .role-fields.active {
            display: block !important;
            padding: 32px 0 !important;
            border-bottom: 2px solid var(--gray-100) !important;
            animation: fadeIn 0.3s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        /* Checkbox */
        .form-check {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 24px;
        }
        
        .form-check input[type="checkbox"] {
            width: 20px;
            height: 20px;
            border: 2px solid var(--gray-300);
            border-radius: 6px;
            cursor: pointer;
            flex-shrink: 0;
            margin-top: 2px;
        }
        
        .form-check input[type="checkbox"]:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .form-check label {
            font-size: 14px;
            color: var(--gray-700);
            cursor: pointer;
        }
        
        .form-check label a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        
        .form-check label a:hover {
            text-decoration: underline;
        }
        
        /* Submit Button */
        .btn-submit {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }
        
        .btn-submit:active {
            transform: translateY(0);
        }
        
        .btn-submit::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn-submit:active::before {
            width: 300px;
            height: 300px;
        }
        
        .btn-submit svg {
            width: 20px;
            height: 20px;
            margin-right: 8px;
            vertical-align: -4px;
        }
        
        /* Footer */
        .register-footer {
            background: var(--gray-50);
            padding: 24px 40px;
            text-align: center;
            border-top: 2px solid var(--gray-100);
        }
        
        .register-footer p {
            margin: 0;
            color: var(--gray-600);
            font-size: 15px;
        }
        
        .register-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        
        .register-footer a:hover {
            text-decoration: underline;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .navbar {
                padding: 12px 20px;
            }
            
            .register-container {
                margin-top: 80px;
                padding: 0 10px;
            }
            
            .register-card {
                margin: 0;
            }
            
            .register-header {
                padding: 32px 24px;
            }
            
            .register-icon {
                width: 64px;
                height: 64px;
                font-size: 28px;
            }
            
            .register-header h1 {
                font-size: 24px;
            }
            
            .register-body {
                padding: 24px;
            }
            
            .role-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            
            .role-card {
                padding: 20px 16px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .register-footer {
                padding: 20px 24px;
            }
        }
        
        @media (max-width: 480px) {
            .register-container {
                padding: 0 5px;
                margin-top: 70px;
            }
            
            .register-body {
                padding: 16px;
            }
            
            .register-header {
                padding: 24px 16px;
            }
            
            .form-section {
                padding: 20px 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                <path d="M2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
            <span>EduSurvey Pro</span>
        </a>
        <a href="login.php" class="navbar-cta">Sign In</a>
    </nav>

    <!-- Main Container -->
    <div class="register-container">
        <div class="register-card">
            <!-- Header -->
            <div class="register-header">
                <div class="register-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <h1>Create Your Account</h1>
                <p>Join EduSurvey Pro and start sharing your feedback</p>
            </div>
            
            <!-- Error/Success Messages -->
            <?php if (isset($alertType) && isset($alertMessage)): ?>
                <div class="alert alert-<?php echo $alertType; ?>">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <?php if ($alertType === 'success'): ?>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        <?php else: ?>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        <?php endif; ?>
                    </svg>
                    <span><?php echo htmlspecialchars($alertMessage); ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Form Body -->
            <div class="register-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="registerForm">
                    <!-- Step 1: Select Role -->
                    <div class="form-section">
                        <div class="section-header">
                            <span class="step-badge">1</span>
                            <h2 class="section-title">Select Your Role</h2>
                        </div>
                        <p class="section-subtitle">Choose how you'll use EduSurvey Pro</p>
                        
                        <div class="role-grid">
                            <div class="role-card <?php echo ($role === 'student') ? 'selected' : ''; ?>" data-role="student">
                                <div class="role-icon student">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 14l9-5-9-5-9 5 9 5z"/>
                                        <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                                    </svg>
                                </div>
                                <div class="role-name">Student</div>
                                <div class="role-desc">Take surveys and view analytics</div>
                                <div class="role-checkmark">
                                    <svg fill="currentColor" viewBox="0 0 20 20" style="width: 16px; height: 16px;">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            
                            <div class="role-card <?php echo ($role === 'teacher') ? 'selected' : ''; ?>" data-role="teacher">
                                <div class="role-icon teacher">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                                <div class="role-name">Faculty</div>
                                <div class="role-desc">View feedback and ratings</div>
                                <div class="role-checkmark">
                                    <svg fill="currentColor" viewBox="0 0 20 20" style="width: 16px; height: 16px;">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                            
                            <div class="role-card <?php echo ($role === 'admin') ? 'selected' : ''; ?>" data-role="admin">
                                <div class="role-icon admin">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </div>
                                <div class="role-name">Admin/HOD</div>
                                <div class="role-desc">Manage surveys and users</div>
                                <div class="role-checkmark">
                                    <svg fill="currentColor" viewBox="0 0 20 20" style="width: 16px; height: 16px;">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <input type="hidden" name="role" id="role" value="<?php echo htmlspecialchars($role); ?>">
                        <?php if (!empty($role_err)): ?>
                            <div class="invalid-feedback" style="display: block; margin-top: 12px;"><?php echo htmlspecialchars($role_err); ?></div>
                        <?php endif; ?>
                        
                        <!-- Access Code Section -->
                        <div class="access-code-box <?php echo ($role === 'teacher' || $role === 'admin') ? 'active' : ''; ?>" id="accessCodeBox">
                            <div class="access-code-header">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                                <div class="access-code-info">
                                    <strong>Access Code Required</strong>
                                    <p>This role requires a valid access code. Please enter the code provided by your institution.</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <input 
                                    type="text" 
                                    name="access_code" 
                                    id="access_code" 
                                    class="form-input <?php echo (!empty($access_code_err)) ? 'is-invalid' : ''; ?>" 
                                    value="<?php echo htmlspecialchars($access_code); ?>"
                                    placeholder="Enter access code"
                                >
                                <?php if (!empty($access_code_err)): ?>
                                    <span class="invalid-feedback"><?php echo htmlspecialchars($access_code_err); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Step 2: Personal Information -->
                    <div class="form-section">
                        <div class="section-header">
                            <span class="step-badge">2</span>
                            <h2 class="section-title">Personal Information</h2>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="name">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Full Name
                                </label>
                                <input 
                                    type="text" 
                                    name="name" 
                                    id="name" 
                                    autocomplete="name"
                                    class="form-input <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" 
                                    value="<?php echo htmlspecialchars($name); ?>"
                                    placeholder="Enter your full name" 
                                    required
                                >
                                <?php if (!empty($name_err)): ?>
                                    <span class="invalid-feedback"><?php echo htmlspecialchars($name_err); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    Email Address
                                </label>
                                <input 
                                    type="email" 
                                    name="email" 
                                    id="email" 
                                    autocomplete="email"
                                    class="form-input <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" 
                                    value="<?php echo htmlspecialchars($email); ?>"
                                    placeholder="you@university.edu" 
                                    required
                                >
                                <?php if (!empty($email_err)): ?>
                                    <span class="invalid-feedback"><?php echo htmlspecialchars($email_err); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="dob">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Date of Birth
                                </label>
                                <input 
                                    type="date" 
                                    name="dob" 
                                    id="dob" 
                                    autocomplete="bday"
                                    class="form-input <?php echo (!empty($dob_err)) ? 'is-invalid' : ''; ?>" 
                                    value="<?php echo htmlspecialchars($dob); ?>"
                                    required
                                >
                                <?php if (!empty($dob_err)): ?>
                                    <span class="invalid-feedback"><?php echo htmlspecialchars($dob_err); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                    <!-- Step 3: Role-Specific Information -->
                    <!-- Student Fields -->
                    <div id="studentFields" class="form-section role-fields <?php echo ($role === 'student') ? 'active' : ''; ?>">
                        <div class="section-header">
                            <span class="step-badge" id="studentStepBadge">3</span>
                            <h2 class="section-title">Academic Information</h2>
                        </div>
                            
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="roll_no">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                    </svg>
                                    Roll Number
                                </label>
                                <input 
                                    type="text" 
                                    name="roll_no" 
                                    id="roll_no" 
                                    class="form-input <?php echo (!empty($roll_no_err)) ? 'is-invalid' : ''; ?>" 
                                    value="<?php echo htmlspecialchars($roll_no); ?>"
                                    placeholder="Enter your roll number"
                                >
                                <?php if (!empty($roll_no_err)): ?>
                                    <span class="invalid-feedback"><?php echo htmlspecialchars($roll_no_err); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="course">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                    Course
                                </label>
                                <select 
                                    name="course" 
                                    id="course" 
                                    class="form-select <?php echo (!empty($course_err)) ? 'is-invalid' : ''; ?>"
                                >
                                    <option value="">Select Course</option>
                                    <?php if (empty($courseList)): ?>
                                        <option value="B.Tech Computer Science" <?php echo ($course === 'B.Tech Computer Science') ? 'selected' : ''; ?>>B.Tech Computer Science</option>
                                        <option value="B.Tech Information Technology" <?php echo ($course === 'B.Tech Information Technology') ? 'selected' : ''; ?>>B.Tech Information Technology</option>
                                        <option value="B.Tech Electronics" <?php echo ($course === 'B.Tech Electronics') ? 'selected' : ''; ?>>B.Tech Electronics</option>
                                        <option value="B.Tech Civil Engineering" <?php echo ($course === 'B.Tech Civil Engineering') ? 'selected' : ''; ?>>B.Tech Civil Engineering</option>
                                        <option value="B.Tech Mechanical Engineering" <?php echo ($course === 'B.Tech Mechanical Engineering') ? 'selected' : ''; ?>>B.Tech Mechanical Engineering</option>
                                        <option value="BBA" <?php echo ($course === 'BBA') ? 'selected' : ''; ?>>BBA</option>
                                        <option value="BCA" <?php echo ($course === 'BCA') ? 'selected' : ''; ?>>BCA</option>
                                        <option value="B.Com" <?php echo ($course === 'B.Com') ? 'selected' : ''; ?>>B.Com</option>
                                    <?php else: ?>
                                        <?php foreach ($courseList as $c): ?>
                                            <option value="<?php echo htmlspecialchars($c); ?>" <?php echo ($course === $c) ? 'selected' : ''; ?>><?php echo htmlspecialchars($c); ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <?php if (!empty($course_err)): ?>
                                    <span class="invalid-feedback"><?php echo htmlspecialchars($course_err); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="division">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    Division
                                </label>
                                <select 
                                    name="division" 
                                    id="division" 
                                    class="form-select <?php echo (!empty($division_err)) ? 'is-invalid' : ''; ?>"
                                >
                                    <option value="">Select Division</option>
                                    <?php if (empty($divisionList)): ?>
                                        <option value="Computer Science A" <?php echo ($division === 'Computer Science A') ? 'selected' : ''; ?>>Computer Science A</option>
                                        <option value="Computer Science B" <?php echo ($division === 'Computer Science B') ? 'selected' : ''; ?>>Computer Science B</option>
                                        <option value="IT A" <?php echo ($division === 'IT A') ? 'selected' : ''; ?>>IT A</option>
                                        <option value="IT B" <?php echo ($division === 'IT B') ? 'selected' : ''; ?>>IT B</option>
                                        <option value="Electronics A" <?php echo ($division === 'Electronics A') ? 'selected' : ''; ?>>Electronics A</option>
                                    <?php else: ?>
                                        <?php foreach ($divisionList as $div): ?>
                                            <option value="<?php echo htmlspecialchars($div); ?>" <?php echo ($division === $div) ? 'selected' : ''; ?>><?php echo htmlspecialchars($div); ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <?php if (!empty($division_err)): ?>
                                    <span class="invalid-feedback"><?php echo htmlspecialchars($division_err); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Teacher Fields -->
                    <div id="teacherFields" class="form-section role-fields <?php echo ($role === 'teacher') ? 'active' : ''; ?>">
                        <div class="section-header">
                            <span class="step-badge" id="teacherStepBadge">3</span>
                            <h2 class="section-title">Professional Information</h2>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="department">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    Department
                                </label>
                                <select 
                                    name="department" 
                                    id="department" 
                                    class="form-select <?php echo (!empty($department_err)) ? 'is-invalid' : ''; ?>"
                                >
                                    <option value="">Select Department</option>
                                    <?php if (empty($departmentList)): ?>
                                        <option value="Computer Science" <?php echo ($department === 'Computer Science') ? 'selected' : ''; ?>>Computer Science</option>
                                        <option value="Information Technology" <?php echo ($department === 'Information Technology') ? 'selected' : ''; ?>>Information Technology</option>
                                        <option value="Electronics" <?php echo ($department === 'Electronics') ? 'selected' : ''; ?>>Electronics</option>
                                        <option value="Civil Engineering" <?php echo ($department === 'Civil Engineering') ? 'selected' : ''; ?>>Civil Engineering</option>
                                        <option value="Mechanical Engineering" <?php echo ($department === 'Mechanical Engineering') ? 'selected' : ''; ?>>Mechanical Engineering</option>
                                        <option value="Mathematics" <?php echo ($department === 'Mathematics') ? 'selected' : ''; ?>>Mathematics</option>
                                        <option value="Physics" <?php echo ($department === 'Physics') ? 'selected' : ''; ?>>Physics</option>
                                    <?php else: ?>
                                        <?php foreach ($departmentList as $dept): ?>
                                            <option value="<?php echo htmlspecialchars($dept); ?>" <?php echo ($department === $dept) ? 'selected' : ''; ?>><?php echo htmlspecialchars($dept); ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <?php if (!empty($department_err)): ?>
                                    <span class="invalid-feedback"><?php echo htmlspecialchars($department_err); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="experience">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    Years of Experience
                                </label>
                                <input 
                                    type="number" 
                                    name="experience" 
                                    id="experience" 
                                    class="form-input <?php echo (!empty($experience_err)) ? 'is-invalid' : ''; ?>" 
                                    value="<?php echo htmlspecialchars($experience); ?>"
                                    min="0" 
                                    max="50"
                                    placeholder="0"
                                >
                                <?php if (!empty($experience_err)): ?>
                                    <span class="invalid-feedback"><?php echo htmlspecialchars($experience_err); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="form-grid full">
                            <div class="form-group">
                                <label for="subjects">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                    Subjects Taught
                                </label>
                                <textarea 
                                    name="subjects" 
                                    id="subjects" 
                                    class="form-textarea <?php echo (!empty($subjects_err)) ? 'is-invalid' : ''; ?>" 
                                    placeholder="Enter subjects separated by commas (e.g., Mathematics, Physics, Chemistry)"
                                ><?php echo htmlspecialchars($subjects); ?></textarea>
                                <?php if (!empty($subjects_err)): ?>
                                    <span class="invalid-feedback"><?php echo htmlspecialchars($subjects_err); ?></span>
                                <?php endif; ?>
                                <span class="form-hint">Separate multiple subjects with commas</span>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Security -->
                    <div class="form-section">
                        <div class="section-header">
                            <span class="step-badge" id="securityStepBadge">4</span>
                            <h2 class="section-title">Security</h2>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="password">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    Password
                                </label>
                                <div class="password-wrapper">
                                    <input 
                                        type="password" 
                                        name="password" 
                                        id="password" 
                                        autocomplete="new-password"
                                        class="form-input <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" 
                                        placeholder="Enter password"
                                        style="padding-right: 48px;"
                                        required
                                    >
                                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                </div>
                                <?php if (!empty($password_err)): ?>
                                    <span class="invalid-feedback"><?php echo htmlspecialchars($password_err); ?></span>
                                <?php endif; ?>
                                <span class="form-hint">Minimum 6 characters</span>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    Confirm Password
                                </label>
                                <div class="password-wrapper">
                                    <input 
                                        type="password" 
                                        name="confirm_password" 
                                        id="confirm_password" 
                                        autocomplete="new-password"
                                        class="form-input <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" 
                                        placeholder="Re-enter password"
                                        style="padding-right: 48px;"
                                        required
                                    >
                                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                </div>
                                <?php if (!empty($confirm_password_err)): ?>
                                    <span class="invalid-feedback"><?php echo htmlspecialchars($confirm_password_err); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Password Strength Indicator -->
                        <div class="password-strength" id="passwordStrength">
                            <div class="strength-bar">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                            <span class="strength-text" id="strengthText">Password Strength: Weak</span>
                        </div>
                    </div>

                    <!-- Terms and Submit -->
                    <div class="form-section">
                        <div class="form-check">
                            <input type="checkbox" id="terms" required>
                            <label for="terms">
                                I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and <a href="privacy.php" target="_blank">Privacy Policy</a>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn-submit">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            Create Account
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Footer -->
            <div class="register-footer">
                <p>Already have an account? <a href="login.php">Sign in here</a></p>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Role selection handler
        document.querySelectorAll('.role-card').forEach(card => {
            card.addEventListener('click', function() {
                // Remove selected from all
                document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
                // Add selected to clicked
                this.classList.add('selected');
                // Update hidden input
                document.getElementById('role').value = this.dataset.role;
                
                // Hide all role fields
                document.querySelectorAll('.role-fields').forEach(f => f.classList.remove('active'));
                
                // Handle access code section
                const accessCodeBox = document.getElementById('accessCodeBox');
                
                if (this.dataset.role === 'teacher' || this.dataset.role === 'admin') {
                    accessCodeBox.classList.add('active');
                } else {
                    accessCodeBox.classList.remove('active');
                }
                
                // Show relevant fields and update step numbers
                const securityBadge = document.getElementById('securityStepBadge');
                if (this.dataset.role === 'student') {
                    document.getElementById('studentFields').classList.add('active');
                    securityBadge.textContent = '4';
                } else if (this.dataset.role === 'teacher') {
                    document.getElementById('teacherFields').classList.add('active');
                    securityBadge.textContent = '4';
                } else {
                    securityBadge.textContent = '4';
                }
            });
        });
        
        // Password visibility toggle
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            if (field.type === 'password') {
                field.type = 'text';
            } else {
                field.type = 'password';
            }
        }
        
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            
            if (password.length > 0) {
                strengthDiv.classList.add('active');
                
                let strength = 0;
                if (password.length >= 6) strength++;
                if (password.length >= 8) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^A-Za-z0-9]/.test(password)) strength++;
                
                strengthFill.className = 'strength-fill';
                if (strength <= 1) {
                    strengthFill.classList.add('weak');
                    strengthText.textContent = 'Password Strength: Weak';
                } else if (strength <= 2) {
                    strengthFill.classList.add('fair');
                    strengthText.textContent = 'Password Strength: Fair';
                } else if (strength <= 3) {
                    strengthFill.classList.add('good');
                    strengthText.textContent = 'Password Strength: Good';
                } else {
                    strengthFill.classList.add('strong');
                    strengthText.textContent = 'Password Strength: Strong';
                }
            } else {
                strengthDiv.classList.remove('active');
            }
        });
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            const selectedRole = document.querySelector('.role-card.selected');
            if (selectedRole) {
                const securityBadge = document.getElementById('securityStepBadge');
                if (selectedRole.dataset.role === 'student' || selectedRole.dataset.role === 'teacher') {
                    securityBadge.textContent = '4';
                }
            }
        });
    </script>
</body>
</html>

<?php
// Close connection
closeConnection($conn);
?>
