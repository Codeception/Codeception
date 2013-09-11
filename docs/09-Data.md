# Working with Data

Tests should not affect each other. That's a rule. When tests interact with databases they may change data inside them. This leads to data inconsistency. A test may try to insert a record that is already inserted, or retrieve a deleted record. To avoid test failures, the database should be brought to it's initial state. Codeception has different methods and approaches to get your data cleaned.

This chapter summarizes all of the notices on clean ups from previous chapters and suggests the best strategies to choose data storage backends.

When we choose to clean up a database, we should make this cleaning as fast as possible. Tests should always run fast. Rebuilding the database from scratch is not the best way, but might be the only one. In any case, you should use a special test database for testing. Do not ever test on a development or production database!

## Automatic Cleanup

Codeception has a Db module, which takes on most of the tasks of database interaction. By default it will try to repopulate the database from a dump and clean it up after each test. This module expects a database dump in SQL format. It's already prepared for configuration in codeception.yml

```yaml
modules:
    config:
        Db:
            dsn: 'PDO DSN HERE'
            user: 'root'
            password:
            dump: tests/_data/your-dump-name.sql
```

After you enable this module in your test suite, it will automatically populate the database from a dump and repopulate it on each test run. These settings can be changed through the _populate_ and _cleanup_ options, which may be set to disabled.

The Db module is a rough tool. It works for any type of database supported by PDO. It could be used for all of the tests if it wasn't so slow. Loading a dump can take a lot of time that can be saved by using other techniques. When your test and application share the same connection, as may be the case in functional and unit tests, the best way to speed up everything is either to put all of the code in transactions or use SQLite Memory. When database interactions are performed through different connections, as we do in acceptance tests, the best solution we can propose is to use an SQLite file database, replacing it instead of rebuilding it after each test.

## Separate connections

In acceptance tests, your test is interacting with the application through a web server. There is no way to receive a database connection from the web server. This means that the test and the application will work with the same database but on different connections. Provide in the Db module the same credentials that your application uses, and then you can access the database for assertions (`seeInDatabase` actions) and perform automatic cleanups.

## Shared connections

When an application or it's parts are run within the Codeception process, you can use your application connection in your tests. 
If you can access the connection, all database operations can be put into one global transaction and rolled back at the end. That will dramatically improve performance. Nothing will be written to the database at the end, thus no database repopulation is actually needed.

### ORM modules

If your application is using an ORM like Doctrine or Doctrine2, connect the respective modules to the suite. By default they will cover everything in a transaction. If you use several database connections, or there are transactions not tracked by the ORM, that module will be useless for you.

An ORM module can be connected with a Db module, but by default both will perform cleanup. Thus you should explicitly set which module is used:

In __tests/functional.suite.yml__:

```yaml
modules:
	enabled: [Db, Doctrine1, TestHelper]
	config:
		Db:
			cleanup: false
```

Still, the Db module will perform database population from a dump before each test. Use `populate: false` to disable it.

### Dbh module

If you use PostgreSQL, or any other database which supports nested transactions, you can use the Dbh module. It takes a PDO instance from your application, starts a transaction at the beginning of the tests, and rolls it back at the end.
A PDO connection can be set in the bootstrap file. This module also overrides the `seeInDatabase` and `dontSeeInDatabase` actions of the Db module.

To use the Db module for population and Dbh for cleanups, use this config:

```yaml
modules:
	enabled: [Db, Dbh, TestHelper]
	config:
		Db:
			cleanup: false
```

Please, note that Dbh module should go after the Db. That allows the Dbh module to override actions.

## Fixtures

Fixtures are sample data that we can use in tests. This data can be either generated, or taken from a sample database. Fixtures should be set in the bootstrap file. Depending on the suite, fixture definitions may change. 

#### Fixtures for Acceptance and Functional Tests

Fixtures for acceptance and functional tests fixtures can be simply defined and used.

Fixture definition in __bootstrap.php_
```php
<?php
// let's take user from sample database. 
// we can populate it with Db module
$davert = Doctrine::getTable('User')->findOneBy('name', 'davert');

?>
```

Fixture usage in a sample acceptance or functional test.

```php
<?php
$I = new TestGuy($scenario);
$I->amLoggedAs($davert);
$I->see('Welcome, Davert');
?>
```

All variables from the bootstrap file are passed to the _Cept_ files of the testing suite. 

You can use the [Faker](https://github.com/fzaninotto/Faker) library to create tested data within a bootstrap file.

### Per Test Fixtures

* New in 1.6.2 *

If you want to create special database record for one test, you can use [`haveInDatabase`](http://codeception.com/docs/modules/Db#haveInDatabase) method of `Db` module.

``` php
<?php 
$I = new TestGuy($scenario);
$I->haveInDatabase('posts', array('title' => 'Top 10 Testing Frameworks', 'body' => '1. Codeception'));
$I->amOnPage('/posts');
$I->see('Top 10 Testing Frameworks');
?>
```

`haveInDatabase` method does nothing then inserts into database a row with provided values. A record that was added will be deleted in the end of a test. In `MongoDB` module we have similar [`haveInCollection`](http://codeception.com/docs/modules/MongoDb#haveInCollection) method.

## Conclusion

Codeception is not abandoning the developer when dealing with data. Tools for database population and cleanups are bundled within the Db module. To manipulate sample data in a test, use fixtures that can be defined within the bootstrap file.
