<?php
session_start();
$pageTitle = "Documentation";
$basePath = "../";
require_once '../core/includes/header.php';
?>

<style>
/* Modern Documentation Page Styles */
.docs-hero {
    background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
    min-height: 45vh;
    display: flex;
    align-items: center;
    position: relative;
    overflow: hidden;
    margin-top: -20px;
}

.docs-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23667eea' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
}

.hero-content {
    position: relative;
    z-index: 2;
}

.hero-title {
    font-size: 3rem;
    font-weight: 800;
    color: white;
    animation: fadeInUp 1s ease-out;
}

.hero-subtitle {
    font-size: 1.3rem;
    color: rgba(255,255,255,0.8);
    animation: fadeInUp 1s ease-out 0.2s both;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Sidebar Navigation */
.docs-sidebar {
    position: sticky;
    top: 100px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
    overflow: hidden;
}

.sidebar-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 1.5rem;
    color: white;
}

.sidebar-header h5 {
    margin: 0;
    font-weight: 700;
}

.docs-nav {
    padding: 0;
}

.docs-nav .nav-link {
    padding: 1rem 1.5rem;
    color: #4a5568;
    font-weight: 500;
    border-left: 3px solid transparent;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 12px;
}

.docs-nav .nav-link:hover {
    background: linear-gradient(90deg, rgba(102,126,234,0.1) 0%, transparent 100%);
    color: #667eea;
    border-left-color: #667eea;
}

.docs-nav .nav-link.active {
    background: linear-gradient(90deg, rgba(102,126,234,0.15) 0%, transparent 100%);
    color: #667eea;
    border-left-color: #667eea;
    font-weight: 600;
}

.docs-nav .nav-link i {
    width: 20px;
    text-align: center;
    font-size: 0.9rem;
}

/* Main Content Area */
.docs-content {
    background: white;
    border-radius: 24px;
    box-shadow: 0 15px 50px rgba(0,0,0,0.08);
    overflow: hidden;
}

.docs-content-header {
    background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
    padding: 2rem;
    border-bottom: 1px solid rgba(102,126,234,0.1);
}

.docs-content-body {
    padding: 2rem;
}

/* Section Styling */
.docs-section {
    margin-bottom: 3rem;
    scroll-margin-top: 100px;
}

.section-badge {
    display: inline-block;
    padding: 0.4rem 1rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 0.75rem;
}

.section-title {
    font-size: 2rem;
    font-weight: 800;
    background: linear-gradient(135deg, #1a1a2e 0%, #667eea 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 0.5rem;
}

.section-desc {
    font-size: 1.1rem;
    color: #6b7280;
}

/* Role Cards */
.role-card {
    background: linear-gradient(145deg, #ffffff 0%, #f8f9ff 100%);
    border-radius: 20px;
    padding: 2rem;
    height: 100%;
    border: 1px solid rgba(102,126,234,0.1);
    transition: all 0.3s ease;
}

.role-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(102,126,234,0.15);
    border-color: #667eea;
}

.role-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    margin-bottom: 1.25rem;
}

.role-card h4 {
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 1rem;
}

.role-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.role-list li {
    padding: 0.75rem 0;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 12px;
    color: #4a5568;
    transition: all 0.2s ease;
}

.role-list li:last-child {
    border-bottom: none;
}

.role-list li:hover {
    color: #667eea;
    padding-left: 5px;
}

.role-list li i {
    color: #667eea;
    font-size: 0.9rem;
}

/* Modern Accordion */
.modern-accordion .accordion-item {
    border: none;
    margin-bottom: 1rem;
    border-radius: 16px !important;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.modern-accordion .accordion-button {
    padding: 1.25rem 1.5rem;
    font-weight: 600;
    color: #1a1a2e;
    background: white;
    border-radius: 16px !important;
}

.modern-accordion .accordion-button:not(.collapsed) {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: none;
}

.modern-accordion .accordion-button::after {
    background-size: 16px;
    transition: all 0.3s ease;
}

.modern-accordion .accordion-button:not(.collapsed)::after {
    filter: brightness(0) invert(1);
}

.modern-accordion .accordion-body {
    padding: 1.5rem;
    background: #f8f9ff;
}

.modern-accordion .accordion-body ol,
.modern-accordion .accordion-body ul {
    margin: 0;
    padding-left: 1.25rem;
}

.modern-accordion .accordion-body li {
    padding: 0.5rem 0;
    color: #4a5568;
}

/* Feature Cards Grid */
.feature-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

@media (max-width: 768px) {
    .feature-grid {
        grid-template-columns: 1fr;
    }
}

.feature-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    border: 1px solid rgba(102,126,234,0.1);
    transition: all 0.3s ease;
}

.feature-card:hover {
    border-color: #667eea;
    box-shadow: 0 10px 30px rgba(102,126,234,0.1);
}

