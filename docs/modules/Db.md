# Db


Works with SQL database.

The most important function of this module is to clean a database before each test.
That's why this module was added to the global configuration file `codeception.yml`.
To have your database properly cleaned you should configure it to access the database.
This module also provides actions to perform checks in a database.

In order to have your database populated with data you need a raw SQL dump.
Simply put the dump in the `tests/_data` directory (by default) and specify the path in the config.
The next time after the database is cleared, all your data will be restored from the dump.
Don't forget to include `CREATE TABLE` statements in the dump.

Supported and tested databases are:

* MySQL
* SQLite (only file)
* PostgreSQL

Supported but not tested.

* MSSQL
* Oracle

Connection is done by database Drivers, which are stored in the `Codeception\Lib\Driver` namespace.
[Check out the drivers](https://github.com/Codeception/Codeception/tree/2.1/src/Codeception/Lib/Driver)
if you run into problems loading dumps and cleaning databases.

## Status

* Maintainer: **Gintautas Miselis**
* stability:
    - Mysql: **stable**
    - SQLite: **stable**
    - Postgres: **beta**
    - MSSQL: **alpha**
    - Oracle: **alpha**

*Please review the code of non-stable modules and provide patches if you have issues.*

## Config

* dsn *required* - PDO DSN
* user *required* - user to access database
* password *required* - password
* dump - path to database dump
* populate: true - whether the the dump should be loaded before the test suite is started
* cleanup: true - whether the dump should be reloaded after each test
* reconnect: false - whether the module should reconnect to the database before each test

## Example

    modules:
       enabled:
          - Db:
             dsn: 'mysql:host=localhost;dbname=testdb'
             user: 'root'
             password: ''
             dump: 'tests/_data/dump.sql'
             populate: true
             cleanup: false
             reconnect: true

## SQL data dump

 * Comments are permitted.
 * The `dump.sql` may contain multiline statements.
 * The delimiter, a semi-colon in this case, must be on the same line as the last statement:

```sql
-- Add a few contacts to the table.
REPLACE INTO `Contacts` (`created`, `modified`, `status`, `contact`, `first`, `last`) VALUES
(NOW(), NOW(), 1, 'Bob Ross', 'Bob', 'Ross'),
(NOW(), NOW(), 1, 'Fred Flintstone', 'Fred', 'Flintstone');

-- Remove existing orders for testing.
DELETE FROM `Order`;
```
## Query generation

seeInDatabase, dontSeeInDatabase, seeNumRecords, grabFromDatabase and grabNumRecords methods
accept arrays as criteria. WHERE condition is generated using item key as a field name and
item value as a field value.

Example:
```php
<?php
$I->seeInDatabase('users', array('name' => 'Davert', 'email' => 'davert@mail.com'));

```
Will generate:

```sql
SELECT COUNT(*) FROM `users` WHERE `name` = 'Davert' AND `email` = 'davert@mail.com'
```
New addition to 2.1.9 is ability to use LIKE in condition. It is achieved by adding ' like' to column name.

Example:
```php
<?php
$I->seeInDatabase('users', array('name' => 'Davert', 'email like' => 'davert%'));

```
Will generate:

```sql
SELECT COUNT(*) FROM `users` WHERE `name` = 'Davert' AND `email` LIKE 'davert%'
```
## Public Properties
* dbh - contains the PDO connection
* driver - contains the Connection Driver



## Actions

### dontSeeInDatabase
 
Effect is opposite to ->seeInDatabase

Asserts that there is no record with the given column values in a database.
Provide table name and column values.

``` php
<?php
$I->dontSeeInDatabase('users', array('name' => 'Davert', 'email' => 'davert * `mail.com'));` 
```
Fails if such user was found.

 * `param string` $table
 * `param array` $criteria


### grabFromDatabase
 
Fetches a single column value from a database.
Provide table name, desired column and criteria.

``` php
<?php
$mail = $I->grabFromDatabase('users', 'email', array('name' => 'Davert'));
```

 * `param string` $table
 * `param string` $column
 * `param array` $criteria



### grabNumRecords
 
Returns the number of rows in a database

 * `param string` $table    Table name
 * `param array`  $criteria Search criteria [Optional]

 * `return` int


### haveInDatabase
 
Inserts an SQL record into a database. This record will be erased after the test.

```php
<?php
$I->haveInDatabase('users', array('name' => 'miles', 'email' => 'miles * `davis.com'));` 
?>
```

 * `param string` $table
 * `param array` $data

 * `return integer` $id


### seeInDatabase
 
Asserts that a row with the given column values exists.
Provide table name and column values.

``` php
<?php
$I->seeInDatabase('users', array('name' => 'Davert', 'email' => 'davert * `mail.com'));` 
```
Fails if no such user found.

 * `param string` $table
 * `param array` $criteria


### seeNumRecords
 
Asserts that the given number of records were found in the database.

```php
<?php
$I->seeNumRecords(1, 'users', ['name' => 'davert'])
?>
```

 * `param int` $expectedNumber Expected number
 * `param string` $table Table name
 * `param array` $criteria Search criteria [Optional]

<p>&nbsp;</p><div class="alert alert-warning">Module reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/tree/2.2/src/Codeception/Module/Db.php">Help us to improve documentation. Edit module reference</a></div>
