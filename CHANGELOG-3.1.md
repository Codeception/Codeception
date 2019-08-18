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
