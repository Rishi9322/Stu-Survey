    </main>
    
    <!-- Modern Footer -->
    <footer class="footer-modern">
        <div class="container">
            <div class="footer-content">
                <div class="row">
                    <div class="col-lg-4 mb-4">
                        <div class="footer-brand">
                            <h3>
                                <i class="fas fa-graduation-cap me-2"></i>
                                EduSurvey Pro
                            </h3>
                            <p>Transforming education through intelligent feedback systems and comprehensive analytics. Empowering institutions to create better learning experiences.</p>
                            <div class="social-links">
                                <a href="#" class="social-link">
                                    <i class="fab fa-facebook"></i>
                                </a>
                                <a href="#" class="social-link">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="#" class="social-link">
                                    <i class="fab fa-linkedin"></i>
                                </a>
                                <a href="#" class="social-link">
                                    <i class="fab fa-instagram"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-2 col-md-6 mb-4">
                        <div class="footer-section">
                            <h4>Platform</h4>
                            <ul class="footer-links">
                                <li><a href="<?php echo $basePath; ?>public/index.php">Home</a></li>
                                <li><a href="<?php echo $basePath; ?>public/login.php">Login</a></li>
                                <li><a href="<?php echo $basePath; ?>public/register.php">Register</a></li>
                                <li><a href="#" onclick="alert('Feature coming soon!')">Features</a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="col-lg-2 col-md-6 mb-4">
                        <div class="footer-section">
                            <h4>Resources</h4>
                            <ul class="footer-links">
                                <li><a href="<?php echo $basePath; ?>public/documentation.php">Documentation</a></li>
                                <li><a href="<?php echo $basePath; ?>app/api/api.php">API Reference</a></li>
                                <li><a href="<?php echo $basePath; ?>public/help.php">Help Center</a></li>
                                <li><a href="<?php echo $basePath; ?>public/documentation.php#tutorials">Tutorials</a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="col-lg-2 col-md-6 mb-4">
                        <div class="footer-section">
                            <h4>Company</h4>
                            <ul class="footer-links">
                                <li><a href="<?php echo $basePath; ?>public/about.php">About Us</a></li>
                                <li><a href="<?php echo $basePath; ?>public/privacy.php">Privacy Policy</a></li>
                                <li><a href="<?php echo $basePath; ?>public/terms.php">Terms of Service</a></li>
                                <li><a href="<?php echo $basePath; ?>public/contact.php">Contact</a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="col-lg-2 col-md-6 mb-4">
                        <div class="footer-section">
                            <h4>Contact Info</h4>
                            <div class="contact-info">
                                <div class="contact-item">
                                    <i class="fas fa-envelope me-2"></i>
                                    <span>info@edusurvey.pro</span>
                                </div>
                                <div class="contact-item">
                                    <i class="fas fa-phone me-2"></i>
                                    <span>+1 (555) 123-4567</span>
                                </div>
                                <div class="contact-item">
                                    <i class="fas fa-map-marker-alt me-2"></i>
                                    <span>123 Education St, Learning City</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <p>&copy; <?php echo date('Y'); ?> EduSurvey Pro. All rights reserved.</p>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="college-selector">
                            <label for="collegeSelect" class="text-light me-2">Select College:</label>
                            <select id="collegeSelect" class="form-select form-select-sm d-inline-block" style="width: auto;">
                                <option value="">Choose College</option>
                                <option value="Clone_tcsc/clone_pages/www.tcsc.edu.in/index.html">TCSC</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <p>Made with <i class="fas fa-heart text-danger"></i> for Education</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap JS (Latest) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS (Bootstrap 5 compatible) -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="<?php echo $basePath; ?>assets/js/script.js"></script>
    
    <!-- Header Scroll Effect -->
    <script>
        // Modern header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.getElementById('header');
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
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
        
        // College selection dropdown functionality
        const collegeSelect = document.getElementById('collegeSelect');
        if (collegeSelect) {
            collegeSelect.addEventListener('change', function() {
                if (this.value) {
                    window.location.href = this.value;
                }
            });
        }
    </script>
</body>
</html>
