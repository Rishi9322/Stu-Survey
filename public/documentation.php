<?php
session_start();
$pageTitle = "Documentation";
$basePath = "../";
require_once '../core/includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-12 text-center mb-4">
            <h1 class="display-4 mb-3">
                <i class="fas fa-file-alt me-3 text-primary"></i>EduSurvey Pro Documentation
            </h1>
            <p class="lead text-muted">Complete guides and tutorials to help you get the most out of our platform</p>
        </div>
    </div>

    <!-- Breadcrumb Navigation -->
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Documentation</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-book me-2"></i>Quick Navigation</h5>
                </div>
                <div class="card-body p-0">
                    <nav class="nav nav-pills flex-column">
                        <a class="nav-link active" href="#getting-started">Getting Started</a>
                        <a class="nav-link" href="#user-guide">User Guide</a>
                        <a class="nav-link" href="#admin-guide">Admin Guide</a>
                        <a class="nav-link" href="#api-docs">API Documentation</a>
                        <a class="nav-link" href="#project-docs">Project Documents</a>
                        <a class="nav-link" href="#troubleshooting">Troubleshooting</a>
                    </nav>
                </div>
            </div>
        </div>
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0"><i class="fas fa-book-open me-2"></i>Documentation Guide</h2>
                </div>
                <div class="card-body">
                    <section id="getting-started" class="mb-5">
                        <h2 class="text-primary mb-3">Getting Started</h2>
                        <p class="lead">Welcome to EduSurvey Pro! This comprehensive guide will help you get up and running quickly.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h4>For Students</h4>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item"><i class="fas fa-user-plus me-2"></i>Create your account</li>
                                    <li class="list-group-item"><i class="fas fa-poll me-2"></i>Complete surveys</li>
                                    <li class="list-group-item"><i class="fas fa-star me-2"></i>Rate teachers</li>
                                    <li class="list-group-item"><i class="fas fa-chart-bar me-2"></i>View analytics</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h4>For Teachers</h4>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item"><i class="fas fa-clipboard-list me-2"></i>Access feedback</li>
                                    <li class="list-group-item"><i class="fas fa-chart-line me-2"></i>View performance data</li>
                                    <li class="list-group-item"><i class="fas fa-users me-2"></i>Manage student interactions</li>
                                    <li class="list-group-item"><i class="fas fa-lightbulb me-2"></i>Submit suggestions</li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <section id="user-guide" class="mb-5">
                        <h2 class="text-primary mb-3">User Guide</h2>
                        
                        <div class="accordion" id="userGuideAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#loginProcess">
                                        Login Process
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
                                        Survey Completion
                                    </button>
                                </h2>
                                <div id="surveyCompletion" class="accordion-collapse collapse" data-bs-parent="#userGuideAccordion">
                                    <div class="accordion-body">
                                        <p>Complete surveys to provide valuable feedback:</p>
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

                    <section id="admin-guide" class="mb-5">
                        <h2 class="text-primary mb-3">Administrator Guide</h2>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            This section is for system administrators who manage the EduSurvey Pro platform.
                        </div>
                        
                        <h4>Key Features</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <h5><i class="fas fa-users me-2"></i>User Management</h5>
                                <p>Create, edit, and manage user accounts across all roles.</p>
                            </div>
                            <div class="col-md-6">
                                <h5><i class="fas fa-chart-pie me-2"></i>Analytics Dashboard</h5>
                                <p>Comprehensive reporting and data visualization tools.</p>
                            </div>
                            <div class="col-md-6">
                                <h5><i class="fas fa-cog me-2"></i>System Configuration</h5>
                                <p>Configure system settings, surveys, and AI models.</p>
                            </div>
                            <div class="col-md-6">
                                <h5><i class="fas fa-robot me-2"></i>AI Insights</h5>
                                <p>Advanced AI-powered analysis and recommendations.</p>
                            </div>
                        </div>
                    </section>

                    <section id="api-docs" class="mb-5">
                        <h2 class="text-primary mb-3">API Documentation</h2>
                        <p>For developers integrating with EduSurvey Pro:</p>
                        
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5>Base URL</h5>
                                <code>http://localhost/stu/api/</code>
                                
                                <h5 class="mt-3">Authentication</h5>
                                <p>All API requests require authentication via session or API key.</p>
                                
                                <h5 class="mt-3">Available Endpoints</h5>
                                <ul>
                                    <li><code>GET /surveys</code> - List all surveys</li>
                                    <li><code>POST /responses</code> - Submit survey responses</li>
                                    <li><code>GET /analytics</code> - Retrieve analytics data</li>
                                    <li><code>GET /users</code> - User management (Admin only)</li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <section id="project-docs" class="mb-5">
                        <h2 class="text-primary mb-3">Project Documents</h2>
                        <p>Access comprehensive project documentation and guides:</p>
                        
                        <!-- Quick overview -->
                        <div class="alert alert-info mb-4">
                            <h5><i class="fas fa-info-circle me-2"></i>Available Documents</h5>
                            <p class="mb-2">This section provides access to all markdown documentation files in the project:</p>
                            <ul class="mb-0">
                                <li><strong>Reorganization Guide</strong> - Complete project restructuring documentation</li>
                                <li><strong>README</strong> - Project overview and setup instructions</li>
                                <li><strong>AI Report</strong> - Comprehensive AI system analysis and capabilities</li>
                                <li><strong>Deployment Guide</strong> - Deployment instructions and success stories</li>
                                <li><strong>Python Setup</strong> - Python environment configuration guide</li>
                                <li><strong>File Structure</strong> - Detailed file organization documentation</li>
                            </ul>
                        </div>
                        
                        <!-- Search bar for documents -->
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="docSearch" placeholder="Search documents..." onkeyup="filterDocuments()">
                                <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="row" id="documentsGrid">
                            <?php 
                            // Define markdown files with descriptions
                            $mdFiles = [
                                'REORGANIZATION_GUIDE.md' => [
                                    'title' => 'Reorganization Guide',
                                    'description' => 'Complete guide to the new project structure and file organization',
                                    'icon' => 'fas fa-sitemap',
                                    'path' => '../REORGANIZATION_GUIDE.md'
                                ],
                                'README.md' => [
                                    'title' => 'Project README',
                                    'description' => 'Main project overview, features, and setup instructions',
                                    'icon' => 'fas fa-file-alt',
                                    'path' => '../README.md'
                                ],
                                'AI_COMPREHENSIVE_REPORT.md' => [
                                    'title' => 'AI System Report',
                                    'description' => 'Comprehensive AI system capabilities and performance analysis',
                                    'icon' => 'fas fa-robot',
                                    'path' => '../AI_COMPREHENSIVE_REPORT.md'
                                ],
                                'DEPLOYMENT_SUCCESS.md' => [
                                    'title' => 'Deployment Guide',
                                    'description' => 'Deployment success stories and configuration guide',
                                    'icon' => 'fas fa-rocket',
                                    'path' => '../DEPLOYMENT_SUCCESS.md'
                                ],
                                'python_path_fix_guide.md' => [
                                    'title' => 'Python Setup Guide',
                                    'description' => 'Python environment configuration and troubleshooting',
                                    'icon' => 'fab fa-python',
                                    'path' => '../python_path_fix_guide.md'
                                ],
                                'docs/file-structure.md' => [
                                    'title' => 'File Structure',
                                    'description' => 'Detailed file structure documentation',
                                    'icon' => 'fas fa-folder-tree',
                                    'path' => '../docs/file-structure.md'
                                ]
                            ];
                            
                            foreach ($mdFiles as $filename => $info): ?>
                                <div class="col-lg-6 col-md-12 mb-3 doc-item" data-title="<?php echo strtolower($info['title']); ?>" data-description="<?php echo strtolower($info['description']); ?>" data-filename="<?php echo strtolower($filename); ?>">
                                    <div class="card h-100 border-primary">
                                        <div class="card-body">
                                            <div class="d-flex align-items-start">
                                                <div class="flex-shrink-0">
                                                    <i class="<?php echo $info['icon']; ?> fa-2x text-primary me-3"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h5 class="card-title"><?php echo $info['title']; ?></h5>
                                                    <p class="card-text"><?php echo $info['description']; ?></p>
                                                    <div class="btn-group" role="group">
                                                        <button class="btn btn-primary btn-sm" onclick="loadMarkdownContent('<?php echo $filename; ?>', '<?php echo $info['path']; ?>')">
                                                            <i class="fas fa-eye me-1"></i>View
                                                        </button>
                                                        <button class="btn btn-outline-primary btn-sm" onclick="downloadMarkdownFile('<?php echo $filename; ?>', '<?php echo addslashes($info['title']); ?>')">
                                                            <i class="fas fa-download me-1"></i>Download
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Markdown Viewer Modal -->
                        <div class="modal fade" id="markdownModal" tabindex="-1" aria-labelledby="markdownModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="markdownModalLabel">Document Viewer</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div id="markdownContent" class="markdown-content">
                                            <div class="text-center">
                                                <div class="spinner-border" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-primary" id="downloadCurrentDoc">
                                            <i class="fas fa-download me-1"></i>Download
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section id="troubleshooting" class="mb-5">
                        <h2 class="text-primary mb-3">Troubleshooting</h2>
                        
                        <h4>Common Issues</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Issue</th>
                                        <th>Solution</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Can't log in</td>
                                        <td>Check credentials and ensure account is active</td>
                                    </tr>
                                    <tr>
                                        <td>Survey not loading</td>
                                        <td>Refresh page or contact administrator</td>
                                    </tr>
                                    <tr>
                                        <td>Data not displaying</td>
                                        <td>Clear browser cache and reload</td>
                                    </tr>
                                    <tr>
                                        <td>Permission denied</td>
                                        <td>Contact admin to verify role permissions</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <div class="alert alert-success">
                        <i class="fas fa-question-circle me-2"></i>
                        <strong>Need More Help?</strong> Contact our support team at 
                        <a href="mailto:support@edusurvey.pro">support@edusurvey.pro</a>
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
.markdown-content {
    max-height: 75vh;
    overflow-y: auto;
    padding: 0 10px;
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
    color: #0d6efd;
    margin-top: 0;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 3px solid #e9ecef;
}

