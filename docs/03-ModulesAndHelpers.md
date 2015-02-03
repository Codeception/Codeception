# Modules and Helpers

Codeception uses modularity to create a comfortable testing environment for every test suite you write. 
Modules allow you to choose the actions and assertions that can be performed in tests.

All actions and assertions that can be performed by the Tester object in a class are defined in modules. It might look like Codeception limits you in testing, but that's not true. You can extend the testing suite with your own actions and assertions, by writing them into a custom module.

Let's look at the following test:

```php
<?php
$I = new FunctionalTester($scenario);
$I->amOnPage('/');
$I->see('Hello');
$I->seeInDatabase('users', array('id' => 1));
$I->seeFileFound('running.lock');
?>
```

It can operate with different entities: the web page can be loaded with the PhpBrowser module, the database assertion uses the Db module, and file state can be checked with the Filesystem module. 

Modules are attached to Actor classes in the suite config.
For example, in `tests/functional.suite.yml` we should see:

```yaml
class_name: FunctionalTester
modules:
    enabled: [PhpBrowser, Db, Filesystem]
```

The FunctionalTester class has its methods defined in modules. Actually it doesn't contain any of them, but rather acts as a proxy. It knows which module executes this action and passes parameters into it. To make your IDE see all of the FunctionalTester methods, you use the `build` command. It generates the definition of the FunctionalTester class by copying the signatures from the corresponding modules.

## Standard Modules

Codeception has many bundled modules which will help you run tests for different purposes and different environments. The number of modules is not constant -- it's supposed to grow as more frameworks and ORMs are supported. See all of them listed in the main menu under the Modules section.

All of these modules are documented. You can review their detailed references on [GitHub](https://github.com/Codeception/Codeception/tree/master/docs/modules).

## Helpers

Codeception doesn't restrict you to only the modules from the main repository. No doubt your project might need your own actions added to the test suite. By running the `bootstrap` command, Codeception generates three dummy modules for you, one for each of the newly created suites. These custom modules are called 'Helpers', and they can be found in the `tests/_support` directory.

It's a good idea to define missing actions or assertion commands in helpers.

Note: Helpers class names must end with "*Helper.php"

Let's say we are going to extend the FunctionalHelper class. By default it's linked with a FunctionalTester class and functional test suite.

```php
<?php
namespace Codeception\Module;
// here you can define custom functions for FunctionalTester

class FunctionalHelper extends \Codeception\Module
{
}
?>
```

As for actions, everything is quite simple. Every action you define is a public function. Write any public method, run the `build` command, and you will see the new function added into the FunctionalTester class.

Note: Public methods prefixed by `_` are treated as hidden and won't be added to your Actor class. 

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
$I = new FunctionalTester($scenario);
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

Just type `$this->assert` to see all of them.


### Resolving Collisions

What happens if you have two modules that contain the same named actions?
Codeception allows you to override actions by changing the module order.
The action from the second module will be loaded and the action from the first one will be ignored.
The order of the modules can be defined in the suite config.

### Connecting Modules

It's possible that you will need to access internal data or functions from other modules. For example, for your module you might need a connection from Doctrine, or a web browser from Symfony.

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

By using the `getModule` function, you get access to all of the public methods and properties of the requested module. The dbh property was defined as public specifically to be available to other modules.

This technique may be also useful if you need to perform a sequence of actions taken from other modules.

For example:

```php
<?php
function seeConfigFilesCreated()
{
    $filesystem = $this->getModule('Filesystem');
    $filesystem->seeFileFound('codeception.yml');
    $filesystem->openFile('codeception.yml');
    $filesystem->seeInFile('paths');
}
?>
```

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
    $client->request($method, $uri, $params);
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

Modules can be configured from the suite config file, or globally from `codeception.yml`.

Mandatory parameters should be defined in the `$requiredFields` property of the module class. Here is how it is done in the Db module:

```php
<?php
class Db extends \Codeception\Module {
    protected $requiredFields = array('dsn', 'user', 'password');
?>
```

The next time you start the suite without setting one of these values, an exception will be thrown. 

For optional parameters, you should set default values. The `$config` property is used to define optional parameters as well as their values. In the WebDriver module we use default Selenium Server address and port. 

```php
<?php
class WebDriver extends \Codeception\Module
{
    protected $requiredFields = array('browser', 'url');    
    protected $config = array('host' => '127.0.0.1', 'port' => '4444');
?>    
```

The host and port parameter can be redefined in the suite config. Values are set in the `modules:config` section of the configuration file.

```yaml
modules:
    enabled:
        - WebDriver
        - Db
    config:
        WebDriver:
            url: 'http://mysite.com/'
            browser: 'firefox'
        Db:
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

Like each class, a Helper can be inherited from a module.

```php
<?php
namespace Codeception\Module;
class MySeleniumHelper extends \Codeception\Module\WebDriver  {
}
?>
```

In an inherited helper, you replace implemented methods with your own realization.
You can also replace `_before` and `_after` hooks, which might be an option when you need to customize starting and stopping of a testing session.

If some of the methods of the parent class should not be used in a child module, you can disable them. Codeception has several options for this:

```php
<?php
namespace Codeception\Module;
class MySeleniumHelper extends \Codeception\Module\WebDriver 
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
namespace Codeception\Module;
class SecondDbHelper extends Db {
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

Modules are the true power of Codeception. They are used to emulate multiple inheritances for Actor classes (UnitTester, FunctionalTester, AcceptanceTester, etc).
Codeception provides modules to emulate web requests, access data, interact with popular PHP libraries, etc.
For your application you might need custom actions. These can be defined in helper classes.
If you have written a module that may be useful to others, share it.
Fork the Codeception repository, put the module into the __src/Codeception/Module__ directory, and send a pull request.
