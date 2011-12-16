# Modules

Codeception uses modularity to create comfortable testing environment for every test suite you write. 
Modules allows you choose actions and assertions that can be perform in tests.

All actions and assertions, that can be performed by Guy object in class are defined in modules. It might look that Codeception limits you in testing, still it's not true. You can extend testing suite with your own actions and assertions, writing them into custom module. 

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
class_name: CodeGuy
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

## Custom modules

Codeception doesn't bound you only to modules from main repository. No doubts that for your project you might need your own actions added to test suite. By running the 'bootstrap' command, Codeception generates for you three dummy modules, for each of newly created suites. This custom modules are called 'Helpers', and they can be found in 'tests/helpers' path. 

It's good idea to define missing actions or assertion commands in helpers. 

Let's say we are going to extend TestHelper class. By default it's linked with a TestGuy class and functional test suite.

``` php
<?php
namespace Codeception\Module;
// here you can define custom functions for TestGuy

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

Every 'see' or 'dontSee' function requires at least one assert. Codeception uses PHPUnit_Framework_Assert classes to define them. 

``` php
<?php

function seeClassExist($class)
{
      \PHPUnit_Framework_Assert::assertTrue(class_exists($class));
}
```

For PHPUnit you might have used to $this->assertContains construction. But in Codeception modules you should rely on static methods of PHPUnit_Framework_Assert class. 

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
For dontSeeClassExist, the PHPUnit_Framework_Assert::assertFalse will be called.

## Connecting modules

It's possible that you would need to access internal data or functions from other modules. For example, for your module, you might need connection from Doctrine, or web browser from Symfony.

...



