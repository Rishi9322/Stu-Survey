<?php
namespace Tests\Unit\WhiteBox;

use Tests\BaseTestCase;

/**
 * WHITE BOX TESTS - Complaints/Suggestions Module
 * Tests internal logic: Validation, workflow, database operations
 */
class ComplaintsWhiteBoxTest extends BaseTestCase
{
    /**
     * Test 1: Subject field validation
     * Tests: Subject must not be empty, min/max length
     */
    public function testSubjectValidation()
    {
        $validateSubject = function($subject) {
            $minLength = 5;
            $maxLength = 100;
            return !empty($subject) && strlen($subject) >= $minLength && strlen($subject) <= $maxLength;
        };
        
        // Invalid
        $this->assertFalse($validateSubject(''));
        $this->assertFalse($validateSubject('Hi'));
        $this->assertFalse($validateSubject(str_repeat('a', 101)));
        
        // Valid
        $this->assertTrue($validateSubject('Issue with class'));
        $this->assertTrue($validateSubject('Room needs renovation'));
    }
    
    /**
     * Test 2: Description field validation
     * Tests: Description required, min length
     */
    public function testDescriptionValidation()
    {
        $validateDescription = function($description) {
            $minLength = 10;
            return !empty($description) && strlen($description) >= $minLength;
        };
        
        $this->assertFalse($validateDescription(''));
        $this->assertFalse($validateDescription('Short'));
        $this->assertTrue($validateDescription('This is a detailed complaint description'));
    }
    
    /**
     * Test 3: Type field validation
     * Tests: Type must be complaint or suggestion
     */
    public function testTypeValidation()
    {
        $validateType = function($type) {
            return in_array($type, ['complaint', 'suggestion']);
        };
        
        $this->assertTrue($validateType('complaint'));
        $this->assertTrue($validateType('suggestion'));
        $this->assertFalse($validateType('invalid'));
        $this->assertFalse($validateType(''));
    }
    
    /**
     * Test 4: Status workflow validation
     * Tests: Status transitions follow valid paths
     */
    public function testStatusWorkflow()
    {
        $validTransitions = [
            'open' => ['pending_review', 'resolved', 'closed'],
            'pending_review' => ['open', 'resolved', 'closed'],
            'resolved' => ['open', 'closed'],
            'closed' => []
        ];
        
        $canTransition = function($from, $to) use ($validTransitions) {
            return in_array($to, $validTransitions[$from] ?? []);
        };
        
        // Valid transitions
        $this->assertTrue($canTransition('open', 'pending_review'));
        $this->assertTrue($canTransition('pending_review', 'resolved'));
        
        // Invalid transitions
        $this->assertFalse($canTransition('closed', 'open'));
    }
    
    /**
     * Test 5: Resolve complaint SQL injection prevention
     * Tests: Resolution notes are safe from SQL injection
     */
    public function testSQLInjectionPrevention()
    {
        $complaintId = $this->createTestComplaint();
        
        // Attempt SQL injection in resolution notes
        $maliciousNote = "'; DROP TABLE suggestions_complaints; --";
        
        $stmt = $this->pdo->prepare(
            'UPDATE suggestions_complaints SET resolution_notes = ? WHERE id = ?'
        );
        $stmt->execute([$maliciousNote, $complaintId]);
        
        // Table should still exist
        $stmt = $this->pdo->query('SELECT COUNT(*) as count FROM suggestions_complaints');
        $count = $stmt->fetch()['count'];
        
        $this->assertGreaterThan(0, $count);
    }
    
