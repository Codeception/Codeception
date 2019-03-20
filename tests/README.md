# Codeception Internal Tests

In case you submit a pull request, you will be asked for writing a test.

There are 3 suites for testing

* cli - acceptance tests of CLI commands
* coverage - acceptance tests of code coverage
* unit - all unit/integration/etc tests.

## Set up
1. Clone the repository to your local machine
1. Make sure you have the MongoDB extension enabled. It's not included in PHP by default, you can download it from http://pecl.php.net/package/mongodb
1. Run `composer install` in the cloned project directory

To run the web tests:
1. Start PHP's internal webserver in the project directory:
    ```
    php -S 127.0.0.1:8000 -t tests/data/app
    ```
1. Start Selenium server
1. Run:
    ```
    php codecept run web --env chrome
    ```

## Unit

The most important tests in this suite are Module tests located in `test/unit/Codeception/Module`. Unlike you would expect, most of tests there are integrational tests. For example, `WebDriverTest` requires an actual Selenium Server to be running.

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
* MongoDb, AMQP, etc - corresponding backends

### Demo Application

When a module requires a web server with the demo application running, you can find this app in `tests/data/app`. To execute tests for **PhpBrowser** or **WebDriver** you need to start a web server for this dir:

```
php -S 127.0.0.1:8000 -t tests/data/app
```

If you run `FrameworkTest` for various frameworks, you don't need a web server running.

It is a very basic PHP application developed with `glue` microframework. To add a new html page for a test:

1. Create a new file in `tests/data/app/view`
1. Add a route in `tests/data/app/index.php`
1. Add a class in `tests/data/app/controllers.php`

To see the page in the browser, open `http://localhost:8000/your-route`

Then create a test in `tests/web/WebDriverTest.php`, and run it with `php codecept run web WebDriverTest::yourTest --env chrome`

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

---

## Dockerized testing

### Local testing and development with `docker-compose`

Using `docker-compose` for test configurations

    cd tests

Build the `codeception/codeception` image

    docker-compose build

Start

    docker-compose up -d

By default the image has `codecept` as its entrypoint, to run the tests simply supply the `run` command

    docker-compose run --rm codecept help

Run suite

    docker-compose run --rm codecept run cli

Run folder

    docker-compose run --rm codecept run unit Codeception/Command

Run single test

    docker-compose run --rm codecept run cli ExtensionsCest

Development bash

    docker-compose run --rm --entrypoint bash codecept

Cleanup

    docker-compose run --rm codecept clean

In parallel

    docker-compose --project-name test-cli run -d --rm codecept run --html report-cli.html cli & \
    docker-compose --project-name test-unit-command run -d --rm codecept run --html report-unit-command.html unit Codeception/Command & \
    docker-compose --project-name test-unit-constraints run -d --rm codecept run --html report-unit-constraints.html unit Codeception/Constraints

### Adding services

Add Redis to `docker-compose.yml`

    services:
      [...]
      redis:
        image: redis:3

Update `host`

     protected static $config = [
         'database' => 15,
         'host' => 'redis'
     ];

Run Redis tests

    docker-compose run --rm codecept run unit Codeception/Module/RedisTest

Further Examples

      firefox:
        image: selenium/standalone-firefox-debug:2.52.0
      chrome:
        image: selenium/standalone-chrome-debug:2.52.0
      mongo:
        image: mongo
