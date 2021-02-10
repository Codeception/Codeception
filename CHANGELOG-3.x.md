#### 3.1.2

* [Doctrine2] Support non-entity doctrine @id on the haveInRepository debug message by @Basster
* [Frameworks][PhpBrowser] Improved detection of content-type for .fail files by @jasny
* [Frameworks][PhpBrowser] Add HTML snapshots of failing tests to HTML Report by @reinholdfuereder
* [Symfony] Fixed runConsoleCommand by @wirwolf
* [Symfony] grabService uses the special test service container if available by @burned42
* [Webdriver] Display cookie details in debug output by @Mitrichius
* [WebDriver] Improved error text of WaitForJS method by @eriksape
* Code coverage does not fail when excluded directory not exists by @Mitrichius
* Use path resolver for bootstrap file detection by @laoneo
* [Docs] Documentation updates by @burned42, @kishorehariram, @Mitrichius, @ruudboon, @sva1sva

#### 3.1.1

* Preparation for Symfony 5, removes deprecation message when Symfony 4.3 components are used. See #5670 by @Naktibalda
* [Db] Support initial queries execution after creating connection. See #5660 by @tadasauciunas

```yml
Db:
    dsn: # dsn goes hre
    initial_queries:
        - 'CREATE DATABASE IF NOT EXISTS temp_db;'
        - 'USE temp_db;'
        - 'SET NAMES utf8;'
```

* Do not fail steps for `retry` and `tryTo` step decorators. Fixes #5666 by @Mitrichius
* [Symfony] Added `runSymfonyConsoleCommand` by @wirwolf

```php
$result = $I->runSymfonyConsoleCommand('hello:world', '--verbose' => 3]);
```
* [Doctrine2] Bugfix: calling `haveInRepository` with preconstructed entity requires providing constructor parameters. See #5680 by @burned42
* [Doctrine2] Make debug message in `haveInRepository` to support entities with composite keys of entities in bidirectional relations. See #5685 by Basster. Fixes #5663.
* Adds possibility to use absolute path for `groups` files in `codeception.yml`. #5674 by @maks-rafalko
* Fixes the issue with paths in `groups` section when `codeception.yml` is not in the root of the project. #5674) by maks-rafalko.
* [Asserts] `expectException` deprecated in favor of `expectThrowable`.

#### 3.1.0

* Unified bootstrap file loading
* Deprecated configuring bootstrap for all suites implicitly
* Added `--bootstrap` option to `run`
* [Asserts] Added specialized assertEquals methods
* [Doctrine2] Added support for Expression and Criteria objects in queries by @alexkunin
* [Doctrine2] Added fixture support by @alexkunin
* [Doctrine2] added refreshEntities and clearEntityManager methods by @alexkunin
* [Doctrine2] implement recursive entity creation  by @alexkunin
* [Doctrine2] properly handle non-typical primary keys in haveInRepository by @alexkunin
* [Doctrine2] merge functionality of persistEntity into haveInRepository by @alexkunin
* [Doctrine2] deprecated persistEntity method by @alexkunin
* [Doctrine2] Make haveInRepository support constructor parameters by @burned42
* [Symfony] Fixed symfony 4.3 deprecation message removal by @juntereiner
* [Yii2] Allow to preserve session with recreateApplication enabled by @Slamdunk
* [Docs] Documentation updates by @Nebulosar and @mikehaertl
* [Docker] Changed base image to 7.3-cli by @OneEyedSpaceFish
* Robo build:phar command can be used for building custom phar files again
* Improved file path detection for groups #5609 by @adaniloff
* Shortened error output of unexpected exit to one line by @Slamdunk
* Fixed composer.json of codeception/base package

