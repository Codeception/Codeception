#### 5.0.0

* Compatible with PHPUnit 9 only (probably will change to 10-only before release)
* Compatible with Symfony 4.4 - 6.0
* Requires PHP 7.4 or higher
* Merged codeception/phpunit-wrapper 9.0 branch to codeception/codeception code base.
* Removed JSON and TAP loggers
* Removed code coverage blacklist functionality
* Removed deprecated class aliases
  - Codeception\TestCase\Test
  - Codeception\Platform\Group
  - Codeception\Platform\Group
  - Codeception\TestCase
* Introduced strict types in the code base.
* Added optional value to fail-fast option (#6275) by #Verest
