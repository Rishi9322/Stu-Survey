<?php
// Start output buffering
if (ob_get_level() == 0) {
    ob_start();
}

// Include config file
require_once "../../core/includes/config.php";
require_once "../../core/includes/functions.php";

// Include the AI Insights Engine
require_once __DIR__ . '/../../ai/engines/AIInsightsEngine.php';

// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isLoggedIn() || !hasRole("admin")) {
    header("location: ../../public/login.php");
    exit;
}

// Set page variables
$pageTitle = "AI Insights & Analytics";
$basePath = "../../";

// Initialize AI Engine
$ai_engine = new AIInsightsEngine($conn);
$ai_insights = $ai_engine->generateInsights();

?>

<?php include '../../core/includes/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-brain me-2 text-primary"></i>
                        AI Insights & Analytics Dashboard
                    </h1>
                    <p class="text-muted">Advanced AI-powered analysis with predictive insights and intelligent recommendations</p>
                </div>
                <div class="text-end">
                    <div class="badge bg-success fs-6 me-2">
                        <i class="fas fa-check-circle me-1"></i>
                        AI Engine v2.0 Active
                    </div>
                    <br>
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>
                        Last updated: <?php echo $ai_insights['analysis_metadata']['generated_at'] ?? date('Y-m-d H:i:s'); ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Overview Dashboard -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="p-3">
                                <div class="display-6 text-primary fw-bold">
                                    <?php echo $ai_insights['analysis_metadata']['confidence_level'] ?? 'N/A'; ?>%
                                </div>
                                <small class="text-muted">AI Confidence Level</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3">
                                <div class="display-6 text-success fw-bold">
                                    <?php echo ucfirst($ai_insights['predictive_insights']['trend_direction'] ?? 'analyzing'); ?>
                                </div>
                                <small class="text-muted">Trend Direction</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3">
                                <div class="display-6 text-info fw-bold">
                                    <?php echo count($ai_insights['recommendations'] ?? []); ?>
                                </div>
                                <small class="text-muted">Active Recommendations</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3">
                                <div class="display-6 text-warning fw-bold">
                                    <?php echo $ai_insights['analysis_metadata']['analysis_scope'] ?? 'N/A'; ?>
                                </div>
                                <small class="text-muted">Analysis Period</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Predictive Analytics Section -->
    <?php if (isset($ai_insights['predictive_insights']) && $ai_insights['predictive_insights']['confidence'] > 50): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h5 class="mb-0">
                        <i class="fas fa-crystal-ball me-2"></i>
                        Predictive Analytics & Future Insights
                    </h5>
                </div>
                <div class="card-body">
                    <?php $predictive = $ai_insights['predictive_insights']; ?>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>📈 Trend Analysis</h6>
                            <div class="alert alert-<?php echo $predictive['trend_direction'] === 'improving' ? 'success' : ($predictive['trend_direction'] === 'declining' ? 'warning' : 'info'); ?>">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong>Trend:</strong> <?php echo ucfirst($predictive['trend_direction']); ?>
                                        <br>
                                        <strong>Confidence:</strong> <?php echo $predictive['confidence']; ?>%
                                        <?php if (isset($predictive['forecasted_metrics']['next_month_rating'])): ?>
                                        <br>
                                        <strong>Predicted Rating:</strong> <?php echo $predictive['forecasted_metrics']['next_month_rating']; ?>/5
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-end">
                                        <i class="fas fa-<?php echo $predictive['trend_direction'] === 'improving' ? 'arrow-up' : ($predictive['trend_direction'] === 'declining' ? 'arrow-down' : 'minus'); ?> fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>💡 AI Recommendations</h6>
                            <?php if (!empty($predictive['recommendations'])): ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach (array_slice($predictive['recommendations'], 0, 3) as $rec): ?>
                                        <div class="list-group-item border-0 px-0">
                                            <i class="fas fa-lightbulb text-warning me-2"></i>
                                            <?php echo htmlspecialchars($rec); ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($predictive['risk_factors']) || !empty($predictive['opportunities'])): ?>
                    <hr>
                    <div class="row">
                        <?php if (!empty($predictive['risk_factors'])): ?>
                        <div class="col-md-6">
                            <h6 class="text-danger">⚠️ Risk Factors</h6>
                            <div class="bg-light p-3 rounded">
                                <?php foreach ($predictive['risk_factors'] as $risk): ?>
                                    <div class="text-danger mb-1">• <?php echo htmlspecialchars($risk); ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($predictive['opportunities'])): ?>
                        <div class="col-md-6">
                            <h6 class="text-success">🚀 Opportunities</h6>
                            <div class="bg-light p-3 rounded">
                                <?php foreach ($predictive['opportunities'] as $opportunity): ?>
                                    <div class="text-success mb-1">• <?php echo htmlspecialchars($opportunity); ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Content Row -->
    <div class="row">
        <!-- Enhanced Recommendations -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-brain me-2"></i>
                        AI-Generated Smart Recommendations
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($ai_insights['recommendations'])): ?>
                        <div class="row">
                            <?php foreach (array_slice($ai_insights['recommendations'], 0, 6) as $index => $rec): ?>
                                <div class="col-lg-6 mb-3">
                                    <div class="card h-100 border-<?php 
                                        echo $rec['type'] == 'critical' ? 'danger' : 
                                            ($rec['type'] == 'urgent' ? 'warning' : 
                                            ($rec['type'] == 'success' ? 'success' : 
                                            ($rec['type'] == 'opportunity' ? 'info' : 'secondary'))); 
                                    ?>">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <span class="badge bg-<?php 
                                                    echo $rec['type'] == 'critical' ? 'danger' : 
                                                        ($rec['type'] == 'urgent' ? 'warning' : 
                                                        ($rec['type'] == 'success' ? 'success' : 
                                                        ($rec['type'] == 'opportunity' ? 'info' : 'secondary'))); 
                                                ?>">
                                                    <?php echo ucfirst($rec['type']); ?>
                                                </span>
                                                <?php if (isset($rec['priority_score'])): ?>
                                                    <small class="text-muted">Priority: <?php echo $rec['priority_score']; ?></small>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <h6 class="card-title"><?php echo htmlspecialchars($rec['title']); ?></h6>
                                            <p class="card-text small text-muted mb-2">
                                                <?php echo htmlspecialchars($rec['description']); ?>
                                            </p>
                                            
                                            <div class="action-section">
                                                <strong class="text-primary">Recommended Action:</strong>
                                                <div class="small mt-1"><?php echo htmlspecialchars($rec['action']); ?></div>
                                            </div>
                                            
                                            <?php if (isset($rec['timeline']) || isset($rec['estimated_impact'])): ?>
                                                <hr class="my-2">
                                                <div class="row small text-muted">
                                                    <?php if (isset($rec['timeline'])): ?>
                                                        <div class="col-6">
                                                            <i class="fas fa-clock me-1"></i><?php echo $rec['timeline']; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (isset($rec['estimated_impact'])): ?>
                                                        <div class="col-6">
                                                            <i class="fas fa-impact me-1"></i><?php echo ucfirst($rec['estimated_impact']); ?> Impact
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-robot fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">AI Analysis in Progress</h5>
                            <p class="text-muted">The AI engine is monitoring patterns and will generate recommendations as data becomes available.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- AI Chat Assistant -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-comments me-2"></i>
                        AI Assistant Chat
                    </h6>
                </div>
                <div class="card-body p-0 d-flex flex-column">
                    <div id="chat-messages" class="flex-grow-1" style="height: 400px; overflow-y: auto; padding: 15px; background: #f8f9fa;">
                        <div class="chat-message ai-message mb-3">
                            <div class="d-flex">
                                <div class="avatar bg-info text-white rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-robot fa-sm"></i>
                                </div>
                                <div class="message-content bg-white rounded px-3 py-2 shadow-sm">
                                    <small class="text-muted">AI Assistant</small>
                                    <div>Hello! I'm your AI insights assistant. Ask me about ratings, trends, complaints, suggestions, or any specific analytics you'd like to explore.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 border-top bg-white">
                        <div class="input-group">
                            <input type="text" class="form-control" id="chat-input" placeholder="Ask me about your data..." aria-label="Chat input">
                            <button class="btn btn-info" type="button" id="send-chat">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                Try: "What's the trend?", "Show complaint analysis", "Any recommendations?"
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Compact Analytics Section -->
    <div class="row">
        <!-- Sentiment Overview -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-heart me-2 text-danger"></i>
                        Sentiment Analysis Summary
                    </h6>
                </div>
                <div class="card-body">
                    <?php 
                    $complaints_sentiment = $ai_insights['complaints']['sentiment_summary'] ?? ['positive' => 0, 'negative' => 0, 'neutral' => 0];
                    $suggestions_sentiment = $ai_insights['suggestions']['sentiment_summary'] ?? ['positive' => 0, 'negative' => 0, 'neutral' => 0];
                    $total_complaints = array_sum($complaints_sentiment);
                    $total_suggestions = array_sum($suggestions_sentiment);
                    ?>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small><strong>Complaints</strong></small>
                            <small class="text-muted"><?php echo $total_complaints; ?> total</small>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <?php if ($total_complaints > 0): ?>
                                <div class="progress-bar bg-success" style="width: <?php echo ($complaints_sentiment['positive'] / $total_complaints) * 100; ?>%" title="Positive: <?php echo $complaints_sentiment['positive']; ?>">
                                    <?php if ($complaints_sentiment['positive'] > 0) echo $complaints_sentiment['positive']; ?>
                                </div>
                                <div class="progress-bar bg-warning" style="width: <?php echo ($complaints_sentiment['neutral'] / $total_complaints) * 100; ?>%" title="Neutral: <?php echo $complaints_sentiment['neutral']; ?>">
                                    <?php if ($complaints_sentiment['neutral'] > 0) echo $complaints_sentiment['neutral']; ?>
                                </div>
                                <div class="progress-bar bg-danger" style="width: <?php echo ($complaints_sentiment['negative'] / $total_complaints) * 100; ?>%" title="Negative: <?php echo $complaints_sentiment['negative']; ?>">
                                    <?php if ($complaints_sentiment['negative'] > 0) echo $complaints_sentiment['negative']; ?>
                                </div>
                            <?php else: ?>
                                <div class="progress-bar bg-light text-dark" style="width: 100%">No Data</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small><strong>Suggestions</strong></small>
                            <small class="text-muted"><?php echo $total_suggestions; ?> total</small>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <?php if ($total_suggestions > 0): ?>
                                <div class="progress-bar bg-success" style="width: <?php echo ($suggestions_sentiment['positive'] / $total_suggestions) * 100; ?>%" title="Positive: <?php echo $suggestions_sentiment['positive']; ?>">
                                    <?php if ($suggestions_sentiment['positive'] > 0) echo $suggestions_sentiment['positive']; ?>
                                </div>
                                <div class="progress-bar bg-warning" style="width: <?php echo ($suggestions_sentiment['neutral'] / $total_suggestions) * 100; ?>%" title="Neutral: <?php echo $suggestions_sentiment['neutral']; ?>">
                                    <?php if ($suggestions_sentiment['neutral'] > 0) echo $suggestions_sentiment['neutral']; ?>
                                </div>
                                <div class="progress-bar bg-danger" style="width: <?php echo ($suggestions_sentiment['negative'] / $total_suggestions) * 100; ?>%" title="Negative: <?php echo $suggestions_sentiment['negative']; ?>">
                                    <?php if ($suggestions_sentiment['negative'] > 0) echo $suggestions_sentiment['negative']; ?>
                                </div>
                            <?php else: ?>
                                <div class="progress-bar bg-light text-dark" style="width: 100%">No Data</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <small class="text-muted">
                            <span class="badge bg-success me-1">😊 Positive</span>
                            <span class="badge bg-warning me-1">😐 Neutral</span>
                            <span class="badge bg-danger">😞 Negative</span>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Topic Analysis -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-tags me-2 text-warning"></i>
                        Key Topics & Themes
                    </h6>
                </div>
                <div class="card-body">
                    <?php 
                    $complaint_topics = $ai_insights['complaints']['topics']['primary_topics'] ?? [];
                    $suggestion_topics = $ai_insights['suggestions']['topics']['primary_topics'] ?? [];
                    ?>
                    
                    <?php if (!empty($complaint_topics)): ?>
                        <div class="mb-3">
                            <small class="text-muted fw-bold">TOP COMPLAINT TOPICS</small>
                            <?php $count = 0; foreach ($complaint_topics as $topic => $frequency): if (++$count > 5) break; ?>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-danger-soft text-danger"><?php echo htmlspecialchars($topic); ?></span>
                                    <small class="text-muted"><?php echo $frequency; ?>x</small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($suggestion_topics)): ?>
                        <div>
                            <small class="text-muted fw-bold">TOP SUGGESTION TOPICS</small>
                            <?php $count = 0; foreach ($suggestion_topics as $topic => $frequency): if (++$count > 5) break; ?>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-success-soft text-success"><?php echo htmlspecialchars($topic); ?></span>
                                    <small class="text-muted"><?php echo $frequency; ?>x</small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (empty($complaint_topics) && empty($suggestion_topics)): ?>
                        <div class="text-center text-muted">
                            <i class="fas fa-search fa-2x mb-2"></i>
                            <p>No topics identified yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-danger-soft { background-color: rgba(220, 53, 69, 0.1) !important; }
