#### 2.5.6

* [WebDriver] Fixed `loadSessionSnapshot` with php-webdriver 1.1.3 by @Naktibalda.
* [WebDriver] Avoid removing required fields in cookies. #5470 by @JorisVanEijden
* [PhpBrowser][Frameworks] Support for `formaction` attribute in `button` to submit forms. By @Dezzpil
* [FTP] Updated to phpseclib v2 constants
* Fixes: Gherkin Scenarios not loading when specified in group file by @mozillalives. See #5457

#### 2.5.5

* [Laravel] Fix missing declaration shouldReport in Laravel 5.8 by @edno
* [Lumen] add support for Laravel\Lumen\Application::boot by @lendormi
* [WebDriver] Fixed SetCookie for chromedriver 2.46+ by @JorisVanEijden
* [ZendExpressive] Fixed recreateApplicationBetweenRequests option, by @artmnv
* [Gherkin] Add possibility to dynamically load contexts (#5409) by @hansdubois
* [Build command] Fixed message printing number of methods in actor class
* Documented usage of IS NULL in Doctrine module by @ThomasLandauer

#### 2.5.4

* Restored compatibility with vlucas/phpdotenv v2
* [Doctrine] Fixed cleanup issue #5326

#### 2.5.3

* [Db] cleanup database if populator is used
* [FTP] Compatibility with phpseclib v2 by @kardagan
* [JsonType] Fixed issue #5230 Dropped filters after a string:regex by @ellisgl
* [Symfony] Fixed persistent service functionality for Symfony 3 by @Naktibalda
* [ZendExpressive] Set Cookie header in request by @Naktibalda
* Updated vlucas/phpdotenv package to ^3.0 version by @KartaviK
* Documentation improvements by @chrisaligent @richleland @SanzhiyevMergen @sdlins

#### 2.5.2

* [ZendExppressive] Support for Zend Expressive v3 by @Naktibalda
* [ZendExppressive] Added options to reload application between tests and between requests by @Naktibalda
* [Symfony] Fix "already initialized service", "reboot kernel issue" #5262 by @gdmfx
* {Yii2] Prevent NPE #5259 by @SilverFire
* [Db] isPopulated method was hidden by renaming to _isPopulated by @Naktibalda
* [Db] don't clear database for empty dump by @Slamdunk
* [AMQP] added methods `seeQueueIsEmpty`, `dontSeeQueueIsEmpty`, `seeNumberOfMessagesInQueue`, `scheduleQueueCleanup` method by @kardagan
* [REST][PhpBrowser][Frameworks] Save page source as .fail.json or .fail.xml depending on content type, by @freiondrej
* [Doctrine2] Cleanup property works after on reconfigure #5250 by @joelmedeiros
* [JsonType] Allow to use : in regex filter (#5273) by @ellisgl
* [WebDriver] Print curl error to debug output if WebDriver failed to connect #5315 by @Naktibalda
* [Logger] Ignores empty context and extra fields, by @siad007
* [Recorder] Improved steps ignoring in Recorder extension with meta steps and annotations support #5210 by @sspat.
* `@dataProvider` works with yield/generators #5271 by @burned42
* Fixed issue ArrayContainsComparator do not Intersect correctly Empty expected nested array #5303 by @malinink
* Fixed issue of steps with mocked objects #5163 by @dh9325
* Added Environment Name To Descriptor Unique Signatures #5294 by @Tenzian
* Run command: Added `--phpunit-xml` option, which produces xml report having the same structure as PhpUnit's #5004 by @Naktibalda
* Bootstrap command: Changed namespace shortcut to `-s` #5275 by @Naktibalda
* Improved the docs; by @h311ion, @gimler, @picass0, @josephzidell


#### 2.5.1

* Recorder extension improvements by @OneEyedSpaceFish. See #5177:
  * HTML layout improvements
  * Restructured tests to show nested output
  * file operation exceptions / log them without throwing exceptions
  * fix edge cases with file operations (too long wantTo, etc.)
  * the ability to automatically purge old reports (from previous runs)
  * display errors in the recorded page rather than saving it as error.png
  * the ability not to display any Unicode characters if ANSI only output is requested
  * the ability not to display any colors in output if no-colors is requested
  * the ability to change colors in the generated list based on configuration
* [Db] Made `_loadDump` unconditional like it was in 2.4. Fixed #5195 by @Naktibalda
* [Db] Allows to specify more than one dump file. See #5220 by @Fenikkusu
* [WebDriver] Added `waitForElementClickable` by @FatBoyXPC
* Code coverage: added `work_dir` config option to map remote paths to local. See #5225 by @Fenikkusu
* [Lumen] Added Lumen 5.5+ support for getRoutes method by @lendormi
* [Yii2] Restored `getApplication()` API by @Slamdunk
* [Yii2] Added deprecation doc to `getApplication()` by @Slamdunks
* [Doctrine2] Reloading module on reconfigure to persist new configs. See #5241 by @joelmedeiros
* [Doctrine2] Rollback all nested transactions created within test by @Dukecz
* [DataFactory] Reloading module on reconfigure to persist new configs. See #5241 by @joelmedeiros
* [Phalcon] Allows null content in response. By @Fenikkusu
* [Phalcon] Added `session` config option to override session class. By @Fenikkusu
* [Asserts] Added `expectThrowable()` method by @burned42
* Use `*.yaml` for params loading

#### 2.5.0

* [**Snapshot testing**](https://codeception.com/docs/09-Data#Testing-Dynamic-Data-with-Snapshots) introduced. Test dynamic data sets by comparing current values with previously saved ones.
* [Db] **Multi database support**. See #4857 by @eXorus
  * `amConnectedToDatabase` method added.
  * `performInDatabase` method added.
* Rerun tests in **[shuffle mode](https://codeception.com/docs/07-AdvancedUsage#Shuffle)** in the same order by setting seed value. By @SamMousa
* [PhpBrowser][Frameworks] **Breaking Change** `seeLink` now matches the end of a URL, instead of partial matching. By @Slamdunk
  * Previous: `$I->seeLink('Delete','/post/1');` matches `<a href="/post/199">Delete</a>`
  * Now: `$I->seeLink('Delete','/post/1');` does NOT match `<a href="/post/199">Delete</a>`
* [WebDriver] Keep coverage cookies in `loadSessionSnapshot`. Fix by @rajras
* [Yii2] Prevent null pointer exception by @SilverFire. See #5136
* [Yii2] Fixed issue with empty response stream by @SamMousa.
* [Yii2] Fixed `Too many connections` issue #4926. By @roslov
* [Yii2] Fixed #4769: `amLoggedInAs()` throws TypeError. By @SamMousa
* [Recorder Extension] Fixing recorder extension issues caused by phpunit 7.2.7 update by @OneEyedSpaceFish
* [Logger Extension] Added `codecept_log` function to write to logs from any place. Fixes #3551 by @siad007
* [WebDriver] Report correct strict locator in error message. When `see()` and `dontSee()` are used with array selector. Fix by @Naktibalda.
* [Phalcon] Use bind for find record. See #5158 by @Joilson
* [Phalcon] Add support for nullable fields in `findRecord()` by @arjanwestdorp
* Added `memory_limit` to `dry-run` command by @siad007. Fixes #5090
* Added ext-curl to the composer require section by @siad007
* Make `coverage: show_only_summary` configurable. See #5142 by @Quexer69
* Ensure php extension `mbstring` is available by @siad007. Fixes #4575

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

#### 2.3.9

* Added `Codeception\Step\Argument\PasswordArgument` to pass sensitive data into tests:

```php
<?php
use \Codeception\Step\Argument\PasswordArgument;

$I->amOnPage('/form/password_argument');
$I->fillField('password', new PasswordArgument('thisissecret'));
```

* [WebDriver] added `clearField` method to clean up input fields by @eknej
* [DataFactory] added `make` method to create instances without saving them to database. But @ibpavlov
* [REST] Fixed passing a file to `sendPOST()` without name, size or type parameter. BY @zebraf1
* [REST] Add missing / to relative url from config by @bscheshirwork
* Fixed HTML Report marks tests as succeeded by @mpgo13
* `clean` command works recursively with included setups. By @davidnewcomb

#### 2.3.8

* `Codeception\Util\Stub` moved to standalone package [Codeception\Stub](https://github.com/Codeception/Stub):
  * Use `Codeception\Stub` instead of `Codeception\Util\Stub`
  * Mocking methods `::once`, `::never`, etc moved to `Codeception\Stub\Expected` class
  * Calling mocking methods from `Codeception\Util\Stub` provides deprecation warning.
  * Non-static API is [recommended to use for mocking](https://codeception.com/docs/05-UnitTests)
* [WebDriver] Added `executeAsyncJS` action to run asynchronous scripts.
* [WebDriver] Added second parameter to `executeJS` to pass additional arguments into JavaScript function.
* [Yii2] `setCookie` signs cookies when signing enabled. #4656 By @SamMousa
* [Yii2] Method `createAndSetCsrfCookie` added. #4656 By @SamMousa
* Compatibility with phpunit-mock-objects 5.* by @Naktibalda
* [DataFactory] Removed dependency to `league/factory-muffin-faker` by @Naktibalda and @Insolita
* Fixed auto-rebuilding Actor classes when dependencies are used. See #4694 by @stefankleff.
* [Symfony] allows to use Symfony Dotenv component to parse `.env` files. Fix by @ebuildy
* Added the ability to export the code coverage data in PHPUnit xml format by @tobiasstadler
  * `--coverage-phpunit` option added
  * Allows to use mutation testing with [Inflection](https://infection.github.io)
* [ZendExpressive] Added Doctrine2 integration by @artmnv
* [PhpBrowser][Frameworks] Added `_getResponseStatusCode` hidden method for using in helpers. By @FanchTheSystem
* [Yii2] Use Yii DI to instantiate record class. Fixes #4762. By @bscheshirwork
* Remote Code Coverage improvements #4768 by @bscheshirwork
  * Remove `:port` for cookie domain;
  * `->amOnPage('/');` executed when running code coverage with WebDriver
* Fixed running single test with `include` config parameter. Fixes #4733 by @ppetpadriew
* Fixed running single test when a custom suite path is configured (For instance, in single-suite setups).
* `generate:test` command won't include `tester` property if actor is not set for this config.
* [Facebook] Module is not maintained and is deprecated. If you are using it and you want to keep it, please contact us.

#### 2.3.7

* **Symfony 4 support** implemented by @VolCh.
  * Dependencies updated to support Symfony 4.x components.
  * [Symfony] Support for Symfony Flex directory and namespace structure
  * [Demo application](https://github.com/Codeception/symfony-demo) was updated to Symfony 4.0
* [Db] `seeInDatabse`, `dontSeeInDatabase`, `grabFromDatabase` and other methods to support SQL comparison operators: `<`, `>`, `>=`, `<=`, `!=`, `like`. Thanks @susgo and @Naktibalda.
* [Db] Fixed quoting around schema identifiers in MSSQL by @Naktibalda. See #4542.
* [Db] Added SSL options for connection. Thanks @kossi84
* [Db] Fix getting Database name from DSN in MSSQL by @yesdevnull.
* [PhpBrowser] Fixed setting `User-Agent` in config via `headers`. Fixed #4576 by @Naktibalda.
* [WebDriver] Implemented `dontSeeInPopup` by @kpascal.
* [WebDriver] Allow to click a button located by its `title` attribute. See #4586 by @gimler.
* [Silex] `app` property added to public API. Thanks @sky003
* [Yii2] Pass DB to Yii application as early as possible to reuse old connection. By @SilverFire. See #4601
* [Yii2] Resetting global event handlers after a test. See #4621 by @SamMousa
* [Yii2] Recreate request object to reset headers and cookies before each request. Fixes #4587 by @erickskrauch
* [MongoDb] Allowing `.tgz` files to be accepted for database dumps. #4611 by @Lukazar
* [PhpBrowser][Frameworks] Fixed usage of `see` when source code contains `<=` JS operator. By @tobias-kuendig Fixes #4509.
* [Queue] Added configuration parameter `endpoint` for AmazonSQS by @gitis.
* Fixed signature error in `DummyCodeCoverage::stop` See #4665 by @network-spy
* Throw exception if `exit(0)` was accidentally called. Fixes false-positive test reports. See #4604 by Fenikkusu.
* Fixed using `path: tests: .` in configuration. Fixes #4432 by @marcovtwout
* Fixed suite name containing slash in remote code coverage. #4612 by @bscheshirwork
* Improved generated actions file by removing redundant `use` section. #4614 by @bscheshirwork
* Don't skip last test if some test has missing dependency by @Naktibalda. Fixes #4598
* Improved PHP 7.2 compatibility by @FanchTheSystem. See #4557
* Implemented `Descriptor::getTestSignatureUnique` to create unique names for tests. See #4673 by @Tenzian. Fixes #4672
* Fixed `setExpectedException()` default value for PHPUnit 5.7.23 by @MilesChou. See #4566
* Fixed printing wrong failed step by @eXorus. See #4654
* Fixed undefined `argv` warnings, added check for `register_argc_argv`. Fixes #4595 by @Naktibalda
* Added `init` command to `codecept.phar` by @Naktibalda.

And many thanks to our awesome contributors! Thanks to @VolCh for upgrading to Symfony 4, thanks @Naktibalda for patches and reviews and
thanks to @carusogabriel for refactoring tests.

#### 2.3.6

* **Laravel 5.5 compatibility**. Laravel5 module documentation updated.
* [Doctrine2][DataFactory] Fixes using Doctrine2 with DataFactory module. See #4529. Fix by @samusenkoiv
* [REST] Fixed JsonType crash when key 0 is not an array. Fixes #4517 by @Naktibalda
* [PhpBrowser][Frameworks] `haveHttpHeader` enhanced to handle special characters. #4541 by @bnpatel1990
* [WebDriver] Delete all cookies before loading session snapshot. Fix by @eXorus. See #4487
* Added `suite_namespace` config option to suite config. Allows to set custom namespace for tests per suite. #4525 by @pohnean
* [Db] Module enhancements by @eXorus:
  * added `updateInDatabase` method
  * added hidden `_insertInDatabase` to insert record without cleanup
* [Yii2] Set transaction also in `backupConfig` when initializing yii2 module
* [Yii2] Unload fixtures after rolling back database transaction. By @devonliu02  (#4497)
* [Yii2] Use `andWhere` instead of `where` in Yii module's `findRecord()` by @SamMousa. See #4482
* [REST] Added `amNTLMAuthenticated` for NTLM authentication using PhpBrowser. By @Tenzian
* Inject exception file and line number frame into stack trace in case it is missing. By @rhl-jfm at #4491)
* `Extension\RunFailed`. Added `fail-group` parameter to customize name of a failed group. By @ maxgorovenko
* Added `\Codeception\Util\Fixtures::exists()` method by @eXorus
* Added line number to `TestParseException` exception message by @gaainf. See #4446
* Fixed `init` command: create the `_generated` folder before writing a `.gitignore` file there by @nstapelbroek. See #4449
* Better failure messages for `@dataProvider` by @sh41. See #4439
* Fixed aliasing issue with `Codeception/Verify` by @ddinchev

#### 2.3.5

* Fixed HTML report with unencoded HTML code by @mpgo13. See #3819 #4423
* Made `assertArraySubset` protected across all modules by @guidocella
* [WebDriver][PhpBrowser][Frameworks] Added support for associative arrays in `seeInFormFields` by @guidocella
* [PhpBrowser][Frameworks] Submit default values of checkboxes. See #4411 by @guidocella
* [PhpBrowser][Frameworks] Make `seeInField` check options' texts and trimmed texts. By @guidocella
* [PhpBrowser] Prevents `submitForm` to submit inputs in disabled fieldsets. Fixes #4426 by @moebrowne
* [PhpBrowser] Fixed `amOnUrl` with empty path component. If path component was empty, it used previous url. Fixes #4383 by @Naktibalda
* [Db] Improved postgres cleanup (recreate schema) by @samusenkoiv
* [Laravel5] Don't duplicate associative array fields on form submission. See #4414 by @guidocella
* [WebDriver] Fixed `webDriver->getCapabilities()` for `facebook/php-webdriver` < 1.3 (could happen on PHP 5.4, 5.5). Fixes #4435
* [WebDriver] Make `wait` accept fractional amount of seconds to wait for less than a second. By @gvlasov
* [Laravel5] Changing params loader to use `$_SERVER` global instead of `$_ENV`. See #4401 by @EricTendian
* [Mongo] Fixes `haveInCollection` using `__toString`. See #4442 by @samusenkoiv
* Dereferencing variables for Steps output. Fixes #4402 by @alambe
* [Symfony] Load persistent services before loading profiler. See #4437 by @samusenkoiv

#### 2.3.4

* Added `@prepare` annotation to make realtime configuration for tests in Cest and Test classes. [See documentation](https://codeception.com/docs/06-ModulesAndHelpers#Runtime-Configuration-of-a-Test).

Example: disabling Doctrine2 database transaction for a test

```php
<?php
/**@prepare disableTransactions */
function testDoctrine()
{
}

protected function disableTransactions(Doctrine2 $module)
{
   $module->_reconfigure(['cleanup' => false]);
}
```
* [WebDriver] **SmartWait**. Automatically waits for a few extra seconds for element to appear on a page before failing. Can reduce high usage of `wait*` methods. [See Documentation](https://codeception.com/docs/03-AcceptanceTests#SmartWait)
* Added [RunProcess extension](https://codeception.com/extensions#RunProcess). Use it to start/stop Selenium (or other process) automatically for a test suite.
* [WebDriver] Customization improvements:
  * added `start` option to disable autostart of a browser for tests. (can be useful for Cloud testing setups)
  * added `_capabilities` method for setting desired capabilities in runtime (can be combined with `@prepare` annotation)
  * `_initializeSession` and `_closeSession` can be used in Helpers to start and stop browser manually (combine with `start: false` config)
* Fixed running a single test from a global config when using included configs. See #4366 by @zebraf1 (improves PhpStorm integration)
* [Doctrine2][Laravel5][Yii2][Phalcon] Print debug information for started/stopped transactions in tests. See #4352
* [PhpBrowser][Frameworks] click with context respects base tag #4330 by @Naktibalda.
* [Yii2] Split `cleanup` configuration option (backward-compatible): (#4379 by @leandrogehlen)
  * `cleanup` - to cleanup loaded fixtures
  * `transaction` - wrap tes into transaction
* [Asserts] Added `assertStringStartsWith` and `assertArraySubset` by @guidocella
* [Db] Added `updateInDatabase` method by @eXorus. See #4385
* In helpers and modules to check `$module::$excludeActions` property for existence before accessing it. Fixes #4381 by @CactusCoder
* [Symfony] Fixed printing debug response when `Symfony::extractRawRoles()` failed on security collector (Symfony >= 3.3) #4309 by @Basster
* [Laravel5] Fixed bug with `disable_exception_handling` functionality. See #4370. By @janhenkgerritsen
* [Db] Added `grabColumnFromDatabase` to fetches values from the column in database. By @RebOOter

#### 2.3.3

* Fixed running with `--coverage`, `--xml`, `--html` options without parameters (Symfony Console 3.3 compatibility).
* Removed `files` section from `composer.json` (regression from 2.3.2) to avoid unintentionally loading shim files. Fixes [Yii migration issue](https://github.com/yiisoft/yii2/issues/14226).
* [WebDriver] `saveScreenshot` allows to save screenshots with no name passed in. See #4263 by @eXorus
* [REST][PhpBrowser] Fixed #4287, using empty values for headers by @tobiastom.
* Phar `self-update` downloads php5.4 build if php version <7.0. Fixes #4269

#### 2.3.2

* [Db] Fixed: Database has been cleaned up between tests even with `cleanup: false`.
* [Db] Made `dump` optional if `populator` is set. Fixes #4247
* Fixed `generate:suite` command to create a directory for the suite. Fixes #4251
* Fixed composer autoloading with PHPUnit 6 by @enumag. See #4262

#### 2.3.1

* Updated composer constraints to include PHPUnit 6.x

#### 2.3.0

* **PHPUnit 6.x** support #4142 by @MontealegreLuis. Class aliases are used, so PHPUnit 4.x and 5.x (for PHP <7) are still supported as well.
* Suite customization. [Announcement](/05-22-2017/codeception-2-3.html#configuration-improvements)
* Installation Templates. [Announcement](/05-22-2017/codeception-2-3.html#installation-templates)
* DotReporter introduced. Use it with
```
codecept run --ext DotReporter
```
* `--ext` parameter added to load extensions dynamically.
* Db Populator [Announcement](/05-22-2017/codeception-2-3.html#db-populator) by @brutuscat
* [Db] New configuration defaults, cleanups are disabled: `cleanup: false`, `populate: false`. Enable them to load dumps between tests.
* [Redis] New configuration defaults, cleanups are disabled: `cleanupBefore: 'never'` by @hchonan
* Command `generate:phpunit` removed.
* Bootstrap `_bootstrap.php` files are disabled by default.
* Configuration changes: `actor` replaced with `actor_suffix` in global config
* Configuration changes: `class_name` replaced with `actor` in suite config

#### 2.2.12

* Don't skip other tests after a failed test #4226 by @Naktibalda
* [REST] `seeResponseContainsJson` doesn't crash when json response is not an array by @Naktibalda
* [PhpBrowser] Fixed redirecting to schemaless url by @Naktibalda #4218
* [Doctrine2] Added `grabEntityFromRepository`, `grabEntitiesFromRepository` methods by @maximelebastard

#### 2.2.11

* [WebDriver] Added `_restart` method to restart browser with a new configuration.
* [WebDriver] Added `_findClickable` to public API so can be used from helpers. By @tiger-seo
* [WebDriver] `seeLink` compares relative links correctly #4182
* [Webdriver] fixed attachFile messages when the file does not exist by @Naktibalda
* Fixed setting paths in environments and using `--override` options. By @kusnir. See #4143
* [Yii1] Allow to set only host in `url` config. #4172 by @SG5.
* [Yii1] Allow to make requests end with slash. #4190 by @SG5
* [Yii2] Allows use `InitDbFixture` feature #4201
* [Yii2] Add missing YII2 lifecycle events. #4187
* Don't run test if exception was thrown in `_before` of a module #4197 by @Naktibalda
* [Mongo] Fixed parsing dbname. See #4186 by @retnek
* [Mongo] Improved legacy driver check by @retnek. See #4178
* [WebDriver][PhpBrowser][Frameworks] Added `grabPageSource` method by @Kolyunya
* [PhpBrowser][REST] Add DELETE method to supported form data request methods in Guzzle6 by
* [PhpBrowser][REST] Restore request headers in multi-session testing. Fixes #4157
* Recorder Extension: Replace non-alphanumeric characters with underscores by @tiger-seo. Fixes Recorder on Windows
* [REST] Documented different ways to upload files
* Fixed `$scenario->current('name')` #4154 by @Naktibalda
* [AMQP] Documented parameters of `declareQueue`, `declareExchange` by @Naktibalda
* [Doctrine2] Safe prefix aliases for `buildAssociationQuery` by @jfxninja. See #4195
* Fixed output of failed step by @Naktibalda #4135 https://phptest.club/t/seeelement-wierd-fail-message/1470
* [WebDriver] fixed `friend->leave` method. Clearing base element on closing session. Fixes #4098
* [Symfony] Make symfony bootstrap.php.cache optional for php version > 7 by @patrickjahns
* Gherkin: Command `gherkin:snippets` to generate stub function name for non-english features. By @kuntashov
* Gherkin: Steps with PyString and with inline string argument considered the same. Fixes #4121 by @kuntashov
* [Db] `Oci::cleanup()` should be able to drop objects with case sensitive name. By @pavelkovar
* [Db] loadDump reports sql statement which caused error, fixes regression from 2.2.10. See #4120. By @Naktibalda.
* [Asserts] Add delta parameter to `assertEquals()` `assertNotEquals()` methods by @spideyfusion
* [Yii2] Removed check and notification for environment other than `test` by @samdark
* [Yii2] Unload fixtures only if `cleanup` configuration equals true. #4207 by @Faryshta
* [ZF2] Removed `session_write_close()` from ZF2 module by @tasselchof. Fixes #4112
* Fixed textual representation of can't steps by @Naktibalda
* [Lumen] Added IoC methods from Laravel5 module: `haveBinding`, `haveSingleton`, `haveContextualBinding`, `haveInstance`, `haveApplicationHandler`, `clearApplicationHandlers`. By @kt81
* [Lumen] Clear facade cache only when facade exists. Same change as #3124 for refactored Lumen module by @kt81
* [ZendExpressive] Support Zend Expressive 2.0 by @Naktibalda
* [Doctrine2] `haveFakeRepository` updated to work with Doctrine >= 2.5.7 by @laszlo-karpati #4212
* Command `bootstrap` adds `support/_generated` to gitignore. By @Naktibalda

#### 2.2.10

* Prefer local composer installation if available. Solves issues with incompatibility between locally and globally installed or packaged in phar file Codeception dependencies. Fix by @Naktibalda See #3997
* Added console completion by @gdscei. See [documentation](https://codeception.com/docs/07-AdvancedUsage#Shell-autocompletion)
* [WebDriver] Fixed compatibility with `facebook/webdriver` 1.4.0 by @Naktibalda. See #4076 Fixes #4073
* Run a suite by its path #4079

```
codecept run tests/unit
```
Improves recent [PHPStorm integration](https://blog.jetbrains.com/phpstorm/2017/03/codeception-support-comes-to-phpstorm-2017-1/). Codeception tests can be started by running a suite directory.

* [WebDriver] Fixed using `performOn` with `ActionSequence`; supporting multiple actions of same kind. #4066 by @davertmik. Fixes #4044
* [Laravel5] Added `haveApplicationHandler` and `clearApplicationHandlers` methods. See #4068. By @janhenkgerritsen
* [Laravel5] Close all Laravel DB connections after test execution. Fixes #4031 by @rmblstrp
* [Laravel5] Update Laravel5 `database_migrations_path` to by null by default by @timbroder. Fixes #3990
* [DataFactory] Add `cleanup` option to skip auto cleanup. By @alexpts. See #3996
* Fixed printScenarioFail with multiple feature scenarios by @gimler. See #3868
* Fixed generating JUnit XML when Selenium server can’t be connected. Closes #3653 by @Naktibalda
* Fixes running local suites (under tests folder) and included suite mixed (via include path). See #4063
* [Db] Run the last statement in dump file even if it doesn't end with delimiter. #4071 by @Naktibalda. Fixes #4059
* [Memcache] Fixed calling flush on null by @Jurigag. See #4074
* [Yii2] Fixtures behavior compatibility with `yii2-codeception` by @leandrogehlen. See #4016
* `g:suite` allows generate suites with uppercase names. Fixes #4072
* Enabled incomplete/skipped/risky/warning settings for logger. See #3890. By @mario-naether

```yaml
settings:
    report_useless_tests: false
    disallow_test_output: false
    be_strict_about_changes_to_global_state: false
    log_incomplete_skipped: false
```
* [WebDriver] Fixed double coverage cookie check by @boboldehampsink. See #2923 #4020
* [WebDriver] Fixed `switchToIframe` regression from 2.2.9 by @lcobucci. PR #4000
* Speed improvement for group lookup by @pitpit. See #4025
* Added parse error to `TestParseException` in PHP7 by @Naktibalda. See #4007
* Auto injection for `Codeception\Test\Unit` format #4070. Allows to customize injection of support objects into a testcase:

```php
<?php
public function _inject(UnitTester $unit)
{
    $this->i = $unit;
}
```

#### 2.2.9

* [Laravel5] **Laravel 5.4 support** by @janhenkgerritsen
* [WebDriver] Added `performOn` to wait for element, and run actions inside it. See [complete reference](https://codeception.com/docs/modules/WebDriver#performOn). #3986
* [WebDriver] Improved error messages for `wait*` methods by @disc. See #3983
* [REST] Binary responses support by @spikyjt #3993 #3985
  * `seeBinaryResponseEquals` assert that binary response matches a hash
  * `seeBinaryResponseEquals` assert that binary response doesn't match a hash
  * hide binary response on debug
* [Laravel5] module fix error for applications that do not use a database. See #3954 by @janhenkgerritsen. Fixed #3942
* [Laravel5] database seeders to be executed inside a transaction. See #3954 by @janhenkgerritsen. Fixed #3948 by @janhenkgerritsen
* [Yii2] reverted #3834, closing transaction after each request. #3973 by @iRipVanWinkle. Fixes #3961
* Added crap4j report support. Use `--coverage-crap4j` option and `codeception/c3` 2.0.10
* [PhpBrowser][Frameworks] If form has no id, use action attribute as identifier by @Naktibalda. Fixes #3953
* Fixed test coloring output when a Feature title has some special chars in it like `/` or `-`
* [REST] Added missing @part `json` and `xml` to `deleteHeader` by @freezy-sk

#### 2.2.8

* [WebDriver] Added tab actions (not supported in PhantomJS):
  * `openNewTab` opens a new tab and switches to it
  * `closeTab` closes a tab and switches to previous
  * `switchToNextTab` switches to next tab
  * `switchToPreviousTab` switches to previous tab
* [WebDriver] Added actions to click element by coordinates. Via @gimler
  * `clickWithLeftButton` clicks element with offset
  * `clickWithRightButton` right clicks on element with offset
* [WebDriver] Added `js_error_logging` option to print JS logs in console and in HTML report by @ngraf. See #3821
* [WebDriver] Improvements to `seeInField` by @gimler. See #3905
  * support option text in seeInField not only value
  * fix bug match with and without whitespaces
  * fix bug seeInField not working after selectOption
* [Wedriver] `pageload_timeout` config option added. The amount of time to wait for a page load to complete before throwing an error. This patch allows to reduce issues from phantomjs random freezing. See #3874. Thanks to @oprudkyi
* [WebDriver] `checkOption` can check option by name #3852. By @gimler
* [WebDriver] Fixed clicking numerical links, like `<a href='/'>222</a>` (DOM Exception 12 errors). See #3865. By @gimler
* [PhpBrowser][Frameworks] Fixed #3824 when submitForm used wrong value for `select` by @JorisVanEijden
* [Laravel5] Added `seeNumRecords` and `grabNumRecords` methods. See #3816. By @dmoreno
* Improved `@depends` to work with `@dataprovider`. Fixes #3862. Thanks @edno
* Fixed relative paths for screenshots in HTML report. Fixes #3857
* Improved error description when injecting invalid classes by @timtkachenko
* Improved `--override` option to support deep configs. See #3820
* [Yii2] Clear unloaded fixtures after test. Closes #3794
* [PhpBrowser] Ensure sessions have independent cookies by @insightfuls. Fixes #3911
* Implemented load params from php files by @arrilot. See #3914
* [Yii2] Fixes #3916: Don't try to start transaction when working with non-transactional DBs by @samdark.
* [REST] Removed broken xdebug_remote functionality by @Naktibalda. Fixes #3883
* Added graceful termination by Ctrl-C in PHP 7.1 by @AdrianSkierniewski. See #3907
* [Db] Disconnect after initializing when using reconnect, fixes #3903. By @insightfuls
* [Phalcon] Fixed handling `$_SERVER` with Phalcon Connector by @sergeyklay
* Avoid notice when checking width of terminal on Windows by @ashnazg. See #3893
* [Filesystem] `dontSeeFileFound` searches in path by @Naktibalda. Fixes #3877
* [PhpBrowser][Frameworks] `grabValueFrom` to work after `fillField` by @wumouse. Fix #3866
* [Db] Oci driver to cleans up views #3881, and result set improvements #3840 by @ashnazg.
* [Yii2] Close transaction created by the controller-action on interruption. See #3834. By @alex20465
* [Yii2] Fixed using `part: init` with other modules like WebDriver. See #3876. By @margori
* [REST] Implemented `dontSeeResponseJsonMatchesXpath` method by @Naktibalda. Closes #3843
* [REST] Convert array having single element to XML correctly. Fixes #3827 by @Naktibalda
* Linter to check `exec` function to be enabled before using it. By @Naktibalda. See #3886
* Fixed #3922: division by zero in steps output on small terminal windows.
* Improved getting terminal width from ENV variable (bash). Fixes #3788 by @schmunk42

#### 2.2.7

* **Config validation** with `codecept config:validate` command. Use it:

```
codecept config:validate
codecept config:validate acceptance
```

This should help you next time you get messed with YAML formatting.

* Gherkin improvements:
  * multiple step definitions per method allowed (Fixes #3670).
  * regex validation for Gherkin steps; throws exception if invalid regex passed. Fixes #3676
  * currency chars supported in placeholders:

  $,€,£ and other signs can be used before or after a number inside Gherkin scenario. This char will be ignored inside a PHP variable, so you receive only number.

```gherkin
When I have 100$ => $num === 100
And I have $100 => $num === 100
```

* escaped strings can now be passed into placeholders. Fixes #3676.

* Codeception is tested with latest verision of HHVM
* Extensions loader refactored:
  * Extensions can be **enabled for suite** in suite config.
  * Extensions can be loaded per suite and per environment.
  * Extensions configs can be done inside `enabled` section (as it is for modules):

```yaml
extensions:
  enabled:
      Codeception\Extension\Recorder:
          delete_successful: false
```

* **Added dataprovider to Cest** format by @endo. See [updated documentation](https://codeception.com/docs/07-AdvancedUsage#Examples).
* Params loader refactored. Using `vlucas/phpdotenv` to parse .env files. Please install it if you don't have it yet.
* Improved `generate:suite` command to generate actor file for suite.
* HTML reporter: snapshot and screenshots paths made relative to make them accessible on CI. Fixes #3702
* [WebDriver] added `protocol` and `path` config options by @sven-carstens-udg. See #3717
* [PhpBrowser][Frameworks] Honour `<base href="">` meta tag by @Naktibalda. See #3764
* [Yii2] Removed mockAssetManager by @githubjeka
* [Yii2] Added procesing for native url formats of Yii2 #3725 by @githubjeka
* [Yii2] Fixed unintentional DB connection drop during exception logging, #3696 by @ivokund
* [Yii2] Fixed calling `_fixtures()` method of Cest class. See #3655, fixes #3612 by @primipilus
* [Db] Fixed `removeInserted` for Sqlite by @Naktibalda. Fixes #3680
* Allows to get groups from scenario by `$scenario->getGroups()`. By @frantzen. See #3675
* Fixed #3225: incorrect steps shown for multiple canXXX conditional assertion failures. By @Mitrichius
* [SOAP] Force string for debugSection output by @Noles. Fixes #3690
* Fixed #3562 group files with exact test not working with `@example` on Windows by @Naktibalda.
* [Laravel5] Added `vendor_dir` option. See #3775. By @AdrianSkierniewski
* [Laravel5] Fixed error where custom service container bindings were not available on the first request. See #3728. By @janhenkgerritsen
* [Lumen] Fixed error where a non-existing exception class was thrown. See #3729. By @janhenkgerritsen
* [Phalcon] Added `services` part which can be used to `grabServiceFromContainer` and `addServiceToContainer` when conflicting module is used. By @sergeyklay
* [Phalcon] **Refactored**. Moved in-memory session adapter to the separated namespace. By @sergeyklay
* [Phalcon] Fixed overwriting server parameters on requests. By @sergeyklay
* [Asserts] `assertCount` method added by @disc
* Documentation improvements by @CJDennis

#### 2.2.6 (October 2016)

* Ability to update config on run with `--override` (`-o`) option. Usage Examples:
  * `codecept run -o "settings: shuffle: true"`: enable shuffle
  * `codecept run -o "settings: lint: false"`: disable linting
* [WebDriver] **HTML report to include screenshots of failed tests.** See #3602
* [PhpBrowser][Frameworks] HTML report to include HTML of failed tests. See #3602
* [Apc] **Module added** to interact with the Alternative PHP Cache (APC) using either APCu or APC extension. By @sergeyklay
* [Laravel5] Add `run_database_seeder` configuration option. See #3625 and #3630. By @Bouhnosaure
* [Laravel5] Add `database_migrations_path` configuration option. See #3628. By @janhenkgerritsen
* [Laravel5][Lumen] Fixed issue that caused the `have` and `haveMultiple` methods not being available when using the ORM part of the modules. See #3587. By @janhenkgerritsen
* [PhpBrowser][Frameworks] Fixed clicking on a button inside the link
* [PhpBrowser][Frameworks] Click on the first clickable item when clickBySelector is used
* [PhpBrowser][Frameworks] Anchor is no longer sent to server
* Removed tags from `see`/`dontSee` output and friends output
* `--` separates options from arguments in `codecept run` by @Naktibalda. Fixes #3614. See #3615
* Fixed terminating run process with Ctrl-C for PHP 7.0. Disabled graceful termination
* [Yii2] fixed Yii2 logging complex data by @svoboda2010 Fixes #3452
* [Yii2] `cleanup` set to true by default (as it was documented but not enabled).
* [Yii2] Close db connections when running `haveFixtures` by @Ni-san. Fixes #3456. See #3586
* [Yii2] Fixed loading fixtures from `_fixtures` method in testcase by @iRipVanWinkle. See #3565
* [MongoDb] Added support for [mongofill](https://github.com/mongofill/mongofill), an alternative Mongo client in pure PHP. By @hlogeon at #3641
* [MongoDb] Fixed data import using mongotype dump type by @hlogeon #3637
* Fixed #3392 by normalizing namespace loading classes in DI getterby @Mitrichius at #3633
* [Symfony] Fixed #3608  `[PHPUnit_Framework_Exception] implode()` while printing debug for security roles by @Prazmok.
* [Yii1] Fix domain regex #3581 to return correct value by @amashigeseiji See #3597
* [WebDriver] Improved tests stability when Selenium server is gone #3534 by @eXorus. Fixes #3531
* [WebDriver] Tests are errored when Selenium server can't be connected. See #3603
* MetaSteps are printed even with disabled xdebug by @niclopez. See #3600
* [WebDriver] submit button in `submitForm` can be located by name or strict locator by @imjoehaines. See #3560
* [SOAP][REST] removed module conflict by @eXorus.
* Fixed #3571: error handler to call `registerDeprecationErrorHandler` method and `register_shutdown_function` on first SuiteEvent only. By @positronium. See #3572

#### 2.2.5 (September 2016)

* Support for PhpUnit 5.x.
* [Lumen] Major refactoring of Lumen module. See #3533. By @janhenkgerritsen
* [Laravel5] Removed calls to `Auth::logout()`, `Session::flush()` and `Cache::flush()` from after hook. See #3493. By @janhenkgerritsen
* [Memcache] Updated `Memcache::seeInMemcached` to check if the key exists alone or with the desired value. By @sergeyklay
* [Memcache] Added `Memcache::haveInMemcached`. By @sergeyklay
* [Memcache] Fixed `Memcache::dontSeeInMemcached`. By @sergeyklay
* [ZF2] Zend Framework 3 Support. Made `init_autoloader` optional, because ZF3 uses composer for autoloading #3525. By @Naktibalda
* [ZF2] Fixed accessing Doctrine Entity Manager when client is not initialized. By @chris1312. See #3524
* [Yii2] Allow to load fixtures from `_fixtures` method of a testcase. [See reference](https://codeception.com/docs/modules/Yii2#Fixtures). Fixes usage of nested transactions #3520. By @kalyabin and @davertmik
* [Yii1] Fix private property accessible; allows to change urlManager class to subclass of CUrlManager. See @3287. By @amashigeseiji
* Escaped tags in debug output by @Naktibalda. See #3507. Fixes #3495
* Fixed #3410: Wrong subSteps rendering in HTML ResultPrinter by @niclopez
* [WebDriver] Improved exception message thrown when click('name') does not match any element #3546 by @Naktibalda. Fixes #3528
* [SOAP] Removed conflict with REST module. `seeResponseCodeIs` is deprecated in favor of `seeSoapResponseCodeIs` by @eXorus. See #3512. Fixes #3512
* Fixed #3472: group Files not working with a non-empty data provider by @eXorus
* [REST] Disabled resetting server parameters in _before. Fixed REST+Laravel usage: #3263. See #3539. By @janhenkgerritsen
* [REST] Improved output of failed JsonType assertions #3480. By @Naktibalda. Fixes #2858
* [REST] Requests are added to browser history #3446. Fixes regression #3197. By @Naktibalda
* [REST] application/json header check made case insensitive. Fixes #3516. By @Naktibalda
* Fix bug in Coverage Filter related to relative filepaths #3518. By @sbacic
* [Db] PostgreSQL: fixed a problem when sequences are not a standard format (ie. table_id_seq). See #3506. By @alexjeen
* [Symfony] Persist doctrine.dbal.backend_connection if Doctrine2 module is used #3500. Fixes #3479. By @Naktibalda
* [Doctrine2] Using `Doctrine\ORM\EntityManagerInterface` as valid em instance #3467. Fixes #3459. By @akbwm
* [MongoDb] Fixes `mongorestore` command syntax and adds --quiet option to config
* [Facebook] Replaced `facebook/php-sdk-v4` library with `facebook/graph-sdk`.
* Fixed #3433 detection of relative path when `codeception.yml` is not in project root. See #3434. By @loren-osborn
* Handle deprecation messages according to `error_level` setting #3460. Fixes #3424. By @Naktibalda.

#### 2.2.4 (August 2016)

* Improved using complex params, nested params can be set using dot (`.`). See #3339
* [Yii2] Mailer mock is now configured with options that make sense for it. Fixes #3382
* [Yii2] Fixed creating `@webroot` directory on running functional tests. See #3387
* [Yii2] Fixed regression in Yii 2 connector not allowing to work with output of error pages in functional tests. Fixes #3332
* [Mongo] support of standard mongodump/mongorestore tools to populate mongo db database. Thanks @GSokol. Fixes #3427
* [REST] `seeResponseIsJson` fails when response is empty. See #3401, closes #3400
* [AMQP] Added `purgeQueue` and `purgeAllQueues` actions. By @niclopez
* [DataFactory] `haveMultiple` fixed; corrected the order of arguments in `FactoryMuffin->seed`. See #3413 by @buffcode
* [SOAP] Improved error reporting by @eXorus See #3426 #3422
* [SOAP] Added `SOAPAction` config param to unset `SOAPAction` header in SOAP >= 1.2. See #3396
* [REST] fixed digest authentication. See #3416
* [Laravel5] Fixed an issue with error handling for Laravel 5.3. See #3420. By @bonsi.
* [Laravel5] Fixed an issue with uploaded files. See #3417. By @torkiljohnsen.
* [ZF2] Support for zend-mvc 3.0
* [Db] Error is thrown if SQLite memory is used. #3319
* [Frameworks] `REQUEST_TIME` server variable to be set on request. By @gimler. Fixes #3374

#### 2.2.3 (July 2016)

* [Yii2] Improvements:
  * Added `init` part to initialize Yii app for unit and acceptance testing.
  * added `entryScript` and `entryUrl` config values for acceptance testing.
  * Fixtures support: `haveFixtures`, `grabFixtures` methods.
  * Yii logs to be printed in debug mode.
  * added `amOnRoute` method.
  * added `amloggedInAs` method.
  * added `grabComponent` method.
  * added `seeEmailIsSent`, `grabLastSentEmail`, etc and email part.
  * assetManager disabled for unit/functional tests.
* Fixed `@example` to `@group` defined in group files. By @eXorus. Fixes #3278
* Added `ReqiuiresPackage` interface to set external dependencies for modules.
* Fixed timing values in output. Closes #3331
* Fixed merging module configs. Closes #3292
* [Recorder Extension] Fixes saving of files on windows and with using examples.
* [DataFactory] Fixed loading factories twice by @samusenkoiv. See #3314
* [Laravel5] Added `run_database_migrations` configuration option. By @janhenkgerritsen.
* [Laravel5] Added `callArtisan` method. By @janhenkgerritsen.
* [Laravel5] Added `disableModelEvents()` method and `disable_model_events` configuration option. Fixes #2897.
* [REST] Allow objects in files array #3298
* [ZF2] Added addServiceToContainer method
* [ZendExpressive] allow instances of UploadedFile in files array
* [ZF2] Added addServiceToContainer method
* Don't fail test validation when exec function is disabled by @Naktialda

#### 2.2.2

* Parameters can be applied to global `codeception.yml` config. See #3255 Thanks to @LeRondPoint
* Fixed loading of parameters from `.env.*` files. See #3224. By @smotesko
* Better failure diff messages by @k0pernikus
* UTF-8 improvements (replaced with custom `ucfirst`, `strtoupper` => `mb_strtoupper`) by @Naktibalda. See #3211
* Print execution time of non-successful tests by @Naktibalda. Fixes #3274
* [WebDriver][PhpBrowser][Frameworks] Fixed created files on failure. Fixes #3207
* [Frameworks][PhpBrowser] Adjacent forms submit improvements by @dizzy7. Fixes #2331
* [WebDriver] Fixed adjacent `selectOption` with similar options by @eXorus. Fixes #3246
* [DataFactory] fixed loading factories from relative paths. Fixes #3208
* *Test\Gherkin* Added JUnit reporter #3273
* *Test\Gherkin* Added support for multiple languages by @dizzy7. See #3203
* *Test\Unit* Dependencies can pass and receive values the same way as it is done in PHPUnit. Fixes #3213
* [Symfony] Fixed failing tests when the profiler is disabled by @dizzy7. See #3223
* [REST] Added `Codecepion\Util\HttpCode` util class with HTTP code constants. See [class reference](https://github.com/Codeception/Codeception/blob/2.2/docs/reference/HttpCode.md)
* [REST] Support simple key-value format for file uploads. See #3244
* Bugfix with duplicate instances in the modules container #3219 by @dizzy7
* [REST] Added `deleteHeader` method by @Naktibalda. Fixes #3161
* [Yii1] `init` part added to avoid conflicts with `WebDriver`
* `generate:snippets` can accept second parameter to generate snippets from a specific file or folder.
* [Db] Added `grabNumRecords` method by @tocsick. See #3175
* Fixed group events fire twice #3112. By @jstaudenmaier
* [ZF2] Added services part which can be used to `grabServiceFromContainer` when conflicting module is used by @Naktibalda.
* Improved Examples to be Traversable; Fixed console output for complex data structures.
* [Laravel5] Added `haveBinding`, `haveSingleton`, `haveContextualBinding` and `haveInstance` methods. By @janhenkgerritsen. See #2904.
* + changes from 2.1.11

#### 2.2.1

* PHPUnit 5.4 and PHPUnit/php-code-coverage 4.0 compatibility.

#### 2.2.0

* **Gherkin format support**. [Announcement](https://github.com/Codeception/Codeception/pull/2750#issue-129899745)
* **Core Test Format Refactorings** Codeception becomes true multiformat testing platform. Format requires a [Loader](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Test/Loader/LoaderInterface.php) and class extending [Test](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Test/Test.php) class, implementing [TestInterface](https://github.com/Codeception/Codeception/blob/master/src/Codeception/TestInterface.php).
  * *Breaking* `Codeception\TestCase` replaced with `Codeception\TestInterface` in code and in module signatures.
  * *Breaking* Cept/Cest classes are no longer extending `PHPUnit_Framework_TestCase`, so they don't have `expectException`, `getMock`, etc.
  * Reduced stack trace for scenario-driven test formats. Codeception tests implement `PHPUnit_Framework_Test` instead of extending heavy `PHPUnit_Framework_TestCase` class.
* *Breaking* **Conflicts API implemented** Frameworks + PhpBrowser + WebDriver can't be used together unless only non-conflicting part is used. [Announcement](https://codeception.com/03-05-2016/codeception-2.2.-upcoming-features.html#conflicts)
* **Examples** as an alternative to Data Providers. [Announcement](https://codeception.com/03-10-2016/even-more-features-of-codeception.html#examples)
* **Params** loading from yml, env files or environment. [Announcement](https://codeception.com/03-05-2016/codeception-2.2.-upcoming-features.html#params)
* **Test dependencies** with `@depends` annotation. [Announcement](https://codeception.com/03-05-2016/codeception-2.2.-upcoming-features.html#test-dependencies)
* **Custom Commands** inject your own commands as as simple as extension. [Announcement](https://codeception.com/03-10-2016/even-more-features-of-codeception.html#custom-commands)
* `codecept dry-run` command added to show scenario steps without executing them.
* *Breaking* [Dbh] module removed
* *Breaking* [Laravel4] module removed. See #2866
* *Breaking* [Laravel5] Minimum supported Laravel version is 5.1. See [#3243](https://github.com/Codeception/Codeception/issues/3243#issuecomment-227078266)
* *Breaking* [Laravel5] Removed `createModel` method, use `have` method instead. See #2866
* *Breaking* [Laravel5] Removed `makeModel` method. See #2866
* *Breaking* [Laravel5] Renamed `haveModel` method to `have`. See #2866
* *Breaking* [Symfony] public property `container` removed
* *Breaking* [Asserts] removed deprecated `assertLessThen` and `assertGreaterThen`
* *Breaking* mocks created with `Codeception\Util\Stub` are not verified in Cests. See #3005
* *Breaking* [REST] `grabAttributeFrom` renamed to `grabAttributeFromXmlElement` to avoid conflicts
* [WebDriver] allows getting current browser and capabilities in test. [Announcement](https://codeception.com/03-10-2016/even-more-features-of-codeception.html#Getting-current-browser-and-capabilities-in-tests)
* [AngularJS] module added. Extends WebDriver module for AngularJS testing. [Announcement](https://codeception.com/03-10-2016/even-more-features-of-codeception.html#angularjs)
* [DataFactory] module added. Performs data generation using FactoryMuffin library [Announcement](https://codeception.com/03-10-2016/even-more-features-of-codeception.html#datafactory)
* [Redis] Module rewritten using Predis library as driver by @marcverney
* [Laravel5] Added a `haveMultiple` method to create more than one model per call. See #2866
* [Laravel5] [Lumen] The `haveRecord`, `seeRecord`, `dontSeeRecord` and `grabRecord` methods now also accept Eloquent model class names instead of only database table names. See #2866
* [Symfony] module Symfony2 renamed to Symfony
* [Phalcon] Merged `Phalcon1` and `Phalcon2` modules into one `Phalcon` due the fact that Phalcon Framework v1.3.x no longer supported at all
* [Asserts] More `assert*` methods from PHPUnit added
* [Asserts] Added `expectException` method
* [WebDriver][Frameworks][PhpBrowser] `selectOption` can receive option as strict locator to exactly match option by text or by value. Use `['value' => 'myvalue']` or `['text' => 'optiontext']` to select a proper option. By @gdscei and @davertmik See #3003
* Added config option to disable modules using `modules: disabled:`.
* [Sequence] Changed the prefix value. Generated sequences to include id inside a prefix: `sq('user1') => 'user1_876asd8as87a'. Added `prefix` config option.
* Deprecation errors won't fail tests but will be printed.
* Official [Docker image](https://hub.docker.com/r/codeception/codeception/) introduced by @schmunk42

#### 2.1.11

* [Yii1] Improved Yii connector. AR metadata is cleaned up between requests. `regenerateId` of session is disabled.
* [REST][InnerBrowser] redirect is not triggered when Location header is set but response code is not 3xx. By @Naktibalda. Fixes #3171.
* [PhpBrowser][Frameworks] checkboxes can be located by label by @dizzy7. See #3237
* [PhpBrowser][Frameworks] field can be matched by its trimmed label value. See #3209. By @dizzy7
* [WebDriver] fixed URL matching in WebDriver::seeLink
* [WebDriver][InnerBrowser] Improved error messages of `seeLink` and `dontSeeLink`

#### 2.1.10

* PHPUnit version locked to <5.4
* [Db] Added missing support for LIKE condition to SqlSrv driver

#### 2.1.9

* PHPUnit 5.4 compatibility for creating mocks using `Codeception\Util\Stub` by @davertmik. See #3093 and #3080
* Updated dependencies to support Symfony 3.1
* [Laravel5] Fixed issue where non-existing services were called in _before and _after methods. See #3028.
* Fix self-update command to update only to stable versions by @MAXakaWIZARD
* Added `settings: backup_global` to config, to disable backup_global option of PHPUnit by @mkeasling. See #3045. Fixes #3044
* [PhpBrowser][Frameworks] `see` matches UTF-8 text case-insensitively by @Naktibalda. Fixes #3114
* Fixed page object generation with namespaces by @eugene-manuilov and @Naktibalda. See #3126 Fixes #3012
* `--steps` will not disable code coverage. By @Naktibalda. Fixes #2620
* Suppress console coverage report with `--quiet` by @EspadaV8. See #2370
* Improved multibyte output in console by @kt81. See #3130
* [Lumen] Fixed: `initializeLumen()` method has been called twice on start by @kt81. See #3124 #2607
* [Db] Allow INT Parameter SQL Binding by @davidcochrum . Fixes #3118
* [Db] Support LIKE conditions in assertions.
* [Db] Improved regex for parsing comments by @dima-stefantsov. See #3138
* [Dbh] Fix `seeInDatabase` and `dontSeeInDatabase` with empty criteria. Closes #3116
* [Symfony] Improve fail messages on seeInCurrentRoute and seeCurrentRouteIs
* [Symfony] Improve route comparison on seeInCurrentRoute and seeCurrentRouteIs
* [WebDriver] multi session testing with friends improved by @eXorus. Webdriver sessions are finished correctly; `leave()` method added to Friend class. See #3068
* [PhpBrowser] added `handler` and `middleware` config options to customize Guzzle handlers and middleware
* Added full support of phpunit-bridge features.
* [Laravel] Fixed issue where non-existing services were called in _before and _after methods. See #3028.
* [WebDriver] fixed using `saveSessionSnapshot` with codecoverage. Closes #2923
* [ZF2] create new instance of Application for each request

#### 2.1.8

* `Util\Locator` added methods to create locators to match element at their position: `elementAt`, `firstElement`, `lastElement`
* [Symfony] Refactor to unify service retrieval, avoid memleaks and reduce memory footprint. Closes #2938 and #2954.
* [Symfony] New optoin `rebootable_client` that reboots client's kernel before each request.
* [WebDriver] fixed `seeInField` for textarea with whitespaces before and after string. Closes #2921
* [Symfony] Deprecated `grabServiceFromContainer` use `grabService` instead. For consistency with other frameworks.
* [Asserts] More `assert*` methods from PHPUnit added
* [Asserts] Added `expectException` method
* `codecept self-update` works with proxy by @gr1ev0us
* [Phalcon1 add params support for method amOnRoute by @MelnykDmitro

#### 2.1.7

* **PHPUnit 5.x support**
* Global Bootstrap, Suite Bootstrap, Module Initialization happens before test loading. Fixes issues of autoloading TestCase classes introduced in 2.1.5, see #2872
* Added option to skip PHP files validation in `codeception.yml` - `settings: lint: false`
* [Facebook] Updated to  facebook/php-sdk-v4 version 5 by @orhan-swe and @tigerseo #2828 #2415
* [WebDriver] Added `scrollTo` action by @javigomez and @davertmik #2844
* Fix encoding problems in PHP prior to 5.6 by @pejaycz. See #2831
* [Queue] Fixed `clearQueue` for AmazonSQS by @mikitu #2805
* [Db] Fixed loading files in Sqlite @mcustiel See #2812
* [PhpBrowser] `amHttpAuthenticated` allows null, null as parameters to unset authentication. #2896
* `Util\Locator` added `contains` method to easily locate any element containing a text.
* [Laravel5] Added `guard` parameters to `seeAuthentication` and `dontSeeAuthentication` methods. By @janhenkgerritsen. See #2876
* [Laravel5] Added functionality to disable/enable Laravel's exception handling. By @janhenkgerritsen. See #2763
* [Laravel5] Authentication now persists between requests when calling `amLoggedAs` with an instance of `Authenticable`. See #2795
* [REST] Fixed dontSeeXmlResponseMatchesXpath method #2825 by @mangust404
* [ZF2] Fixed POST parameters #2814 by @Naktibalda
* [ZF1] Call Zend_Registry::_unsetInstance in _after #2863 by @Naktibalda

#### 2.1.6

* Starting from 2.1.6 you can **download PHP 5.4 compatible phar build** at https://codeception.com/php54/codecept.phar by @Naktibalda. See [installation guide](https://codeception.com/install).
* [WebDriver] Fixed uploading files with **PhantomJS** #1823 by @DavertMik and @Naktibalda. Please specify your browser name as `phantom` in WebDriver config in order to use PhantomJS-specific hooks.
* Fixed parsing PHP files with spaces in name on PHP<7 by @acuthbert. Fixes #2647
* [WebDriver] Fixed proxy error when using with Chrome #2651 by @vaikla
* [Laravel5] Allow Laravel5 application URL to be set through config. By @gmhenderson. See #2676
* [Laravel5] Mocked events should also return an array. Fix by @devinfd
* Fixed using codecoverage with environments #2634
* Various HHVM improvements by @Naktibalda, for instance, Asserts module issues has been fixed.
* [REST] Fixes #2775 `seeResponseJsonMatchesXpath` when JSON contains ampersand. By @Naktibalda.
* [Filesystem] Added `seeNumberNewLines` method to check the number of new lines in opened file. By @sergeyklay
* [Symfony2] Added `seeCurrentRouteMatches` action by @laszlo-karpati See #2665
* [Sequence] Added `sqs` function to generate unique sequences per suite. #2766 by @johnatannvmd
* [FTP] Fixed various bugs by @k-serenade. See #2755
* [Frameworks][PhpBrowser] Fixed #2733: `seeOptionIsSelected` sees first option as selected if none is selected by @Naktibalda
* [Symfony2] Removed 'localhost' from getInternalDomains by @Naktibalda. Fixed #2717
* Bugfix for using groups by directory on Windows by @tortuetorche See #2550 and #2551
* [REST] Fixed failed message for `seeHttpHeader` and `dontSeeHttpHeader` from null to expected value #2697 by @zondor
* [REST] Added methods to control redirect: `stopFollowingRedirects` and `startFollowingRedirects` by @brutuscat
* [Recorder Extension] Added `animate_slides` config to disable left-right sliding animation between screenshots by @vml-rmott

#### 2.1.5

* **PHP7 support**
* **Symfony3 support**
* [ZendExpressive] **module added** by @Naktibalda
* [Frameworks] **Internal Domains**: Framework modules now throw an `ExternalUrlException` when a test tries to open a URL that is not handled by the framework, i.e. an external URL. See #2396
* Syntax check for tests. If PHP7 is used, `ParseException` handles syntax error, otherwise linting happens with `php -l`. @davertmik
* Fixed Cest generation to not include "use" statements if no namespaces set
* [REST] Modified JsonArray::sequentialArrayIntersect to return complete matches only by @Naktibalda. Fixes #2635
* [REST] Fixes validation of several types with filters. See #2581 By @davertmik
* [REST] JsonType improved URL filter to use `filter_var($value, FILTER_VALIDATE_URL)`
* [REST] JsonType to support collections: all items in an array will be validates against JsonType. By @davertmik
* [REST] Various fixes to JsonType: #2555 #2548 #2542
* [REST] Hides binary request data in debug by @codemedic. Fixed #1884, See #2552
* [WebDriver] Allow `appendField` to work with content editable div by @nsanden #2588
* [WebDriver] Allows adding ssl proxy settings by @mjntan35.
* [Symfony2] Config option `cache_router` added (disabled by default) by @raistlin.
* [Doctrine] Fixed #2060: Too many connections error by @dranzd
* [Symfony2] `services` part added to allow access Symfony DIC while wokring with WebDriver or PhpBrowser by @laszlo-karpati See #2629
* [WebDriver][PhpBrowser] Unified setCookie "expires" param name by @davertmik. See #2582
* [Memcache] add adaptive close call on `_after` by @pfz. See #2572
* [Symfony2] Move kernel booting and container set up into _initialize() method by @Franua  #2491
* [WebDriver] Fixed `seeInField` for textareas by @nsanden
* [Yii2][REST] Fixed using Yii2 as dependency for REST by @Naktibalda. See #2562
* [Laravel5] Removed `enableMiddleware` and `enableEvents` methods. See #2602. By @janhenkgerritsen
* [Laravel] Refactored modules. See #2602. By @janhenkgerritsen
* [Laravel5] Fix bug for `seeCurrentRouteIs` when routes don't match. See #2593. By @maddhatter
* [PhpBrowser] Set curl options for Guzzle6 correctly. See #2533. By @Naktibalda
* Fixed usage of GroupObject by unit tests. GroupObjects can skip tests by @davetmik. See #2617

#### 2.1.4

* [PhpBrowser][Frameworks] Added `_getResponseContent` hidden method. By @Naktibalda
* [PhpBrowser][Frameworks] Added `moveBack` method. By @Naktibalda
* [WebDriver][PhpBrowser][Frameworks] Added `seeInSource`, `dontSeeInSource` methods to check raw HTML instead of stripped text in `see`/`dontSee`. By @zbateson in #2465
* [WebDriver] print Selenium WebDriver logs on failure or manually with `debugWebDriverLogs` in debug mode. Config option `debug_log_entries` added. See #2471 By @MasonM and @DavertMik.
* [ZF2] grabs service from container without reinitializing it. Fixes #2519 where Doctrine2 gets different instances of the entity manager everytime grabServiceFromContainer is called. By @dranzd
* [REST] fixed usage of JsonArray and `json_last_error_msg` function on PHP 5.4. See #2535. By @Naktibalda
* [REST] `seeResponseIsJsonType` can now validate emails with `string:email` definition. By @DavertMik
* [REST] `seeResponseIsJsonType`: `string|null` as well as `null|string` can be used to match null type. #2522 #2500 By @vslovik
* [REST] REST methods can be used to inspect result of the last request made by PhpBrowser or framework module. see #2507. By @Naktibalda
* [Silex] Doctrine provider added. Doctrine2 module can be connected to Silex app with `depends: Silex` in config. By @arduanov #2503
* [Laravel5] Removed `expectEvents` and added `seeEventTriggered` and `dontSeeEventTriggered`. By @janhenkgerritsen
* [Laravel5] Fixed fatal error in `seeCurrentRouteIs` and `seeCurrentActionIs` methods. See #2517. By @janhenkgerritsen
* [Laravel5] Improved the error messages for several methods. See #2476. By @janhenkgerritsen
* [Laravel5] Improved form error methods. See #2432. By @janhenkgerritsen
* [Laravel5] Added wrapper methods for Laravel 5 model factories. See #2442. By @janhenkgerritsen
* [Phalcon] Added `amOnRoute` and `seeCurrentRouteIs` methods by @sergeyklay
* [Phalcon] Added `seeSessionHasValues` by @sergeyklay
* [Phalcon] Added `getApplication()` method by @sergeyklay
* [Symfony2] Sets `xdebug.max_nesting_level` to 200 only if it is lower. Fixes error hiding #2462 by @mhightower
* [Db] Save the search path when importing Postgres dumps #2441 by @EspadaV8
* [Yii2] Fixed problems with transaction rollbacks when using the `cleanup` flag. See #2488. By @ivokund
* [Yii2] Clean up previously uploaded files between tests by @tibee
* Actor classes generation improved by @codemedic #2453
* Added support for nested helper by @luka-zitnik #2494
* Make `generate:suite` respect bootstrap setting in #2512. By @dmitrivereshchagin

#### 2.1.3

* [REST] **Added matching data types** by with new methods `seeResponseMatchesJsonType` and `dontSeeResponseMatchesJsonType`. See #2391
* [PhpBrowser][Frameworks] added `_request` and `_loadPage` hidden API methods for performing arbitrary requests.
* [PhpBrowser][Frameworks] Fixed `seeInField`, `dontSeeInField` for disabled fields #2378. See #2414.
* Environment files can now be located in subfolders of `tests/_env` by @Zifius
* [Symfony2] Fixed issue when accessing profiler when no request has been performed #652.
* [Symfony2] Added `amOnRoute` and `seeCurrentRouteIs` methods  by @raistlin
* [ZF2] Added `amOnRoute` and `seeCurrentRouteIs` methods module, by @Naktibalda
* Fixed issue with trailing slashes in `seeCurrentUrlEquals` and `dontSeeCurrentUrlEquals` methods #2324. By @janhenkgerritsen
* Warning is displayed once using unconfigured environment.
* Fixed loading environment configurations for Cept files by @splinter89
* Fixed bootstrap with namespaces to inject namespaced actor classes properly.
* [PhpBrowser][Frameworks] added hidden `_request()` method to send requests to backend from Helper classes.
* [Laravel5] Added `disableEvents()`, `enableEvents()` and `expectEvents()` methods. By @janhenkgerritsen
* [Laravel5] Added `dontSeeFormErrors()` method. By @janhenkgerritsen
* [Db] Deleted Oracle driver (it existed by mistake, the real driver is Oci). By @Naktibalda
* [Db] Implemented getPrimaryKey method for Sqlite, Mysql, Postgresql, Oracle and MsSql. By @Naktibalda
* [Db] Implemented support for composite primary keys and tables without primary keys. By @Naktibalda
* Fixed the scalarizeArray to be aware of NULL fields #2264. By @fbidu
* [Soap] Fixed SOAP module #2296. By @relaxart
* Fixed a bug where blank lines in a groups file would run every test in the project #2297. By @imjoehaines
* [WebDriver] seeNumberOfElements should only count visible elements #2303. By @sascha-egerer
* [PhpBrowser][Frameworks] Verbose output for all HTTP requests. By @Naktibalda
* [PhpBrowser][Frameworks] Throw `Codeception\Exception\ExternalUrlException` when framework module tries to open an external URL #2328. By @Naktibalda
* [PhpBrowser][Frameworks] Added `switchToIframe` method. By @Naktibalda
* [Dbh] module deprecated

#### 2.1.2

* **Updated to PHPUnit 4.8**
* Enhancement: **Wildcard includes enabled when testing [multiple applications](https://codeception.com/docs/08-Customization#One-Runner-for-Multiple-Applications)**. See #2016 By @nzod
* [Symfony2] fixed Doctrine2 integration: Doctrine transactions will start before each test and rollback afterwards. *2015-08-08*
* [Doctrine2] establishing connection and starting transaction is moved to `_before`. *2015-08-08*
* [PhpBrowser] Removed disabled and file fields from form values. By @Naktibalda *2015-08-08*
* [ZF2] Added grabServiceFromContainer function. By InVeX  *2015-08-08*
* [PhpBrowser][Guzzle6] Disabled strict mode of CookieJar #2234 By @Naktibalda *2015-08-04*
* [Laravel5] Added `disableMiddleware()` and `enableMiddleware()` methods. By @janhenkgerritsen *2015-08-07*
* Enhancement: If a specific *ActorActions trait does not exist in `tests/_support/_generated` directory, it will be created automatically before run.
* Enhancement: do not execute all included suites if you run one specific suite *2015-08-08*
* `Extension\Recorder` navigate over slides with left and right arrow keys, do not create screenshots for comment steps.
* `Extension\Recorder` generates index html for all saved records.
* `Extension\Recorder` fixed for creating directories twice. Fixed #2216
* `Extension\Logger` fixed #2216
* Fixed injection of Helpers into Cest and Test files. See #2222
* `Stub::makeEmpty` on interfaces works again by @Naktibalda
* Command `generate:scenarios` fixed for Cest files by @mkudenko See #1962
* [Db] Quoted table name in Db::select, removed identical methods from child classes by @Naktibalda. See #2231
* [WebDriver] added support for running tests on a remote server behind a proxy with `http_proxy` and `http_proxy_port` config options by @jdq22 *2015-07-29*
* [Laravel] Fixed issue with error handling for `haveRecord()` method in Laravel modules #2217 by @janhenkgerritsen *2015-07-28*
* Fixed displayed XML/HTML report path #2187 by @Naktibalda *2015-07-27*
* [WebDriver] Fixed `waitForElementChange` fatal error by @stipsan
* [Db] Enhanced dollar quoting ($$) processing in PostgreSQL driver by @YasserHassan *2015-07-20*
* [REST] Created tests for file-upload with REST module. By @Naktibalda *2015-08-08*
* [Lumen] Fixed issue where wrong request object was passed to the Lumen application by @janhenkgerritsen *2015-07-18*

#### 2.1.1

* [WebDriver] **Upgraded to facebook/webdriver 1.0** *2015-07-11*
  WebDriver classes were moved to `Facebook\WebDriver` namespace. Please take that into account when using WebDriver API directly.
  Till 2.2 Codeception will keep non-namespaced aliases of WebDriver classes.
* Module Reference now contains documentation for hidden API methods which should be used in Helper classes
* Skipped and Incomplete tests won't fire `test.before` and `test.after` events. For instance, WebDriver browser won't be started and Db cleanups won't be executed on incomplete or skipped tests.
* Annotations `skip` and `incomplete` enabled in Cest files #2131
* [WebDriver][PhpBrowser][Frameworks] `_findElements($locator)` method added to use in Helper classes *2015-07-11*
  Now you can use `$this->getModule('WebDriver')->findElements('.user');` in Helpers to match all elements with `user` class using WebDriver module
* [PhpBrowser] Fixed `amOnUrl` method to open absolute URLs.
* [PhpBrowser][Frameworks] Fix for `fillField` using values that contain ampersands by @GawainLynch and @zbateson Issue #2132
* [WebDriver][PhpBrowser][Frameworks] Fixed missing HTTPS when trying to access protected pages #2141

#### 2.1.0

* [Recorder](https://github.com/Codeception/Codeception/tree/master/ext#codeceptionextensionrecorder) extension added. Shows acceptance test progress with a recorded slideshow.
* **Updated to Guzzle 6**. Codeception can now work both with Guzzle v5 and Guzzle v6. PhpBrowser chooses right connector depending on Guzzle version installed. By @davertmik and @enumag
* Annotations in Cept files.
  Instead of calling `$scenario->skip()`, `$scenario->group('firefox')`, etc, it is recommended to set scenario metadata with annotations `// @skip`, `// @group firefox`.
  Annotations can be parsed from line or block comments. `$scenario->skip()` and `$scenario->incomplete()` are still valid and can be executed inside conditional statements:
  ```
  if (!extension_loaded('xdebug')) $scenario->skip('Xdebug required')
  ```
* **PSR-4**: all support classes moved to `tests/_support` by default. Actors, Helpers, PageObjects, StepObjects, GroupObjects to follow PSR-4 naming style. Autoloader implemented by @splinter89.
* **Dependency Injection**: support classes can be injected into tests. Support classes can be injected into each other too. This happens by implementing method `_inject` and explicitly specifying class names as parameters. Implemented by @splinter89.
* **Actor classes can be extended**, their generated parts were moved to special traits in `_generated` namespace. Each *Tester class can be updated with custom methods.
* **Module config simplified**: Modules can be configured in `enabled` section of suite config.
* **Conflicts**: module can define conflicts with each other by implementing `_conflicts` method
* **Dependencies**: module can explicitly define dependencies and expect their injection by implementing `_inject` and `_depends` methods and relying on dependency injection container.
* **Current** modules, environment, and test name can be received in scenario. Example: `$scenario->current('env')` returns current environment name. Fixes #1251
* **Environment Matrix**: environments can be merged. Environment configs can be created in `tests/_envs`, environment generator added. Implemented by By @sjableka. See #1747
* **Custom Printers**: XML, JSON, TAP, Report printers can be redefined in configuration. See #1425
* [Db] Added `reconnect` option for long running tests, which will connect to database before the test and disconnect after. By @Naktibalda
* Module parts. Actions of modules can be loaded partially in order to disable actions which are not used in current tests. For instance, disable web actions of framework modules in unit testsing.
* **Kohana**, **Symfony1**, **Doctrine1** modules considered deprecated and moved to standalone packages.
* `shuffle` added to settings. Randomizes order of running tests. See #1504
* Console output improved: scenario stack traces contain files and lines of fail.
* [Doctrine2][Symfony2] `symfony_em_service` config option moved from Doctrine2 to Symfony2 module and renamed to `em_service` *2015-06-03*
* [PhpBrowser][Frameworks] Fixed cloning form nodes `Codeception\Lib\InnerBrowser::getFormFromCrawler(): ID XXX already defined` *2015-05-13*
* [WebDriver] session snapshot implemented, allows to store cookies and load them, i.e., to keep user session between testss.
* [WebDriver][PhpBrowser][Frameworks] Malformed XPath locators wil throw an exception #1441
* `MODULE_INIT` event is fired before initializing modules #1370
* Graceful tests termination using `pcntl_signal`. See #1286
* Group classes renamed to GroupObjects; Base GroupObject class renamed to `Codeception\GroupObject`
* Official extensions moved to `ext` dir; Base Extension class renamed to `Codeception\Extension`
* Duplicate environment options won't cause Codeception to run environment tests twice
* [Phalcon1] `haveServiceInDi` method implemented by @sergeyklay
* [Db] `seeNumRecords` method added by @sergeyklay

#### 2.0.15

* [Phalcon1] Fixed getting has more than one field by @sergeyklay #2010.
* [PhpBrowser][Frameworks] Compute relative URIs against the effective request URI when there is a redirect. #2058 #2057
* [PhpBrowser] Fixed Guzzle Connector headers by @valeriyaslovikovskaya #2028
* [Symfony2] kernel is created for every test by @quaninte #2020
* [WebDriver] Added WebDriver init settings `connection_timeout` and `request_timeout` by @n8whnp #2065
* [MongoDb] added ability to change the database by @clarkeash #2015
* [Doctrine2] Fixed issues after first request is made #2025 @AlexStansfield
* [REST] Improved JsonArray to compare repeated values correctly by @Naktibalda #2070
* [MongoDb] Remove not necessary config fields `user` and `password` by @nicklasos
* `Stub::construct` can be used to set private/protected properties by @Naktibalda #2082
* Fixed @before and @after hooks in Cest. _before method was executed on each call of method specified in @before annotation *2015-06-15*
* [Laravel5] Fix for domains in `route()` helper. See #2000. *2015-06-04*
* [REST] Fixed sending `JsonSerializable` object on POST by @Naktibalda and @andersonamuller. See #1988 #1994
* [MongoDb] escaped filename shell argument for loading MongoDB by @christoph-hautzinger. #1998 *2015-06-03*
* [Lumen] **Module added** by @janhenkgerritsen

#### 2.0.14

* Improved output *2015-05-22*
  * data providers print simplified
  * output respects console size with `tput` and tries to fit area
  * non-interactive environments for `tput` are ignored
* [Frameworks][PhpBrowser][Symfony2] Fields are passed as PHP-array on form submission the same way as `Symfony\Component\DomCrawler\Form->getPhpValues()` does. Fixes fails of Symfony form tests  *2015-05-22*
* [Laravel4] Fixed bug with filters. See #1810. *2015-05-21*
* [PhpBrowser][Frameworks] Fixed working associative array form fields (like `FooBar[bar]`). Fixes regression #1923 by @davertmik and @zbateson.
* [PhpBrowser][Frameworks] Fixed cloning form nodes Codeception\Lib\InnerBrowser::getFormFromCrawler(): ID XXX already defined *2015-05-13*
* [Laravel4] [Laravel5] Improved error message for `amOnRoute` and `amOnAction` methods if route or action does not exist *2015-05-04*
* [Laravel4] Fixed issue with session configuration *2015-05-01*
* [Laravel4] Partial rewrite of module *2015-05-01*
  * Added `getApplication()` method
  * Added `seeFormHasErrors()`, `seeFormErrorMessages(array $bindings)` and `seeFormErrorMessage($key, $errorMessage)` methods
  * Deprecated `seeSessionHasErrors()` and `seeSessionErrorMessage(array $bindings)` methods.
* fixed stderr output messages in PHPStorm console *2015-04-26*
* Allow following symlinks when searching for tests by @nechutny
* Fixed `g:scenarios --single-file` missing linebreaks between scenarios by @Zifius Parially fixes #1866
* [Frameworks][PhpBrowser] Fixed errors like `[ErrorException] Array to string conversion` when using strict locators. Fix by @neochief #1881
* [Frameworks][PhpBrowser] Fix for URLs with query parameters not properly constructed for GET form submissions by @zbateson Fixes #1891
* [Facebook] Updated Facebook SDK to 4.0 by @enginvardar. See #1896.
* [DB] Quote table name in `Db::getPrimaryKeyColumn` and `Db::deleteQueryMethods` by @Naktibalda. See #1912
* [Silex] Can be used for API functional testing. Improvement by @arduanov See #1945
* [Doctrine2] Added new config option `symfony_em_service` to specify service name for Doctrine entity manager in Symfony DIC by @danieltuwien #1915
* [Db] Reversed order of removing records with foreign keys created by `haveInDatabase`. Fixes #1942 by @satahippy
* [Db] Quote names in PostgreSQL queries. Fix #1916 by @satahippy
* [ZF1] Various improvements by @Naktibalda See #1924
* [ZF2][ZF2] Improved passing request headers by @Naktibalda
* [Phalcon1] Improved dependency injector container check by @sergeyklay #1967
* [Yii2] Enabled logging by @TriAnMan #1539
* Attribute `feature` added to xml reports in `Codeception\TestCase\Test` test report by @tankist. See #1964
* Fixed #1779 by @Naktibalda
* ...special thanks to @Naktibalda for creating demo [ZF1](https://github.com/Naktibalda/codeception-zf1-tests) and [ZF2](https://github.com/Naktibalda/codeception-zf2-tests) applications with api tests examples.

#### 2.0.13

* Updated to PHPUnit 4.6
* [Db] fixed regression introduced in 2.0.11. `haveInDatabase` works in PostgreSQL on tables with 'id' as primary key. Fix by @akireikin #1846 #1761
* added `--no-rebuild` option to disable automatic actor classes rebuilds *2015-04-24*
* suppressed warnings on failed actor classes auto-rebuilds
* enable group listener for grouping with annotation by @litpuvn Fixes #1830
* unix terminals output improved by calculating screen size. Done by @DexterHD See #1858
* [Yii2] Remove line to activate request parsers by @m8rge #1843
* [PhpBrowser][Frameworks] Various `fillField`/`submitForm` improvements by @zbateson See #1840. Fixes #1828, #1689
* Allow following symlinks when searching for tests by @nechutny #1862

#### 2.0.12

* [Laravel5] Fix for undefined method `Symfony\Component\HttpFoundation\Request::route()` by @janhenkgerritsen
* [Yii2] Fix https support and verbose output added by @TriAnMan See #1770
* [Yii2] `haveRecord` to insert insert unsafe attributes by @nkovacs. Fixes #1775
* [Asserts] `assertSame` and `assertNotSame` added by @hidechae *2015-04-03*
* [Laravel5] Add `packages` option for application packages by @jonathantorres  #1782
* [PhpBrowser][WebDriver][Frameworks] `seeInFormFields` method added for checking multiple form field values. See #1795 *2015-04-03*
* [ZF2] Fixed setting Content-Type header by @Gorp See #1796 *2015-04-03*
* [Yii2] Pass body request into yii2 request, allowing to send Xml payload by @m8rge. See #1806
* Fixed conditional assertions firing TEST_AFTER event by @zbateson. Issues #1647 #1354 and #1111 *2015-04-03*
* Fixing mocking Laravel models by removing `__mocked` property in classes created with Stub by @EVODelavega See #1785 *2015-04-03*
* [WebDriver] `submitForm` allows array parameter values by @zbateson *2015-04-03*
* [SOAP] Added `framework_collect_buffer` option to disable buffering output by @Noles *2015-04-03*
* [Laravel4] added  to run artisan commands by @bgetsug *2015-04-03*
* [AMQP] add a routing key to a push to exchange by @jistok *2015-04-03*
* Interactive console updated to work with namespaces by @jistok *2015-04-03*
* [PhpBrowser] added deleteHeader method by @zbateson *2015-04-03*
* Disabling loading of bootstrap files by setting `bootstrap: false` in globall settings or inside suite config. Fixes #1813 *2015-04-03*

#### 2.0.11

* Updated to PHPUnit 4.5 *2015-02-23*
* [Laravel5] module added by @janhenkgerritsen *2015-02-23*
* Fixed problem with extensions being always loaded with default options by @sjableka. Fixes #1716 *2015-02-23*
* [Db] Cleanup now works for tables with primary is not named 'id'. Fix by @KennethVeipert See #1727 *2015-02-23*
* [PhpBrowser][Frameworks] `submitForm` improvements by @zbateson: *2015-02-23*

Removed submitForm's reliance on using parse_str and parse_url to
generate params (which caused unexpected side-effects like failing
for values with ampersands).

Modified the css selector for input elements so disabled input
elements don't get sent default values.

Modifications to ensure multiple values get sent correctly.

* [Laravel4] middleware is loaded on requests. Fixed #1680 by @jotweh *2015-02-23*
* [Dbh] Begin transaction only unless transaction is already in progress by @thecatontheflat *2015-02-23*
* [PhpBrowser][Frameworks] Fix quiet crash when crawler is null by @aivus. See #1714 *2015-02-23*
* [Yii2] Fixed usage of PUT method by @miroslav-chandler *2015-02-23*

#### 2.1.0

* [WebDriver] Saving and restoring session snapshots implemented *2015-03-16*

#### 2.0.10

* **Console Improvement**: better formatting of test progress. Improved displaying of debug messages and PHP Fatal Errors.
  Codeception now uses features of interactive shell to print testing progress.
  In case of non-interactive shell (when running from CI like Jenkins) this feature is gracefully degraded to standard mode.
  You can turn off interactive printing manually by providing `--no-interaction` option or simply `-n`
* `ExceptionWrapper` messages unpacked into normal and verbose exceptions.
* HTML reports now allow to filter tests by status. Thanks to @raistlin
* Added '_failed' hook for Cest tests. Fixes #1660 *2015-02-02*
* [REST] fixed setting Host header. Issue #1650 *2015-02-02*
* [Laravel4] Disconnecting from database after each test to prevent Too many connections exception #1665 by @mnabialek *2015-02-02*
* [Symfony2] Fixed kernel reuse in #1656 by @hacfi *2015-02-01*
* [REST] request params are now correctly saved to `$this->params` property. Fixes #1682 by @gmhenderson *2015-02-01*
* Interactive shell updated: deprecated Symfony helpers replaced, printed output cleaned *2015-01-28*
* [PhpBrowser][Frameworks] Fixed `matchOption` to return the option value in case there is no value attribute by @synchrone. See #1663 *2015-01-26*
* Fixed remote context options on CodeCoverage by @synchrone. See #1664 *2015-01-26*
* [MongoDb] `seeNumElementsInCollection` method added by @sahanh
* [MongoDb] Added new methods: `grabCollectionCount`, `seeElementIsArray`, `seeElementIsObject` by @antoniofrignani
* [WebDriver] Allow `selectOption()` to select options not inside forms by @n8whnp See #1638
* [FTP] Added support for sftp connections with an RSA SSH key by @mattvot.
* [PhpBrowser][WebDriver] allows to handle domain and path for cookies *2015-01-24*
* [CLI] Allow CLI module to handle nonzero response codes without failing by @DevShep
* [Yii2] Fix the bug with `session_id()`. See #1606 by @TriAnMan
* [PhpBrowser][Frameworks] Fix double slashes in certain forms submitted by `submitForm` by @Revisor. See #1625
* [Facebook] `grabFacebookTestUserId` method added by @ipalaus
* Always eval error level settings passed from config file.

#### 2.0.9

* **Fixed Symfony 2.6 compatibility in Yaml::parse by @antonioribeiro**
* Specific tests can be executed without adding .php extension by @antonioribeiro See #1531 *2014-12-20*

Now you can run specific test using shorter format:

```
codecept run unit tests/unit/Codeception/TestLoaderTest
codecept run unit Codeception
codecept run unit Codeception:testAddCept

codecept run unit Codeception/TestLoaderTest.php
codecept run unit Codeception/TestLoaderTest
codecept run unit Codeception/TestLoaderTest.php:testAddCept
codecept run unit Codeception/TestLoaderTest:testAddCept

codecept run unit tests/unit/Codeception
codecept run unit tests/unit/Codeception:testAddCept
codecept run unit tests/unit/Codeception/TestLoaderTest.php
codecept run unit tests/unit/Codeception/TestLoaderTest.php:testAddCept
codecept run unit tests/unit/Codeception/TestLoaderTest
codecept run unit tests/unit/Codeception/TestLoaderTest:testAddCept
```

* [Db] Remove table constraints prior to drop table in clean up for SqlSrv by @jonsa *2014-12-20*
* [PhpBrowser][Frameworks] Fixed: submitForm with form using site-root relative paths may fail depending on configuration #1510 by @zbateson *2014-12-20*
* [WebDriver][PhpBrowser][Frameworks] `seeInField` method to work for radio, checkbox and select fields. Thanks to @zbateson *2014-12-20*
* Fixed usage of `--no-colors` flag by @zbateson. Issue #1562 *2014-12-20*
* [REST] sendXXX methods now encode objects implementing JsonSerializable interfaces. *2014-12-19*
* [REST] added methods to validate JSON structure *2014-12-19*

[seeResponseJsonMatchesJsonPath](https://codeception.com/docs/modules/REST#seeResponseJsonMatchesJsonPath) validates response JSON against [JsonPath](https://goessner.net/articles/JsonPath/).
Usage of JsonPath requires library `flow/jsonpath` to be installed.

[seeResponseJsonMatchesXpath](https://codeception.com/docs/modules/REST#seeResponseJsonMatchesXpath) validates response JSON against XPath.
It converts JSON structure into valid XML document and executes XPath for it.

[grabDataFromResponseByJsonPath](https://codeception.com/docs/modules/REST#grabDataFromResponseByJsonPath) method was added as well to grab data JSONPath.

* [REST] `grabDataFromJsonResponse` deprecated in favor of `grabDataFromResponseByJsonPath` *2014-12-19*
* [PhpBrowser][Frameworks] fixed `Unreachable field` error while filling [] fields in input and textarea fields. Issues #1585 #1602 *2014-12-18*

#### 2.0.8

* Dependencies updated: facebook/php-webdriver 0.5.x and guzzle 5 *2014-11-17*
* [WebDriver] Fixed selectOption and (dont)seeOptionIsSelected for multiple radio button groups by @MasonM. See #1467 *2014-11-18*
* [WebDriver][PhpBrowser][Frameworks] Clicked submit button can be specified as 3rd parameter in `submitForm` method by @zbateson. See #1518
* [ZF1] Format ZF response to Symfony\Component\BrowserKit\Response by @MOuli90. Fixes #1476
* [PhpBrowser][Frameworks] fixed `grabValueFrom` method by @zbateson. See #1512
* [Db] Fixed Postgresql error with schemas by @rafreis. Fixes #970
* [PhpBrowser] Fix for meta refresh tags with interval by @zbateson. See #1515
* [PhpBrowser][Frameworks] Fixed: `grabTextFrom` doesn't work with regex by @zbateson. See #1519
* Cest tests support multiple `@before` and `@after` annotations. Thanks to @draculus and @zbateson. See #1517
* [FTP] Stops test execution on failed connection by @yegortokmakov
* [AMQP] Fix for purging queues on initialization stage. Check for open channel is not needed and it prevents from cleaning queue by @yegortokmakov
* CodeCoverage remote context configuration added by @synchrone. See #1524 [Documentation updated](https://codeception.com/docs/11-Codecoverage#Remote-Context-Options)
* Implemented better descriptions for error exception. Fix #1503
* Added `c3_url` option to code coverage settings. `c3_url` allows to explicitly set url for index file with c3 included. See #1024
* [PhpBrowser][Frameworks] Fixed selecting checkbock in a group of checkboxes #1535
* [PhpBrowser][Frameworks] submitForm sends default values for radio buttons and checkboxes by @zbateson. Fixes #1507 *2014-11-3*
* [ZF2] Close any open ZF2 sessions by @FnTm. See #1486 *2014-10-24*

#### 2.0.7

* [Db] Made the postgresql loader load $$ syntax correctly by @rtuin. See #1450 *2014-10-12*
* [Yii1] fixed syntax typo in Yii1 Connector by @xt99 *2014-10-12*
* [PhpBrowser][WebDriver] amOnUrl method added for opening absolute urls. This behavior taken from amOnPage method, initially introduced in 2.0.6 *2014-10-12*
* Fixed usage of whitespaces in wantTo. See #1456 *2014-10-12*
* [WebDriver][PhpBrowser][Frameworks] fillField is matching element by name, then by CSS. Fixes #1454 *2014-10-12*

#### 2.0.6

* Fixed list of executed suites while running included suites by @gureedo. See #1427 *2014-10-08*
* [Frameworks] support files and request names containing square brackets, dots, spaces. See #1438. Thanks to @kkopachev *2014-10-08*
* [PhpBrowser] array of files for Guzzle to support format: file[foo][bar]. Fixes #342 by @kkopachev *2014-10-07*
* Added strict mode for XML generation. *2014-10-06*

In this mode only standard JUnit attributes are added to XML reports, so special attributes like `feature` won't be included. This improvement fixes usage XML reports with Jenkins #1408
  To enable strict xml generation add to `codeception.yml`:

```
settings:
    strict_xml: true
```

* Fixed retrieval of codecoverage reports on remote server #1379 *2014-10-06*
* [PhpBrowser][Frameworks] Malformed XPath won't throw fatal error, but makes tests fail. Fixes #1409 *2014-10-06*
* Build command generates actors for included suites. See #1267 *2014-10-03*
* CodeCoverage throws error on unsuccessful requests (status code is not 200) to remote server. Fixes #346 *2014-10-03*
* CodeCoverage can be disabled per suite. Fix #1249 *2014-10-02*
* Fix: --colors and --no-colors options can override settings from config *2014-10-02*
* [WebDriver] `waitForElement*` methods accept strict locators and WebDriverBy as parameters. See #1396 *2014-09-29*
* [PhpBrowser] `executeInGuzzle` uses baseUrl set from config. Fixes #1416 *2014-09-29*
* [Laravel4] fire booted callbacks between requests without kernel reboot. Fixes #1389, See #1415 *2014-09-29*
* [WebDriver][PhpBrowser][Frameworks] `submitForm` accepts forms with document-relative paths. Fixes #1274 *2014-09-28*
* [WebDriver][PhpBrowser][Frameworks] Fixed #1381: `fillField` fails for a form without a submit button by @zbateson *2014-09-28*
* [PhpBrowser][WebDriver] `amOnPage` now accepts absolute urls *2014-09-27*
* [Db] ignore errors from lastInsertId by @tomykaira *2014-09-27*
* [WebDriver] saves HTML snapshot on fail *2014-09-27*
* [WebDriver] fixed #1392: findField should select by id, css, then fall back on xpath *2014-09-27*
* [WebDriver] Don't check for xpath if css selector is set, by @Danielss89 #1367 *2014-09-27*
* Specify actor class for friends by @tomykaira. See #1394 *2014-09-27*

#### 2.0.5

* [Queue] module added with AWS, Iron.io, and Beanstalkd support. Thanks to @nathanmac *2014-08-21*
* [WebDriver] fixed attachFile error message when file does not exists #1333 by @TroyRudolph *2014-08-21*
* [Asserts] Added assertLessThan and assertLessThanOrEqual by @Great-Antique *2014-08-21*
* [ZF2] fixed #1283 by @dkiselew *2014-08-21*
* Added functionality to Stub to allow ConsecutiveCallStub. See #1300 by @nathanmac *2014-08-21*
* Cest generator inserts  object into _before and _after methods by @TroyRudolph *2014-08-21*
* [PhpBrowser][Frameworks] Fixed #1304 - ->selectOption() fails if two submit buttons present by @fdjohnston *2014-08-21*
* [WebDriver][PhpBrowser][Frameworks] seeNumberOfElements method added by @dynasource *2014-08-21*
* recursive runner for included suites by @dynasource *2014-08-21*
* Disabled skipped/incomplete tests logging in jUnit logger for smooth Bamboo integration by @ayastreb *2014-08-21*

#### 2.0.4

* [Laravel4] More functional, cli, and api tests added to demo application <https://github.com/Codeception/sample-l4-app> *2014-08-05*
* Fix: GroupManager uses DIRECTORY_SEPARATOR for loaded tests *2014-08-05*
* [Laravel4] Uses `app.url` config value for creating requests. Fixes #1095 *2014-08-04*
* [Laravel4] `seeAuthenticated` / `dontSeeAuthenticated` assertions added to check that current user is authenticated *2014-08-04*
* [Laravel4] `logout` action added *2014-08-04*
* [Laravel4] `amLoggedAs` can login user by credentials *2014-08-04*
* [Laravel4] Added `amOnRoute`, `amOnAction`, `seeCurrentRouteIs`, `seeCurrentActionIs` actions *2014-08-04*
* [Laravel4] Added `haveEnabledFilters` and `haveDisabledFilters` actions to toggle filters in runtime *2014-08-04*
* [Laravel4] Added `filters` option to enable filters on testing *2014-08-04*
* [REST] seeResponseContainsJson should not take arrays order into account. See #1268 *2014-08-04*
* [REST] grabDataFromJsonResponse accepts empty path to return entire json response *2014-08-04*
* [REST] print_r replaced with var_export for better output on json comparison #1210 *2014-08-02*
* [REST] Reset request variables in the before hook by @brutuscat #1232 *2014-08-01*
* [Db] Oci driver for oracle database by @Sikolasol #1234 #1243 *2014-08-01*
* [Laravel4] Unit testing and test environment are now configurable #1255 by @ipalaus *2014-08-01*
* [Laravel4] Fixed Cest testing when using Laravel's Auth #1258 by @ipalaus *2014-08-01*
* FIX #948 code coverage HTML: uncovered files missing by @RLasinski *2014-07-26*
* [Laravel4] project root relative config parameter added by @kernio *2014-07-26*

#### 2.0.3

* [Symfony2] Symfony3 directory structure implemented by @a6software *2014-07-21*
* Console: printing returned values *2014-07-21*
* FIX: TAP and JSON logging should not be started when no option --json or --tap provided *2014-07-21*
* [Doctrine2] FIXED: persisting transaction between Symfony requests *2014-07-19*
* [Symfony2] created Symfony2 connector with persistent services *2014-07-19*
* [Doctrine2] implemented haveInRepository method (previously empty) *2014-07-17*
* When Cest fails @after method wont be executed *2014-07-17*
* [Laravel4] App is rebooted before each test. Fixes #1205 *2014-07-15*
* FIX: `codeception/specify` is now available in phar *2014-07-14*
* FIX: Interactive console works again *2014-07-09*
* `_bootstrap.php` is now loaded before `beforeSuite` module hooks.
* FIX: Suite `_bootstrap.php` was loaded after test run by @samdark *2014-07-11*

#### 2.0.2

* [PhpBrowser][Frameworks] correctly send values when there are several submit buttons in a form by @TrustNik *2014-07-08*
* [REST] fixed connection with framework modules *2014-07-06*
* [PhpBrowser][Frameworks] `checkOption` now works for checkboxes with array[] name by @TrustNik
* [PhpBrowser][Frameworks] FIX: `seeOptionIsSelected` and `dontSeeOptionIsSelected` now works with radiobuttons by @TrustNik *2014-07-05*
* [FTP] MODULE ADDED by @nathanmac *2014-07-05*
* [WebDriver] Enabled remote upload of local files to remote selenium server by @motin *2014-07-05*
* [Yii2][Yii1] disabled logging for better functional test performance

#### 2.0.1

* [Phalcon1] Fixed connector
* [WebDriver] added seeInPageSource and dontSeeInPageSource methods
* [WebDriver] see method now checks only for visible BODY element by @artyfarty
* [REST] added Bearer authentication by @dizews
* removed auto added submit buttons in forms previously used as hook for DomCrawler
* BUGFIX: PHP 5.4.x compatibility fixed. Sample error output: 'Method WelcomeCept.php does not exist' #1084 #1069 #1109
* Second parameter of Cest method is treated as scenario variable on parse. Fix #1058
* prints raw stack trace including codeception classes in -vvv mode
* screenshots on fail are saved to properly named files #1075
* [Symfony2] added debug config option to switch debug mode by @pmcjury

#### 2.0.0

* renamed `_logs` dir to `_output` by default
* renamed `_helpers` dir to `_support` by default
* Guy renamed to Tester
* Bootstrap command got 3 installation modes: default, compat, setup
* added --coverage-text option

#### 2.0.0-RC2

* removed fabpot/goutte, added Guzzle4 connector
* group configuration can accept groups by patterns

#### 2.0.0-RC

* [WebDriver] makeScreenshot does not use filename of a test
* added `grabAttributeFrom`
* seeElement to accept attributes in second parameter: seeElement('input',['name'=>'login'])

#### 2.0.0-beta

* executeInGuzzle is back in PhpBrowser
* environment can be accessed via ->env in test
* before/after methods of Cest can take  object
* moved logger to extension
* bootstrap files are loaded before suite only
* extension can reconfigure global config
* removed RefactorAddNamespace and Analyze commands
* added options to set output files for xml, html reports, and coverage
* added extension to rerun failed tests
* webdriver upgraded to 0.4
* upgraded to PHPUnit 4
