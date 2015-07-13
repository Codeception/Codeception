# Reusing Test Code

Codeception uses modularity to create a comfortable testing environment for every test suite you write. 
Modules allow you to choose the actions and assertions that can be performed in tests.

## What are Actors

All actions and assertions that can be performed by the Actor object in a class are defined in modules. It might look like Codeception limits you in testing, but that's not true. You can extend the testing suite with your own actions and assertions, by writing them into a custom module, called Helper. We will get back to this later in this chapter, but now let's look at the following test:

```php
<?php
$I = new AcceptanceTester($scenario);
$I->amOnPage('/');
$I->see('Hello');
$I->seeInDatabase('users', ['id' => 1]);
$I->seeFileFound('running.lock');
?>
```

It can operate with different entities: the web page can be loaded with the PhpBrowser module, the database assertion uses the Db module, and file state can be checked with the Filesystem module. 

Modules are attached to Actor classes in the suite config.
For example, in `tests/functional.suite.yml` we should see:

```yaml
class_name: AcceptanceTester
modules:
    enabled: 
        - PhpBrowser:
            url: http://localhost
        - Db
        - Filesystem
```

The FunctionalTester class has its methods defined in modules. But let's see what's inside `AcceptanceTester` class, which is located inside `tests/_support` directory:

```php
<?php
/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

   /**
    * Define custom actions here
    */

}
?>
```

The most important part is `_generated\AcceptanceTesterActions` trait, which is used as a proxy for enabled modules. It knows which module executes which action and passes parameters into it. This trait was created by running `codecept build` and is regenerated each time module or configuration changes.

It is recommended to put widely used actions inside an Actor class. A good example of such case may be `login` action which probably be actively involved in acceptance or functional testing.

``` php
<?php
class AcceptanceTester extends \Codeception\Actor
{
    // do not ever remove this line!
    use _generated\AcceptanceTesterActions;

    public function login($name, $password)
    {
        $I = $this;
        $I->amOnPage('/login');
        $I->submitForm('#loginForm' [
            'login' => $name, 
            'password' => $password
        ]);
        $I->see($name, '.navbar');
    } 
}
?>
```

Now you can use `login` method inside your tests:

```php
<?php
$I = new AcceptanceTester($scenario);
$I->login('miles', '123456');
?>
```

