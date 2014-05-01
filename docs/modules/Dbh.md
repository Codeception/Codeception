# Dbh Module

**For additional reference, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/Dbh.php)**
## Codeception\Module\Dbh

* *Extends* `Codeception\Module`
* *Implements* `Codeception\Lib\Interfaces\Db`

This module replaces Db module for functional and unit testing, and requires PDO instance to be set.
Be default it will cover all database queries into transaction and rollback it afterwards.
The database should support nested transactions, in order to make cleanup work as expected.

Pass PDO instance to this module from within your bootstrap file.

In _bootstrap.php:

``` php
<?php
\Codeception\Module\Dbh::$dbh = $dbh;
?>
```

This will make all queries in this connection run withing transaction and rolled back afterwards.

Note, that you can't use this module with MySQL. Or perhaps you don't use transactions in your project, then it's ok.
Otherwise consider using ORMs like Doctrine, that emulate nested transactions, or switch to Db module.

## Status

* Maintainer: **davert**
* Stability: **stable**
* Contact: codecept@davert.mail.ua

This module despite of it's stability may act unstable because of transactions issue. If test fails with fatal error and transaction is not finished, it may affect other transactions.

*Please review the code of non-stable modules and provide patches if you have issues.*

### Configuration

* cleanup: true - enable cleanups by covering all queries inside transaction.

### Example

    modules: 
       enabled: [Dbh]
       config:
          Dbh:
             cleanup: false

#### *public static* dbh
#### *public static* includeInheritedActionsBy setting it to false module wan't inherit methods of parent class.

 * `var`  bool
#### *public static* onlyActionsAllows to explicitly set what methods have this class.

 * `var`  array
#### *public static* excludeActionsAllows to explicitly exclude actions from module.

 * `var`  array
#### *public static* aliasesAllows to rename actions

 * `var`  array





### seeInDatabase
#### *public* seeInDatabase($table, $criteria = null)Checks if a row with given column values exists.
Provide table name and column values.

Example:

``` php
<?php
$I->seeInDatabase('users', array('name' => 'Davert', 'email' => 'davert * `mail.com'));` 

```
Will generate:

``` sql
SELECT COUNT(*) FROM `users` WHERE `name` = 'Davert' AND `email` = 'davert * `mail.com'` 
```
Fails if no such user found.

 * `param`        $table
 * `param`  array $criteria
### dontSeeInDatabase
#### *public* dontSeeInDatabase($table, $criteria = null)Effect is opposite to ->seeInDatabase

Checks if there is no record with such column values in database.
Provide table name and column values.

Example:

``` php
<?php
$I->dontSeeInDatabase('users', array('name' => 'Davert', 'email' => 'davert * `mail.com'));` 

```
Will generate:

``` sql
SELECT COUNT(*) FROM `users` WHERE `name` = 'Davert' AND `email` = 'davert * `mail.com'` 
```
Fails if such user was found.

 * `param`        $table
 * `param`  array $criteria

### grabFromDatabase
#### *public* grabFromDatabase($table, $column, $criteria = null)Fetches a single column value from a database.
Provide table name, desired column and criteria.

Example:

``` php
<?php
$mail = $I->grabFromDatabase('users', 'email', array('name' => 'Davert'));

```

 * `version`  1.1

 * `param`        $table
 * `param`        $column
 * `param`  array $criteria

 * `return`  mixed





































