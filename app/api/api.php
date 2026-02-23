<?php
session_start();
$pageTitle = "API Reference";
$basePath = "../../";
require_once '../../core/includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-12 text-center mb-4">
            <h1 class="display-4 mb-3">
                <i class="fas fa-cogs me-3 text-primary"></i>EduSurvey Pro API
            </h1>
            <p class="lead text-muted">Developer documentation and reference guide for our REST API</p>
        </div>
    </div>

    <!-- Breadcrumb Navigation -->
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>public/index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>public/documentation.php">Documentation</a></li>
                    <li class="breadcrumb-item active" aria-current="page">API Reference</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3">
            <div class="card sticky-top" style="top: 100px;">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-code me-2"></i>Quick Navigation</h5>
                </div>
                <div class="card-body p-0">
                    <nav class="nav nav-pills flex-column">
                        <a class="nav-link active" href="#introduction">Introduction</a>
                        <a class="nav-link" href="#authentication">Authentication</a>
                        <a class="nav-link" href="#endpoints">Endpoints</a>
                        <a class="nav-link" href="#responses">Response Format</a>
                        <a class="nav-link" href="#errors">Error Handling</a>
                        <a class="nav-link" href="#examples">Code Examples</a>
                        <a class="nav-link" href="#rate-limits">Rate Limits</a>
                    </nav>
                </div>
            </div>
        </div>
        
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0"><i class="fas fa-code me-2"></i>API Reference Guide</h2>
                </div>
                <div class="card-body">
                    <section id="introduction" class="mb-5">
                        <h2 class="text-primary mb-3">Introduction</h2>
                        <p class="lead">The EduSurvey Pro API provides programmatic access to survey data, analytics, and user management features.</p>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Base URL:</strong> <code>https://api.edusurvey.pro/v1</code>
                        </div>

                        <h4>Features</h4>
                        <ul>
                            <li>RESTful API design with JSON responses</li>
                            <li>OAuth 2.0 and API key authentication</li>
                            <li>Rate limiting and usage analytics</li>
                            <li>Comprehensive error reporting</li>
                            <li>Webhook support for real-time updates</li>
                        </ul>
                    </section>

                    <section id="authentication" class="mb-5">
                        <h2 class="text-primary mb-3">Authentication</h2>
                        
                        <h4>API Key Authentication</h4>
                        <p>Include your API key in the request header:</p>
                        <div class="card bg-light">
                            <div class="card-body">
                                <code>Authorization: Bearer YOUR_API_KEY</code>
                            </div>
                        </div>

                        <h4>OAuth 2.0 (Recommended)</h4>
                        <p>For applications requiring user-specific access:</p>
                        <ol>
                            <li>Register your application in the developer portal</li>
                            <li>Redirect users to the authorization endpoint</li>
                            <li>Exchange authorization code for access token</li>
                            <li>Use access token for authenticated requests</li>
                        </ol>

                        <div class="alert alert-warning">
                            <i class="fas fa-key me-2"></i>
                            <strong>Security:</strong> Never expose API keys in client-side code. Use environment variables or secure configuration files.
                        </div>
                    </section>

                    <section id="endpoints" class="mb-5">
                        <h2 class="text-primary mb-3">API Endpoints</h2>

                        <h4>Surveys</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Method</th>
                                        <th>Endpoint</th>
                                        <th>Description</th>
                                        <th>Auth Required</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="badge bg-success">GET</span></td>
                                        <td><code>/surveys</code></td>
                                        <td>List all available surveys</td>
                                        <td>Yes</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-success">GET</span></td>
                                        <td><code>/surveys/{id}</code></td>
                                        <td>Get specific survey details</td>
                                        <td>Yes</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-primary">POST</span></td>
                                        <td><code>/surveys</code></td>
                                        <td>Create new survey</td>
                                        <td>Admin</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-warning">PUT</span></td>
                                        <td><code>/surveys/{id}</code></td>
                                        <td>Update survey</td>
                                        <td>Admin</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-danger">DELETE</span></td>
                                        <td><code>/surveys/{id}</code></td>
                                        <td>Delete survey</td>
                                        <td>Admin</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h4>Responses</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Method</th>
                                        <th>Endpoint</th>
                                        <th>Description</th>
                                        <th>Auth Required</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="badge bg-primary">POST</span></td>
                                        <td><code>/responses</code></td>
                                        <td>Submit survey response</td>
                                        <td>Yes</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-success">GET</span></td>
                                        <td><code>/responses</code></td>
                                        <td>List survey responses</td>
                                        <td>Admin</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-success">GET</span></td>
                                        <td><code>/responses/{id}</code></td>
                                        <td>Get specific response</td>
                                        <td>Owner/Admin</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h4>Analytics</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Method</th>
                                        <th>Endpoint</th>
                                        <th>Description</th>
                                        <th>Auth Required</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="badge bg-success">GET</span></td>
                                        <td><code>/analytics/summary</code></td>
                                        <td>Get analytics summary</td>
                                        <td>Admin</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-success">GET</span></td>
                                        <td><code>/analytics/trends</code></td>
                                        <td>Get trend analysis</td>
                                        <td>Admin</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-success">GET</span></td>
                                        <td><code>/analytics/export</code></td>
                                        <td>Export analytics data</td>
                                        <td>Admin</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h4>Users</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Method</th>
                                        <th>Endpoint</th>
                                        <th>Description</th>
                                        <th>Auth Required</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="badge bg-success">GET</span></td>
                                        <td><code>/users/profile</code></td>
                                        <td>Get current user profile</td>
                                        <td>Yes</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-warning">PUT</span></td>
                                        <td><code>/users/profile</code></td>
                                        <td>Update user profile</td>
                                        <td>Yes</td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge bg-success">GET</span></td>
                                        <td><code>/users</code></td>
                                        <td>List all users</td>
                                        <td>Admin</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section id="responses" class="mb-5">
                        <h2 class="text-primary mb-3">Response Format</h2>
                        
                        <h4>Success Response</h4>
                        <div class="card bg-light">
                            <div class="card-body">
                                <pre><code>{
  "success": true,
  "data": {
    "id": 123,
    "title": "Student Satisfaction Survey",
    "description": "Annual satisfaction survey",
    "created_at": "2025-09-13T10:00:00Z"
  },
  "meta": {
    "timestamp": "2025-09-13T10:30:00Z",
    "version": "1.0"
  }
}</code></pre>
                            </div>
                        </div>

                        <h4>Error Response</h4>
                        <div class="card bg-light">
                            <div class="card-body">
                                <pre><code>{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Required field 'title' is missing",
    "details": {
      "field": "title",
      "type": "required"
    }
  },
  "meta": {
    "timestamp": "2025-09-13T10:30:00Z",
    "version": "1.0"
  }
}</code></pre>
                            </div>
                        </div>
                    </section>

                    <section id="errors" class="mb-5">
                        <h2 class="text-primary mb-3">Error Handling</h2>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>HTTP Status</th>
                                        <th>Error Code</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>400</td>
                                        <td>BAD_REQUEST</td>
                                        <td>Invalid request syntax or parameters</td>
                                    </tr>
                                    <tr>
                                        <td>401</td>
                                        <td>UNAUTHORIZED</td>
                                        <td>Authentication required or invalid credentials</td>
                                    </tr>
                                    <tr>
                                        <td>403</td>
                                        <td>FORBIDDEN</td>
                                        <td>Insufficient permissions for requested action</td>
                                    </tr>
                                    <tr>
                                        <td>404</td>
                                        <td>NOT_FOUND</td>
                                        <td>Requested resource does not exist</td>
                                    </tr>
                                    <tr>
                                        <td>422</td>
                                        <td>VALIDATION_ERROR</td>
                                        <td>Request validation failed</td>
                                    </tr>
                                    <tr>
                                        <td>429</td>
                                        <td>RATE_LIMITED</td>
                                        <td>Too many requests, rate limit exceeded</td>
                                    </tr>
                                    <tr>
                                        <td>500</td>
                                        <td>INTERNAL_ERROR</td>
                                        <td>Internal server error</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section id="examples" class="mb-5">
                        <h2 class="text-primary mb-3">Code Examples</h2>

                        <h4>JavaScript (Fetch)</h4>
                        <div class="card bg-light">
                            <div class="card-body">
                                <pre><code>// Get all surveys
