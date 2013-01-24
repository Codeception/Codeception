# Db Module
**For additional reference,, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/Db)**
Works with SQL dabatase.

The most important function of this module is cleaning database before each test.
That's why this module was added into global configuration file: codeception.yml.
To have your database properly cleaned you should configure it to access the database.

In order to have your database populated with data you need a raw SQL dump.
Just put it in ``` tests/_data ``` dir (by default) and specify path to it in config.
Next time after database is cleared all your data will be restored from dump.
Don't forget to include CREATE TABLE statements into it.

Performance may dramatically change when using SQLite file database storage.
Consider converting your database into SQLite3 format with one of [provided tools](http://www.sqlite.org/cvstrac/wiki?p=ConverterTools).
While using SQLite database not recreated from SQL dump, but a database file is copied itself. So database repopulation is just about copying file.

Supported and tested databases are:

* MySQL
* SQLite (only file)
* PostgreSQL

Supported but not tested.

* MSSQL
* Orcale

Connection is done by database Drivers, which are stored in Codeception\Util\Driver namespace.
Check out drivers if you get problems loading dumps and cleaning databases.

## Status

* Maintainer: **davert**
* stability:
    - Mysql: stable
    - SQLite: stable
    - Postgres: beta
    - MSSQL: alpha
    - Oracle: alpha
* Contact: codecept@davert.mail.ua

*Please review the code of non-stable modules and provide patches if you have issues.*

## Config

* dsn *required* - PDO DSN
* user *required* - user to access database
* password *required* - password
* dump - path to database dump.
* populate: true - should the dump be loaded before test suite is started.
* cleanup: true - should the dump be reloaded after each test

Also provides actions to perform checks in database.

## Public Properties
* dbh - contains PDO connection.
* driver - contains Connection Driver. See [list all available drivers](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Util/Driver)


## Actions


### dontSeeInDatabase


Effect is opposite to ->seeInDatabase

Checks if there is no record with such column values in database.
Provide table name and column values.

Example:

``` php
<?php
$I->seeInDatabase('users', array('name' => 'Davert', 'email' => 'davert@mail.com'));

```
Will generate:

``` sql
SELECT COUNT(*) FROM `users` WHERE `name` = 'Davert' AND `email` = 'davert@mail.com'
```
Fails if such user was found.

 * param $table
 * param array $criteria


### grabFromDatabase


Fetches a single column value from a database.
Provide table name, desired column and criteria.

Example:

``` php
<?php
$mail = $I->grabFromDatabase('users', 'email', array('name' => 'Davert'));

```

 * version 1.1
 * param $table
 * param $column
 * param array $criteria
 * return mixed


### seeInDatabase


Checks if a row with given column values exists.
Provide table name and column values.

Example:

``` php
<?php
$I->seeInDatabase('users', array('name' => 'Davert', 'email' => 'davert@mail.com'));

```
Will generate:

``` sql
SELECT COUNT(*) FROM `users` WHERE `name` = 'Davert' AND `email` = 'davert@mail.com'
```
Fails if no such user found.

 * param $table
 * param array $criteria
