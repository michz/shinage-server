@api @schedule
Feature: In order to read the schedule
  As a screen using a REST client
  I can execute the readonly management functions for schedule

  Background:
    Given There is a user with username "apitester@shinage.test" and password "noneededpassword"
    And There is a screen with guid "screen1"
    And The screen "screen1" belongs to user "apitester@shinage.test"
    And The user "apitester@shinage.test" has a presentation of type "slideshow" and title "testpres1"
    And The user "apitester@shinage.test" has a presentation of type "slideshow" and title "testpres2"

  Scenario: I can get the empty schedule
    Given I use the screen guid "screen1" for rest authentication
    When I get the schedule
    Then I can see that the schedule is empty

  Scenario: I cannot schedule a presentation
    Given I use the screen guid "screen1" for rest authentication
    When I schedule the presentation "testpres1" on screen "screen1" from "2035-02-06 10:00:00" to "2035-02-06 18:00:00"
    Then I should get an Access Denied response

  Scenario: I can see a scheduled presentation
    Given I use the screen guid "screen1" for rest authentication
    When The presentation "testpres1" is scheduled on screen "screen1" from "2035-02-06 10:00:00" to "2035-02-06 18:00:00"
    And I get the schedule
    Then I should see that the schedule contains exactly "1" item
    And I should see the presentation "testpres1" scheduled on screen "screen1" from "2035-02-06 10:00:00" to "2035-02-06 18:00:00"

  Scenario: I cannot delete a scheduled presentation
    Given I use the screen guid "screen1" for rest authentication
    And The presentation "testpres1" is scheduled on screen "screen1" from "2035-02-06 10:00:00" to "2035-02-06 18:00:00"
    When I get the schedule
    And I remember the first item of the schedule
    And I delete the remembered item of the schedule
    Then I should get an Access Denied response

# TODO: Move the following into an own feature file
  Scenario: I can get a full presentation that is scheduled in my screen
    Given I use the screen guid "screen1" for rest authentication
    And The presentation "testpres1" is scheduled on screen "screen1" from "2035-02-06 10:00:00" to "2035-02-06 18:00:00"
    And I get the presentation "testpres1"
    Then I can see that the api request was successful

  Scenario: I cannot modify a full presentation that is scheduled in my screen
    Given I use the screen guid "screen1" for rest authentication
    And The presentation "testpres1" is scheduled on screen "screen1" from "2035-02-06 10:00:00" to "2035-02-06 18:00:00"
    And I update the presentation "testpres1" with settings:
      """
      {"slides":[{"title":"NewSlideWithTestString"},{"title":"AnotherNewSlideWithTestString"}]}
      """
    Then I should get an Access Denied response

  Scenario: I cannot delete a full presentation that is scheduled in my screen
    Given I use the screen guid "screen1" for rest authentication
    And The presentation "testpres1" is scheduled on screen "screen1" from "2035-02-06 10:00:00" to "2035-02-06 18:00:00"
    And I delete the presentation "testpres1"
    Then I should get an Access Denied response
