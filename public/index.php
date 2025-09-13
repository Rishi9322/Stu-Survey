<?php
// Initialize session
session_start();

// If already logged in, redirect to respective dashboard
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    switch ($_SESSION["role"]) {
        case "student":
            header("location: ../app/student/dashboard.php");
            break;
        case "teacher":
            header("location: ../app/teacher/dashboard.php");
            break;
        case "admin":
            header("location: ../app/admin/dashboard.php");
            break;
    }
    exit;
}

// Set page variables
$pageTitle = "Student Satisfaction Survey System - Home";
$basePath = "../";
?>


<?php include '../core/includes/header.php'; ?>

<!-- Hero Section with Modern Design -->
<section class="hero-modern">
    <div class="hero-bg-overlay"></div>
    <div class="hero-particles"></div>
    <div class="container">
        <div class="row align-items-center min-vh-100">
            <div class="col-lg-6">
                <div class="hero-content">
                    <span class="hero-badge">🎓 Educational Excellence</span>
                    <h1 class="hero-title">
                        Transform Education with 
                        <span class="text-gradient">Smart Feedback</span>
                    </h1>
                    <p class="hero-description">
                        Join thousands of students and educators in creating a better learning environment. 
                        Your voice drives innovation and excellence in education.
                    </p>
                    <div class="hero-stats">
                        <div class="stat-item">
                            <div class="stat-number">2.5K+</div>
                            <div class="stat-label">Active Students</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">150+</div>
                            <div class="stat-label">Faculty Members</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">98%</div>
                            <div class="stat-label">Satisfaction Rate</div>
                        </div>
                    </div>
                    <div class="hero-buttons">
                        <a href="login.php" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Get Started
                        </a>
                        <a href="register.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-user-plus me-2"></i>
                            Join Now
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-visual">
                    <div class="dashboard-preview">
                        <div class="dashboard-header">
                            <div class="dashboard-dots">
                                <span class="dot red"></span>
                                <span class="dot yellow"></span>
                                <span class="dot green"></span>
                            </div>
                        </div>
                        <div class="dashboard-content">
                            <div class="chart-container">
                                <div class="chart-bar" style="height: 60%"></div>
                                <div class="chart-bar" style="height: 80%"></div>
                                <div class="chart-bar" style="height: 45%"></div>
                                <div class="chart-bar" style="height: 90%"></div>
                                <div class="chart-bar" style="height: 70%"></div>
                            </div>
                            <div class="stats-grid">
                                <div class="stat-card">
                                    <div class="stat-icon blue">📊</div>
                                    <div class="stat-text">Analytics</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-icon green">✅</div>
                                    <div class="stat-text">Surveys</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section with Enhanced Design -->
<section class="features-modern">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-badge">Why Choose Us</span>
            <h2 class="section-title">Powerful Features for Modern Education</h2>
            <p class="section-description">
                Discover how our comprehensive survey system transforms the educational experience
            </p>
        </div>
        
        <div class="features-grid-modern">
            <div class="feature-card">
                <div class="feature-icon-wrapper">
                    <div class="feature-icon blue">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <h3>Real-time Analytics</h3>
                <p>Get instant insights with interactive dashboards, advanced filtering, and automated reporting tools.</p>
                <div class="feature-highlight">
                    <span class="highlight-text">Advanced AI insights</span>
                </div>
            </div>
            
            <div class="feature-card featured">
                <div class="feature-badge">Most Popular</div>
                <div class="feature-icon-wrapper">
                    <div class="feature-icon purple">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                </div>
                <h3>Anonymous & Secure</h3>
                <p>Complete privacy protection with end-to-end encryption and anonymous feedback options.</p>
                <div class="feature-highlight">
                    <span class="highlight-text">Bank-level security</span>
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon-wrapper">
                    <div class="feature-icon green">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <h3>Multi-role Access</h3>
                <p>Seamless experience for students, teachers, and administrators with role-based permissions.</p>
                <div class="feature-highlight">
                    <span class="highlight-text">Smart permissions</span>
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon-wrapper">
                    <div class="feature-icon orange">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                </div>
                <h3>Mobile Optimized</h3>
                <p>Perfect experience across all devices with progressive web app capabilities.</p>
                <div class="feature-highlight">
                    <span class="highlight-text">Offline support</span>
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon-wrapper">
                    <div class="feature-icon red">
                        <i class="fas fa-bolt"></i>
                    </div>
                </div>
                <h3>Lightning Fast</h3>
                <p>Optimized performance with caching, CDN integration, and minimal loading times.</p>
                <div class="feature-highlight">
                    <span class="highlight-text">99.9% uptime</span>
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon-wrapper">
                    <div class="feature-icon teal">
                        <i class="fas fa-cog"></i>
                    </div>
                </div>
                <h3>Smart Automation</h3>
                <p>Automated reminders, smart scheduling, and AI-powered insights for better engagement.</p>
                <div class="feature-highlight">
                    <span class="highlight-text">ML powered</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials-section">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-badge">Testimonials</span>
            <h2 class="section-title">What Our Community Says</h2>
        </div>
        
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"This system has revolutionized how we collect and analyze student feedback. The insights help us improve our teaching methods continuously."</p>
                </div>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="author-info">
                        <h4>Dr. Sarah Johnson</h4>
                        <span>Professor, Computer Science</span>
                    </div>
                </div>
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"Finally, a platform where student voices are heard! The anonymous feedback feature makes it easy to share honest opinions."</p>
                </div>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="author-info">
                        <h4>Alex Chen</h4>
                        <span>Computer Science Student</span>
                    </div>
                </div>
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"The analytics dashboard provides incredible insights that help us make data-driven decisions for institutional improvement."</p>
                </div>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="author-info">
                        <h4>Michael Davis</h4>
                        <span>Academic Administrator</span>
                    </div>
                </div>
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section with Modern Design -->
<section class="cta-modern">
    <div class="container">
        <div class="cta-content">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h2 class="cta-title">Ready to Transform Your Educational Experience?</h2>
                    <p class="cta-description">
                        Join thousands of educators and students who are already using our platform to create positive change in education.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="cta-buttons">
                        <a href="login.php" class="btn btn-light btn-lg">
                            <i class="fas fa-rocket me-2"></i>
                            Start Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="cta-bg-pattern"></div>
    </div>
</section>

<?php include '../core/includes/footer.php'; ?>
