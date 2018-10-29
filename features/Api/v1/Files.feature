@api @files
Feature: In order to manage files remotely
  As a REST client
  I can execute the management functions

  Background:
    Given There is a user with username "apitester@shinage.test" and password "noneededpassword"
    And The user "apitester@shinage.test" has the roles "ROLE_SUPER_ADMIN"
    And The user "apitester@shinage.test" has an api key "testapikey" with scope "FILES"
    And In the pool the user "apitester@shinage.test" has a file "/dir1/file1" with content "testcontent1"
    And In the pool the user "apitester@shinage.test" has a file "/file2" with content "testcontent2"
    And There is a user with username "othertester@shinage.test" and password "noneededpassword"

  Scenario: I can get a list of files and directories of one directory
    Given I use the api key "testapikey"
    When I get the file pool contents of "/user:apitester@shinage.test"
    Then I can see that the api request was successfull
    And I can see that the api response contains directory "dir1/"
    And I can see that the api response contains file "file2"

  Scenario: I cannot get a list of files and directories of another user's directory
    Given I use the api key "testapikey"
    When I get the file pool contents of "/user:othertester@shinage.test"
    Then I get an Access Denied response

  Scenario: I cannot get a list of files and directories in root directory
    Given I use the api key "testapikey"
    When I get the file pool contents of "/"
    Then I get an Not Found response

  Scenario: I cannot get a list of files and directories from a non existing user
    Given I use the api key "testapikey"
    When I get the file pool contents of "/user:you-dont-get-me@nowhere.test"
    Then I get an Access Denied response

