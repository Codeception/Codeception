# Changelog

#### 2.2.5

* Support for PhpUnit 5.x.
* [Lumen] Major refactoring of Lumen module. See [#3533](https://github.com/Codeception/Codeception/pull/3533). By @janhenkgerritsen
* [Laravel5] Removed calls to `Auth::logout()`, `Session::flush()` and `Cache::flush()` from after hook. See #3493. By @janhenkgerritsen
* [Memcache] Updated `Memcache::seeInMemcached` to check if the key exists alone or with the desired value. By @sergeyklay
* [Memcache] Added `Memcache::haveInMemcached`. By @sergeyklay
* [Memcache] Fixed `Memcache::dontSeeInMemcached`. By @sergeyklay
* [ZF2] Zend Framework 3 Support. Made `init_autoloader` optional, because ZF3 uses composer for autoloading #3525. By @Naktibalda
* [ZF2] Fixed accessing Doctrine Entity Manager when client is not initialized. By @chris1312. See #3524
* [Yii2] Allow to load fixtures from `_fixtures` method of a testcase. [See reference](http://codeception.com/docs/modules/Yii2#Fixtures). Fixes usage of nested transactions #3520. By @kalyabin and @davertmik
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

#### 2.2.4

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

#### 2.2.3

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
* *Breaking* **Conflicts API implemented** Frameworks + PhpBrowser + WebDriver can't be used together unless only non-conflicting part is used. [Announcement](http://codeception.com/03-05-2016/codeception-2.2.-upcoming-features.html#conflicts)
* **Examples** as an alternative to Data Providers. [Announcement](http://codeception.com/03-10-2016/even-more-features-of-codeception.html#examples)
* **Params** loading from yml, env files or environment. [Announcement](http://codeception.com/03-05-2016/codeception-2.2.-upcoming-features.html#params)
* **Test dependencies** with `@depends` annotation. [Announcement](http://codeception.com/03-05-2016/codeception-2.2.-upcoming-features.html#test-dependencies)
* **Custom Commands** inject your own commands as as simple as extension. [Announcement](http://codeception.com/03-10-2016/even-more-features-of-codeception.html#custom-commands)
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
* [WebDriver] allows getting current browser and capabilities in test. [Announcement](http://codeception.com/03-10-2016/even-more-features-of-codeception.html#Getting-current-browser-and-capabilities-in-tests)
* [AngularJS] module added. Extends WebDriver module for AngularJS testing. [Announcement](http://codeception.com/03-10-2016/even-more-features-of-codeception.html#angularjs)
* [DataFactory] module added. Performs data generation using FactoryMuffin library [Announcement](http://codeception.com/03-10-2016/even-more-features-of-codeception.html#datafactory)
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

[seeResponseJsonMatchesJsonPath](http://codeception.com/docs/modules/REST#seeResponseJsonMatchesJsonPath) validates response JSON against [JsonPath](http://goessner.net/articles/JsonPath/).
Usage of JsonPath requires library `flow/jsonpath` to be installed.

[seeResponseJsonMatchesXpath](http://codeception.com/docs/modules/REST#seeResponseJsonMatchesXpath) validates response JSON against XPath.
It converts JSON structure into valid XML document and executes XPath for it.

[grabDataFromResponseByJsonPath](http://codeception.com/docs/modules/REST#grabDataFromResponseByJsonPath) method was added as well to grab data JSONPath.

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
* CodeCoverage remote context configuration added by @synchrone. See #1524 [Documentation updated](http://codeception.com/docs/11-Codecoverage#Remote-Context-Options)
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
