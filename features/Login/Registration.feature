@registration
Feature: As an interested user
  In order to use the system
  I want to be able to register

  Background:
    Given There is an organization "test-orga1@orga1.test"
    And There is an organization "test-orga2@orga2.test"

  Scenario: I can register with a registration code
    Given There is a registration code "code123456789"
    When I am on page "register"
    And I fill the field "form[registrationCode]" with "code123456789"
    And I fill the field "form[email]" with "testusername1@test.test"
    And I fill the field "form[password][first]" with "testpassword"
    And I fill the field "form[password][second]" with "testpassword"
    And I click on the button "form[save]"
    Then I should see that the registration was successful

  Scenario: I cannot register with a wrong registration code
    Given There is a registration code "code1"
    When I am on page "register"
    And I fill the field "form[registrationCode]" with "wrong_code"
    And I fill the field "form[email]" with "testusername1@test.test"
    And I fill the field "form[password][first]" with "testpassword"
    And I fill the field "form[password][second]" with "testpassword"
    And I click on the button "form[save]"
    Then I should see that the registration was not successful

  Scenario: I cannot register with an expired registration code
    Given There is a registration code "code1" that is valid until "2001-01-01 11:10:10"
    When I am on page "register"
    And I fill the field "form[registrationCode]" with "code1"
    And I fill the field "form[email]" with "testusername1@test.test"
    And I fill the field "form[password][first]" with "testpassword"
    And I fill the field "form[password][second]" with "testpassword"
    And I click on the button "form[save]"
    Then I should see that the registration was not successful

  Scenario: I cannot register multiple time with the same registration code
    Given There is a registration code "code123456789"
    When I am on page "register"
    And I fill the field "form[registrationCode]" with "code123456789"
    And I fill the field "form[email]" with "testusername1@test.test"
    And I fill the field "form[password][first]" with "testpassword"
    And I fill the field "form[password][second]" with "testpassword"
    And I click on the button "form[save]"
    And I am on page "register"
    And I fill the field "form[registrationCode]" with "code123456789"
    And I fill the field "form[email]" with "testusername2@test.test"
    And I fill the field "form[password][first]" with "testpassword"
    And I fill the field "form[password][second]" with "testpassword"
    And I click on the button "form[save]"
    Then I should see that the registration was not successful

  Scenario: I can register with a registration code and belong to its organization
    Given There is a registration code "orgacode1" belonging to organization "test-orga1@orga1.test"
    When I am on page "register"
    And I fill the field "form[registrationCode]" with "orgacode1"
    And I fill the field "form[email]" with "testusername3@test.test"
    And I fill the field "form[password][first]" with "testpassword"
    And I fill the field "form[password][second]" with "testpassword"
    And I click on the button "form[save]"
    Then I should see that the registration was successful
    And The user "testusername3@test.test" should be in organization "test-orga1@orga1.test"

  Scenario: I can register and be assigned automatically to organization by mail host
    Given There is a registration code "code4"
    And The organization "test-orga1@orga1.test" has automatically assignment by mail host enabled
    When I am on page "register"
    And I fill the field "form[registrationCode]" with "code4"
    And I fill the field "form[email]" with "testusername4@orga1.test"
    And I fill the field "form[password][first]" with "testpassword"
    And I fill the field "form[password][second]" with "testpassword"
    And I click on the button "form[save]"
    Then I should see that the registration was successful
    And The user "testusername4@orga1.test" should be in organization "test-orga1@orga1.test"
    And The user "testusername4@orga1.test" should not be in organization "test-orga2@orga2.test"
