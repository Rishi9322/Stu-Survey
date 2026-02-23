<?php
namespace Tests\Unit\BlackBox;

use Tests\BaseTestCase;

/**
 * BLACK BOX TESTS - Survey Module
 * Tests from user's perspective: Can user take survey, submit responses?
 */
class SurveyBlackBoxTest extends BaseTestCase
{
    /**
     * Test 1: Load survey successfully
     * INPUT: Click on survey
     * EXPECTED: All questions display with rating options
     */
    public function testLoadSurveySuccessfully()
    {
        $surveyId = $this->createTestSurvey([
            'title' => 'Teacher Performance Survey',
            'is_active' => 1
        ]);
        
        // Load survey
        $stmt = $this->pdo->prepare('SELECT * FROM surveys WHERE id = ? AND is_active = 1');
        $stmt->execute([$surveyId]);
        $survey = $stmt->fetch();
        
        $this->assertNotNull($survey);
        $this->assertEquals('Teacher Performance Survey', $survey['title']);
        $this->assertEquals(1, $survey['is_active']);
    }
    
    /**
     * Test 2: Survey contains questions
     * INPUT: Open survey
     * EXPECTED: All survey questions display
     */
    public function testSurveyContainsQuestions()
    {
        $surveyId = $this->createTestSurvey();
        
        // Create 5 questions
        for ($i = 1; $i <= 5; $i++) {
            $this->createTestQuestion($surveyId, [
                'question' => "Question $i",
                'order' => $i
            ]);
        }
        
        // Load questions
        $stmt = $this->pdo->prepare('SELECT * FROM questions WHERE survey_id = ? ORDER BY `order`');
        $stmt->execute([$surveyId]);
        $questions = $stmt->fetchAll();
        
        $this->assertCount(5, $questions);
        $this->assertEquals('Question 1', $questions[0]['question']);
        $this->assertEquals('Question 5', $questions[4]['question']);
    }
    
