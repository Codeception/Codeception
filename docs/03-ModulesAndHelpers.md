# Modules and Helpers

Codeception uses modularity to create a comfortable testing environment for every test suite you write. 
Modules allow you to choose the actions and assertions that can be performed in tests.

All actions and assertions that can be performed by the Guy object in a class are defined in modules. It might look like Codeception limits you in testing, but it's not true. You can extend the testing suite with your own actions and assertions, writing them into a custom module.

Let's look at this test.

``` php
<?php

$I = new TestGuy($scenario);
$I->amOnPage('/');
$I->see('Hello');
$I->seeInDatabase('users', array('id' => 1));
$I->seeFileFound('running.lock');
?>
```

It can operate with different entities: the web page can be loaded with the Symfony1 module, the database assertion uses the Db module, and file state can be checked with the Filesystem module. 

Modules are attached to Guy classes in the suite config.
For example, in `tests/functional.suite.yml` we should see.

```yaml
class_name: TestGuy
modules:
    enabled: [Symfony1, Db, Filesystem]
```

The TestGuy class has it's methods defined in modules. Actually, it doesn't contain any of them, but acts as a proxy for them. It knows which module executes this action and passes parameters into it. To make your IDE see all of the TestGuy methods, you use the `build` command. It generates the definition of the TestGuy class by copying the signatures from the configured modules.

## Standard Modules

Codeception has many bundled modules which will help you run tests for different purposes and in different environments. The number of modules is not constant -- it's supposed to grow as more frameworks and ORMs are supported.
See all of them listed in the main menu under Modules.

All of these modules are documented. You can review their detailed references on [GitHub](https://github.com/DavertMik/Codeception/tree/master/docs/modules).

## Helpers

Codeception doesn't restrict you to only the modules from the main repository. No doubt your project might need your own actions added to the test suite. By running the `bootstrap` command, Codeception generates three dummy modules for you, one for each of the newly created suites. These custom modules are called 'Helpers', and they can be found in the `tests/_helpers` path. 

It's a good idea to define missing actions or assertion commands in helpers. 

Let's say we are going to extend the TestHelper class. By default it's linked with a TestGuy class and a functional test suite.

``` php
<?php
namespace Codeception\Module;
// here you can define custom functions for TestGuy

class TestHelper extends \Codeception\Module
{
}
?>
```

As for actions, everything is quite simple. Every action you define is a public function. Write any public method, run the `build` command, and you will see the new function added into the TestGuy class. Note: Public methods prefixed by `_` are treated as hidden and won't be added to your Guy class. 

Assertions can be a bit tricky. First of all, it's recommended to prefix all your assert actions with `see` or `dontSee`. In Codeception philosophy, all tests are performed by humans, i.e. guys. The expected result they see (or don't see) is what we use for the assertion.

Name your assertions like this:

```php
seePageReloaded();
seeClassIsLoaded($classname);
dontSeeUserExist($user);
```
And then use them in your tests:

```php
<?php
$I = new TestGuy($scenario);
$I->seePageReloaded();
$I->seeClassIsLoaded('TestGuy');
$I->dontSeeUserExist($user);
?>
```

Every `see` or `dontSee` function requires at least one assert. Codeception uses PHPUnit assertions.

You can define asserts by using assertXXX methods of the Codeception module.
As Codeception uses PHPUnit asserts, if you miss some of the asserts, you can use PHPUnit static methods from the `PHPUnit_Framework_Assert` class for more.

``` php
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

``` php
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

Also each module has special `$this->assert` and `$this->assertNot` methods. They both take the same arguments and are useful if you need to define both positive and negative assertions in your module. These functions take an array as a parameter, where the first value of the array is the name of the PHPUnit assert function.

```php
<?php

$this->assert(array('Equals',$int,3));
$this->assertNot(array('internalType',$int,'bool'));
$this->assert(array('Contains', array(3,5,9), 3));
?>
```
Let's see how to define both `see` and `dontSee` actions without code duplication.

```php
<?php

public function seeClassExist($class)
{
    $this->assert($this->proceedSeeClassExist($class));
}

public function dontSeeClassExist($class)
{
    $this->assertNot($this->proceedSeeClassExist($class));
}

protected function proceedSeeClassExist($class)
{
    return array('True',get_class($class));
}
?>
```
For `dontSeeClassExist`, the `assertFalse` will be called.

### Resolving Collisions

What happens if you have two modules which contain the same named actions?
Codeception allows you to override actions by changing the module order.
The action from the second module will be loaded and the action from the first will be ignored.
The order of the modules can be defined in the suite config.

### Connecting Modules

It's possible that you will need to access internal data or functions from other modules. For example, for your module you might need a connection from Doctrine, or a web browser from Symfony.

Modules can interact with each other through the `getModule` method. Please note that this method will throw an exception if the required module was not loaded.

Let's imagine that we are writing a module which reconnects to a database. It's supposed to use the dbh connection value from the Db module.

```php
<?php

function reconnectToDatabase() {
    $dbh = $this->getModule('Db')->dbh;
    $dbh->close();
    $dbh->open();
}
?>
```
By using the `getModule` function you get access to all of the public methods and properties of the requested module.
The dbh property was defined as public specifically to be available to other modules.

