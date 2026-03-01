<?php
require_once __DIR__ . '/../../core/includes/secure_config.php';

class AdvancedAIProvider {
    private $grok_api_key;
    private $openrouter_api_key;
    private $site_url;
    private $site_name;
    
    public function __construct() {
        $this->grok_api_key = SecureConfig::getGrokApiKey();
        $this->openrouter_api_key = SecureConfig::getOpenRouterApiKey();
        $this->site_url = SecureConfig::getSiteUrl();
        $this->site_name = SecureConfig::getSiteName();
        
        if (empty($this->grok_api_key) && empty($this->openrouter_api_key)) {
            error_log("Warning: No AI API keys configured. Check .env file.");
        }
    }
    
    private $models = [
        'grok-compound' => [
            'name' => 'Grok Compound',
            'description' => 'Advanced reasoning and analysis',
            'provider' => 'groq',
            'endpoint' => 'https://api.groq.com/openai/v1/chat/completions',
            'model' => 'groq/compound',
            'limits' => ['rpm' => 30, 'rpd' => 250, 'tpm' => 70000],
            'features' => ['reasoning', 'analysis', 'complex_queries']
        ],
        'mistral-devstral' => [
            'name' => 'Mistral Devstral',
            'description' => 'Mistral coding AI via OpenRouter (Free)',
            'provider' => 'openrouter',
            'endpoint' => 'https://openrouter.ai/api/v1/chat/completions',
            'model' => 'mistralai/devstral-2512:free',
            'limits' => ['rpm' => 20, 'rpd' => 200, 'context' => '128K'],
            'features' => ['free', 'coding', 'reasoning', 'analysis']
        ],
        'nvidia-nemotron' => [
            'name' => 'NVIDIA Nemotron',
            'description' => 'NVIDIA 30B AI via OpenRouter (Free)',
            'provider' => 'openrouter',
            'endpoint' => 'https://openrouter.ai/api/v1/chat/completions',
            'model' => 'nvidia/nemotron-3-nano-30b-a3b:free',
            'limits' => ['rpm' => 20, 'rpd' => 200, 'context' => '32K'],
            'features' => ['free', 'fast', 'analysis']
        ],
        'xiaomi-mimo' => [
            'name' => 'Xiaomi MiMo',
            'description' => 'Xiaomi flash AI via OpenRouter (Free)',
            'provider' => 'openrouter',
            'endpoint' => 'https://openrouter.ai/api/v1/chat/completions',
            'model' => 'xiaomi/mimo-v2-flash:free',
            'limits' => ['rpm' => 20, 'rpd' => 200, 'context' => '32K'],
            'features' => ['free', 'fast', 'multilingual']
        ],
        'allen-olmo' => [
            'name' => 'Allen OLMo Think',
            'description' => 'Allen AI 32B thinking model (Free)',
            'provider' => 'openrouter',
            'endpoint' => 'https://openrouter.ai/api/v1/chat/completions',
            'model' => 'allenai/olmo-3.1-32b-think:free',
            'limits' => ['rpm' => 20, 'rpd' => 200, 'context' => '32K'],
            'features' => ['free', 'reasoning', 'thinking', 'analysis']
        ],
        'local-python' => [
            'name' => 'Local Python AI',
            'description' => 'Fast local processing',
            'provider' => 'local',
            'endpoint' => 'local',
            'model' => 'advanced_ai_engine.py',
            'limits' => ['unlimited' => true],
            'features' => ['fast', 'local', 'privacy']
        ]
    ];
    
    public function getAvailableModels() {
        return $this->models;
    }
    
    public function generateAdvancedResponse($prompt, $model = 'local-python', $context = []) {
        try {
            switch ($model) {
                case 'grok-compound':
                    if (empty($this->grok_api_key)) {
                        throw new Exception("Grok API key not configured");
                    }
                    return $this->callGrokAPI($prompt, $context);
                    
                case 'mistral-devstral':
                    return $this->callOpenRouterAPI($prompt, $context, 'mistralai/devstral-2512:free');
                    
                case 'nvidia-nemotron':
                    return $this->callOpenRouterAPI($prompt, $context, 'nvidia/nemotron-3-nano-30b-a3b:free');
                    
                case 'xiaomi-mimo':
                    return $this->callOpenRouterAPI($prompt, $context, 'xiaomi/mimo-v2-flash:free');
                    
                case 'allen-olmo':
                    return $this->callOpenRouterAPI($prompt, $context, 'allenai/olmo-3.1-32b-think:free');
                    
                case 'local-python':
                default:
                    return $this->simpleLocalFallback($prompt, $context);
            }
        } catch (Exception $e) {
            // Fallback to simple local processing on error
            if ($model !== 'local-python') {
                error_log("AI Model Error ($model): " . $e->getMessage());
                return $this->simpleLocalFallback($prompt, $context);
            } else {
                return [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'model' => $model,
                    'provider' => 'error'
                ];
            }
        }
    }
    
