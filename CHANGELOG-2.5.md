#### 2.5.0

* [**Snapshot testing**](https://codeception.com/docs/09-Data#Testing-Dynamic-Data-with-Snapshots) introduced. Test dynamic data sets by comparing current values with previously saved ones.
* [Db] **Multi database support**. See #4857 by @eXorus
  * `amConnectedToDatabase` method added.
  * `performInDatabase` method added.
* Rerun tests in **[shuffle mode](https://codeception.com/docs/07-AdvancedUsage#Shuffle)** in the same order by setting seed value. By @SamMousa
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
