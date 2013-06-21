# Advanced Usage

In this chapter we will cover some technics and options that you can use to improve your testing experience and stay with better organization of your project. 

## Interactive Console

Interactive console was added to try Codeception commands before executing them inside a test. 
This feature was introduced in 1.6.0 version. 

![console](http://img267.imageshack.us/img267/204/003nk.png)

You can execute console with 

``` bash
php codecept.phar console suitename
```

Now you can execute all commands of appropriate Guy class and see immidiate results. That is especially useful for when used with Selenium modules. It always takes too long to launch Selenium and browser for tests. But with console you can try different selectors, and different commands, and then write a test that would pass for sure when executed.

And a special hint: show your boss how you can nicely manipulate web pages with console and Selenium. With this you can convince him that it is easy to automate this steps and introduce acceptance testing to the project.

## Running from different folders.

If you have several project with Codeception tests in it you can use one `codecept.phar` file to run their tests.
You can pass a `-c` option to any Codeception command, excluding `bootstrap` to execute codeception in other directory.

```

php codecept.phar run -c ~/projects/ecommerce/
php codecept.phar run -c ~/projects/drupal/
php codecept.phar generate:cept acceptance CreateArticle -c ~/projects/drupal/

```

To create a project in directory other then current just provide it's path as a parameter.


```

php codecept.phar bootstrap ~/projects/drupal/


```

Basically `-c` option allows you specify not only the path but a config file to be used. Thus, you can have several `codeception.yml` file for your test suite. You may use it to specify different environments and settings. Just pass a filename into `-c` parameter to execute tests with specific config settings.

## Groups

There are several ways to execute bunch of tests. You can run tests from specific directory:

```

php codecept.phar run tests/acceptance/admin


```

Or execute one (or several) specific groups of tests:

```

php codecept.phar run -g admin -g editor

```

In this case all tests that belongs to groups admin or editor will be executed. Groups concept were taken from PHPUnit and in classical PHPUnit tests they behave just in the same way. To add Cept to the group - use `$scenario` variable:

``` php
<?php
$scenario->group('admin');
$scenario->group('editor');
// or
$scenario->group(array('admin', 'editor'))
// or
$scenario->groups(array('admin', 'editor'))

$I = new WebGuy($scenario);
$I->wantToTest('admin area');
?>
```
For Tests and Cests you can use annotation `@group` to add a test to the group.

``` php
<?php
/**
 * @group admin
 */
public function testAdminUser()
{
    $this->assertEquals('admin', User::find(1)->role);
}
?>
```
Same annotation can be used in Cest classes.

## Cest Classes

In case you want to get a class-like structure for your Cepts, instead of plain PHP, you can use Cest format.
It is very simple and is fully compatible with Cept scenarios. It means If you feel like your test is long enough and you want to split it - you can easily move it into class. 

You can start Cest file by running the command:

```
php codecept.phar generate:cest suitename CestName
```

The generated file will look like:

``` php
<?php
class BasicCest
{

    public function _before()
    {
    }

    public function _after()
    {
    }

    // tests
    public function tryToTest(\WebGuy $I) {
    
    }
}
?>
```

**Each public method of Cest (except, those starting from `_`) will be executed as a test** and will receive Guy class as the first parameter and `$scenario` variable as a second. 

In `_before` and `_after` method you can use common setups, teardowns for the tests in the class. That actually makes Cest tests more flexible, then Cepts that rely only on similar methods in Helper classes.

As you see we are passing Guy class into `tryToTest` stub. That allows us to write a scenarios the way we did before.

``` php
<?php
class BasicCest
{
    // test
    public function checkLogin(\WebGuy $I) {
        $I->wantTo('log in to site');
        $I->amOnPage('/');
        $I->click('Login');
        $I->fillField('username', 'jon');
        $I->fillField('password','coltrane');
        $I->click('Enter');
        $I->see('Hello, Jon');
        $I->seeInCurrentUrl('/account');
    }
}
?>
```

But there is a limitation in Cest files. It can't work with `_bootstrap.php` the way we did in scenario tests.
It was useful to store some variables in bootstraps that should be passed into scenario.
In Cest files you should inject all external variables manually, using static or global variables.

As a workaround you can choose [Fixtures](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Fixtures.php) class which is nothing more then global storage to your variables. You can pass variables from `_bootstrap.php` or any other place just with `Fixtures::add()` call. But probably you can use Cest classes `_before` and `_after` methods to load fixtures on the start of test, and deleting them afterwards. Pretty useful too.

As you see, Cest class have no parent like `\Codeception\TestCase\Test` or `PHPUnit_Framework_TestCase`. That was done intentionally. This allows you to extend class any time you wnat by attaching any meta-testing class to it's parent. In meta class you can write common behaviors and workarounds that may be used in child class. But don't forget to make them `protected` so they won't be executed as a tests themselves.

Also you can define `_failed` method in Cest class which will be called if test finished with `error` or fail.

## Refactoring

As test base growth they will require refactoring, sharing common variables and behaviors. The classical example for this is `login` action which will be called for maybe every test of your test suite. It's wise to make it written one time and use it in all tests. 

It's pretty obvious that for such cases you can use your own PHP classes to define such methods. 

``` php
<?php class TestCommons 
{
    public static $username = 'jon';
    public static $password = 'coltrane';

    public static logMeIn($I)
    {
        $I->amOnPage('/login');
        $I->fillField('username', 'jon');
        $I->fillField('password','coltrane');
        $I->click('Enter');
    }
}
?>
```

This file can be required in `_bootstrap.php` file

``` php
<?php
// bootstrap
require_once '/path/to/test/commons/TestCommons.php';
?>
```

and used in your scenarios:

``` php
<?php
$I = new WebGuy($scenario);
TestCommons::logMeIn($I);
?>
``` 

You should get the idea by now. Codeception doesn't provide any particular strategy for you to manage the tests. But it is flexible enough to create all the support classes you need for your test suites. The same way, with custom classes you can implement `PageObject` and `StepObject` patterns.

## PageObject and StepObjects

In next versions Codeception will provide PageObjects and StepObjects out of the box. 
But today we encourage you to try your own implementations. Whe have some nice blogpost for you to learn what are PageObjects, why you ever want to use them and how they can be implemented.

* [Ruling the Swarm (of Tests)](http://phpmaster.com/ruling-the-swarm-of-tests-with-codeception/) by Michael Bodnarchuk.
* [Implementing Page Objects in Codeception](http://jonstuff.blogspot.ca/2013/05/implementing-page-objects-in.html) by Jon Phipps.

## One Runner for Multiple Applications





## Conclusion

Codeception is a framework which may look simple at first sight. But it allows you to build powerful test with one  APIs, refactor them, and write them faster using interactive console. Codeception tests can easily be organized with groups or cest classes. Probably too much abilities for the one framework. But nevertheless Codeception follows the KISS pricinple: it's easy to start, easy to learn, easy to extend. 