const response = await fetch('https://api.edusurvey.pro/v1/surveys', {
  headers: {
    'Authorization': 'Bearer YOUR_API_KEY',
    'Content-Type': 'application/json'
  }
});

const data = await response.json();
console.log(data);</code></pre>
                            </div>
                        </div>

                        <h4>Python (Requests)</h4>
                        <div class="card bg-light">
                            <div class="card-body">
                                <pre><code>import requests

headers = {
    'Authorization': 'Bearer YOUR_API_KEY',
    'Content-Type': 'application/json'
}

response = requests.get(
    'https://api.edusurvey.pro/v1/surveys', 
    headers=headers
)

data = response.json()
print(data)</code></pre>
                            </div>
                        </div>

                        <h4>PHP (cURL)</h4>
                        <div class="card bg-light">
                            <div class="card-body">
                                <pre><code>$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => 'https://api.edusurvey.pro/v1/surveys',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer YOUR_API_KEY',
        'Content-Type: application/json'
    ]
]);

$response = curl_exec($curl);
curl_close($curl);

$data = json_decode($response, true);
var_dump($data);</code></pre>
                            </div>
                        </div>
                    </section>

                    <section id="rate-limits" class="mb-5">
                        <h2 class="text-primary mb-3">Rate Limits</h2>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-clock me-2"></i>
                            <strong>Rate Limits:</strong> To ensure fair usage, API requests are limited based on your subscription tier.
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Plan</th>
                                        <th>Requests per Hour</th>
                                        <th>Burst Limit</th>
                                        <th>Concurrent Requests</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Free</td>
                                        <td>100</td>
                                        <td>10</td>
                                        <td>2</td>
                                    </tr>
                                    <tr>
                                        <td>Basic</td>
                                        <td>1,000</td>
                                        <td>50</td>
                                        <td>5</td>
                                    </tr>
                                    <tr>
                                        <td>Professional</td>
                                        <td>10,000</td>
                                        <td>200</td>
                                        <td>20</td>
                                    </tr>
                                    <tr>
                                        <td>Enterprise</td>
                                        <td>Custom</td>
                                        <td>Custom</td>
                                        <td>Custom</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h4>Rate Limit Headers</h4>
                        <p>All API responses include rate limit information in headers:</p>
                        <ul>
                            <li><code>X-RateLimit-Limit</code>: Maximum requests per hour</li>
                            <li><code>X-RateLimit-Remaining</code>: Remaining requests in current window</li>
                            <li><code>X-RateLimit-Reset</code>: Unix timestamp when limit resets</li>
                        </ul>
                    </section>

                    <div class="alert alert-info">
                        <i class="fas fa-question-circle me-2"></i>
                        <strong>Need Help?</strong> Contact our developer support team at 
                        <a href="mailto:developers@edusurvey.pro">developers@edusurvey.pro</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Smooth scrolling for navigation links
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
            
            // Update active nav link
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        }
    });
});

// Highlight active section on scroll
window.addEventListener('scroll', function() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link');
    
    let current = '';
    sections.forEach(section => {
        const sectionTop = section.getBoundingClientRect().top;
        if (sectionTop <= 150) {
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
</script>

<?php require_once '../../core/includes/footer.php'; ?>
