# Working with Data

Tests should not affect each other. That's a rule of thumb. When tests interact with database, they may change data inside it, which would eventually lead to data inconsistency. A test may try to insert a record that has already been inserted, or retrieve a deleted record. To avoid test failures, the database should be brought to its initial state before each test. Codeception has different methods and approaches to get your data cleaned.

This chapter summarizes all of the notices on cleaning ups from the previous chapters and suggests the best strategies of how to choose data storage backends.

When we decide to clean up a database, we should make this cleaning as fast as possible. Tests should always run fast. Rebuilding the database from scratch is not the best way, but might be the only one. In any case, you should use a special test database for testing. **Do not ever run tests on development or production database!**

## Db

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


<div class="alert alert-notice">
Use <a href="http://codeception.com/docs/06-ModulesAndHelpers#Dynamic-Configuration-With-Params">module params</a> to set database credentials from environment variables or from application configuration files.
</div>

After you enable this module in your test suite, it will automatically populate the database from a dump and repopulate it on each test run. These settings can be changed through the `populate` and `cleanup` options, which may be set to `false`.

<div class="alert alert-notice">
Full database cleanup can be painfully slow if you use large database dumps. It is recommended to do more data testing on functional and integration level, this way you can get performance bonuses from using ORM.
</div>

In acceptance tests your tests are interacting with the application through a web server.This means that the test and the application will work with the same database. You should provide in the Db module the same credentials that your application uses, and then you can access the database for assertions (`seeInDatabase` actions) and to perform automatic cleanups.

Db module provides actions to create and verify data inside a database.

If you want to create special database record for one test, you can use [`haveInDatabase`](http://codeception.com/docs/modules/Db#haveInDatabase) method of `Db` module.

```php
<?php 
$I->haveInDatabase('posts', [
  'title' => 'Top 10 Testing Frameworks', 
  'body' => '1. Codeception'
]);
$I->amOnPage('/posts');
$I->see('Top 10 Testing Frameworks');

```

`haveInDatabase` inserts a row with provided values into database. All added records will be deleted in the end of a test. 

If you want to check that a table record was created use [`seeInDatabase`](http://codeception.com/docs/modules/Db#haveInDatabase) method:

```php
<?php
$I->amOnPage('/posts/1');
$I->fillField('comment', 'This is nice!');
$I->click('Submit');
$I->seeInDatabase('comments', ['body' => 'This is nice!']);

```

Follow the module [reference](http://codeception.com/docs/modules/Db) for other methods you can use for database testing.

There are also modules for [MongoDb](http://codeception.com/docs/modules/MongoDb), [Redis](http://codeception.com/docs/modules/Redis), [Memcache](http://codeception.com/docs/modules/Memcache) which act in similar manner.

### Sequence

If database cleanup takes to long for you you can follow different strategy: create new data for each test. This way the only problem you may face is duplication of data records. [Sequence](http://codeception.com/docs/modules/Sequence) was created to solve this. It provides `sq()` function which generates unique suffixes for creating data in tests. 

## ORM modules

Probably your application is using ORM to work with database. In this case Codeception allows you to use methods of ORM to work with database, instead of accessing database directly. This way you can work with models and entities of a domain, and not on tables and rows.

By using ORM in functional and integration you can also improve performance of your tests. Instead of cleaning up database after each test ORM module will wrap all the database actions into transactions and rollback it in the end. This way no actual data will be written to the database. This cleanup strategy is enabled by default, you can disable it by setting `cleanup: false` in config of any ORM module.

### ActiveRecord

Popular frameworks like Laravel, Yii, and Phalcon include ActiveRecord data layer by default. Because of this tight integration you just need to enable framework module, and use its config for database access. 

Corresponding framework modules provide similar methods for ORM access:

* `haveRecord`
* `seeRecord`
* `dontSeeRecord`
* `grabRecord`

They allow to create and check data by model name and field names in model. Here is the example in Laravel

```php
<?php
// create record and get its id
$id = $I->haveRecord('posts', ['body' => 'My first blogpost', 'user_id' => 1]);
$I->amOnPage('/posts/'.$id);
$I->see('My first blogpost', 'article');
// check record exists
$I->seeRecord('posts', ['id' => $id]);
$I->click('Delete');
// record was deleted
$I->dontSeeRecord('posts', ['id' => $id]); 

```

<div class="alert alert-notice">
Laravel5 module also provides `haveModel`, `makeModel` methods which use factories to generate models with fake data.
</div>

In case you want to use ORM for integration testing only, you should enable framework module with only `ORM` part enabled:

```yaml
modules:
    enabled:
        - Laravel5:
            - part: ORM
```

```yaml
modules:
    enabled:
        - Yii2:
            - part: ORM
```

This way no web actions will be added to `$I` object. 

If you want to use ORM to work with data inside acceptance tests you should also include only ORM part of a module. Please note, that inside acceptance tests web application works inside a webserver so test data can't be cleaned up by rolling back transaction. So you will need to disable cleanup and use `Db` module to cleanup database betweem tests. Here is a sample config:

```yaml
modules:
    enabled:
        - WebDriver:
            url: http://localhost
            browser: firefox
        - Laravel5:
            cleanup: false
        - Db
```


### DataMapper

Doctrine is also a popular ORM, unlike other it implements DataMapper pattern and is not bound to any framework. [Doctrine2](http://codeception.com/docs/modules/Doctrine2) module requires `EntityManager` instance to work with. It can be obtained from a frameworks Symfony or Zend Framework (configured with Doctrine):

```yaml
modules:
    enabled:
        - Symfony
        - Doctrine2:
            depends: Symfony
```

```yaml
modules:
    enabled:
        - ZF2
        - Doctrine2:
            depends: ZF2
```


If no framework is used with Doctrine you should provide `connection_callback` option with a valid callback to function which returns `EntityManager` instance.

Doctrine2 also provides methods to create and check data:

* `haveInRepository`
* `grabFromRepository`
* `seeInRepository`
* `dontSeeInRepository`

### DataFactory

Preparing data for testing is a very creative nevertheless boring task. If you create a record you will need to fill in all fields of a model. It would be much easier to use [Faker](https://github.com/fzaninotto/Faker) for this task, and which is more effective to setup data generation rules for models. Such set of rules is called *factories* and are provided by [DataFactory](http://codeception.com/docs/modules/DataFactory) module.

Once configured it can create records with ease:

```php
<?php
// creates a new user
$user_id = $I->have('App\Model\User');
// creates 3 posts
$I->haveMultiple('App\Model\Post', 3);
```

Created records will be deleted at the end of a test. DataFactory works only with ORM, so require one of ORM modules to be enabled:

```yaml
modules:
    enabled:
        - Yii2:
            configFile: path/to/config.php
        - DataFactory:
            depends: Yii2
```

```yaml
modules:
    enabled:
        - Symfony
        - Doctrine2:
            depends: Symfony
        - DataFactory:
            depends: Doctrine2            
```

DataFactory provides a powerful solution for managing data in integration/functional/acceptance tests. Read the [full reference](http://codeception.com/docs/modules/DataFactory) to learn how to setup this module.

## Conclusion

Codeception is not leaving the developer when dealing with data. Tools for database population and cleanups are bundled within the `Db` module. If you use ORM you can use one of provided framework modules to operate with database through data abstraction layer, and use DataFactory module to generate new records with ease.
