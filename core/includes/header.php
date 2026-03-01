<?php
// Ensure $basePath is defined and normalized. Pages may set $basePath (e.g. "../").
// If not set or is a relative path, try to compute a sensible default when the
// project is served under a subfolder like /stu/public/.
if (!isset($basePath) || empty($basePath)) {
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    // If the app appears under a folder named 'stu', default to '/stu/public/'
    if (strpos($script, '/stu/') !== false) {
        $basePath = '/stu/public/';
    } else {
        // fallback to site root
        $basePath = '/';
    }
}

// Normalize the basePath to always end with a single slash and avoid duplicated 'public'
$basePath = rtrim($basePath, '/') . '/';
$basePath = str_replace('/public/public/', '/public/', $basePath);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Student Satisfaction Survey System - Transform Education with Smart Feedback'; ?></title>
    
    <!-- Preconnect to external domains for better performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    <!-- Modern Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS (Latest) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome (Latest) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/style.css">
    
    <!-- DataTables CSS (Bootstrap 5 compatible) -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <!-- Anime.js for Advanced Animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    
    <!-- Enhanced Custom CSS with Animation Support -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/animations.css">
    
    <!-- Additional Meta Tags -->
    <meta name="description" content="Transform education with our comprehensive student satisfaction survey system. Gather insights, improve teaching quality, and enhance the learning experience.">
    <meta name="keywords" content="student satisfaction, survey system, education, feedback, analytics, teaching quality">
    <meta name="author" content="Student Satisfaction Survey System">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Student Satisfaction Survey System">
    <meta property="og:description" content="Transform education with smart feedback and comprehensive analytics">
    <meta property="og:type" content="website">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo $basePath; ?>assets/images/favicon.ico">
    
    <!-- Theme Color -->
    <meta name="theme-color" content="#6366f1">
    
    <!-- Notification Badge Style -->
    <style>
        .notification-badge {
            font-size: 0.65rem;
            padding: 0.25rem 0.4rem;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: translate(-50%, -50%) scale(1); }
            50% { transform: translate(-50%, -50%) scale(1.1); }
        }
        
        /* GLOBAL: Prevent modal backdrop from blocking clicks when no modal is open */
        body:not(.modal-open) .modal-backdrop,
        .modal-backdrop.orphan {
            display: none !important;
            opacity: 0 !important;
            pointer-events: none !important;
            visibility: hidden !important;
            z-index: -1 !important;
        }
        
        /* Ensure modals appear above backdrops */
        .modal {
            z-index: 1055 !important;
        }
        
        .modal-backdrop.show {
            z-index: 1050 !important;
        }
    </style>
