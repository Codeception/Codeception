Feature: Inline argument example

  Scenario: Inline argument
    Given I have inline argument "test"
    When I print argument
    Then I see output "test"

  Scenario: Running step with inline argument
    Given I print "test"
