<?php
session_start();
$pageTitle = "Contact Us";
$basePath = "../";
require_once '../core/includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-12 text-center mb-5">
            <h1 class="display-4">
                <i class="fas fa-phone me-3 text-primary"></i>Contact Us
            </h1>
            <p class="lead text-muted">We're here to help! Get in touch with our team</p>
        </div>
    </div>

    <!-- Breadcrumb Navigation -->
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Contact Us</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <!-- Contact Information -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-info-circle me-2"></i>Get in Touch</h4>
                </div>
                <div class="card-body">
                    <div class="contact-info">
                        <div class="mb-4">
                            <h5><i class="fas fa-envelope text-primary me-2"></i>Email</h5>
                            <p class="mb-1">General Inquiries: <br><strong>info@edusurvey.pro</strong></p>
                            <p class="mb-1">Support: <br><strong>support@edusurvey.pro</strong></p>
                            <p>Sales: <br><strong>sales@edusurvey.pro</strong></p>
                        </div>
                        
                        <div class="mb-4">
                            <h5><i class="fas fa-phone text-success me-2"></i>Phone</h5>
                            <p class="mb-1">Main Office: <br><strong>+1 (555) 123-4567</strong></p>
                            <p class="mb-1">Support Line: <br><strong>+1 (555) 123-4568</strong></p>
                            <p class="small text-muted">Mon-Fri 9:00 AM - 6:00 PM EST</p>
                        </div>
                        
                        <div class="mb-4">
                            <h5><i class="fas fa-map-marker-alt text-danger me-2"></i>Address</h5>
                            <p>
                                EduSurvey Pro Headquarters<br>
                                123 Education Street<br>
                                Learning City, LC 12345<br>
                                United States
                            </p>
                        </div>

                        <div>
                            <h5><i class="fas fa-clock text-warning me-2"></i>Business Hours</h5>
                            <p class="mb-1">Monday - Friday: 9:00 AM - 6:00 PM</p>
                            <p class="mb-1">Saturday: 10:00 AM - 2:00 PM</p>
                            <p>Sunday: Closed</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Send us a Message</h4>
                </div>
                <div class="card-body">
                    <form id="contactForm" method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstName" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="firstName" name="firstName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lastName" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="lastName" name="lastName" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="organization" class="form-label">Organization/Institution</label>
                            <input type="text" class="form-control" id="organization" name="organization" placeholder="Your school, college, or organization">
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject *</label>
                            <select class="form-select" id="subject" name="subject" required>
                                <option value="">Select a topic</option>
                                <option value="general">General Inquiry</option>
                                <option value="support">Technical Support</option>
                                <option value="sales">Sales & Pricing</option>
                                <option value="demo">Request Demo</option>
                                <option value="feature">Feature Request</option>
                                <option value="partnership">Partnership Opportunity</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Message *</label>
                            <textarea class="form-control" id="message" name="message" rows="5" placeholder="Please provide details about your inquiry..." required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="priority" class="form-label">Priority Level</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="low">Low - General inquiry</option>
                                <option value="medium" selected>Medium - Standard request</option>
                                <option value="high">High - Urgent issue</option>
                                <option value="critical">Critical - System down</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter">
                                <label class="form-check-label" for="newsletter">
                                    Subscribe to our newsletter for updates and educational insights
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="reset" class="btn btn-outline-secondary me-md-2">
                                <i class="fas fa-undo me-1"></i>Clear Form
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-paper-plane me-1"></i>Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0"><i class="fas fa-question-circle me-2"></i>Frequently Asked Questions</h4>
                </div>
                <div class="card-body">
                    <div class="accordion" id="contactFAQ">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    How quickly will I receive a response?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#contactFAQ">
                                <div class="accordion-body">
                                    We aim to respond to all inquiries within 24 hours during business days. Critical issues are prioritized and typically addressed within 4 hours.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Can I schedule a demo?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#contactFAQ">
                                <div class="accordion-body">
                                    Absolutely! Select "Request Demo" from the subject dropdown and include your preferred times. We'll schedule a personalized demonstration of EduSurvey Pro's features.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Do you offer phone support?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#contactFAQ">
                                <div class="accordion-body">
                                    Yes! Phone support is available for existing customers during business hours. New customers can also call for sales inquiries and general questions.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 11">
    <div id="successToast" class="toast" role="alert">
        <div class="toast-header bg-success text-white">
            <i class="fas fa-check-circle me-2"></i>
            <strong class="me-auto">Success</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            Your message has been sent successfully! We'll get back to you soon.
        </div>
    </div>
    
    <div id="errorToast" class="toast" role="alert">
        <div class="toast-header bg-danger text-white">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong class="me-auto">Error</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            There was an error sending your message. Please try again or contact us directly.
        </div>
    </div>
</div>

<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get form data
    const formData = new FormData(this);
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending...';
    submitBtn.disabled = true;
    
    // Simulate form submission (replace with actual submission logic)
    setTimeout(() => {
        // Show success message
        const successToast = new bootstrap.Toast(document.getElementById('successToast'));
        successToast.show();
        
        // Reset form
        this.reset();
        
        // Restore button
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        
        // In a real implementation, you would send the data to a server endpoint
        console.log('Form data would be sent to server:', Object.fromEntries(formData));
    }, 2000);
});

// Character counter for message field
const messageField = document.getElementById('message');
const messageContainer = messageField.parentNode;

messageField.addEventListener('input', function() {
    const maxLength = 1000;
    const currentLength = this.value.length;
    
    let counter = messageContainer.querySelector('.char-counter');
    if (!counter) {
        counter = document.createElement('small');
        counter.className = 'char-counter text-muted';
        messageContainer.appendChild(counter);
    }
    
    counter.textContent = `${currentLength}/${maxLength} characters`;
    
    if (currentLength > maxLength) {
        counter.classList.add('text-danger');
        counter.classList.remove('text-muted');
    } else {
        counter.classList.add('text-muted');
        counter.classList.remove('text-danger');
    }
});
</script>

<?php require_once '../core/includes/footer.php'; ?>