However, implementing all actions for a reuse in one actor class may lead to breaking the [Single Responsibility Principle](http://en.wikipedia.org/wiki/Single_responsibility_principle). 

## StepObjects

If `login` method defined in Actor class may be used in 90% of your tests,
StepObjects are great if you need some common functionality for a group of tests. Let's say you are going to test and admin area of a site. Probably you won't need the same actions from admin area while testing the frontend, so it's a good idea to move those admin-specific into their own class. We will call such class a StepObject.

Lets create an Admin StepObject with generator, by specifying test suite, and passing method expected names on prompt.

```bash
$ php codecept.phar generate:stepobject acceptance Admin
```

You will be asked to enter action names, but it's optional. Enter one at a time, and press Enter. After specifying all needed actions, leave empty line to go on to StepObject creation.

```bash
$ php codecept.phar generate:stepobject acceptance Admin
Add action to StepObject class (ENTER to exit): loginAsAdmin
Add action to StepObject class (ENTER to exit):
StepObject was created in /tests/acceptance/_support/Step/Acceptance/Admin.php
```

It will generate class in `/tests/_support/Step/Acceptance/Admin.php` similar to this:

```php
<?php
namespace Step\Acceptance;

class Admin extends \AcceptanceTester
{
    public function loginAsAdmin()
    {
        $I = $this;
    }
}
?>
```

As you see, this class is very simple. It extends `AcceptanceTester` class, thus, all methods and properties of `AcceptanceTester` are available for usage in it.

`loginAsAdmin` method may be implemented like this:

```php
<?php
namespace Step\Acceptance;

class Member extends \AcceptanceTester
{
    public function loginAsAdmin()
    {
        $I = $this;
        $I->amOnPage('/admin');
        $I->fillField('username', 'admin');
        $I->fillField('password', '123456');
        $I->click('Login');
    }
}
?>
```

In tests you can use a StepObject by instantiating `Step\Acceptance\Admin` instead of `AcceptanceTester`.

```php
<?php
use Step/Acceptance/Admin as AdminTester;

$I = new AdminTester($scenario);
$I->loginAsAdmin();
?>
```

Same as above, StepObject can be instanticated automatically by Dependency Injection Container, when used inside Cest format:

```php
<?php
class UserCest 
{    
    function showUserProfile(\Step\Acceptance\Admin $I)
    {
        $I->loginAsAdmin();
        $I->amOnPage('/admin/profile');
        $I->see('Admin Profile', 'h1');        
    }
}
?>
```

If you have complex interaction scenario you may use several step objects in one test. If you feel like adding too many actions into your Actor class (which is AcceptanceTester in this case) consider to move some of them into separate StepObjects.


## PageObjects

For acceptance and functional testing we will need not only to have common actions to be reused accross different tests, we should have buttons, links, and form fields to be reused as well. For those cases we need to implement 
[PageObject pattern](http://code.google.com/p/selenium/wiki/PageObjects), which is widely used by test automation engineers. The PageObject pattern represents a web page as a class and the DOM elements on that page as its properties, and some basic interactions as its methods.
PageObjects are very important when you are developing a flexible architecture of your tests. Please do not hardcode complex CSS or XPath locators in your tests but rather move them into PageObject classes.

Codeception can generate a PageObject class for you with command:

```bash
$ php codecept.phar generate:pageobject Login
```

This will create a `Login` class in `tests/_support/Page`. The basic PageObject is nothing more than an empty class with a few stubs.
It is expected you will get it populated with UI locators of a page it represents and then those locators will be used on a page.
Locators are represented with public static properties:

```php
<?php
namespace Page;

class Login
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
use Page\Login as LoginPage;

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

But let's move further. A PageObject concept also defines that methods for the page interaction should also be stored in a PageObject class. It now stores a passed instance of an Actor class. An AcceptanceTester can be accessed via `AcceptanceTester` property of that class. Let's define a `login` method in this class.

```php
<?php
class UserLoginPage
{
    // include url of current page
    public static $URL = '/login';

    /**
     * @var AcceptanceTester
     */
    protected $tester;

    public function __construct(AcceptanceTester $I)
    {
        $this->tester = $I;
    }

    public function login($name, $password)
    {
        $I = $this->tester;

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
$loginPage = new \Page\Login($I);
$loginPage->login('bill evans', 'debby');
$I->amOnPage('/profile');
$I->see('Bill Evans Profile', 'h1');
?>
```

If you write your scenario-driven tests in Cest format (which is the recommended approach), you can bypass manual creation of a PageObject and delegate this task to Codeception. If you specify which object you need for a test, Codeception will try to create it using the dependency injection container. In the case of a PageObject you should declare a class as a parameter for a test method:

```php
<?php
class UserCest 
{    
    function showUserProfile(AcceptanceTester $I, \Page\Login $loginPage)
    {
        $loginPage->login('bill evans', 'debby');
        $I->amOnPage('/profile');
        $I->see('Bill Evans Profile', 'h1');        
    }
}
?>
```

The dependency nijection container can construct any object that require any known class type. For instance, `Page\Login` required `AcceptanceTester`, and so it was injected into `Page\Login` constructor, and PageObject was created and passed into method arguments. You should specify explicitly the types of requried objects for Codeception to know what objects should be created for a test. Dependency Injection will be described in the next chapter. 

## Modules and Helpers

In the examples above we only grouped actions into one. What happens when we need to create a custom action? 
In this case it's a good idea to define missing actions or assertion commands in custom modules, which are called Helpers. They can be found in the `tests/_support/Helper` directory.

<div class="alert alert-info">
We already know how to create a custom login method in AcceptanceTester class. We used actions from standard modules and combined them to make it easier for a user to log in. Helpers allow us to create **new actions** unrelated to standard modules (or using their internals).  
</div>


```php
<?php
namespace Helper;
// here you can define custom functions for FunctionalTester

class Functional extends \Codeception\Module
{
}
?>
```

As for actions, everything is quite simple. Every action you define is a public function. Write any public method, run the `build` command, and you will see the new function added into the FunctionalTester class.


<div class="alert alert-info">
Public methods prefixed by `_` are treated as hidden and won't be added to your Actor class.
</div>

Assertions can be a bit tricky. First of all, it's recommended to prefix all your assert actions with `see` or `dontSee`.

Name your assertions like this:

```php
<?php
$I->seePageReloaded();
$I->seeClassIsLoaded($classname);
$I->dontSeeUserExist($user);
?>
```
And then use them in your tests:

```php
<?php
$I->seePageReloaded();
$I->seeClassIsLoaded('FunctionalTester');
$I->dontSeeUserExist($user);
?>
```

You can define asserts by using assertXXX methods in modules. Not all PHPUnit assert methods are included in modules, but you can use PHPUnit static methods from the `PHPUnit_Framework_Assert` class to leverage all of them.

```php
<?php

function seeClassExist($class)
{
    $this->assertTrue(class_exists($class));
    // or
    \PHPUnit_Framework_Assert::assertTrue(class_exists($class));
}
?>
```

In your helpers you can use these assertions:

```php
<?php

function seeCanCheckEverything($thing)
{
    $this->assertTrue(isset($thing), "this thing is set");
    $this->assertFalse(empty($any), "this thing is not empty");
    $this->assertNotNull($thing, "this thing is not null");
    $this->assertContains("world", $thing, "this thing contains 'world'");
    $this->assertNotContains("bye", $thing, "this thing doesn`t contain 'bye'");
    $this->assertEquals("hello world", $thing, "this thing is 'Hello world'!");
    // ...
}
?>
```

### Resolving Collisions

What happens if you have two modules that contain the same named actions?
Codeception allows you to override actions by changing the module order.
The action from the second module will be loaded and the action from the first one will be ignored.
The order of the modules can be defined in the suite config.

However, some of modules may conflict with each other. In order to avoid confusion which module is used in the first place, Framework modules, PhpBrowser, and WebDriver can't be used together. The `_conflicts` method of a module is used to specify which class or interface it conflicts with. Codeception will throw an exception if there will be a module enabled which matches the provided criteria.

### Accessing Other Modules

It's possible that you will need to access internal data or functions from other modules. For example, for your module you might need to access responses or internal actions of modules.

Modules can interact with each other through the `getModule` method. Please note that this method will throw an exception if the required module was not loaded.

Let's imagine that we are writing a module that reconnects to a database. It's supposed to use the dbh connection value from the Db module.

```php
<?php

function reconnectToDatabase() {
    $dbh = $this->getModule('Db')->dbh;
    $dbh->close();
    $dbh->open();
}
?>
```

By using the `getModule` function, you get access to all of the public methods and properties of the requested module. The `dbh` property was defined as public specifically to be available to other modules.

Modules may also contain methods that are exposed for use in helper classes. Those methods start with `_` prefix and are not available in Actor classes, so can be accessed only from modules and extensions.

You should use them to write your own actions using module internals.
   
```php
<?php
function seeNumResults($num)
{
    // retrieving webdriver session
    /**@var $table \Facebook\WebDriver\WebDriverElement */
    $table = $this->getModule('WebDriver')->_findElements('#result');
    $this->assertEquals('table', $table->getTagName());
    $results = $el->findElements('tr');

    // asserting that table contains exactly $num rows
    $this->assertEquals($num, count($results));
}
?>
```

In this example we use API of <a href="https://github.com/facebook/php-webdriver">facebook/php-webdriver</a> library, a Selenium WebDriver client a module is build on. 
You can also access `webDriver` property of a module to get access to `Facebook\WebDriver\RemoteWebDriver` instance for direct Selenium interaction.
 
### Hooks

Each module can handle events from the running test. A module can be executed before the test starts, or after the test is finished. This can be useful for bootstrap/cleanup actions.
You can also define special behavior for when the test fails. This may help you in debugging the issue.
For example, the PhpBrowser module saves the current webpage to the `tests/_output` directory when a test fails.

All hooks are defined in `\Codeception\Module` and are listed here. You are free to redefine them in your module.

```php
<?php

    // HOOK: used after configuration is loaded
    public function _initialize() {
    }

    // HOOK: on every Actor class initialization
    public function _cleanup() {
    }

    // HOOK: before each suite
    public function _beforeSuite($settings = array()) {
    }

    // HOOK: after suite
    public function _afterSuite() {
    }    

    // HOOK: before each step
    public function _beforeStep(\Codeception\Step $step) {
    }

    // HOOK: after each step
    public function _afterStep(\Codeception\Step $step) {
    }

    // HOOK: before test
    public function _before(\Codeception\TestCase $test) {
    }

    // HOOK: after test
    public function _after(\Codeception\TestCase $test) {
    }

    // HOOK: on fail
    public function _failed(\Codeception\TestCase $test, $fail) {
    }
?>
```

Please note that methods with a `_` prefix are not added to the Actor class. This allows them to be defined as public but used only for internal purposes.

### Debug

As we mentioned, the `_failed` hook can help in debugging a failed test. You have the opportunity to save the current test's state and show it to the user, but you are not limited to this.

Each module can output internal values that may be useful during debug.
For example, the PhpBrowser module prints the response code and current URL every time it moves to a new page.
Thus, modules are not black boxes. They are trying to show you what is happening during the test. This makes debugging your tests less painful.

To display additional information, use the `debug` and `debugSection` methods of the module.
Here is an example of how it works for PhpBrowser:

```php
<?php
    $this->debugSection('Request', $params);
    $this->client->request($method, $uri, $params);
    $this->debug('Response Code: ' . $this->client->getStatusCode());
?>    
```

This test, running with the PhpBrowser module in debug mode, will print something like this:

```bash
I click "All pages"
* Request (GET) http://localhost/pages {}
* Response code: 200
```



### Configuration

Modules and Helpers can be configured from the suite config file, or globally from `codeception.yml`.

Mandatory parameters should be defined in the `$requiredFields` property of the class. Here is how it is done in the Db module:

```php
<?php
class Db extends \Codeception\Module 
{
    protected $requiredFields = ['dsn', 'user', 'password'];
?>
```

The next time you start the suite without setting one of these values, an exception will be thrown. 

For optional parameters, you should set default values. The `$config` property is used to define optional parameters as well as their values. In the WebDriver module we use default Selenium Server address and port. 

```php
<?php
class WebDriver extends \Codeception\Module
{
    protected $requiredFields = ['browser', 'url'];    
    protected $config = ['host' => '127.0.0.1', 'port' => '4444'];
?>    
```

The host and port parameter can be redefined in the suite config. Values are set in the `modules:config` section of the configuration file.

```yaml
modules:
    enabled:
        - WebDriver:
            url: 'http://mysite.com/'
            browser: 'firefox'
        - Db:
            cleanup: false
            repopulate: false
```

Optional and mandatory parameters can be accessed through the `$config` property. Use `$this->config['parameter']` to get its value. 

### Dynamic Configuration

If you want to reconfigure a module at runtime, you can use the `_reconfigure` method of the module.
You may call it from a helper class and pass in all the fields you want to change.

```php
<?php
$this->getModule('WebDriver')->_reconfigure(array('browser' => 'chrome'));
?>
```

At the end of a test, all your changes will be rolled back to the original config values.

### Additional options

Another option to extend standard module functionality is to create a helper inherited from the module.

```php
<?php
namespace Helper;

class MyExtendedSelenium extends \Codeception\Module\WebDriver  {
}
?>
```

In this helper you replace implemented methods with your own implementation.
You can also replace `_before` and `_after` hooks, which might be an option when you need to customize starting and stopping of a testing session.

If some of the methods of the parent class should not be used in a child module, you can disable them. Codeception has several options for this:

```php
<?php
namespace Helper;

class MyExtendedSelenium extends \Codeception\Module\WebDriver 
{
    // disable all inherited actions
    public static $includeInheritedActions = false;

    // include only "see" and "click" actions
    public static $onlyActions = array('see','click');

    // exclude "seeElement" action
    public static $excludeActions = array('seeElement');
}
?>
```

Setting `$includeInheritedActions` to false adds the ability to create aliases for parent methods.
 It allows you to resolve conflicts between modules. Let's say we want to use the `Db` module with our `SecondDbHelper`
 that actually inherits from `Db`. How can we use `seeInDatabase` methods from both modules? Let's find out.

```php
<?php
namespace Helper;

class SecondDb extends \Codeception\Module\Db 
{
    public static $includeInheritedActions = false;

    public function seeInSecondDb($table, $data)
    {
        $this->seeInDatabase($table, $data);
    }
}
?>
```

Setting `$includeInheritedActions` to false won't include the methods from parent classes into the generated Actor.
Still, you can use inherited methods in your helper class.

## Conclusion

There are lots of ways to create reusable and readable tests. Group common actions into one and move them to Actor class or Step Objects. Move CSS and XPath locators into PageObjects. Write your custom actions and assertions in Helpers. Scenario-driven tests should not contain anything more complex than `$I->doSomething` commands. Following this approach will allow you to keep your tests clean, readable, stable and making them easy to maintain.