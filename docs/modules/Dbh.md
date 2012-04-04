# Dbh Module

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

### Configuration

* cleanup: true - enable cleanups by covering all queries inside transaction.


## Actions


### dontSeeInDatabase


Effect is opposite to ->seeInDatabase

Checks if there is no record with such column values in database.
Provide table name and column values.

Example:

``` php
<?php
$I->seeInDatabase('users', array('name' => 'Davert', 'email' => 'davert * mail.com'));

```
Will generate:

``` sql
SELECT COUNT(*) FROM `users` WHERE `name` = 'Davert' AND `email` = 'davert * mail.com'
```
Fails if such user was found.

 * param $table
 * param array $criteria


### seeInDatabase


Checks if a row with given column values exists.
Provide table name and column values.

Example:

``` php
<?php
$I->seeInDatabase('users', array('name' => 'Davert', 'email' => 'davert * mail.com'));

```
Will generate:

``` sql
SELECT COUNT(*) FROM `users` WHERE `name` = 'Davert' AND `email` = 'davert * mail.com'
```
Fails if no such user found.

 * param $table
 * param array $criteria
