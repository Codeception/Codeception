


Works with SQL database.

The most important function of this module is cleaning database before each test.
That's why this module was added into global configuration file: codeception.yml.
To have your database properly cleaned you should configure it to access the database.
Also provides actions to perform checks in database.

In order to have your database populated with data you need a raw SQL dump.
Just put it in `tests/_data` dir (by default) and specify path to it in config.
Next time after database is cleared all your data will be restored from dump.
Don't forget to include CREATE TABLE statements into it.

Supported and tested databases are:

* MySQL
* SQLite (only file)
* PostgreSQL

Supported but not tested.

* MSSQL
* Oracle

Connection is done by database Drivers, which are stored in Codeception\Lib\Driver namespace.
Check out drivers if you get problems loading dumps and cleaning databases.

## Status

* Maintainer: **davert**
* stability:
    - Mysql: **stable**
    - SQLite: **stable**
    - Postgres: **beta**
    - MSSQL: **alpha**
    - Oracle: **alpha**
* Contact: codecept@davert.mail.ua

*Please review the code of non-stable modules and provide patches if you have issues.*

## Config

* dsn *required* - PDO DSN
* user *required* - user to access database
* password *required* - password
* dump - path to database dump.
* populate: true - should the dump be loaded before test suite is started.
* cleanup: true - should the dump be reloaded after each test
* reconnect: false - should the module reconnect to database before each test

### Example

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

### SQL data dump

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

## Public Properties
* dbh - contains PDO connection.
* driver - contains Connection Driver. See [list all available drivers](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Util/Driver)



### dontSeeInDatabase
 
Effect is opposite to ->seeInDatabase

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

 * `param`       $table
 * `param array` $criteria


### grabFromDatabase
 
Fetches a single column value from a database.
Provide table name, desired column and criteria.

Example:

``` php
<?php
$mail = $I->grabFromDatabase('users', 'email', array('name' => 'Davert'));

```

 * `Available since` 1.1

 * `param`       $table
 * `param`       $column
 * `param array` $criteria



### haveInDatabase
 
Inserts SQL record into database. This record will be erased after the test.

``` php
<?php
$I->haveInDatabase('users', array('name' => 'miles', 'email' => 'miles * `davis.com'));` 
?>
```

 * `param`       $table
 * `param array` $data

 * `return integer` $id


### seeInDatabase
 
Checks if a row with given column values exists.
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

 * `param`       $table
 * `param array` $criteria


### seeNumRecords
 
Asserts that found number of records in database

``` php
<?php
$I->seeNumRecords(1, 'users', ['name' => 'davert'])
?>
```

 * `param int`    $expectedNumber      Expected number
 * `param string` $table    Table name
 * `param array`  $criteria Search criteria [Optional]

<p>&nbsp;</p><div class="alert alert-warning">Module reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/tree/2.1/src/Codeception/Module/Db.php">Help us to improve documentation. Edit module reference</a></div>
