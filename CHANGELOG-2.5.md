#### 2.5.4

* Restored compatility with vlucas/phpdotenv v2
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
