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


