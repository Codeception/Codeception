# Modules

Codeception uses modularity to create comfortable testing environment for every test suite you write.
Modules allows you choose actions and assertions that can be perform in tests.

All actions and assertions, that can be performed by Guy object in class are defined in modules. It might look that Codeception limits you in testing, still it's not true. You can extend testing suite with your own actions and assertions, writing them into custom module.

Let's look at this test.

``` php
<?php
$I = new TestGuy($scenario);
$I->amOnPage('/');
$I->see('Hello');
$I->seeInDatabase('users', array('id' => 1));
$I->seeFileFound('running.lock');
```

It can operate with different entities: the web page can be loaded with Symfony1 module, the database assertion uses Db module, and file state can be checked with Filesystem module.

Modules are attached to Guy-classes in suite config.
For current example in 'tests/functional.suite.yml' we should see.

```
class_name: CodeGuy
modules:
    enabled: [Symfony1, Db, Filesystem]
```

The TestGuy class has it's methods defined in modules. Actually, it doesn't contain any of them, but acts as a proxy for them. It knows which module executes this action and passes parameters into it. To make your IDE see all methods of TestGuy listed, you use the 'build' command. It generates definition of TestGuy class by copying signatures from modules.

## Standard Modules

Codeception has many bundled modules which would help you run tests for different purposes and on different environments. The modules number is not constant - it's supposed to grow, as more frameworks, ORMs are supported.

Let's list all available modules

* Db - a most useful one. Can refresh your database after each run. Also can check data in database exists.
* PhpBrowser - emulates browser with [Goutte PHP Web Scraper](https://github.com/fabpot/Goutte), driven by [Mink](http://mink.behat.org). Commonly used for acceptance tests.
* Filesystem - simple module to perform checks in your filesystem.
* Doctrine1 - provides additional tools for projects powered with Doctrine ORM
* Doctrine2 - same as above, but for Doctrine2 ORM. Also has powerful tools to mock Doctrine internal objects.
* Symfony1 - connector to Symfony1 framework. Codeception scenarios are run in application's test environment.
* Unit - module for unit testing. Contains powerful methods for mocking objects, running test methods, asserting, etc.

All of this modules are documented. You can review their detailed reference on [GitHub](https://github.com/DavertMik/Codeception/tree/master/docs/modules).

## Custom modules


