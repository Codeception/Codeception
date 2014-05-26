# Doctrine1 Module

**For additional reference, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/Doctrine1.php)**
## Codeception\Module\Doctrine1

* *Extends* `Codeception\Module`

Performs DB operations with Doctrine ORM 1.x

Uses active Doctrine connection. If none can be found will fail.

This module cleans all cached data after each test.

## Status

* Maintainer: **davert**
* Stability: **stable**
* Contact: codecept@davert.mail.ua

## Config
* cleanup: true - all doctrine queries will be run in transaction, which will be rolled back at the end of test.


#### *public static* includeInheritedActionsBy setting it to false module wan't inherit methods of parent class.

 * `var`  bool
#### *public static* onlyActionsAllows to explicitly set what methods have this class.

 * `var`  array
#### *public static* excludeActionsAllows to explicitly exclude actions from module.

 * `var`  array
#### *public static* aliasesAllows to rename actions

 * `var`  array






### seeInTable
#### *public* seeInTable($model, $values = null) Checks table contains row with specified values
Provide Doctrine model name can be passed to addWhere DQL

Example:

``` php
<?php
$I->seeInTable('User', array('name' => 'Davert', 'email' => 'davert * `mail.com'));` 

```

 * `param`  $model
 * `param`  array $values
### dontSeeInTable
#### *public* dontSeeInTable($model, $values = null) Checks table doesn't contain row with specified values
Provide Doctrine model name and criteria that can be passed to addWhere DQL

Example:

``` php
<?php
$I->dontSeeInTable('User', array('name' => 'Davert', 'email' => 'davert * `mail.com'));` 

```

 * `param`  $model
 * `param`  array $values
### grabFromTable
#### *public* grabFromTable($model, $column, $values = null) Fetches single value from a database.
Provide Doctrine model name, desired field, and criteria that can be passed to addWhere DQL

Example:

``` php
<?php
$mail = $I->grabFromTable('User', 'email', array('name' => 'Davert'));

```

 * `param`  $model
 * `param`  $column
 * `param`  array $values
 * `return`  mixed





































