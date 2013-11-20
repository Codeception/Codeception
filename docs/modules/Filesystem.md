# Filesystem Module
**For additional reference, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/Filesystem.php)**


Module for testing local filesystem.
Fork it to extend the module for FTP, Amazon S3, others.

## Status

* Maintainer: **davert**
* Stability: **stable**
* Contact: codecept@davert.mail.ua

Module was developed to test Codeception itself.

## Actions


### amInPath


Enters a directory In local filesystem.
Project root directory is used by default

 * param $path


### cleanDir


Erases directory contents

``` php
<?php
$I->cleanDir('logs');
?>
```

 * param $dirname


### copyDir


Copies directory with all contents

``` php
<?php
$I->copyDir('vendor','old_vendor');
?>
```

 * param $src
 * param $dst


### deleteDir


Deletes directory with all subdirectories

``` php
<?php
$I->deleteDir('vendor');
?>
```

 * param $dirname


### deleteFile


Deletes a file

``` php
<?php
$I->deleteFile('composer.lock');
?>
```

 * param $filename


### deleteThisFile


Deletes a file


### dontSeeInThisFile


Checks If opened file doesn't contain `text` in it

``` php
<?php
$I->openFile('composer.json');
$I->dontSeeInThisFile('codeception/codeception');
?>
```

 * param $text


### getName

__not documented__


### openFile


Opens a file and stores it's content.

Usage:

``` php
<?php
$I->openFile('composer.json');
$I->seeInThisFile('codeception/codeception');
?>
```

 * param $filename


### seeFileContentsEqual


Checks the strict matching of file contents.
Unlike `seeInThisFile` will fail if file has something more then expected lines.
Better to use with HEREDOC strings.
Matching is done after removing "\r" chars from file content.

``` php
<?php
$I->openFile('process.pid');
$I->seeFileContentsEqual('3192');
?>
```

 * param $text


### seeFileFound


Checks if file exists in path.
Opens a file when it's exists

``` php
<?php
$I->seeFileFound('UserModel.php','app/models');
?>
```

 * param $filename
 * param string $path


### seeInThisFile


Checks If opened file has `text` in it.

Usage:

``` php
<?php
$I->openFile('composer.json');
$I->seeInThisFile('codeception/codeception');
?>
```

 * param $text
