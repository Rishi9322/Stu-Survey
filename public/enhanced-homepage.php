<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Satisfaction Survey System - Future Ready</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Exo+2:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg-primary: #0a0a0a;
            --bg-secondary: #1a1a1a;
            --bg-tertiary: #2a2a2a;
            --accent-blue: #00d4ff;
            --accent-purple: #a855f7;
            --accent-green: #00ff94;
            --text-primary: #ffffff;
            --text-secondary: #b0b0b0;
            --text-tertiary: #808080;
            --glow-blue: 0 0 20px rgba(0, 212, 255, 0.8);
            --glow-purple: 0 0 20px rgba(168, 85, 247, 0.8);
            --glow-green: 0 0 20px rgba(0, 255, 148, 0.8);
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        .light-mode {
            --bg-primary: #f8fafc;
            --bg-secondary: #e2e8f0;
            --bg-tertiary: #cbd5e1;
            --accent-blue: #0ea5e9;
            --accent-purple: #8b5cf6;
            --accent-green: #10b981;
            --text-primary: #1e293b;
            --text-secondary: #475569;
            --text-tertiary: #64748b;
            --glow-blue: 0 0 20px rgba(14, 165, 233, 0.6);
            --glow-purple: 0 0 20px rgba(139, 92, 246, 0.6);
            --glow-green: 0 0 20px rgba(16, 185, 129, 0.6);
            --glass-bg: rgba(0, 0, 0, 0.03);
            --glass-border: rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Exo 2', sans-serif;
            background: radial-gradient(ellipse at center, var(--bg-secondary) 0%, var(--bg-primary) 100%);
            color: var(--text-primary);
            overflow-x: hidden;
            scroll-behavior: smooth;
            transition: all 0.3s ease;
        }

        /* Navigation */
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
            transition: all 0.3s ease;
            padding: 1rem 0;
        }

        .navbar.scrolled {
            background: rgba(10, 10, 10, 0.95);
            backdrop-filter: blur(30px);
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo {
            font-family: 'Orbitron', monospace;
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--accent-blue);
            text-shadow: var(--glow-blue);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .logo:hover {
            color: var(--accent-purple);
            text-shadow: var(--glow-purple);
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        .nav-links a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            font-weight: 500;
        }

        .nav-links a:hover {
            color: var(--accent-blue);
            text-shadow: var(--glow-blue);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--accent-blue);
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .theme-toggle {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            color: var(--text-primary);
            padding: 0.5rem;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .theme-toggle:hover {
            background: var(--accent-blue);
            color: var(--bg-primary);
            box-shadow: var(--glow-blue);
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding-top: 5rem;
        }

        .hero-content {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            padding: 0 2rem;
        }

        .hero-text {
            z-index: 10;
        }

        .hero-title {
            font-family: 'Orbitron', monospace;
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 900;
            line-height: 1.2;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple), var(--accent-green));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: glow-pulse 3s ease-in-out infinite alternate;
        }

        .hero-subtitle {
            font-size: 1.5rem;
            color: var(--text-secondary);
            margin-bottom: 2rem;
            font-weight: 300;
            opacity: 0;
            animation: fadeInUp 1s ease 0.5s forwards;
        }

        .hero-description {
            font-size: 1.1rem;
            color: var(--text-tertiary);
            margin-bottom: 3rem;
            line-height: 1.6;
            opacity: 0;
            animation: fadeInUp 1s ease 1s forwards;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple));
            color: white;
            box-shadow: var(--glow-blue);
        }

        .btn-secondary {
            background: var(--glass-bg);
            color: var(--text-primary);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(10px);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 212, 255, 0.4);
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        /* 3D Canvas */
        .canvas-container {
            position: relative;
            height: 600px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #threejs-canvas {
            border-radius: 20px;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        }

        /* Features Section */
        .features {
            padding: 8rem 2rem;
            background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
        }

        .section-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-title {
            font-family: 'Orbitron', monospace;
            font-size: clamp(2rem, 4vw, 3rem);
            text-align: center;
            margin-bottom: 3rem;
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 4rem;
        }

        .feature-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 212, 255, 0.1), transparent);
            transition: left 0.5s;
        }

        .feature-card:hover::before {
            left: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 212, 255, 0.3);
            border-color: var(--accent-blue);
        }

        .feature-icon {
            font-size: 3rem;
            color: var(--accent-blue);
            margin-bottom: 1rem;
            display: inline-block;
            text-shadow: var(--glow-blue);
        }

        .feature-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-primary);
        }

        .feature-description {
            color: var(--text-secondary);
            line-height: 1.6;
        }

        /* Statistics Section */
        .statistics {
            padding: 8rem 2rem;
            background: var(--bg-primary);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-top: 4rem;
        }

        .stat-card {
            text-align: center;
            padding: 2rem;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: scale(1.05);
            box-shadow: var(--glow-purple);
        }

        .stat-number {
            font-family: 'Orbitron', monospace;
            font-size: 3rem;
            font-weight: 900;
            color: var(--accent-purple);
            text-shadow: var(--glow-purple);
            display: block;
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin-top: 0.5rem;
        }

        /* Testimonials Section */
        .testimonials {
            padding: 8rem 2rem;
            background: linear-gradient(135deg, var(--bg-tertiary) 0%, var(--bg-secondary) 100%);
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 4rem;
        }

        .testimonial-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 2rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--glow-green);
        }

        .testimonial-text {
            font-style: italic;
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
            line-height: 1.6;
            font-size: 1.1rem;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .author-info h4 {
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .author-info span {
            color: var(--text-tertiary);
            font-size: 0.9rem;
        }

        /* Services Section */
        .services {
            padding: 8rem 2rem;
            background: var(--bg-primary);
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 3rem;
            margin-top: 4rem;
        }

        .service-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 3rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .service-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, var(--accent-green), transparent);
            opacity: 0.1;
            transition: all 0.3s ease;
        }

        .service-card:hover::after {
            width: 200px;
            height: 200px;
            opacity: 0.2;
        }

        .service-card:hover {
            transform: translateY(-10px);
            border-color: var(--accent-green);
            box-shadow: var(--glow-green);
        }

        .service-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .service-icon {
            font-size: 2.5rem;
            color: var(--accent-green);
            text-shadow: var(--glow-green);
        }

        .service-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .service-description {
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .service-features {
            list-style: none;
        }

        .service-features li {
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            padding-left: 1.5rem;
            position: relative;
        }

        .service-features li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--accent-green);
            font-weight: bold;
        }

        /* Footer */
        .footer {
            background: var(--bg-tertiary);
            padding: 4rem 2rem 2rem;
            border-top: 1px solid var(--glass-border);
        }

        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
        }

        .footer-section h3 {
            color: var(--text-primary);
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .footer-section p, .footer-section a {
            color: var(--text-secondary);
            text-decoration: none;
            line-height: 1.6;
        }

        .footer-section a:hover {
            color: var(--accent-blue);
        }

        .footer-bottom {
            text-align: center;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid var(--glass-border);
            color: var(--text-tertiary);
        }

        /* Scroll Animations */
        .scroll-animate {
            opacity: 0;
            transform: translateY(50px);
            transition: all 0.6s ease;
        }

        .scroll-animate.animated {
            opacity: 1;
            transform: translateY(0);
        }

        /* Floating Elements */
        .floating {
            animation: floating 6s ease-in-out infinite;
        }

        .floating-delayed {
            animation: floating 6s ease-in-out infinite;
            animation-delay: -2s;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-content {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .nav-links {
                display: none;
            }

            .canvas-container {
                height: 400px;
            }

            .cta-buttons {
                justify-content: center;
            }
        }

        /* Keyframes */
        @keyframes glow-pulse {
            0% { filter: brightness(1) drop-shadow(0 0 5px currentColor); }
            100% { filter: brightness(1.2) drop-shadow(0 0 20px currentColor); }
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes floating {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(2deg); }
        }

        @keyframes rotate {
            0% { transform: rotateY(0deg); }
            100% { transform: rotateY(360deg); }
        }

        /* Scroll Progress Bar */
        .scroll-progress {
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 3px;
            background: linear-gradient(90deg, var(--accent-blue), var(--accent-purple), var(--accent-green));
            z-index: 9999;
            transition: width 0.25s ease;
        }
    </style>
</head>
<body>
    <!-- Scroll Progress Bar -->
    <div class="scroll-progress" id="scrollProgress"></div>

    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <a href="#" class="logo">SURVEY SYSTEM</a>
            <ul class="nav-links">
                <li><a href="../index.php">Home</a></li>
                <li><a href="../login.php">Login</a></li>
                <li><a href="../register.php">Register</a></li>
                <li><a href="../documentation.php">Documentation</a></li>
                <li><a href="../about.php">About</a></li>
                <li><a href="../contact.php">Contact</a></li>
            </ul>
            <button class="theme-toggle" id="themeToggle">
                <i class="fas fa-moon"></i>
            </button>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">Next-Gen Survey Experience</h1>
                <p class="hero-subtitle">AI-Powered Student Satisfaction Analytics</p>
                <p class="hero-description">
                    Transform the way you collect, analyze, and visualize student feedback with our cutting-edge 
                    survey platform. Powered by advanced AI algorithms and featuring an intuitive 3D interface 
                    that makes data exploration engaging and insightful.
                </p>
                <div class="cta-buttons">
                    <a href="../login.php" class="btn btn-primary">
                        <i class="fas fa-rocket"></i>
                        Get Started
                    </a>
                    <a href="../register.php" class="btn btn-secondary">
                        <i class="fas fa-user-plus"></i>
                        Sign Up Free
                    </a>
                    <a href="../documentation.php" class="btn btn-secondary">
                        <i class="fas fa-book"></i>
                        Documentation
                    </a>
                </div>
            </div>
            <div class="canvas-container floating">
                <canvas id="threejs-canvas"></canvas>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features scroll-animate">
        <div class="section-container">
            <h2 class="section-title">Powerful Features</h2>
            <div class="features-grid">
                <div class="feature-card floating">
                    <i class="fas fa-brain feature-icon"></i>
                    <h3 class="feature-title">AI-Powered Analytics</h3>
                    <p class="feature-description">
                        Advanced machine learning algorithms analyze survey responses to provide deep insights 
                        into student satisfaction patterns and trends.
                    </p>
                </div>
                <div class="feature-card floating-delayed">
                    <i class="fas fa-cube feature-icon"></i>
                    <h3 class="feature-title">3D Data Visualization</h3>
                    <p class="feature-description">
                        Immersive 3D charts and graphs make complex data easy to understand and explore 
                        with interactive visualizations.
                    </p>
                </div>
                <div class="feature-card floating">
                    <i class="fas fa-mobile-alt feature-icon"></i>
                    <h3 class="feature-title">Mobile-First Design</h3>
                    <p class="feature-description">
                        Responsive design ensures perfect functionality across all devices, from smartphones 
                        to desktop computers.
                    </p>
                </div>
                <div class="feature-card floating-delayed">
                    <i class="fas fa-shield-alt feature-icon"></i>
                    <h3 class="feature-title">Enterprise Security</h3>
                    <p class="feature-description">
                        Bank-level encryption and security protocols protect sensitive student data and 
                        survey responses.
                    </p>
                </div>
                <div class="feature-card floating">
                    <i class="fas fa-clock feature-icon"></i>
                    <h3 class="feature-title">Real-Time Updates</h3>
                    <p class="feature-description">
                        Live dashboard updates show survey results as they come in, with instant 
                        notifications and alerts.
                    </p>
                </div>
                <div class="feature-card floating-delayed">
                    <i class="fas fa-chart-line feature-icon"></i>
                    <h3 class="feature-title">Advanced Reporting</h3>
                    <p class="feature-description">
                        Generate comprehensive reports with custom filters, export options, and 
                        automated insights.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="statistics scroll-animate">
        <div class="section-container">
            <h2 class="section-title">Platform Statistics</h2>
            <div class="stats-grid">
                <div class="stat-card floating">
                    <span class="stat-number">50,000+</span>
                    <span class="stat-label">Active Students</span>
                </div>
                <div class="stat-card floating-delayed">
                    <span class="stat-number">1,200+</span>
                    <span class="stat-label">Educational Institutions</span>
                </div>
                <div class="stat-card floating">
                    <span class="stat-number">2.5M+</span>
                    <span class="stat-label">Surveys Completed</span>
                </div>
                <div class="stat-card floating-delayed">
                    <span class="stat-number">99.9%</span>
                    <span class="stat-label">Uptime Reliability</span>
                </div>
                <div class="stat-card floating">
                    <span class="stat-number">4.9/5</span>
                    <span class="stat-label">User Satisfaction</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials scroll-animate">
        <div class="section-container">
            <h2 class="section-title">What Our Users Say</h2>
            <div class="testimonials-grid">
                <div class="testimonial-card floating">
                    <p class="testimonial-text">
                        "The 3D visualizations completely transformed how we interpret student feedback. 
                        The insights are incredible and the interface is so intuitive!"
                    </p>
                    <div class="testimonial-author">
                        <div class="author-avatar">DR</div>
                        <div class="author-info">
                            <h4>Dr. Sarah Johnson</h4>
                            <span>Dean of Academic Affairs, MIT</span>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card floating-delayed">
                    <p class="testimonial-text">
                        "Implementation was seamless and the AI-powered analytics helped us identify 
                        improvement areas we never knew existed. Outstanding platform!"
                    </p>
                    <div class="testimonial-author">
                        <div class="author-avatar">MR</div>
                        <div class="author-info">
                            <h4>Prof. Michael Rodriguez</h4>
                            <span>Head of Student Services, Stanford</span>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card floating">
                    <p class="testimonial-text">
                        "The real-time dashboard and mobile-friendly design made it easy for our students 
                        to participate. Response rates increased by 300%!"
                    </p>
                    <div class="testimonial-author">
                        <div class="author-avatar">EW</div>
                        <div class="author-info">
                            <h4>Dr. Emily Wong</h4>
                            <span>Research Director, UC Berkeley</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services scroll-animate">
        <div class="section-container">
            <h2 class="section-title">Our Services</h2>
            <div class="services-grid">
                <div class="service-card floating">
                    <div class="service-header">
                        <i class="fas fa-poll service-icon"></i>
                        <h3 class="service-title">Survey Creation</h3>
                    </div>
                    <p class="service-description">
                        Build sophisticated surveys with our drag-and-drop interface. Multiple question types, 
                        branching logic, and AI-powered question suggestions.
                    </p>
                    <ul class="service-features">
                        <li>Advanced question types and logic</li>
                        <li>AI-powered question optimization</li>
                        <li>Multi-language support</li>
                        <li>Custom branding and themes</li>
                        <li>Mobile-optimized templates</li>
                    </ul>
                </div>
                <div class="service-card floating-delayed">
                    <div class="service-header">
                        <i class="fas fa-chart-bar service-icon"></i>
                        <h3 class="service-title">Data Analytics</h3>
                    </div>
                    <p class="service-description">
                        Transform raw survey data into actionable insights with our advanced analytics engine. 
                        Machine learning algorithms identify patterns and trends automatically.
                    </p>
                    <ul class="service-features">
                        <li>Real-time analytics dashboard</li>
                        <li>Predictive trend analysis</li>
                        <li>Custom report generation</li>
                        <li>Data export in multiple formats</li>
                        <li>Automated insight generation</li>
                    </ul>
                </div>
                <div class="service-card floating">
                    <div class="service-header">
                        <i class="fas fa-users service-icon"></i>
                        <h3 class="service-title">Team Collaboration</h3>
                    </div>
                    <p class="service-description">
                        Collaborate seamlessly with team members, share insights, and manage permissions. 
                        Built-in communication tools and workflow management.
                    </p>
                    <ul class="service-features">
                        <li>Multi-user collaboration</li>
                        <li>Role-based access control</li>
                        <li>Comment and annotation system</li>
                        <li>Task assignment and tracking</li>
                        <li>Integration with popular tools</li>
                    </ul>
                </div>
                <div class="service-card floating-delayed">
                    <div class="service-header">
                        <i class="fas fa-cogs service-icon"></i>
                        <h3 class="service-title">System Integration</h3>
                    </div>
                    <p class="service-description">
                        Seamlessly integrate with your existing systems and workflows. API access, 
                        SSO authentication, and custom integration solutions.
                    </p>
                    <ul class="service-features">
                        <li>RESTful API access</li>
                        <li>Single Sign-On (SSO) support</li>
                        <li>Webhook notifications</li>
                        <li>Custom integration development</li>
                        <li>Enterprise-grade security</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Student Satisfaction Survey System</h3>
                <p>Revolutionizing how educational institutions collect and analyze student feedback through AI-powered tools and immersive 3D visualizations.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <p><a href="../index.php">Home</a></p>
                <p><a href="../about.php">About Us</a></p>
                <p><a href="../contact.php">Contact</a></p>
                <p><a href="../help.php">Help Center</a></p>
            </div>
            <div class="footer-section">
                <h3>Legal</h3>
                <p><a href="../privacy.php">Privacy Policy</a></p>
                <p><a href="../terms.php">Terms of Service</a></p>
                <p><a href="../documentation.php">Documentation</a></p>
            </div>
            <div class="footer-section">
                <h3>Connect</h3>
                <p>Follow us for updates and insights</p>
                <p>Email: support@surveysystem.edu</p>
                <p>Phone: +1 (555) 123-4567</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Student Satisfaction Survey System. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;
        const icon = themeToggle.querySelector('i');

        // Load saved theme
        if (localStorage.getItem('theme') === 'light') {
            body.classList.add('light-mode');
            icon.className = 'fas fa-sun';
        }

        themeToggle.addEventListener('click', () => {
            body.classList.toggle('light-mode');
            const isLight = body.classList.contains('light-mode');
            icon.className = isLight ? 'fas fa-sun' : 'fas fa-moon';
            localStorage.setItem('theme', isLight ? 'light' : 'dark');
        });

        // Navbar scroll effect
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 100) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Scroll progress
        const scrollProgress = document.getElementById('scrollProgress');
        window.addEventListener('scroll', () => {
            const totalHeight = document.documentElement.scrollHeight - window.innerHeight;
            const progress = (window.scrollY / totalHeight) * 100;
            scrollProgress.style.width = progress + '%';
        });

        // Scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.scroll-animate').forEach(el => {
            observer.observe(el);
        });

        // 3D Scene Setup
        let scene, camera, renderer, laptop, animationId;
        let mouseX = 0, mouseY = 0;
        let targetRotationX = 0, targetRotationY = 0;

        function init3D() {
            const canvas = document.getElementById('threejs-canvas');
            const container = canvas.parentElement;
            
            // Scene setup
            scene = new THREE.Scene();
            camera = new THREE.PerspectiveCamera(75, container.offsetWidth / container.offsetHeight, 0.1, 1000);
            renderer = new THREE.WebGLRenderer({ 
                canvas: canvas, 
                antialias: true, 
                alpha: true 
            });
            
            renderer.setSize(container.offsetWidth, container.offsetHeight);
            renderer.setClearColor(0x000000, 0);
            renderer.shadowMap.enabled = true;
            renderer.shadowMap.type = THREE.PCFSoftShadowMap;

            // Lighting
            const ambientLight = new THREE.AmbientLight(0x404040, 0.6);
            scene.add(ambientLight);

            const directionalLight = new THREE.DirectionalLight(0x00d4ff, 1);
            directionalLight.position.set(5, 5, 5);
            directionalLight.castShadow = true;
            scene.add(directionalLight);

            const spotLight = new THREE.SpotLight(0xa855f7, 0.8);
            spotLight.position.set(-5, 5, 0);
            scene.add(spotLight);

            // Create procedural laptop
            createLaptop();

            // Camera position
            camera.position.set(0, 2, 8);
            camera.lookAt(0, 0, 0);

            // Mouse interaction
            canvas.addEventListener('mousemove', onMouseMove, false);

            // Start animation
            animate();
        }

        function createLaptop() {
            laptop = new THREE.Group();

            // Laptop base
            const baseGeometry = new THREE.BoxGeometry(4, 0.3, 3);
            const baseMaterial = new THREE.MeshPhongMaterial({ 
                color: 0x2a2a2a,
                shininess: 100 
            });
            const base = new THREE.Mesh(baseGeometry, baseMaterial);
            base.castShadow = true;
            base.receiveShadow = true;
            laptop.add(base);

            // Laptop screen
            const screenGeometry = new THREE.BoxGeometry(3.8, 2.5, 0.1);
            const screenMaterial = new THREE.MeshPhongMaterial({ 
                color: 0x1a1a1a,
                shininess: 100 
            });
            const screen = new THREE.Mesh(screenGeometry, screenMaterial);
            screen.position.set(0, 1.4, -1.45);
            screen.rotation.x = -0.1;
            screen.castShadow = true;
            laptop.add(screen);

            // Screen content (glowing effect)
            const screenContentGeometry = new THREE.PlaneGeometry(3.5, 2.2);
            const screenContentMaterial = new THREE.MeshBasicMaterial({ 
                color: 0x00d4ff,
                transparent: true,
                opacity: 0.8
            });
            const screenContent = new THREE.Mesh(screenContentGeometry, screenContentMaterial);
            screenContent.position.set(0, 1.4, -1.4);
            screenContent.rotation.x = -0.1;
            laptop.add(screenContent);

            // Keyboard
            const keyboardGeometry = new THREE.BoxGeometry(3.5, 0.05, 2.5);
            const keyboardMaterial = new THREE.MeshPhongMaterial({ 
                color: 0x333333 
            });
            const keyboard = new THREE.Mesh(keyboardGeometry, keyboardMaterial);
            keyboard.position.set(0, 0.175, 0);
            laptop.add(keyboard);

            // Add some keys for detail
            for (let i = 0; i < 50; i++) {
                const keyGeometry = new THREE.BoxGeometry(0.15, 0.03, 0.15);
                const keyMaterial = new THREE.MeshPhongMaterial({ 
                    color: 0x555555 
                });
                const key = new THREE.Mesh(keyGeometry, keyMaterial);
                key.position.set(
                    (Math.random() - 0.5) * 3.2,
                    0.215,
                    (Math.random() - 0.5) * 2.2
                );
                laptop.add(key);
            }

            // Touchpad
            const touchpadGeometry = new THREE.BoxGeometry(1, 0.02, 0.8);
            const touchpadMaterial = new THREE.MeshPhongMaterial({ 
                color: 0x444444 
            });
            const touchpad = new THREE.Mesh(touchpadGeometry, touchpadMaterial);
            touchpad.position.set(0, 0.19, 0.8);
            laptop.add(touchpad);

            scene.add(laptop);
        }

        function onMouseMove(event) {
            const rect = event.target.getBoundingClientRect();
            mouseX = ((event.clientX - rect.left) / rect.width) * 2 - 1;
            mouseY = -((event.clientY - rect.top) / rect.height) * 2 + 1;

            targetRotationY = mouseX * 0.3;
            targetRotationX = mouseY * 0.2;
        }

        function animate() {
            animationId = requestAnimationFrame(animate);

            if (laptop) {
                // Smooth rotation based on mouse
                laptop.rotation.y += (targetRotationY - laptop.rotation.y) * 0.05;
                laptop.rotation.x += (targetRotationX - laptop.rotation.x) * 0.05;

                // Add floating animation
                laptop.position.y = Math.sin(Date.now() * 0.001) * 0.2;
                
                // Add subtle rotation animation
                laptop.rotation.y += 0.005;

                // Scroll-based transformations
                const scrollPercent = window.scrollY / (document.documentElement.scrollHeight - window.innerHeight);
                laptop.rotation.z = scrollPercent * Math.PI * 0.1;
                laptop.scale.setScalar(1 + scrollPercent * 0.2);
            }

            renderer.render(scene, camera);
        }

        // Handle window resize
        window.addEventListener('resize', () => {
            const container = document.querySelector('.canvas-container');
            camera.aspect = container.offsetWidth / container.offsetHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(container.offsetWidth, container.offsetHeight);
        });

        // Enhanced scroll effects
        window.addEventListener('scroll', () => {
            const scrollY = window.scrollY;
            const scrollPercent = scrollY / (document.documentElement.scrollHeight - window.innerHeight);

            // Parallax effect on floating elements
            document.querySelectorAll('.floating').forEach((element, index) => {
                const speed = 0.5 + index * 0.1;
                element.style.transform = `translateY(${scrollY * speed * 0.1}px) rotate(${scrollPercent * 2}deg)`;
            });

            // 3D model scroll interactions
            if (laptop) {
                laptop.position.x = Math.sin(scrollPercent * Math.PI * 2) * 0.5;
                laptop.rotation.z = scrollPercent * Math.PI * 0.2;
                
                // Screen glow effect based on scroll
                const screenContent = laptop.children.find(child => child.material && child.material.color.getHex() === 0x00d4ff);
                if (screenContent) {
                    screenContent.material.opacity = 0.5 + Math.sin(scrollPercent * Math.PI * 4) * 0.3;
                }
            }

            // Content animations
            document.querySelectorAll('.feature-card, .stat-card, .testimonial-card, .service-card').forEach((card, index) => {
                const rect = card.getBoundingClientRect();
                const isVisible = rect.top < window.innerHeight && rect.bottom > 0;
                
                if (isVisible) {
                    const progress = 1 - (rect.top / window.innerHeight);
                    card.style.transform = `translateY(${(1 - progress) * 20}px) rotate(${progress * 2 - 1}deg) scale(${0.95 + progress * 0.05})`;
                    card.style.opacity = Math.max(0.1, progress);
                }
            });
        });

        // Counter animation
        function animateCounters() {
            document.querySelectorAll('.stat-number').forEach(counter => {
                const target = parseInt(counter.textContent.replace(/[^\d]/g, ''));
                const increment = target / 100;
                let current = 0;
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    
                    if (counter.textContent.includes('%')) {
                        counter.textContent = Math.floor(current) + '%';
                    } else if (counter.textContent.includes('M')) {
                        counter.textContent = (current / 1000000).toFixed(1) + 'M+';
                    } else if (counter.textContent.includes('K') || target >= 1000) {
                        counter.textContent = (current / 1000).toFixed(target >= 10000 ? 0 : 1) + 'K+';
                    } else if (counter.textContent.includes('.')) {
                        counter.textContent = (current / 10).toFixed(1) + '/5';
                    } else {
                        counter.textContent = Math.floor(current).toLocaleString() + '+';
                    }
                }, 50);
            });
        }

        // Observe stat cards for counter animation
        const statObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
                    entry.target.classList.add('counted');
                    setTimeout(animateCounters, 200);
                }
            });
        }, { threshold: 0.5 });

        document.querySelectorAll('.stats-grid').forEach(grid => {
            statObserver.observe(grid);
        });

        // Initialize 3D scene when page loads
        window.addEventListener('load', () => {
            setTimeout(init3D, 100);
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (animationId) {
                cancelAnimationFrame(animationId);
            }
        });
    </script>
</body>
</html>