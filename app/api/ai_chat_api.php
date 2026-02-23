<?php
// Prevent any HTML output and ensure clean JSON response
error_reporting(0);
ini_set('display_errors', 0);

// Set JSON header immediately
header('Content-Type: application/json');

// AI Chat Bot API for real-time suggestions
class AIChatBot {
    private $conn;
    private $ai_engine;
    
    public function __construct($connection, $ai_engine) {
        $this->conn = $connection;
        $this->ai_engine = $ai_engine;
    }
    
    public function processQuery($query) {
        $query = strtolower(trim($query));
        
        // Pattern matching for common admin queries
        if (strpos($query, 'rating') !== false || strpos($query, 'score') !== false) {
            return $this->getRatingInsights();
        }
        
        if (strpos($query, 'complaint') !== false) {
            return $this->getComplaintInsights();
        }
        
        if (strpos($query, 'suggestion') !== false) {
            return $this->getSuggestionInsights();
        }
        
        if (strpos($query, 'trend') !== false || strpos($query, 'pattern') !== false) {
            return $this->getTrendAnalysis();
        }
        
        if (strpos($query, 'recommendation') !== false || strpos($query, 'advice') !== false) {
            return $this->getRecommendations();
        }
        
        if (strpos($query, 'teacher') !== false || strpos($query, 'instructor') !== false) {
            return $this->getTeacherInsights();
        }
        
        if (strpos($query, 'student') !== false) {
            return $this->getStudentInsights();
        }
        
        // Default response
        return $this->getGeneralSummary();
    }
    
    private function getRatingInsights() {
        $query = "SELECT AVG(rating) as avg_rating, COUNT(*) as total, 
                  SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive_count
                  FROM survey_responses";
        $result = mysqli_query($this->conn, $query);
        $data = mysqli_fetch_assoc($result);
        
        $avg = round($data['avg_rating'], 2);
        $positive_percentage = round(($data['positive_count'] / $data['total']) * 100, 1);
        
        return [
            'type' => 'rating_analysis',
            'message' => "📊 **Rating Analysis**: The current average rating is **{$avg}/5** based on {$data['total']} responses. {$positive_percentage}% of ratings are 4 stars or higher, which " . 
                        ($positive_percentage >= 70 ? "indicates excellent satisfaction!" : 
                        ($positive_percentage >= 50 ? "shows good satisfaction levels." : "suggests room for improvement.")),
            'data' => $data
        ];
    }
    
    private function getComplaintInsights() {
        $insights = $this->ai_engine->analyzeComplaints();
        $total = $insights['total_count'];
        $negative_pct = $total > 0 ? round(($insights['sentiment_summary']['negative'] / $total) * 100, 1) : 0;
        
        $top_topic = !empty($insights['key_topics']) ? array_key_first($insights['key_topics']) : 'N/A';
        
        return [
            'type' => 'complaint_analysis',
            'message' => "⚠️ **Complaint Analysis**: Found **{$total} complaints** in recent data. {$negative_pct}% show negative sentiment. " .
                        ($total > 0 ? "The most mentioned topic is '{$top_topic}'. " : "") .
                        ($negative_pct > 60 ? "**Action needed**: High negative sentiment requires immediate attention." : "Sentiment levels are manageable."),
            'data' => $insights
        ];
    }
    
    private function getSuggestionInsights() {
        $insights = $this->ai_engine->analyzeSuggestions();
        $total = $insights['total_count'];
        $positive_pct = $total > 0 ? round(($insights['sentiment_summary']['positive'] / $total) * 100, 1) : 0;
        
        return [
            'type' => 'suggestion_analysis',
            'message' => "💡 **Suggestion Analysis**: Received **{$total} suggestions** with {$positive_pct}% showing positive sentiment. " .
                        ($positive_pct > 50 ? "Students are actively contributing constructive ideas!" : "Mixed sentiment in suggestions.") .
                        " Consider implementing popular suggestions to boost engagement.",
            'data' => $insights
        ];
    }
    
    private function getTrendAnalysis() {
        $query = "SELECT DATE(created_at) as date, COUNT(*) as count, AVG(rating) as avg_rating 
                  FROM survey_responses 
                  WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
                  GROUP BY DATE(created_at)
                  ORDER BY date DESC
                  LIMIT 7";
        $result = mysqli_query($this->conn, $query);
        
        $trends = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $trends[] = $row;
        }
        