.bg-success-soft { background-color: rgba(25, 135, 84, 0.1) !important; }
.border-left-danger { border-left: 4px solid #dc3545 !important; }
.border-left-warning { border-left: 4px solid #ffc107 !important; }
.border-left-success { border-left: 4px solid #198754 !important; }
.border-left-info { border-left: 4px solid #0dcaf0 !important; }

.chat-message { animation: fadeIn 0.3s ease-in; }
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.card { transition: transform 0.2s ease-in-out; }
.card:hover { transform: translateY(-2px); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatInput = document.getElementById('chat-input');
    const sendButton = document.getElementById('send-chat');
    const chatMessages = document.getElementById('chat-messages');

    function addMessageToChat(sender, message, isTemporary = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${sender}-message mb-3`;
        if (isTemporary) messageDiv.id = 'temp-message';
        
        const isUser = sender === 'user';
        const avatarClass = isUser ? 'bg-primary' : 'bg-info';
        const avatarIcon = isUser ? 'fas fa-user' : 'fas fa-robot';
        const alignmentClass = isUser ? 'justify-content-end' : '';
        const messageClass = isUser ? 'bg-primary text-white' : 'bg-white';
        
        messageDiv.innerHTML = `
            <div class="d-flex ${alignmentClass}">
                ${!isUser ? `<div class="avatar ${avatarClass} text-white rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                    <i class="${avatarIcon} fa-sm"></i>
                </div>` : ''}
                <div class="message-content ${messageClass} rounded px-3 py-2 shadow-sm" style="max-width: 80%;">
                    ${!isUser ? '<small class="text-muted">AI Assistant</small>' : '<small class="text-light">You</small>'}
                    <div>${message}</div>
                </div>
                ${isUser ? `<div class="avatar ${avatarClass} text-white rounded-circle ms-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                    <i class="${avatarIcon} fa-sm"></i>
                </div>` : ''}
            </div>
        `;
        
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
        return messageDiv;
    }

    function sendMessage() {
        const query = chatInput.value.trim();
        if (!query) return;
        
        // Add user message to chat
        addMessageToChat('user', query);
        chatInput.value = '';
        
        // Show typing indicator
        const typingDiv = addMessageToChat('ai', '<i class="fas fa-spinner fa-spin me-2"></i>Analyzing your request...', true);
        
        // Send to AI
        fetch('ai_chat_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=chat_query&query=' + encodeURIComponent(query)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Invalid JSON response:', text);
                    throw new Error('Invalid response format from server');
                }
            });
        })
        .then(data => {
            // Remove typing indicator
            typingDiv.remove();
            
            // Add AI response
            if (data.error) {
                addMessageToChat('ai', '❌ Error: ' + data.error);
            } else {
                addMessageToChat('ai', data.message);
            }
        })
        .catch(error => {
            typingDiv.remove();
            console.error('Chat error:', error);
            addMessageToChat('ai', '❌ Error connecting to AI assistant. Please try again.');
        });
    }

    sendButton.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
});
</script>

<?php include '../../core/includes/footer.php'; ?>

