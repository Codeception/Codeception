Feature: Run gherkin
  In order to test a feature
  As a user
  I need to be able to see output

  Scenario: Check file exists
    Given I have terminal opened
    When I am in current directory
    Then there is a file "scenario.suite.yml"
    And there are values in file"
      | email              | password | enabled | groups              |
      | beth@example.com   | foo1     | yes     | Wholesale Customers |
      | martha@example.com | bar1     | yes     | Retail Customers    |