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

* Starting from 2.1.6 you can **download PHP 5.4 compatible phar build** at http://codeception.com/php54/codecept.phar by @Naktibalda. See [installation guide](http://codeception.com/install).
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
* Enhancement: **Wildcard includes enabled when testing [multiple applications](http://codeception.com/docs/08-Customization#One-Runner-for-Multiple-Applications)**. See #2016 By @nzod
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