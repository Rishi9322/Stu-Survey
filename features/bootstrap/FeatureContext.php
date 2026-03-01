<?php

use Behat\Behat\Context\Context;
use Behat\MinkExtension\Context\MinkContext;
use PDO;
use PDOException;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext implements Context
{
    private $dbConnection;
    private $createdFeedbackIds = [];

    /**
     * Initializes context.
     */
    public function __construct(
        ?string $db_host = null,
        ?string $db_name = null,
        ?string $db_user = null,
        ?string $db_pass = null
    )
    {
        try {
            $host = $db_host ?? getenv('DB_HOST') ?? '127.0.0.1';
            $name = $db_name ?? getenv('DB_NAME') ?? 'stu';
            $user = $db_user ?? getenv('DB_USER') ?? 'root';
            $pass = $db_pass ?? getenv('DB_PASS') ?? '';

            $this->dbConnection = new PDO(
                "mysql:host={$host};dbname={$name};charset=utf8mb4",
                $user,
                $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * @Given I am logged in as a student
     */
    public function iAmLoggedInAsAStudent()
    {
        $this->visit('/login.php');
        $this->fillField('username', 'student@test.com');
        $this->fillField('password', 'password123');
        $this->pressButton('Login');
    }

    /**
     * @Given I am logged in as a teacher
     */
    public function iAmLoggedInAsATeacher()
    {
        $this->visit('/login.php');
        $this->fillField('username', 'teacher@test.com');
        $this->fillField('password', 'password123');
        $this->pressButton('Login');
    }

    /**
     * @Given I am logged in as an admin
     */
    public function iAmLoggedInAsAnAdmin()
    {
        $this->visit('/login.php');
        $this->fillField('username', 'admin@test.com');
        $this->fillField('password', 'admin123');
        $this->pressButton('Login');
    }

    /**
     * @Given I fill in all other required fields
     */
    public function iFillInAllOtherRequiredFields()
    {
        $this->fillField('full_name', 'Test User');
        $this->fillField('username', 'testuser' . time());
        $this->fillField('password', 'TestPass123');
        $this->fillField('confirm_password', 'TestPass123');
    }

    /**
     * @Given I have submitted feedback that is :status
     */
    public function iHaveSubmittedFeedbackThatIs($status)
    {
        // Create test feedback in database with given status
        $stmt = $this->dbConnection->prepare(
            "INSERT INTO feedback (user_id, subject, message, status, created_at, is_anonymous) 
             VALUES (:user_id, :subject, :message, :status, NOW(), 1)"
        );

        $stmt->execute([
            'user_id' => 1,
            'subject' => 'Test Feedback',
            'message' => 'Test Message',
            'status' => $status,
        ]);

        $this->createdFeedbackIds[] = (int) $this->dbConnection->lastInsertId();
    }

    /**
     * @Given there is a pending feedback
     */
    public function thereIsAPendingFeedback()
    {
        $this->iHaveSubmittedFeedbackThatIs('Pending');
    }

    /**
     * @Then I should see a list of my previous feedback
     */
    public function iShouldSeeAListOfMyPreviousFeedback()
    {
        $this->assertSession()->elementExists('css', '.feedback-list');
    }

    /**
     * @Then each feedback should show submission date
     */
    public function eachFeedbackShouldShowSubmissionDate()
    {
        $this->assertSession()->elementExists('css', '.feedback-date');
    }

    /**
     * @Then each feedback should show status
     */
    public function eachFeedbackShouldShowStatus()
    {
        $this->assertSession()->elementExists('css', '.feedback-status');
    }

    /**
     * @When I fill in feedback details
     */
    public function iFillInFeedbackDetails()
    {
        $this->selectOption('feedback_type', 'Course Quality');
        $this->fillField('subject', 'Test Feedback');
        $this->fillField('message', 'This is a test feedback message');
        $this->selectOption('rating', '4');
    }

    /**
     * @Then the feedback should be saved without my name
     */
    public function theFeedbackShouldBeSavedWithoutMyName()
    {
        // Verify last inserted feedback is anonymous
        $stmt = $this->dbConnection->query(
            "SELECT is_anonymous FROM feedback ORDER BY id DESC LIMIT 1"
        );
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result || $result['is_anonymous'] != 1) {
            throw new Exception('Feedback was not saved as anonymous');
        }
    }

    /**
     * @Then the feedback status should change to :status
     */
    public function theFeedbackStatusShouldChangeTo($status)
    {
        // Verify feedback status in database
        $this->assertPageContainsText($status);
    }

    /**
     * @Then the student should be notified
     */
    public function theStudentShouldBeNotified()
    {
        // Check for notification in database or UI
        $this->assertSession()->elementExists('css', '.notification-success');
    }

    /**
     * @Then the student should receive the rejection reason
     */
    public function theStudentShouldReceiveTheRejectionReason()
    {
        $this->assertSession()->elementExists('css', '.rejection-reason');
    }

    /**
     * Clean up after each scenario
     *
     * @AfterScenario
     */
    public function cleanUp()
    {
        if (empty($this->createdFeedbackIds)) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($this->createdFeedbackIds), '?'));
        $stmt = $this->dbConnection->prepare("DELETE FROM feedback WHERE id IN ($placeholders)");
        $stmt->execute($this->createdFeedbackIds);
        $this->createdFeedbackIds = [];
    }
}
