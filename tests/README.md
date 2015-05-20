# Codeception Internal Tests

In case you submit pull request you will be asking for writing a test.
But it's pretty hard to figure out where to start and how to make all tests pass.

There are 3 suites for testing

* cli - acceptance tests of CLI commands
* coverage - acceptance tests of code coverage
* unit - all unit/integration/etc tests.

## Unit

The most important tests in this suite are Module tests located in `test/unit/Codeception/Module`. Unlike you would expect most of tests there are integrational tests. For example, `WebDriverTest` require actual selenium server to be executed.

### Testing a Module

These are basic steps if you want to add a test for a module:

1. Find corresponding module test in `tests/unit/Codeception/Module`
2. Start web server or selenium server if needed
3. Write a test
4. Execute only that test. **Do not start all test suite**

Requirements:

* PhpBrowser - demo application running on web server
* WebDriver, Selenium2 - demo application on web server + selenium server
* Frameworks (general all-framework tests) - demo application
* MongoDb, AMQP, Facebook, etc - corresponding backends

### Demo Application

When module require a web server with demo application running. You can find this app in `tests/data/app`. To execute tests for **PhpBrowser**, **WebDriver** you need to start a web server in this dir:

```
php -S 127.0.0.1:8000 -t tests/data/app
```

If you run `FrameworkTest` for various frameworks, you don't need a web server running.

It is a very basic PHP application developed with `glue` microframework. There are various html pages in `view` subdir that are used in tests. To add a new html page, you should add a file into `tests/data/view`, then add it to routes of `tests/data/app/index.php` file:

```
$urls = array(
    '/' => 'index',
    '/info' => 'info',
    '/cookies' => 'cookies',
    '/search.*' => 'search',
    '/login' => 'login',
    '/redirect' => 'redirect',
    '/facebook\??.*' => 'facebookController',
    '/form/(field|select|checkbox|file|textarea|hidden|complex|button|radio|select_multiple|empty|popup|example1)(#)?' => 'form',
    '/articles\??.*' => 'articles'
)
```

And into `tests/data/app/controllers.php`.

For regression testing, and real world HTML page examples that you can add a html page into `tests/data/app/view/form/exampleX.php` file and add its name to routes. 

### How to write module tests

Learn by examples! There are pretty much other tests already written. Please follow their structure. 

By default you are supposed to access module methods via `module` property. 

```php
<?php
$this->module->amOnPage('/form/checkbox');
$this->module->appendField('form input[name=terms]', 'I Agree123');
?>
```

To verify that form was submitted correctly you can use `data::get` method.

Example:

```php
<?php
function testPasswordField()
{
    $this->module->amOnPage('/form/complex');
    $this->module->submitForm('form', array(
       'password' => '123456'
    ));
    $form = data::get('form');
    $this->assertEquals('123456', $form['password']);
}
?>
```

There is also a convention to use `shouldFail` method to set expected fail.

```php
<?php
protected function shouldFail()
{
    $this->setExpectedException('PHPUnit_Framework_AssertionFailedError');
}


public function testAppendFieldRadioButtonByValueFails()
{
    $this->shouldFail();
    $this->module->amOnPage('/form/radio');
    $this->module->appendField('form input[name=terms]','disagree123');
}

?>
```

## Cli

For most cases Codeception is self-tested using acceptance tests in *cli* suite. That is how Codeception core classes are tested. And actually there is no possibility to unit test many cases. Because you can't ask PHPUnit to mock PHPUnit classes.

If you send Pull Request to Codeception core and you don't know how to get it tested, just create new cli test for that. Probably you will need some additional files, maybe another suite configurations, so add them. 

That is why Codeception can't have code coverage reports, as we rely on acceptance tests in testing core.

You can run all cli tests with

```
codecept run cli
```

Test cases are:

* generating codeception templates
* running tests with different configs in different modes
* testing execution order
* running multi-app tests
* etc

### Claypit + Sandbox 

Before each test `tests/data/claypit` is copied to `tests/data/sandbox`, and all the test actions will be executed inside that sandbox. In the end this directory is removed. In sandbox different codeception tests may be executed and checked for exepected output.

Example test:

```php
<?php
$I = new CliGuy($scenario);
$I->wantToTest('build command');
$I->runShellCommand('php codecept build');
$I->seeInShellOutput('generated successfully');
$I->seeFileFound('CodeGuy.php','tests/unit');
$I->seeFileFound('CliGuy.php','tests/cli');
$I->seeInThisFile('seeFileFound(');
?>
```

There are various test suites in `tests/data/claypit`. Maybe you will need to create a new suite for your tests.

## Coverage

Acceptance tests that use demo application and `c3` collector, to check that a code coverage can be collected remotely. This tests are rarely updated, they should just work )
