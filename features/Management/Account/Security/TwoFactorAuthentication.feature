@management @account @security
Feature: In order to login more securely
  As a user
  I can configure two factor authentication

  Background:
    Given There is a user with username "test@test.test" and password "testPassword"
    And The user "test@test.test" has the roles "ROLE_SUPER_ADMIN"
    And The user "test@test.test" has two factor authentication disabled at all
    And I am logged in as user "test@test.test" with password "testPassword"

  Scenario: I can see that all two factor authentication modes are disabled
    Given I am on page "manage/account/security"
    Then I should see that two factor authentication via totp is disabled
    And I should see that two factor authentication via mail is disabled

  Scenario: I can enable two factor authentification via email
    Given I am on page "manage/account/security"
    And I click on the `enable mail auth` button
    Then I should see that two factor authentication via mail is enabled
