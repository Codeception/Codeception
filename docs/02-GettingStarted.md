# Getting Started

Let's take a look at Codeception's architecture. We assume that you already [installed](http://codeception.com/install) it, and bootstrapped your first test suites. Codeception has generated three of them: unit, functional, and acceptance. They are well described in the previous chapter. Inside your __/tests__ folder you will have three config files and three directories with names corresponding to these suites. Suites are independent groups of tests with a common purpose. 

## Actors

One of the main concepts of Codeception is representation of tests as actions of a person. We have a UnitTester, who executes functions and tests the code. We also have a FunctionalTester, a qualified tester, who tests the application as a whole, with knowledge of its internals. And an AcceptanceTester, a user that works with our application through an interface that we provide.

Each of these Actors are PHP classes along with the actions that they are allowed to do. As you can see, each of these Actors have different abilities. They are not constant, you can extend them. One Actor belongs to one testing suite. 

Actor classes are not written but generated from suite configuration. When you change configuration, actor classes are **rebuilt automatically**.

If Actor classes are not created or updated as you expect, try to generate them manually with `build` command:

```bash
$ php codecept.phar build
```


## Writing a Sample Scenario

By default tests are written as narrative scenarios. To make a PHP file a valid scenario, its name should have a `Cept` suffix. 

Let's say, we created a file `tests/acceptance/SigninCept.php`

We can do that by running the following command:

```bash
$ php codecept.phar generate:cept acceptance Signin
```

A Scenario always starts with Actor class initialization. After that, writing a scenario is just like typing `$I->` and choosing a proper action from the auto-completion list.

```php
<?php
$I = new AcceptanceTester($scenario);
?>
```

Let's log in to our website. We assume that we have a 'login' page where we are getting authenticated by providing a username and password. Then we are sent to a user page, where we see the text `Hello, %username%`. Let's look at how this scenario is written in Codeception.

```php
<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('log in as regular user');
$I->amOnPage('/login');
$I->fillField('Username','davert');
$I->fillField('Password','qwerty');
$I->click('Login');
$I->see('Hello, davert');
?>
```

Before we execute this test, we should make sure that the website is running on a local web server. Let's open the `tests/acceptance.suite.yml` file and replace the URL with the URL of your web application:

``` yaml
config:
    PhpBrowser:
        url: 'http://myappurl.local'
```

After we configured the URL we can run this test with the `run` command:

``` bash
$ php codecept.phar run
```

Here is the output we should see:

``` bash
Acceptance Tests (1) -------------------------------
Trying log in as regular user (SigninCept.php)   Ok
----------------------------------------------------

Functional Tests (0) -------------------------------
----------------------------------------------------

Unit Tests (0) -------------------------------------
----------------------------------------------------

Time: 1 second, Memory: 21.00Mb

OK (1 test, 1 assertions)
```

Let's get a detailed output:

```bash
$ php codecept.phar run acceptance --steps
```

We should see a step-by-step report on the performed actions.

```bash
Acceptance Tests (1) -------------------------------
Trying to log in as regular user (SigninCept.php)
Scenario:
* I am on page "/login"
* I fill field "Username" "davert"
* I fill field "Password" "qwerty"
* I click "Login"
* I see "Hello, davert"
  OK
----------------------------------------------------  

Time: 0 seconds, Memory: 21.00Mb

OK (1 test, 1 assertions)
```

That was a very simple test that you can reproduce for your own website.
By emulating the user's actions you can test all of your websites the same way.

Give it a try!

## Modules and Helpers

The actions in Actor classes are taken from modules. Generated Actor classes emulate multiple inheritance. Modules are designed to have one action performed with one method. According to the [DRY principle](http://en.wikipedia.org/wiki/Don%27t_repeat_yourself), if you use the same scenario components in different modules, you can combine them and move them to a custom module. By default each suite has an empty module, which can be used to extend Actor classes. They are stored in the __support__ directory.

## Bootstrap

Each suite has its own bootstrap file. It's located in the suite directory and is named `_bootstrap.php`. It will be executed before test suite. There is also a global bootstrap file located in the `tests` directory. It can be used to include additional files.

## Test Formats

Codeception supports three test formats. Beside the previously described scenario-based Cept format, Codeception can also execute [PHPUnit test files for unit testing](http://codeception.com/docs/06-UnitTests), and [class-based Cest](http://codeception.com/docs/07-AdvancedUsage#Cest-Classes) format. They are covered in later chapters. There is no difference in the way the tests of either format will be run in the suite.

## Configuration

Codeception has a global configuration in `codeception.yml` and a config for each suite. We also support `.dist` configuration files. If you have several developers in a project, put shared settings into `codeception.dist.yml` and personal settings into `codeception.yml`. The same goes for suite configs. For example, the `unit.suite.yml` will be merged with `unit.suite.dist.yml`. 


## Running Tests

Tests can be started with the `run` command.

```bash
$ php codecept.phar run
```

With the first argument you can run tests from one suite.

```bash
$ php codecept.phar run acceptance
```

To run exactly one test, add a second argument. Provide a local path to the test, from the suite directory.

```bash
$ php codecept.phar run acceptance SigninCept.php
```

Alternatively you can provide the full path to test file:

```bash
$ php codecept.phar run tests/acceptance/SigninCept.php
```

You can execute one test from a test class (for Cest or Test formats)

```bash
$ php codecept.phar run tests/acceptance/SignInCest.php:anonymousLogin
```

You can provide a directory path as well:

```bash
$ php codecept.phar run tests/acceptance/backend
```

This will execute all tests from the backend dir.

To execute a group of tests that are not stored in the same directory, you can organize them in [groups](http://codeception.com/docs/07-AdvancedUsage#Groups).

### Reports

To generate JUnit XML output, you can provide the `--xml` option, and `--html` for HTML report. 

```bash
$ php codecept.phar run --steps --xml --html
```

This command will run all tests for all suites, displaying the steps, and building HTML and XML reports. Reports will be stored in the `tests/_output/` directory.

To learn all available options, run the following command:

```bash
$ php codecept.phar help run
```

## Debugging

To receive detailed output, tests can be executed with the `--debug` option.
You may print any information inside a test using the `codecept_debug` function.

### Generators

There are plenty of useful Codeception commands:

* `generate:cept` *suite* *filename* - Generates a sample Cept scenario
* `generate:cest` *suite* *filename* - Generates a sample Cest test
* `generate:test` *suite* *filename* - Generates a sample PHPUnit Test with Codeception hooks
* `generate:phpunit` *suite* *filename* - Generates a classic PHPUnit Test
* `generate:suite` *suite* *actor* - Generates a new suite with the given Actor class name
* `generate:scenarios` *suite* - Generates text files containing scenarios from tests


## Conclusion

We took a look into the Codeception structure. Most of the things you need were already generated by the `bootstrap` command. After you have reviewed the basic concepts and configurations, you can start writing your first scenario. 
