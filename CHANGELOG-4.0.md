#### 4.0.3

* Fixed command autocompletion #5806 by @svycka

#### 4.0.2

* Fixed errors in bootstrap scripts #5806

#### 4.0.1

* Fixed error reporting error in upgrade4 script
* Symfony 5 compatibility: Improved detection of event-dispatcher version

#### 4.0.0

* Extracted modules from Codeception core to separate repository
* Separated building of phar files and documentation from Codeception core.
* Implemented upgrade script
* Support for Symfony 5
* Support for phpdotenv v4 by @sunspikes
* New Feature: Ability to stash/unstash commands in interactive mode by @pohnean
* [Fixtures] Cleanup by name @soupli
* GroupManager throws exception if path used in group configuration does not exist.
* GroupManager supports absolute and backtracking (..) paths in group files.