        if (count($trends) >= 2) {
            $recent_avg = $trends[0]['avg_rating'];
            $prev_avg = $trends[1]['avg_rating'];
            $trend = $recent_avg > $prev_avg ? 'improving' : ($recent_avg < $prev_avg ? 'declining' : 'stable');
            
            return [
                'type' => 'trend_analysis',
                'message' => "📈 **Trend Analysis**: Rating trend is **{$trend}** over the past week. " .
                            "Recent average: {$recent_avg}/5. " .
                            ($trend === 'improving' ? "Great job! Keep up the good work." :
                            ($trend === 'declining' ? "⚠️ Attention needed to reverse the decline." : "Performance is stable.")),
                'data' => $trends
            ];
        }
        
        return [
            'type' => 'trend_analysis',
            'message' => "📈 **Trend Analysis**: Insufficient data for trend analysis. Need more survey responses over time.",
            'data' => []
        ];
    }
    
    private function getRecommendations() {
        $insights = $this->ai_engine->generateInsights();
        $recs = $insights['recommendations'];
        
        if (!empty($recs)) {
            $urgent_count = count(array_filter($recs, function($r) { return $r['type'] === 'urgent'; }));
            $message = "🎯 **AI Recommendations**: Generated **" . count($recs) . " recommendations**. ";
            
            if ($urgent_count > 0) {
                $message .= "⚠️ **{$urgent_count} urgent items** need immediate attention. ";
            }
            
            $message .= "Top recommendation: " . $recs[0]['title'];
            
            return [
                'type' => 'recommendations',
                'message' => $message,
                'data' => $recs
            ];
        }
        
        return [
            'type' => 'recommendations',
            'message' => "🎯 **AI Recommendations**: No specific recommendations at this time. System is monitoring for patterns.",
            'data' => []
        ];
    }
    
    private function getTeacherInsights() {
        $query = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_ratings,
                  COUNT(DISTINCT teacher_id) as unique_teachers
                  FROM teacher_ratings";
        $result = mysqli_query($this->conn, $query);
        $data = mysqli_fetch_assoc($result);
        
        $avg = round($data['avg_rating'], 2);
        
        return [
            'type' => 'teacher_insights',
            'message' => "👨‍🏫 **Teacher Insights**: **{$data['unique_teachers']} teachers** have been rated with an average of **{$avg}/5** across {$data['total_ratings']} ratings. " .
                        ($avg >= 4.0 ? "Excellent teaching performance!" :
                        ($avg >= 3.5 ? "Good performance, room for improvement." : "⚠️ Teaching quality needs attention.")),
            'data' => $data
        ];
    }
    
    private function getStudentInsights() {
        $query = "SELECT COUNT(DISTINCT user_id) as active_students,
                  COUNT(*) as total_responses
                  FROM survey_responses sr
                  JOIN users u ON sr.user_id = u.id
                  WHERE u.role = 'student'";
        $result = mysqli_query($this->conn, $query);
        $data = mysqli_fetch_assoc($result);
        
        $query2 = "SELECT COUNT(*) as total_students FROM users WHERE role = 'student'";
        $result2 = mysqli_query($this->conn, $query2);
        $total = mysqli_fetch_assoc($result2)['total_students'];
        
        $participation = $total > 0 ? round(($data['active_students'] / $total) * 100, 1) : 0;
        
        return [
            'type' => 'student_insights',
            'message' => "🎓 **Student Insights**: **{$data['active_students']} out of {$total} students** have participated in surveys ({$participation}% participation rate). " .
                        "Total responses: {$data['total_responses']}. " .
                        ($participation >= 70 ? "Excellent engagement!" :
                        ($participation >= 50 ? "Good engagement." : "⚠️ Low participation - consider engagement strategies.")),
            'data' => array_merge($data, ['total_students' => $total, 'participation_rate' => $participation])
        ];
    }
    
    private function getGeneralSummary() {
        $insights = $this->ai_engine->generateInsights();
        
        return [
            'type' => 'general_summary',
            'message' => "📋 **General Summary**: System has analyzed recent feedback and generated insights. " .
                        "You can ask me about specific topics like 'complaints', 'ratings', 'trends', 'teachers', or 'recommendations' for detailed analysis.",
            'data' => $insights
        ];
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        require_once "../../core/includes/config.php";
        require_once "../../core/includes/functions.php";
        
        // Include the AIInsightsEngine class
        require_once __DIR__ . '/../../ai/engines/AIInsightsEngine.php';
        
        session_start();
        
        if (!isLoggedIn() || !hasRole("admin")) {
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        if ($_POST['action'] === 'chat_query') {
            $ai_engine = new AIInsightsEngine($conn);
            $chat_bot = new AIChatBot($conn, $ai_engine);
            
            $query = $_POST['query'] ?? '';
            $response = $chat_bot->processQuery($query);
            
            echo json_encode($response);
            exit;
        }
        
        echo json_encode(['error' => 'Invalid action']);
        exit;
        
    } catch (Exception $e) {
        echo json_encode(['error' => 'Server error occurred']);
        exit;
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}
?>

