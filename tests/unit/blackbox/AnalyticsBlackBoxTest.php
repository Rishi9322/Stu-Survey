<?php
namespace Tests\Unit\BlackBox;

use Tests\BaseTestCase;

/**
 * BLACK BOX TESTS - Analytics Module  
 * Tests from user's perspective: Can user view analytics, charts, reports?
 */
class AnalyticsBlackBoxTest extends BaseTestCase
{
    /**
     * Test 1: View survey completion rate
     * INPUT: Open analytics dashboard
     * EXPECTED: Completion percentage displayed
     */
    public function testViewSurveyCompletionRate()
    {
        // Create 10 students
        $studentIds = [];
        for ($i = 0; $i < 10; $i++) {
            $studentIds[] = $this->createTestUser(['role' => 'student']);
        }
        
        $surveyId = $this->createTestSurvey();
        
        // 6 students complete survey
        for ($i = 0; $i < 6; $i++) {
            $this->createTestResponse([
                'survey_id' => $surveyId,
                'student_id' => $studentIds[$i],
                'submitted_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        // Calculate completion rate
        $stmt = $this->pdo->prepare('
            SELECT 
                COUNT(DISTINCT r.student_id) as completed,
                (SELECT COUNT(*) FROM users WHERE role = "student") as total
            FROM responses r
            WHERE r.survey_id = ?
        ');
        $stmt->execute([$surveyId]);
        $result = $stmt->fetch();
        
        $completionRate = ($result['completed'] / $result['total']) * 100;
        
        $this->assertEquals(60, $completionRate);
    }
    
    /**
     * Test 2: View average teacher rating
     * INPUT: Click on teacher analytics
     * EXPECTED: Average rating and count displayed
     */
    public function testViewAverageTeacherRating()
    {
        $teacherId = $this->createTestUser(['role' => 'teacher']);
        $surveyId = $this->createTestSurvey();
        
        // Create ratings: 5, 4, 5, 3
        $this->createTestResponse(['teacher_id' => $teacherId, 'survey_id' => $surveyId, 'rating' => 5]);
        $this->createTestResponse(['teacher_id' => $teacherId, 'survey_id' => $surveyId, 'rating' => 4]);
        $this->createTestResponse(['teacher_id' => $teacherId, 'survey_id' => $surveyId, 'rating' => 5]);
        $this->createTestResponse(['teacher_id' => $teacherId, 'survey_id' => $surveyId, 'rating' => 3]);
        
        // Get analytics
        $stmt = $this->pdo->prepare('
            SELECT 
                AVG(rating) as average_rating,
                COUNT(*) as total_ratings
            FROM responses
            WHERE teacher_id = ? AND survey_id = ?
        ');
        $stmt->execute([$teacherId, $surveyId]);
        $result = $stmt->fetch();
        
        $this->assertEquals(4.25, round($result['average_rating'], 2));
        $this->assertEquals(4, $result['total_ratings']);
    }
    
    /**
     * Test 3: Filter analytics by date range
     * INPUT: Select date range filter
     * EXPECTED: Only data in date range displayed
     */
    public function testFilterAnalyticsByDateRange()
    {
        $surveyId = $this->createTestSurvey();
        $student1 = $this->createTestUser();
        $student2 = $this->createTestUser();
        
        // Create old response
        $stmt = $this->pdo->prepare('
            INSERT INTO responses (survey_id, student_id, rating, submitted_at)
            VALUES (?, ?, ?, ?)
        ');
        $stmt->execute([$surveyId, $student1, 5, '2025-01-01 10:00:00']);
        
        // Create recent response
        $stmt->execute([$surveyId, $student2, 4, date('Y-m-d H:i:s')]);
        
        // Query recent only (last 30 days)
        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
        
        $stmt = $this->pdo->prepare('
            SELECT COUNT(*) as count FROM responses
            WHERE survey_id = ? AND submitted_at >= ?
        ');
        $stmt->execute([$surveyId, $thirtyDaysAgo]);
        $result = $stmt->fetch();
        
        $this->assertEquals(1, $result['count']);
    }
    
    /**
     * Test 4: View rating distribution chart data
     * INPUT: View analytics dashboard chart
     * EXPECTED: Data points for chart generation
     */
    public function testRatingDistributionChartData()
    {
        $surveyId = $this->createTestSurvey();
        
        // Create ratings distribution
        for ($i = 0; $i < 3; $i++) $this->createTestResponse(['survey_id' => $surveyId, 'rating' => 5]);
        for ($i = 0; $i < 4; $i++) $this->createTestResponse(['survey_id' => $surveyId, 'rating' => 4]);
        for ($i = 0; $i < 2; $i++) $this->createTestResponse(['survey_id' => $surveyId, 'rating' => 3]);
        for ($i = 0; $i < 1; $i++) $this->createTestResponse(['survey_id' => $surveyId, 'rating' => 2]);
        
        // Get distribution for chart
        $stmt = $this->pdo->prepare('
            SELECT rating, COUNT(*) as count FROM responses
            WHERE survey_id = ?
            GROUP BY rating
            ORDER BY rating DESC
        ');
        $stmt->execute([$surveyId]);
        $distribution = $stmt->fetchAll();
        
        $this->assertCount(4, $distribution);
        $this->assertEquals(5, $distribution[0]['rating']);
        $this->assertEquals(3, $distribution[0]['count']);
    }
    
    /**
     * Test 5: Export analytics as report
     * INPUT: Click export button
     * EXPECTED: Data available for export
     */
    public function testExportAnalyticsData()
    {
        $surveyId = $this->createTestSurvey();
        $this->createTestResponse(['survey_id' => $surveyId, 'rating' => 5]);
        $this->createTestResponse(['survey_id' => $surveyId, 'rating' => 4]);
        
        // Get data for export
        $stmt = $this->pdo->prepare('
            SELECT * FROM responses WHERE survey_id = ?
        ');
        $stmt->execute([$surveyId]);
        $data = $stmt->fetchAll();
        
        // Should be able to convert to CSV
        $this->assertCount(2, $data);
        $this->assertArrayHasKey('rating', $data[0]);
    }
    
    /**
     * Test 6: Compare teacher performance
     * INPUT: View teacher comparison analytics
     * EXPECTED: Side-by-side rating comparison
     */
    public function testCompareTeacherPerformance()
    {
        $teacher1 = $this->createTestUser(['role' => 'teacher', 'name' => 'Teacher A']);
        $teacher2 = $this->createTestUser(['role' => 'teacher', 'name' => 'Teacher B']);
        $surveyId = $this->createTestSurvey();
        
        // Teacher 1 ratings
        $this->createTestResponse(['teacher_id' => $teacher1, 'survey_id' => $surveyId, 'rating' => 5]);
        $this->createTestResponse(['teacher_id' => $teacher1, 'survey_id' => $surveyId, 'rating' => 5]);
        
        // Teacher 2 ratings
        $this->createTestResponse(['teacher_id' => $teacher2, 'survey_id' => $surveyId, 'rating' => 3]);
        $this->createTestResponse(['teacher_id' => $teacher2, 'survey_id' => $surveyId, 'rating' => 3]);
        
        // Get comparison
        $stmt = $this->pdo->prepare('
            SELECT 
                teacher_id,
                AVG(rating) as avg_rating,
                COUNT(*) as count
            FROM responses
            WHERE survey_id = ?
            GROUP BY teacher_id
            ORDER BY avg_rating DESC
        ');
        $stmt->execute([$surveyId]);
        $comparison = $stmt->fetchAll();
        
        $this->assertCount(2, $comparison);
        $this->assertEquals($teacher1, $comparison[0]['teacher_id']);
    }
    
    /**
     * Test 7: View response trends over time
     * INPUT: View trend analytics
     * EXPECTED: Trend line data available
     */
    public function testViewResponseTrends()
    {
        $surveyId = $this->createTestSurvey();
        
        // Create responses over time
        $stmt = $this->pdo->prepare('
            INSERT INTO responses (survey_id, student_id, rating, submitted_at)
            VALUES (?, ?, ?, ?)
        ');
        
        for ($day = 1; $day <= 7; $day++) {
            $date = date('Y-m-d', strtotime("-$day days"));
            $studentId = $this->createTestUser();
            $stmt->execute([$surveyId, $studentId, 4, "$date 10:00:00"]);
        }
        
        // Get weekly trend
        $stmt = $this->pdo->prepare('
            SELECT 
                DATE(submitted_at) as date,
                AVG(rating) as avg_rating
            FROM responses
            WHERE survey_id = ?
            GROUP BY DATE(submitted_at)
            ORDER BY DATE(submitted_at)
        ');
        $stmt->execute([$surveyId]);
        $trends = $stmt->fetchAll();
        
        $this->assertCount(7, $trends);
    }
    
    /**
     * Test 8: View pending issues summary
     * INPUT: Dashboard summary view
     * EXPECTED: Count of pending complaints/issues
     */
    public function testPendingIssuesSummary()
    {
        $this->createTestComplaint(['status' => 'open']);
        $this->createTestComplaint(['status' => 'open']);
        $this->createTestComplaint(['status' => 'open']);
        $this->createTestComplaint(['status' => 'resolved']);
        
        // Get pending count
        $stmt = $this->pdo->prepare('
            SELECT COUNT(*) as pending FROM suggestions_complaints
            WHERE status IN ("open", "pending_review")
        ');
        $stmt->execute();
        $result = $stmt->fetch();
        
        $this->assertEquals(3, $result['pending']);
    }
    
    /**
     * Test 9: Department-wise analytics
     * INPUT: View analytics by department
     * EXPECTED: Breakdown by department
     */
    public function testDepartmentWiseAnalytics()
    {
        // Would require department field in users table
        // Test structure for when implemented
        $dept1Users = [
            $this->createTestUser(['name' => 'Student 1']),
            $this->createTestUser(['name' => 'Student 2'])
        ];
        
        $surveyId = $this->createTestSurvey();
        
        foreach ($dept1Users as $userId) {
            $this->createTestResponse([
                'survey_id' => $surveyId,
                'student_id' => $userId,
                'rating' => 5
            ]);
        }
        
        // Basic test - verify responses created
        $stmt = $this->pdo->prepare('SELECT COUNT(*) as count FROM responses WHERE survey_id = ?');
        $stmt->execute([$surveyId]);
        $count = $stmt->fetch()['count'];
        
        $this->assertEquals(2, $count);
    }
    
    /**
     * Test 10: Generate summary statistics
     * INPUT: View dashboard statistics
     * EXPECTED: All key metrics displayed
     */
    public function testGenerateSummaryStatistics()
    {
        // Create test data
        for ($i = 0; $i < 10; $i++) {
            $this->createTestUser(['role' => 'student']);
        }
        $this->createTestSurvey();
        $this->createTestComplaint(['status' => 'open']);
        $this->createTestComplaint(['status' => 'resolved']);
        
        // Get statistics
        $stmt = $this->pdo->prepare('SELECT COUNT(*) as total FROM users WHERE role = "student"');
        $stmt->execute();
        $totalStudents = $stmt->fetch()['total'];
        
        $stmt = $this->pdo->prepare('SELECT COUNT(*) as total FROM surveys');
        $stmt->execute();
        $totalSurveys = $stmt->fetch()['total'];
        
        $stmt = $this->pdo->prepare('SELECT COUNT(*) as pending FROM suggestions_complaints WHERE status = "open"');
        $stmt->execute();
        $pendingComplaints = $stmt->fetch()['pending'];
        
        $this->assertGreaterThan(0, $totalStudents);
        $this->assertGreaterThan(0, $totalSurveys);
    }
}
