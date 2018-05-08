@api @screen_remote
Feature: As a player
  I can do a so called heartbeat

  Background:
    Given There is a screen with guid "012345"


  @todo
  Scenario: I can see that the screen was not registered yet
    When I do a heartbeat with guid "11111"
    Then I should see that the screen is not registered



