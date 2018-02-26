#### 2.4.0

* PHPUnit 7.x compatibility
* Internal API refactored:
  * Modern PHP class names used internally
  * Moved PHPUnit related classes to [codeception/phpunit-wrapper](https://github.com/Codeception/phpunit-wrapper) package.
* Dropped PHP 5.4 and PHP 5.5 support (PHP 5.5 still may work)
* Cest hooks behavior changed (by @fffilimonov):
  * `_failed` called when test fails
  * `_passed` called when tests is successful
  * `_after` is called for failing and successful tests
   