    /**
     * Test 6: Complaint count by status
     * Tests: Aggregation query for status distribution
     */
    public function testComplaintCountByStatus()
    {
        $this->createTestComplaint(['status' => 'open']);
        $this->createTestComplaint(['status' => 'open']);
        $this->createTestComplaint(['status' => 'pending_review']);
        $this->createTestComplaint(['status' => 'resolved']);
        
        $stmt = $this->pdo->prepare('
            SELECT status, COUNT(*) as count 
            FROM suggestions_complaints 
            GROUP BY status
        ');
        $stmt->execute();
        $distribution = $stmt->fetchAll();
        
        $this->assertCount(3, $distribution);
    }
    
    /**
     * Test 7: Auto-timestamp complaint submission
     * Tests: created_at timestamp set automatically
     */
    public function testAutoTimestampOnCreation()
    {
        $beforeTime = date('Y-m-d H:i:s');
        
        $complaintId = $this->createTestComplaint();
        
        $afterTime = date('Y-m-d H:i:s');
        
        $stmt = $this->pdo->prepare('SELECT created_at FROM suggestions_complaints WHERE id = ?');
        $stmt->execute([$complaintId]);
        $complaint = $stmt->fetch();
        
        $this->assertNotNull($complaint['created_at']);
        $this->assertGreaterThanOrEqual($beforeTime, $complaint['created_at']);
    }
    
    /**
     * Test 8: Resolution notes only on resolved status
     * Tests: Resolution notes should be null for open complaints
     */
    public function testResolutionNotesLogic()
    {
        $openComplaint = $this->createTestComplaint([
            'status' => 'open'
        ]);
        
        $stmt = $this->pdo->prepare('SELECT resolution_notes FROM suggestions_complaints WHERE id = ?');
        $stmt->execute([$openComplaint]);
        $complaint = $stmt->fetch();
        
        $this->assertNull($complaint['resolution_notes']);
        
        // Resolve complaint
        $stmt = $this->pdo->prepare('
            UPDATE suggestions_complaints 
            SET status = ?, resolution_notes = ?
            WHERE id = ?
        ');
        $stmt->execute(['resolved', 'Issue fixed', $openComplaint]);
        
        // Verify notes set
        $stmt = $this->pdo->prepare('SELECT resolution_notes FROM suggestions_complaints WHERE id = ?');
        $stmt->execute([$openComplaint]);
        $complaint = $stmt->fetch();
        
        $this->assertNotNull($complaint['resolution_notes']);
    }
    
    /**
     * Test 9: Complaint assignment to admin
     * Tests: Complaint can be assigned to resolving admin
     */
    public function testComplaintAssignmentToAdmin()
    {
        $adminId = $this->createTestUser(['role' => 'admin']);
        
        $complaintId = $this->createTestComplaint();
        
        // Update complaint with admin assignment
        $stmt = $this->pdo->prepare('
            UPDATE suggestions_complaints 
            SET resolved_by = ? 
            WHERE id = ?
        ');
        $stmt->execute([$adminId, $complaintId]);
        
        // Verify assignment
        $stmt = $this->pdo->prepare('SELECT resolved_by FROM suggestions_complaints WHERE id = ?');
        $stmt->execute([$complaintId]);
        $complaint = $stmt->fetch();
        
        $this->assertEquals($adminId, $complaint['resolved_by']);
    }
    
    /**
     * Test 10: Pagination for large complaint lists
     * Tests: Efficiently retrieve paginated complaints
     */
    public function testComplaintPagination()
    {
        // Create 25 complaints
        for ($i = 0; $i < 25; $i++) {
            $this->createTestComplaint([
                'subject' => 'Complaint ' . $i
            ]);
        }
        
        $pageSize = 10;
        $page = 1;
        $offset = ($page - 1) * $pageSize;
        
        $stmt = $this->pdo->prepare('
            SELECT * FROM suggestions_complaints 
            ORDER BY created_at DESC
            LIMIT ' . (int)$pageSize . ' OFFSET ' . (int)$offset . '
        ');
        $stmt->execute();
        $pageResults = $stmt->fetchAll();
        
        $this->assertCount(10, $pageResults);
        
        // Get total count
        $stmt = $this->pdo->prepare('SELECT COUNT(*) as total FROM suggestions_complaints');
        $stmt->execute();
        $totalCount = $stmt->fetch()['total'];
        
        $this->assertEquals(25, $totalCount);
    }
}
