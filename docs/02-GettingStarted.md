# Getting Started

Let's take a look at Codeception's architecture. We assume that you already [installed](http://codeception.com/install) it, and bootstrapped your first test suites. Codeception has generated three of them: unit, functional, and acceptance. They are well described in the previous chapter. Inside your __/tests__ folder you will have three config files and three directories with names corresponding to these suites. Suites are independent groups of tests with a common purpose. 

## Actors

One of the main concepts of Codeception is representation of tests as actions of a person. We have a UnitTester, who executes functions and tests the code. We also have a FunctionalTester, a qualified tester, who tests the application as a whole, with knowledge of its internals. And an AcceptanceTester, a user that works with our application through an interface that we provide.

Actor classes are not written but generated from suite configuration. **Methods of actor classes are generally taken from Codeception Modules**. Each module provides predefined actions for different testing purposes, and they can be combined to fit the testing environment. Codeception tries to solve 90% of possible testing issues in its modules, so you don't have reinvent the wheel. We think that you can spend more time on writing tests and less on writing support code to make those tests run. By default AcceptanceTester relies on PhpBrowser module, which is set in `tests/acceptance.suite.yml` configuration file:

```yaml
class_name: AcceptanceTester
modules:
    enabled:
        - PhpBrowser:
            url: http://localhost/myapp/
        - \Helper\Acceptance
```

In this configuration file you can enable/disable and reconfigure modules for your needs.
When you change configuration, actor classes are rebuilt automatically. If Actor classes are not created or updated as you expect, try to generate them manually with `build` command:

```bash
php codecept build
```


## Writing a Sample Scenario

By default tests are written as narrative scenarios. To make a PHP file a valid scenario, its name should have a `Cept` suffix. 

Let's say, we created a file `tests/acceptance/SigninCept.php`

We can do that by running the following command:

```bash
php codecept generate:cept acceptance Signin
```

A Scenario always starts with Actor class initialization. After that, writing a scenario is just like typing `$I->` and choosing a proper action from the auto-completion list. Let's log in to our website.

```php
<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('login to website');

```

The `wantTo` section describes your scenario in brief. There are additional comment methods that are useful to describe context of a scenario:

```php
<?php
$I = new AcceptanceTester($scenario);
$I->am('user'); // actor's role
$I->wantTo('login to website'); // feature to test
$I->lookForwardTo('access all website features'); // result to achieve
```

After we have described the story background, let's start writing a scenario.

We assume that we have a 'login' page where we are getting authenticated by providing a username and password. Then we are sent to a user page, where we see the text `Hello, %username%`. Let's look at how this scenario is written in Codeception.

```php
<?php
$I = new AcceptanceTester($scenario);
$I->am('user');
$I->wantTo('login to website');
$I->lookForwardTo('access all website features');
$I->amOnPage('/login');
$I->fillField('Username','davert');
$I->fillField('Password','qwerty');
$I->click('Login');
$I->see('Hello, davert');
```

This scenario can probably be read by non-technical people. If you just remove all special chars like braces, arrows and `$`, this test transforms into plain English text:

```
I am user
I wantTo login to website
I lookForwardTo access all website features
I amOnPage '/login'
I fillField 'Username','davert'
I fillField 'Password','qwerty'
I click 'Login'
I see 'Hello, davert'
```

Codeception generate this text represenation from PHP code by executing:

``` bash
php codecept generate:scenarios
```

Generated scenarios will be stored in your `_output` directory in text files.

Before we execute this test, we should make sure that the website is running on a local web server. Let's open the `tests/acceptance.suite.yml` file and replace the URL with the URL of your web application:

``` yaml
class_name: AcceptanceTester
modules:
    enabled:
        - PhpBrowser:
            url: 'http://myappurl.local'
        - \Helper\Acceptance
```

After we configured the URL we can run this test with the `run` command:

``` bash
php codecept run
```

Here is the output we should see:

``` bash
Acceptance Tests (1) -------------------------------
✔ SigninCept: Login to website
----------------------------------------------------

Time: 1 second, Memory: 21.00Mb

OK (1 test, 1 assertions)
```

Let's get a detailed output:

```bash
php codecept run acceptance --steps
```

We should see a step-by-step report on the performed actions.

```bash
Acceptance Tests (1) -------------------------------
SigninCept: Login to website
Signature: SigninCept.php
Test: tests/acceptance/SigninCept.php
Scenario --
 I am user
 I look forward to access all website features
 I am on page "/login"
 I fill field "Username" "davert"
 I fill field "Password" "qwerty"
 I click "Login"
 I see "Hello, davert"
 OK
----------------------------------------------------  

Time: 0 seconds, Memory: 21.00Mb

OK (1 test, 1 assertions)
```

This simple test can be extended to a complete scenario of site usage. 
So by emulating the user's actions you can test any of your websites.

Give it a try!

## Bootstrap

Each suite has its own bootstrap file. It's located in the suite directory and is named `_bootstrap.php`. It will be executed before test suite. There is also a global bootstrap file located in the `tests` directory. It can be used to include additional files.

## Cept, Cest and Test Formats

Codeception supports three test formats. Beside the previously described scenario-based Cept format, Codeception can also execute [PHPUnit test files for unit testing](http://codeception.com/docs/05-UnitTests), and Cest format.

Cest combines scenario-driven test approach with OOP design. In case you want to group a few testing scenarios into one you should consider using Cest format. In the example below we are testing CRUD actions within a single file but with a several test (one per each operation):

```php
<?php
class PageCrudCest
{
    function _before(AcceptanceTester $I)
    {
        // will be executed at the beginning of each test
        $I->amOnPage('/');
    }

    function createPage(AcceptanceTester $I)
    {
       // todo: write test
    }

    function viewPage(AcceptanceTester $I)
    {
       // todo: write test
    }    

    function updatePage(AcceptanceTester $I)
    {
        // todo: write test
    }
    
    function deletePage(AcceptanceTester $I)
    {
       // todo: write test
    }
}
```

Such Cest file can be created by running a generator:

```bash
php codecept generate:cest acceptance PageCrud
```

Learn more about [Cest format](http://codeception.com/docs/07-AdvancedUsage#Cest-Classes) in Advanced Testing section.

## BDD

Codeception allows to execute user stories in Gherkin format in a similar manner as it is done in Cucumber or Behat. Please refer to [BDD chapter of guides](http://codeception.com/docs/07-BDD) to learn more.

## Configuration

Codeception has a global configuration in `codeception.yml` and a config for    each suite. We also support `.dist` configuration files. If you have several developers in a project, put shared settings into `codeception.dist.yml` and personal settings into `codeception.yml`. The same goes for suite configs. For example, the `unit.suite.yml` will be merged with `unit.suite.dist.yml`. 

## Running Tests

Tests can be started with the `run` command.

```bash
php codecept run
```

With the first argument you can run all tests from one suite.

```bash
php codecept run acceptance
```

To limit tests run to a single class, add a second argument. Provide a local path to the test class, from the suite directory.

```bash
php codecept run acceptance SigninCept.php
```

Alternatively you can provide the full path to test file:

```bash
php codecept run tests/acceptance/SigninCept.php
```

You can further filter which tests are run by appending a method name to the class, separated by a colon (for Cest or Test formats):

```bash
php codecept run tests/acceptance/SignInCest.php:^anonymousLogin$
```

You can provide a directory path as well. This will execute all tests from the backend dir:

```bash
php codecept run tests/acceptance/backend
```

Using regular expressions, you can even run many different test methods from the same directory or class. For example, this will execute all tests from the backend dir beginning with the word login:

```bash
php codecept run tests/acceptance/backend:^login
```

To execute a group of tests that are not stored in the same directory, you can organize them in [groups](http://codeception.com/docs/07-AdvancedUsage#Groups).

### Reports

To generate JUnit XML output, you can provide the `--xml` option, and `--html` for HTML report. 

```bash
php codecept run --steps --xml --html
```

This command will run all tests for all suites, displaying the steps, and building HTML and XML reports. Reports will be stored in the `tests/_output/` directory.

To learn all available options, run the following command:

```bash
php codecept help run
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
* `generate:feature` *suite* *filename* - Generates Gherkin feature file
* `generate:suite` *suite* *actor* - Generates a new suite with the given Actor class name
* `generate:scenarios` *suite* - Generates text files containing scenarios from tests
* `generate:helper` *filename* - Generates a sample Helper File
* `generate:pageobject` *suite* *filename* - Generates a sample Page object
* `generate:stepobject` *suite* *filename* - Generates a sample Step object
* `generate:environment` *env* - Generates a sample Environment configuration
* `generate:groupobject` *group* - Generates a sample Group Extension


## Conclusion

We took a look into the Codeception structure. Most of the things you need were already generated by the `bootstrap` command. After you have reviewed the basic concepts and configurations, you can start writing your first scenario. 