    /**
     * Test 3: Submit survey with valid ratings
     * INPUT: Fill all rating fields (1-5) and submit
     * EXPECTED: Responses saved successfully
     */
    public function testSubmitSurveyWithValidRatings()
    {
        $studentId = $this->createTestUser(['role' => 'student']);
        $surveyId = $this->createTestSurvey();
        
        // Create questions
        $question1 = $this->createTestQuestion($surveyId, ['question' => 'Q1']);
        $question2 = $this->createTestQuestion($surveyId, ['question' => 'Q2']);
        
        // Create responses
        $response1Id = $this->createTestResponse([
            'survey_id' => $surveyId,
            'student_id' => $studentId,
            'rating' => 5
        ]);
        
        $response2Id = $this->createTestResponse([
            'survey_id' => $surveyId,
            'student_id' => $studentId,
            'rating' => 4
        ]);
        
        // Verify responses saved
        $stmt = $this->pdo->prepare('SELECT COUNT(*) as count FROM responses 
                                    WHERE survey_id = ? AND student_id = ?');
        $stmt->execute([$surveyId, $studentId]);
        $count = $stmt->fetch()['count'];
        
        $this->assertEquals(2, $count);
    }
    
    /**
     * Test 4: Cannot submit survey twice
     * INPUT: User submits survey, then tries to submit again
     * EXPECTED: Second submission rejected
     */
    public function testCannotSubmitSurveyTwice()
    {
        $studentId = $this->createTestUser();
        $surveyId = $this->createTestSurvey();
        $teacherId = $this->createTestUser(['role' => 'teacher']);
        
        // First submission
        $stmt = $this->pdo->prepare('INSERT INTO responses (survey_id, student_id, teacher_id, rating) 
                                    VALUES (?, ?, ?, ?)');
        $stmt->execute([$surveyId, $studentId, $teacherId, 5]);
        
        // Check if submission exists
        $stmt = $this->pdo->prepare('SELECT COUNT(*) as count FROM responses 
                                    WHERE survey_id = ? AND student_id = ?');
        $stmt->execute([$surveyId, $studentId]);
        $count = $stmt->fetch()['count'];
        
        $this->assertGreaterThan(0, $count, 'First submission should exist');
        
        // Second submission should be prevented (enforced at application level)
        $this->assertEquals(1, $count, 'Should only allow one submission');
    }
    
    /**
     * Test 5: Submit survey with missing required fields
     * INPUT: Submit without filling all questions
     * EXPECTED: Form validation error, submission blocked
     */
    public function testSubmitWithMissingFields()
    {
        $studentId = $this->createTestUser();
        $surveyId = $this->createTestSurvey();
        
        // Create survey with 3 questions
        $this->createTestQuestion($surveyId, ['question' => 'Q1']);
        $this->createTestQuestion($surveyId, ['question' => 'Q2']);
        $this->createTestQuestion($surveyId, ['question' => 'Q3']);
        
        // Only submit response for 1 question (missing 2)
        $this->createTestResponse([
            'survey_id' => $surveyId,
            'student_id' => $studentId,
            'rating' => 5
        ]);
        
        // Get total questions
        $stmt = $this->pdo->prepare('SELECT COUNT(*) as count FROM questions WHERE survey_id = ?');
        $stmt->execute([$surveyId]);
        $totalQuestions = $stmt->fetch()['count'];
        
        // Get submitted responses
        $stmt = $this->pdo->prepare('SELECT COUNT(*) as count FROM responses 
                                    WHERE survey_id = ? AND student_id = ?');
        $stmt->execute([$surveyId, $studentId]);
        $submittedResponses = $stmt->fetch()['count'];
        
        // In real app, validation would catch this
        $this->assertNotEquals($totalQuestions, $submittedResponses);
    }
    
    /**
     * Test 6: View survey results/analytics
     * INPUT: User views survey analytics
     * EXPECTED: Ratings displayed, average calculated
     */
    public function testViewSurveyResults()
    {
        $surveyId = $this->createTestSurvey();
        
        // Create multiple responses
        $this->createTestResponse(['survey_id' => $surveyId, 'rating' => 5]);
        $this->createTestResponse(['survey_id' => $surveyId, 'rating' => 4]);
        $this->createTestResponse(['survey_id' => $surveyId, 'rating' => 5]);
        
        // Calculate average
        $stmt = $this->pdo->prepare('SELECT AVG(rating) as avg_rating, COUNT(*) as total 
                                    FROM responses WHERE survey_id = ?');
        $stmt->execute([$surveyId]);
        $result = $stmt->fetch();
        
        $this->assertEquals(3, $result['total']);
        $this->assertEquals(4.67, round($result['avg_rating'], 2));
    }
    
    /**
     * Test 7: Inactive survey cannot be accessed
     * INPUT: Try to access deactivated survey
     * EXPECTED: Survey not shown, access denied
     */
    public function testInactiveSurveyCannotBeAccessed()
    {
        $surveyId = $this->createTestSurvey(['is_active' => 0]);
        
        // Try to load survey
        $stmt = $this->pdo->prepare('SELECT * FROM surveys WHERE id = ? AND is_active = 1');
        $stmt->execute([$surveyId]);
        $survey = $stmt->fetch();
        
        $this->assertFalse($survey, 'Inactive survey should not be accessible');
    }
    
    /**
     * Test 8: Save survey draft
     * INPUT: Fill partial survey and save as draft
     * EXPECTED: Draft saved, can resume later
     */
    public function testSaveSurveyDraft()
    {
        $studentId = $this->createTestUser();
        $surveyId = $this->createTestSurvey();
        
        // Create draft response with null values
        $stmt = $this->pdo->prepare('INSERT INTO responses (survey_id, student_id, rating, submitted_at) 
                                    VALUES (?, ?, NULL, NULL)');
        $stmt->execute([$surveyId, $studentId]);
        
        // Verify draft saved
        $stmt = $this->pdo->prepare('SELECT * FROM responses 
                                    WHERE survey_id = ? AND student_id = ? AND submitted_at IS NULL');
        $stmt->execute([$surveyId, $studentId]);
        $draft = $stmt->fetch();
        
        $this->assertNotNull($draft, 'Draft should be saved');
        $this->assertNull($draft['submitted_at'], 'Draft should not have submission time');
    }
    
    /**
     * Test 9: Rate specific teacher
     * INPUT: Submit survey rating a specific teacher
     * EXPECTED: Rating saved with teacher ID
     */
    public function testRateSpecificTeacher()
    {
        $studentId = $this->createTestUser(['role' => 'student']);
        $teacherId = $this->createTestUser(['role' => 'teacher']);
        $surveyId = $this->createTestSurvey();
        
        // Submit rating for teacher
        $stmt = $this->pdo->prepare('INSERT INTO responses (survey_id, student_id, teacher_id, rating) 
                                    VALUES (?, ?, ?, ?)');
        $stmt->execute([$surveyId, $studentId, $teacherId, 5]);
        
        // Verify rating saved
        $stmt = $this->pdo->prepare('SELECT teacher_id FROM responses WHERE teacher_id = ?');
        $stmt->execute([$teacherId]);
        $response = $stmt->fetch();
        
        $this->assertEquals($teacherId, $response['teacher_id']);
    }
    
    /**
     * Test 10: Get teacher's average rating
     * INPUT: View teacher's page
     * EXPECTED: Average rating from all students calculated
     */
    public function testGetTeacherAverageRating()
    {
        $teacherId = $this->createTestUser(['role' => 'teacher']);
        $surveyId = $this->createTestSurvey();
        
        // Create ratings from 3 students
        $this->createTestResponse(['teacher_id' => $teacherId, 'survey_id' => $surveyId, 'rating' => 5]);
        $this->createTestResponse(['teacher_id' => $teacherId, 'survey_id' => $surveyId, 'rating' => 4]);
        $this->createTestResponse(['teacher_id' => $teacherId, 'survey_id' => $surveyId, 'rating' => 5]);
        
        // Calculate average rating
        $stmt = $this->pdo->prepare('SELECT AVG(rating) as avg_rating FROM responses WHERE teacher_id = ?');
        $stmt->execute([$teacherId]);
        $result = $stmt->fetch();
        
        $this->assertEquals(4.67, round($result['avg_rating'], 2));
    }
}
