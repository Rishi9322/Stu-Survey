Feature: User Login
  As a registered user
  I want to log into the system
  So that I can access my dashboard

  Background:
    Given I am on the homepage
    
  Scenario: Successful student login
    Given I am on "/login.php"
    When I fill in "username" with "student@test.com"
    And I fill in "password" with "password123"
    And I press "Login"
    Then I should see "Student Dashboard"
    And I should be on "/app/student/dashboard.php"
    
  Scenario: Successful teacher login
    Given I am on "/login.php"
    When I fill in "username" with "teacher@test.com"
    And I fill in "password" with "password123"
    And I press "Login"
    Then I should see "Teacher Dashboard"
    And I should be on "/app/teacher/dashboard.php"
    
  Scenario: Failed login with wrong password
    Given I am on "/login.php"
    When I fill in "username" with "student@test.com"
    And I fill in "password" with "wrongpassword"
    And I press "Login"
    Then I should see "Invalid credentials"
    And I should be on "/login.php"
    
  Scenario: Login with empty credentials
    Given I am on "/login.php"
    When I press "Login"
    Then I should see "Please fill in all fields"
    
  Scenario: Logout functionality
    Given I am logged in as a student
    When I click "Logout"
    Then I should be on "/public/index.php"
    And I should see "You have been logged out"
