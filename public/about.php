<?php
session_start();
$pageTitle = "About Us";
$basePath = "../";
require_once '../core/includes/header.php';
?>

<style>
/* Modern About Page Styles */
.about-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    min-height: 60vh;
    display: flex;
    align-items: center;
    position: relative;
    overflow: hidden;
    margin-top: -20px;
}

.about-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.08'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    animation: patternMove 20s linear infinite;
}

@keyframes patternMove {
    0% { transform: translate(0, 0); }
    100% { transform: translate(60px, 60px); }
}

.hero-content {
    position: relative;
    z-index: 2;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 800;
    color: white;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    animation: fadeInUp 1s ease-out;
}

.hero-subtitle {
    font-size: 1.4rem;
    color: rgba(255,255,255,0.9);
    animation: fadeInUp 1s ease-out 0.2s both;
}

.floating-shapes {
    position: absolute;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: 1;
}

.shape {
    position: absolute;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    animation: float 15s ease-in-out infinite;
}

.shape-1 { width: 80px; height: 80px; top: 10%; left: 10%; animation-delay: 0s; }
.shape-2 { width: 120px; height: 120px; top: 60%; left: 80%; animation-delay: -3s; }
.shape-3 { width: 60px; height: 60px; top: 80%; left: 20%; animation-delay: -5s; }
.shape-4 { width: 100px; height: 100px; top: 20%; left: 70%; animation-delay: -7s; }

@keyframes float {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-30px) rotate(180deg); }
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Glass Card Effect */
.glass-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 24px;
    border: 1px solid rgba(255,255,255,0.3);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    overflow: hidden;
}

.glass-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 35px 60px -15px rgba(0, 0, 0, 0.25);
}

.glass-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
}

/* Icon Containers */
.icon-container {
    width: 80px;
    height: 80px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    transition: all 0.3s ease;
}

.icon-container.gradient-1 { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.icon-container.gradient-2 { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
.icon-container.gradient-3 { background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%); }
.icon-container.gradient-4 { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.icon-container.gradient-5 { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.icon-container.gradient-6 { background: linear-gradient(135deg, #5ee7df 0%, #b490ca 100%); }

.glass-card:hover .icon-container {
    transform: scale(1.1) rotate(5deg);
}

/* Section Titles */
.section-badge {
    display: inline-block;
    padding: 0.5rem 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 1rem;
}

.section-title {
    font-size: 2.8rem;
    font-weight: 800;
    background: linear-gradient(135deg, #1a1a2e 0%, #667eea 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 1rem;
}

.section-desc {
    font-size: 1.2rem;
    color: #6b7280;
    max-width: 600px;
    margin: 0 auto;
}

/* Team Cards */
.team-card {
    background: white;
    border-radius: 24px;
    padding: 2rem;
    text-align: center;
    position: relative;
    overflow: hidden;
    transition: all 0.4s ease;
    border: none;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
}

.team-card:hover {
    transform: translateY(-15px);
    box-shadow: 0 25px 50px rgba(102, 126, 234, 0.25);
}

.team-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 100px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 24px 24px 0 0;
}

.team-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 5px solid white;
    position: relative;
    z-index: 2;
    margin-top: 30px;
    object-fit: cover;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
}

.team-card:hover .team-avatar {
    transform: scale(1.1);
    border-color: #667eea;
}

.team-name {
    font-size: 1.4rem;
    font-weight: 700;
    color: #1a1a2e;
    margin-top: 1rem;
}

.team-role {
    color: #667eea;
    font-weight: 600;
    font-size: 0.95rem;
}

.team-bio {
    color: #6b7280;
    font-size: 0.9rem;
    margin-top: 0.75rem;
}

.team-social {
    margin-top: 1.25rem;
}

.team-social a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f3f4f6;
    color: #667eea;
    margin: 0 0.3rem;
    transition: all 0.3s ease;
}

.team-social a:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transform: translateY(-3px);
}

/* Stats Section */
.stats-section {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    border-radius: 32px;
    padding: 4rem 2rem;
    position: relative;
    overflow: hidden;
}

.stats-section::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(102,126,234,0.1) 0%, transparent 50%);
    animation: pulse-bg 10s ease-in-out infinite;
}

