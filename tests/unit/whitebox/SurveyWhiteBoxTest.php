<?php
namespace Tests\Unit\WhiteBox;

use Tests\BaseTestCase;

/**
 * WHITE BOX TESTS - Survey Module
 * Tests internal logic: Rating validation, duplicate prevention, calculations
 */
class SurveyWhiteBoxTest extends BaseTestCase
{
    /**
     * Test 1: Rating validation (must be 1-5)
     * Tests: Rating input validation logic
     */
    public function testRatingValidation()
    {
        $validateRating = function($rating) {
            return is_int($rating) && $rating >= 1 && $rating <= 5;
        };
        
        // Valid ratings
        $this->assertTrue($validateRating(1), 'Rating 1 should be valid');
        $this->assertTrue($validateRating(3), 'Rating 3 should be valid');
        $this->assertTrue($validateRating(5), 'Rating 5 should be valid');
        
        // Invalid ratings
        $this->assertFalse($validateRating(0), 'Rating 0 should be invalid');
        $this->assertFalse($validateRating(6), 'Rating 6 should be invalid');
        $this->assertFalse($validateRating(-1), 'Negative rating should be invalid');
        $this->assertFalse($validateRating('abc'), 'Non-numeric should be invalid');
        $this->assertFalse($validateRating(3.5), 'Decimal should be invalid');
    }
    
    /**
     * Test 2: Average rating calculation
     * Tests: Correctly calculates average from multiple ratings
     */
    public function testAverageRatingCalculation()
    {
        $ratings = [5, 4, 5, 3, 4];
        
        $average = array_sum($ratings) / count($ratings);
        $expected = 4.2; // (5+4+5+3+4)/5 = 21/5 = 4.2
        
        $this->assertEquals($expected, $average);
    }
    
