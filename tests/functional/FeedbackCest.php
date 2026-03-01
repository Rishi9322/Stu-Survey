<?php

namespace Tests\Functional;

use Tests\Support\FunctionalTester;

class FeedbackCest
{
    // Test Student Credentials
    private $studentCredentials = [
        'email' => 'student@test.com',
        'password' => 'Student123!'
    ];

    private function loginAsStudent(FunctionalTester $I)
    {
        $I->amOnPage('/login.php');
        $I->fillField('username', $this->studentCredentials['email']);
        $I->fillField('password', $this->studentCredentials['password']);
        $I->click('Login');
    }

    public function testFeedbackPageLoads(FunctionalTester $I)
    {
        $I->wantTo('Verify feedback page loads for logged-in student');
        $this->loginAsStudent($I);
        $I->amOnPage('/app/student/feedback.php');
        $I->seeResponseCodeIs(200);
        $I->see('Submit Feedback');
    }

    public function testSubmitBasicFeedback(FunctionalTester $I)
    {
        $I->wantTo('Submit basic feedback as a student');
        $this->loginAsStudent($I);
        $I->amOnPage('/app/student/feedback.php');
        
        $I->selectOption('feedback_type', 'Course Quality');
        $I->fillField('subject', 'Great Course');
        $I->fillField('message', 'The course content was excellent');
        $I->selectOption('rating', '5');
        $I->click('Submit Feedback');
        
        $I->see('Feedback submitted successfully');
    }

    public function testSubmitAnonymousFeedback(FunctionalTester $I)
    {
        $I->wantTo('Submit anonymous feedback');
        $this->loginAsStudent($I);
        $I->amOnPage('/app/student/feedback.php');
        
        $I->checkOption('is_anonymous');
        $I->selectOption('feedback_type', 'Teaching Method');
        $I->fillField('subject', 'Anonymous Feedback');
        $I->fillField('message', 'This is anonymous feedback');
        $I->selectOption('rating', '4');
        $I->click('Submit Feedback');
        
        $I->see('Feedback submitted successfully');
    }

    public function testViewFeedbackHistory(FunctionalTester $I)
    {
        $I->wantTo('View my feedback history');
        $this->loginAsStudent($I);
        $I->amOnPage('/app/student/feedback-history.php');
        
        $I->seeResponseCodeIs(200);
        $I->see('My Feedback History');
    }

    public function testCannotSubmitEmptyFeedback(FunctionalTester $I)
    {
        $I->wantTo('Ensure empty feedback cannot be submitted');
        $this->loginAsStudent($I);
        $I->amOnPage('/app/student/feedback.php');
        
        $I->click('Submit Feedback');
        $I->see('Please fill in all required fields');
    }

    public function testFeedbackRequiresLogin(FunctionalTester $I)
    {
        $I->wantTo('Verify feedback page requires authentication');
        $I->amOnPage('/app/student/feedback.php');
        
        // Should redirect to login
        $I->seeCurrentUrlEquals('/login.php');
        $I->see('Please log in');
    }
}