    private function callGrokAPI($prompt, $context) {
        if (empty($this->grok_api_key)) {
            throw new Exception("Grok API key not configured");
        }
        
        $system_prompt = $this->buildSystemPrompt($context);
        
        $payload = [
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $system_prompt
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 4000,
            'top_p' => 0.9,
            'stream' => false
        ];
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->grok_api_key
        ];
        
        $response = $this->makeAPICall('https://api.groq.com/openai/v1/chat/completions', $payload, $headers);
        
        if ($response && isset($response['choices'][0]['message']['content'])) {
            return [
                'success' => true,
                'response' => $response['choices'][0]['message']['content'],
                'model' => 'Grok Compound',
                'tokens_used' => $response['usage']['total_tokens'] ?? 0,
                'provider' => 'groq'
            ];
        }
        
        throw new Exception("Invalid response from Grok API");
    }
    
    private function callOpenRouterAPI($prompt, $context, $model) {
        if (empty($this->openrouter_api_key)) {
            throw new Exception("OpenRouter API key not configured");
        }
        
        $system_prompt = $this->buildSystemPrompt($context);
        
        $payload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $system_prompt
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 4000,
            'top_p' => 0.9,
            'stream' => false
        ];
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->openrouter_api_key,
            'HTTP-Referer: ' . $this->site_url,
            'X-Title: ' . $this->site_name
        ];
        
        $response = $this->makeAPICall('https://openrouter.ai/api/v1/chat/completions', $payload, $headers);
        
        if ($response && isset($response['choices'][0]['message']['content'])) {
            return [
                'success' => true,
                'response' => $response['choices'][0]['message']['content'],
                'model' => $model,
                'tokens_used' => $response['usage']['total_tokens'] ?? 0,
                'provider' => 'openrouter',
                'reasoning' => $response['choices'][0]['message']['reasoning'] ?? null
            ];
        }
        
        throw new Exception("Invalid response from OpenRouter API");
    }
    
    private function callLocalPythonAI($prompt, $context) {
        // Try different Python commands
        $python_commands = ['py', 'python', 'python3'];
        $python_script = dirname(__FILE__) . '/../advanced_ai_engine.py';
        
        if (!file_exists($python_script)) {
            throw new Exception("Python AI engine script not found");
        }
        
        $input_data = [
            'query' => $prompt,
            'context' => $context,
            'action' => 'advanced_analysis'
        ];
        
        $input_json = json_encode($input_data);
        $python_found = false;
        $output = null;
        $error = null;
        
        foreach ($python_commands as $cmd) {
            try {
                $command = "$cmd \"$python_script\"";
                $descriptorspec = [
                    0 => ["pipe", "r"],  // stdin
                    1 => ["pipe", "w"],  // stdout
                    2 => ["pipe", "w"]   // stderr
                ];
                
                $process = proc_open($command, $descriptorspec, $pipes);
                
                if (is_resource($process)) {
                    fwrite($pipes[0], $input_json);
                    fclose($pipes[0]);
                    
                    $output = stream_get_contents($pipes[1]);
                    $error = stream_get_contents($pipes[2]);
                    
                    fclose($pipes[1]);
                    fclose($pipes[2]);
                    
                    $return_code = proc_close($process);
                    
                    if ($return_code === 0 && !empty($output)) {
                        $python_found = true;
                        break;
                    }
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        
        if (!$python_found || empty($output)) {
            return [
                'success' => true,
                'response' => "Local analysis completed",
                'model' => 'Local Python AI',
                'provider' => 'local',
                'note' => 'Python AI engine encountered an error.'
            ];
        }
        
        try {
            $result = json_decode($output, true);
            if ($result && isset($result['success'])) {
                return [
                    'success' => $result['success'],
                    'response' => $result['response'] ?? 'Local analysis completed',
                    'model' => 'Local Python AI',
                    'provider' => 'local',
                    'insights' => $result['insights'] ?? null
                ];
            }
        } catch (Exception $e) {
            // JSON decode error
        }
        
        return [
            'success' => true,
            'response' => "Advanced analysis encountered an error.",
            'model' => 'Local Python AI',
            'provider' => 'local'
        ];
    }
    
    private function buildSystemPrompt($context) {
        $prompt = "You are an advanced AI assistant for an educational feedback system called Student Survey System. ";
        $prompt .= "You have access to real data about student complaints, suggestions, survey ratings, and teacher performance. ";
        $prompt .= "Analyze this data and provide detailed, actionable insights. Always reference specific data points in your response.\n\n";
        
        if (!empty($context)) {
            // Add system statistics
            if (isset($context['stats'])) {
                $stats = $context['stats'];
                $prompt .= "=== SYSTEM STATISTICS ===\n";
                if (isset($stats['complaints'])) {
                    $prompt .= "- Total Complaints: {$stats['complaints']['total_complaints']}, Pending: {$stats['complaints']['pending_complaints']}\n";
                }
                if (isset($stats['suggestions'])) {
                    $prompt .= "- Total Suggestions: {$stats['suggestions']['total_suggestions']}, Pending: {$stats['suggestions']['pending_suggestions']}\n";
                }
                if (isset($stats['users'])) {
                    $prompt .= "- Users: {$stats['users']['total_users']} total ({$stats['users']['students']} students, {$stats['users']['teachers']} teachers)\n";
                }
                $prompt .= "\n";
            }
            
            // Add survey statistics
            if (isset($context['survey_stats']) && !empty($context['survey_stats'])) {
                $ss = $context['survey_stats'];
                $prompt .= "=== SURVEY RATINGS DATA ===\n";
                $prompt .= "- Average Rating: " . round($ss['avg_rating'] ?? 0, 2) . "/5\n";
                $prompt .= "- Total Responses: " . ($ss['total_responses'] ?? 0) . "\n";
                $prompt .= "- Min Rating: " . ($ss['min_rating'] ?? 0) . ", Max Rating: " . ($ss['max_rating'] ?? 0) . "\n";
                $prompt .= "- Rating Std Dev: " . round($ss['rating_std'] ?? 0, 2) . "\n\n";
            }
            
            // Add teacher statistics  
            if (isset($context['teacher_stats']) && !empty($context['teacher_stats'])) {
                $ts = $context['teacher_stats'];
                $prompt .= "=== TEACHER PERFORMANCE DATA ===\n";
                $prompt .= "- Average Teacher Rating: " . round($ts['avg_teacher_rating'] ?? 0, 2) . "/5\n";
                $prompt .= "- Total Teacher Ratings: " . ($ts['total_teacher_ratings'] ?? 0) . "\n";
                $prompt .= "- Unique Teachers Rated: " . ($ts['unique_teachers'] ?? 0) . "\n\n";
            }
            
            // Add predictive insights
            if (isset($context['predictive_insights']) && !empty($context['predictive_insights'])) {
                $pi = $context['predictive_insights'];
                $prompt .= "=== TREND ANALYSIS ===\n";
                $prompt .= "- Trend Direction: " . ($pi['trend_direction'] ?? 'unknown') . "\n";
                $prompt .= "- Confidence: " . ($pi['confidence'] ?? 0) . "%\n\n";
            }
            
            // Add recent complaints with full text
            if (isset($context['recent_complaints']) && !empty($context['recent_complaints'])) {
                $prompt .= "=== RECENT COMPLAINTS (Full Text) ===\n";
                $count = 1;
                foreach (array_slice($context['recent_complaints'], 0, 10) as $complaint) {
                    $desc = $complaint['description'] ?? '';
                    if (!empty($desc)) {
                        $prompt .= "{$count}. [{$complaint['priority']}] {$desc}\n";
                        $count++;
                    }
                }
                $prompt .= "\n";
            }
            
            // Add complaints sentiment summary
            if (isset($context['complaints']['sentiment_summary'])) {
                $ss = $context['complaints']['sentiment_summary'];
                $prompt .= "Complaint Sentiment: Positive({$ss['positive']}), Negative({$ss['negative']}), Neutral({$ss['neutral']})\n";
            }
            
            // Add complaints top topics
            if (isset($context['complaints']['topics']['primary_topics'])) {
                $topics = array_slice(array_keys($context['complaints']['topics']['primary_topics']), 0, 5);
                if (!empty($topics)) {
                    $prompt .= "Top Complaint Keywords: " . implode(", ", $topics) . "\n\n";
                }
            }
            
            // Add recent suggestions with full text
            if (isset($context['recent_suggestions']) && !empty($context['recent_suggestions'])) {
                $prompt .= "=== RECENT SUGGESTIONS (Full Text) ===\n";
                $count = 1;
                foreach (array_slice($context['recent_suggestions'], 0, 10) as $suggestion) {
                    $desc = $suggestion['description'] ?? '';
                    if (!empty($desc)) {
                        $prompt .= "{$count}. [{$suggestion['status']}] {$desc}\n";
                        $count++;
                    }
                }
                $prompt .= "\n";
            }
            
            // Add suggestions sentiment summary
            if (isset($context['suggestions']['sentiment_summary'])) {
                $ss = $context['suggestions']['sentiment_summary'];
                $prompt .= "Suggestion Sentiment: Positive({$ss['positive']}), Negative({$ss['negative']}), Neutral({$ss['neutral']})\n";
            }
            
            // Add recommendations
            if (isset($context['recommendations']) && !empty($context['recommendations'])) {
                $prompt .= "\n=== CURRENT RECOMMENDATIONS ===\n";
                foreach (array_slice($context['recommendations'], 0, 5) as $rec) {
                    $prompt .= "- [{$rec['priority_score']}] {$rec['title']}: {$rec['description']}\n";
                }
                $prompt .= "\n";
            }
        }
        
        $prompt .= "\n=== INSTRUCTIONS ===\n";
        $prompt .= "Based on the above data, provide a comprehensive answer to the user's question. ";
        $prompt .= "Include specific numbers and data points. Be helpful and actionable. ";
        $prompt .= "Format your response with clear sections using markdown when appropriate.";
        
        return $prompt;
    }
    
    private function makeAPICall($url, $data, $headers) {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL Error: $error");
        }
        
        if ($http_code !== 200) {
            throw new Exception("HTTP Error: $http_code - $response");
        }
        
        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON Decode Error: " . json_last_error_msg());
        }
        
        return $decoded;
    }
    
    private function simpleLocalFallback($prompt, $context) {
        // Enhanced local analysis using the AI engine
        require_once __DIR__ . '/AIInsightsEngine.php';
        require_once __DIR__ . '/../../core/includes/config.php';
        
        global $conn;
        $ai_engine = new AIInsightsEngine($conn);
        
        $prompt_lower = strtolower($prompt);
        $response = "";
        
        // Detect query intent and provide relevant insights
        if (strpos($prompt_lower, 'rating') !== false || strpos($prompt_lower, 'score') !== false) {
            $insights = $ai_engine->generateInsights();
            $stats = $insights['survey_stats'] ?? [];
            $avg = round($stats['avg_rating'] ?? 0, 2);
            $total = $stats['total_responses'] ?? 0;
            $response = "📊 **Rating Analysis**: The current average rating is **{$avg}/5** based on {$total} survey responses. ";
            if ($avg >= 4) {
                $response .= "This indicates excellent satisfaction levels!";
            } elseif ($avg >= 3) {
                $response .= "This shows good satisfaction with room for improvement.";
            } else {
                $response .= "This suggests attention is needed to improve satisfaction.";
            }
        }
        elseif (strpos($prompt_lower, 'complaint') !== false) {
            $complaints = $ai_engine->analyzeComplaints();
            $total = $complaints['total_count'] ?? 0;
            $negative = $complaints['sentiment_summary']['negative'] ?? 0;
            $topics = array_slice(array_keys($complaints['topics']['primary_topics'] ?? []), 0, 3);
            $response = "⚠️ **Complaint Analysis**: Found **{$total} complaints**. ";
            $response .= "{$negative} have negative sentiment. ";
            if (!empty($topics)) {
                $response .= "Top issues: " . implode(", ", $topics) . ". ";
            }
            $response .= "Consider addressing the most mentioned topics to improve satisfaction.";
        }
        elseif (strpos($prompt_lower, 'suggestion') !== false) {
            $suggestions = $ai_engine->analyzeSuggestions();
            $total = $suggestions['total_count'] ?? 0;
            $positive = $suggestions['sentiment_summary']['positive'] ?? 0;
            $response = "💡 **Suggestion Analysis**: Received **{$total} suggestions**. ";
            $response .= "{$positive} have positive sentiment, showing constructive feedback from users.";
        }
        elseif (strpos($prompt_lower, 'trend') !== false || strpos($prompt_lower, 'pattern') !== false) {
            $insights = $ai_engine->generateInsights();
            $predictive = $insights['predictive_insights'] ?? [];
            $direction = $predictive['trend_direction'] ?? 'stable';
            $confidence = $predictive['confidence'] ?? 0;
            $response = "📈 **Trend Analysis**: Current trend direction is **{$direction}** with {$confidence}% confidence. ";
            if ($direction === 'improving') {
                $response .= "Great progress! Continue current practices.";
            } elseif ($direction === 'declining') {
                $response .= "Action needed: investigate causes and implement improvements.";
            } else {
                $response .= "Maintain current standards and look for improvement opportunities.";
            }
        }
        elseif (strpos($prompt_lower, 'recommendation') !== false || strpos($prompt_lower, 'advice') !== false) {
            $insights = $ai_engine->generateInsights();
            $recommendations = $insights['recommendations'] ?? [];
            $response = "🎯 **Recommendations**: Found **" . count($recommendations) . "** active recommendations:\n\n";
            foreach (array_slice($recommendations, 0, 3) as $rec) {
                $response .= "• **{$rec['title']}**: {$rec['description']} (Priority: {$rec['priority_score']})\n";
            }
        }
        elseif (strpos($prompt_lower, 'teacher') !== false || strpos($prompt_lower, 'instructor') !== false) {
            $insights = $ai_engine->generateInsights();
            $teacher = $insights['teacher_stats'] ?? [];
            $avg = round($teacher['avg_teacher_rating'] ?? 0, 2);
            $total = $teacher['total_teacher_ratings'] ?? 0;
            $response = "👨‍🏫 **Teacher Analysis**: Average teacher rating is **{$avg}/5** based on {$total} ratings. ";
            if ($avg >= 4) {
                $response .= "Excellent teaching quality observed!";
            } else {
                $response .= "Consider providing additional training or support.";
            }
        }
        elseif (strpos($prompt_lower, 'summary') !== false || strpos($prompt_lower, 'overview') !== false) {
            $insights = $ai_engine->generateInsights();
            $complaints = $insights['complaints']['total_count'] ?? 0;
            $suggestions = $insights['suggestions']['total_count'] ?? 0;
            $confidence = $insights['analysis_metadata']['confidence_level'] ?? 0;
            $response = "📋 **System Overview**:\n\n";
            $response .= "• Total Complaints: {$complaints}\n";
            $response .= "• Total Suggestions: {$suggestions}\n";
            $response .= "• AI Confidence: {$confidence}%\n";
            $response .= "• Recommendations: " . count($insights['recommendations'] ?? []) . " active\n\n";
            $response .= "Use specific queries like 'ratings', 'complaints', 'trends' for detailed analysis.";
        }
        else {
            // General response with helpful info
            $insights = $ai_engine->generateInsights();
            $response = "🤖 **AI Assistant**: I can help you analyze:\n\n";
            $response .= "• **Ratings** - Survey satisfaction scores\n";
            $response .= "• **Complaints** - Issues and concerns\n";
            $response .= "• **Suggestions** - User recommendations\n";
            $response .= "• **Trends** - Patterns over time\n";
            $response .= "• **Teachers** - Instructor performance\n";
            $response .= "• **Summary** - Overall system overview\n\n";
            $response .= "Ask about any of these topics for detailed insights!";
        }
        
        return [
            'success' => true,
            'response' => $response,
            'model' => 'Local AI Engine',
            'tokens_used' => str_word_count($response),
            'provider' => 'local-enhanced',
            'sentiment' => 'neutral'
        ];
    }
    
    public function answerCustomQuery($query, $model = 'local-python', $context = []) {
        return $this->generateAdvancedResponse($query, $model, $context);
    }
}
?>