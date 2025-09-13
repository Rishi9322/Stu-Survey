<?php
require_once '../../core/includes/config.php';
require_once 'AdvancedAIProvider.php';

/**
 * Database-C            $stmt = $this->pdo->prepare("SELECT 
                type,
                COUNT(*) as item_count,
                GROUP_CONCAT(DISTINCT subject SEPARATOR '; ') as common_subjects
            FROM suggestions_complaints 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY type 
            ORDER BY item_count DESC");ystem
 * Reads actual data from database and provides intelligent analysis
 */
class DatabaseAI {
    private $pdo;
    private $ai_provider;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        $this->ai_provider = new AdvancedAIProvider();
    }
    
    /**
     * Get database statistics for AI context
     */
    public function getDatabaseStats() {
        try {
            $stats = [];
            
            // Complaints statistics
            $stmt = $this->pdo->query("SELECT 
                COUNT(*) as total_complaints,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_complaints,
                COUNT(CASE WHEN status = 'resolved' THEN 1 END) as resolved_complaints,
                COUNT(CASE WHEN type = 'complaint' THEN 1 END) as complaints_only,
                COUNT(CASE WHEN type = 'suggestion' THEN 1 END) as suggestions_only
            FROM suggestions_complaints");
            $stats['complaints'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Suggestions statistics
            $stmt = $this->pdo->query("SELECT 
                COUNT(*) as total_suggestions,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_suggestions,
                COUNT(CASE WHEN status = 'resolved' THEN 1 END) as resolved_suggestions,
                COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress_suggestions
            FROM suggestions_complaints WHERE type = 'suggestion'");
            $stats['suggestions'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Users statistics
            $stmt = $this->pdo->query("SELECT 
                COUNT(*) as total_users,
                COUNT(CASE WHEN role = 'student' THEN 1 END) as students,
                COUNT(CASE WHEN role = 'teacher' THEN 1 END) as teachers,
                COUNT(CASE WHEN role = 'admin' THEN 1 END) as admins
            FROM users");
            $stats['users'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $stats;
        } catch (Exception $e) {
            error_log("Database stats error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get recent complaints for analysis
     */
    public function getRecentComplaints($limit = 20) {
        try {
            $stmt = $this->pdo->prepare("SELECT 
                id, subject, description, status, type, 
                created_at, submitted_by_role 
            FROM suggestions_complaints 
            WHERE type = 'complaint'
            ORDER BY created_at DESC 
            LIMIT ?");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Recent complaints error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get recent suggestions for analysis
     */
    public function getRecentSuggestions($limit = 20) {
        try {
            $stmt = $this->pdo->prepare("SELECT 
                id, subject, description, status, type, 
                created_at, submitted_by_role 
            FROM suggestions_complaints 
            WHERE type = 'suggestion'
            ORDER BY created_at DESC 
            LIMIT ?");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Recent suggestions error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get trending issues by analyzing complaint patterns
     */
    public function getTrendingIssues($days = 30) {
        try {
            $stmt = $this->pdo->prepare("SELECT 
                category,
                COUNT(*) as complaint_count,
                AVG(CASE priority 
                    WHEN 'high' THEN 3 
                    WHEN 'medium' THEN 2 
                    WHEN 'low' THEN 1 
                END) as avg_priority_score,
                GROUP_CONCAT(DISTINCT subject SEPARATOR '; ') as common_subjects
            FROM complaints 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY category 
            ORDER BY complaint_count DESC, avg_priority_score DESC");
            $stmt->execute([$days]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Trending issues error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get comprehensive database context for AI analysis
     */
    public function getDatabaseContext() {
        return [
            'stats' => $this->getDatabaseStats(),
            'recent_complaints' => $this->getRecentComplaints(10),
            'recent_suggestions' => $this->getRecentSuggestions(10),
            'trending_issues' => $this->getTrendingIssues(30)
        ];
    }
    
    /**
     * Analyze database data with AI
     */
    public function analyzeWithAI($query, $model = 'grok-compound') {
        $context = $this->getDatabaseContext();
        
        $enhanced_query = "Based on the actual database data provided, please analyze: " . $query;
        
        return $this->ai_provider->generateAdvancedResponse($enhanced_query, $model, $context);
    }
    
    /**
     * Generate insights from database patterns
     */
    public function generateInsights($model = 'grok-compound') {
        $context = $this->getDatabaseContext();
        
        $query = "Analyze the current database data and provide key insights about:
        1. Most critical issues that need immediate attention
        2. Patterns in student feedback and complaints
        3. Success rate of implemented suggestions
        4. Recommendations for improving the education system
        5. Priority areas for administrative focus
        
        Please provide specific, actionable insights based on the actual data.";
        
        return $this->ai_provider->generateAdvancedResponse($query, $model, $context);
    }
    
    /**
     * Search and analyze specific data
     */
    public function searchAndAnalyze($search_term, $model = 'local-python') {
        try {
            // Search suggestions and complaints
            $stmt = $this->pdo->prepare("SELECT 
                type, id, subject, description, 
                status, submitted_by_role, created_at 
            FROM suggestions_complaints 
            WHERE subject LIKE ? OR description LIKE ?
            ORDER BY created_at DESC
            LIMIT 15");
            
            $search_pattern = "%$search_term%";
            $stmt->execute([$search_pattern, $search_pattern]);
            $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($search_results)) {
                return [
                    'success' => true,
                    'response' => "No data found matching '$search_term' in the database.",
                    'model' => 'Database Search',
                    'provider' => 'local'
                ];
            }
            
            $query = "Analyze the following search results for '$search_term' and provide insights:
            
            What patterns do you see?
            What are the main themes?
            What recommendations would you make?
            How serious are these issues?";
            
            $context = ['search_results' => $search_results, 'search_term' => $search_term];
            
            return $this->ai_provider->generateAdvancedResponse($query, $model, $context);
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Search analysis failed: ' . $e->getMessage(),
                'model' => 'Database Search',
                'provider' => 'local'
            ];
        }
    }
    
    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics() {
        try {
            $stmt = $this->pdo->query("SELECT 
                'Resolution Rate' as metric,
                CONCAT(
                    ROUND(
                        (COUNT(CASE WHEN status = 'resolved' THEN 1 END) * 100.0 / COUNT(*)), 
                        1
                    ), 
                    '%'
                ) as value,
                'complaints' as category
            FROM suggestions_complaints WHERE type = 'complaint'
            UNION ALL
            SELECT 
                'Implementation Rate' as metric,
                CONCAT(
                    ROUND(
                        (COUNT(CASE WHEN status = 'resolved' THEN 1 END) * 100.0 / COUNT(*)), 
                        1
                    ), 
                    '%'
                ) as value,
                'suggestions' as category
            FROM suggestions_complaints WHERE type = 'suggestion'
            UNION ALL
            SELECT 
                'Average Response Time' as metric,
                CONCAT(
                    ROUND(AVG(DATEDIFF(NOW(), created_at)), 1), 
                    ' days'
                ) as value,
                'overall' as category
            FROM suggestions_complaints 
            WHERE status = 'resolved'");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Performance metrics error: " . $e->getMessage());
            return [];
        }
    }
}
?>