.feature-card .feature-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin-bottom: 1rem;
}

.feature-card h5 {
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 0.5rem;
}

.feature-card p {
    color: #6b7280;
    margin: 0;
    font-size: 0.95rem;
}

/* API Section */
.api-card {
    background: linear-gradient(135deg, #1a1a2e 0%, #2d2d44 100%);
    border-radius: 20px;
    overflow: hidden;
}

.api-header {
    background: rgba(102,126,234,0.2);
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.api-header h5 {
    color: #a5b4fc;
    font-weight: 600;
    margin: 0;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.api-body {
    padding: 1.5rem;
}

.api-url {
    background: rgba(0,0,0,0.3);
    border-radius: 10px;
    padding: 1rem 1.25rem;
    font-family: 'Consolas', 'Monaco', monospace;
    color: #22d3ee;
    font-size: 0.95rem;
    margin-bottom: 1.5rem;
}

.api-section-title {
    color: white;
    font-weight: 600;
    margin-bottom: 0.75rem;
    font-size: 1rem;
}

.api-text {
    color: rgba(255,255,255,0.7);
    font-size: 0.95rem;
}

.endpoint-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.endpoint-list li {
    padding: 0.75rem 1rem;
    background: rgba(0,0,0,0.2);
    border-radius: 8px;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 12px;
}

.endpoint-method {
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
}

.method-get {
    background: rgba(34, 197, 94, 0.2);
    color: #22c55e;
}

.method-post {
    background: rgba(59, 130, 246, 0.2);
    color: #3b82f6;
}

.endpoint-path {
    font-family: 'Consolas', 'Monaco', monospace;
    color: #e2e8f0;
}

.endpoint-desc {
    color: rgba(255,255,255,0.5);
    font-size: 0.85rem;
    margin-left: auto;
}

/* Document Cards */
.doc-card {
    background: white;
    border-radius: 20px;
    padding: 1.5rem;
    height: 100%;
    border: 2px solid transparent;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.doc-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #667eea, #764ba2);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.doc-card:hover {
    border-color: rgba(102,126,234,0.3);
    box-shadow: 0 15px 40px rgba(102,126,234,0.15);
    transform: translateY(-5px);
}

.doc-card:hover::before {
    transform: scaleX(1);
}

.doc-icon {
    width: 55px;
    height: 55px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    color: white;
    flex-shrink: 0;
}

.doc-card h5 {
    font-weight: 700;
    color: #1a1a2e;
    margin-bottom: 0.5rem;
}

.doc-card p {
    color: #6b7280;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.doc-btn-group .btn {
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 10px;
}

.btn-doc-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
}

.btn-doc-primary:hover {
    background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
    color: white;
    transform: translateY(-2px);
}

.btn-doc-outline {
    background: transparent;
    color: #667eea;
    border: 2px solid rgba(102,126,234,0.3);
}

.btn-doc-outline:hover {
    background: rgba(102,126,234,0.1);
    border-color: #667eea;
    color: #667eea;
}

/* Search Box */
.search-box {
    background: white;
    border-radius: 16px;
    padding: 0.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.search-box:focus-within {
    border-color: #667eea;
    box-shadow: 0 4px 20px rgba(102,126,234,0.2);
}

.search-box .input-group-text {
    background: transparent;
    border: none;
    color: #667eea;
}

.search-box .form-control {
    border: none;
    padding: 0.75rem;
    font-size: 1rem;
}

.search-box .form-control:focus {
    box-shadow: none;
}

.search-box .btn {
    border: none;
    background: transparent;
    color: #6b7280;
}

.search-box .btn:hover {
    color: #667eea;
}

/* Troubleshooting Table */
.trouble-table {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.trouble-table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.trouble-table thead th {
    color: white;
    font-weight: 600;
    padding: 1rem 1.5rem;
    border: none;
}

.trouble-table tbody td {
    padding: 1rem 1.5rem;
    vertical-align: middle;
    border-color: rgba(0,0,0,0.05);
}

.trouble-table tbody tr:hover {
    background: rgba(102,126,234,0.05);
}

/* Info Alert */
.info-alert {
    background: linear-gradient(135deg, rgba(102,126,234,0.1) 0%, rgba(118,75,162,0.1) 100%);
    border: none;
    border-radius: 16px;
    padding: 1.5rem;
    border-left: 4px solid #667eea;
}

.info-alert h5 {
    color: #667eea;
    font-weight: 700;
}

/* Help Card */
.help-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 2rem;
    color: white;
    text-align: center;
}

.help-card h4 {
    font-weight: 700;
    margin-bottom: 1rem;
}

.help-card p {
    opacity: 0.9;
    margin-bottom: 1.5rem;
}

.help-card a {
    color: white;
    text-decoration: underline;
    font-weight: 600;
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
@media (max-width: 991px) {
    .docs-sidebar {
        position: relative;
        top: 0;
        margin-bottom: 2rem;
    }
    
    .hero-title {
        font-size: 2.2rem;
    }
}

@media (max-width: 768px) {
    .docs-hero {
        min-height: 35vh;
    }
    
    .section-title {
        font-size: 1.6rem;
    }
}
</style>

<!-- Hero Section -->
<section class="docs-hero">
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-10 mx-auto text-center text-white">
                <span class="badge bg-white bg-opacity-20 text-white px-4 py-2 rounded-pill mb-4" style="font-size: 0.9rem; backdrop-filter: blur(10px);">
                    <i class="fas fa-book-open me-2"></i>Documentation Center
                </span>
                <h1 class="hero-title mb-4">EduSurvey Pro Documentation</h1>
                <p class="hero-subtitle mb-4">
                    Complete guides, tutorials, and API references to help you master our platform
                </p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="#getting-started" class="btn btn-light btn-lg rounded-pill px-4">
                        <i class="fas fa-rocket me-2"></i>Quick Start
                    </a>
                    <a href="#api-docs" class="btn btn-outline-light btn-lg rounded-pill px-4">
                        <i class="fas fa-code me-2"></i>API Docs
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
            <li class="breadcrumb-item active" aria-current="page">Documentation</li>
        </ol>
    </nav>
</div>

<!-- Main Content -->
<section class="py-4">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="docs-sidebar fade-in-up">
                    <div class="sidebar-header">
                        <h5><i class="fas fa-compass me-2"></i>Navigation</h5>
                    </div>
                    <nav class="docs-nav nav flex-column">
                        <a class="nav-link active" href="#getting-started">
                            <i class="fas fa-play-circle"></i>Getting Started
                        </a>
                        <a class="nav-link" href="#user-guide">
                            <i class="fas fa-user"></i>User Guide
                        </a>
                        <a class="nav-link" href="#admin-guide">
                            <i class="fas fa-user-shield"></i>Admin Guide
                        </a>
                        <a class="nav-link" href="#api-docs">
                            <i class="fas fa-code"></i>API Documentation
                        </a>
                        <a class="nav-link" href="#project-docs">
                            <i class="fas fa-folder-open"></i>Project Documents
                        </a>
                        <a class="nav-link" href="#troubleshooting">
                            <i class="fas fa-wrench"></i>Troubleshooting
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Content -->
            <div class="col-lg-9">
                <div class="docs-content fade-in-up" style="transition-delay: 0.2s;">
                    <div class="docs-content-header">
                        <h2 class="mb-1 fw-bold"><i class="fas fa-book me-3 text-primary"></i>Documentation Guide</h2>
                        <p class="text-muted mb-0">Everything you need to know about EduSurvey Pro</p>
                    </div>
                    <div class="docs-content-body">
                        
                        <!-- Getting Started Section -->
                        <section id="getting-started" class="docs-section">
                            <span class="section-badge">Start Here</span>
                            <h2 class="section-title">Getting Started</h2>
                            <p class="section-desc mb-4">Welcome to EduSurvey Pro! This guide will help you get up and running quickly.</p>
                            
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="role-card">
                                        <div class="role-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>
                                        <h4>For Students</h4>
                                        <ul class="role-list">
                                            <li><i class="fas fa-user-plus"></i>Create your account</li>
                                            <li><i class="fas fa-poll"></i>Complete surveys</li>
                                            <li><i class="fas fa-star"></i>Rate teachers</li>
                                            <li><i class="fas fa-chart-bar"></i>View analytics</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="role-card">
                                        <div class="role-icon" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                                            <i class="fas fa-chalkboard-teacher"></i>
                                        </div>
                                        <h4>For Teachers</h4>
                                        <ul class="role-list">
                                            <li><i class="fas fa-clipboard-list"></i>Access feedback</li>
                                            <li><i class="fas fa-chart-line"></i>View performance data</li>
                                            <li><i class="fas fa-users"></i>Manage student interactions</li>
                                            <li><i class="fas fa-lightbulb"></i>Submit suggestions</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- User Guide Section -->
                        <section id="user-guide" class="docs-section">
                            <span class="section-badge">User Manual</span>
                            <h2 class="section-title">User Guide</h2>
                            <p class="section-desc mb-4">Step-by-step instructions for common tasks.</p>
                            
                            <div class="accordion modern-accordion" id="userGuideAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#loginProcess">
                                            <i class="fas fa-sign-in-alt me-3"></i>Login Process
                                        </button>
                                    </h2>
                                    <div id="loginProcess" class="accordion-collapse collapse show" data-bs-parent="#userGuideAccordion">
                                        <div class="accordion-body">
                                            <ol>
                                                <li>Navigate to the login page</li>
                                                <li>Enter your username and password</li>
                                                <li>Select your role (Student, Teacher, or Admin)</li>
                                                <li>Click "Login" to access your dashboard</li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#surveyCompletion">
                                            <i class="fas fa-clipboard-check me-3"></i>Survey Completion
                                        </button>
                                    </h2>
                                    <div id="surveyCompletion" class="accordion-collapse collapse" data-bs-parent="#userGuideAccordion">
                                        <div class="accordion-body">
                                            <p class="mb-3">Complete surveys to provide valuable feedback:</p>
                                            <ul>
                                                <li>Access surveys from your dashboard</li>
                                                <li>Rate questions on a 1-5 scale</li>
                                                <li>Provide detailed comments when requested</li>
                                                <li>Submit your responses</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Admin Guide Section -->
                        <section id="admin-guide" class="docs-section">
                            <span class="section-badge">Administration</span>
                            <h2 class="section-title">Administrator Guide</h2>
                            
                            <div class="info-alert alert mb-4">
                                <h5><i class="fas fa-info-circle me-2"></i>Admin Access Required</h5>
                                <p class="mb-0">This section is for system administrators who manage the EduSurvey Pro platform.</p>
                            </div>
                            
                            <div class="feature-grid">
                                <div class="feature-card">
                                    <div class="feature-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <h5>User Management</h5>
                                    <p>Create, edit, and manage user accounts across all roles.</p>
                                </div>
                                <div class="feature-card">
                                    <div class="feature-icon" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                                        <i class="fas fa-chart-pie"></i>
                                    </div>
                                    <h5>Analytics Dashboard</h5>
                                    <p>Comprehensive reporting and data visualization tools.</p>
                                </div>
                                <div class="feature-card">
                                    <div class="feature-icon" style="background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);">
                                        <i class="fas fa-cog"></i>
                                    </div>
                                    <h5>System Configuration</h5>
                                    <p>Configure system settings, surveys, and AI models.</p>
                                </div>
                                <div class="feature-card">
                                    <div class="feature-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                                        <i class="fas fa-robot"></i>
                                    </div>
                                    <h5>AI Insights</h5>
                                    <p>Advanced AI-powered analysis and recommendations.</p>
                                </div>
                            </div>
                        </section>

                        <!-- API Documentation Section -->
                        <section id="api-docs" class="docs-section">
                            <span class="section-badge">For Developers</span>
                            <h2 class="section-title">API Documentation</h2>
                            <p class="section-desc mb-4">Integrate EduSurvey Pro with your applications.</p>
                            
                            <div class="api-card">
                                <div class="api-header">
                                    <h5><i class="fas fa-server me-2"></i>Base Configuration</h5>
                                </div>
                                <div class="api-body">
                                    <div class="api-url">
                                        <i class="fas fa-link me-2"></i>http://localhost/stu/api/
                                    </div>
                                    
                                    <h6 class="api-section-title"><i class="fas fa-key me-2"></i>Authentication</h6>
                                    <p class="api-text mb-4">All API requests require authentication via session or API key.</p>
                                    
                                    <h6 class="api-section-title"><i class="fas fa-route me-2"></i>Available Endpoints</h6>
                                    <ul class="endpoint-list">
                                        <li>
                                            <span class="endpoint-method method-get">GET</span>
                                            <span class="endpoint-path">/surveys</span>
                                            <span class="endpoint-desc">List all surveys</span>
                                        </li>
                                        <li>
                                            <span class="endpoint-method method-post">POST</span>
                                            <span class="endpoint-path">/responses</span>
                                            <span class="endpoint-desc">Submit survey responses</span>
                                        </li>
                                        <li>
                                            <span class="endpoint-method method-get">GET</span>
                                            <span class="endpoint-path">/analytics</span>
                                            <span class="endpoint-desc">Retrieve analytics data</span>
                                        </li>
                                        <li>
                                            <span class="endpoint-method method-get">GET</span>
                                            <span class="endpoint-path">/users</span>
                                            <span class="endpoint-desc">User management (Admin only)</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </section>

                        <!-- Project Documents Section -->
                        <section id="project-docs" class="docs-section">
                            <span class="section-badge">Resources</span>
                            <h2 class="section-title">Project Documents</h2>
                            <p class="section-desc mb-4">Access comprehensive project documentation and guides.</p>
                            
                            <!-- Search Box -->
                            <div class="search-box mb-4">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="docSearch" placeholder="Search documents..." onkeyup="filterDocuments()">
                                    <button class="btn" type="button" onclick="clearSearch()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="row g-4" id="documentsGrid">
                                <?php 
                                // Define markdown files with descriptions
                                $mdFiles = [
                                    'REORGANIZATION_GUIDE.md' => [
                                        'title' => 'Reorganization Guide',
                                        'description' => 'Complete guide to the new project structure and file organization',
                                        'icon' => 'fas fa-sitemap',
                                        'path' => '../REORGANIZATION_GUIDE.md',
                                        'color' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
                                    ],
                                    'README.md' => [
                                        'title' => 'Project README',
                                        'description' => 'Main project overview, features, and setup instructions',
                                        'icon' => 'fas fa-file-alt',
                                        'path' => '../README.md',
                                        'color' => 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)'
                                    ],
                                    'AI_COMPREHENSIVE_REPORT.md' => [
                                        'title' => 'AI System Report',
                                        'description' => 'Comprehensive AI system capabilities and performance analysis',
                                        'icon' => 'fas fa-robot',
                                        'path' => '../AI_COMPREHENSIVE_REPORT.md',
                                        'color' => 'linear-gradient(135deg, #ee0979 0%, #ff6a00 100%)'
                                    ],
                                    'DEPLOYMENT_SUCCESS.md' => [
                                        'title' => 'Deployment Guide',
                                        'description' => 'Deployment success stories and configuration guide',
                                        'icon' => 'fas fa-rocket',
                                        'path' => '../DEPLOYMENT_SUCCESS.md',
                                        'color' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)'
                                    ],
                                    'python_path_fix_guide.md' => [
                                        'title' => 'Python Setup Guide',
                                        'description' => 'Python environment configuration and troubleshooting',
                                        'icon' => 'fab fa-python',
                                        'path' => '../python_path_fix_guide.md',
                                        'color' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)'
                                    ],
                                    'docs/file-structure.md' => [
                                        'title' => 'File Structure',
                                        'description' => 'Detailed file structure documentation',
                                        'icon' => 'fas fa-folder-tree',
                                        'path' => '../docs/file-structure.md',
                                        'color' => 'linear-gradient(135deg, #5ee7df 0%, #b490ca 100%)'
                                    ]
                                ];
                                
                                foreach ($mdFiles as $filename => $info): ?>
                                    <div class="col-lg-6 doc-item" data-title="<?php echo strtolower($info['title']); ?>" data-description="<?php echo strtolower($info['description']); ?>" data-filename="<?php echo strtolower($filename); ?>">
                                        <div class="doc-card">
                                            <div class="d-flex align-items-start gap-3">
                                                <div class="doc-icon" style="background: <?php echo $info['color']; ?>;">
                                                    <i class="<?php echo $info['icon']; ?>"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h5><?php echo $info['title']; ?></h5>
                                                    <p><?php echo $info['description']; ?></p>
                                                    <div class="doc-btn-group d-flex gap-2">
                                                        <button class="btn btn-doc-primary btn-sm" onclick="loadMarkdownContent('<?php echo $filename; ?>', '<?php echo $info['path']; ?>')">
                                                            <i class="fas fa-eye me-1"></i>View
                                                        </button>
                                                        <button class="btn btn-doc-outline btn-sm" onclick="downloadMarkdownFile('<?php echo $filename; ?>', '<?php echo addslashes($info['title']); ?>')">
                                                            <i class="fas fa-download me-1"></i>Download
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        
                        <!-- Markdown Viewer Modal -->
                        <div class="modal fade" id="markdownModal" tabindex="-1" aria-labelledby="markdownModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                <div class="modal-content" style="border-radius: 20px; overflow: hidden;">
                                    <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                        <h5 class="modal-title text-white" id="markdownModalLabel"><i class="fas fa-file-alt me-2"></i>Document Viewer</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body" style="background: #f8f9ff;">
                                        <div id="markdownContent" class="markdown-content">
                                            <div class="text-center py-5">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer" style="border: none; background: white;">
                                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-doc-primary rounded-pill px-4" id="downloadCurrentDoc">
                                            <i class="fas fa-download me-1"></i>Download
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </section>

                        <!-- Troubleshooting Section -->
                        <section id="troubleshooting" class="docs-section">
                            <span class="section-badge">Support</span>
                            <h2 class="section-title">Troubleshooting</h2>
                            <p class="section-desc mb-4">Solutions to common issues you might encounter.</p>
                            
                            <div class="table-responsive trouble-table mb-4">
                                <table class="table mb-0">
                                    <thead>
                                        <tr>
                                            <th><i class="fas fa-exclamation-triangle me-2"></i>Issue</th>
                                            <th><i class="fas fa-check-circle me-2"></i>Solution</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>Can't log in</strong></td>
                                            <td>Check credentials and ensure account is active</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Survey not loading</strong></td>
                                            <td>Refresh page or contact administrator</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Data not displaying</strong></td>
                                            <td>Clear browser cache and reload</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Permission denied</strong></td>
                                            <td>Contact admin to verify role permissions</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="help-card">
                                <h4><i class="fas fa-question-circle me-2"></i>Need More Help?</h4>
                                <p>Our support team is available 24/7 to assist you with any questions or issues.</p>
                                <a href="mailto:support@edusurvey.pro" class="btn btn-light rounded-pill px-4">
                                    <i class="fas fa-envelope me-2"></i>support@edusurvey.pro
                                </a>
                            </div>
                        </section>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Intersection Observer for fade-in animations
document.addEventListener('DOMContentLoaded', function() {
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
});

// Smooth scrolling for navigation links
document.querySelectorAll('.docs-nav .nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        
        // Only process valid hrefs that start with #
        if (!href || href === '#' || !href.startsWith('#')) {
            return;
        }
        
        e.preventDefault();
        
        try {
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // Update active nav link
                document.querySelectorAll('.docs-nav .nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            }
        } catch (error) {
            console.error('Invalid selector:', href, error);
        }
    });
});

