---
layout: doc
title: Codeception - Documentation
---

# Cli Module
**For additional reference, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/Cli.php)**


Wrapper for basic shell commands and shell output

### Responsibility
* Maintainer: **davert**
* Status: **stable**
* Contact: codecept@davert.mail.ua

*Please review the code of non-stable modules and provide patches if you have issues.*

### Actions


#### dontSeeInShellOutput


Checks that output from latest command doesn't contain text

 * param $text



#### runShellCommmand


Executes a shell command

 * param $command


#### seeInShellOutput


Checks that output from last executed command contains text

 * param $text