.markdown-rendered .doc-h2 {
    font-size: 1.8rem;
    font-weight: 600;
    color: #6c757d;
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.markdown-rendered .doc-h3 {
    font-size: 1.4rem;
    font-weight: 600;
    color: #0dcaf0;
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
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
}

.markdown-rendered .code-header {
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
    font-size: 0.85rem;
    background: linear-gradient(135deg, #343a40, #495057);
}

.markdown-rendered pre {
    margin: 0;
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
    font-size: 0.9rem;
    line-height: 1.4;
    white-space: pre-wrap;
    word-wrap: break-word;
    background: #2d3748 !important;
}

.markdown-rendered .inline-code {
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
    font-size: 0.9em;
    background: #f8f9fa !important;
    border: 1px solid #e9ecef;
    padding: 2px 6px;
    border-radius: 4px;
    color: #e83e8c;
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
    color: #0d6efd;
}

/* Links */
.markdown-rendered a {
    color: #0d6efd;
    text-decoration: none;
    border-bottom: 1px dotted #0d6efd;
    transition: all 0.2s ease;
}

.markdown-rendered a:hover {
    color: #0a58ca;
    border-bottom: 1px solid #0a58ca;
    background: rgba(13, 110, 253, 0.05);
    padding: 2px 4px;
    border-radius: 3px;
}

/* Blockquotes */
.markdown-rendered blockquote {
    background: rgba(13, 110, 253, 0.05);
    border-left: 4px solid #0d6efd;
    border-radius: 0 8px 8px 0;
    margin: 1.5rem 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.markdown-rendered blockquote p {
    color: #495057;
    font-style: italic;
    font-weight: 500;
}

/* Tables */
.markdown-rendered table {
    border-collapse: collapse;
    width: 100%;
    margin: 1.5rem 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-radius: 8px;
    overflow: hidden;
}

.markdown-rendered table th {
    background: linear-gradient(135deg, #0d6efd, #0a58ca);
    color: white;
    font-weight: 600;
    text-align: left;
    padding: 12px 16px;
}

.markdown-rendered table td {
    padding: 10px 16px;
    border-bottom: 1px solid #e9ecef;
}

.markdown-rendered table tr:hover {
    background-color: rgba(13, 110, 253, 0.05);
}

/* Horizontal Rules */
.markdown-rendered hr {
    border: none;
    height: 2px;
    background: linear-gradient(90deg, transparent, #0d6efd, transparent);
    margin: 2rem 0;
}

/* Strong and Emphasis */
.markdown-rendered strong {
    color: #212529;
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

.card:hover {
    transform: translateY(-2px);
    transition: transform 0.2s;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>

<?php require_once '../core/includes/footer.php'; ?>
