
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
* Fixed output of failed step by @Naktibalda #4135 http://phptest.club/t/seeelement-wierd-fail-message/1470
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
* Added console completion by @gdscei. See [documentation](http://codeception.com/docs/07-AdvancedUsage#Shell-autocompletion)
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
* [WebDriver] Added `performOn` to wait for element, and run actions inside it. See [complete reference](http://codeception.com/docs/modules/WebDriver#performOn). #3986
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

* **Added dataprovider to Cest** format by @endo. See [updated documentation](http://codeception.com/docs/07-AdvancedUsage#Examples).
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
