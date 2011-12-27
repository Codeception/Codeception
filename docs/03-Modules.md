# Modules

Codeception uses modularity to create comfortable testing environment for every test suite you write. 
Modules allows you choose actions and assertions that can be perform in tests.

All actions and assertions that can be performed by Guy object in class are defined in modules. It might look that Codeception limits you in testing, still it's not true. You can extend testing suite with your own actions and assertions, writing them into custom module.

Let's look at this test.

``` php
<?php

$I = new TestGuy($scenario);
$I->amOnPage('/');
$I->see('Hello');
$I->seeInDatabase('users', array('id' => 1));
$I->seeFileFound('running.lock');
```

It can operate with different entities: the web page can be loaded with Symfony1 module, the database assertion uses Db module, and file state can be checked with Filesystem module. 

Modules are attached to Guy-classes in suite config.
For current example in 'tests/functional.suite.yml' we should see.

```
class_name: TestGuy
modules:
    enabled: [Symfony1, Db, Filesystem]
```

The TestGuy class has it's methods defined in modules. Actually, it doesn't contain any of them, but acts as a proxy for them. It knows which module executes this action and passes parameters into it. To make your IDE see all methods of TestGuy listed, you use the 'build' command. It generates definition of TestGuy class by copying signatures from modules.

## Standard Modules

Codeception has many bundled modules which would help you run tests for different purposes and on different environments. The modules number is not constant - it's supposed to grow, as more frameworks, ORMs are supported.

Let's list all available modules

