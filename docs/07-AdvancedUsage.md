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

In case you want to get a class-like structure for your Cepts, instead of plain PHP, you can use the Cest format.
It is very simple and is fully compatible with Cept scenarios. It means if you feel like your test is long enough and you want to split it - you can easily move it into classes. 

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

As you see, Cest class have no parent like `\Codeception\TestCase\Test` or `PHPUnit_Framework_TestCase`. That was done intentionally. This allows you to extend class any time you want by attaching any meta-testing class to it's parent. In meta class you can write common behaviors and workarounds that may be used in child class. But don't forget to make them `protected` so they won't be executed as a tests themselves.

Also you can define `_failed` method in Cest class which will be called if test finished with `error` or fail.

### Annotations

*added in 1.7.0*

You can control Cest files with annotations. You can use `@guy` annotation to pass Guy class different then set via config. This is quite useful if you want to pass a StepObject there (see below).

``` php
<?php
/**
 * @guy WebGuy\AdminSteps
 */
class AdminCest {

    function banUser(WebGuy\AdminSteps $I)
    {
        // ...
    }

}
?>
```
The guy annotation can be added to method DocBlock as well.

You can control execution flow with `@before` and `@after` annotations. You may move common actions into protected (non-test) methods and invoke them before or after the test method by putting them into annotations.

``` php
<?php
class ModeratorCest {

    protected function login(WebGuy $I)
    {
        $I->amOnPage('/login');
        $I->fillField('Username', 'miles');
        $I->fillField('Password', 'davis');
        $I->click('Login');
    }

    /**
     * @before login
     */
    function banUser(WebGuy $I)
    {
        $I->amOnPage('/users/charlie-parker');
        $I->see('Ban', '.button');
        $I->click('Ban');        
    }
}
?>
```

You can use `@before` and `@after` for included functions also. But you can't have multiple annotations in one method.

## Refactoring

As test base growth they will require refactoring, sharing common variables and behaviors. The classical example for this is `login` action which will be called for maybe every test of your test suite. It's wise to make it written one time and use it in all tests. 

It's pretty obvious that for such cases you can use your own PHP classes to define such methods. 

``` php
<?php class TestCommons 
{
    public static $username = 'jon';
    public static $password = 'coltrane';

    public static function logMeIn($I)
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

If you caught the idea, let's learn of built-in features for structuring your test code.
We will discover PageObject and StepObject patterns implementation in Codeception.

## PageObjects

[PageObject pattern](http://code.google.com/p/selenium/wiki/PageObjects) is widely used by test automation engineers. The Page Object pattern represents a web page as a class and the DOM elements on that page as properties, and some basic interactions as a methods.
PageObjects are very important when you are developing a flexible architecture of your tests. Please do not hardcode complex CSS or XPath locators in your tests, but rather move them into PageObject classes.

Codeception can generate a pageobject class for you with command:

```
php codecept.phar generate:pageobject Login
```

This will create a `LoginPage` class in `tests/_pages`. The basic pageobject is nothing more then empty class with a few stubs.
It is expected you will get it populated with UI locators of a page its represent and then those locators will be used on a page.
Locators are represented with public static properties:

``` php
<?php
class LoginPage
{
    const URL = '/login';

    static $usernameField = '#mainForm #username';
    static $passwordField = '#mainForm input[name=password]';
    static $loginButton = "#mainForm input[type=submit]";
}
?>
```

And this is how this page object can be used in a test:

``` php
<?php
$I = new WebGuy($scenario);
$I->wantTo('login to site');
$I->amOnPage(LoginPage::URL);
$I->fillField(LoginPage::$usernameField, 'bill evans');
$I->fillField(LoginPage::$passwordField, 'debby');
$I->click(LoginPage::$loginButton);
$I->see('Welcome, bill');
?>
```
As you see you can freely change markup of your login page and all the tests interacting with this page, will have their locators updated according to properties of LoginPage class. 

But lets move further. A PageObject concept also defines that methods for the page interaction should also be stored in a PageObject class.
This can't be done in `LoginPage` class we just generated. Because this class is accessible across all test suites, we do not know which guy class will be used for interaction. Thus, we will need to generate another page object. In this case we will explicitly define the suite to be used:

```
php codecept.phar generate:pageobject acceptance UserLogin
```

*we called this class UserLogin for not to get into conflict with Login class we created before*

This generated `UserLoginPage` class looks almost the same way as LoginPage class we had before, with one differences. A now stores an instance of Guy object passed. A webguy can be accessed via `webGuy` property of that class. Let's define a `login` method in this class.

``` php
<?php

