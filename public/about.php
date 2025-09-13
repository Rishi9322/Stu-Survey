<?php
session_start();
$pageTitle = "About Us";
$basePath = "../";
require_once '../core/includes/header.php';
?>

<div class="container my-5">
    <!-- Hero Section -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="display-4 mb-3">
                <i class="fas fa-graduation-cap me-3 text-primary"></i>About EduSurvey Pro
            </h1>
            <p class="lead text-muted">Transforming education through intelligent feedback systems and comprehensive analytics</p>
        </div>
    </div>

    <!-- Breadcrumb Navigation -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">About Us</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Mission Section -->
    <div class="row mb-5">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="card-title text-primary">
                        <i class="fas fa-bullseye me-2"></i>Our Mission
                    </h3>
                    <p class="card-text">
                        At EduSurvey Pro, we believe that quality education thrives on continuous feedback and improvement. 
                        Our mission is to empower educational institutions with cutting-edge tools that facilitate meaningful 
                        communication between students, teachers, and administrators.
                    </p>
                    <p class="card-text">
                        We strive to create a platform where every voice is heard, every concern is addressed, and every 
                        suggestion contributes to building better learning environments.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="card-title text-success">
                        <i class="fas fa-eye me-2"></i>Our Vision
                    </h3>
                    <p class="card-text">
                        To become the leading platform for educational feedback and analytics, fostering transparent 
                        communication and data-driven decision making in educational institutions worldwide.
                    </p>
                    <p class="card-text">
                        We envision a future where technology seamlessly bridges the gap between educational stakeholders, 
                        creating environments that promote continuous learning, improvement, and excellence.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-4">What Makes Us Different</h2>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-robot fa-3x text-primary mb-3"></i>
                    <h4>AI-Powered Analytics</h4>
                    <p>Advanced artificial intelligence analyzes feedback patterns and provides actionable insights for continuous improvement.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                    <h4>Privacy & Security</h4>
                    <p>Your data is protected with enterprise-grade security measures, ensuring confidentiality and compliance with privacy regulations.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-mobile-alt fa-3x text-warning mb-3"></i>
                    <h4>User-Friendly Design</h4>
                    <p>Intuitive interface designed for all skill levels, ensuring easy adoption across your entire educational community.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Section -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-4">Our Team</h2>
            <p class="text-center text-muted mb-5">Meet the dedicated professionals behind EduSurvey Pro</p>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card text-center">
                <div class="card-body">
                    <img src="https://via.placeholder.com/150x150/007bff/ffffff?text=CEO" class="rounded-circle mb-3" alt="CEO">
                    <h5>Dr. Sarah Johnson</h5>
                    <p class="text-muted">Chief Executive Officer</p>
                    <p class="small">Former education researcher with 15+ years in educational technology and institutional improvement.</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card text-center">
                <div class="card-body">
                    <img src="https://via.placeholder.com/150x150/28a745/ffffff?text=CTO" class="rounded-circle mb-3" alt="CTO">
                    <h5>Michael Chen</h5>
                    <p class="text-muted">Chief Technology Officer</p>
                    <p class="small">Software architect specializing in AI/ML applications and scalable educational platforms.</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card text-center">
                <div class="card-body">
                    <img src="https://via.placeholder.com/150x150/dc3545/ffffff?text=CPO" class="rounded-circle mb-3" alt="CPO">
                    <h5>Lisa Rodriguez</h5>
                    <p class="text-muted">Chief Product Officer</p>
                    <p class="small">UX/UI expert with deep understanding of educational workflows and user experience design.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Values Section -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-4">Our Core Values</h2>
        </div>
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card border-0 text-center">
                <div class="card-body">
                    <i class="fas fa-heart fa-2x text-danger mb-3"></i>
                    <h5>Student-Centric</h5>
                    <p class="small">Every feature is designed with student success and well-being at its core.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card border-0 text-center">
                <div class="card-body">
                    <i class="fas fa-handshake fa-2x text-primary mb-3"></i>
                    <h5>Transparency</h5>
                    <p class="small">Open communication and honest feedback drive positive institutional change.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card border-0 text-center">
                <div class="card-body">
                    <i class="fas fa-lightbulb fa-2x text-warning mb-3"></i>
                    <h5>Innovation</h5>
                    <p class="small">Continuously evolving to meet the changing needs of modern education.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card border-0 text-center">
                <div class="card-body">
                    <i class="fas fa-users fa-2x text-success mb-3"></i>
                    <h5>Community</h5>
                    <p class="small">Building stronger educational communities through meaningful connections.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="card bg-primary text-white mb-5">
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3 mb-3">
                    <h2 class="display-4">50K+</h2>
                    <p>Students Served</p>
                </div>
                <div class="col-md-3 mb-3">
                    <h2 class="display-4">200+</h2>
                    <p>Institutions</p>
                </div>
                <div class="col-md-3 mb-3">
                    <h2 class="display-4">1M+</h2>
                    <p>Survey Responses</p>
                </div>
                <div class="col-md-3 mb-3">
                    <h2 class="display-4">95%</h2>
                    <p>Satisfaction Rate</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Section -->
    <div class="row">
        <div class="col-lg-8 mx-auto text-center">
            <h3 class="mb-4">Ready to Transform Your Institution?</h3>
            <p class="lead mb-4">
                Join thousands of educational institutions already using EduSurvey Pro to improve their educational outcomes.
            </p>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <a href="contact.php" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-envelope me-2"></i>Get in Touch
                    </a>
                </div>
                <div class="col-md-6 mb-3">
                    <a href="register.php" class="btn btn-outline-primary btn-lg w-100">
                        <i class="fas fa-user-plus me-2"></i>Start Free Trial
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../core/includes/footer.php'; ?>