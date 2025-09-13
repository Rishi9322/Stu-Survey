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
</head>
<body class="has-fixed-header">
    <header class="header" id="header">
        <div class="container">
            <nav class="navbar navbar-expand-lg">
                <a href="<?php echo $basePath; ?>public/index.php" class="navbar-brand">
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
                                    <a href="<?php echo $basePath; ?>app/student/dashboard.php" class="nav-link">
                                        <i class="fas fa-tachometer-alt me-1"></i>
                                        Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo $basePath; ?>app/student/profile.php" class="nav-link">
                                        <i class="fas fa-user me-1"></i>
                                        Profile
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo $basePath; ?>app/student/survey.php" class="nav-link">
                                        <i class="fas fa-poll me-1"></i>
                                        Survey
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo $basePath; ?>app/student/analytics.php" class="nav-link">
                                        <i class="fas fa-chart-bar me-1"></i>
                                        Analytics
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo $basePath; ?>core/includes/logout.php" class="nav-link">
                                        <i class="fas fa-sign-out-alt me-1"></i>
                                        Logout
                                    </a>
                                </li>
                            <?php elseif ($_SESSION["role"] === "teacher"): ?>
                                <li class="nav-item">
                                    <a href="<?php echo $basePath; ?>app/teacher/dashboard.php" class="nav-link">
                                        <i class="fas fa-tachometer-alt me-1"></i>
                                        Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo $basePath; ?>app/teacher/profile.php" class="nav-link">
                                        <i class="fas fa-user me-1"></i>
                                        Profile
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo $basePath; ?>app/teacher/survey.php" class="nav-link">
                                        <i class="fas fa-poll me-1"></i>
                                        Survey
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo $basePath; ?>app/teacher/analytics.php" class="nav-link">
                                        <i class="fas fa-chart-line me-1"></i>
                                        Analytics
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo $basePath; ?>core/includes/logout.php" class="nav-link">
                                        <i class="fas fa-sign-out-alt me-1"></i>
                                        Logout
                                    </a>
                                </li>
                            <?php elseif ($_SESSION["role"] === "admin"): ?>
                                <li class="nav-item">
                                    <a href="<?php echo $basePath; ?>app/admin/dashboard.php" class="nav-link">
                                        <i class="fas fa-tachometer-alt me-1"></i>
                                        Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo $basePath; ?>app/admin/profile.php" class="nav-link">
                                        <i class="fas fa-user me-1"></i>
                                        Profile
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo $basePath; ?>app/admin/survey_management.php" class="nav-link">
                                        <i class="fas fa-tasks me-1"></i>
                                        Surveys
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo $basePath; ?>app/admin/user_management.php" class="nav-link">
                                        <i class="fas fa-users me-1"></i>
                                        Users
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo $basePath; ?>app/admin/complaints.php" class="nav-link">
                                        <i class="fas fa-comment-alt me-1"></i>
                                        Feedback
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo $basePath; ?>app/admin/ai_insights.php" class="nav-link">
                                        <i class="fas fa-brain me-1"></i>
                                        AI Insights
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo $basePath; ?>core/includes/logout.php" class="nav-link">
                                        <i class="fas fa-sign-out-alt me-1"></i>
                                        Logout
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php else: ?>
                            <li class="nav-item">
                                <a href="<?php echo $basePath; ?>public/index.php" class="nav-link">
                                    <i class="fas fa-home me-1"></i>
                                    Home
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo $basePath; ?>public/login.php" class="nav-link">
                                    <i class="fas fa-sign-in-alt me-1"></i>
                                    Login
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo $basePath; ?>public/register.php" class="btn btn-primary btn-sm ms-2">
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
        <?php if (isset($alertMessage) && isset($alertType)): ?>
            <div class="container mt-4">
                <div class="alert alert-<?php echo $alertType; ?> alert-dismissible fade show" role="alert">
                    <?php echo $alertMessage; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        <?php endif; ?>
