Feature: Admin Dashboard
  As an administrator
  I want to manage the system
  So that I can maintain quality and oversight

  Background:
    Given I am logged in as an admin
    
  Scenario: View all feedback submissions
    Given I am on "/app/admin/feedback-management.php"
    Then I should see all pending feedback
    And I should see approved feedback
    And I should see rejected feedback
    
  Scenario: Approve feedback
    Given I am on "/app/admin/feedback-management.php"
    And there is a pending feedback
    When I click "Approve" on the feedback
    Then the feedback status should change to "Approved"
    And the student should be notified
    
  Scenario: Reject feedback with reason
    Given I am on "/app/admin/feedback-management.php"
    And there is a pending feedback
    When I click "Reject" on the feedback
    And I fill in "rejection_reason" with "Does not meet quality standards"
    And I press "Confirm Rejection"
    Then the feedback status should change to "Rejected"
    And the student should receive the rejection reason
    
  Scenario: Manage user accounts
    Given I am on "/app/admin/user-management.php"
    Then I should see a list of all users
    And I should be able to filter by user type
    And I should be able to search by name or email
    
  Scenario: Deactivate user account
    Given I am on "/app/admin/user-management.php"
    When I click "Deactivate" for a user
    Then the user should not be able to log in
    And the user should receive a notification
    
  Scenario: View system analytics
    Given I am on "/app/admin/analytics.php"
    Then I should see total feedback count
    And I should see feedback by type chart
    And I should see average ratings
    And I should see user activity statistics
