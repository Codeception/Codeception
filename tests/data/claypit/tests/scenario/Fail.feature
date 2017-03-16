Feature: Run gherkin
  In order to test a feature
  As a user
  I need to be able to see output

  Scenario: Fail because file games.zip not exist
    Then I see file "games.zip"

  Scenario: Fail because file tools.zip not exist
    Then I see file "tools.zip"