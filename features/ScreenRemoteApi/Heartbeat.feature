@api @screen_remote
Feature: As a player
  I can do a so called heartbeat


  Background:
    Given There is a screen with guid "123456"


  Scenario: I can see that the screen was not registered yet
    When I do a heartbeat with guid "11111"
    Then I should see that the screen is not registered


  Scenario: I can see that the screen was registered
    When I do a heartbeat with guid "123456"
    Then I should see that the screen is registered


  # @TODO Test if last connect date is set correctly

