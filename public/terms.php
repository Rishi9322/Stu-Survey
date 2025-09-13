<?php
session_start();
$pageTitle = "Terms of Service";
$basePath = "../";
require_once '../core/includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h1 class="mb-0"><i class="fas fa-file-contract me-2"></i>Terms of Service</h1>
                </div>
                <div class="card-body">
                    <p class="lead">Welcome to EduSurvey Pro. These Terms of Service govern your use of our platform and services.</p>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Effective Date:</strong> <?php echo date('F j, Y'); ?> | 
                        <strong>Last Updated:</strong> <?php echo date('F j, Y'); ?>
                    </div>

                    <h3>1. Acceptance of Terms</h3>
                    <p>By accessing or using EduSurvey Pro ("Service"), you agree to be bound by these Terms of Service ("Terms"). If you disagree with any part of these terms, you may not access the Service.</p>

                    <h3>2. Description of Service</h3>
                    <p>EduSurvey Pro provides:</p>
                    <ul>
                        <li>Educational survey and feedback management platform</li>
                        <li>Analytics and reporting tools for educational institutions</li>
                        <li>AI-powered insights and recommendations</li>
                        <li>User management and administrative features</li>
                        <li>Data visualization and export capabilities</li>
                    </ul>

                    <h3>3. User Accounts and Registration</h3>
                    <h5>Account Creation</h5>
                    <ul>
                        <li>You must provide accurate and complete information during registration</li>
                        <li>You are responsible for maintaining the security of your account credentials</li>
                        <li>You must notify us immediately of any unauthorized access</li>
                        <li>One person may not maintain multiple accounts</li>
                    </ul>

                    <h5>Account Types</h5>
                    <ul>
                        <li><strong>Student Accounts:</strong> For completing surveys and viewing personal analytics</li>
                        <li><strong>Teacher Accounts:</strong> For accessing feedback and student interaction data</li>
                        <li><strong>Administrator Accounts:</strong> For managing institutional data and settings</li>
                    </ul>

                    <h3>4. Acceptable Use Policy</h3>
                    <h5>Permitted Uses</h5>
                    <ul>
                        <li>Complete surveys honestly and thoughtfully</li>
                        <li>Provide constructive feedback for educational improvement</li>
                        <li>Use analytics for legitimate educational purposes</li>
                        <li>Maintain professional communication standards</li>
                    </ul>

                    <h5>Prohibited Uses</h5>
                    <p>You may not:</p>
                    <ul>
                        <li>Provide false or misleading information in surveys</li>
                        <li>Attempt to manipulate or bias survey results</li>
                        <li>Access accounts or data belonging to others</li>
                        <li>Use the service for commercial purposes without permission</li>
                        <li>Engage in harassment, discrimination, or inappropriate behavior</li>
                        <li>Attempt to reverse engineer or compromise platform security</li>
                        <li>Distribute malware or engage in illegal activities</li>
                    </ul>

                    <h3>5. Privacy and Data Protection</h3>
                    <ul>
                        <li>Your privacy is protected according to our Privacy Policy</li>
                        <li>Survey responses may be shared with your educational institution</li>
                        <li>Aggregated, anonymized data may be used for research and improvement</li>
                        <li>You retain ownership of your personal data and feedback</li>
                        <li>We comply with FERPA and other applicable education privacy laws</li>
                    </ul>

                    <h3>6. Intellectual Property Rights</h3>
                    <h5>Our Content</h5>
                    <ul>
                        <li>EduSurvey Pro platform, software, and documentation are our property</li>
                        <li>All trademarks, logos, and brand names belong to us</li>
                        <li>You may not copy, distribute, or create derivative works without permission</li>
                    </ul>

                    <h5>Your Content</h5>
                    <ul>
                        <li>You retain ownership of survey responses and feedback you provide</li>
                        <li>You grant us license to use your content for service improvement</li>
                        <li>You are responsible for ensuring your content doesn't infringe others' rights</li>
                    </ul>

                    <h3>7. Educational Institution Responsibilities</h3>
                    <p>Institutions using our platform must:</p>
                    <ul>
                        <li>Obtain necessary permissions for student data collection</li>
                        <li>Comply with applicable educational privacy laws</li>
                        <li>Use survey data responsibly for educational improvement</li>
                        <li>Ensure appropriate supervision of user accounts</li>
                        <li>Maintain current contact information and administrative details</li>
                    </ul>

                    <h3>8. Service Availability and Modifications</h3>
                    <ul>
                        <li>We strive to maintain 99.9% uptime but cannot guarantee uninterrupted service</li>
                        <li>We may modify features, add new services, or discontinue certain functions</li>
                        <li>Scheduled maintenance will be announced in advance when possible</li>
                        <li>We reserve the right to suspend service for security or legal reasons</li>
                    </ul>

                    <h3>9. Payment Terms (For Premium Features)</h3>
                    <ul>
                        <li>Subscription fees are billed in advance</li>
                        <li>All fees are non-refundable unless otherwise specified</li>
                        <li>Price changes will be communicated 30 days in advance</li>
                        <li>Failure to pay may result in account suspension</li>
                        <li>Free trial terms are specified at the time of signup</li>
                    </ul>

                    <h3>10. Limitation of Liability</h3>
                    <p>To the fullest extent permitted by law:</p>
                    <ul>
                        <li>We provide the service "as is" without warranties</li>
                        <li>We are not liable for indirect, incidental, or consequential damages</li>
                        <li>Our total liability is limited to the amount paid for the service</li>
                        <li>We are not responsible for third-party integrations or services</li>
                        <li>Users are responsible for backing up their important data</li>
                    </ul>

                    <h3>11. Indemnification</h3>
                    <p>You agree to indemnify and hold us harmless from claims arising from:</p>
                    <ul>
                        <li>Your use of the service in violation of these terms</li>
                        <li>Content you submit that infringes others' rights</li>
                        <li>Your violation of applicable laws or regulations</li>
                        <li>Unauthorized access to accounts under your control</li>
                    </ul>

                    <h3>12. Termination</h3>
                    <h5>By You</h5>
                    <ul>
                        <li>You may close your account at any time</li>
                        <li>Contact support for account deletion requests</li>
                        <li>Some data may be retained as required by law</li>
                    </ul>

                    <h5>By Us</h5>
                    <p>We may terminate accounts for:</p>
                    <ul>
                        <li>Violation of these terms or acceptable use policy</li>
                        <li>Non-payment of fees (for premium accounts)</li>
                        <li>Fraudulent or illegal activity</li>
                        <li>Extended periods of inactivity</li>
                        <li>Technical or security concerns</li>
                    </ul>

                    <h3>13. Dispute Resolution</h3>
                    <ul>
                        <li>Most disputes can be resolved through our support team</li>
                        <li>Formal disputes will be resolved through binding arbitration</li>
                        <li>You waive the right to participate in class action lawsuits</li>
                        <li>Governing law is the state where our headquarters is located</li>
                    </ul>

                    <h3>14. Changes to Terms</h3>
                    <ul>
                        <li>We may update these terms to reflect service changes or legal requirements</li>
                        <li>Significant changes will be announced via email or platform notifications</li>
                        <li>Continued use constitutes acceptance of updated terms</li>
                        <li>If you disagree with changes, you may close your account</li>
                    </ul>

                    <h3>15. Contact Information</h3>
                    <p>For questions about these terms:</p>
                    <div class="card bg-light">
                        <div class="card-body">
                            <p><strong>Legal Department</strong><br>
                            EduSurvey Pro<br>
                            123 Education Street<br>
                            Learning City, LC 12345</p>
                            
                            <p><strong>Email:</strong> legal@edusurvey.pro<br>
                            <strong>Phone:</strong> +1 (555) 123-4571</p>
                        </div>
                    </div>

                    <div class="alert alert-info mt-4">
                        <i class="fas fa-handshake me-2"></i>
                        <strong>Agreement:</strong> By using EduSurvey Pro, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service.
                    </div>

                    <div class="text-center mt-4">
                        <a href="privacy.php" class="btn btn-outline-primary me-2">
                            <i class="fas fa-shield-alt me-1"></i>Privacy Policy
                        </a>
                        <a href="contact.php" class="btn btn-outline-success">
                            <i class="fas fa-envelope me-1"></i>Contact Legal Team
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../core/includes/footer.php'; ?>
