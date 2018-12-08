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
    Then I can see that the api request was successful
    And I can see that the api response contains directory "dir1/"
    And I can see that the api response contains file "file2"

  Scenario: I cannot get a list of files and directories of another user's directory
    Given I use the api key "testapikey"
    When I get the file pool contents of "/user:othertester@shinage.test"
    Then I should get an Access Denied response

  Scenario: I cannot get a list of files and directories in root directory
    Given I use the api key "testapikey"
    When I get the file pool contents of "/"
    Then I should get a Not Found response

  Scenario: I cannot get a list of files and directories from a non existing user
    Given I use the api key "testapikey"
    When I get the file pool contents of "/user:you-dont-get-me@nowhere.test"
    Then I should get an Access Denied response

  Scenario: I can upload a file
    Given I use the api key "testapikey"
    When I put a file to "/user:apitester@shinage.test/folder/file.txt" with contents:
      """
      Content of the file.
      """
    Then I should get a No Content response

  Scenario: I can upload and see a file
    Given I use the api key "testapikey"
    When I put a file to "/user:apitester@shinage.test/folder/file.txt" with contents:
      """
      Content of the file.
      """
    And I get the file pool contents of "/user:apitester@shinage.test/folder/"
    Then I can see that the api response contains file "file.txt"

  Scenario: I can upload a file and retrieve its contents later
    Given I use the api key "testapikey"
    When I put a file to "/user:apitester@shinage.test/folder/file.txt" with contents:
      """
      Content of the file.
      """
    And I get the file pool contents of "/user:apitester@shinage.test/folder/file.txt"
    Then I can see that the returned file contains
      """
      Content of the file.
      """

  Scenario: I can upload a file and overwrite it and retrieve its contents later
    Given I use the api key "testapikey"
    When I put a file to "/user:apitester@shinage.test/folder/file.txt" with contents:
      """
      Content of the file.
      """
    And I put a file to "/user:apitester@shinage.test/folder/file.txt" with contents:
      """
      Content of the new file.
      """
    And I get the file pool contents of "/user:apitester@shinage.test/folder/file.txt"
    Then I can see that the returned file contains
      """
      Content of the new file.
      """

  Scenario: I cannot upload a file named as a directory
    Given I use the api key "testapikey"
    When I put a file to "/user:apitester@shinage.test/folder/" with contents:
      """
      Content of the file.
      """
    Then I should get a Bad Request response

  Scenario: I cannot upload a file named like an existing directory
    Given I use the api key "testapikey"
    When I put a file to "/user:apitester@shinage.test/folder/file.txt" with contents:
      """
      Content of the file.
      """
    And I put a file to "/user:apitester@shinage.test/folder" with contents:
      """
      Content of the file.
      """
    Then I should get a Bad Request response

  Scenario: I can delete a file
    Given I use the api key "testapikey"
    When I delete at "/user:apitester@shinage.test/dir1/file1"
    And I get the file pool contents of "/user:apitester@shinage.test/dir1/"
    Then I can see that the api response does not contain file "file.txt"

  Scenario: I cannot delete a non existing file
    Given I use the api key "testapikey"
    When I delete at "/user:apitester@shinage.test/dir5/file8"
    Then I should get a Not Found response

  Scenario: I can delete an empty directory
    Given I use the api key "testapikey"
    When I delete at "/user:apitester@shinage.test/dir1/file1"
    Then I should get a No Content response
    When I delete at "/user:apitester@shinage.test/dir1"
    Then I should get a No Content response

  Scenario: I cannot delete a non empty directory
    Given I use the api key "testapikey"
    When I delete at "/user:apitester@shinage.test/dir1"
    Then I should get a Bad Request response

  Scenario: I cannot delete a file above my root
    Given I use the api key "testapikey"
    When I delete at "/user:apitester@shinage.test/dir5/..%2F..%2F..%2F..%2Fetc/passwd"
    Then I should get an Access Denied response

  Scenario: I can get a 304 for a cached files
    Given I use the api key "testapikey"
    And In the pool the user "apitester@shinage.test" has a file "/dir1/cached1" with content "testcontent1"
    And this pool file has the last modified timestamp "2018-01-01 00:00:00"
    When I get the file pool contents of "/user:apitester@shinage.test/dir1/cached1" if modfied since "2018-06-01 00:00:00"
    Then I should get a Not Modified response

  Scenario: I can get a 200 for a cached but modified files
    Given I use the api key "testapikey"
    And In the pool the user "apitester@shinage.test" has a file "/dir1/cached1" with content "testcontent1"
    And this pool file has the last modified timestamp "2018-06-01 00:00:00"
    When I get the file pool contents of "/user:apitester@shinage.test/dir1/cached1" if modfied since "2018-01-01 00:00:00"
    Then I can see that the api request was successful
