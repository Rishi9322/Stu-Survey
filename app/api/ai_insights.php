<?php
// Start output buffering
if (ob_get_level() == 0) {
    ob_start();
}

// Include config file
require_once "../../core/includes/config.php";
require_once "../../core/includes/functions.php";

// Include the AI Insights Engine and Advanced AI Provider
require_once __DIR__ . '/../../ai/engines/AIInsightsEngine.php';
require_once __DIR__ . '/../../ai/engines/AdvancedAIProvider.php';

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

// Initialize AI Engine and Advanced Provider
$ai_engine = new AIInsightsEngine($conn);
$advanced_ai = new AdvancedAIProvider();
$available_models = $advanced_ai->getAvailableModels();

// Handle AJAX requests for advanced AI
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'get_advanced_insights') {
        $complaints_data = $ai_engine->analyzeComplaints();
        $suggestions_data = $ai_engine->analyzeSuggestions();
        $selected_model = $_POST['model'] ?? 'grok-compound';
        
        $result = $advanced_ai->generateContextualInsights($complaints_data, $suggestions_data, $selected_model);
        echo json_encode($result);
        exit;
    }
    
    if ($_POST['action'] === 'custom_query') {
        $question = $_POST['question'] ?? '';
        $selected_model = $_POST['model'] ?? 'grok-compound';
        
        // Get comprehensive data context for AI models
        $complaints_data = $ai_engine->analyzeComplaints();
        $suggestions_data = $ai_engine->analyzeSuggestions();
        $full_insights = $ai_engine->generateInsights();
        
        $data_context = [
            'complaints' => $complaints_data,
            'suggestions' => $suggestions_data,
            'survey_stats' => $full_insights['survey_stats'] ?? [],
            'teacher_stats' => $full_insights['teacher_stats'] ?? [],
            'predictive_insights' => $full_insights['predictive_insights'] ?? [],
            'recommendations' => $full_insights['recommendations'] ?? [],
            'recent_complaints' => array_map(function($c) {
                return [
                    'priority' => 'medium',
                    'subject' => 'Complaint',
                    'description' => $c['message'] ?? ''
                ];
            }, array_slice($complaints_data['complaints'] ?? [], 0, 10)),
            'recent_suggestions' => array_map(function($s) {
                return [
                    'status' => 'active',
                    'subject' => 'Suggestion',
                    'description' => $s['message'] ?? ''
                ];
            }, array_slice($suggestions_data['suggestions'] ?? [], 0, 10)),
            'stats' => [
                'complaints' => [
                    'total_complaints' => $complaints_data['total_count'] ?? 0,
                    'pending_complaints' => $complaints_data['total_count'] ?? 0
                ],
                'suggestions' => [
                    'total_suggestions' => $suggestions_data['total_count'] ?? 0,
                    'pending_suggestions' => $suggestions_data['total_count'] ?? 0
                ],
                'users' => [
                    'total_users' => $full_insights['survey_stats']['unique_users'] ?? 0,
                    'students' => $full_insights['survey_stats']['unique_users'] ?? 0,
                    'teachers' => $full_insights['teacher_stats']['unique_teachers'] ?? 0
                ]
            ]
        ];
        
        $result = $advanced_ai->answerCustomQuery($question, $selected_model, $data_context);
        echo json_encode($result);
        exit;
    }
}

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
                    <div class="badge bg-success fs-6">
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

    <!-- AI Model Selector Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-robot fa-2x text-white me-3"></i>
                                <div>
                                    <label for="ai-model-select" class="form-label mb-0 text-white fw-bold">Select AI Model</label>
                                    <small class="text-white-50 d-block">Choose the AI engine for analysis</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select id="ai-model-select" class="form-select form-select-lg">
                                <?php foreach ($available_models as $key => $model): ?>
                                    <option value="<?php echo $key; ?>" <?php echo $key === 'grok-compound' ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($model['name']); ?>
                                        <?php if (isset($model['limits']['unlimited'])): ?>
                                            (Unlimited)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div id="model-info" class="text-white">
                                <div class="d-flex align-items-center mb-1">
                                    <span id="ai-model-badge" class="badge bg-light text-dark me-2">Grok Compound</span>
                                    <span id="model-limits" class="small text-white-50">30 RPM • 250 RPD</span>
                                </div>
                                <div id="model-features">
                                    <span class="badge bg-white bg-opacity-25 me-1">reasoning</span>
                                    <span class="badge bg-white bg-opacity-25 me-1">analysis</span>
                                    <span class="badge bg-white bg-opacity-25">complex_queries</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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

        <!-- Enhanced AI Chat Assistant -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #6f42c1 0%, #007bff 100%);">
                    <div class="d-flex justify-content-between align-items-center text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-robot me-2"></i>
                            Advanced AI Chat
                        </h6>
                        <div class="d-flex align-items-center">
                            <span id="ai-model-badge" class="badge bg-light text-dark fs-7">Grok Compound</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0 d-flex flex-column">
                    <div id="chat-messages" class="flex-grow-1" style="height: 400px; overflow-y: auto; padding: 15px; background: #f8f9fa;">
                        <div class="chat-message ai-message mb-3">
                            <div class="d-flex">
                                <div class="avatar bg-primary text-white rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-brain fa-sm"></i>
                                </div>
                                <div class="message-content bg-white rounded px-3 py-2 shadow-sm">
                                    <small class="text-muted d-flex align-items-center">
                                        <span id="current-ai-model">Grok Compound AI</span>
                                        <span class="badge bg-success ms-2 fs-7">Advanced</span>
                                    </small>
                                    <div>Hello! I'm your advanced AI assistant powered by state-of-the-art language models. I can provide deep insights, answer complex questions, and generate actionable recommendations based on your educational feedback data.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 border-top bg-white">
                        <!-- Quick Action Buttons -->
                        <div class="d-flex gap-1 mb-2 flex-wrap">
                            <button class="btn btn-outline-primary btn-sm quick-query" data-query="Analyze the overall sentiment and provide recommendations">
                                📊 Full Analysis
                            </button>
                            <button class="btn btn-outline-success btn-sm quick-query" data-query="What are the top 3 priority issues to address?">
                                🎯 Priorities
                            </button>
                            <button class="btn btn-outline-info btn-sm quick-query" data-query="Show me positive feedback and success areas">
                                ✨ Positives
                            </button>
                            <button class="btn btn-outline-warning btn-sm quick-query" data-query="Predict future trends based on current data">
                                🔮 Trends
                            </button>
                        </div>
                        
                        <div class="input-group">
                            <input type="text" class="form-control" id="chat-input" placeholder="Ask me anything about your educational data..." aria-label="Chat input">
                            <button class="btn btn-primary" type="button" id="send-chat">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        
                        <div class="mt-2 d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                💡 Powered by advanced AI • Ask complex questions • Get detailed insights
                            </small>
                            <button class="btn btn-link btn-sm p-0" id="get-advanced-insights">
                                <i class="fas fa-magic me-1"></i>Auto Insights
                            </button>
                        </div>
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
    const modelSelect = document.getElementById('ai-model-select');
    const modelFeatures = document.getElementById('model-features');
    const modelLimits = document.getElementById('model-limits');
    const modelBadge = document.getElementById('ai-model-badge');
    const currentAIModel = document.getElementById('current-ai-model');

    // Model information
    const modelData = <?php echo json_encode($available_models); ?>;

    // Update model info when selection changes
    function updateModelInfo() {
        const selectedModel = modelSelect.value;
        const model = modelData[selectedModel];
        
        if (model) {
            if (modelBadge) modelBadge.textContent = model.name;
            if (currentAIModel) currentAIModel.textContent = model.name + ' AI';
            
            // Update features
            if (modelFeatures) {
                modelFeatures.innerHTML = '';
                if (model.features) {
                    model.features.forEach(feature => {
                        const badge = document.createElement('span');
                        badge.className = 'badge bg-white bg-opacity-25 me-1';
                        badge.textContent = feature;
                        modelFeatures.appendChild(badge);
                    });
                }
            }
            
            // Update limits
            if (modelLimits) {
                if (model.limits && model.limits.unlimited) {
                    modelLimits.textContent = '✓ Unlimited Usage';
                } else if (model.limits) {
                    modelLimits.textContent = `${model.limits.rpm || 0} RPM • ${model.limits.rpd || 0} RPD`;
                }
            }
        }
    }

    // Initialize on load
    updateModelInfo();
    
    // Update on change
    modelSelect.addEventListener('change', updateModelInfo);

    function renderMarkdown(text) {
        if (!text) return '';
        
        // Convert markdown to HTML
        let html = text
            // Bold text: **text** or __text__
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/__(.*?)__/g, '<strong>$1</strong>')
            
            // Italic text: *text* or _text_
            .replace(/(?<!\*)\*(?!\*)([^*]+)\*(?!\*)/g, '<em>$1</em>')
            .replace(/(?<!_)_(?!_)([^_]+)_(?!_)/g, '<em>$1</em>')
            
            // Headers: ### Header, ## Header, # Header
            .replace(/^### (.*$)/gm, '<h6 class="mt-3 mb-2 fw-bold text-primary">$1</h6>')
            .replace(/^## (.*$)/gm, '<h5 class="mt-3 mb-2 fw-bold text-primary">$1</h5>')
            .replace(/^# (.*$)/gm, '<h4 class="mt-3 mb-2 fw-bold text-primary">$1</h4>')
            
            // Lists: - item or * item
            .replace(/^[\-\*]\s(.*)$/gm, '<li class="mb-1">$1</li>')
            
            // Line breaks and paragraphs
            .replace(/\n\n/g, '</p><p class="mb-2">')
            .replace(/\n/g, '<br>');
        
        // Wrap in paragraph if it doesn't start with a block element
        if (!html.match(/^<[h1-6]|^<li|^<div|^<p/)) {
            html = '<p class="mb-2">' + html + '</p>';
        }
        
        // Wrap consecutive list items in ul tags
        html = html.replace(/(<li[^>]*>.*?<\/li>(?:\s*<li[^>]*>.*?<\/li>)*)/gs, function(match) {
            return '<ul class="mb-2 ps-3">' + match + '</ul>';
        });
        
        // Clean up extra paragraphs around block elements
        html = html.replace(/<p[^>]*>(<[h1-6][^>]*>.*?<\/[h1-6]>)<\/p>/g, '$1');
        html = html.replace(/<p[^>]*>(<ul.*?<\/ul>)<\/p>/g, '$1');
        
        return html;
    }

    function addMessageToChat(sender, message, isTemporary = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${sender}-message mb-3`;
        if (isTemporary) messageDiv.id = 'temp-message';
        
        const isUser = sender === 'user';
        const avatarClass = isUser ? 'bg-success' : 'bg-primary';
        const avatarIcon = isUser ? 'fas fa-user' : 'fas fa-brain';
        const alignmentClass = isUser ? 'justify-content-end' : '';
        const messageClass = isUser ? 'bg-success text-white' : 'bg-white';
        const senderName = isUser ? 'You' : (currentAIModel.textContent || 'AI Assistant');
        
        messageDiv.innerHTML = `
            <div class="d-flex ${alignmentClass}">
                ${!isUser ? `<div class="avatar ${avatarClass} text-white rounded-circle me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                    <i class="${avatarIcon} fa-sm"></i>
                </div>` : ''}
                <div class="message-content ${messageClass} rounded px-3 py-2 shadow-sm" style="max-width: 80%;">
                    <small class="text-muted ${isUser ? 'text-white-50' : ''}">
                        ${senderName}
                        ${isTemporary ? '<i class="fas fa-spinner fa-spin ms-1"></i>' : ''}
                    </small>
                    <div class="message-text">${sender === 'ai' ? renderMarkdown(message) : message}</div>
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

    async function sendChatMessage() {
        const query = chatInput.value.trim();
        if (!query) return;

        // Add user message to chat
        addMessageToChat('user', query);
        chatInput.value = '';
        sendButton.disabled = true;

        // Add temporary loading message
        const tempMessage = addMessageToChat('ai', 'Analyzing your request with advanced AI...', true);

        try {
            const formData = new FormData();
            formData.append('action', 'custom_query');
            formData.append('question', query);
            formData.append('model', modelSelect.value);

            const response = await fetch('ai_insights.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            // Remove temporary message
            const tempEl = document.getElementById('temp-message');
            if (tempEl) tempEl.remove();

            if (result.success) {
                let aiResponse = result.response;
                
                // Add model information if available
                if (result.model) {
                    aiResponse += `\n\n<small class="text-muted"><i class="fas fa-info-circle me-1"></i>Generated by ${result.model}</small>`;
                }
                
                // Add token usage if available
                if (result.tokens_used) {
                    aiResponse += ` <span class="badge bg-secondary ms-1">${result.tokens_used} tokens</span>`;
                }
                
                addMessageToChat('ai', aiResponse);
            } else {
                addMessageToChat('ai', result.error || 'Sorry, I encountered an error processing your request.');
                
                if (result.fallback) {
                    setTimeout(() => {
                        addMessageToChat('ai', '🔄 Fallback Response: ' + result.fallback);
                    }, 1000);
                }
            }
        } catch (error) {
            // Remove temporary message
            const tempEl = document.getElementById('temp-message');
            if (tempEl) tempEl.remove();
            
            addMessageToChat('ai', 'Sorry, there was an error connecting to the AI service. Please try again.');
            console.error('Chat error:', error);
        }

        sendButton.disabled = false;
    }

    // Quick query buttons
    document.querySelectorAll('.quick-query').forEach(button => {
        button.addEventListener('click', function() {
            const query = this.getAttribute('data-query');
            chatInput.value = query;
            sendChatMessage();
        });
    });

    // Auto insights button
    document.getElementById('get-advanced-insights').addEventListener('click', async function() {
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Generating...';
        this.disabled = true;

        try {
            const formData = new FormData();
            formData.append('action', 'get_advanced_insights');
            formData.append('model', modelSelect.value);

            const response = await fetch('ai_insights.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                addMessageToChat('ai', '🔍 <strong>Advanced Auto-Generated Insights:</strong><br><br>' + result.response);
            } else {
                addMessageToChat('ai', 'Unable to generate advanced insights: ' + (result.error || 'Unknown error'));
            }
        } catch (error) {
            addMessageToChat('ai', 'Error generating advanced insights. Please try again.');
            console.error('Auto insights error:', error);
        }

        this.innerHTML = '<i class="fas fa-magic me-1"></i>Auto Insights';
        this.disabled = false;
    });

    // Event listeners
    sendButton.addEventListener('click', sendChatMessage);
    chatInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendChatMessage();
        }
    });

    // Initialize model info
    if (modelSelect.value) {
        modelSelect.dispatchEvent(new Event('change'));
    }
});
</script>

<?php include '../../core/includes/footer.php'; ?>

