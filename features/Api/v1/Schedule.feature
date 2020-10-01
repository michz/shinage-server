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

  Scenario: I can see only scheduled presentations that end after from condition
    Given I use the api key "testapikey"
    When I schedule the presentation "testpres1" on screen "screen1" from "2035-02-06 10:00:00" to "2035-02-06 18:00:00"
    And I schedule the presentation "testpres1" on screen "screen1" from "2035-01-01 10:00:00" to "2035-02-02 18:00:00"
    And I schedule the presentation "testpres2" on screen "screen1" from "2030-01-01 01:00:00" to "2030-01-01 18:00:00"
    And I get the schedule from "2035-02-01 10:00:00"
    Then I should see that the schedule contains exactly "2" items
    And I should see the presentation "testpres1" scheduled on screen "screen1" from "2035-02-06 10:00:00" to "2035-02-06 18:00:00"

  Scenario: I can see only scheduled presentations that start before until condition
    Given I use the api key "testapikey"
    When I schedule the presentation "testpres1" on screen "screen1" from "2035-02-06 10:00:00" to "2035-02-06 18:00:00"
    And I schedule the presentation "testpres1" on screen "screen1" from "2035-02-07 10:00:00" to "2035-03-26 18:00:00"
    And I schedule the presentation "testpres2" on screen "screen1" from "2040-01-01 01:00:00" to "2040-01-01 18:00:00"
    And I get the schedule until "2035-03-06 10:00:00"
    Then I should see that the schedule contains exactly "2" items
    And I should see the presentation "testpres1" scheduled on screen "screen1" from "2035-02-06 10:00:00" to "2035-02-06 18:00:00"

  Scenario: I can see only scheduled presentations that start or end in time range
    Given I use the api key "testapikey"
    When I schedule the presentation "testpres1" on screen "screen1" from "2035-01-01 01:00:00" to "2035-01-20 18:00:00"
    And I schedule the presentation "testpres1" on screen "screen1" from "2035-02-01 01:00:00" to "2035-02-20 18:00:00"
    And I schedule the presentation "testpres2" on screen "screen1" from "2035-03-01 01:00:00" to "2035-03-20 18:00:00"
    And I schedule the presentation "testpres2" on screen "screen1" from "2035-04-01 01:00:00" to "2035-04-20 18:00:00"
    And I schedule the presentation "testpres2" on screen "screen1" from "2035-05-01 01:00:00" to "2035-05-20 18:00:00"
    And I get the schedule from "2035-02-10 10:00:00" until "2035-04-10 10:00:00"
    Then I should see that the schedule contains exactly "3" items
    And I should see the presentation "testpres1" scheduled on screen "screen1" from "2035-02-01 01:00:00" to "2035-02-20 18:00:00"
    And I should see the presentation "testpres2" scheduled on screen "screen1" from "2035-03-01 01:00:00" to "2035-03-20 18:00:00"
    And I should see the presentation "testpres2" scheduled on screen "screen1" from "2035-04-01 01:00:00" to "2035-04-20 18:00:00"

  Scenario: I can delete a scheduled presentation
    Given I use the api key "testapikey"
    When I schedule the presentation "testpres1" on screen "screen1" from "2035-02-06 10:00:00" to "2035-02-06 18:00:00"
    And I get the schedule
    And I remember the first item of the schedule
    And I delete the remembered item of the schedule
    And I get the schedule
    Then I can see that the schedule is empty

  Scenario: I can see an including collision is handled
    Given I use the api key "testapikey"
    When I schedule the presentation "testpres1" on screen "screen1" from "2035-02-06 10:00:00" to "2035-02-06 18:00:00"
    And I schedule the presentation "testpres2" on screen "screen1" from "2035-02-06 08:00:00" to "2035-02-06 20:00:00"
    And I get the schedule
    Then I should see that the schedule contains exactly "1" item
    And I should see the presentation "testpres2" scheduled on screen "screen1" from "2035-02-06 08:00:00" to "2035-02-06 20:00:00"

  Scenario: I can see an tiling collision is handled
    Given I use the api key "testapikey"
    When I schedule the presentation "testpres1" on screen "screen1" from "2035-02-06 10:00:00" to "2035-02-06 18:00:00"
    And I schedule the presentation "testpres2" on screen "screen1" from "2035-02-06 12:00:00" to "2035-02-06 14:00:00"
    And I get the schedule
    Then I should see that the schedule contains exactly "3" item
    And I should see the presentation "testpres1" scheduled on screen "screen1" from "2035-02-06 10:00:00" to "2035-02-06 12:00:00"
    And I should see the presentation "testpres1" scheduled on screen "screen1" from "2035-02-06 14:00:00" to "2035-02-06 18:00:00"
    And I should see the presentation "testpres2" scheduled on screen "screen1" from "2035-02-06 12:00:00" to "2035-02-06 14:00:00"

  Scenario: I can see an ending overlapping collision is handled
    Given I use the api key "testapikey"
    When I schedule the presentation "testpres1" on screen "screen1" from "2035-02-06 10:00:00" to "2035-02-06 18:00:00"
    And I schedule the presentation "testpres2" on screen "screen1" from "2035-02-06 12:00:00" to "2035-02-06 20:00:00"
    And I get the schedule
    Then I should see that the schedule contains exactly "2" item
    And I should see the presentation "testpres1" scheduled on screen "screen1" from "2035-02-06 10:00:00" to "2035-02-06 12:00:00"
    And I should see the presentation "testpres2" scheduled on screen "screen1" from "2035-02-06 12:00:00" to "2035-02-06 20:00:00"

  Scenario: I can see a starting overlapping collision is handled
    Given I use the api key "testapikey"
    When I schedule the presentation "testpres1" on screen "screen1" from "2035-02-06 10:00:00" to "2035-02-06 18:00:00"
    And I schedule the presentation "testpres2" on screen "screen1" from "2035-02-06 08:00:00" to "2035-02-06 12:00:00"
    And I get the schedule
    Then I should see that the schedule contains exactly "2" item
    And I should see the presentation "testpres1" scheduled on screen "screen1" from "2035-02-06 12:00:00" to "2035-02-06 18:00:00"
    And I should see the presentation "testpres2" scheduled on screen "screen1" from "2035-02-06 08:00:00" to "2035-02-06 12:00:00"

  Scenario: I can see an ending edge collision is handled
    Given I use the api key "testapikey"
    When I schedule the presentation "testpres1" on screen "screen1" from "2035-02-06 10:00:00" to "2035-02-06 18:00:00"
    And I schedule the presentation "testpres2" on screen "screen1" from "2035-02-06 12:00:00" to "2035-02-06 18:00:00"
    And I get the schedule
    Then I should see that the schedule contains exactly "2" item
    And I should see the presentation "testpres1" scheduled on screen "screen1" from "2035-02-06 10:00:00" to "2035-02-06 12:00:00"
    And I should see the presentation "testpres2" scheduled on screen "screen1" from "2035-02-06 12:00:00" to "2035-02-06 18:00:00"

  Scenario: I can see a starting edge collision is handled
    Given I use the api key "testapikey"
    When I schedule the presentation "testpres1" on screen "screen1" from "2035-02-06 10:00:00" to "2035-02-06 18:00:00"
    And I schedule the presentation "testpres2" on screen "screen1" from "2035-02-06 10:00:00" to "2035-02-06 14:00:00"
    And I get the schedule
    Then I should see that the schedule contains exactly "2" item
    And I should see the presentation "testpres1" scheduled on screen "screen1" from "2035-02-06 14:00:00" to "2035-02-06 18:00:00"
    And I should see the presentation "testpres2" scheduled on screen "screen1" from "2035-02-06 10:00:00" to "2035-02-06 14:00:00"
