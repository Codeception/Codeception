#### 3.0.3
- [Laravel5] Add `make` and `makeMultiple` methods for generating model instance
- [Lumen] Add `make` and `makeMultiple` methods for generating model instance

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
