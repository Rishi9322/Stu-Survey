<?php
// Enhanced AI Insights Engine with Python Integration
class AIInsightsEngine {
    private $conn;
    private $config;
    private $python_path = 'python'; // Will be auto-detected
    
    public function __construct($connection) {
        $this->conn = $connection;
        $this->detectPythonPath();
        $this->loadConfig();
    }
    
    private function detectPythonPath() {
        // Try different Python paths on Windows
        $possible_paths = [
            'python',  // Default
            'py',      // Python Launcher
            'python3', // Linux/Mac style
            'C:\\Python\\python.exe',
            'C:\\Python3\\python.exe',
            'C:\\Python313\\python.exe',  // Based on version 3.13.7
            'C:\\Users\\' . get_current_user() . '\\AppData\\Local\\Programs\\Python\\Python313\\python.exe'
        ];
        
        foreach ($possible_paths as $path) {
            $test_cmd = $path . ' --version 2>&1';
            $output = shell_exec($test_cmd);
            if ($output && strpos($output, 'Python') !== false && strpos($output, 'was not found') === false) {
                $this->python_path = $path;
                return;
            }
        }
        
        // If no Python found, keep default for fallback behavior
        $this->python_path = 'python';
    }
    
    private function loadConfig() {
        $config_file = __DIR__ . '/ai_config.json';
        if (file_exists($config_file)) {
            $this->config = json_decode(file_get_contents($config_file), true);
        } else {
            // Fallback configuration
            $this->config = [
                'sentiment_analysis' => [
                    'positive_patterns' => [
                        ['words' => ['good', 'great', 'excellent'], 'weight' => 2]
                    ],
                    'negative_patterns' => [
                        ['words' => ['bad', 'terrible'], 'weight' => 2]
                    ]
                ]
            ];
        }
    }
    
    // Enhanced sentiment analysis using Python engine
    public function analyzeSentiment($texts, $use_advanced = true) {
        if (!is_array($texts)) {
            $texts = [$texts];
        }
        
        if ($use_advanced && $this->isPythonAvailable()) {
            return $this->advancedSentimentAnalysis($texts);
        } else {
            return $this->basicSentimentAnalysis($texts);
        }
    }
    
    private function advancedSentimentAnalysis($texts) {
        $data = json_encode(['texts' => $texts]);
        $python_script = __DIR__ . '/ai_engine.py';
        
        // Execute Python script
        $command = escapeshellcmd($this->python_path) . ' ' . 
                  escapeshellarg($python_script) . ' ' . 
                  'sentiment_analysis ' . 
                  escapeshellarg($data);
        
        $output = shell_exec($command);
        
        if ($output) {
            $result = json_decode($output, true);
            if ($result && !isset($result['error'])) {
                return $this->formatSentimentResult($result);
            }
        }
        
        // Fallback to basic analysis
        return $this->basicSentimentAnalysis($texts);
    }
    
    private function formatSentimentResult($advanced_result) {
        return [
            'sentiment' => $advanced_result['overall_sentiment'] ?? 'neutral',
            'confidence' => $advanced_result['confidence'] ?? 50,
            'emotional_breakdown' => $advanced_result['emotional_breakdown'] ?? [],
            'key_phrases' => $advanced_result['key_phrases'] ?? [],
            'detailed_scores' => $advanced_result['sentiment_scores'] ?? []
        ];
    }
    
