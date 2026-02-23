<?php
namespace Tests\Unit\WhiteBox;

use Tests\BaseTestCase;

/**
 * WHITE BOX TESTS - Analytics Module
 * Tests internal logic: Calculations, aggregations, edge cases
 */
class AnalyticsWhiteBoxTest extends BaseTestCase
{
    /**
     * Test 1: Calculate average rating with edge cases
     * Tests: Average calculation handles null, zero, edge values
     */
    public function testAverageRatingCalculation()
    {
        $calculateAverage = function($ratings) {
            $validRatings = array_filter($ratings, fn($r) => $r !== null && $r > 0);
            return empty($validRatings) ? 0 : array_sum($validRatings) / count($validRatings);
        };
        
        // Normal case
        $this->assertEquals(4, $calculateAverage([5, 4, 3]));
        
        // With null
        $this->assertEquals(4.5, $calculateAverage([5, 4, null]));
        
        // Empty
        $this->assertEquals(0, $calculateAverage([]));
        
        // Single value
        $this->assertEquals(5, $calculateAverage([5]));
    }
    
    /**
     * Test 2: Calculate percentage with zero denominator
     * Tests: Division by zero handling
     */
    public function testPercentageCalculation()
    {
        $calculatePercentage = function($numerator, $denominator) {
            return $denominator === 0 ? 0 : ($numerator / $denominator) * 100;
        };
        
        // Normal
        $this->assertEquals(50, $calculatePercentage(5, 10));
        
        // Zero denominator
        $this->assertEquals(0, $calculatePercentage(5, 0));
        
        // Zero numerator
        $this->assertEquals(0, $calculatePercentage(0, 10));
    }
    
