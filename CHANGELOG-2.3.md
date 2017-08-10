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
* Dereferencing vairables for Steps output. Fixes #4402 by @alambe
* [Symfony] Load persistent services before loading profiler. See #4437 by @samusenkoiv

#### 2.3.4

* Added `@prepare` annotation to make realtime configuration for tests in Cest and Test classes. [See documentation](http://codeception.com/docs/06-ModulesAndHelpers#Runtime-Configuration-of-a-Test).
 
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
* [WebDriver] **SmartWait**. Automatically waits for a few extra seconds for element to appear on a page before failing. Can reduce high usage of `wait*` methods. [See Documentation](http://codeception.com/docs/03-AcceptanceTests#SmartWait)
* Added [RunProcess extension](http://codeception.com/extensions#RunProcess). Use it to start/stop Selenium (or other process) automatically for a test suite.   
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


