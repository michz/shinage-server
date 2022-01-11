@login

  Feature: I can login

    Background:
      Given I am on page "login"
      And There is a user with username "tester@shinage.dev" and password "testpassword"
      And The user "tester@shinage.dev" has the roles "ROLE_SUPER_ADMIN"

    Scenario: I can see login form
      Then I see an input field with name "_username"
      And I see an input field with name "_password"
      And I see an button with name "_submit"

    Scenario: I cannot login with wrong credentials
      When I fill the field "_username" with "tester2@shinage.dev"
      And I fill the field "_password" with "test2password"
      And I click on the button "_submit"
      Then I should be on page "login" again
      And I should see an error message
