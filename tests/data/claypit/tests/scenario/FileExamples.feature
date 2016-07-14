Feature: Suite configs
  In order to run tests
  As a user
  I need to have suite config files

  Scenario Outline: Check file exists
    Given I have terminal opened
    When I am inside "<directory>"
    Then there is a file "<filename>"

    Examples:
      | directory | filename            |
      | .         | unit.suite.yml      |
      | .         | scenario.suite.yml  |
      | .         | dummy.suite.yml     |