// Update active nav on scroll
window.addEventListener('scroll', function() {
    const sections = document.querySelectorAll('.docs-section');
    const navLinks = document.querySelectorAll('.docs-nav .nav-link');
    
    let current = '';
    sections.forEach(section => {
        const sectionTop = section.offsetTop - 150;
        if (window.scrollY >= sectionTop) {
            current = section.getAttribute('id');
        }
    });
    
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === '#' + current) {
            link.classList.add('active');
        }
    });
});

// Markdown file loader
let currentDocUrl = '';

function loadMarkdownContent(filename, filePath) {
    currentDocUrl = filePath;
    const modal = new bootstrap.Modal(document.getElementById('markdownModal'));
    const modalTitle = document.getElementById('markdownModalLabel');
    const contentDiv = document.getElementById('markdownContent');
    
    modalTitle.textContent = filename;
    contentDiv.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading ${filename}...</p>
        </div>
    `;
    
    modal.show();
    
    // Fetch and display markdown content
    fetch(`../app/api/markdown_reader.php?file=${encodeURIComponent(filename)}`)
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('API Response:', data);
            if (data.status === 200) {
                // Enhanced markdown to HTML conversion with better formatting
                let htmlContent = data.content;
                
                // First, handle code blocks to preserve them
                const codeBlocks = [];
                htmlContent = htmlContent.replace(/```(\w+)?\n([\s\S]*?)```/g, (match, lang, code) => {
                    const index = codeBlocks.length;
                    codeBlocks.push(`<div class="code-block mb-4">
                        <div class="code-header bg-dark text-light px-3 py-2 rounded-top">
                            <small><i class="fas fa-code me-2"></i>${lang || 'Code'}</small>
                        </div>
                        <pre class="bg-dark text-light p-3 rounded-bottom mb-0"><code class="language-${lang || 'text'}">${code.trim()}</code></pre>
                    </div>`);
                    return `__CODE_BLOCK_${index}__`;
                });
                
                // Handle inline code
                htmlContent = htmlContent.replace(/`([^`]+)`/g, '<code class="inline-code bg-light text-dark px-2 py-1 rounded">$1</code>');
                
                // Handle headers with proper hierarchy
                htmlContent = htmlContent.replace(/^# (.*$)/gim, '<h1 class="doc-h1 text-primary border-bottom pb-2 mb-4">$1</h1>');
                htmlContent = htmlContent.replace(/^## (.*$)/gim, '<h2 class="doc-h2 text-secondary mt-4 mb-3">$1</h2>');
                htmlContent = htmlContent.replace(/^### (.*$)/gim, '<h3 class="doc-h3 text-info mt-3 mb-2">$1</h3>');
                htmlContent = htmlContent.replace(/^#### (.*$)/gim, '<h4 class="doc-h4 mt-3 mb-2">$1</h4>');
                htmlContent = htmlContent.replace(/^##### (.*$)/gim, '<h5 class="doc-h5 mt-2 mb-2">$1</h5>');
                
                // Handle bold and italic with proper nesting
                htmlContent = htmlContent.replace(/\*\*\*(.*?)\*\*\*/g, '<strong><em>$1</em></strong>');
                htmlContent = htmlContent.replace(/\*\*(.*?)\*\*/g, '<strong class="fw-bold">$1</strong>');
                htmlContent = htmlContent.replace(/\*(.*?)\*/g, '<em class="fst-italic">$1</em>');
                
                // Handle links
                htmlContent = htmlContent.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" class="text-decoration-none" target="_blank"><i class="fas fa-external-link-alt me-1"></i>$1</a>');
                
                // Handle lists - first collect all list items
                const lines = htmlContent.split('\n');
                const processedLines = [];
                let inUnorderedList = false;
                let inOrderedList = false;
                let listIndentLevel = 0;
                
                for (let i = 0; i < lines.length; i++) {
                    const line = lines[i].trim();
                    
                    // Unordered list items
                    if (line.match(/^[-*+]\s+(.+)/)) {
                        const content = line.replace(/^[-*+]\s+/, '');
                        if (!inUnorderedList) {
                            processedLines.push('<ul class="list-styled mb-3">');
                            inUnorderedList = true;
                        }
                        processedLines.push(`<li class="mb-2">${content}</li>`);
                    }
                    // Ordered list items
                    else if (line.match(/^\d+\.\s+(.+)/)) {
                        const content = line.replace(/^\d+\.\s+/, '');
                        if (!inOrderedList) {
                            if (inUnorderedList) {
                                processedLines.push('</ul>');
                                inUnorderedList = false;
                            }
                            processedLines.push('<ol class="list-styled mb-3">');
                            inOrderedList = true;
                        }
                        processedLines.push(`<li class="mb-2">${content}</li>`);
                    }
                    // Regular content
                    else {
                        if (inUnorderedList) {
                            processedLines.push('</ul>');
                            inUnorderedList = false;
                        }
                        if (inOrderedList) {
                            processedLines.push('</ol>');
                            inOrderedList = false;
                        }
                        
                        // Handle paragraphs
                        if (line.length > 0 && !line.includes('<h') && !line.includes('__CODE_BLOCK_')) {
                            processedLines.push(`<p class="doc-paragraph mb-3 lh-lg">${line}</p>`);
                        } else if (line.includes('<h') || line.includes('__CODE_BLOCK_')) {
                            processedLines.push(line);
                        } else {
                            processedLines.push('<br>');
                        }
                    }
                }
                
                // Close any remaining lists
                if (inUnorderedList) processedLines.push('</ul>');
                if (inOrderedList) processedLines.push('</ol>');
                
                htmlContent = processedLines.join('\n');
                
                // Handle blockquotes
                htmlContent = htmlContent.replace(/^>\s*(.*$)/gim, '<blockquote class="blockquote border-start border-primary border-4 ps-3 py-2 mb-3 bg-light"><p class="mb-0">$1</p></blockquote>');
                
                // Handle horizontal rules
                htmlContent = htmlContent.replace(/^---+$/gm, '<hr class="my-4">');
                
                // Restore code blocks
                codeBlocks.forEach((block, index) => {
                    htmlContent = htmlContent.replace(`__CODE_BLOCK_${index}__`, block);
                });
                
                // Handle tables (basic support)
                htmlContent = htmlContent.replace(/\|(.+)\|/g, (match, content) => {
                    const cells = content.split('|').map(cell => cell.trim());
                    const tableCells = cells.map(cell => `<td class="px-3 py-2">${cell}</td>`).join('');
                    return `<tr>${tableCells}</tr>`;
                });
                
                if (htmlContent.includes('<tr>')) {
                    htmlContent = '<div class="table-responsive mb-4"><table class="table table-striped table-hover">' + htmlContent + '</table></div>';
                }
                
                contentDiv.innerHTML = `<div class="markdown-rendered">${htmlContent}</div>`;
            } else {
                console.error('API returned error:', data);
                throw new Error(data.error || 'Unknown error');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            contentDiv.innerHTML = `
                <div class="alert alert-danger">
                    <h4>Error loading document</h4>
                    <p>Could not load ${filename}. Error: ${error.message}</p>
                    <p><strong>Debug info:</strong></p>
                    <p>Requested URL: ../app/api/markdown_reader.php?file=${encodeURIComponent(filename)}</p>
                    <p>Please try downloading the file directly or contact support.</p>
                </div>
            `;
        });
}

// Download current document button
document.getElementById('downloadCurrentDoc').addEventListener('click', function() {
    if (currentDocUrl) {
        window.open(currentDocUrl, '_blank');
    }
});

// Download markdown file function
function downloadMarkdownFile(filename, title) {
    fetch(`../app/api/markdown_reader.php?file=${encodeURIComponent(filename)}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 200) {
                // Create a blob with the markdown content
                const blob = new Blob([data.content], { type: 'text/markdown' });
                const url = window.URL.createObjectURL(blob);
                
                // Create a temporary link and trigger download
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                
                // Clean up
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            } else {
                alert('Error downloading file: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error downloading file: ' + error.message);
        });
}

// Document search functionality
function filterDocuments() {
    const searchTerm = document.getElementById('docSearch').value.toLowerCase();
    const docItems = document.querySelectorAll('.doc-item');
    let visibleCount = 0;
    
    docItems.forEach(item => {
        const title = item.dataset.title || '';
        const description = item.dataset.description || '';
        const filename = item.dataset.filename || '';
        
        const matches = title.includes(searchTerm) || 
                       description.includes(searchTerm) || 
                       filename.includes(searchTerm);
        
        if (matches || searchTerm === '') {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    // Show/hide "no results" message
    let noResults = document.getElementById('noResultsMessage');
    if (visibleCount === 0 && searchTerm !== '') {
        if (!noResults) {
            noResults = document.createElement('div');
            noResults.id = 'noResultsMessage';
            noResults.className = 'col-12 text-center py-4';
            noResults.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-search me-2"></i>
                    No documents found matching "<strong>${searchTerm}</strong>"
                </div>
            `;
            document.getElementById('documentsGrid').appendChild(noResults);
        }
    } else if (noResults) {
        noResults.remove();
    }
}

function clearSearch() {
    document.getElementById('docSearch').value = '';
    filterDocuments();
}
</script>

<style>
/* Enhanced Markdown Content Styles */
.markdown-content {
    max-height: 70vh;
    overflow-y: auto;
    padding: 1.5rem;
    background: white;
    border-radius: 16px;
}

.markdown-rendered {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
    line-height: 1.7;
    color: #333;
    font-size: 16px;
}

/* Enhanced Typography */
.markdown-rendered .doc-h1 {
    font-size: 2.2rem;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-top: 0;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 3px solid rgba(102,126,234,0.2);
}

.markdown-rendered .doc-h2 {
    font-size: 1.8rem;
    font-weight: 600;
    color: #1a1a2e;
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.markdown-rendered .doc-h3 {
    font-size: 1.4rem;
    font-weight: 600;
    color: #667eea;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
}

.markdown-rendered .doc-h4 {
    font-size: 1.2rem;
    font-weight: 500;
    margin-top: 1.25rem;
    margin-bottom: 0.5rem;
}

.markdown-rendered .doc-h5 {
    font-size: 1.1rem;
    font-weight: 500;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}

/* Enhanced Paragraphs */
.markdown-rendered .doc-paragraph {
    font-size: 16px;
    line-height: 1.8;
    margin-bottom: 1.25rem;
    text-align: justify;
    color: #495057;
}

/* Code Styling */
.markdown-rendered .code-block {
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    overflow: hidden;
    margin: 1.5rem 0;
}

.markdown-rendered .code-header {
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
    font-size: 0.85rem;
    background: linear-gradient(135deg, #1a1a2e 0%, #2d2d44 100%);
}

.markdown-rendered pre {
    margin: 0;
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
    font-size: 0.9rem;
    line-height: 1.5;
    white-space: pre-wrap;
    word-wrap: break-word;
    background: #1a1a2e !important;
}

.markdown-rendered .inline-code {
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
    font-size: 0.9em;
    background: rgba(102,126,234,0.1) !important;
    border: 1px solid rgba(102,126,234,0.2);
    padding: 2px 8px;
    border-radius: 6px;
    color: #667eea;
}

/* List Styling */
.markdown-rendered .list-styled {
    padding-left: 1.5rem;
}

.markdown-rendered .list-styled li {
    margin-bottom: 0.75rem;
    line-height: 1.6;
    color: #495057;
}

.markdown-rendered .list-styled li::marker {
    color: #667eea;
}

/* Links */
.markdown-rendered a {
    color: #667eea;
    text-decoration: none;
    border-bottom: 1px dotted #667eea;
    transition: all 0.2s ease;
}

.markdown-rendered a:hover {
    color: #764ba2;
    border-bottom: 1px solid #764ba2;
    background: rgba(102,126,234,0.05);
    padding: 2px 4px;
    border-radius: 3px;
}

/* Blockquotes */
.markdown-rendered blockquote {
    background: linear-gradient(135deg, rgba(102,126,234,0.1) 0%, rgba(118,75,162,0.1) 100%);
    border-left: 4px solid #667eea;
    border-radius: 0 12px 12px 0;
    margin: 1.5rem 0;
    padding: 1rem 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.markdown-rendered blockquote p {
    color: #495057;
    font-style: italic;
    font-weight: 500;
    margin: 0;
}

/* Tables */
.markdown-rendered table {
    border-collapse: collapse;
    width: 100%;
    margin: 1.5rem 0;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    border-radius: 12px;
    overflow: hidden;
}

.markdown-rendered table th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    text-align: left;
    padding: 12px 16px;
}

.markdown-rendered table td {
    padding: 12px 16px;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.markdown-rendered table tr:hover {
    background-color: rgba(102,126,234,0.05);
}

/* Horizontal Rules */
.markdown-rendered hr {
    border: none;
    height: 2px;
    background: linear-gradient(90deg, transparent, #667eea, transparent);
    margin: 2rem 0;
}

/* Strong and Emphasis */
.markdown-rendered strong {
    color: #1a1a2e;
    font-weight: 600;
}

.markdown-rendered em {
    color: #495057;
    font-style: italic;
}

/* Responsive Design */
@media (max-width: 768px) {
    .markdown-rendered {
        font-size: 14px;
    }
    
    .markdown-rendered .doc-h1 {
        font-size: 1.8rem;
    }
    
    .markdown-rendered .doc-h2 {
        font-size: 1.5rem;
    }
    
    .markdown-rendered .doc-h3 {
        font-size: 1.3rem;
    }
}
</style>

<?php require_once '../core/includes/footer.php'; ?>
