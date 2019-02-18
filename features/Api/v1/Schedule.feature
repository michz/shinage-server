@api @schedule
Feature: In order to manage the schedule
  As a REST client
  I can execute the management functions for schedule

  Background:
    Given There is a user with username "apitester@shinage.test" and password "noneededpassword"
    And The user "apitester@shinage.test" has an api key "testapikey" with scope "SCHEDULE"
    And There is a screen with guid "screen1"
    And The screen "screen1" belongs to user "apitester@shinage.test"
    And The user "apitester@shinage.test" has a presentation of type "slideshow" and title "testpres1"
    And The user "apitester@shinage.test" has a presentation of type "slideshow" and title "testpres2"

  Scenario: I can get the empty schedule
    Given I use the api key "testapikey"
    When I get the schedule
    Then I can see that the schedule is empty

  Scenario: I can schedule a presentation
    Given I use the api key "testapikey"
    When I schedule the presentation "testpres1" on screen "screen1" from "2035-02-06 10:00:00" to "2035-02-06 18:00:00"
    Then I can see that the api request was successful
    And I should see in the response that the presentation "testpres1" is scheduled on screen "screen1" from "2035-02-06 10:00:00" to "2035-02-06 18:00:00"

  Scenario: I can see a scheduled presentation
    Given I use the api key "testapikey"
    When I schedule the presentation "testpres1" on screen "screen1" from "2035-02-06 10:00:00" to "2035-02-06 18:00:00"
    And I get the schedule
    Then I should see that the schedule contains exactly "1" item
    And I should see the presentation "testpres1" scheduled on screen "screen1" from "2035-02-06 10:00:00" to "2035-02-06 18:00:00"

  Scenario: I can delete a scheduled presentation
    Given I use the api key "testapikey"
    When I schedule the presentation "testpres1" on screen "screen1" from "2035-02-06 10:00:00" to "2035-02-06 18:00:00"
    And I get the schedule
    And I remember the first item of the schedule
    And I delete the remembered item of the schedule
    And I get the schedule
    Then I can see that the schedule is empty
