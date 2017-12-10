@presentationEditor
Feature: I can create a slideshow presentation
  Background:
    Given There is a user with username "testUser" and password "testPassword"
    And The user "testUser" has the roles "ROLE_SUPER_ADMIN"
    And I am logged in as user "testUser"


  Scenario: I can create a slideshow presentation and go to editor
    Given I am on page "/manage/presentations/create"
    When I fill the field "form_presentation[title]" with "TestPresentation1234"
    And I select "slideshow" from "form_presentation[type]"
    And I click on the button "form_presentation[save]"
    Then I should be on page "/manage/presentations"
    And I see a headline "TestPresentation1234"
    When I follow the edit link for "TestPresentation1234"
    And I should see the slideshow editor
