Feature: PyString argument example

  Scenario: PyString argument
    Given I have PyString argument
      """
      First line
      Second line
      """
    When I print argument
    Then I see output
      """
      First line
      Second line
      """

  Scenario: Running step with PyString argument
    Given I print
      """
      First line
      Second line
      """
