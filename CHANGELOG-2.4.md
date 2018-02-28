#### 2.4.0

* **PHPUnit 7.x compatibility**
* **Dropped PHP 5.4 and PHP 5.5** support (PHP 5.5 still may work)
* Internal API refactored:
  * Modern PHP class names used internally
  * Moved PHPUnit related classes to [codeception/phpunit-wrapper](https://github.com/Codeception/phpunit-wrapper) package.
  * Removed `shims` for underscore PHPUnit classes > namespaced PHP classes
* Cest hooks behavior changed (by @fffilimonov):
  * `_failed` called when test fails
  * `_passed` called when tests is successful
  * `_after` is called for failing and successful tests   

**Upgrade Notice**: If you face issues with underscore PHPUnit class names (like PHPUnit_Framework_Assert) you have two options:

* Lock version for PHPUnit in composer.json: "phpunit/phpunit":"^5.0.0"
* Update your codebase and replace underscore PHPUnit class names to namespaced (PHPUnit 6+ API) 