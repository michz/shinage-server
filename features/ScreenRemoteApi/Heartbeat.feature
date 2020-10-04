@api @screen_remote
Feature: As a player
  I can do a so called heartbeat


  Background:
    Given There is a screen with guid "123456"
    And The screen "123456" belongs to an arbitrary user


  Scenario: I can see that the screen was not registered yet
    When I do a heartbeat with guid "11111"
    Then I should see that the screen is not registered


  Scenario: I can see that the screen was registered
    When I do a heartbeat with guid "123456"
    Then I should see that the screen is registered


  Scenario: I can see that there is a command available for screen
    Given There is a command "reboot" available for screen "123456"
    When I do a heartbeat with guid "123456"
    Then I should see that there is a command "reboot" available


