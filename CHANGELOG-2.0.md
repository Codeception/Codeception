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
