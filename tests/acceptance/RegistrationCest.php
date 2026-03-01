<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class RegistrationCest
{
    public function testRegistrationPageLoads(AcceptanceTester $I)
    {
        $I->wantTo('Verify registration page loads correctly');
        $I->amOnPage('/register.php');
        $I->seeResponseCodeIs(200);
        $I->see('Register', 'h1');
        $I->seeElement('select[name="user_type"]');
        $I->seeElement('input[name="full_name"]');
        $I->seeElement('input[name="email"]');
        $I->seeElement('input[name="password"]');
    }

    public function testSuccessfulStudentRegistration(AcceptanceTester $I)
    {
        $I->wantTo('Register a new student account');
        $I->amOnPage('/register.php');
        
        $timestamp = time();
        $I->selectOption('user_type', 'Student');
        $I->fillField('full_name', 'Test Student');
        $I->fillField('email', "student{$timestamp}@test.com");
        $I->fillField('username', "student{$timestamp}");
        $I->fillField('password', 'SecurePass123');
        $I->fillField('confirm_password', 'SecurePass123');
        $I->click('Register');
        
        $I->see('Registration successful');
        $I->seeCurrentUrlEquals('/login.php');
    }

    public function testRegistrationWithExistingEmail(AcceptanceTester $I)
    {
        $I->wantTo('Verify registration fails with existing email');
        $I->amOnPage('/register.php');
        
        // Try to register with an existing test user email
        $I->selectOption('user_type', 'Student');
        $I->fillField('full_name', 'Test User');
        $I->fillField('email', 'student@test.com'); // Existing test user email
        $I->fillField('username', 'testuser' . time());
        $I->fillField('password', 'SecurePass123');
        $I->fillField('confirm_password', 'SecurePass123');
        $I->click('Register');
        
        $I->see('Email already exists');
        $I->seeCurrentUrlEquals('/register.php');
    }

    public function testRegistrationWithMismatchedPasswords(AcceptanceTester $I)
    {
        $I->wantTo('Verify registration fails with mismatched passwords');
        $I->amOnPage('/register.php');
        
        $timestamp = time();
        $I->selectOption('user_type', 'Student');
        $I->fillField('full_name', 'Test User');
        $I->fillField('email', "user{$timestamp}@test.com");
        $I->fillField('username', "user{$timestamp}");
        $I->fillField('password', 'Password123');
        $I->fillField('confirm_password', 'DifferentPass123');
        $I->click('Register');
        
        $I->see('Passwords do not match');
    }

    public function testRegistrationWithInvalidEmail(AcceptanceTester $I)
    {
        $I->wantTo('Verify registration fails with invalid email');
        $I->amOnPage('/register.php');
        
        $I->selectOption('user_type', 'Student');
        $I->fillField('full_name', 'Test User');
        $I->fillField('email', 'invalid-email');
        $I->fillField('username', 'testuser');
        $I->fillField('password', 'SecurePass123');
        $I->fillField('confirm_password', 'SecurePass123');
        $I->click('Register');
        
        $I->see('Please enter a valid email');
    }

    public function testRegistrationWithWeakPassword(AcceptanceTester $I)
    {
        $I->wantTo('Verify registration requires strong password');
        $I->amOnPage('/register.php');
        
        $timestamp = time();
        $I->selectOption('user_type', 'Student');
        $I->fillField('full_name', 'Test User');
        $I->fillField('email', "user{$timestamp}@test.com");
        $I->fillField('username', "user{$timestamp}");
        $I->fillField('password', '123'); // Too short
        $I->fillField('confirm_password', '123');
        $I->click('Register');
        
        $I->see('Password must be at least');
    }

    public function testTeacherRegistrationShowsAdditionalFields(AcceptanceTester $I)
    {
        $I->wantTo('Verify teacher registration shows additional fields');
        $I->amOnPage('/register.php');
        
        $I->selectOption('user_type', 'Teacher');
        $I->seeElement('input[name="department"]');
        $I->seeElement('input[name="subject"]');
    }

    public function testRegistrationFormValidation(AcceptanceTester $I)
    {
        $I->wantTo('Test client-side form validation');
        $I->amOnPage('/register.php');
        $I->click('Register');
        
        // Should show validation messages
        $I->see('Please fill in all fields');
    }
}