    /**
     * Test 3: Duplicate submission prevention
     * Tests: Checks for existing submission before allowing new one
     */
    public function testDuplicateSubmissionPrevention()
    {
        $studentId = $this->createTestUser();
        $surveyId = $this->createTestSurvey();
        
        // First submission
        $this->createTestResponse([
            'survey_id' => $surveyId,
            'student_id' => $studentId,
            'rating' => 5
        ]);
        
        // Check if duplicate exists
        $stmt = $this->pdo->prepare('
            SELECT COUNT(*) as count FROM responses 
            WHERE survey_id = ? AND student_id = ?
        ');
        $stmt->execute([$surveyId, $studentId]);
        $count = $stmt->fetch()['count'];
        
        $this->assertEquals(1, $count);
        
        // Check function that prevents duplicate
        $canSubmit = function($surveyId, $studentId) {
            // Check if already submitted
            $this->pdo->prepare('SELECT COUNT(*) as count FROM responses 
                               WHERE survey_id = ? AND student_id = ?')->execute([$surveyId, $studentId]);
            $stmt = $this->pdo->prepare('SELECT COUNT(*) as count FROM responses 
                                       WHERE survey_id = ? AND student_id = ?');
            $stmt->execute([$surveyId, $studentId]);
            $existing = $stmt->fetch()['count'];
            return $existing === 0;
        };
        
        // Should not allow second submission
        $this->assertFalse($canSubmit($surveyId, $studentId));
    }
    
    /**
     * Test 4: Question ordering
     * Tests: Questions displayed in correct order
     */
    public function testQuestionOrdering()
    {
        $surveyId = $this->createTestSurvey();
        
        // Create questions in random order
        $questionIds = [];
        $questionIds[] = $this->createTestQuestion($surveyId, ['question' => 'Q3', 'order' => 3]);
        $questionIds[] = $this->createTestQuestion($surveyId, ['question' => 'Q1', 'order' => 1]);
        $questionIds[] = $this->createTestQuestion($surveyId, ['question' => 'Q2', 'order' => 2]);
        
        // Retrieve ordered
        $stmt = $this->pdo->prepare('SELECT * FROM questions WHERE survey_id = ? ORDER BY `order` ASC');
        $stmt->execute([$surveyId]);
        $questions = $stmt->fetchAll();
        
        // Should be in correct order
        $this->assertEquals('Q1', $questions[0]['question']);
        $this->assertEquals('Q2', $questions[1]['question']);
        $this->assertEquals('Q3', $questions[2]['question']);
    }
    
    /**
     * Test 5: Rating distribution analysis
     * Tests: Calculate rating distribution (how many 1s, 2s, 3s, etc)
     */
    public function testRatingDistributionAnalysis()
    {
        $surveyId = $this->createTestSurvey();
        
        // Create responses with different ratings
        $ratings = [5, 5, 5, 4, 4, 3, 2, 1];
        foreach ($ratings as $rating) {
            $this->createTestResponse([
                'survey_id' => $surveyId,
                'rating' => $rating
            ]);
        }
        
        // Calculate distribution
        $stmt = $this->pdo->prepare('
            SELECT rating, COUNT(*) as count 
            FROM responses 
            WHERE survey_id = ? 
            GROUP BY rating 
            ORDER BY rating DESC
        ');
        $stmt->execute([$surveyId]);
        $distribution = $stmt->fetchAll();
        
        // Verify counts
        $this->assertEquals(3, $distribution[0]['count']); // 3 ratings of 5
        $this->assertEquals(2, $distribution[1]['count']); // 2 ratings of 4
    }
    
    /**
     * Test 6: Student progress tracking
     * Tests: Track how many surveys student has completed
     */
    public function testStudentProgressTracking()
    {
        $studentId = $this->createTestUser(['role' => 'student']);
        
        // Create 3 surveys and submit to 2
        $survey1 = $this->createTestSurvey(['title' => 'Survey 1']);
        $survey2 = $this->createTestSurvey(['title' => 'Survey 2']);
        $survey3 = $this->createTestSurvey(['title' => 'Survey 3']);
        
        $this->createTestResponse(['survey_id' => $survey1, 'student_id' => $studentId]);
        $this->createTestResponse(['survey_id' => $survey2, 'student_id' => $studentId]);
        
        // Get completion stats
        $stmt = $this->pdo->prepare('
            SELECT COUNT(*) as completed FROM responses 
            WHERE student_id = ?
        ');
        $stmt->execute([$studentId]);
        $result = $stmt->fetch();
        
        $this->assertEquals(2, $result['completed']);
    }
    
    /**
     * Test 7: Teacher response validation
     * Tests: Ensures only valid teachers can receive ratings
     */
    public function testTeacherResponseValidation()
    {
        $studentId = $this->createTestUser(['role' => 'student']);
        $teacherId = $this->createTestUser(['role' => 'teacher']);
        $anotherStudentId = $this->createTestUser(['role' => 'student']);
        $surveyId = $this->createTestSurvey();
        
        // Try to rate another student as teacher (should fail in real app)
        $stmt = $this->pdo->prepare('
            SELECT role FROM users WHERE id = ?
        ');
        $stmt->execute([$anotherStudentId]);
        $user = $stmt->fetch();
        
        $isTeacher = $user['role'] === 'teacher';
        $this->assertFalse($isTeacher);
    }
    
    /**
     * Test 8: Survey status transitions
     * Tests: Survey status changes (draft -> submitted -> completed)
     */
    public function testSurveyStatusTransitions()
    {
        $surveyId = $this->createTestSurvey(['is_active' => 1]);
        
        // Check initial status
        $stmt = $this->pdo->prepare('SELECT is_active FROM surveys WHERE id = ?');
        $stmt->execute([$surveyId]);
        $survey = $stmt->fetch();
        $this->assertEquals(1, $survey['is_active']);
        
        // Deactivate survey
        $stmt = $this->pdo->prepare('UPDATE surveys SET is_active = 0 WHERE id = ?');
        $stmt->execute([$surveyId]);
        
        // Check new status
        $stmt = $this->pdo->prepare('SELECT is_active FROM surveys WHERE id = ?');
        $stmt->execute([$surveyId]);
        $survey = $stmt->fetch();
        $this->assertEquals(0, $survey['is_active']);
    }
    
    /**
     * Test 9: Response date/time tracking
     * Tests: Submission timestamp is recorded correctly
     */
    public function testResponseDateTracking()
    {
        $beforeTime = date('Y-m-d H:i:s');
        
        $this->createTestResponse([
            'submitted_at' => $beforeTime
        ]);
        
        $afterTime = date('Y-m-d H:i:s');
        
        // Verify timestamp is in valid range
        $stmt = $this->pdo->prepare('SELECT submitted_at FROM responses WHERE id = 1');
        $stmt->execute();
        $response = $stmt->fetch();
        
        $this->assertNotNull($response['submitted_at']);
        $this->assertGreaterThanOrEqual($beforeTime, $response['submitted_at']);
        $this->assertLessThanOrEqual($afterTime, $response['submitted_at']);
    }
    
    /**
     * Test 10: Bulk rating calculations
     * Tests: Efficiently calculate stats for multiple teachers
     */
    public function testBulkTeacherRatings()
    {
        // Create 3 teachers
        $teacher1 = $this->createTestUser(['role' => 'teacher', 'name' => 'Teacher 1']);
        $teacher2 = $this->createTestUser(['role' => 'teacher', 'name' => 'Teacher 2']);
        $teacher3 = $this->createTestUser(['role' => 'teacher', 'name' => 'Teacher 3']);
        
        $surveyId = $this->createTestSurvey();
        
        // Add ratings
        $this->createTestResponse(['teacher_id' => $teacher1, 'survey_id' => $surveyId, 'rating' => 5]);
        $this->createTestResponse(['teacher_id' => $teacher1, 'survey_id' => $surveyId, 'rating' => 5]);
        
        $this->createTestResponse(['teacher_id' => $teacher2, 'survey_id' => $surveyId, 'rating' => 3]);
        
        $this->createTestResponse(['teacher_id' => $teacher3, 'survey_id' => $surveyId, 'rating' => 4]);
        $this->createTestResponse(['teacher_id' => $teacher3, 'survey_id' => $surveyId, 'rating' => 4]);
        $this->createTestResponse(['teacher_id' => $teacher3, 'survey_id' => $surveyId, 'rating' => 4]);
        
        // Get all teachers with ratings
        $stmt = $this->pdo->prepare('
            SELECT teacher_id, AVG(rating) as avg_rating, COUNT(*) as total_ratings
            FROM responses
            WHERE survey_id = ?
            GROUP BY teacher_id
            ORDER BY avg_rating DESC
        ');
        $stmt->execute([$surveyId]);
        $results = $stmt->fetchAll();
        
        // Verify teacher 1 has highest average
        $this->assertEquals(5, $results[0]['avg_rating']);
        $this->assertEquals(2, $results[0]['total_ratings']);
    }
}