    private function basicSentimentAnalysis($texts) {
        $results = [];
        $summary = ['positive' => 0, 'negative' => 0, 'neutral' => 0];
        
        foreach ($texts as $text) {
            $combined_text = strtolower($text);
            
            $positive_score = 0;
            $negative_score = 0;
            
            // Use config-based analysis
            foreach ($this->config['sentiment_analysis']['positive_patterns'] as $pattern) {
                $weight = $pattern['weight'] ?? 1;
                if (isset($pattern['words'])) {
                    foreach ($pattern['words'] as $word) {
                        $positive_score += substr_count($combined_text, strtolower($word)) * $weight;
                    }
                }
            }
            
            foreach ($this->config['sentiment_analysis']['negative_patterns'] as $pattern) {
                $weight = $pattern['weight'] ?? 1;
                if (isset($pattern['words'])) {
                    foreach ($pattern['words'] as $word) {
                        $negative_score += substr_count($combined_text, strtolower($word)) * $weight;
                    }
                }
            }
            
            $total_score = $positive_score + $negative_score;
            
            if ($positive_score > $negative_score) {
                $sentiment = 'positive';
                $confidence = $total_score > 0 ? min(($positive_score / $total_score) * 100, 90) : 50;
            } elseif ($negative_score > $positive_score) {
                $sentiment = 'negative';
                $confidence = $total_score > 0 ? min(($negative_score / $total_score) * 100, 90) : 50;
            } else {
                $sentiment = 'neutral';
                $confidence = 50;
            }
            
            $results[] = [
                'text' => $text,
                'sentiment' => $sentiment,
                'confidence' => $confidence,
                'scores' => ['positive' => $positive_score, 'negative' => $negative_score]
            ];
            
            $summary[$sentiment]++;
        }
        
        return [
            'summary' => $summary,
            'details' => $results,
            'total_analyzed' => count($texts)
        ];
    }
    
    // Enhanced topic extraction
    public function extractTopics($texts) {
        if ($this->isPythonAvailable()) {
            return $this->advancedTopicExtraction($texts);
        } else {
            return $this->basicTopicExtraction($texts);
        }
    }
    
    private function advancedTopicExtraction($texts) {
        $data = json_encode(['texts' => $texts]);
        $python_script = __DIR__ . '/ai_engine.py';
        
        $command = escapeshellcmd($this->python_path) . ' ' . 
                  escapeshellarg($python_script) . ' ' . 
                  'topic_extraction ' . 
                  escapeshellarg($data);
        
        $output = shell_exec($command);
        
        if ($output) {
            $result = json_decode($output, true);
            if ($result && !isset($result['error'])) {
                return $result;
            }
        }
        
        return $this->basicTopicExtraction($texts);
    }
    
    private function basicTopicExtraction($texts) {
        $all_text = strtolower(implode(' ', $texts));
        
        // Basic keyword extraction
        $stop_words = ['the', 'is', 'at', 'which', 'on', 'and', 'a', 'to', 'are', 'as', 'was', 'with', 'for'];
        $words = preg_split('/\s+/', preg_replace('/[^\w\s]/', '', $all_text));
        $words = array_filter($words, function($word) use ($stop_words) {
            return strlen($word) > 3 && !in_array($word, $stop_words);
        });
        
        $word_count = array_count_values($words);
        arsort($word_count);
        
        return [
            'primary_topics' => array_slice($word_count, 0, 10, true),
            'category_breakdown' => [],
            'priority_issues' => []
        ];
    }
    
    // Predictive analytics
    public function generatePredictiveInsights() {
        $historical_data = $this->getHistoricalData();
        
        if ($this->isPythonAvailable() && count($historical_data) >= 5) {
            return $this->advancedPredictiveAnalysis($historical_data);
        } else {
            return $this->basicTrendAnalysis($historical_data);
        }
    }
    
