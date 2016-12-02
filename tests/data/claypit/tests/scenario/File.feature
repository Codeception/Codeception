Feature: Run gherkin
  In order to test a feature
  As a user
  I need to be able to see output

  Scenario: Check file exists
    Given I have terminal opened
    When I am in current directory
    Then there is a file "scenario.suite.yml"
    And there are keywords in "scenario.suite.yml"
      | class_name | ScenarioGuy |
      | enabled   | Filesystem   |


  Scenario: Describe a new feature
    Given I have only idea of what's going on here


  Scenario: Check file once more
    Given I am in current directory
    When there is a file "scenario.suite.yml"
    Then I see file "scenario.suite.yml"