</head>
<body class="has-fixed-header">
    <header class="header" id="header">
        <div class="container">
            <nav class="navbar navbar-expand-lg">
                <a href="/stu/public/index.php" class="navbar-brand">
                    <i class="fas fa-graduation-cap me-2"></i>
                    EduSurvey Pro
                </a>
                
                <!-- Mobile Menu Toggle -->
                <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon">
                        <i class="fas fa-bars"></i>
                    </span>
                </button>
                
                <!-- Navigation Menu -->
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                            <?php if ($_SESSION["role"] === "student"): ?>
                                <li class="nav-item">
                                    <a href="/stu/app/student/dashboard.php" class="nav-link">
                                        <i class="fas fa-tachometer-alt me-1"></i>
                                        Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/stu/app/student/profile.php" class="nav-link">
                                        <i class="fas fa-user me-1"></i>
                                        Profile
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/stu/app/student/survey.php" class="nav-link">
                                        <i class="fas fa-poll me-1"></i>
                                        Survey
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/stu/app/student/analytics.php" class="nav-link">
                                        <i class="fas fa-chart-bar me-1"></i>
                                        Analytics
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/stu/core/includes/logout.php" class="nav-link">
                                        <i class="fas fa-sign-out-alt me-1"></i>
                                        Logout
                                    </a>
                                </li>
                            <?php elseif ($_SESSION["role"] === "teacher"): ?>
                                <li class="nav-item">
                                    <a href="/stu/app/teacher/dashboard.php" class="nav-link">
                                        <i class="fas fa-tachometer-alt me-1"></i>
                                        Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/stu/app/teacher/profile.php" class="nav-link">
                                        <i class="fas fa-user me-1"></i>
                                        Profile
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/stu/app/teacher/survey.php" class="nav-link">
                                        <i class="fas fa-poll me-1"></i>
                                        Survey
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/stu/app/teacher/analytics.php" class="nav-link">
                                        <i class="fas fa-chart-line me-1"></i>
                                        Analytics
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/stu/core/includes/logout.php" class="nav-link">
                                        <i class="fas fa-sign-out-alt me-1"></i>
                                        Logout
                                    </a>
                                </li>
                            <?php elseif ($_SESSION["role"] === "admin"): ?>
                                <?php 
                                // Get unread notification count for admin (only if function and connection exist)
                                $unreadCount = 0;
                                if (function_exists('getUnreadNotificationCount') && isset($conn)) {
                                    $unreadCount = getUnreadNotificationCount($conn);
                                }
                                ?>
                                <li class="nav-item">
                                    <a href="/stu/app/admin/dashboard.php" class="nav-link">
                                        <i class="fas fa-tachometer-alt me-1"></i>
                                        Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/stu/app/admin/profile.php" class="nav-link">
                                        <i class="fas fa-user me-1"></i>
                                        Profile
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/stu/app/admin/survey_management.php" class="nav-link">
                                        <i class="fas fa-tasks me-1"></i>
                                        Surveys
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/stu/app/admin/user_management.php" class="nav-link">
                                        <i class="fas fa-users me-1"></i>
                                        Users
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/stu/app/admin/access_codes.php" class="nav-link">
                                        <i class="fas fa-key me-1"></i>
                                        Access Codes
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/stu/app/admin/complaints.php" class="nav-link position-relative">
                                        <i class="fas fa-comment-alt me-1"></i>
                                        Feedback
                                        <?php if ($unreadCount > 0): ?>
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge">
                                            <?php echo $unreadCount > 99 ? '99+' : $unreadCount; ?>
                                        </span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/stu/app/api/ai_insights.php" class="nav-link">
                                        <i class="fas fa-brain me-1"></i>
                                        AI Insights
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/stu/core/includes/logout.php" class="nav-link">
                                        <i class="fas fa-sign-out-alt me-1"></i>
                                        Logout
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php else: ?>
                            <li class="nav-item">
                                <a href="/stu/public/index.php" class="nav-link">
                                    <i class="fas fa-home me-1"></i>
                                    Home
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/stu/public/login.php" class="nav-link">
                                    <i class="fas fa-sign-in-alt me-1"></i>
                                    Login
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/stu/public/register.php" class="btn btn-primary btn-sm ms-2">
                                    <i class="fas fa-user-plus me-1"></i>
                                    Get Started
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <script>
        // Enhanced header with Anime.js animations
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.getElementById('header');
            if (header && document.body.classList.contains('has-fixed-header')) {
                const headerHeight = header.offsetHeight;
                document.body.style.paddingTop = (headerHeight + 10) + 'px';
            }

            // Initialize header animations
            initHeaderAnimations();
            initPageTransitions();
            initInteractiveElements();
        });

        // Header slide-in animation
        function initHeaderAnimations() {
            anime({
                targets: '.header',
                translateY: [-100, 0],
                opacity: [0, 1],
                duration: 800,
                easing: 'easeOutExpo',
                delay: 100
            });

            // Navbar items stagger animation
            anime({
                targets: '.nav-item',
                opacity: [0, 1],
                translateY: [-20, 0],
                duration: 600,
                delay: anime.stagger(100, {start: 300}),
                easing: 'easeOutQuart'
            });

            // Brand bounce effect
            anime({
                targets: '.navbar-brand',
                scale: [0.8, 1],
                opacity: [0, 1],
                duration: 1000,
                easing: 'easeOutElastic(1, .8)',
                delay: 200
            });
        }

        // Page transition effects
        function initPageTransitions() {
            // Fade in main content
            anime({
                targets: 'main',
                opacity: [0, 1],
                translateY: [30, 0],
                duration: 1000,
                easing: 'easeOutQuart',
                delay: 600
            });

            // Cards entrance animation
            anime({
                targets: '.card, .alert',
                opacity: [0, 1],
                translateY: [20, 0],
                scale: [0.95, 1],
                duration: 800,
                delay: anime.stagger(100, {start: 800}),
                easing: 'easeOutQuart'
            });
        }

        // Interactive element animations
        function initInteractiveElements() {
            // Button hover animations
            document.querySelectorAll('.btn').forEach(btn => {
                btn.addEventListener('mouseenter', function() {
                    anime({
                        targets: this,
                        scale: 1.05,
                        duration: 200,
                        easing: 'easeOutQuart'
                    });
                });

                btn.addEventListener('mouseleave', function() {
                    anime({
                        targets: this,
                        scale: 1,
                        duration: 200,
                        easing: 'easeOutQuart'
                    });
                });
            });

            // Icon pulse animation
            anime({
                targets: '.fas, .fab',
                scale: [1, 1.1, 1],
                duration: 2000,
                loop: true,
                direction: 'alternate',
                easing: 'easeInOutSine',
                delay: anime.stagger(200, {from: 'center'})
            });
        }
    </script>
    
    <main>
        <?php
        // Local debug bar (enabled only when DEV_DEBUG environment variable is set)
        $devDebug = getenv('DEV_DEBUG');
        if ($devDebug === '1') {
            // Attempt to read a mysqli connection error if available
            $dbError = '';
            if (isset($conn) && function_exists('mysqli_error')) {
                $dbError = mysqli_error($conn);
            }
            echo '<div style="position:fixed;right:10px;bottom:10px;z-index:9999;background:#111;color:#fff;padding:10px;border-radius:6px;max-width:480px;font-size:12px;opacity:0.95;">';
            echo '<strong>DEV DEBUG</strong><br/>';
            echo '<div style="max-height:120px;overflow:auto;margin-top:6px;">' . htmlspecialchars($dbError) . '</div>';
            echo '</div>';
        }
        ?>
        <?php if (!empty($alertMessage) && isset($alertType)): ?>
            <?php
                $toastIcon = $alertType === 'success' ? 'check-circle' : ($alertType === 'danger' ? 'exclamation-circle' : 'info-circle');
                $toastColor = $alertType === 'success' ? '#10b981' : ($alertType === 'danger' ? '#ef4444' : '#3b82f6');
            ?>
            <div id="globalToast" style="
                position:fixed; top:20px; right:20px; z-index:9999;
                min-width:320px; max-width:440px;
                background:white; border-radius:12px;
                box-shadow:0 20px 60px rgba(0,0,0,0.15), 0 0 0 1px rgba(0,0,0,0.05);
                overflow:hidden; transform:translateX(120%); opacity:0;
                transition:transform 0.4s cubic-bezier(0.22,1,0.36,1), opacity 0.4s ease;
            ">
                <div style="display:flex; align-items:center; gap:12px; padding:16px 18px;">
                    <div style="width:38px;height:38px;border-radius:10px;background:<?php echo $toastColor; ?>15;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-<?php echo $toastIcon; ?>" style="color:<?php echo $toastColor; ?>;font-size:1.1rem;"></i>
                    </div>
                    <div style="flex-grow:1;font-size:0.9rem;font-weight:500;color:#1f2937;line-height:1.4;">
                        <?php echo $alertMessage; ?>
                    </div>
                    <button onclick="dismissToast()" style="background:none;border:none;color:#9ca3af;cursor:pointer;padding:4px;font-size:1.1rem;line-height:1;" title="Dismiss">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div style="height:3px;background:#e5e7eb;">
                    <div id="toastProgress" style="height:100%;background:<?php echo $toastColor; ?>;width:100%;transition:width 4s linear;"></div>
                </div>
            </div>
            <script>
                (function(){
                    var t = document.getElementById('globalToast');
                    if (!t) return;
                    setTimeout(function(){ t.style.transform='translateX(0)'; t.style.opacity='1'; }, 100);
                    setTimeout(function(){ var p=document.getElementById('toastProgress'); if(p) p.style.width='0%'; }, 200);
                    setTimeout(function(){ dismissToast(); }, 4200);
                })();
                function dismissToast(){
                    var t=document.getElementById('globalToast');
                    if(!t) return;
                    t.style.transform='translateX(120%)'; t.style.opacity='0';
                    setTimeout(function(){ t.remove(); }, 400);
                }
            </script>
        <?php endif; ?>
