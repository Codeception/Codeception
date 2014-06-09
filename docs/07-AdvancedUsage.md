# Advanced Usage

In this chapter we will cover some techniques and options that you can use to improve your testing experience and stay with better organization of your project. 

## Cest Classes

In case you want to get a class-like structure for your Cepts, instead of plain PHP, you can use the Cest format.
It is very simple and is fully compatible with Cept scenarios. It means if you feel like your test is long enough and you want to split it - you can easily move it into classes. 

You can start Cest file by running the command:

```
php codecept.phar generate:cest suitename CestName
```

The generated file will look like:

```php
<?php
class BasicCest
{
    public function _before(\AcceptanceTester $I)
    {
    }

    public function _after(\AcceptanceTester $I)
    {
    }

    // tests
    public function tryToTest(\AcceptanceTester $I) 
    {    
    }
}
?>
```

**Each public method of Cest (except, those starting from `_`) will be executed as a test** and will receive Actor class as the first parameter and `$scenario` variable as a second. 

In `_before` and `_after` method you can use common setups, teardowns for the tests in the class. That actually makes Cest tests more flexible, then Cepts that rely only on similar methods in Helper classes.

As you see we are passing Guy class into `tryToTest` stub. That allows us to write a scenarios the way we did before.

```php
<?php
class BasicCest
{
    // test
    public function checkLogin(\AcceptanceTester $I) 
    {
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

As you see, Cest class have no parent like `\Codeception\TestCase\Test` or `PHPUnit_Framework_TestCase`. That was done intentionally. This allows you to extend class with common behaviors and workarounds that may be used in child class. But don't forget to make them `protected` so they won't be executed as a tests themselves.

Also you can define `_failed` method in Cest class which will be called if test finished with `error` or fail.

### Before/After Annotations

You can control execution flow with `@before` and `@after` annotations. You may move common actions into protected (non-test) methods and invoke them before or after the test method by putting them into annotations.

```php
<?php
class ModeratorCest {

    protected function login(AcceptanceTester $I)
    {
        $I->amOnPage('/login');
        $I->fillField('Username', 'miles');
        $I->fillField('Password', 'davis');
        $I->click('Login');
    }

    /**
     * @before login
     */
    function banUser(AcceptanceTester $I)
    {
        $I->amOnPage('/users/charlie-parker');
        $I->see('Ban', '.button');
        $I->click('Ban');        
    }
}
?>
```

You can use `@before` and `@after` for included functions also. But you can't have multiple annotations in one method.

### Depends Annotation

With `@depends` annotation you can specify a test that should be passed before current one. If the test fails, current test will be skipped.
You should pass a method name of a test you are relying on.

``` php
<?php
class ModeratorCest {

    public function login(AcceptanceTester $I)
    {
        // logs moderator in
    }

    /**
     * @depends login
     */
    function banUser(AcceptanceTester $I)
    {
        // bans user
    }
}
?>
```

Hint: `@depends` can be combined with `@before`.

## Interactive Console

Interactive console was added to try Codeception commands before executing them inside a test. 

![console](http://img267.imageshack.us/img267/204/003nk.png)

You can execute console with 

``` bash
php codecept.phar console suitename
```

Now you can execute all commands of appropriate Actor class and see immidiate results. That is especially useful for when used with WebDriver module. It always takes too long to launch Selenium and browser for tests. But with console you can try different selectors, and different commands, and then write a test that would pass for sure when executed.

And a special hint: show your boss how you can nicely manipulate web pages with console and Selenium. It will be easy to convince to automate this steps and introduce acceptance testing to the project.

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

In this case all tests that belongs to groups `admin` or `editor` will be executed. Groups concept were taken from PHPUnit and in classical PHPUnit tests they behave just in the same way. To add Cept to the group - use `$scenario` variable:

```php
<?php
$scenario->group('admin');
$scenario->group('editor');
// or
$scenario->group(array('admin', 'editor'))
// or
$scenario->groups(array('admin', 'editor'))

$I = new AcceptanceTester($scenario);
$I->wantToTest('admin area');
?>
```

For Tests and Cests you can use annotation `@group` to add a test to the group.

```php
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

### Group Files

Groups can be defined in global or suite confuguration file.
Tests for groups can be specified as array or as path to file containing list of groups.

```yaml
groups:
  # add 2 tests to db group
  db: [tests/unit/PersistTest.php, tests/unit/DataTest.php]

  # add list of tests to slow group
  slow: tests/_data/slow  
```

