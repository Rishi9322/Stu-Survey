<?php
// Start output buffering at the very beginning
ob_start();

// Start session
session_start();

// Unset any existing session variables if arriving from logout
if (isset($_SESSION['logout_success'])) {
    // Keep only the logout success message
    $logout_message = $_SESSION['logout_success'];
    // Start with a clean session
    session_unset();
    // Set back just the logout message
    $_SESSION['logout_success'] = $logout_message;
}

// Include config file
require_once "../core/includes/config.php";
require_once "../core/includes/functions.php";

// Initialize variables
$email = $password = $role = "student"; // Default role to student
$email_err = $password_err = $role_err = $login_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Enable output buffering
    ob_start();
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate role
    if (empty(trim($_POST["role"]))) {
        $role_err = "Please select a role.";
    } else {
        $role = trim($_POST["role"]);
    }
    
    // For debugging - log the submitted values
    error_log("Login attempt - Email: " . $email . ", Role: " . $role);
    
    // Check input errors before attempting to login
    if (empty($email_err) && empty($password_err) && empty($role_err)) {
        if (loginUser($email, $password, $role, $conn)) {
            // Log success
            error_log("Login successful, redirecting to dashboard");
            
            // Make sure session is active and has all required data
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            
            // Double-check session data is set correctly
            if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
                error_log("Session loggedin status not set properly after login");
                $_SESSION["loggedin"] = true;
            }
            
            // Set a success flag in the session
            $_SESSION['login_success'] = true;
            
            // Log session data before redirect
            error_log("Session data before redirect: " . print_r($_SESSION, true));
            
            // Handle redirection directly here instead of using redirectToDashboard
            $targetLocation = "";
            
            switch ($_SESSION["role"]) {
                case "student":
                    $targetLocation = "../app/student/dashboard.php";
                    break;
                case "teacher":
                    $targetLocation = "../app/teacher/dashboard.php";
                    break;
                case "admin":
                    $targetLocation = "../app/admin/dashboard.php";
                    break;
                default:
                    $targetLocation = "index.php";
            }
            
            error_log("Direct redirect to: " . $targetLocation);
            
            // Clean output buffer and redirect
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            // Perform direct redirect
            header("Location: " . $targetLocation);
            exit; // Ensure script stops here
        } else {
            // Login failed - attempt to provide more debug info in local dev
            $login_err = "Invalid email, password, or role combination.";
            // If there was a recent mysqli error, surface it to logs for local debugging
            if (function_exists('mysqli_error')) {
                $dbErr = mysqli_error($conn);
                if (!empty($dbErr)) {
                    error_log("Login DB error: " . $dbErr);
                    // For local development, also append a non-sensitive hint to the user message
                    $login_err .= " (server validation error)";
                }
            }
        }
    }
}

