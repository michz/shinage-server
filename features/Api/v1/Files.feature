@api @files
Feature: In order to manage files remotely
  As a REST client
  I can execute the management functions

  Background:
    Given There is a user with username "apitester@shinage.test" and password "noneededpassword"
    And The user "apitester@shinage.test" has the roles "ROLE_SUPER_ADMIN"
    And The user "apitester@shinage.test" has an api key "testapikey" with scope "FILES"
    And In the pool there is a file "/dir1/file1" with content "testcontent1"
    And In the pool there is a file "/file2" with content "testcontent2"

  Scenario: I can get a list of files and directories of one directory
    Given I use the api key "testapikey"
    When I get the file pool contents of "/"
    Then I can see that the api response contains directory "dir1"
    And I can see that the api response contains file "file2"