@keyframes pulse-bg {
    0%, 100% { transform: scale(1); opacity: 0.5; }
    50% { transform: scale(1.2); opacity: 0.8; }
}

.stat-item {
    text-align: center;
    position: relative;
    z-index: 2;
}

.stat-number {
    font-size: 3.5rem;
    font-weight: 800;
    background: linear-gradient(135deg, #667eea 0%, #f093fb 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    line-height: 1;
}

.stat-label {
    color: rgba(255,255,255,0.8);
    font-size: 1rem;
    font-weight: 500;
    margin-top: 0.5rem;
}

.stat-icon {
    font-size: 1.5rem;
    color: rgba(102,126,234,0.5);
    margin-bottom: 0.75rem;
}

/* Values Grid */
.value-card {
    background: linear-gradient(145deg, #ffffff 0%, #f8f9ff 100%);
    border-radius: 20px;
    padding: 2rem 1.5rem;
    text-align: center;
    border: 1px solid rgba(102,126,234,0.1);
    transition: all 0.3s ease;
    height: 100%;
}

.value-card:hover {
    border-color: #667eea;
    box-shadow: 0 15px 40px rgba(102,126,234,0.15);
}

.value-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.5rem;
    color: white;
}

.value-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 0.5rem;
}

.value-desc {
    color: #6b7280;
    font-size: 0.9rem;
}

/* CTA Section */
.cta-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 32px;
    padding: 4rem;
    position: relative;
    overflow: hidden;
}

.cta-section::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 300px;
    height: 300px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    transform: translate(50%, -50%);
}

.cta-section::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 200px;
    height: 200px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
    transform: translate(-50%, 50%);
}

.cta-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: white;
}

.cta-desc {
    color: rgba(255,255,255,0.9);
    font-size: 1.2rem;
}

.btn-cta {
    padding: 1rem 2.5rem;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 50px;
    transition: all 0.3s ease;
}

.btn-cta-primary {
    background: white;
    color: #667eea;
    border: none;
}

.btn-cta-primary:hover {
    background: #f8f9ff;
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.btn-cta-outline {
    background: transparent;
    color: white;
    border: 2px solid rgba(255,255,255,0.5);
}

.btn-cta-outline:hover {
    background: rgba(255,255,255,0.1);
    border-color: white;
    color: white;
}

/* Timeline for Story */
.story-timeline {
    position: relative;
    padding-left: 30px;
}

.story-timeline::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: linear-gradient(180deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    border-radius: 3px;
}

.timeline-item {
    position: relative;
    padding-bottom: 2rem;
    padding-left: 2rem;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -36px;
    top: 0;
    width: 16px;
    height: 16px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 0 0 4px rgba(102,126,234,0.2);
}

.timeline-year {
    font-size: 0.85rem;
    font-weight: 700;
    color: #667eea;
    text-transform: uppercase;
    letter-spacing: 2px;
}

.timeline-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0.25rem 0;
}

.timeline-desc {
    color: #6b7280;
    font-size: 0.95rem;
}

