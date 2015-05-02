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

## Dependency Injection

Codeception supports simple dependency injection for Cest and \Codeception\TestCase\Test classes. It means that you can specify which classes you need as parameters of the special `_inject()` method, and Codeception will automatically create the respective objects and invoke this method, passing all dependencies as arguments. This may be useful when working with Helpers, for example:

```php
<?php
class SignUpCest
{
    /**
     * @var Helper\SignUp
     */
    protected $signUp;

    /**
     * @var Helper\NavBarHelper
     */
    protected $navBar;
 
    protected function _inject(\Helper\SignUp $signUp, \Helper\NavBar $navBar)
    {
        $this->signUp = $signUp;
        $this->navBar = $navBar;
    }
    
    public function signUp(\AcceptanceTester $I)
    {
        $I->wantTo('sign up');
 
        $this->navBar->click('Sign up');
        $this->signUp->register([
            'first_name'            => 'Joe',
            'last_name'             => 'Jones',
            'email'                 => 'joe@jones.com',
            'password'              => '1234',
            'password_confirmation' => '1234'
        ]);
    }
}
?>
```

Example of Test class:

```php
<?php
class MathTest extends \Codeception\TestCase\Test
{
   /**
    * @var \UnitTester
    */
    protected $tester;

    /**
     * @var Helper\Math
     */
    protected $math;

    protected function _inject(\Helper\Math $math)
    {
        $this->math = $math;
    }

    public function testAll()
    {
        $this->assertEquals(3, $this->math->add(1, 2));
        $this->assertEquals(1, $this->math->subtract(3, 2));
    }
}
?>
```

However, Dependency Injection is not limited to this. It allows you to **inject any class**, which can be constructed with arguments known to Codeception.

In order to make auto-wiring work, you will need to implement `_inject()` method with the list of desired arguments. It is important to speicfy the type of arguments, so Codeception can guess which objects are expected to be received. The `_inject()` will be invoked just once right after creation of the TestCase object (either Cest or Test). Dependency Injection will also work in a similar manner for Helper and Actor classes.

Each test of Cest class can declare its own dependencies and receive them from method arguments:

```php
<?php
class UserCest
{
    function updateUser(\Helper\User $u, \AcceptanceTester $I, \Page\User $userPage)
    {
        $user = $u->createDummyUser();
        $userPage->login($user->getName(), $user->getPassword());
        $userPage->updateProfile(['name' => 'Bill']);
        $I->see('Profile was saved');
        $I->see('Profile of Bill','h1');
    }
}
?>
```

Moreover, Codeception can resolve dependencies recursively (when `A` depends on `B`, and `B` depends on `C` etc.) and handle parameters of primitive types with default values (like `$param = 'default'`). Of course, you are not allowed to have *cyclic dependencies*.

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
        - \Helper\Acceptance
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

Basically you can define different environments inside the `env` root, name them (`phantom`, `chrome` etc.),
and then redefine any configuration parameters that were set before.

You can also define environments in separate configuration files placed in the directory specified by `envs` option in
`paths` configuration:

```yaml
paths:
    envs: tests/_envs
```

Names of these files are used as environments names (e.g. `chrome.yml` or `chrome.dist.yml` for environment named `chrome`). 
You can generate a new file with environment configuration using `generate:environment` command:

```bash
$ php codecept.phar g:env chrome
```

and in there you can just specify options that you wish to override:

```yaml
modules:
    config:
        WebDriver:
            browser: 'chrome'
```

Environment configuration files are merged into the main configuration before suite configuration is merged.

You can easily switch between those configs by running tests with `--env` option. To run tests only for PhantomJS you need to pass `--env phantom` option:

```bash
$ php codecept.phar run acceptance --env phantom
```

To run tests in all 3 browsers, just list all the environments:

```bash
$ php codecept.phar run acceptance --env phantom --env chrome --env firefox
```

and tests will be executed 3 times, each time in a different browser.

It's also possible to merge multiple environments into one configuration by using comma as a separator:

```bash
$ php codecept.phar run acceptance --env dev,phantom --env dev,chrome --env dev,firefox
```

Configuration is merged in the given order. This way you can easily create multiple combinations of your environment configurations.

Depending on environment you may choose which tests are to be executed.
For example, you might need some tests to be executed only in Firefox, and a few tests only in Chrome.

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
$scenario->env(['phantom', 'firefox']);
?>
```

If merged environments are used, then you can specify multiple required environments (order is ignored):

```php
<?php
$scenario->env('firefox,dev');
$scenario->env('dev,phantom');
?>
```

This way you can easily control which tests will be executed for each environments.

### Current values

Sometimes you may need to change test behavior in realtime. For instance, behavior of the same test may differ in Firefox and in Chromium.
In runtime we can receive current environment name, test name, or list of enabled modules by calling `$scenario->current()` method.
    
```php
<?php
// retrieve current environment
$scenario->current('env'); 

// list of all enabled modules
$scenario->current('modules'); 

// test name
$scenario->current('name');
?>
```

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
$scenario->group(['admin', 'editor']);
// or
$scenario->groups(['admin', 'editor'])

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


## Custom Reporters

In order to customize output you can use Extensions, as it is done in [SimpleOutput Extension](https://github.com/Codeception/Codeception/blob/master/ext%2FSimpleOutput.php).
But what if you need to change output format of XML or JSON results triggered with `--xml` or `--json` options?
Codeception uses printers from PHPUnit and overrides some of them. If you need to customize one of standard reporters you can override them too.
If you are thinking on implementing your own reporter you should add `reporters` section to `codeception.yml` and override one of standard printer classes to your own:

```yaml
reporters: 
    xml: Codeception\PHPUnit\Log\JUnit
    html: Codeception\PHPUnit\ResultPrinter\HTML
    tap: PHPUnit_Util_Log_TAP
    json: PHPUnit_Util_Log_JSON
    report: Codeception\PHPUnit\ResultPrinter\Report
```

All reporters implement [PHPUnit_Framework_TestListener](https://phpunit.de/manual/current/en/extending-phpunit.html#extending-phpunit.PHPUnit_Framework_TestListener) interface.
It is recommended to read the code of original reporter before overriding it.

## Conclusion

Codeception is a framework which may look simple at first glance. But it allows you to build powerful tests with a single API, refactor them, and write them faster using the interactive console. Codeception tests can be easily organized in groups or Cest classes.
