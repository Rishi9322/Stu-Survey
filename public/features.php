<?php
session_start();
$pageTitle = "Features";
$basePath = "../";
require_once '../core/includes/header.php';
?>

<style>
/* Modern Features Page Styles */
.features-hero {
    background: linear-gradient(135deg, #00c6fb 0%, #005bea 50%, #6a11cb 100%);
    min-height: 55vh;
    display: flex;
    align-items: center;
    position: relative;
    overflow: hidden;
    margin-top: -20px;
}

.features-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.08'%3E%3Cpath d='M50 50c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10c0 5.523-4.477 10-10 10s-10-4.477-10-10 4.477-10 10-10zM10 10c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10c0 5.523-4.477 10-10 10S0 25.523 0 20s4.477-10 10-10zm10 8c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8zm40 40c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    animation: patternFloat 30s linear infinite;
}

@keyframes patternFloat {
    0% { transform: translate(0, 0); }
    100% { transform: translate(80px, 80px); }
}

.hero-content {
    position: relative;
    z-index: 2;
}

.hero-title {
    font-size: 3.2rem;
    font-weight: 800;
    color: white;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    animation: fadeInUp 1s ease-out;
}

.hero-subtitle {
    font-size: 1.3rem;
    color: rgba(255,255,255,0.9);
    animation: fadeInUp 1s ease-out 0.2s both;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Floating Elements */
.floating-icons {
    position: absolute;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: 1;
}

.float-icon {
    position: absolute;
    font-size: 2rem;
    color: rgba(255,255,255,0.15);
    animation: floatUp 15s ease-in-out infinite;
}

.float-icon:nth-child(1) { left: 10%; top: 20%; animation-delay: 0s; }
.float-icon:nth-child(2) { left: 80%; top: 30%; animation-delay: -3s; }
.float-icon:nth-child(3) { left: 25%; top: 70%; animation-delay: -6s; }
.float-icon:nth-child(4) { left: 70%; top: 60%; animation-delay: -9s; }
.float-icon:nth-child(5) { left: 50%; top: 15%; animation-delay: -12s; }

@keyframes floatUp {
    0%, 100% { transform: translateY(0) rotate(0deg); opacity: 0.15; }
    50% { transform: translateY(-30px) rotate(10deg); opacity: 0.25; }
}

/* Section Styles */
.section-badge {
    display: inline-block;
    padding: 0.5rem 1.5rem;
    background: linear-gradient(135deg, #00c6fb 0%, #005bea 100%);
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
    background: linear-gradient(135deg, #1a1a2e 0%, #005bea 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 1rem;
}

.section-desc {
    font-size: 1.2rem;
    color: #6b7280;
    max-width: 700px;
    margin: 0 auto;
}

/* Feature Cards - Main Grid */
.feature-main-card {
    background: white;
    border-radius: 24px;
    padding: 2.5rem;
    height: 100%;
    position: relative;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 1px solid rgba(0,0,0,0.05);
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
}

.feature-main-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, var(--card-color-1), var(--card-color-2));
    transform: scaleX(0);
    transition: transform 0.4s ease;
    transform-origin: left;
}

.feature-main-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 25px 60px rgba(0,0,0,0.15);
}

.feature-main-card:hover::before {
    transform: scaleX(1);
}

.feature-icon-wrap {
    width: 80px;
    height: 80px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
    position: relative;
    transition: all 0.3s ease;
}

.feature-main-card:hover .feature-icon-wrap {
    transform: scale(1.1) rotate(5deg);
}

.feature-icon-wrap i {
    font-size: 2rem;
    color: white;
}

.feature-main-card h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 1rem;
}

.feature-main-card p {
    color: #6b7280;
    font-size: 1rem;
    line-height: 1.7;
    margin-bottom: 1.5rem;
}

.feature-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.feature-list li {
    padding: 0.5rem 0;
    color: #4a5568;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.95rem;
}

.feature-list li i {
    color: #10b981;
    font-size: 0.85rem;
}

/* Highlight Features */
.highlight-section {
    background: linear-gradient(135deg, #1a1a2e 0%, #2d2d44 100%);
    border-radius: 32px;
    padding: 4rem;
    position: relative;
    overflow: hidden;
}

.highlight-section::before {
    content: '';
    position: absolute;
    top: -100px;
    right: -100px;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(0,198,251,0.15) 0%, transparent 70%);
    border-radius: 50%;
}

