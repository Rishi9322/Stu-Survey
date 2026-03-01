<?php

namespace Tests\Unit\BlackBox;

use PHPUnit\Framework\TestCase;

/**
 * Demo Black Box Tests with Intentional Failures
 * These tests demonstrate how failures appear in the HTML reports
 */
class DemoFailuresBlackBoxTest extends TestCase
{
    /**
     * Test 1: This test will pass - Valid login
     */
    public function testValidLoginPass()
    {
        $this->assertTrue(true, "Valid login should succeed");
    }

    /**
     * Test 2: This test will fail - Invalid password handling
     */
    public function testInvalidPasswordFail()
    {
        // Intentionally failing assertion
        $this->assertEquals('error', 'success', "Password validation failed - expected error but got success");
    }

    /**
     * Test 3: This test will pass - User registration
     */
    public function testUserRegistrationPass()
    {
        $this->assertNotEmpty(['user' => 'test'], "Registration data should not be empty");
    }

    /**
     * Test 4: This test will fail - Email validation
     */
    public function testEmailValidationFail()
    {
        // Intentionally failing assertion
        $this->assertStringContainsString('@', 'invalid-email', "Email should contain @ symbol");
    }

    /**
     * Test 5: This test will pass - Session creation
     */
    public function testSessionCreationPass()
    {
        $this->assertIsArray(['session_id' => '123'], "Session should be an array");
    }

    /**
     * Test 6: This test will fail - Password strength check
     */
    public function testPasswordStrengthFail()
    {
        $weakPassword = 'abc';
        $this->assertGreaterThan(8, strlen($weakPassword), "Password must be longer than 8 characters");
    }

    /**
     * Test 7: This test will pass - User logout
     */
    public function testUserLogoutPass()
    {
        $this->assertNull(null, "Session should be null after logout");
    }

    /**
     * Test 8: This test will fail - Role validation
     */
    public function testRoleValidationFail()
    {
        $userRole = 'guest';
        $this->assertContains($userRole, ['admin', 'teacher', 'student'], "Role must be valid");
    }

    /**
     * Test 9: This test will pass - Profile update
     */
    public function testProfileUpdatePass()
    {
        $this->assertIsString('John Doe', "Name should be a string");
    }

    /**
     * Test 10: This test will fail - Account activation
     */
    public function testAccountActivationFail()
    {
        $accountStatus = 'pending';
        $this->assertEquals('active', $accountStatus, "Account should be activated");
    }
}
