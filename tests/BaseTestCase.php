<?php
namespace Tests;

use PHPUnit\Framework\TestCase;

/**
 * Base Test Case Class
 * Provides common functionality for all tests
 */
abstract class BaseTestCase extends TestCase
{
    protected $pdo;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Get database connection from bootstrap
        $this->pdo = TestDatabase::getConnection();
        $this->pdo->exec('USE ' . DB_NAME);
        
        // Truncate tables before each test
        TestDatabase::truncateAllTables();
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
    }
    
    /**
     * Helper: Insert test user
     */
    protected function createTestUser($data = []) {
        $defaults = [
            'name' => 'Test User ' . uniqid(),
            'email' => 'user_' . uniqid() . '@test.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
            'role' => 'student',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $user = array_merge($defaults, $data);
        
        $stmt = $this->pdo->prepare('
            INSERT INTO users (name, email, password, role, is_active, created_at)
            VALUES (:name, :email, :password, :role, :is_active, :created_at)
        ');
        
        $stmt->execute($user);
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Helper: Insert test survey
     */
    protected function createTestSurvey($data = []) {
        $defaults = [
            'title' => 'Test Survey',
            'description' => 'Test Description',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $survey = array_merge($defaults, $data);
        
        $stmt = $this->pdo->prepare('
            INSERT INTO surveys (title, description, is_active, created_at)
            VALUES (:title, :description, :is_active, :created_at)
        ');
        
        $stmt->execute($survey);
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Helper: Insert test question
     */
    protected function createTestQuestion($surveyId, $data = []) {
        $defaults = [
            'survey_id' => $surveyId,
            'question' => 'How satisfied are you?',
            'question_type' => 'rating',
            'is_active' => 1,
            'order' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $question = array_merge($defaults, $data);
        
        $stmt = $this->pdo->prepare('
            INSERT INTO questions (survey_id, question, question_type, is_active, `order`, created_at)
            VALUES (:survey_id, :question, :question_type, :is_active, :order, :created_at)
        ');
        
        $stmt->execute($question);
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Helper: Insert test response
     */
    protected function createTestResponse($data = []) {
        $defaults = [
            'survey_id' => null,
            'student_id' => null,
            'teacher_id' => null,
            'rating' => 5,
            'submitted_at' => date('Y-m-d H:i:s')
        ];
        
        $response = array_merge($defaults, $data);
        
        // Create survey if not provided
        if ($response['survey_id'] === null) {
            $response['survey_id'] = $this->createTestSurvey();
        }
        
        // Create or reuse student if numeric ID provided
        if ($response['student_id'] !== null && is_numeric($response['student_id']) && $response['student_id'] > 0) {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) as c FROM users WHERE id = ?');
            $stmt->execute([$response['student_id']]);
            if ($stmt->fetch()['c'] == 0) {
                $response['student_id'] = $this->createTestUser();
            }
        }
        
        $stmt = $this->pdo->prepare('
            INSERT INTO responses (survey_id, student_id, teacher_id, rating, submitted_at)
            VALUES (:survey_id, :student_id, :teacher_id, :rating, :submitted_at)
        ');
        
        $stmt->execute($response);
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Helper: Insert test complaint
     */
    protected function createTestComplaint($data = []) {
        $defaults = [
            'subject' => 'Test Complaint',
            'description' => 'Test Description',
            'type' => 'complaint',
            'submitted_by_role' => 'student',
            'status' => 'open',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $complaint = array_merge($defaults, $data);
        
        $stmt = $this->pdo->prepare('
            INSERT INTO suggestions_complaints (subject, description, type, submitted_by_role, status, created_at)
            VALUES (:subject, :description, :type, :submitted_by_role, :status, :created_at)
        ');
        
        $stmt->execute($complaint);
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Helper: Assert database has record
     */
    protected function assertDatabaseHas($table, $where = []) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM $table WHERE " . 
            implode(' AND ', array_map(function($k) { return "$k = ?"; }, array_keys($where))));
        
        $stmt->execute(array_values($where));
        $result = $stmt->fetch();
        
        $this->assertGreaterThan(0, $result['count'], "Record not found in $table");
    }
    
    /**
     * Helper: Assert database doesn't have record
     */
    protected function assertDatabaseMissing($table, $where = []) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM $table WHERE " . 
            implode(' AND ', array_map(function($k) { return "$k = ?"; }, array_keys($where))));
        
        $stmt->execute(array_values($where));
        $result = $stmt->fetch();
        
        $this->assertEquals(0, $result['count'], "Record found in $table");
    }
}
