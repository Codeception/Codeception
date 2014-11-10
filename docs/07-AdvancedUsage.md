# Advanced Usage

In this chapter we will cover some techniques and options that you can use to improve your testing experience and stay with better organization of your project. 

## Cest Classes

In case you want to get a class-like structure for your Cepts, you can use the Cest format instead of plain PHP.
It is very simple and is fully compatible with Cept scenarios. It means that if you feel that your test is long enough and you want to split it - you can easily move it into classes.

You can create Cest file by running the command:

```bash
$ php codecept.phar generate:cest suitename CestName
```

The generated file will look like this:

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

**Each public method of Cest (except those starting with `_`) will be executed as a test** and will receive Actor class as the first parameter and `$scenario` variable as the second one.

In `_before` and `_after` methods you can use common setups and teardowns for the tests in the class. This actually makes Cest tests more flexible then Cepts, which rely only on similar methods in Helper classes.

As you see, we are passing Actor object into `tryToTest` method. It allows us to write scenarios the way we did before.

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
        $I->fillField('username', 'john');
        $I->fillField('password', 'coltrane');
        $I->click('Enter');
        $I->see('Hello, John');
        $I->seeInCurrentUrl('/account');
    }
}
?>
```

As you see, Cest class have no parents like `\Codeception\TestCase\Test` or `PHPUnit_Framework_TestCase`. This is done intentionally. It allows you to extend class with common behaviors and workarounds that may be used in child classes. But don't forget to make these methods `protected` so they won't be executed as tests.

Also you can define `_failed` method in Cest class which will be called if test finishes with `error` or fails.

### Before/After Annotations

You can control execution flow with `@before` and `@after` annotations. You may move common actions into protected (non-test) methods and invoke them before or after the test method by putting them into annotations. It is possible to invoke several methods by using more than one `@before` or `@after` annotation. Methods are invoked in order from top to bottom.

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
    public function banUser(AcceptanceTester $I)
    {
        $I->amOnPage('/users/charlie-parker');
        $I->see('Ban', '.button');
        $I->click('Ban');
    }
    
    /**
     * @before login
     * @before cleanup
     * @after logout
     * @after close
     */
    public function addUser(AcceptanceTester $I)
    {
        $I->amOnPage('/users/charlie-parker');
        $I->see('Ban', '.button');
        $I->click('Ban');
    }
}
?>
```

You can also use `@before` and `@after` for included functions. But you can't have multiple annotations of the same kind for single method - one method can have only one `@before` and only one `@after` annotation.

### Depends Annotation

With `@depends` annotation you can specify a test that should be passed before the current one. If that test fails, the current test will be skipped.
You should pass a method name of a test you are relying on.

```php
<?php
class ModeratorCest {

    public function login(AcceptanceTester $I)
    {
        // logs moderator in
    }

    /**
     * @depends login
     */
    public function banUser(AcceptanceTester $I)
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

You can run the console with the following command:

``` bash
$ php codecept.phar console suitename
```

Now you can execute all commands of appropriate Actor class and see results immediately. This is especially useful when used with `WebDriver` module. It always takes too long to launch Selenium and browser for tests. But with console you can try different selectors, and different commands, and then write a test that would pass for sure when executed.

And a special hint: show your boss how you can nicely manipulate web pages with console and Selenium. It will be easy to convince to automate this steps and introduce acceptance testing to the project.

## Running from different folders

If you have several projects with Codeception tests, you can use single `codecept.phar` file to run all of your tests.
You can pass `-c` option to any Codeception command, excluding `bootstrap`, to execute Codeception in another directory.

```bash
$ php codecept.phar run -c ~/projects/ecommerce/
$ php codecept.phar run -c ~/projects/drupal/
$ php codecept.phar generate:cept acceptance CreateArticle -c ~/projects/drupal/
```

To create a project in directory different from the current one, just provide its path as a parameter.

```bash
$ php codecept.phar bootstrap ~/projects/drupal/
```

Basically `-c` option allows you to specify not only the path, but a config file to be used. Thus, you can have several `codeception.yml` files for your test suite. You may use it to specify different environments and settings. Just pass a filename into `-c` parameter to execute tests with specific config settings.

## Groups

There are several ways to execute bunch of tests. You can run tests from specific directory:

```bash
$ php codecept.phar run tests/acceptance/admin
```

Or execute one (or several) specific groups of tests:

```bash
$ php codecept.phar run -g admin -g editor
```

In this case all tests that belongs to groups `admin` and `editor` will be executed. Concept of groups was taken from PHPUnit and in classical PHPUnit tests they behave just in the same way. To add Cept to the group - use `$scenario` variable:

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

For Tests and Cests you can use `@group` annotation to add a test to the group.

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

Groups can be defined in global or suite configuration file.
Tests for groups can be specified as array or as path to file containing list of groups.

```yaml
groups:
  # add 2 tests to db group
  db: [tests/unit/PersistTest.php, tests/unit/DataTest.php]

  # add list of tests to slow group
  slow: tests/_data/slow  
