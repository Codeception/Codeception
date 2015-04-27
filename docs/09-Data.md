# Working with Data

Tests should not affect each other. That's a rule of thumb. When tests interact with database, they may change data inside it, which would eventually lead to data inconsistency. A test may try to insert a record that has already been inserted, or retrieve a deleted record. To avoid test failures, the database should be brought to its initial state before each test. Codeception has different methods and approaches to get your data cleaned.

This chapter summarizes all of the notices on cleaning ups from the previous chapters and suggests the best strategies of how to choose data storage backends.

When we decide to clean up a database, we should make this cleaning as fast as possible. Tests should always run fast. Rebuilding the database from scratch is not the best way, but might be the only one. In any case, you should use a special test database for testing. **Do not ever run tests on development or production database!**

## Automatic Cleanup

Codeception has a `Db` module, which takes on most of the tasks of database interaction. By default it will try to repopulate the database from a dump and clean it up after each test. This module expects a database dump in SQL format. It's already prepared for configuration in `codeception.yml`:

```yaml
modules:
    config:
        Db:
            dsn: 'PDO DSN HERE'
            user: 'root'
            password:
            dump: tests/_data/your-dump-name.sql
```

After you enable this module in your test suite, it will automatically populate the database from a dump and repopulate it on each test run. These settings can be changed through the `populate` and `cleanup` options, which may be set to `false`.

The `Db` module is a rough tool. It works for any type of database supported by PDO. It could be used for all of the tests if it wasn't so slow. Loading a dump can take a lot of time that can be saved by using other techniques. When your test and application share the same database connection, as may be the case in functional and unit tests, the best way to speed up everything is to put all of the code in transaction, rolled back when test ends. In acceptance tests, different database connections are used, you can speed up tests by using SQLite file database. Alternatively, you can avoid recreating database on every test, but cleaning up all updates after the test.

## Separate connections

In acceptance tests, your test is interacting with the application through a web server. There is no way to receive a database connection from the web server. This means that the test and the application will work with the same database but on different connections. Provide in the Db module the same credentials that your application uses, and then you can access the database for assertions (`seeInDatabase` actions) and perform automatic cleanups.

## Shared connections

When an application or its parts are run within the Codeception process, you can use your application connection in your tests.
If you can access the connection, all database operations can be put into one global transaction and rolled back at the end. That will dramatically improve performance. Nothing will be written to the database at the end, thus no database repopulation is actually needed.

### ORM modules

If your application is using an ORM like Doctrine or Doctrine2, connect the respective modules to the suite. By default they will cover everything in a transaction. If you use several database connections, or there are transactions not tracked by the ORM, that module will be useless for you.

An ORM module can be connected with a `Db` module, but by default both will perform cleanup. Thus you should explicitly set which module is used:

In `tests/functional.suite.yml`:

```yaml
modules:
	  enabled: 
        - Db:
          cleanup: false      
        - Doctrine2:
            depends: Symfony2
        - \Helper\Functional
```

Still, the `Db` module will perform database population from a dump before each test. Use `populate: false` to disable it.

### Dbh module

If you use PostgreSQL, or any other database which supports nested transactions, you can use the `Dbh` module. It takes a PDO instance from your application, starts a transaction at the beginning of the tests, and rolls it back at the end.
A PDO connection can be set in the bootstrap file. This module also overrides the `seeInDatabase` and `dontSeeInDatabase` actions of the `Db` module.

To use the `Db` module for population and `Dbh` for cleanups, use this config:

```yaml
modules:
  	enabled: 
        - Db:
            cleanup: false
        - Dbh
        - \Helper\Functional
```

Please, note that `Dbh` module should go after the `Db`. That allows the `Dbh` module to override actions.

## Fixtures

Fixtures are sample data that we can use in tests. This data can be either generated, or taken from a sample database. Fixtures can be defined in separate PHP file and loaded in tests.

#### Fixtures for Acceptance and Functional Tests

Let's create `fixtures.php` file in `tests/functional` and load data from database to be used in tests.

```php
<?php
// let's take user from sample database,
// we can populate it with Db module
$john = User::findOneBy('name', 'john');
?>
```

Fixture usage in a sample acceptance or functional test:

```php
<?php
require 'fixtures.php';

$I = new FunctionalTester($scenario);
$I->amLoggedAs($john);
$I->see('Welcome, John');
?>
```

Also you can use the [Faker](https://github.com/fzaninotto/Faker) library to create test data within a bootstrap file.

### Per Test Fixtures

If you want to create special database record for one test, you can use [`haveInDatabase`](http://codeception.com/docs/modules/Db#haveInDatabase) method of `Db` module.

```php
<?php 
$I = new FunctionalTester($scenario);
$I->haveInDatabase('posts', [
  'title' => 'Top 10 Testing Frameworks', 
  'body' => '1. Codeception'
]);
$I->amOnPage('/posts');
$I->see('Top 10 Testing Frameworks');
?>
```

`haveInDatabase` inserts a row with provided values into database. All added records will be deleted in the end of a test. In `MongoDB` module we have similar [`haveInCollection`](http://codeception.com/docs/modules/MongoDb#haveInCollection) method.

## Conclusion

Codeception is not abandoning the developer when dealing with data. Tools for database population and cleanups are bundled within the `Db` module. To manipulate sample data in a test, use fixtures that can be defined within the bootstrap file.
