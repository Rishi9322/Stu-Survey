Feature: User Registration
  As a new user
  I want to register an account
  So that I can use the system

  Scenario: Successful student registration
    Given I am on "/register.php"
    When I select "Student" from "user_type"
    And I fill in "full_name" with "John Doe"
    And I fill in "email" with "john.doe@test.com"
    And I fill in "username" with "johndoe"
    And I fill in "password" with "SecurePass123"
    And I fill in "confirm_password" with "SecurePass123"
    And I press "Register"
    Then I should see "Registration successful"
    And I should be on "/login.php"
    
  Scenario: Registration with existing email
    Given I am on "/register.php"
    When I fill in "email" with "existing@test.com"
    And I fill in all other required fields
    And I press "Register"
    Then I should see "Email already exists"
    
  Scenario: Registration with mismatched passwords
    Given I am on "/register.php"
    When I fill in "password" with "Password123"
    And I fill in "confirm_password" with "DifferentPass123"
    And I press "Register"
    Then I should see "Passwords do not match"
    
  Scenario: Registration with invalid email format
    Given I am on "/register.php"
    When I fill in "email" with "invalid-email"
    And I press "Register"
    Then I should see "Please enter a valid email"
    
  Scenario: Teacher registration requires additional info
    Given I am on "/register.php"
    When I select "Teacher" from "user_type"
    Then I should see "Department" field
    And I should see "Subject" field