    /**
     * Test 3: Aggregation query performance
     * Tests: Efficient database aggregations
     */
    public function testAggregationQueryPerformance()
    {
        $surveyId = $this->createTestSurvey();
        
        // Create 100 responses
        for ($i = 1; $i <= 100; $i++) {
            $this->createTestResponse([
                'survey_id' => $surveyId,
                'student_id' => ($i % 10) + 1,
                'rating' => (($i % 5) + 1)
            ]);
        }
        
        // Single aggregation query should be efficient
        $stmt = $this->pdo->prepare('
            SELECT 
                COUNT(*) as total_responses,
                AVG(rating) as avg_rating,
                MIN(rating) as min_rating,
                MAX(rating) as max_rating,
                SUM(rating) as sum_rating
            FROM responses
            WHERE survey_id = ?
        ');
        
        $start = microtime(true);
        $stmt->execute([$surveyId]);
        $result = $stmt->fetch();
        $duration = microtime(true) - $start;
        
        $this->assertEquals(100, $result['total_responses']);
        $this->assertLessThan(0.1, $duration); // Should be fast
    }
    
    /**
     * Test 4: Response count by rating
     * Tests: Group by aggregation
     */
    public function testResponseCountByRating()
    {
        $surveyId = $this->createTestSurvey();
        
        // Create distribution
        for ($rating = 1; $rating <= 5; $rating++) {
            for ($i = 0; $i < $rating; $i++) {
                $this->createTestResponse(['survey_id' => $surveyId, 'rating' => $rating]);
            }
        }
        
        // Get distribution
        $stmt = $this->pdo->prepare('
            SELECT rating, COUNT(*) as count
            FROM responses
            WHERE survey_id = ?
            GROUP BY rating
            ORDER BY rating
        ');
        $stmt->execute([$surveyId]);
        $distribution = $stmt->fetchAll();
        
        // 1 response with rating 1, 2 with rating 2, etc
        $this->assertEquals(1, $distribution[0]['count']);
        $this->assertEquals(2, $distribution[1]['count']);
        $this->assertEquals(5, $distribution[4]['count']);
    }
    
    /**
     * Test 5: Date range filtering
     * Tests: Correct date filtering logic
     */
    public function testDateRangeFiltering()
    {
        $surveyId = $this->createTestSurvey();
        
        // Insert responses across dates
        $dates = [
            '2025-01-01',
            '2025-01-15',
            '2025-02-01',
            '2025-02-15'
        ];
        
        foreach ($dates as $date) {
            $studentId = $this->createTestUser();
            $stmt = $this->pdo->prepare('
                INSERT INTO responses (survey_id, student_id, rating, submitted_at)
                VALUES (?, ?, ?, ?)
            ');
            $stmt->execute([$surveyId, $studentId, 5, $date . ' 10:00:00']);
        }
        
        // Query January only
        $stmt = $this->pdo->prepare('
            SELECT COUNT(*) as count FROM responses
            WHERE survey_id = ? AND MONTH(submitted_at) = 1
        ');
        $stmt->execute([$surveyId]);
        $result = $stmt->fetch();
        
        $this->assertEquals(2, $result['count']);
    }
    
    /**
     * Test 6: Handling missing data in calculations
     * Tests: NULL values don't break aggregations
     */
    public function testHandlingMissingData()
    {
        $surveyId = $this->createTestSurvey();
        $studentId = $this->createTestUser();
        
        // Create response with null rating
        $stmt = $this->pdo->prepare('
            INSERT INTO responses (survey_id, student_id, rating)
            VALUES (?, ?, NULL)
        ');
        $stmt->execute([$surveyId, $studentId]);
        
        // Create normal responses
        $this->createTestResponse(['survey_id' => $surveyId, 'rating' => 5]);
        $this->createTestResponse(['survey_id' => $surveyId, 'rating' => 4]);
        
        // Average should ignore NULL
        $stmt = $this->pdo->prepare('
            SELECT AVG(rating) as avg_rating, COUNT(*) as total
            FROM responses
            WHERE survey_id = ?
        ');
        $stmt->execute([$surveyId]);
        $result = $stmt->fetch();
        
        $this->assertEquals(4.5, $result['avg_rating']);
        $this->assertEquals(3, $result['total']);
    }
    
    /**
     * Test 7: Percentile calculations
     * Tests: Calculate data percentiles for distribution
     */
    public function testPercentileCalculations()
    {
        $ratings = [1, 2, 3, 4, 5, 5, 5, 5, 5];
        
        sort($ratings);
        $count = count($ratings);
        
        // Median (50th percentile)
        $median = $ratings[floor(($count - 1) / 2)];
        $this->assertEquals(5, $median);
        
        // Mode (most frequent)
        $counts = array_count_values($ratings);
        arsort($counts);
        $mode = key($counts);
        $this->assertEquals(5, $mode);
    }
    
    /**
     * Test 8: Teacher ranking by average rating
     * Tests: Rank teachers by performance
     */
    public function testTeacherRanking()
    {
        $surveyId = $this->createTestSurvey();
        
        // Create teachers with different ratings
        $teacher1 = $this->createTestUser(['role' => 'teacher']);
        $teacher2 = $this->createTestUser(['role' => 'teacher']);
        $teacher3 = $this->createTestUser(['role' => 'teacher']);
        
        // Teacher 1: avg 5
        for ($i = 0; $i < 3; $i++) {
            $this->createTestResponse(['teacher_id' => $teacher1, 'survey_id' => $surveyId, 'rating' => 5]);
        }
        
        // Teacher 2: avg 3
        for ($i = 0; $i < 3; $i++) {
            $this->createTestResponse(['teacher_id' => $teacher2, 'survey_id' => $surveyId, 'rating' => 3]);
        }
        
        // Teacher 3: avg 4
        for ($i = 0; $i < 3; $i++) {
            $this->createTestResponse(['teacher_id' => $teacher3, 'survey_id' => $surveyId, 'rating' => 4]);
        }
        
        // Get ranking
        $stmt = $this->pdo->prepare('
            SELECT teacher_id, AVG(rating) as avg_rating
            FROM responses
            WHERE survey_id = ?
            GROUP BY teacher_id
            ORDER BY avg_rating DESC
        ');
        $stmt->execute([$surveyId]);
        $ranking = $stmt->fetchAll();
        
        $this->assertEquals($teacher1, $ranking[0]['teacher_id']);
        $this->assertEquals(5, $ranking[0]['avg_rating']);
    }
    
    /**
     * Test 9: Completion rate calculation
     * Tests: Accurately calculate survey completion percentage
     */
    public function testCompletionRateCalculation()
    {
        // Create 10 students
        for ($i = 0; $i < 10; $i++) {
            $this->createTestUser(['role' => 'student']);
        }
        
        $surveyId = $this->createTestSurvey();
        
        // 7 students complete survey
        for ($i = 1; $i <= 7; $i++) {
            $this->createTestResponse(['survey_id' => $surveyId, 'student_id' => $i]);
        }
        
        // Calculate rate
        $stmt = $this->pdo->prepare('
            SELECT 
                COUNT(DISTINCT r.student_id) as completed,
                COUNT(DISTINCT u.id) as total
            FROM responses r
            FULL OUTER JOIN users u ON u.role = "student"
            WHERE r.survey_id = ?
        ');
        
        // For SQLite compatibility
        $stmt = $this->pdo->prepare('
            SELECT 
                (SELECT COUNT(DISTINCT student_id) FROM responses WHERE survey_id = ?) as completed,
                (SELECT COUNT(*) FROM users WHERE role = "student") as total
        ');
        $stmt->execute([$surveyId]);
        $result = $stmt->fetch();
        
        $rate = ($result['completed'] / $result['total']) * 100;
        $this->assertEquals(70, $rate);
    }
    
    /**
     * Test 10: Trend analysis over time
     * Tests: Identify improvement/decline trends
     */
    public function testTrendAnalysis()
    {
        $surveyId = $this->createTestSurvey();
        
        // Week 1: avg rating 3
        // Week 2: avg rating 4
        // Week 3: avg rating 5 (improvement)
        
        for ($week = 1; $week <= 3; $week++) {
            $date = date('Y-m-d', strtotime("-" . (4 - $week) . " weeks"));
            for ($i = 0; $i < 3; $i++) {
                $studentId = $this->createTestUser();
                $stmt = $this->pdo->prepare('
                    INSERT INTO responses (survey_id, student_id, rating, submitted_at)
                    VALUES (?, ?, ?, ?)
                ');
                $stmt->execute([$surveyId, $studentId, $week + 1, $date . ' 10:00:00']);
            }
        }
        
        // Get weekly trend
        $stmt = $this->pdo->prepare('
            SELECT 
                WEEK(submitted_at) as week,
                AVG(rating) as avg_rating
            FROM responses
            WHERE survey_id = ?
            GROUP BY WEEK(submitted_at)
            ORDER BY week
        ');
        $stmt->execute([$surveyId]);
        $trend = $stmt->fetchAll();
        
        // Should show improvement
        $this->assertGreaterThan($trend[0]['avg_rating'], $trend[count($trend)-1]['avg_rating']);
    }
}
