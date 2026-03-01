Feature: Feedback Management
  As a student
  I want to submit feedback
  So that I can share my experience

  Background:
    Given I am logged in as a student
    
  Scenario: Submit new feedback
    Given I am on "/app/student/feedback.php"
    When I select "Course Quality" from "feedback_type"
    And I fill in "subject" with "Great Course Experience"
    And I fill in "message" with "The course was well structured and informative"
    And I select "5" from "rating"
    And I press "Submit Feedback"
    Then I should see "Feedback submitted successfully"
    And I should be on "/app/student/dashboard.php"
    
  Scenario: View my feedback history
    Given I am on "/app/student/feedback-history.php"
    Then I should see a list of my previous feedback
    And each feedback should show submission date
    And each feedback should show status
    
  Scenario: Edit pending feedback
    Given I have submitted feedback that is "Pending"
    When I am on "/app/student/feedback-history.php"
    And I click "Edit" on the pending feedback
    Then I should be able to modify the message
    And I should be able to change the rating
    
  Scenario: Cannot edit approved feedback
    Given I have submitted feedback that is "Approved"
    When I am on "/app/student/feedback-history.php"
    Then I should not see "Edit" button for approved feedback
    
  Scenario: Anonymous feedback submission
    Given I am on "/app/student/feedback.php"
    When I check "Submit anonymously"
    And I fill in feedback details
    And I press "Submit Feedback"
    Then the feedback should be saved without my name