That technique may be also useful if you need to perform a sequence of actions taken from other modules.

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

### Undefined Actions in Helpers

In case you have an action in test which is not defined yet, you can automatically create a stub method for it in the corresponding helper. To do so, you can use an `analyze` command which scans all tests and searches for actions that do not exist in any of the connected modules.

So, you can assign writing tests to non-technical guys or QAs. In case they lack some actions they define them in test.

``` php
<?php
$I->doManyCoolThings();
?>
```

By running the `analyze` command you will be asked if you want to add `doManyCoolThings` to the current Helper.


### Hooks

Each module can handle events from the running test. A module can be executed before the test starts, or after the test is finished. This can be useful for bootstrap/cleanup actions.
You can also define special behavior for when the test fails. This may help you in debugging the issue.
For example, the PhpBrowser module saves the current webpage to the log directory if the test fails.

All hooks are defined in `\Codeception\Module` and are listed here. You are free to redefine them in your module.

```php
<?php

    // HOOK: used after configuration is loaded
    public function _initialize() {
    }

	// HOOK: on every Guy class initialization
	public function _cleanup() {
	}

	// HOOK: before each step
	public function _beforeStep(\Codeception\Step $step) {
	}

	// HOOK: after each  step
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

Please note that methods with a `_` prefix are not added to the Guy class. This allows them to be defined as public, but used only for internal purposes.

### Debug

As we mentioned, the `_failed` hook can help in debugging a failed test. You have the opportunity to save the current test's state and show it to the user.

But you are not limited to this. Each module can output internal values that may be useful during debug.
For example, the PhpBrowser module prints the response code and current URL every time it moves to a new page.
Thus, modules are not black boxes. They are trying to show you what is happening during the test. This makes debugging your tests less painful.

To display additional information, use the `debug` and `debugSection` methods of the module.
Here is an example of how it works for PhpBrowser:

```php
<?php
    $this->debug('Request ('.$method.'): '.$uri.' '. json_encode($params));
    $browser->request($method, $uri, $params);
    $this->debug('Response code: '.$this->session->getStatusCode());
?>    
```

This test, running with PhpBrowser module in debug mode, will print something like this:

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
The next time you start the suite without setting these values, an exception will be thrown. 

For optional parameters, you should set default values. The `$config` property is used to define optional parameters as well as their values. In the Selenium module we use the default Selenium Server address and port. 

```php
<?php
class Selenium extends \Codeception\Util\MinkJS
{
    protected $requiredFields = array('browser', 'url');    
    protected $config = array('host' => '127.0.0.1', 'port' => '4444');
?>    
```

The host and port parameter can be redefined in the suite config. Values are set in the `modules:config` section of the configuration file.

```yaml
modules:
    enabled:
        - Selenium
        - Db
    config:
        Selenium:
            url: 'http://mysite.com/'
            browser: 'firefox'
        Db:
            cleanup: false
            repopulate: false
```

Optional and mandatory parameters can be accessed through the `$config` property. Use `$this->config['parameter']` to get it's value. 

### Dynamic Configuration

*new in 1.6.2*

If you want to reconfigure module in run time, you can use the `_reconfigure` method of the module.
You may call it from helper class and pass in all the fields you want to change.

``` php
<?php
$this->getModule('Selenium2')->_reconfigure(array('browser' => 'chrome'));
?>
```

By the end of a test all your changes will be rolled back to the original config values.

### Extension options

*new in 1.6.3*

Like each class, a Helper can be inherited from a module.

``` php
<?php
namespace Codeception\Module;
class MySeleniumHelper extends Selenium2 {
}
?>
```

In an inherited helper, you replace implemented methods with your own realization.
You can also replace `_before` and `_after` hooks, which might be an option when you need to customize starting and stopping of a testing session.

If some of the methods of the parent class should not be used in child module, you can disable them. Codeception has several options for this.

``` php
<?php
namespace Codeception\Module;
class MySeleniumHelper extends Selenium2 {
    // disable all inherited actions
    public static $includeInheritedActions = false;

    // include only "see" and "click" actions
    public static $onlyActions = array('see','click');

    // exclude "seeElement" action
    public static $excludeActions = array('seeElement');
}
?>
```

Option `$includeInheritedActions` set to false adds the ability to create aliases for parent methods.
 It allows you to resolve conflicts between modules. Let's say we want to use the `Db` module with our `SecondDbHelper`
 that actually inherits from `Db`. How can we use `seeInDatabase` methods from both modules? Let's find out.

``` php
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

`$includeInheritedActions` set to false won't include the methods from parent classes into the generated Guy.
Still you can use inherited methods in your helper class.

## Conclusion

Modules are the true power of Codeception. They are used to emulate multiple inheritance for Guy classes (CodeGuy, TestGuy, WebGuy, etc).
Codeception provides modules to emulate web requests, access data, interact with popular PHP libraries, etc.
For your application you might need custom actions. These can be defined in helper classes.
If you have written a module that may be useful to others, share it.
Fork the Codeception repository, put the module into the __src/Codeception/Module__ directory, and send a pull request. Many thanks if you do so.
