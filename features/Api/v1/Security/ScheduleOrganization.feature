@api @security @schedule @organization
Feature: In order to manage the schedule in an organization
  As a REST client
  I can execute the management functions for my and my teams' schedules

  Background:
    Given There is a user with username "singer@shinage.test" and password "noneededpassword"
    And There is a user with username "drummer@shinage.test" and password "noneededpassword"
    And The user "singer@shinage.test" has an api key "voice" with scope "SCHEDULE"
    And The user "drummer@shinage.test" has an api key "stick" with scope "SCHEDULE"
    And There is an organization with name "band"
    And The user "singer@shinage.test" belongs to the organization "band"
    And The user "drummer@shinage.test" belongs to the organization "band"
    And There is a screen with guid "sheet"
    And The screen "sheet" belongs to user "singer@shinage.test"
    And The organization "band" has the right to "schedule" for the screen "sheet"
    And The user "band" has a presentation of type "slideshow" and title "song1"
    And The user "band" has a presentation of type "slideshow" and title "song2"
    And The presentation "song1" is scheduled now for screen "sheet"

  Scenario: I can see the schedule of the organization
    Given I use the api key "stick"
    When I get the schedule
    Then I should see that the schedule contains exactly 1 item

  Scenario: I can schedule a presentation
    Given I use the api key "stick"
    When I schedule the presentation "song1" on screen "sheet" from "2035-02-06 10:00:00" to "2035-02-06 18:00:00"
    Then I should get a No Content response

  Scenario: I can see a scheduled presentation
    Given I use the api key "stick"
    When I schedule the presentation "song1" on screen "sheet" from "2035-02-06 10:00:00" to "2035-02-06 18:00:00"
    And I get the schedule
    Then I should see that the schedule contains exactly "2" items
    And I should see the presentation "song1" scheduled on screen "sheet" from "2035-02-06 10:00:00" to "2035-02-06 18:00:00"

  Scenario: I can delete a scheduled presentation
    Given I use the api key "stick"
    When I schedule the presentation "song1" on screen "sheet" from "2035-02-06 10:00:00" to "2035-02-06 18:00:00"
    And I get the schedule
    And I remember the first item of the schedule
    And I delete the remembered item of the schedule
    And I get the schedule
    Then I should see that the schedule contains exactly 1 item
