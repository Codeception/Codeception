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
* [Interactive pause](https://codeception.com/docs/02-GettingStarted#Interactive-Pause) inside tests with `$I->pause();` command in debug mode added. Allows to write and debug test in realtime.
* Introduced [Step Decorators](https://codeception.com/docs/08-Customization#Step-Decorators) - auto-generated actions around module and helper methods. As part of this feature implemented
  * [Conditional Assertions](https://codeception.com/docs/03-AcceptanceTests#Conditional-Assertions) (`$I->canSee()`)
  * [Retries](https://codeception.com/docs/03-AcceptanceTests#Retry) (`$I->retryClick()`); 
  * [Silent Actions](https://codeception.com/docs/03-AcceptanceTests#A-B-Testing)(`$I->tryToClick`).
* Print artifacts on test failure
* [REST] Short API responses in debug mode with `shortDebugResponse` config option. See #5455 by @sebastianneubert 
* [WebDriver] `switchToIFrame` allow to locate iframe by CSS/XPath.
* [PhpBrowser][Frameworks] clickButton throws exception if button is outside form by @Naktibalda.
* Updated to PHP 7.3 in Docker container by @OneEyedSpaceFish
* Recorder Extension: Added timestamp information with `include_microseconds` config option. By @OneEyedSpaceFish.
* [REST] Fixed sending request with duplicated slash with endpoint + URL. By @nicholascus 
* [Db] Remove generateWhereClause method from SqlSrv to be compatible with other drivers. By @Naktibalda
