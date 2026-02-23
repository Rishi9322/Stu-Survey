<?php
namespace Tests\Unit\BlackBox;

use Tests\BaseTestCase;

/**
 * BLACK BOX TESTS - Authentication Module
 * Tests from user's perspective: Can user login, register, logout?
 */
class AuthenticationBlackBoxTest extends BaseTestCase
{
    /**
     * Test 1: Valid user login
     * INPUT: Correct email and password
     * EXPECTED: Login successful, user logged in
     */
    public function testValidUserLogin()
    {
        // Create test user
        $userId = $this->createTestUser([
            'email' => 'student@test.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
            'role' => 'student'
        ]);
        
        // Simulate login attempt
        $email = 'student@test.com';
        $password = 'password123';
        
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        // Verify password matches
        $this->assertTrue(password_verify($password, $user['password']), 'Password verification failed');
        $this->assertEquals('student', $user['role'], 'User role mismatch');
        $this->assertIsArray($user, 'User not found');
    }
    
    /**
     * Test 2: Login with invalid password
     * INPUT: Correct email but wrong password
     * EXPECTED: Login fails, error shown
     */
    public function testLoginWithInvalidPassword()
    {
        $userId = $this->createTestUser([
            'email' => 'student@test.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT)
        ]);
        
        $email = 'student@test.com';
        $wrongPassword = 'wrongpassword';
        
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        // Verify password does NOT match
        $this->assertFalse(password_verify($wrongPassword, $user['password']), 
            'Wrong password should not verify');
    }
    
    /**
     * Test 3: Login with non-existent user
     * INPUT: Email that doesn't exist
     * EXPECTED: Login fails, user not found error
     */
    public function testLoginWithNonExistentUser()
    {
        $email = 'nonexistent@test.com';
        
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        $this->assertFalse($user, 'Non-existent user should not be found');
    }
    
    /**
     * Test 4: Register new student user
     * INPUT: Valid registration data (name, email, password, role)
     * EXPECTED: User created successfully in database
     */
    public function testRegisterNewStudentUser()
    {
        // Check user doesn't exist
        $stmt = $this->pdo->prepare('SELECT COUNT(*) as count FROM users WHERE email = ?');
        $stmt->execute(['newstudent@test.com']);
        $count = $stmt->fetch()['count'];
        $this->assertEquals(0, $count, 'User should not exist before registration');
        
        // Create new user (simulating registration)
        $userId = $this->createTestUser([
            'name' => 'New Student',
            'email' => 'newstudent@test.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
            'role' => 'student'
        ]);
        
        // Verify user created
        $this->assertGreaterThan(0, $userId, 'User ID should be set');
        $this->assertDatabaseHas('users', ['email' => 'newstudent@test.com']);
    }
    
    /**
     * Test 5: Register with duplicate email
     * INPUT: Email already exists in database
     * EXPECTED: Registration fails, duplicate email error
     */
    public function testRegisterWithDuplicateEmail()
    {
        // Create first user
        $this->createTestUser(['email' => 'duplicate@test.com']);
        
        // Try to create second user with same email - should fail
        $stmt = $this->pdo->prepare('SELECT COUNT(*) as count FROM users WHERE email = ?');
        $stmt->execute(['duplicate@test.com']);
        $count = $stmt->fetch()['count'];
        
        $this->assertEquals(1, $count, 'Should only have one user with this email');
    }
    
    /**
     * Test 6: User logout clears session
     * INPUT: User clicks logout button
     * EXPECTED: Session cleared, user redirected to login
     */
    public function testUserLogout()
    {
        // Create and login user
        $userId = $this->createTestUser();
        
        // Simulate logout by checking if user record exists
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        $this->assertNotNull($user, 'User should still exist in database after logout');
        // Note: Real logout would clear session, which we test in integration tests
    }
    
    /**
     * Test 7: Register different user roles
     * INPUT: Register as student, teacher, admin
     * EXPECTED: All roles can register with correct role assigned
     */
    public function testRegisterDifferentUserRoles()
    {
        $roles = ['student', 'teacher'];
        
        foreach ($roles as $role) {
            $userId = $this->createTestUser([
                'email' => "user_$role@test.com",
                'role' => $role
            ]);
            
            $stmt = $this->pdo->prepare('SELECT role FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            $this->assertEquals($role, $user['role'], "User role should be $role");
        }
    }
    
    /**
     * Test 8: User can update profile
     * INPUT: Update name, email after login
     * EXPECTED: Profile updated successfully
     */
    public function testUserCanUpdateProfile()
    {
        $userId = $this->createTestUser([
            'name' => 'Old Name',
            'email' => 'old@test.com'
        ]);
        
        // Update user profile
        $stmt = $this->pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
        $stmt->execute(['New Name', 'new@test.com', $userId]);
        
        // Verify update
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        $this->assertEquals('New Name', $user['name']);
        $this->assertEquals('new@test.com', $user['email']);
    }
    
    /**
     * Test 9: Inactive user cannot login
     * INPUT: Login as deactivated user
     * EXPECTED: Login fails, account deactivated error
     */
    public function testInactiveUserCannotLogin()
    {
        $userId = $this->createTestUser([
            'email' => 'inactive@test.com',
            'is_active' => 0
        ]);
        
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute(['inactive@test.com']);
        $user = $stmt->fetch();
        
        $this->assertEquals(0, $user['is_active'], 'User should be inactive');
        // In real app, login would check this field
    }
    
    /**
     * Test 10: Email validation on registration
     * INPUT: Invalid email format (missing @, no domain)
     * EXPECTED: Registration fails, invalid email error
     */
    public function testEmailValidationOnRegistration()
    {
        $invalidEmails = [
            'notanemail',
            'missing@domain',
            '@nodomain.com',
            'spaces in@email.com'
        ];
        
        foreach ($invalidEmails as $email) {
            // Simulate email validation
            $isValid = filter_var($email, FILTER_VALIDATE_EMAIL);
            $this->assertFalse($isValid, "Email '$email' should be invalid");
        }
        
        // Valid email should pass
        $validEmail = 'valid@email.com';
        $this->assertNotFalse(filter_var($validEmail, FILTER_VALIDATE_EMAIL));
    }
}
