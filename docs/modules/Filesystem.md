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
$I->seeInThisFile('codeception/codeception');
?>
```

 * param $text


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
