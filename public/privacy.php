<?php
session_start();
$pageTitle = "Privacy Policy";
$basePath = "../";
require_once '../core/includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h1 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Privacy Policy</h1>
                </div>
                <div class="card-body">
                    <p class="lead">At EduSurvey Pro, we are committed to protecting your privacy and ensuring the security of your personal information.</p>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Last Updated:</strong> <?php echo date('F j, Y'); ?>
                    </div>

                    <h3>1. Information We Collect</h3>
                    <h5>Personal Information</h5>
                    <ul>
                        <li>Name, email address, and contact information</li>
                        <li>Educational institution affiliation</li>
                        <li>Role designation (student, teacher, administrator)</li>
                        <li>Academic information relevant to surveys</li>
                    </ul>

                    <h5>Survey Data</h5>
                    <ul>
                        <li>Survey responses and feedback</li>
                        <li>Ratings and evaluations</li>
                        <li>Comments and suggestions</li>
                        <li>Anonymous usage analytics</li>
                    </ul>

                    <h5>Technical Information</h5>
                    <ul>
                        <li>IP address and browser information</li>
                        <li>Device and operating system details</li>
                        <li>Login timestamps and session data</li>
                        <li>Platform usage statistics</li>
                    </ul>

                    <h3>2. How We Use Your Information</h3>
                    <ul>
                        <li><strong>Service Delivery:</strong> To provide and improve our educational survey platform</li>
                        <li><strong>Analytics:</strong> To generate insights and reports for educational improvement</li>
                        <li><strong>Communication:</strong> To send important updates and support messages</li>
                        <li><strong>Security:</strong> To protect against fraud and unauthorized access</li>
                        <li><strong>Legal Compliance:</strong> To comply with applicable laws and regulations</li>
                    </ul>

                    <h3>3. Information Sharing</h3>
                    <p>We do not sell, trade, or otherwise transfer your personal information to outside parties except:</p>
                    <ul>
                        <li><strong>Educational Institutions:</strong> Aggregated, anonymized data for institutional improvement</li>
                        <li><strong>Service Providers:</strong> Trusted partners who assist in platform operations</li>
                        <li><strong>Legal Requirements:</strong> When required by law or to protect rights and safety</li>
                        <li><strong>Consent:</strong> When you have given explicit permission</li>
                    </ul>

                    <h3>4. Data Security</h3>
                    <p>We implement comprehensive security measures including:</p>
                    <ul>
                        <li>Encryption of data in transit and at rest</li>
                        <li>Regular security audits and assessments</li>
                        <li>Access controls and authentication systems</li>
                        <li>Staff training on data protection practices</li>
                        <li>Secure backup and disaster recovery procedures</li>
                    </ul>

                    <h3>5. Your Rights</h3>
                    <p>You have the right to:</p>
                    <ul>
                        <li><strong>Access:</strong> Request copies of your personal data</li>
                        <li><strong>Correction:</strong> Request correction of inaccurate information</li>
                        <li><strong>Deletion:</strong> Request deletion of your personal data</li>
                        <li><strong>Portability:</strong> Request transfer of your data</li>
                        <li><strong>Objection:</strong> Object to processing of your data</li>
                        <li><strong>Withdraw Consent:</strong> Withdraw consent at any time</li>
                    </ul>

                    <h3>6. Cookies and Tracking</h3>
                    <p>We use cookies and similar technologies to:</p>
                    <ul>
                        <li>Maintain user sessions and preferences</li>
                        <li>Analyze platform usage patterns</li>
                        <li>Improve user experience and functionality</li>
                        <li>Provide personalized content and features</li>
                    </ul>
                    <p>You can control cookie settings through your browser preferences.</p>

                    <h3>7. Data Retention</h3>
                    <ul>
                        <li><strong>Active Accounts:</strong> Data retained while account is active</li>
                        <li><strong>Inactive Accounts:</strong> Data deleted after 3 years of inactivity</li>
                        <li><strong>Survey Data:</strong> Aggregated data may be retained for research purposes</li>
                        <li><strong>Legal Requirements:</strong> Some data retained as required by law</li>
                    </ul>

                    <h3>8. Third-Party Services</h3>
                    <p>Our platform may integrate with third-party services:</p>
                    <ul>
                        <li>Cloud hosting providers for data storage</li>
                        <li>Analytics services for usage insights</li>
                        <li>Communication tools for notifications</li>
                        <li>AI services for advanced analytics</li>
                    </ul>
                    <p>All third-party providers are carefully vetted for privacy compliance.</p>

                    <h3>9. International Data Transfers</h3>
                    <p>If you are located outside the United States, please note that:</p>
                    <ul>
                        <li>Your information may be transferred to and processed in the United States</li>
                        <li>We ensure appropriate safeguards are in place for international transfers</li>
                        <li>We comply with applicable international privacy frameworks</li>
                    </ul>

                    <h3>10. Children's Privacy</h3>
                    <p>We are committed to protecting children's privacy:</p>
                    <ul>
                        <li>We do not knowingly collect data from children under 13</li>
                        <li>For users 13-18, we require parental or guardian consent</li>
                        <li>Educational institutions must verify appropriate permissions</li>
                        <li>Special protections apply to student data under FERPA</li>
                    </ul>

                    <h3>11. Changes to This Policy</h3>
                    <p>We may update this privacy policy to reflect:</p>
                    <ul>
                        <li>Changes in our services or business practices</li>
                        <li>Legal or regulatory requirements</li>
                        <li>Industry best practices and standards</li>
                    </ul>
                    <p>We will notify users of significant changes via email or platform notifications.</p>

                    <h3>12. Contact Information</h3>
                    <p>For privacy-related questions or concerns:</p>
                    <div class="card bg-light">
                        <div class="card-body">
                            <p><strong>Privacy Officer</strong><br>
                            EduSurvey Pro<br>
                            123 Education Street<br>
                            Learning City, LC 12345</p>
                            
                            <p><strong>Email:</strong> privacy@edusurvey.pro<br>
                            <strong>Phone:</strong> +1 (555) 123-4570</p>
                        </div>
                    </div>

                    <div class="alert alert-success mt-4">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Your Trust Matters:</strong> We are committed to maintaining the highest standards of privacy protection and transparency in all our operations.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../core/includes/footer.php'; ?>
