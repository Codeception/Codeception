#### 3.1.2

* [Doctrine2] Support non-entity doctrine @id on the haveInRepository debug message by @Basster 
* [Frameworks][PhpBrowser] Improved detection of content-type for .fail files by @jasny
* [Frameworks][PhpBrowser] Add HTML snapshots of failing tests to HTML Report by @reinholdfuereder
* [Symfony] Fixed runConsoleCommand by @wirwolf
* [Symfony] grabService uses the special test service container if available by @burned42
* [Webdriver] Display cookie details in debug output by @Mitrichius
* [WebDriver] Improved error text of WaitForJS method by @eriksape
* Code coverage does not fail when excluded directory not exists by @Mitrichius
* Use path resolver for bootstrap file detection by @laoneo
* [Docs] Documentation updates by @burned42, @kishorehariram, @Mitrichius, @ruudboon, @sva1sva

#### 3.1.1

* Preparation for Symfony 5, removes deprecation message when Symfony 4.3 components are used. See #5670 by @Naktibalda
* [Db] Support initial queries execution after creating connection. See #5660 by @tadasauciunas 

```yml
Db:
    dsn: # dsn goes hre
    initial_queries:
        - 'CREATE DATABASE IF NOT EXISTS temp_db;'
        - 'USE temp_db;'
        - 'SET NAMES utf8;'
```

* Do not fail steps for `retry` and `tryTo` step decorators. Fixes #5666 by @Mitrichius 
* [Symfony] Added `runSymfonyConsoleCommand` by @wirwolf 

```php
$result = $I->runSymfonyConsoleCommand('hello:world', '--verbose' => 3]);
```
* [Doctrine2] Bugfix: calling `haveInRepository` with preconstructed entity requires providing constructor parameters. See #5680 by @burned42 
* [Doctrine2] Make debug message in `haveInRepository` to support entities with composite keys of entities in bidirectional relations. See #5685 by Basster. Fixes #5663. 
* Adds possibility to use absolute path for `groups` files in `codeception.yml`. #5674 by @maks-rafalko 
* Fixes the issue with paths in `groups` section when `codeception.yml` is not in the root of the project. #5674) by maks-rafalko.
* [Asserts] `expectException` deprecated in favor of `expectThrowable`.

#### 3.1.0

* Unified bootstrap file loading
* Deprecated configuring bootstrap for all suites implicitly
* Added `--bootstrap` option to `run`
* [Asserts] Added specialized assertEquals methods
* [Doctrine2] Added support for Expression and Criteria objects in queries by @alexkunin
* [Doctrine2] Added fixture support by @alexkunin
* [Doctrine2] added refreshEntities and clearEntityManager methods by @alexkunin
* [Doctrine2] implement recursive entity creation  by @alexkunin
* [Doctrine2] properly handle non-typical primary keys in haveInRepository by @alexkunin
* [Doctrine2] merge functionality of persistEntity into haveInRepository by @alexkunin
* [Doctrine2] deprecated persistEntity method by @alexkunin
* [Doctrine2] Make haveInRepository support constructor parameters by @burned42
* [Symfony] Fixed symfony 4.3 deprecation message removal by @juntereiner
* [Yii2] Allow to preserve session with recreateApplication enabled by @Slamdunk
* [Docs] Documentation updates by @Nebulosar and @mikehaertl
* [Docker] Changed base image to 7.3-cli by @OneEyedSpaceFish
* Robo build:phar command can be used for building custom phar files again
* Improved file path detection for groups #5609 by @adaniloff
* Shortened error output of unexpected exit to one line by @Slamdunk
* Fixed composer.json of codeception/base package
