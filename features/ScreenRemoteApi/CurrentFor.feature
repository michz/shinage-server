@api @screen_remote @current_for
Feature: As a player
  I can get the current presentation for a screen

  Background:
    Given There is a screen with guid "1"
    And There is a presentation of type "splash" called "Test1"
    And The presentation "Test1" is scheduled now for screen "1"
    And There is a screen with guid "2"

  Scenario: I can get the current presentation
    When I ask for the current presentation of screen "1"
    Then I should see the url of presentation "Test1"
    And I the last connect timestamp of screen "1" is now

  Scenario: I can get a register presentation if screen does not exist
    When I ask for the current presentation of screen "2"
    Then I should see the url of splash presentation with connect code of screen "2"

  # @TODO Test if last connect date is set correctly

