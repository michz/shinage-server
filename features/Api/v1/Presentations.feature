@api @presentations
Feature: In order to manage presentations remotely
  As a REST client
  I can execute the management functions

  Background:
    Given There is a user with username "apitester@shinage.test" and password "noneededpassword"
    And The user "apitester@shinage.test" has the roles "ROLE_SUPER_ADMIN"
    And The user "apitester@shinage.test" has an api key "testapikey" with scope "PRESENTATIONS"
    And There is a user with username "othertester@shinage.test" and password "noneededpassword"

  Scenario: I can get a an empty list of presentations
    Given I use the api key "testapikey"
    When I get the list of presentations
    Then I can see that the api request was successful
    And I can see that the api response contains no presentation

  Scenario: I can get a list of presentations
    Given I use the api key "testapikey"
    And The user "apitester@shinage.test" has a presentation of type "slideshow" and title "testpres"
    When I get the list of presentations
    Then I can see that the api request was successful
    And I can see that the api response contains a presentation with name "testpres"

  Scenario: I can get an existing presentation
    Given I use the api key "testapikey"
    And The user "apitester@shinage.test" has a presentation of type "slideshow" and title "testpres"
    When I get the presentation "testpres"
    Then I can see that the api request was successful

  Scenario: I can get an existing presentation
    Given I use the api key "testapikey"
    And The user "apitester@shinage.test" has a presentation of type "slideshow" and title "testpres"
    When I get the presentation "testpres"
    Then I can see that the api request was successful

  Scenario: I cannot get a non-existing presentation
    Given I use the api key "testapikey"
    When I get the presentation "testpres"
    Then I should get a Not Found response

  Scenario: I can update an existing presentation
    Given I use the api key "testapikey"
    And The user "apitester@shinage.test" has a presentation of type "slideshow" and title "testpres"
    When I update the presentation "testpres" with settings:
      """
      {}
      """
    Then I can see that the api request was successful

  Scenario: I can update an existing presentation and see that it changed
    Given I use the api key "testapikey"
    And The user "apitester@shinage.test" has a presentation of type "slideshow" and title "testpres"
    When I update the presentation "testpres" with settings:
      """
      {
          "slides": [
              {
                  "duration": 5000,
                  "title": "SlideWithTestString",
                  "transition": "",
                  "type": "Image",
                  "src": "test.jpg"
              },
              {
                  "duration": 5000,
                  "title": "Slide",
                  "transition": "",
                  "type": "Image",
                  "src": "test2.jpg"
              }
          ]
      }
      """
    And I get the presentation "testpres"
    Then I can see that the api request was successful
    And I can see that the presentation contains a slide with title "SlideWithTestString"

  Scenario: I can delete an existing presentation and see that it does not exist anymore
    Given I use the api key "testapikey"
    And The user "apitester@shinage.test" has a presentation of type "slideshow" and title "testpres"
    When I delete the presentation "testpres"
    And I get the presentation "testpres"
    Then I should get a Not Found response
