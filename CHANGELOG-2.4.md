#### 2.4.5

* Fixed PHPUnit 7.2 compatibility.
* Introduced **RunBefore** extension to execute scripts before running tests. See #5049 by @aashmelev.  
* [Db] Added two options for MySQL by @bangertz
    * `ssl_cipher` - list of one or more permissible ciphers to use for SSL encryption
    * `ssl_verify_server_cert` - disables certificate CN verification
* [Db] Always disconnect before connect when `reconnect` is set. By @ashnazg
* [Db] More explicit PDO closing upon destruction and close opened transactions by @ashnazg.
* [Recorder Extension] Improved error logging by @OneEyedSpaceFish. See #5101
* [Lumen] Fixed file uploads via REST module. By @retnek.
* Fixed: function getMetadata() may not exist, results in fatal error. See #4913 by @marcovtwout 

#### 2.4.4

* Recently added `extends` property in the `codeception.yml` and `*.suite.yml` files now support absolute paths; by @silverfire
* Fixed absolute paths handling on Windows in ParamLoader; by @silverfire
* [Yii2] Refactored database connection handling by @SamMousa. Database connections should now always be closed after tests no matter how you have opened them or who is holding references to them. See  #5045
* [Symfony] Email handling improved by @mbohal. Fixes #5058.  
    * Added optional argument `$expectedCount` to `seeEmailIsSent`
    * Added `dontSeeEmailIsSent`
* [Recorder Extension] Added `ignore_steps` option to disable recording of specific steps. By @sspat.
* [WebDriver] Fixed "No Session Timeout" fatal error by @davertmik.
* [WebDriver] Added ability to locate clickable element by its title. See #5065 by @gimler  
* [Db] Add `waitlock` config option for the database session to wait for lock in Oracle. By @ashnazg. See #5069
* [REST] Fixed `seeXmlResponseEquals` by @Voziv

#### 2.4.3

* [Create your own test formats](https://codeception.com/docs/07-AdvancedUsage#Formats) (e.g., Cept, Cest, ...); by @mlambley
* [Symfony] Fixed a bug in order to use multiple Kernels; by @alefcastelo
* [Asserts] Added new methods `assertNotTrue` and `assertNotFalse` methods; by @johannesschobel
* [REST][PhpBrowser][Frameworks] Added new methods to check for `Http Status Ranges` with nice "wrappers" (e.g., `seeHttpStatusCodeIsSuccessful()` checks the code between 200 and 299); by @johannesschobel
* Improved the docs; by community

#### 2.4.2

* Added support for `extends` in the `codeception.yml` and `*.suite.yml` files; by @johannesschobel.
 Allows to inherit current config from a provided file. See example for `functional.suite.yml`:

```yml
actor: FunctionalTester
extends: shared.functional.suite.yml
modules:
    enabled:
        - \App\Modules\X\Tests\Helper\Functional
```

* [Yii2] Restore null check for client in Yii2 by @wkritzinger. See #4940
* [Yii2] Resetting Yii application in `_after`. By @SamMousa. See #4928
* [Yii2] **Breaking** `$settings['configFile']` now supports absolute paths. In you have defined relative path to config in absolute manner
* [WebDriverIO] Added `deleteSessionSnapshot` by @vi4o
* [Symfony] Added support for custom kernel names with `kernel_class` config option. By @omnilight.
* [Asserts] Better exception message for `expectException` by @Slamdunk
* [REST] Decode all non-arrays to array. See #4946 by @Amunak, fixes #4944. 
* [ZF2] Fixed compatibility with ZF2 ServiceManager by @omnilight.
* [Laravel5] Fixed memory leak when using Laravel factories inside Codeception. See #4971 by @AdrianSkierniewski
* [Db] Added support for `null` values in MSSQL driver by @philek
* Handle absolute paths in ParamsLoader by @SilverFire
* Fix error on single file test. See #4986 by @mikbox74 
* Upgraded to Codeception/Stub 2.0 by @Naktibalda, fixed compatibility.
 

#### 2.4.1

* Fixed "Uncaught Error: Call to undefined method Codeception\Test\Descriptor::getTestDataSetIndex()" error when filtering tests.
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
(starting with `/`), you must change it. @silverfire
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

