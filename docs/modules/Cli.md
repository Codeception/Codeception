# Cli Module

**For additional reference, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/Cli.php)**
## Codeception\Module\Cli

* *Extends* `Codeception\Module`

Wrapper for basic shell commands and shell output

## Responsibility
* Maintainer: **davert**
* Status: **stable**
* Contact: codecept@davert.mail.ua

*Please review the code of non-stable modules and provide patches if you have issues.*
#### *public* output
#### *public static* includeInheritedActionsBy setting it to false module wan't inherit methods of parent class.

 * `var`  bool
#### *public static* onlyActionsAllows to explicitly set what methods have this class.

 * `var`  array
#### *public static* excludeActionsAllows to explicitly exclude actions from module.

 * `var`  array
#### *public static* aliasesAllows to rename actions

 * `var`  array




### runShellCommand
#### *public* runShellCommand($command)Executes a shell command

 * `param`  $command
### seeInShellOutput
#### *public* seeInShellOutput($text)Checks that output from last executed command contains text

 * `param`  $text
### dontSeeInShellOutput
#### *public* dontSeeInShellOutput($text)Checks that output from latest command doesn't contain text

 * `param`  $text
### seeShellOutputMatches
#### *public* seeShellOutputMatches($regex)






