#### 3.0.3
- [Laravel5] Add `make` and `makeMultiple` methods for generating model instance by @ibpavlov
- [Lumen] Add `make` and `makeMultiple` methods for generating model instance by @ibpavlov
- [Phalcon] ActiveRecord: escape all column names using [] by @maxgalbu
- [Yii2] Fixed issue on PHP7.3 because `preg_quote` now also quotes `#` by @SamMousa
- [ZF2] Persistent service functionality for ZF3 by @svycka
- [ZF2] Doctrine entity manager name is configurable by @svycka
- [Db] Fix sqlite connection close when holding reference in PHP's GC (#5557) by @hoogi91
- [Doctrine2] Fixed handling of embedables, inherited entities and parameter name clashes by @alexkunin
- [Frameworks][PhpBrowser] Fixed compatibility with symfony/browserkit 4.3 by @kapcus
- [Docs] Small documentation updates by @Nebulosar, @reinholdfuereder and @richardbrinkman
- [Docker] Switched to buster php build by @OneEyedSpaceFish

#### 3.0.2
* @weshooper reduced size of exported package.
* --no-redirect option disables the redirect to a Composer-installed version, by @DanielRuf
* PhpUnit 8.2 support by @Naktibalda
* Retry: double wait interval after each execution by @Naktibalda
* [FTP] Bugfix: Make sure _directory will return its directory by @bbatsche
* [Doctrine2] Fixed recursive building of association queries by @alexkunin
* [PhpBrowser] Pass file type option to Guzzle if specified #5548 by @Naktibalda
* [PhpBrowser][Frameworks]  InnerBrowser: selectOption can match by text when option has no value attribute #5547 by @Naktibalda
* [REST] Updated url construction logic, so it does not produce double slashes, by @nicholascus
* [ZF2] Add check for console class before calling it by @carnage
* [Gherkin] Fixed Gherkin setup for single run from group file by @bnpatel1990
* [CodeCoverage] Ability to use a custom cookie domain for code coverage by @maksimovic
* [Docs] @EspadaV8 fixed env substitution in DB module example
* [Docs] @splinter89 mentioned phpdbg and pcov for code coverage
* @el7cosmos, @KartaviK and @davertMik fixed various deprecation messages

#### 3.0.1

* Fixed code duplication when building actors. Fixes #5506 #5500
* Fixed autoloader generation for 3.0 docker images by @OneEyedSpaceFish
* Removed `hoa/console` dependency from `codeception/base` package. 

#### 3.0.0

* **BREAKING** Modules removed:
     * Yii1
     * XMLRPC
     * AngularJS
     * Silex
     * Facebook
     * ZF1
* **POSSIBLE BREAKING** PHPUnit 8.x support. 
> Upgrade Notice: If you face issues with conflicting PHPUnit classes or difference in method signatures, lock version for PHPUnit in composer.json: “phpunit/phpunit”:”^7.0.0”
* **BREAKING** Multi-session testing disabled by default. Add `use \Codeception\Lib\Actor\Shared\Friend;` to enable `$I->haveFriend`.     
* **BREAKING** [WebDriver] `pauseExecution` removed in favor of `$I->pause()`
* [Interactive pause](https://codeception.com/docs/02-GettingStarted#Interactive-Pause) inside tests with `$I->pause()` command in debug mode added. Allows to write and debug test in realtime.
* Introduced [Step Decorators](https://codeception.com/docs/08-Customization#Step-Decorators) - auto-generated actions around module and helper methods. As part of this feature implemented:
  * [Conditional Assertions](https://codeception.com/docs/03-AcceptanceTests#Conditional-Assertions) (`$I->canSee()`)
  * [Retries](https://codeception.com/docs/03-AcceptanceTests#Retry) (`$I->retryClick()`)
  * [Silent Actions](https://codeception.com/docs/03-AcceptanceTests#AB-Testing) (`$I->tryToClick()`)
* Print artifacts on test failure
* [REST] Short API responses in debug mode with `shortDebugResponse` config option. See #5455 by @sebastianneubert 
* [WebDriver] `switchToIFrame` allow to locate iframe by CSS/XPath.
* [PhpBrowser][Frameworks] clickButton throws exception if button is outside form by @Naktibalda.
* Updated to PHP 7.3 in Docker container by @OneEyedSpaceFish
* Recorder Extension: Added timestamp information with `include_microseconds` config option. By @OneEyedSpaceFish.
* [REST] Fixed sending request with duplicated slash with endpoint + URL. By @nicholascus 
* [Db] Remove generateWhereClause method from SqlSrv to be compatible with other drivers. By @Naktibalda
