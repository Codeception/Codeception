@important
Feature: Refund item
  In order to get satisfaction
  As a customer
  I need to be able to get refunds

  Scenario: ジェフは不完全な電子レンジを返します
    Given Jeff has bought a microwave for "$100"
    When he returns the microwave
    Then Jeff should be refunded $100