    private function getHistoricalData() {
        $query = "SELECT 
                    DATE(created_at) as date,
                    AVG(rating) as rating,
                    COUNT(*) as response_count
                  FROM survey_responses 
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
                  GROUP BY DATE(created_at)
                  ORDER BY date ASC";
        
        $result = mysqli_query($this->conn, $query);
        $data = [];
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = [
                    'date' => $row['date'],
                    'rating' => floatval($row['rating']),
                    'count' => intval($row['response_count'])
                ];
            }
        }
        
        return $data;
    }
    
    private function advancedPredictiveAnalysis($historical_data) {
        $data = json_encode(['historical_data' => $historical_data]);
        $python_script = __DIR__ . '/ai_engine.py';
        
        $command = escapeshellcmd($this->python_path) . ' ' . 
                  escapeshellarg($python_script) . ' ' . 
                  'predictive_analysis ' . 
                  escapeshellarg($data);
        
        $output = shell_exec($command);
        
        if ($output) {
            $result = json_decode($output, true);
            if ($result && !isset($result['error'])) {
                return $result;
            }
        }
        
        return $this->basicTrendAnalysis($historical_data);
    }
    
    private function basicTrendAnalysis($historical_data) {
        if (count($historical_data) < 2) {
            return [
                'trend_direction' => 'insufficient_data',
                'confidence' => 0,
                'recommendations' => ['Collect more historical data for trend analysis']
            ];
        }
        
        $ratings = array_column($historical_data, 'rating');
        $recent_avg = array_sum(array_slice($ratings, -5)) / min(5, count($ratings));
        $overall_avg = array_sum($ratings) / count($ratings);
        
        $trend = $recent_avg - $overall_avg;
        
        if ($trend > 0.2) {
            return [
                'trend_direction' => 'improving',
                'confidence' => 70,
                'forecasted_metrics' => ['trend_value' => round($trend, 2)],
                'recommendations' => ['Continue current practices', 'Monitor for sustained improvement']
            ];
        } elseif ($trend < -0.2) {
            return [
                'trend_direction' => 'declining',
                'confidence' => 70,
                'forecasted_metrics' => ['trend_value' => round($trend, 2)],
                'recommendations' => ['Investigate causes of decline', 'Implement corrective measures']
            ];
        } else {
            return [
                'trend_direction' => 'stable',
                'confidence' => 60,
                'forecasted_metrics' => ['trend_value' => round($trend, 2)],
                'recommendations' => ['Maintain current standards', 'Look for improvement opportunities']
            ];
        }
    }
    
    private function isPythonAvailable() {
        $test_command = $this->python_path . ' --version 2>&1';
        $output = shell_exec($test_command);
        return strpos($output, 'Python') !== false;
    }
    
    // Optimized complaint analysis
    public function analyzeComplaints() {
        $query = "SELECT description, created_at FROM suggestions_complaints WHERE type = 'complaint' ORDER BY created_at DESC LIMIT 50";
        $result = mysqli_query($this->conn, $query);
        
        $texts = [];
        $complaints = [];
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $description = $row['description'] ?? '';
                $texts[] = $description;
                $complaints[] = [
                    'message' => $description,
                    'created_at' => $row['created_at'] ?? date('Y-m-d H:i:s')
                ];
            }
        }
        
        // Use enhanced sentiment analysis
        $sentiment_result = $this->analyzeSentiment($texts);
        $topics = $this->extractTopics($texts);
        
        // Calculate sentiment distribution
        $sentiment_summary = $this->calculateSentimentDistribution($texts);
        
        return [
            'complaints' => $complaints,
            'sentiment_analysis' => $sentiment_result,
            'sentiment_summary' => $sentiment_summary,
            'topics' => $topics,
            'total_count' => count($complaints),
            'analysis_timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    // Optimized suggestions analysis
    public function analyzeSuggestions() {
        $query = "SELECT description, created_at FROM suggestions_complaints WHERE type = 'suggestion' ORDER BY created_at DESC LIMIT 50";
        $result = mysqli_query($this->conn, $query);
        
        $texts = [];
        $suggestions = [];
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $description = $row['description'] ?? '';
                $texts[] = $description;
                $suggestions[] = [
                    'message' => $description,
                    'created_at' => $row['created_at'] ?? date('Y-m-d H:i:s')
                ];
            }
        }
        
        $sentiment_result = $this->analyzeSentiment($texts);
        $topics = $this->extractTopics($texts);
        $sentiment_summary = $this->calculateSentimentDistribution($texts);
        
        return [
            'suggestions' => $suggestions,
            'sentiment_analysis' => $sentiment_result,
            'sentiment_summary' => $sentiment_summary,
            'topics' => $topics,
            'total_count' => count($suggestions),
            'analysis_timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    private function calculateSentimentDistribution($texts) {
        $distribution = ['positive' => 0, 'negative' => 0, 'neutral' => 0];
        
        foreach ($texts as $text) {
            $sentiment = $this->analyzeSentiment([$text]);
            $sentiment_type = $sentiment['sentiment'] ?? 'neutral';
            $distribution[$sentiment_type]++;
        }
        
        return $distribution;
    }
    
    // Enhanced insights generation with predictive analytics
    public function generateInsights() {
        $complaints_analysis = $this->analyzeComplaints();
        $suggestions_analysis = $this->analyzeSuggestions();
        $predictive_insights = $this->generatePredictiveInsights();
        
        // Get comprehensive survey statistics
        $survey_stats = $this->getComprehensiveSurveyStats();
        $teacher_stats = $this->getTeacherPerformanceStats();
        
        // Generate intelligent recommendations
        $recommendations = $this->generateIntelligentRecommendations(
            $complaints_analysis, 
            $suggestions_analysis, 
            $survey_stats, 
            $teacher_stats,
            $predictive_insights
        );
        
        return [
            'complaints' => $complaints_analysis,
            'suggestions' => $suggestions_analysis,
            'survey_stats' => $survey_stats,
            'teacher_stats' => $teacher_stats,
            'predictive_insights' => $predictive_insights,
            'recommendations' => $recommendations,
            'analysis_metadata' => [
                'generated_at' => date('Y-m-d H:i:s'),
                'ai_engine_version' => '2.0',
                'analysis_scope' => '90_days',
                'confidence_level' => $this->calculateOverallConfidence($complaints_analysis, $suggestions_analysis)
            ]
        ];
    }
    
    private function getComprehensiveSurveyStats() {
        $query = "SELECT 
                    AVG(rating) as avg_rating,
                    COUNT(*) as total_responses,
                    COUNT(DISTINCT user_id) as unique_users,
                    MIN(rating) as min_rating,
                    MAX(rating) as max_rating,
                    STD(rating) as rating_std
                  FROM survey_responses
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
        
        $result = mysqli_query($this->conn, $query);
        $stats = mysqli_fetch_assoc($result);
        
        // Add trend analysis
        $trend_query = "SELECT 
                         AVG(rating) as avg_rating,
                         DATE(created_at) as response_date
                       FROM survey_responses 
                       WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                       GROUP BY DATE(created_at)
                       ORDER BY response_date ASC";
        
        $trend_result = mysqli_query($this->conn, $trend_query);
        $trend_data = [];
        
        if ($trend_result) {
            while ($row = mysqli_fetch_assoc($trend_result)) {
                $trend_data[] = [
                    'date' => $row['response_date'],
                    'rating' => floatval($row['avg_rating'])
                ];
            }
        }
        
        $stats['trend_data'] = $trend_data;
        return $stats;
    }
    
    private function getTeacherPerformanceStats() {
        $query = "SELECT 
                    AVG(rating) as avg_teacher_rating,
                    COUNT(*) as total_teacher_ratings,
                    COUNT(DISTINCT teacher_id) as unique_teachers
                  FROM teacher_ratings
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)";
        
        $result = mysqli_query($this->conn, $query);
        return mysqli_fetch_assoc($result);
    }
    
    private function calculateOverallConfidence($complaints_analysis, $suggestions_analysis) {
        $total_data_points = $complaints_analysis['total_count'] + $suggestions_analysis['total_count'];
        
        if ($total_data_points >= 50) return 85;
        if ($total_data_points >= 20) return 70;
        if ($total_data_points >= 10) return 55;
        return 40;
    }
    
    // Intelligent recommendation engine
    private function generateIntelligentRecommendations($complaints, $suggestions, $survey_stats, $teacher_stats, $predictive) {
        $recommendations = [];
        
        // Priority scoring system
        $priority_factors = [
            'urgency' => 0,
            'impact' => 0,
            'feasibility' => 0
        ];
        
        // Analyze complaint patterns with AI insights
        if ($complaints['total_count'] > 0) {
            $negative_ratio = ($complaints['sentiment_summary']['negative'] ?? 0) / $complaints['total_count'];
            
            if ($negative_ratio > 0.7) {
                $recommendations[] = [
                    'type' => 'critical',
                    'category' => 'Crisis Management',
                    'title' => 'Critical Negative Sentiment Alert',
                    'description' => "Urgent: {$complaints['sentiment_summary']['negative']} out of {$complaints['total_count']} complaints show negative sentiment.",
                    'action' => 'Implement immediate crisis response protocol',
                    'priority_score' => 95,
                    'estimated_impact' => 'high',
                    'timeline' => 'immediate'
                ];
            }
            
            // AI-powered topic analysis
            if (isset($complaints['topics']['primary_topics']) && !empty($complaints['topics']['primary_topics'])) {
                $top_issue = array_key_first($complaints['topics']['primary_topics']);
                $issue_frequency = $complaints['topics']['primary_topics'][$top_issue];
                
                $recommendations[] = [
                    'type' => 'improvement',
                    'category' => 'Process Improvement',
                    'title' => 'Address Primary Concern: ' . ucfirst($top_issue),
                    'description' => "'{$top_issue}' mentioned {$issue_frequency} times in recent complaints.",
                    'action' => "Launch targeted improvement initiative for {$top_issue}",
                    'priority_score' => 80,
                    'estimated_impact' => 'medium',
                    'timeline' => '2-4 weeks'
                ];
            }
        }
        
        // Predictive insights integration
        if (isset($predictive['trend_direction'])) {
            $trend = $predictive['trend_direction'];
            $confidence = $predictive['confidence'] ?? 0;
            
            if ($trend === 'declining' && $confidence > 70) {
                $recommendations[] = [
                    'type' => 'preventive',
                    'category' => 'Trend Reversal',
                    'title' => 'Declining Trend Intervention Required',
                    'description' => "Predictive analysis shows declining trend with {$confidence}% confidence.",
                    'action' => 'Implement trend reversal strategies immediately',
                    'priority_score' => 85,
                    'estimated_impact' => 'high',
                    'timeline' => '1-2 weeks',
                    'predicted_outcome' => $predictive['forecasted_metrics'] ?? []
                ];
            }
            
            if ($trend === 'improving' && $confidence > 75) {
                $recommendations[] = [
                    'type' => 'optimization',
                    'category' => 'Success Scaling',
                    'title' => 'Scale Successful Practices',
                    'description' => "Positive trend detected with {$confidence}% confidence. Opportunity to amplify success.",
                    'action' => 'Document and replicate successful practices',
                    'priority_score' => 70,
                    'estimated_impact' => 'medium',
                    'timeline' => '3-6 weeks'
                ];
            }
        }
        
        // Survey performance recommendations
        if ($survey_stats && isset($survey_stats['avg_rating'])) {
            $avg_rating = floatval($survey_stats['avg_rating']);
            $response_rate = $this->calculateResponseRate($survey_stats);
            
            if ($avg_rating < 2.5) {
                $recommendations[] = [
                    'type' => 'critical',
                    'category' => 'Performance Recovery',
                    'title' => 'Critical Rating Alert',
                    'description' => "Average rating ({$avg_rating}/5) is critically low.",
                    'action' => 'Execute comprehensive performance recovery plan',
                    'priority_score' => 100,
                    'estimated_impact' => 'critical',
                    'timeline' => 'immediate'
                ];
            }
            
            if ($response_rate < 30) {
                $recommendations[] = [
                    'type' => 'engagement',
                    'category' => 'Data Quality',
                    'title' => 'Low Response Rate',
                    'description' => "Only {$response_rate}% of users are providing feedback.",
                    'action' => 'Launch engagement campaign to increase survey participation',
                    'priority_score' => 60,
                    'estimated_impact' => 'low',
                    'timeline' => '4-8 weeks'
                ];
            }
        }
        
        // Sort by priority score
        usort($recommendations, function($a, $b) {
            return ($b['priority_score'] ?? 0) - ($a['priority_score'] ?? 0);
        });
        
        return array_slice($recommendations, 0, 10); // Return top 10 recommendations
    }
    
    private function calculateResponseRate($survey_stats) {
        $unique_users = $survey_stats['unique_users'] ?? 0;
        
        $total_users_query = "SELECT COUNT(*) as total FROM users WHERE role IN ('student', 'teacher')";
        $result = mysqli_query($this->conn, $total_users_query);
        $total_users = 0;
        
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $total_users = $row['total'] ?? 0;
        }
        
        return $total_users > 0 ? round(($unique_users / $total_users) * 100, 1) : 0;
    }
}
?>
