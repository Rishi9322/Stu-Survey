<?php
session_start();
$pageTitle = "Help Center";
$basePath = "../";
require_once '../core/includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-12 text-center mb-5">
            <h1 class="display-4"><i class="fas fa-life-ring me-3"></i>Help Center</h1>
            <p class="lead text-muted">Find answers to frequently asked questions and get help with EduSurvey Pro</p>
        </div>
    </div>

    <!-- Breadcrumb Navigation -->
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Help Center</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Search Box -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control" placeholder="Search for help topics..." id="helpSearch">
                    </div>
                </div>
            </div>

            <!-- Quick Help Categories -->
            <div class="row mb-5">
                <div class="col-md-4 mb-3">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                            <h5>Getting Started</h5>
                            <p>Account creation, login, and first steps</p>
                            <a href="#getting-started" class="btn btn-outline-primary">View Topics</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <i class="fas fa-poll fa-3x text-success mb-3"></i>
                            <h5>Surveys & Feedback</h5>
                            <p>How to complete surveys and provide feedback</p>
                            <a href="#surveys" class="btn btn-outline-success">View Topics</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 text-center">
                        <div class="card-body">
                            <i class="fas fa-cog fa-3x text-warning mb-3"></i>
                            <h5>Technical Issues</h5>
                            <p>Troubleshooting and technical support</p>
                            <a href="#technical" class="btn btn-outline-warning">View Topics</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ Sections -->
            <section id="getting-started" class="mb-5">
                <h2 class="mb-4"><i class="fas fa-user-plus me-2"></i>Getting Started</h2>
                <div class="accordion" id="gettingStartedAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#createAccount">
                                How do I create an account?
                            </button>
                        </h2>
                        <div id="createAccount" class="accordion-collapse collapse show" data-bs-parent="#gettingStartedAccordion">
                            <div class="accordion-body">
                                <ol>
                                    <li>Click on the "Register" button on the home page</li>
                                    <li>Fill in your personal information (name, email, date of birth)</li>
                                    <li>Choose your role: Student or Teacher</li>
                                    <li>Create a secure password</li>
                                    <li>Complete the role-specific information</li>
                                    <li>Click "Register" to create your account</li>
                                </ol>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Admin accounts must be created by existing administrators.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#firstLogin">
                                What should I do after my first login?
                            </button>
                        </h2>
                        <div id="firstLogin" class="accordion-collapse collapse" data-bs-parent="#gettingStartedAccordion">
                            <div class="accordion-body">
                                <ul>
                                    <li><strong>Complete your profile:</strong> Update any missing information</li>
                                    <li><strong>Explore the dashboard:</strong> Familiarize yourself with the interface</li>
                                    <li><strong>Check available surveys:</strong> Look for surveys you can participate in</li>
                                    <li><strong>Review settings:</strong> Customize your preferences</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="surveys" class="mb-5">
                <h2 class="mb-4"><i class="fas fa-poll me-2"></i>Surveys & Feedback</h2>
                <div class="accordion" id="surveysAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#completeSurvey">
                                How do I complete a survey?
                            </button>
                        </h2>
                        <div id="completeSurvey" class="accordion-collapse collapse" data-bs-parent="#surveysAccordion">
                            <div class="accordion-body">
                                <ol>
                                    <li>Navigate to your dashboard</li>
                                    <li>Look for available surveys in the "Surveys" section</li>
                                    <li>Click on a survey to start</li>
                                    <li>Read each question carefully</li>
                                    <li>Rate or answer using the provided scale (usually 1-5)</li>
                                    <li>Add comments where optional</li>
                                    <li>Review your answers</li>
                                    <li>Click "Submit" to save your responses</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#editResponse">
                                Can I edit my survey responses?
                            </button>
                        </h2>
                        <div id="editResponse" class="accordion-collapse collapse" data-bs-parent="#surveysAccordion">
                            <div class="accordion-body">
                                <p>Once a survey is submitted, responses typically cannot be edited to maintain data integrity. However:</p>
                                <ul>
                                    <li>You can contact an administrator if you made a significant error</li>
                                    <li>Some surveys may allow multiple submissions</li>
                                    <li>Future surveys will incorporate your updated feedback</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rateTeacher">
                                How do I rate a teacher?
                            </button>
                        </h2>
                        <div id="rateTeacher" class="accordion-collapse collapse" data-bs-parent="#surveysAccordion">
                            <div class="accordion-body">
                                <p><strong>For Students:</strong></p>
                                <ol>
                                    <li>Go to your student dashboard</li>
                                    <li>Find the "Teacher Ratings" section</li>
                                    <li>Select the teacher you want to rate</li>
                                    <li>Provide a rating (1-5 stars)</li>
                                    <li>Add constructive comments</li>
                                    <li>Submit your rating</li>
                                </ol>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Please provide constructive feedback to help teachers improve.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="technical" class="mb-5">
                <h2 class="mb-4"><i class="fas fa-cog me-2"></i>Technical Issues</h2>
                <div class="accordion" id="technicalAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#loginIssues">
                                I can't log in to my account
                            </button>
                        </h2>
                        <div id="loginIssues" class="accordion-collapse collapse" data-bs-parent="#technicalAccordion">
                            <div class="accordion-body">
                                <h6>Try these solutions:</h6>
                                <ul>
                                    <li><strong>Check your credentials:</strong> Ensure username and password are correct</li>
                                    <li><strong>Check caps lock:</strong> Passwords are case-sensitive</li>
                                    <li><strong>Clear browser cache:</strong> Old data might be interfering</li>
                                    <li><strong>Try a different browser:</strong> Some browsers may have compatibility issues</li>
                                    <li><strong>Contact support:</strong> If issues persist, contact an administrator</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pageNotLoading">
                                Pages are not loading properly
                            </button>
                        </h2>
                        <div id="pageNotLoading" class="accordion-collapse collapse" data-bs-parent="#technicalAccordion">
                            <div class="accordion-body">
                                <h6>Troubleshooting steps:</h6>
                                <ol>
                                    <li><strong>Refresh the page:</strong> Press F5 or Ctrl+R</li>
                                    <li><strong>Check internet connection:</strong> Ensure you're connected to the internet</li>
                                    <li><strong>Clear browser cache and cookies</strong></li>
                                    <li><strong>Disable browser extensions</strong> temporarily</li>
                                    <li><strong>Try incognito/private mode</strong></li>
                                    <li><strong>Update your browser</strong> to the latest version</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#dataNotSaving">
                                My data is not saving
                            </button>
                        </h2>
                        <div id="dataNotSaving" class="accordion-collapse collapse" data-bs-parent="#technicalAccordion">
                            <div class="accordion-body">
                                <p>If your responses or changes aren't saving:</p>
                                <ul>
                                    <li><strong>Check your internet connection</strong></li>
                                    <li><strong>Don't navigate away</strong> while saving</li>
                                    <li><strong>Look for error messages</strong> on the page</li>
                                    <li><strong>Try submitting again</strong> after a few moments</li>
                                    <li><strong>Contact support</strong> if the problem continues</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Contact Support -->
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3><i class="fas fa-headset me-2"></i>Still Need Help?</h3>
                    <p>Our support team is here to assist you with any questions or issues.</p>
                    <div class="row">
                        <div class="col-md-6">
                            <p><i class="fas fa-envelope me-2"></i>Email: support@edusurvey.pro</p>
                        </div>
                        <div class="col-md-6">
                            <p><i class="fas fa-phone me-2"></i>Phone: +1 (555) 123-4567</p>
                        </div>
                    </div>
                    <a href="contact.php" class="btn btn-light btn-lg">
                        <i class="fas fa-paper-plane me-2"></i>Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Help search functionality
document.getElementById('helpSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const accordions = document.querySelectorAll('.accordion-item');
    
    accordions.forEach(item => {
        const text = item.textContent.toLowerCase();
        if (text.includes(searchTerm) || searchTerm === '') {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// Smooth scrolling for category links
document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', function(e) {
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
</script>

<?php require_once '../core/includes/footer.php'; ?>