// Set page variables
$pageTitle = "Login - Student Satisfaction Survey";
$basePath = "../";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Animated background pattern */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
            animation: backgroundMove 20s ease-in-out infinite;
            pointer-events: none;
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
        
        /* Login Container */
        .login-wrapper {
            width: 100%;
            max-width: 1200px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            margin-top: 80px;
            animation: fadeInUp 0.6s ease-out;
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
        
        /* Left Panel */
        .login-panel-left {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .login-panel-left::before {
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
        
        .login-panel-left > * {
            position: relative;
            z-index: 1;
        }
        
        .login-panel-left h1 {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 24px;
            line-height: 1.2;
        }
        
        .login-panel-left p {
            font-size: 18px;
            opacity: 0.95;
            line-height: 1.6;
            margin-bottom: 32px;
        }
        
        .feature-list {
            list-style: none;
            margin-top: 40px;
        }
        
        .feature-list li {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            font-size: 16px;
        }
        
        .feature-list li svg {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
        }
        
        /* Right Panel */
        .login-panel-right {
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-header {
            margin-bottom: 40px;
        }
        
        .login-header h2 {
            font-size: 32px;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 8px;
        }
        
        .login-header p {
            font-size: 16px;
            color: var(--gray-500);
        }
        
        /* Alert Messages */
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
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
        
        /* Form Styles */
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        
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
        
        .form-input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            font-size: 16px;
            font-family: inherit;
            transition: all 0.3s ease;
            background: var(--gray-50);
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }
        
        .form-input.is-invalid {
            border-color: var(--error);
            background: #fef2f2;
        }
        
        .form-input.is-invalid:focus {
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        }
        
        .invalid-feedback {
            display: block;
            font-size: 13px;
            color: var(--error);
            margin-top: 6px;
            font-weight: 500;
        }
        
        /* Password Toggle */
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
        
        /* Role Selection */
        .role-selection {
            margin-bottom: 8px;
        }
        
        .role-selector {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }
        
        .role-card {
            background: var(--gray-50);
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            position: relative;
        }
        
        .role-card:hover {
            background: var(--gray-100);
            border-color: var(--gray-300);
            transform: translateY(-2px);
        }
        
        .role-card.active {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-color: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }
        
        .role-card svg {
            width: 32px;
            height: 32px;
            margin-bottom: 8px;
            opacity: 0.7;
        }
        
        .role-card.active svg {
            opacity: 1;
        }
        
        .role-card .role-name {
            font-size: 14px;
            font-weight: 600;
        }
        
        .role-card .checkmark {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
        }
        
        .role-card.active .checkmark {
            display: flex;
        }
        
        /* Button */
        .btn-primary {
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
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .btn-primary::before {
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
        
        .btn-primary:active::before {
            width: 300px;
            height: 300px;
        }
        
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        /* Footer Text */
        .form-footer {
            text-align: center;
            font-size: 14px;
            color: var(--gray-600);
            margin-top: 8px;
        }
        
        .form-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .form-footer a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        /* Responsive */
        @media (max-width: 968px) {
            .login-wrapper {
                grid-template-columns: 1fr;
                max-width: 500px;
            }
            
            .login-panel-left {
                display: none;
            }
            
            .login-panel-right {
                padding: 40px 32px;
            }
            
            .navbar {
                padding: 12px 20px;
            }
        }
        
        @media (max-width: 640px) {
            .role-selector {
                grid-template-columns: 1fr;
            }
            
            .login-panel-right {
                padding: 32px 24px;
            }
            
            .login-header h2 {
                font-size: 28px;
            }
        }
        
        /* Loading State */
        .btn-loading {
            position: relative;
            color: transparent;
        }
        
        .btn-loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
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
        <a href="register.php" class="navbar-cta">Get Started</a>
    </nav>
    
    <!-- Login Container -->
    <div class="login-wrapper">
        <!-- Left Panel -->
        <div class="login-panel-left">
            <h1>Welcome Back</h1>
            <p>Sign in to access your personalized dashboard and contribute to educational excellence.</p>
            
            <ul class="feature-list">
                <li>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Real-time survey insights</span>
                </li>
                <li>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Secure data management</span>
                </li>
                <li>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Advanced analytics tools</span>
                </li>
            </ul>
        </div>
        
        <!-- Right Panel -->
        <div class="login-panel-right">
            <div class="login-header">
                <h2>Login</h2>
                <p>Enter your credentials to access your account</p>
            </div>
            
            <!-- Error Message -->
            <?php if (!empty($login_err)): ?>
                <div class="alert alert-danger">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <?php echo htmlspecialchars($login_err); ?>
                </div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <form action="<?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" class="login-form" id="loginForm">
                <!-- Email Field -->
                <div class="form-group">
                    <label for="email">Email Address</label>
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
                
                <!-- Password Field -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            autocomplete="current-password"
                            class="form-input <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>"
                            placeholder="Enter your password"
                            required
                            style="padding-right: 48px;"
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <svg id="eyeIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    <?php if (!empty($password_err)): ?>
                        <span class="invalid-feedback"><?php echo htmlspecialchars($password_err); ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Role Selection -->
                <div class="form-group">
                    <label for="role" class="role-selection">Select Your Role</label>
                    <div class="role-selector">
                        <div class="role-card <?php echo ($role === 'student') ? 'active' : ''; ?>" data-role="student" onclick="selectRole('student')">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M12 14l9-5-9-5-9 5 9 5z"/>
                                <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                            </svg>
                            <div class="role-name">Student</div>
                            <div class="checkmark">
                                <svg fill="currentColor" viewBox="0 0 20 20" style="width: 12px; height: 12px; color: var(--primary);">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        
                        <div class="role-card <?php echo ($role === 'teacher') ? 'active' : ''; ?>" data-role="teacher" onclick="selectRole('teacher')">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <div class="role-name">Faculty</div>
                            <div class="checkmark">
                                <svg fill="currentColor" viewBox="0 0 20 20" style="width: 12px; height: 12px; color: var(--primary);">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        
                        <div class="role-card <?php echo ($role === 'admin') ? 'active' : ''; ?>" data-role="admin" onclick="selectRole('admin')">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <div class="role-name">Admin/HOD</div>
                            <div class="checkmark">
                                <svg fill="currentColor" viewBox="0 0 20 20" style="width: 12px; height: 12px; color: var(--primary);">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="role" id="role" value="<?php echo htmlspecialchars($role); ?>">
                    <?php if (!empty($role_err)): ?>
                        <span class="invalid-feedback"><?php echo htmlspecialchars($role_err); ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="btn-primary" id="submitBtn">
                    <span id="btnText">Login</span>
                </button>
                
                <div class="form-footer">
                    Don't have an account? <a href="register.php">Register here</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Role selection
        function selectRole(role) {
            document.getElementById('role').value = role;
            
            const roleCards = document.querySelectorAll('.role-card');
            roleCards.forEach(card => {
                if (card.getAttribute('data-role') === role) {
                    card.classList.add('active');
                } else {
                    card.classList.remove('active');
                }
            });
        }
        
        // Password visibility toggle
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                `;
            }
        }
        
        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.classList.add('btn-loading');
            submitBtn.disabled = true;
        });
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            const currentRole = document.getElementById('role').value || 'student';
            selectRole(currentRole);
            
            // Add input focus animations
            const inputs = document.querySelectorAll('.form-input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.01)';
                });
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });
        });
    </script>

<?php
// Close connection
closeConnection($conn);
?>
