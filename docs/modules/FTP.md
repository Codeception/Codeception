# FTP/SFTP Module

**For additional reference, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/FTP.php)**

Works with SFTP/FTP servers.

In order to test the contents of a specific file stored on any remote FTP/SFTP system
this module downloads a temporary file to the local system. The temporary directory is
defined by default as ```tests/_data``` to specify a different directory set the tmp config
option to your chosen path.
Don't forget to create the folder and ensure its writable.

Supported and tested FTP types are:

* FTP
* SFTP

Connection uses php build in FTP client for FTP, connection to SFTP uses [phpseclib](http://phpseclib.sourceforge.net/) pulled in using composer.

## Status

* Maintainer: **[nathanmac](http://github.com/nathanmac)**
* stability:
    - FTP: **stable**
    - SFTP: **stable**
* Contact: Nathan Macnamara <nathan.macnamara@outlook.com>

## Config

* type: ftp - type of connection ftp/sftp (defaults to ftp).
* host *required* - hostname/ip address of the ftp server.
* port: 21 - port number for the ftp server
* timeout: 90 - timeout settings for connecting the ftp server.
* user: anonymous - user to access ftp server, defaults to anonymous authentication.
* password - password, defaults to empty for anonymous.
* tmp - path to local directory for storing tmp files.
* passive: true - Turns on or off passive mode (FTP only)
* cleanup: true - remove tmp files from local directory on completion.

### Examples
#### Example (FTP)
    modules:
       enabled: [FTP]
       config:
          FTP:
             type: ftp
             host: '127.0.0.1'
             port: 21
             timeout: 120
             user: 'root'
             password: 'root'
             dump: 'tests/_data/ftp'
             passive: true
             cleanup: false

#### Example (SFTP)
    modules:
       enabled: [FTP]
       config:
          FTP:
             type: sftp
             host: '127.0.0.1'
             port: 22
             timeout: 120
             user: 'root'
             password: 'root'
             dump: 'tests/_data/ftp'
             cleanup: false

## Usage

### grabFileList

Grabber method for returning file/folders listing in an array

```php
<?php
$files = $I->grabFileList();
$count = $I->grabFileCount('TEST', false); // Include . .. .thumbs.db
?>
```

 * `param` $path
 * `param` $ignore (bool) - suppress '.', '..' and '.thumbs.db'

### grabFileCount

Grabber method for returning file/folders count in directory

```php
<?php
$count = $I->grabFileCount();
$count = $I->grabFileCount('TEST', false); // Include . .. .thumbs.db
?>
```

 * `param` $path
 * `param` $ignore (bool) - suppress '.', '..' and '.thumbs.db'

### grabFileSize

Grabber method to return file size

```php
<?php
$size = $I->grabFileSize('test.txt');
?>
```

 * `param` $filename

### grabFileModified

Grabber method to return last modified timestamp

```php
<?php
$time = $I->grabFileModified('test.txt');
?>
```

 * `param` $filename

### grabDirectory

Grabber method to return current working directory

```php
<?php
$pwd = $I->grabDirectory();
?>
```

### loginAs

Change the logged in user mid-way through your test, this closes the
current connection to the server and initialises and new connection.

On initiation of this modules you are automatically logged into
the server using the specified config options or defaulted
to anonymous user if not provided.

``` php
<?php
$I->loginAs('user','password');
?>
```

 * `param` $user
 * `param` $password

### amInPath

Enters a directory on the remote FTP/SFTP system.
Server root directory is used by default

 * `param`  $path

### writeToFile

Saves contents to tmp file and uploads the FTP/SFTP system.
Overwrites current file on server if exists.

``` php
<?php
$I->writeToFile('composer.json', 'some data here');
?>
```

 * `param`  $filename
 * `param`  $contents

### openFile
 
Opens a file (downloads from the remote FTP/SFTP system to a tmp directory for processsing) and stores it's content.

Usage:

``` php
<?php
$I->openFile('composer.json');
$I->seeInThisFile('codeception/codeception');
?>
```

### deleteFile

Deletes a file on the remote FTP/SFTP system

``` php
<?php
$I->deleteFile('composer.lock');
?>
```

 * `param`  $filename


### deleteThisFile

Deletes the currently opened file, on the remote FTP/SFTP system

``` php
<?php
$I->openFile('composer.lock');
$I->deleteThisFile();
?>
```

 * `param`  $filename

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

 * `param`  $text

### seeFileFound
 
Checks if file exists in path on the remote FTP/SFTP system.
DOES NOT OPEN the file when it's exists

``` php
<?php
$I->seeFileFound('UserModel.php','app/models');
?>
```

 * `param`  $filename
 * `param`  string $path

### seeFileFoundMatches

Checks if file exists in path on the remote FTP/SFTP system, using regular expression as filename.
DOES NOT OPEN the file when it's exists

```php
<?php
$I->seeFileFoundMatches('/^UserModel_([0-9]{6}).php$/','app/models');
?>
```

 * `param`  $filename - Regular Expression
 * `param`  string $path

### seeInThisFile
 
Checks If opened file has `text` in it.

Usage:

``` php
<?php
$I->openFile('composer.json');
$I->seeInThisFile('codeception/codeception');
?>
```

 * `param`  $text

### dontSeeFileFound

Checks if file does not exists in path on the remote FTP/SFTP system

 * `param`  $filename
 * `param`  string $path

### seeFileFoundMatches

Checks if file does not exists in path on the remote FTP/SFTP system, using regular expression as filename.
DOES NOT OPEN the file when it's exists

 * `param`  $filename - Regular Expression
 * `param`  string $path

### dontSeeInThisFile

Checks If opened file doesn't contain `text` in it

``` php
<?php
$I->openFile('composer.json');
$I->dontSeeInThisFile('codeception/codeception');
?>
```

 * `param`  $text

### cleanDir

Erases directory contents on the remote FTP/SFTP system

``` php
<?php
$I->cleanDir('logs');
?>
```

 * `param`  $dirname

### deleteDir

Deletes directory with all subdirectories on the remote FTP/SFTP system

``` php
<?php
$I->deleteDir('vendor');
?>
```

 * `param`  $dirname

### copyDir (Not Supported)

Inherited function from Filesystem module, currently not supported in this module