.highlight-section::after {
    content: '';
    position: absolute;
    bottom: -100px;
    left: -100px;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(106,17,203,0.15) 0%, transparent 70%);
    border-radius: 50%;
}

.highlight-content {
    position: relative;
    z-index: 2;
}

.highlight-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: white;
    margin-bottom: 1rem;
}

.highlight-text {
    color: rgba(255,255,255,0.8);
    font-size: 1.15rem;
    line-height: 1.8;
}

.highlight-feature-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

@media (max-width: 768px) {
    .highlight-feature-grid {
        grid-template-columns: 1fr;
    }
}

.highlight-feature-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.25rem;
    background: rgba(255,255,255,0.05);
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,0.1);
    transition: all 0.3s ease;
}

.highlight-feature-item:hover {
    background: rgba(255,255,255,0.1);
    transform: translateX(5px);
}

.highlight-feature-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.highlight-feature-icon i {
    font-size: 1.25rem;
    color: white;
}

.highlight-feature-item h5 {
    color: white;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.highlight-feature-item p {
    color: rgba(255,255,255,0.7);
    font-size: 0.9rem;
    margin: 0;
}

/* Role-Based Features */
.role-tab-nav {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 3rem;
    flex-wrap: wrap;
}

.role-tab-btn {
    padding: 1rem 2rem;
    border: 2px solid rgba(0,91,234,0.2);
    background: white;
    border-radius: 50px;
    font-weight: 600;
    color: #4a5568;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 10px;
}

.role-tab-btn:hover {
    border-color: #005bea;
    color: #005bea;
}

.role-tab-btn.active {
    background: linear-gradient(135deg, #00c6fb 0%, #005bea 100%);
    border-color: transparent;
    color: white;
    box-shadow: 0 10px 30px rgba(0,91,234,0.3);
}

.role-content {
    display: none;
    animation: fadeIn 0.5s ease;
}

.role-content.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.role-feature-card {
    background: linear-gradient(145deg, #ffffff 0%, #f8f9ff 100%);
    border-radius: 20px;
    padding: 2rem;
    height: 100%;
    border: 1px solid rgba(0,91,234,0.1);
    transition: all 0.3s ease;
}

.role-feature-card:hover {
    border-color: #005bea;
    box-shadow: 0 15px 40px rgba(0,91,234,0.15);
    transform: translateY(-5px);
}

.role-feature-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.25rem;
    font-size: 1.5rem;
    color: white;
}

.role-feature-card h4 {
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 0.75rem;
}

.role-feature-card p {
    color: #6b7280;
    font-size: 0.95rem;
    margin: 0;
}

/* Comparison Table */
.comparison-table {
    background: white;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 15px 50px rgba(0,0,0,0.08);
}

.comparison-table thead {
    background: linear-gradient(135deg, #00c6fb 0%, #005bea 100%);
}

.comparison-table thead th {
    color: white;
    font-weight: 600;
    padding: 1.25rem 1.5rem;
    text-align: center;
    border: none;
}

.comparison-table thead th:first-child {
    text-align: left;
}

.comparison-table tbody td {
    padding: 1.25rem 1.5rem;
    text-align: center;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    vertical-align: middle;
}

.comparison-table tbody td:first-child {
    text-align: left;
    font-weight: 500;
    color: #1a1a2e;
}

.comparison-table tbody tr:hover {
    background: rgba(0,198,251,0.05);
}

.check-icon {
    color: #10b981;
    font-size: 1.25rem;
}

.cross-icon {
    color: #ef4444;
    font-size: 1.25rem;
}

/* Integration Logos */
.integration-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1.5rem;
}

.integration-card {
    background: white;
    border-radius: 16px;
    padding: 2rem 1.5rem;
    text-align: center;
    border: 1px solid rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.integration-card:hover {
    border-color: #005bea;
    box-shadow: 0 10px 30px rgba(0,91,234,0.1);
    transform: translateY(-5px);
}

.integration-card i {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    background: linear-gradient(135deg, #00c6fb 0%, #005bea 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.integration-card h5 {
    font-weight: 600;
    color: #1a1a2e;
    margin: 0;
}

/* CTA Section */
.cta-section {
    background: linear-gradient(135deg, #00c6fb 0%, #005bea 50%, #6a11cb 100%);
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
    width: 400px;
    height: 400px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
    transform: translate(50%, -50%);
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
    color: #005bea;
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

/* Fade animations */
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
    .highlight-section { padding: 2rem; }
    .highlight-title { font-size: 1.8rem; }
    .cta-section { padding: 2rem; }
    .cta-title { font-size: 1.8rem; }
}
</style>

<!-- Hero Section -->
<section class="features-hero">
    <div class="floating-icons">
        <i class="float-icon fas fa-chart-line"></i>
        <i class="float-icon fas fa-robot"></i>
        <i class="float-icon fas fa-shield-alt"></i>
        <i class="float-icon fas fa-users"></i>
        <i class="float-icon fas fa-graduation-cap"></i>
    </div>
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center text-white">
                <span class="badge bg-white bg-opacity-20 text-white px-4 py-2 rounded-pill mb-4" style="font-size: 0.9rem; backdrop-filter: blur(10px);">
                    <i class="fas fa-star me-2"></i>Powerful Features
                </span>
                <h1 class="hero-title mb-4">Everything You Need to<br>Transform Education</h1>
                <p class="hero-subtitle mb-5">
                    Discover the comprehensive suite of tools designed to revolutionize 
                    student feedback, enhance teaching quality, and drive institutional excellence.
                </p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="register.php" class="btn btn-light btn-lg rounded-pill px-5 shadow-sm">
                        <i class="fas fa-rocket me-2"></i>Get Started Free
                    </a>
                    <a href="#features-overview" class="btn btn-outline-light btn-lg rounded-pill px-5">
                        <i class="fas fa-arrow-down me-2"></i>Explore Features
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
            <li class="breadcrumb-item active" aria-current="page">Features</li>
        </ol>
    </nav>
</div>

<!-- Main Features Section -->
<section id="features-overview" class="py-5">
    <div class="container">
        <div class="text-center mb-5 fade-in-up">
            <span class="section-badge">Core Features</span>
            <h2 class="section-title">Powerful Tools for Education</h2>
            <p class="section-desc">Our comprehensive platform offers everything you need to collect, analyze, and act on student feedback</p>
        </div>

        <div class="row g-4">
            <!-- Survey Management -->
            <div class="col-lg-4 col-md-6 fade-in-up">
                <div class="feature-main-card" style="--card-color-1: #00c6fb; --card-color-2: #005bea;">
                    <div class="feature-icon-wrap" style="background: linear-gradient(135deg, #00c6fb 0%, #005bea 100%);">
                        <i class="fas fa-poll-h"></i>
                    </div>
                    <h3>Smart Survey Builder</h3>
                    <p>Create engaging surveys with our intuitive drag-and-drop builder. Multiple question types and conditional logic included.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check-circle"></i>Drag & drop interface</li>
                        <li><i class="fas fa-check-circle"></i>15+ question types</li>
                        <li><i class="fas fa-check-circle"></i>Conditional branching</li>
                        <li><i class="fas fa-check-circle"></i>Template library</li>
                    </ul>
                </div>
            </div>

            <!-- AI Analytics -->
            <div class="col-lg-4 col-md-6 fade-in-up" style="transition-delay: 0.1s;">
                <div class="feature-main-card" style="--card-color-1: #6a11cb; --card-color-2: #2575fc;">
                    <div class="feature-icon-wrap" style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h3>AI-Powered Analytics</h3>
                    <p>Leverage advanced AI to uncover hidden patterns, predict trends, and get actionable recommendations.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check-circle"></i>Sentiment analysis</li>
                        <li><i class="fas fa-check-circle"></i>Trend prediction</li>
                        <li><i class="fas fa-check-circle"></i>Smart insights</li>
                        <li><i class="fas fa-check-circle"></i>Auto-generated reports</li>
                    </ul>
                </div>
            </div>

            <!-- Real-time Dashboard -->
            <div class="col-lg-4 col-md-6 fade-in-up" style="transition-delay: 0.2s;">
                <div class="feature-main-card" style="--card-color-1: #11998e; --card-color-2: #38ef7d;">
                    <div class="feature-icon-wrap" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h3>Real-time Dashboard</h3>
                    <p>Monitor feedback as it comes in with beautiful, interactive charts and customizable widgets.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check-circle"></i>Live data updates</li>
                        <li><i class="fas fa-check-circle"></i>Custom widgets</li>
                        <li><i class="fas fa-check-circle"></i>Export options</li>
                        <li><i class="fas fa-check-circle"></i>Mobile responsive</li>
                    </ul>
                </div>
            </div>

            <!-- Teacher Ratings -->
            <div class="col-lg-4 col-md-6 fade-in-up" style="transition-delay: 0.3s;">
                <div class="feature-main-card" style="--card-color-1: #ee0979; --card-color-2: #ff6a00;">
                    <div class="feature-icon-wrap" style="background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);">
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <h3>Teacher Rating System</h3>
                    <p>Comprehensive teacher evaluation with multi-dimensional ratings and constructive feedback mechanisms.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check-circle"></i>Multi-criteria ratings</li>
                        <li><i class="fas fa-check-circle"></i>Anonymous feedback</li>
                        <li><i class="fas fa-check-circle"></i>Performance tracking</li>
                        <li><i class="fas fa-check-circle"></i>Improvement suggestions</li>
                    </ul>
                </div>
            </div>

            <!-- Complaint Management -->
            <div class="col-lg-4 col-md-6 fade-in-up" style="transition-delay: 0.4s;">
                <div class="feature-main-card" style="--card-color-1: #f093fb; --card-color-2: #f5576c;">
                    <div class="feature-icon-wrap" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-comment-dots"></i>
                    </div>
                    <h3>Complaint Management</h3>
                    <p>Streamlined system for handling student concerns with priority levels and resolution tracking.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check-circle"></i>Priority assignment</li>
                        <li><i class="fas fa-check-circle"></i>Status tracking</li>
                        <li><i class="fas fa-check-circle"></i>Resolution timeline</li>
                        <li><i class="fas fa-check-circle"></i>Follow-up system</li>
                    </ul>
                </div>
            </div>

            <!-- Security -->
            <div class="col-lg-4 col-md-6 fade-in-up" style="transition-delay: 0.5s;">
                <div class="feature-main-card" style="--card-color-1: #4facfe; --card-color-2: #00f2fe;">
                    <div class="feature-icon-wrap" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Enterprise Security</h3>
                    <p>Bank-grade security measures protect your data with encryption, access controls, and compliance.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check-circle"></i>End-to-end encryption</li>
                        <li><i class="fas fa-check-circle"></i>Role-based access</li>
                        <li><i class="fas fa-check-circle"></i>GDPR compliant</li>
                        <li><i class="fas fa-check-circle"></i>Audit logging</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- AI Highlight Section -->
<section class="py-5">
    <div class="container">
        <div class="highlight-section fade-in-up">
            <div class="row align-items-center">
                <div class="col-lg-5 mb-4 mb-lg-0">
                    <div class="highlight-content">
                        <span class="badge bg-white bg-opacity-20 text-white px-3 py-2 rounded-pill mb-3">
                            <i class="fas fa-magic me-2"></i>AI-Powered
                        </span>
                        <h2 class="highlight-title">Intelligence That Transforms Feedback</h2>
                        <p class="highlight-text">
                            Our advanced AI engine goes beyond simple data collection. It understands context, 
                            identifies patterns, and provides actionable insights that help improve educational outcomes.
                        </p>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="highlight-feature-grid">
                        <div class="highlight-feature-item">
                            <div class="highlight-feature-icon" style="background: linear-gradient(135deg, #00c6fb 0%, #005bea 100%);">
                                <i class="fas fa-comments"></i>
                            </div>
                            <div>
                                <h5>Sentiment Analysis</h5>
                                <p>Automatically detect positive, negative, and neutral feedback tones</p>
                            </div>
                        </div>
                        <div class="highlight-feature-item">
                            <div class="highlight-feature-icon" style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div>
                                <h5>Predictive Analytics</h5>
                                <p>Forecast satisfaction trends before they become issues</p>
                            </div>
                        </div>
                        <div class="highlight-feature-item">
                            <div class="highlight-feature-icon" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                                <i class="fas fa-tags"></i>
                            </div>
                            <div>
                                <h5>Auto-Categorization</h5>
                                <p>Intelligently sort and tag feedback by topic and priority</p>
                            </div>
                        </div>
                        <div class="highlight-feature-item">
                            <div class="highlight-feature-icon" style="background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);">
                                <i class="fas fa-lightbulb"></i>
                            </div>
                            <div>
                                <h5>Smart Recommendations</h5>
                                <p>Get AI-generated suggestions for improvement actions</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Role-Based Features -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5 fade-in-up">
            <span class="section-badge">Role-Based Access</span>
            <h2 class="section-title">Features for Everyone</h2>
            <p class="section-desc">Tailored experiences for students, teachers, and administrators</p>
        </div>

        <div class="role-tab-nav fade-in-up">
            <button class="role-tab-btn active" onclick="showRoleContent('students')">
                <i class="fas fa-user-graduate"></i>Students
            </button>
            <button class="role-tab-btn" onclick="showRoleContent('teachers')">
                <i class="fas fa-chalkboard-teacher"></i>Teachers
            </button>
            <button class="role-tab-btn" onclick="showRoleContent('admins')">
                <i class="fas fa-user-shield"></i>Administrators
            </button>
        </div>

        <!-- Students Content -->
        <div id="students-content" class="role-content active">
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="role-feature-card">
                        <div class="role-feature-icon" style="background: linear-gradient(135deg, #00c6fb 0%, #005bea 100%);">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <h4>Easy Survey Access</h4>
                        <p>Complete surveys quickly with our mobile-friendly interface and save progress anytime.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="role-feature-card">
                        <div class="role-feature-icon" style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);">
                            <i class="fas fa-star"></i>
                        </div>
                        <h4>Rate Teachers</h4>
                        <p>Provide detailed ratings across multiple criteria to help improve teaching quality.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="role-feature-card">
                        <div class="role-feature-icon" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                            <i class="fas fa-comment-alt"></i>
                        </div>
                        <h4>Submit Complaints</h4>
                        <p>Voice concerns anonymously and track resolution status in real-time.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="role-feature-card">
                        <div class="role-feature-icon" style="background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h4>View Analytics</h4>
                        <p>See how your feedback contributes to positive changes in your institution.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Teachers Content -->
        <div id="teachers-content" class="role-content">
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="role-feature-card">
                        <div class="role-feature-icon" style="background: linear-gradient(135deg, #00c6fb 0%, #005bea 100%);">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4>Performance Insights</h4>
                        <p>Access detailed analytics on your ratings and feedback trends over time.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="role-feature-card">
                        <div class="role-feature-icon" style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h4>Student Feedback</h4>
                        <p>Review constructive feedback and identify areas for improvement.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="role-feature-card">
                        <div class="role-feature-icon" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h4>Submit Suggestions</h4>
                        <p>Propose improvements to courses, facilities, or administrative processes.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="role-feature-card">
                        <div class="role-feature-icon" style="background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <h4>Track Progress</h4>
                        <p>Monitor your improvement over semesters with comparative analytics.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admins Content -->
        <div id="admins-content" class="role-content">
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="role-feature-card">
                        <div class="role-feature-icon" style="background: linear-gradient(135deg, #00c6fb 0%, #005bea 100%);">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <h4>User Management</h4>
                        <p>Manage all users, roles, and permissions from a centralized dashboard.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="role-feature-card">
                        <div class="role-feature-icon" style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h4>System Configuration</h4>
                        <p>Customize surveys, AI models, and platform settings to your needs.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="role-feature-card">
                        <div class="role-feature-icon" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                            <i class="fas fa-file-export"></i>
                        </div>
                        <h4>Advanced Reports</h4>
                        <p>Generate comprehensive reports with AI insights and export in multiple formats.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="role-feature-card">
                        <div class="role-feature-icon" style="background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);">
                            <i class="fas fa-bell"></i>
                        </div>
                        <h4>Alerts & Notifications</h4>
                        <p>Set up automated alerts for critical feedback or low satisfaction scores.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Comparison Table -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5 fade-in-up">
            <span class="section-badge">Compare Plans</span>
            <h2 class="section-title">Choose Your Plan</h2>
            <p class="section-desc">Find the perfect plan for your institution's needs</p>
        </div>

        <div class="table-responsive fade-in-up">
            <table class="comparison-table table mb-0">
                <thead>
                    <tr>
                        <th>Feature</th>
                        <th>Basic</th>
                        <th>Professional</th>
                        <th>Enterprise</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Survey Creation</td>
                        <td><i class="fas fa-check check-icon"></i></td>
                        <td><i class="fas fa-check check-icon"></i></td>
                        <td><i class="fas fa-check check-icon"></i></td>
                    </tr>
                    <tr>
                        <td>Response Collection</td>
                        <td>1,000/month</td>
                        <td>10,000/month</td>
                        <td>Unlimited</td>
                    </tr>
                    <tr>
                        <td>Basic Analytics</td>
                        <td><i class="fas fa-check check-icon"></i></td>
                        <td><i class="fas fa-check check-icon"></i></td>
                        <td><i class="fas fa-check check-icon"></i></td>
                    </tr>
                    <tr>
                        <td>AI-Powered Insights</td>
                        <td><i class="fas fa-times cross-icon"></i></td>
                        <td><i class="fas fa-check check-icon"></i></td>
                        <td><i class="fas fa-check check-icon"></i></td>
                    </tr>
                    <tr>
                        <td>Sentiment Analysis</td>
                        <td><i class="fas fa-times cross-icon"></i></td>
                        <td><i class="fas fa-check check-icon"></i></td>
                        <td><i class="fas fa-check check-icon"></i></td>
                    </tr>
                    <tr>
                        <td>Custom Branding</td>
                        <td><i class="fas fa-times cross-icon"></i></td>
                        <td><i class="fas fa-check check-icon"></i></td>
                        <td><i class="fas fa-check check-icon"></i></td>
                    </tr>
                    <tr>
                        <td>API Access</td>
                        <td><i class="fas fa-times cross-icon"></i></td>
                        <td><i class="fas fa-times cross-icon"></i></td>
                        <td><i class="fas fa-check check-icon"></i></td>
                    </tr>
                    <tr>
                        <td>Dedicated Support</td>
                        <td><i class="fas fa-times cross-icon"></i></td>
                        <td><i class="fas fa-times cross-icon"></i></td>
                        <td><i class="fas fa-check check-icon"></i></td>
                    </tr>
                    <tr>
                        <td>Priority</td>
                        <td><a href="register.php" class="btn btn-outline-primary btn-sm rounded-pill">Get Started</a></td>
                        <td><a href="register.php" class="btn btn-primary btn-sm rounded-pill">Most Popular</a></td>
                        <td><a href="contact.php" class="btn btn-outline-primary btn-sm rounded-pill">Contact Sales</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Integrations -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5 fade-in-up">
            <span class="section-badge">Integrations</span>
            <h2 class="section-title">Works With Your Tools</h2>
            <p class="section-desc">Seamlessly integrate with the platforms you already use</p>
        </div>

        <div class="integration-grid fade-in-up">
            <div class="integration-card">
                <i class="fab fa-google"></i>
                <h5>Google Workspace</h5>
            </div>
            <div class="integration-card">
                <i class="fab fa-microsoft"></i>
                <h5>Microsoft 365</h5>
            </div>
            <div class="integration-card">
                <i class="fab fa-slack"></i>
                <h5>Slack</h5>
            </div>
            <div class="integration-card">
                <i class="fas fa-graduation-cap"></i>
                <h5>Moodle</h5>
            </div>
            <div class="integration-card">
                <i class="fas fa-chalkboard"></i>
                <h5>Canvas LMS</h5>
            </div>
            <div class="integration-card">
                <i class="fas fa-database"></i>
                <h5>REST API</h5>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5">
    <div class="container">
        <div class="cta-section text-center fade-in-up position-relative">
            <div class="position-relative" style="z-index: 2;">
                <h2 class="cta-title mb-3">Ready to Get Started?</h2>
                <p class="cta-desc mb-4 mx-auto" style="max-width: 600px;">
                    Join thousands of institutions using EduSurvey Pro to transform their educational feedback systems.
                </p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="register.php" class="btn btn-cta btn-cta-primary">
                        <i class="fas fa-rocket me-2"></i>Start Free Trial
                    </a>
                    <a href="contact.php" class="btn btn-cta btn-cta-outline">
                        <i class="fas fa-calendar me-2"></i>Schedule Demo
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Scripts -->
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
            const href = this.getAttribute('href');
            if (!href || href === '#') return;
            
            e.preventDefault();
            try {
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            } catch (error) {
                console.error('Invalid selector:', href);
            }
        });
    });
});

// Role tab switching
function showRoleContent(role) {
    // Update buttons
    document.querySelectorAll('.role-tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.currentTarget.classList.add('active');
    
    // Update content
    document.querySelectorAll('.role-content').forEach(content => {
        content.classList.remove('active');
    });
    document.getElementById(role + '-content').classList.add('active');
}
</script>

<?php require_once '../core/includes/footer.php'; ?>