* Db - refreshes your database after each run. Also can check data in database exists.
* PhpBrowser - emulates browser with [Goutte PHP Web Scraper](https://github.com/fabpot/Goutte), driven by [Mink](http://mink.behat.org). Commonly used for acceptance tests.
* Filesystem - module to perform simple assertions in your filesystem. 
* Doctrine1 - provides additional tools for projects powered with Doctrine ORM
* Doctrine2 - similar for Doctrine2 ORM. Also has powerful tools to mock Doctrine internal objects.
* Symfony1 - connector to Symfony1 framework. Codeception scenarios are run in application's test environment.
* Unit - module for unit testing. Contains powerful methods for mocking objects, running test methods, asserting, etc.

All of this modules are documented. You can review their detailed references on [GitHub](https://github.com/DavertMik/Codeception/tree/master/docs/modules).

## Helpers

Codeception doesn't bound you only to modules from main repository. No doubts that for your project you might need your own actions added to test suite. By running the 'bootstrap' command, Codeception generates for you three dummy modules, for each of newly created suites. This custom modules are called 'Helpers', and they can be found in 'tests/helpers' path. 

It's good idea to define missing actions or assertion commands in helpers. 

Let's say we are going to extend TestHelper class. By default it's linked with a TestGuy class and functional test suite.

``` php
<?php
namespace Codeception\Module;
// here you can define custom functions for TestGuy

require_once 'PHPUnit/Framework/Assert/Functions.php';

class TestHelper extends \Codeception\Module
{
}
```

As for actions everything is quite simple. Every action you define is a public function. Write down any public method, run 'build' command, and you will see this function added into TestGuy class. Still, public methods prefixed by '_' are treated as hidden and won't be added you your Guy class. 

Assertions are a bit tricky. First of all it's recommended to prefix all your assert actions with 'see', or 'dontSee'. In Codeception philosophy all tests are performed by humans, i.e. guys. The expected result they see (or they don't) is what we use for assertion.

Name your assertions like:

```
seePageReloaded();
seeClassIsLoaded($classname);
dontSeeUserExist($user);
```
And then use them in your tests:

``` php
<?php
$I = new TestGuy($scenario);
$I->seePageReloaded();
$I->seeClassIsLoaded('TestGuy');
$I->dontSeeUserExist($user);

```

Every 'see' or 'dontSee' function requires at least one assert. Codeception uses PHPUnit assertions.

### Assertions
You can define asserts by using assertXXX functions, from 'PHPUnit/Framework/Assert/Functions.php' file.
In case your application falls into conflict with one of this functions, you can use PHPUnit static methods from class PHPUnit_Framework_Assert to define asserts.

``` php
<?php

function seeClassExist($class)
{
      assertTrue(class_exists($class));
      // or
      \PHPUnit_Framework_Assert::assertTrue(class_exists($class));
}
```

Each module has special $this->assert and $this->assertNot methods. They take the same arguments and are useful if you need to define both positive and negative assertions in your module. This functions take an array as parameter, where the first value of array is the name of PHPUnit assert function.

``` php
<?php

$this->assert(array('Equals',$int,3));
$this->assertNot(array('internalType',$int,'bool'));
$this->assert(array('Contains', array(3,5,9), 3));

```
Let's see how define both 'see' and don't see action without code duplication.

``` php
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

```
For dontSeeClassExist, the 'assertFalse' will be called.

### Resolving Collisions

What happens if you have 2 modules which the same named actions within?
Nothing exceptional. The action from the first module will be loaded, action from second will be ignored.
Order of modules can be defined in suite config.

### Connecting Modules

It's possible that you would need to access internal data or functions from other modules. For example, for your module, you might need connection from Doctrine, or web browser from Symfony.

Each modules can interact with each other by getModule method. Please, note that this method will throw an exception If required module was not loaded.

Let's imagine we are writing module which reconnects to database. It's supposed to use the dbh connection value from Db module.

``` php
<?php

function reconnectToDatabase() {
    $dbh = $this->getModule('Db')->dbh;
    $dbh->close();
    $dbh->open();
}

```
By using getModule function you get access to all public methods and properties of module.
The dbh property was defined public specially to be avaible to other modules.

That may be also useful if you need to perform sequence of actions taken from other modules.

For example:

``` php
<?php
function seeConfigFilesCreated()
{
    $filesystem = $this->getModule('Filesystem');
    $filesystem->seeFileFound('codeception.yml');
    $filesystem->openFile('codeception.yml');
    $filesystem->seeInFile('paths:);
}
```

### Hooks

Each module can handle events from running test. Module can be executed before the test starts, or after test is finished. This can be useful to bootstrap/cleanup actions.
Also you can define special behavior when the test failes. This may help you in debugging the issue.
For example, PhpBrowser module saves current webpage to log dir if the test fails.

All hooks are defined in \Codeception\Module

Here are they listed. You are free to redefine them in you module.

``` php
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

```

Please, note, that methods with '_' prefix are not added to the Guy class. This allows them to be defined as public, but used for internal purposes.

### Debug

As we mentioned, the _failed hook can help in debugging the failed test. You have an opportunty to save the current test's state and show it to user.

But you are not limited to this. Each module can output internal values, that may be useful during debug.
For example, the PhpBrowser module prints response code and current url every time it moves to new page.
Thus, modules are not a black boxes, they are trying to show you what is happening during test. This makes debugging your tests less painful.

To print additional information use debug amd debugSection methods of module.
Here is the sample how it works for PhpBrowser:

``` php
<?php
    $this->debug('Request ('.$method.'): '.$uri.' '. json_encode($params));
    $browser->request($method, $uri, $params);
    $this->debug('Response code: '.$this->session->getStatusCode());
```

The test running with PhpBrowser module in debug mode will print something like this:

```
I click "All pages"
* Request (GET) http://localhost/pages {}
* Response code: 200
```

### Configuration

Modules can be configured from suite config file, or globally from codeception.yml.
Mandatory parameters should be defined in $$requiredFields property of module class. Here how it is done in Db module

```
<?php
class Db extends \Codeception\Module {
    protected $requiredFields = array('dsn', 'user', 'password');
```
Next time you start suite without this values set, an exception will be thrown. 

For the optional parameters you should have default values set. The $config property is used to define optional parameters as well as their values. In Seleinum module we use default Selenium Server address and port. 

``` php
<?php
class Selenium extends \Codeception\Util\MinkJS
{
    protected $requiredFields = array('browser', 'url');    
    protected $config = array('host' => '127.0.0.1', 'port' => '4444');
```

The host and port parameter can be redefined in suite config. Values are set in 'modules:config' section of configuration file.

```
modules:
    enabled:
        - Selenium
        - Db
    config:
        Selenium:
            url: 'http://web.begrouped/'
            browser: 'firefox'
        Db:
            cleanup: false
            repopulate: false
```

 Optional and mandatory parameters can be accessed through the $config property. Use $this->config['parameter'] to get it's value. 


## Conclusion

Modules are the true power of Codeception. They are used to emulate multiple inheritance for Guy-classes (CodeGuy, TestGuy, WebGuy, etc).
Codeception provides modules to emulate web requests, access data, interact with popular PHP libraries, etc.
For your application you might need custom actions, that can be defined in helper classes.
If you have written a module, that may be useful to others, share it.
Fork Codeception repository, put module into src/Codeception/Module dir and push request. Much thanks If you do so.