class UserLoginPage
{
    // include url of current page
    const URL = '/login';

    /**
     * @var WebGuy;
     */
    protected $webGuy;

    public function __construct(WebGuy $I)
    {
        $this->webGuy = $I;
    }

    public static function of(WebGuy $I)
    {
        return new static($I);
    }

    public function login($name, $password)
    {
        $I = $this->webGuy;

        $I->amOnPage(self::URL);
        $I->fillField(LoginPage::$usernameField, $name);
        $I->fillField(LoginPage::$passwordField, $password);
        $I->click(LoginPage::$loginButton);

        return $this;
    }    
}
?>
```

And here is an example of how this PageObject can be used in a test.

``` php
<?php
$I = new WebGuy($scenario);
UserLoginPage::of($I)->login('bill evans', 'debby');
?>
```

Probably we should merge the `UserLoginPage` and `LoginPage` classes as they do play the same role. But LoginPage can be used both in functional and acceptance tests, and UserLoginPage only in tests with a WebGuy. So it's up to you to use global page objects, or local per suite page objects. If you feel like your functional tests have much in common with acceptance, you should store locators in global PageObject class and use StepObjects as an alternative to behavioral PageObjects.


## StepObjects

StepObjects pattern came from BDD frameworks. StepObject class contains a bunch of common actions that may be used widely in different tests.
The `login` method we used above can be a good example of such method. Similarly actions for creating/updating/deleting resources should be moved to StepObject too. Let's create a StepObject class and see what is it like. 

Lets create a "Member" Steps class, a generator will prompt you for methods to include, so let's add a `login` to it.

```
php codecept.phar generate:stepobject acceptance Member
```

This will generate a similar class to `tests/acceptance/_steps/MemberSteps.php`.

``` php
<?php
namespace WebGuy;

class MemberSteps extends \WebGuy
{
    function login()
    {
        $I = $this;

    }
}
?>
```

As you see this class is very simple. But it inherits from `WebGuy` class, thus contains all its methods.
`login` method can be implemented like this:

``` php
<?php
namespace WebGuy;

class MemberSteps extends \WebGuy
{
    function login($name, $password)
    {
        $I = $this;
        $I->amOnPage(LoginPage::URL);
        $I->fillField(LoginPage::$usernameField, $name);
        $I->fillField(LoginPage::$passwordField, $password);
        $I->click(LoginPage::$loginButton);
    }
}
?>
```

In tests you can use a StepObject by instantiating a MemberSteps class instead of WebGuy.

``` php
<?php
$I = new WebGuy\MemberSteps($scenario);
$I->login('bill evans','debby');
?>
```

As you see, StepObject class looks much simpler and readable then classical PageObject.
As an alternative to StepObject we could use methods of `WebHelper` class. In a helper we do not have an access to `$I` object itself,
thus it's better to use Helpers should be used to implement new actions, and StepObjects to combine common scenarios.

## Conclusion

Codeception is a framework which may look simple at first sight. But it allows you to build powerful test with one  APIs, refactor them, and write them faster using interactive console. Codeception tests can easily be organized with groups or Cest classes, store locators in PageObjects and combine common steps in StepObjects.

Probably too much features for the one framework. But nevertheless Codeception follows the KISS pricinple: it's easy to start, easy to learn, easy to extend.