For instance, you can create a file with the list of the most slow tests, and run them inside their own group.
Group file is a plain text file with test names listed by line

```
tests/unit/DbTest.php
tests/unit/UserTest.php:create
tests/unit/UserTest.php:update
```

You can create group files manually or generate them from 3rd party applications. 
Let's say you may write a script that updates the slow group by taking the slowest tests from xml report.

You can even specify pattern for loading multiple group files from one definitions:

```yaml
groups:
  p*: tests/_data/p*
```

This will load all found `p*` files in `tests/_data` as groups 

## Refactoring

As test base growths tests will require refactoring, sharing common variables and behaviors. The classical example for this is `login` action which will be called for maybe every test of your test suite. It's wise to make it written one time and use it in all tests. 

It's pretty obvious that for such cases you can use your own PHP classes to define such methods.

```php
<?php class TestCommons 
{
    public static $username = 'jon';
    public static $password = 'coltrane';

    public static function logMeIn($I)
    {
        $I->amOnPage('/login');
        $I->fillField('username', self::$username);
        $I->fillField('password', self::$password);
        $I->click('Enter');
    }
}
?>
```

This file can be required in `_bootstrap.php` file

```php
<?php
// bootstrap
require_once '/path/to/test/commons/TestCommons.php';
?>
```

and used in your scenarios:

```php
<?php
$I = new AcceptanceTester($scenario);
TestCommons::logMeIn($I);
?>
```

If you caught the idea, let's learn of built-in features for structuring your test code. We will discover PageObject and StepObject patterns implementation in Codeception.

## PageObjects