/* Scroll animations */
.fade-in-up {
    opacity: 0;
    transform: translateY(40px);
    transition: all 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.fade-in-up.visible {
    opacity: 1;
    transform: translateY(0);
}

/* Responsive */
@media (max-width: 768px) {
    .hero-title { font-size: 2.2rem; }
    .hero-subtitle { font-size: 1.1rem; }
    .section-title { font-size: 2rem; }
    .stat-number { font-size: 2.5rem; }
    .cta-section { padding: 2rem; }
    .cta-title { font-size: 1.8rem; }
}
</style>

<!-- Hero Section -->
<section class="about-hero">
    <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
    </div>
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center text-white">
                <span class="badge bg-white text-dark px-4 py-2 rounded-pill mb-4" style="font-size: 0.9rem;">
                    <i class="fas fa-sparkles me-2"></i>Welcome to EduSurvey Pro
                </span>
                <h1 class="hero-title mb-4">Transforming Education<br>Through Innovation</h1>
                <p class="hero-subtitle mb-5">
                    We're on a mission to revolutionize educational feedback with AI-powered insights, 
                    comprehensive analytics, and seamless communication tools.
                </p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="#our-story" class="btn btn-light btn-lg rounded-pill px-5 shadow-sm">
                        <i class="fas fa-play-circle me-2"></i>Our Story
                    </a>
                    <a href="contact.php" class="btn btn-outline-light btn-lg rounded-pill px-5">
                        <i class="fas fa-paper-plane me-2"></i>Get in Touch
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Breadcrumb -->
<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><i class="fas fa-home me-1"></i>Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">About Us</li>
        </ol>
    </nav>
</div>

<!-- Mission & Vision Section -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-6 fade-in-up">
                <div class="glass-card h-100 p-4 p-lg-5 position-relative">
                    <div class="icon-container gradient-1 mb-4">
                        <i class="fas fa-bullseye fa-2x text-white"></i>
                    </div>
                    <h3 class="fw-bold mb-3" style="font-size: 1.8rem;">Our Mission</h3>
                    <p class="text-muted mb-3" style="font-size: 1.05rem; line-height: 1.8;">
                        At EduSurvey Pro, we believe that quality education thrives on continuous feedback and improvement. 
                        Our mission is to empower educational institutions with cutting-edge tools that facilitate meaningful 
                        communication between students, teachers, and administrators.
                    </p>
                    <p class="text-muted mb-0" style="font-size: 1.05rem; line-height: 1.8;">
                        We strive to create a platform where every voice is heard, every concern is addressed, and every 
                        suggestion contributes to building better learning environments.
                    </p>
                    <div class="mt-4">
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 me-2">
                            <i class="fas fa-check me-1"></i>Empowerment
                        </span>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                            <i class="fas fa-check me-1"></i>Communication
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 fade-in-up" style="transition-delay: 0.2s;">
                <div class="glass-card h-100 p-4 p-lg-5 position-relative">
                    <div class="icon-container gradient-2 mb-4">
                        <i class="fas fa-eye fa-2x text-white"></i>
                    </div>
                    <h3 class="fw-bold mb-3" style="font-size: 1.8rem;">Our Vision</h3>
                    <p class="text-muted mb-3" style="font-size: 1.05rem; line-height: 1.8;">
                        To become the leading platform for educational feedback and analytics, fostering transparent 
                        communication and data-driven decision making in educational institutions worldwide.
                    </p>
                    <p class="text-muted mb-0" style="font-size: 1.05rem; line-height: 1.8;">
                        We envision a future where technology seamlessly bridges the gap between educational stakeholders, 
                        creating environments that promote continuous learning, improvement, and excellence.
                    </p>
                    <div class="mt-4">
                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 me-2">
                            <i class="fas fa-check me-1"></i>Leadership
                        </span>
                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">
                            <i class="fas fa-check me-1"></i>Excellence
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-light" style="background: linear-gradient(180deg, #f8f9ff 0%, #ffffff 100%);">
    <div class="container">
        <div class="text-center mb-5 fade-in-up">
            <span class="section-badge">Our Features</span>
            <h2 class="section-title">What Makes Us Different</h2>
            <p class="section-desc">Discover the powerful features that set EduSurvey Pro apart from the rest</p>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-4 fade-in-up">
                <div class="glass-card h-100 p-4 text-center position-relative">
                    <div class="icon-container gradient-1">
                        <i class="fas fa-robot fa-2x text-white"></i>
                    </div>
                    <h4 class="fw-bold mb-3">AI-Powered Analytics</h4>
                    <p class="text-muted">
                        Advanced artificial intelligence analyzes feedback patterns and provides actionable insights for continuous improvement.
                    </p>
                    <ul class="list-unstyled text-start mt-4">
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Smart Pattern Recognition</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Predictive Analytics</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Automated Reports</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-4 fade-in-up" style="transition-delay: 0.15s;">
                <div class="glass-card h-100 p-4 text-center position-relative">
                    <div class="icon-container gradient-2">
                        <i class="fas fa-shield-alt fa-2x text-white"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Privacy & Security</h4>
                    <p class="text-muted">
                        Your data is protected with enterprise-grade security measures, ensuring confidentiality and compliance.
                    </p>
                    <ul class="list-unstyled text-start mt-4">
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>End-to-End Encryption</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>GDPR Compliant</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Regular Security Audits</li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-4 fade-in-up" style="transition-delay: 0.3s;">
                <div class="glass-card h-100 p-4 text-center position-relative">
                    <div class="icon-container gradient-3">
                        <i class="fas fa-mobile-alt fa-2x text-white"></i>
                    </div>
                    <h4 class="fw-bold mb-3">User-Friendly Design</h4>
                    <p class="text-muted">
                        Intuitive interface designed for all skill levels, ensuring easy adoption across your educational community.
                    </p>
                    <ul class="list-unstyled text-start mt-4">
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Responsive Design</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Accessibility First</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Multi-Language Support</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Our Story Section -->
<section id="our-story" class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-5 mb-4 mb-lg-0 fade-in-up">
                <span class="section-badge">Our Journey</span>
                <h2 class="section-title">The EduSurvey Story</h2>
                <p class="text-muted" style="font-size: 1.1rem; line-height: 1.8;">
                    What started as a simple idea to improve student feedback has grown into a comprehensive platform 
                    trusted by institutions worldwide.
                </p>
            </div>
            <div class="col-lg-7 fade-in-up" style="transition-delay: 0.2s;">
                <div class="story-timeline">
                    <div class="timeline-item">
                        <span class="timeline-year">2020</span>
                        <h5 class="timeline-title">The Beginning</h5>
                        <p class="timeline-desc">Founded with a vision to transform educational feedback systems using modern technology.</p>
                    </div>
                    <div class="timeline-item">
                        <span class="timeline-year">2022</span>
                        <h5 class="timeline-title">AI Integration</h5>
                        <p class="timeline-desc">Introduced AI-powered analytics to provide deeper insights from survey responses.</p>
                    </div>
                    <div class="timeline-item">
                        <span class="timeline-year">2024</span>
                        <h5 class="timeline-title">Global Expansion</h5>
                        <p class="timeline-desc">Expanded to serve educational institutions across 50+ countries worldwide.</p>
                    </div>
                    <div class="timeline-item">
                        <span class="timeline-year">2026</span>
                        <h5 class="timeline-title">The Future</h5>
                        <p class="timeline-desc">Continuing to innovate with predictive analytics and personalized learning insights.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-5">
    <div class="container">
        <div class="stats-section fade-in-up">
            <div class="row g-4">
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <i class="fas fa-user-graduate stat-icon"></i>
                        <div class="stat-number" data-count="50000">50K+</div>
                        <div class="stat-label">Students Served</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <i class="fas fa-university stat-icon"></i>
                        <div class="stat-number" data-count="200">200+</div>
                        <div class="stat-label">Institutions</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <i class="fas fa-clipboard-list stat-icon"></i>
                        <div class="stat-number" data-count="1000000">1M+</div>
                        <div class="stat-label">Survey Responses</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <i class="fas fa-smile stat-icon"></i>
                        <div class="stat-number" data-count="95">95%</div>
                        <div class="stat-label">Satisfaction Rate</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5 fade-in-up">
            <span class="section-badge">Our Team</span>
            <h2 class="section-title">Meet the Experts</h2>
            <p class="section-desc">The dedicated professionals driving innovation at EduSurvey Pro</p>
        </div>
        
        <div class="row g-4 justify-content-center">
            <div class="col-lg-4 col-md-6 fade-in-up">
                <div class="team-card">
                    <img src="../assets/images/team/OIP.jpg" class="team-avatar" alt="Dr. Sarah Johnson">
                    <h5 class="team-name">Mr.Rishi Poddar</h5>
                    <p class="team-role">Chief Executive Officer</p>
                    <p class="team-bio">Former education researcher with 15+ years in educational technology and institutional improvement.</p>
                    <div class="team-social">
                        <a href="https://www.linkedin.com/in/rishi-poddar/"><i class="fab fa-linkedin-in"></i></a>
                        <a href="https://github.com/Rishi9322"><i class="fab fa-github"></i></a>
                        <a href="#"><i class="fas fa-envelope"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 fade-in-up" style="transition-delay: 0.15s;">
                <div class="team-card">
                    <img src="../assets/images/team/S.K%20singh.jpg" class="team-avatar" alt="Dr. Santosh Kumar Singh">
                    <h5 class="team-name">Dr. Santosh Kumar Singh</h5>
                    <p class="team-role">Chief Project Guide</p>
                    <p class="team-bio">IQAC Coordinator/ NIRF Nodal officer/ Program Director-International Affiliation/ Head of Department Information Technology at Thakur College of Science & Commerce</p>
                    <div class="team-social">
                        <a href="https://www.linkedin.com/in/dr-santosh-kumar-singh-1401a71ab/"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-github"></i></a>
                        <a href="mailto:sksingh@tcsc.edu.in"><i class="fas fa-envelope"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 fade-in-up" style="transition-delay: 0.3s;">
                <div class="team-card">
                    <img src="../assets/images/team/RAMM.jpg" class="team-avatar" alt="Lisa Rodriguez">
                    <h5 class="team-name">Mr.Sriram Sunkari</h5>
                    <p class="team-role">Chief Product Officer</p>
                    <p class="team-bio">UX/UI expert with deep understanding of educational workflows and user experience design.</p>
                    <div class="team-social">
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-dribbble"></i></a>
                        <a href="#"><i class="fas fa-envelope"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5 fade-in-up">
            <span class="section-badge">Our Values</span>
            <h2 class="section-title">What We Stand For</h2>
            <p class="section-desc">The core principles that guide everything we do</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-3 fade-in-up">
                <div class="value-card">
                    <div class="value-icon" style="background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h5 class="value-title">Student-Centric</h5>
                    <p class="value-desc">Every feature is designed with student success and well-being at its core.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 fade-in-up" style="transition-delay: 0.1s;">
                <div class="value-card">
                    <div class="value-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h5 class="value-title">Transparency</h5>
                    <p class="value-desc">Open communication and honest feedback drive positive institutional change.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 fade-in-up" style="transition-delay: 0.2s;">
                <div class="value-card">
                    <div class="value-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h5 class="value-title">Innovation</h5>
                    <p class="value-desc">Continuously evolving to meet the changing needs of modern education.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 fade-in-up" style="transition-delay: 0.3s;">
                <div class="value-card">
                    <div class="value-icon" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                        <i class="fas fa-users"></i>
                    </div>
                    <h5 class="value-title">Community</h5>
                    <p class="value-desc">Building stronger educational communities through meaningful connections.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5">
    <div class="container">
        <div class="cta-section text-center fade-in-up position-relative">
            <div class="position-relative" style="z-index: 2;">
                <h2 class="cta-title mb-3">Ready to Transform Your Institution?</h2>
                <p class="cta-desc mb-4 mx-auto" style="max-width: 600px;">
                    Join thousands of educational institutions already using EduSurvey Pro to improve their educational outcomes.
                </p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="contact.php" class="btn btn-cta btn-cta-primary">
                        <i class="fas fa-envelope me-2"></i>Get in Touch
                    </a>
                    <a href="register.php" class="btn btn-cta btn-cta-outline">
                        <i class="fas fa-rocket me-2"></i>Start Free Trial
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Scroll Animation Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Intersection Observer for fade-in animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    document.querySelectorAll('.fade-in-up').forEach(el => {
        observer.observe(el);
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});
</script>

<?php require_once '../core/includes/footer.php'; ?>