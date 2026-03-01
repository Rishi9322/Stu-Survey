<?php

namespace Tests\Functional;

use Tests\Support\FunctionalTester;

class LoginCest
{
    // Test User Credentials
    private $studentUser = [
        'email' => 'student@test.com',
        'password' => 'Student123!',
        'role' => 'student'
    ];

    private $teacherUser = [
        'email' => 'teacher@test.com',
        'password' => 'Teacher123!',
        'role' => 'teacher'
    ];

    private $adminUser = [
        'email' => 'admin@test.com',
        'password' => 'Admin123!',
        'role' => 'admin'
    ];

    public function _before(FunctionalTester $I)
    {
        // Clear cookies/session before each test
    }

    public function testLoginPageLoads(FunctionalTester $I)
    {
        $I->wantTo('Verify login page loads correctly');
        $I->amOnPage('/login.php');
        $I->seeResponseCodeIs(200);
        $I->see('Login', 'h1');
        $I->seeElement('input[name="username"]');
        $I->seeElement('input[name="password"]');
        $I->seeElement('button[type="submit"]');
    }

    public function testSuccessfulStudentLogin(FunctionalTester $I)
    {
        $I->wantTo('Test successful student login with credentials: ' . $this->studentUser['email']);
        $I->amOnPage('/login.php');
        $I->fillField('username', $this->studentUser['email']);
        $I->fillField('password', $this->studentUser['password']);
        $I->click('Login');
        
        // Should redirect to student dashboard
        $I->seeCurrentUrlEquals('/app/student/dashboard.php');
        $I->see('Student Dashboard');
    }

    public function testSuccessfulTeacherLogin(FunctionalTester $I)
    {
        $I->wantTo('Test successful teacher login with credentials: ' . $this->teacherUser['email']);
        $I->amOnPage('/login.php');
        $I->fillField('username', $this->teacherUser['email']);
        $I->fillField('password', $this->teacherUser['password']);
        $I->click('Login');
        
        $I->seeCurrentUrlEquals('/app/teacher/dashboard.php');
        $I->see('Teacher Dashboard');
    }

    public function testFailedLoginWithWrongPassword(FunctionalTester $I)
    {
        $I->wantTo('Test login fails with wrong password for user: ' . $this->studentUser['email']);
        $I->amOnPage('/login.php');
        $I->fillField('username', $this->studentUser['email']);
        $I->fillField('password', 'WrongPassword999');
        $I->click('Login');
        
        $I->seeCurrentUrlEquals('/login.php');
        $I->see('Invalid credentials');
    }

    public function testFailedLoginWithEmptyFields(FunctionalTester $I)
    {
        $I->wantTo('Test login fails with empty fields');
        $I->amOnPage('/login.php');
        $I->click('Login');
        
        $I->seeCurrentUrlEquals('/login.php');
        $I->see('Please fill in all fields');
    }

    public function testFailedLoginWithNonexistentUser(FunctionalTester $I)
    {
        $I->wantTo('Test login fails with nonexistent user');
        $I->amOnPage('/login.php');
        $I->fillField('username', 'nonexistent@test.com');
        $I->fillField('password', 'password123');
        $I->click('Login');
        
        $I->seeCurrentUrlEquals('/login.php');
        $I->see('Invalid credentials');
    }

    public function testLogoutFunctionality(FunctionalTester $I)
    {
        $I->wantTo('Test logout functionality for user: ' . $this->studentUser['email']);
        
        // First login with correct credentials
        $I->amOnPage('/login.php');
        $I->fillField('username', $this->studentUser['email']);
        $I->fillField('password', $this->studentUser['password']);
        $I->click('Login');
        
        // Then logout
        $I->click('Logout');
        $I->seeCurrentUrlEquals('/index.php');
        $I->see('You have been logged out');
        
        // Try to access protected page
        $I->amOnPage('/app/student/dashboard.php');
        $I->seeCurrentUrlEquals('/login.php');
    }
}