[PageObject pattern](http://code.google.com/p/selenium/wiki/PageObjects) is widely used by test automation engineers. The Page Object pattern represents a web page as a class and the DOM elements on that page as properties, and some basic interactions as a methods.
PageObjects are very important when you are developing a flexible architecture of your tests. Please do not hardcode complex CSS or XPath locators in your tests but rather move them into PageObject classes.

Codeception can generate a pageobject class for you with command:

```
php codecept.phar generate:pageobject Login
```

This will create a `LoginPage` class in `tests/_pages`. The basic pageobject is nothing more then empty class with a few stubs.
It is expected you will get it populated with UI locators of a page its represent and then those locators will be used on a page.
Locators are represented with public static properties:

```php
<?php
class LoginPage
{
    static $URL = '/login';

    static $usernameField = '#mainForm #username';
    static $passwordField = '#mainForm input[name=password]';
    static $loginButton = "#mainForm input[type=submit]";
}
?>
```

And this is how this page object can be used in a test:

```php
<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('login to site');
$I->amOnPage(LoginPage::$URL);
$I->fillField(LoginPage::$usernameField, 'bill evans');
$I->fillField(LoginPage::$passwordField, 'debby');
$I->click(LoginPage::$loginButton);
$I->see('Welcome, bill');
?>
```
As you see you can freely change markup of your login page and all the tests interacting with this page, will have their locators updated according to properties of LoginPage class. 

But lets move further. A PageObject concept also defines that methods for the page interaction should also be stored in a PageObject class.
This can't be done in `LoginPage` class we just generated. Because this class is accessible across all test suites, we do not know which Actor class will be used for interaction. Thus, we will need to generate another page object. In this case we will explicitly define the suite to be used:

```
php codecept.phar generate:pageobject acceptance UserLogin
```

*we called this class UserLogin for not to get into conflict with Login class we created before*

This generated `UserLoginPage` class looks almost the same way as LoginPage class we had before with one differences. A now stores an instance of Guy object passed. A AcceptanceTester can be accessed via `AcceptanceTester` property of that class. Let's define a `login` method in this class.

```php
<?php
class UserLoginPage
{
    // include url of current page
    static $URL = '/login';

    /**
     * @var AcceptanceTester;
     */
    protected $AcceptanceTester;

    public function __construct(AcceptanceTester $I)
    {
        $this->AcceptanceTester = $I;
    }

    public static function of(AcceptanceTester $I)
    {
        return new static($I);
    }

    public function login($name, $password)
    {
        $I = $this->AcceptanceTester;

        $I->amOnPage(self::$URL);
        $I->fillField(LoginPage::$usernameField, $name);
        $I->fillField(LoginPage::$passwordField, $password);
        $I->click(LoginPage::$loginButton);

        return $this;
    }    
}
?>
```

And here is an example of how this PageObject can be used in a test.

```php
<?php
$I = new AcceptanceTester($scenario);
UserLoginPage::of($I)->login('bill evans', 'debby');
?>
```

Probably we should merge the `UserLoginPage` and `LoginPage` classes as they do play the same role. But LoginPage can be used both in functional and acceptance tests, and UserLoginPage only in tests with a AcceptanceTester. So it's up to you to use global page objects or local per suite page objects. If you feel like your functional tests have much in common with acceptance, you should store locators in global PageObject class and use StepObjects as an alternative to behavioral PageObjects.

## StepObjects

StepObjects pattern came from BDD frameworks. StepObject class contains a bunch of common actions that may be used widely in different tests.
The `login` method we used above can be a good example of such method. Similarly actions for creating/updating/deleting resources should be moved to StepObject too. Let's create a StepObject class and see what is it like. 

Lets create a "Member" Steps class, a generator will prompt you for methods to include, so let's add a `login` to it.

```
php codecept.phar generate:stepobject acceptance Member
```

This will generate a similar class to `tests/acceptance/_steps/MemberSteps.php`.

```php
<?php
namespace AcceptanceTester;

class MemberSteps extends \AcceptanceTester
{
    function login()
    {
        $I = $this;

    }
}
?>
```

As you see this class is very simple. But it inherits from `AcceptanceTester` class, thus contains all its methods.
`login` method can be implemented like this:

```php
<?php
namespace AcceptanceTester;

class MemberSteps extends \AcceptanceTester
{
    function login($name, $password)
    {
        $I = $this;
        $I->amOnPage(\LoginPage::$URL);
        $I->fillField(\LoginPage::$usernameField, $name);
        $I->fillField(\LoginPage::$passwordField, $password);
        $I->click(\LoginPage::$loginButton);
    }
}
?>
```

In tests you can use a StepObject by instantiating a MemberSteps class instead of AcceptanceTester.

```php
<?php
$I = new AcceptanceTester\MemberSteps($scenario);
$I->login('bill evans','debby');
?>
```

As you see, StepObject class looks much simpler and readable then classical PageObject. 
As an alternative to StepObject we could use methods of `AcceptanceHelper` class. In a helper we do not have an access to `$I` object itself,
thus it's better to use Helpers should be used to implement new actions, and StepObjects to combine common scenarios.

## Environments

For cases where you need to run tests over different configurations you can define different config environments.
The most typical use cases are: run acceptance tests in different browsers, or run database tests over different database engines.

Let's demonstrate usage of environments for the browsers case.

We add new lines into `acceptance.suite.yml`:

``` yaml
class_name: AcceptanceTester
modules:
    enabled:
        - WebDriver
        - AcceptanceHelper
    config:
        WebDriver:
            url: 'http://127.0.0.1:8000/'
            browser: 'firefox'

env:
    phantom:
         modules:
            config:
                WebDriver:
                    browser: 'phantomjs'

    chrome:
         modules:
            config:
                WebDriver:
                    browser: 'chrome'

    firefox:
        # nothing changes
```

At first sight this trees of config looks ugly, but it is the most clean way of implementation.
Basically you can define different environments inside the `env` root, name them (`phantom`, `chrome`),
and then you can redefine any configuration parameter that was previously set.

You can easily switch those configs by running tests with `--env` option. To run tests only in phanrom js you pass `--env phantom option`

 ``` bash
 php codecept.phar run acceptance --env phantom
 ```

 To run tests in all 3 browsers, just list all the environments

 ```
 php codecept.phar run acceptance --env phantom --env chrome --env firefox
 ```

and tests will be executed 3 times, each time in a different browser.

Depending on environment you may choose which tests are to be executed.
For example, you might need some tests that will be executed only in Firefox, and few tests only in Chrome.

Desired environment for test can be specified with `@env` annotation for Test and Cest formats

```php
<?php
class UserCest {
    /**
     * This test will be executed only in firefox and phantom environments
     *
     * @env chrome
     * @env phantom
     */
    function webkitOnlyTest(AcceptanceTester $I)
    {
      // I do something
    }
}
?>
```

For Cept you should use `$scenario->env()`:

```php
<?php
$scenario->env('firefox');
$scenario->env('phantom');
$scenario->env(array('phantom', 'firefox'));
?>
```

This way you can easily control what tests will be executed for which browsers.


## Conclusion

Codeception is a framework which may look simple at first sight. But it allows you to build powerful test with one  APIs, refactor them, and write them faster using interactive console. Codeception tests can easily be organized with groups or Cest classes, store locators in PageObjects and combine common steps in StepObjects.

Probably too much features for the one framework. But nevertheless Codeception follows the KISS pricinple: it's easy to start, easy to learn, easy to extend.
