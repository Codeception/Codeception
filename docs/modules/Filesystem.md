# Filesystem Module

**For additional reference, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/Filesystem.php)**
## Codeception\Module\Filesystem

* *Extends* `Codeception\Module`

Module for testing local filesystem.
Fork it to extend the module for FTP, Amazon S3, others.

## Status

* Maintainer: **davert**
* Stability: **stable**
* Contact: codecept@davert.mail.ua

Module was developed to test Codeception itself.



#### *public static* includeInheritedActionsBy setting it to false module wan't inherit methods of parent class.

 * `var`  bool
#### *public static* onlyActionsAllows to explicitly set what methods have this class.

 * `var`  array
#### *public static* excludeActionsAllows to explicitly exclude actions from module.

 * `var`  array
#### *public static* aliasesAllows to rename actions

 * `var`  array




### amInPath
#### *public* amInPath($path)Enters a directory In local filesystem.
Project root directory is used by default

 * `param`  $path

### openFile
#### *public* openFile($filename)Opens a file and stores it's content.

Usage:

``` php
<?php
$I->openFile('composer.json');
$I->seeInThisFile('codeception/codeception');
?>
```

 * `param`  $filename
### deleteFile
#### *public* deleteFile($filename)Deletes a file

``` php
<?php
$I->deleteFile('composer.lock');
?>
```

 * `param`  $filename
### deleteDir
#### *public* deleteDir($dirname)Deletes directory with all subdirectories

``` php
<?php
$I->deleteDir('vendor');
?>
```

 * `param`  $dirname
### copyDir
#### *public* copyDir($src, $dst)Copies directory with all contents

``` php
<?php
$I->copyDir('vendor','old_vendor');
?>
```

 * `param`  $src
 * `param`  $dst
### seeInThisFile
#### *public* seeInThisFile($text)Checks If opened file has `text` in it.

Usage:

``` php
<?php
$I->openFile('composer.json');
$I->seeInThisFile('codeception/codeception');
?>
```

 * `param`  $text
### seeFileContentsEqual
#### *public* seeFileContentsEqual($text)Checks the strict matching of file contents.
Unlike `seeInThisFile` will fail if file has something more then expected lines.
Better to use with HEREDOC strings.
Matching is done after removing "\r" chars from file content.

``` php
<?php
$I->openFile('process.pid');
$I->seeFileContentsEqual('3192');
?>
```

 * `param`  $text
### dontSeeInThisFile
#### *public* dontSeeInThisFile($text)Checks If opened file doesn't contain `text` in it

``` php
<?php
$I->openFile('composer.json');
$I->dontSeeInThisFile('codeception/codeception');
?>
```

 * `param`  $text
### deleteThisFile
#### *public* deleteThisFile()Deletes a file
### seeFileFound
#### *public* seeFileFound($filename, $path = null)Checks if file exists in path.
Opens a file when it's exists

``` php
<?php
$I->seeFileFound('UserModel.php','app/models');
?>
```

 * `param`  $filename
 * `param`  string $path
### dontSeeFileFound
#### *public* dontSeeFileFound($filename, $path = null)Checks if file does not exists in path

 * `param`  $filename
 * `param`  string $path
### cleanDir
#### *public* cleanDir($dirname)Erases directory contents

``` php
<?php
$I->cleanDir('logs');
?>
```

 * `param`  $dirname
### writeToFile
#### *public* writeToFile($filename, $contents)Saves contents to file

 * `param`  $filename
 * `param`  $contents






