```

For instance, you can create a file with the list of the most slow tests, and run them inside their own group.
Group file is a plain text file with test names on separate lines:

```bash
tests/unit/DbTest.php
tests/unit/UserTest.php:create
tests/unit/UserTest.php:update
```

You can create group files manually or generate them from 3rd party applications. 
For example, you may write a script that updates the slow group by taking the slowest tests from xml report.

You can even specify patterns for loading multiple group files by single definition:

```yaml
groups:
  p*: tests/_data/p*
```

This will load all found `p*` files in `tests/_data` as groups.

## Refactoring

As the test base grows, tests will require refactoring to share common variables and behaviors. The classical example is a `login` action which may be called for every test of your test suite. It would be wise to write it once and use it in all tests.

It's pretty obvious that for such cases you can use your own PHP classes to define such methods.

```php
<?php
class TestCommons
{
    public static $username = 'john';
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

Then this file can be required in `_bootstrap.php` file:

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

If you caught the idea, let's learn some built-in features for structuring your test code. We will discover implementation of `PageObject` and `StepObject` patterns in Codeception.

## PageObjects

[PageObject pattern](http://code.google.com/p/selenium/wiki/PageObjects) is widely used by test automation engineers. The PageObject pattern represents a web page as a class and the DOM elements on that page as its properties, and some basic interactions as its methods.
PageObjects are very important when you are developing a flexible architecture of your tests. Please do not hardcode complex CSS or XPath locators in your tests but rather move them into PageObject classes.

Codeception can generate a PageObject class for you with command:

```bash
$ php codecept.phar generate:pageobject Login
```

This will create a `LoginPage` class in `tests/_pages`. The basic PageObject is nothing more then empty class with a few stubs.
It is expected you will get it populated with UI locators of a page it represents and then those locators will be used on a page.
Locators are represented with public static properties:

```php
<?php
class LoginPage
{
    public static $URL = '/login';

    public static $usernameField = '#mainForm #username';
    public static $passwordField = '#mainForm input[name=password]';
    public static $loginButton = '#mainForm input[type=submit]';
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
As you see, you can freely change markup of your login page, and all the tests interacting with this page will have their locators updated according to properties of LoginPage class.

But let's move further. A PageObject concept also defines that methods for the page interaction should also be stored in a PageObject class.
This can't be done in `LoginPage` class we just generated. Because this class is accessible across all test suites, we do not know which Actor class will be used for interaction. Thus, we will need to generate another page object. In this case we will explicitly define the suite to be used:

```bash
$ php codecept.phar generate:pageobject acceptance UserLogin
```

*We called this class UserLogin for not to get into conflict with Login class we created before.*

This generated `UserLoginPage` class looks almost the same way as LoginPage class we had before with one difference. It now stores passed instance of Actor class. An AcceptanceTester can be accessed via `AcceptanceTester` property of that class. Let's define a `login` method in this class.

```php
<?php
class UserLoginPage
{
    // include url of current page
    public static $URL = '/login';

    /**
     * @var AcceptanceTester
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

Probably we should merge the `UserLoginPage` and `LoginPage` classes as they do play the same role. But `LoginPage` can be used both in functional and acceptance tests, when `UserLoginPage` only in tests with `AcceptanceTester`. So it's up to you to use global page objects or local per suite page objects. If you feel like your functional tests have much in common with acceptance tests, you should store locators in global PageObject class and use StepObjects as an alternative to behavioral PageObjects.

## StepObjects

StepObjects pattern came from BDD frameworks. StepObject class contains a bunch of common actions that may be used widely in different tests.
The `login` method we used above can be a good example of such method. Similarly actions for creating/updating/deleting resources should be moved to StepObject too. Let's create a StepObject class and see what it looks like.

Lets create `Member` Steps class, generator will prompt you for methods to include, so let's add `login` to it.

```bash
$ php codecept.phar generate:stepobject acceptance Member
```

You will be asked to enter action names, but it's optional. Enter one at a time, and press Enter. After specifying all needed actions, leave empty line to go on to StepObject creation.

```bash
$ php codecept.phar generate:stepobject acceptance Member
Add action to StepObject class (ENTER to exit): login
Add action to StepObject class (ENTER to exit):
StepObject was created in <you path>/tests/acceptance/_steps/MemberSteps.php
```

It will generate class in `tests/acceptance/_steps/MemberSteps.php` similar to this:

```php
<?php
namespace AcceptanceTester;

class MemberSteps extends \AcceptanceTester
{
    public function login()
    {
        $I = $this;

    }
}
?>
```

As you see, this class is very simple. It extends `AcceptanceTester` class, thus, all methods and properties of `AcceptanceTester` are available for usage in it.

`login` method can be implemented like this:

```php
<?php
namespace AcceptanceTester;

class MemberSteps extends \AcceptanceTester
{
    public function login($name, $password)
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

In tests you can use a StepObject by instantiating `MemberSteps` class instead of `AcceptanceTester`.

```php
<?php
$I = new AcceptanceTester\MemberSteps($scenario);
$I->login('bill evans', 'debby');
?>
```

As you see, StepObject class looks much simpler and readable then classical PageObject. 
As an alternative to StepObject we could use methods of `AcceptanceHelper` class. In a helper we do not have access to `$I` object itself, thus it's better to use Helpers to implement new actions, and StepObjects to combine common scenarios.

## Environments

For cases where you need to run tests with different configurations you can define different config environments.
The most typical use cases are running acceptance tests in different browsers, or running database tests using different database engines.

Let's demonstrate usage of environments for the browsers case.

We need to add new lines to `acceptance.suite.yml`:

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
        # nothing changed
```

At first these config trees may look ugly, but it is the cleanest way of doing this.
Basically you can define different environments inside the `env` root, name them (`phantom`, `chrome` etc.),
and then redefine any configuration parameters that were set before.

You can easily switch between those configs by running tests with `--env` option. To run tests only for PhantomJS you need to pass `--env phantom` option:

```bash
$ php codecept.phar run acceptance --env phantom
```

To run tests in all 3 browsers, just list all the environments:

```bash
$ php codecept.phar run acceptance --env phantom --env chrome --env firefox
```

and tests will be executed 3 times, each time in a different browser.

Depending on environment you may choose which tests are to be executed.
For example, you might need some tests to be executed only in Firefox, and few tests only in Chrome.

Desired environments can be specified with `@env` annotation for tests in Test and Cest formats:

```php
<?php
class UserCest
{
    /**
     * This test will be executed only in 'firefox' and 'phantom' environments
     *
     * @env firefox
     * @env phantom
     */
    public function webkitOnlyTest(AcceptanceTester $I)
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
// or
$scenario->env(array('phantom', 'firefox'));
?>
```

This way you can easily control what tests will be executed for which browsers.


## Conclusion

Codeception is a framework which may look simple at first glance. But it allows you to build powerful tests with single API, refactor them, and write them faster using interactive console. Codeception tests can be easily organized in groups or Cest classes, locators can be stored in PageObjects and common steps can be combined in StepObjects.

Probably it has too much features for one framework. But nevertheless Codeception follows the KISS principle: it's easy to start, easy to learn, and easy to extend.
