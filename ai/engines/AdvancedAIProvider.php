<?php
require_once '../includes/secure_config.php';

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
        'sonoma-sky' => [
            'name' => 'Sonoma Sky Alpha',
            'description' => 'Advanced reasoning via OpenRouter',
            'provider' => 'openrouter',
            'endpoint' => 'https://openrouter.ai/api/v1/chat/completions',
            'model' => 'openrouter/sonoma-sky-alpha',
            'limits' => ['unlimited' => true],
            'features' => ['unlimited', 'reasoning', 'coding', 'analysis']
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
                    
                case 'sonoma-sky':
                    return $this->callOpenRouterAPI($prompt, $context, 'openrouter/sonoma-sky-alpha');
                    
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
                'model' => 'OpenRouter GPT-4O',
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
        $prompt = "You are an advanced AI assistant for an educational feedback system. ";
        $prompt .= "Analyze data and provide insights about student feedback, complaints, and suggestions. ";
        
        if (!empty($context)) {
            $prompt .= "Context data available:\n";
            
            if (isset($context['stats'])) {
                $stats = $context['stats'];
                $prompt .= "System Statistics:\n";
                if (isset($stats['complaints'])) {
                    $prompt .= "- Complaints: {$stats['complaints']['total_complaints']} total, {$stats['complaints']['pending_complaints']} pending\n";
                }
                if (isset($stats['suggestions'])) {
                    $prompt .= "- Suggestions: {$stats['suggestions']['total_suggestions']} total, {$stats['suggestions']['pending_suggestions']} pending\n";
                }
                if (isset($stats['users'])) {
                    $prompt .= "- Users: {$stats['users']['total_users']} total ({$stats['users']['students']} students, {$stats['users']['teachers']} teachers)\n";
                }
            }
            
            if (isset($context['recent_complaints']) && !empty($context['recent_complaints'])) {
                $prompt .= "\nRecent Complaints:\n";
                foreach (array_slice($context['recent_complaints'], 0, 5) as $complaint) {
                    $prompt .= "- [{$complaint['priority']}] {$complaint['subject']}: {$complaint['description']}\n";
                }
            }
            
            if (isset($context['recent_suggestions']) && !empty($context['recent_suggestions'])) {
                $prompt .= "\nRecent Suggestions:\n";
                foreach (array_slice($context['recent_suggestions'], 0, 5) as $suggestion) {
                    $prompt .= "- [{$suggestion['status']}] {$suggestion['subject']}: {$suggestion['description']}\n";
                }
            }
            
            if (isset($context['trending_issues']) && !empty($context['trending_issues'])) {
                $prompt .= "\nTrending Issues:\n";
                foreach (array_slice($context['trending_issues'], 0, 3) as $issue) {
                    $prompt .= "- {$issue['category']}: {$issue['complaint_count']} complaints (Priority: {$issue['avg_priority_score']})\n";
                }
            }
        }
        
        $prompt .= "\nPlease provide helpful, actionable insights and recommendations.";
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
        // Simple rule-based local analysis as fallback
        $keywords = ['complaint', 'problem', 'issue', 'broken', 'not working'];
        $sentiment = 'neutral';
        
        foreach ($keywords as $keyword) {
            if (stripos($prompt, $keyword) !== false) {
                $sentiment = 'negative';
                break;
            }
        }
        
        $response = "Local analysis complete. ";
        if ($sentiment === 'negative') {
            $response .= "Issue detected in query. This appears to be a concern that should be addressed.";
        } else {
            $response .= "Query processed successfully. This appears to be a general inquiry or suggestion.";
        }
        
        return [
            'success' => true,
            'response' => $response,
            'model' => 'Local Fallback',
            'tokens_used' => str_word_count($response),
            'provider' => 'local-simple',
            'sentiment' => $sentiment
        ];
    }
    
    public function answerCustomQuery($query, $model = 'local-python', $context = []) {
        return $this->generateAdvancedResponse($query, $model, $context);
    }
}
?>