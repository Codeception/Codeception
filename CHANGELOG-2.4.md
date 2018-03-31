#### 2.4.1

* Fixed `PHP Fatal error:  Uncaught Error: Call to undefined method Codeception\Test\Descriptor::getTestDataSetIndex()` when filtering tests
* Better support of PHPUnit warning status by @edno:
  * support PHPUnit addWarning()
  * display 'W' instead of success for warning test cases
* Fixed Running test with invalid dataprovider by @okneloper. Fixed  #4888 by @edno
* [Yii2] **Request flow and database transactions refactored** (by @sammousa):
  * **Breaking** Application is no longer available in helpers via `$this->getModule('Yii2'')->app`, now you must use `\Yii::$app` everywhere
  * Multiple databases are now supported
  * More reliable application state before and during test execution
  * Fixtures method is now configurable
  * Subset of misconfigurations are now detected and informative messages created
* Fixed using `$settings['path']` in `Codeception\Configuration::suiteSettings()` on Windows by @olegpro 
* [Laravel5] Added Laravel 5.4+ (5.1+ backward compatible) support for `callArtisan` method in Laravel5 module. See #4860 by @mohamed-aiman 
* Fixed #4854: unnecessary escaping in operation arguments logging by @nicholascus
* Fixed humanizing steps for utf8 strings by @nicholascus. See #4850
* Fixed parsing relative urls in `parse_url`. See #4853 by @quantum-x

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

