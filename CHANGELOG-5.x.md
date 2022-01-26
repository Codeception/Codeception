#### 5.0.0

* Decoupled test execution and reporting from PHPUnit
* Custom reporters implementing TestListener are no longer supported and must be converted to Exceptions.
* Added path_coverage setting to enable path and branch coverage #6158 by @s0lus
* Added optional value to fail-fast option (#6275) by #Verest
* Removed JSON and TAP loggers
* Removed code coverage blacklist functionality
* Removed deprecated class aliases
  - Codeception\TestCase\Test
  - Codeception\Platform\Group
  - Codeception\Platform\Group
  - Codeception\TestCase
* Removed settings disallow_test_output, log_incomplete_skipped and report_useless_tests.
* Introduced strict types in the code base.
* Compatible with PHPUnit 10 only
* Compatible with Symfony 4.4 - 6.0
* Requires PHP 8.0 or higher
