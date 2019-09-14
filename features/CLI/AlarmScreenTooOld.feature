@cli @alarming @cron

Feature: As a cli user
  In order to detect problematic screens
  I want to check the last connection date on cli

  Background:
    Given There is a screen with guid "1"


  Scenario: There is no screen with alarming enabled
    Given The screen "1" has alarming disabled
    When I run the alarming cron job
    Then I should not see any new mails

  Scenario: There is a screen with alarming enabled but it connected correctly
    Given The screen "1" has alarming enabled
    And The screen "1" has the last connection alarming threshold set to "120" minutes
    And The screen "1" has last connected "10" minutes ago
    When I run the alarming cron job
    Then I should not see any new mails

  Scenario: There is a screen with alarming enabled that did not connect recently
    Given The screen "1" has alarming enabled
    And The screen "1" has the alarming mail address set to "test@shinage.test"
    And The screen "1" has the last connection alarming threshold set to "10" minutes
    And The screen "1" has last connected "120" minutes ago
    When I run the alarming cron job
    Then I should see a new mail to "test@shinage.test"

    #@TODO Add a feature to test notification period
