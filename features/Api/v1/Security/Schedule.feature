@api @security @schedule
Feature: In order to manage the schedule securely
  As a REST client
  I can execute the management functions for my schedules

  Background:
    Given There is a user with username "singer@shinage.test" and password "noneededpassword"
    And There is a user with username "drummer@shinage.test" and password "noneededpassword"
    And There is a user with username "guitarist@shinage.test" and password "noneededpassword"
    And The user "singer@shinage.test" has an api key "voice" with scope "SCHEDULE"
    And The user "drummer@shinage.test" has an api key "stick" with scope "SCHEDULE"
    And The user "guitarist@shinage.test" has an api key "finger" with scope "SCHEDULE"
    And There is a screen with guid "sheet"
    And The screen "sheet" belongs to user "singer@shinage.test"
    And The user "singer@shinage.test" has a presentation of type "slideshow" and title "song1"
    And The user "singer@shinage.test" has a presentation of type "slideshow" and title "song2"
    And The presentation "song1" is scheduled now for screen "sheet"

  Scenario: I cannot see another user's schedule
    Given I use the api key "stick"
    When I get the schedule
    Then I can see that the schedule is empty

  Scenario: I cannot schedule on another user's screen
    Given I use the api key "stick"
    When I schedule the presentation "song2" on screen "sheet" from "2035-03-01 00:05:10" to "2035-03-01 23:00:59"
    Then I should get an Access Denied response

  Scenario: I cannot delete another user's scheduled presentation
    Given I use the api key "stick"
    When I delete the presentation "song1" from screen "sheet"
    Then I should get an Access Denied response
