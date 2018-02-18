# Unit & Integration Tests

Codeception uses PHPUnit as a backend for running its tests. Thus, any PHPUnit test can be added to a Codeception test suite
and then executed. If you ever wrote a PHPUnit test then do it just as you did before.
Codeception adds some nice helpers to simplify common tasks.

## Creating a Test

Create a test using `generate:test` command with a suite and test names as parameters:

```bash
php codecept generate:test unit Example
```

It creates a new `ExampleTest` file located in the `tests/unit` directory.

As always, you can run the newly created test with this command:

```bash
php codecept run unit ExampleTest
```

Or simply run the whole set of unit tests with:

```bash
php codecept run unit
```

A test created by the `generate:test` command will look like this:

```php
<?php

class ExampleTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testMe()
    {

    }
}
```

Inside a class:

* all public methods with `test` prefix are tests
* `_before` method is executed before each test (like `setUp` in PHPUnit)
* `_after` method is executed after each test (like `tearDown` in PHPUnit)

## Unit Testing

Unit tests are focused around a single component of an application. 
All external dependencies for components should be replaced with test doubles. 

A typical unit test may look like this: 

```php
<?php
class UserTest extends \Codeception\Test\Unit
{
    public function testValidation()
    {
        $user = User::create();

        $user->username = null;
        $this->assertFalse($user->validate(['username']));

        $user->username = 'toolooooongnaaaaaaameeee';
        $this->assertFalse($user->validate(['username']));

        $user->username = 'davert';
        $this->assertTrue($user->validate(['username']));
    }
}
```

### Assertions

There are pretty many assertions you can use inside tests. The most common are:

* `$this->assertEquals()`
* `$this->assertContains()`
* `$this->assertFalse()`
* `$this->assertTrue()`
* `$this->assertNull()`
* `$this->assertEmpty()`

Assertion methods come from PHPUnit. [See the complete reference at phpunit.de](https://phpunit.de/manual/current/en/appendixes.assertions.html).

### Test Doubles

Codeception provides [Codeception\Stub library](https://github.com/Codeception/Stub) for building mocks and stubs for tests. 
Under the hood it used PHPUnit's mock builder but with much simplified API.

Alternatively, [Mockery](https://github.com/Codeception/MockeryModule) can be used inside Codeception.

#### Stubs

Stubs can be created with a static methods of `Codeception\Stub`.

```php
<?php
$user = \Codeception\Stub::make('User', ['getName' => 'john']);
$name = $user->getName(); // 'john'
```

[See complete reference](http://codeception.com/docs/reference/Mock)

Inside unit tests (`Codeception\Test\Unit`) it is recommended to use alternative API:

```php
<?php
// create a stub with find method replaced
$userRepository = $this->make(UserRepository::class, ['find' => new User]);
$userRepository->find(1); // => User

// create a dummy
$userRepository = $this->makeEmpty(UserRepository::class);

// create a stub with all methods replaced except one
$user = $this->makeEmptyExcept(User::class, 'validate');
$user->validate($data);

// create a stub by calling constructor and replacing a method
$user = $this->construct(User::class, ['name' => 'davert'], ['save' => false]);

// create a stub by calling constructor with empty methods
$user = $this->constructEmpty(User::class, ['name' => 'davert']);

// create a stub by calling constructor with empty methods
$user = $this->constructEmptyExcept(User::class, 'getName', ['name' => 'davert']);
$user->getName(); // => davert
$user->setName('jane'); // => this method is empty
```

[See complete reference](http://codeception.com/docs/reference/Mock)

Stubs can also be created using static methods from `Codeception\Stub` class.
In this 

```php
<?php
\Codeception\Stub::make(UserRepository::class, ['find' => new User]);
```

See a reference for static Stub API  

#### Mocks

To declare expectations for mocks use `Codeception\Stub\Expected` class:

```php
<?php
// create a mock where $user->getName() should never be called
$user = $this->make('User', [
     'getName' => Expected::never(),
     'someMethod' => function() {}
]);
$user->someMethod();

// create a mock where $user->getName() should be called at least once
$user = $this->make('User', [
        'getName' => Expected::atLeastOnce('Davert')
    ]
);
$user->getName();
$userName = $user->getName();
$this->assertEquals('Davert', $userName);
```

[See complete reference](http://codeception.com/docs/reference/Mock)

## Integration Tests

Unlike unit tests integration tests doesn't require the code to be executed in isolation.
That allows us to use database and other components inside a tests. 
To improve the testing experience modules can be used as in functional testing.

### Using Modules

As in scenario-driven functional or acceptance tests you can access Actor class methods.
If you write integration tests, it may be useful to include the `Db` module for database testing.

```yaml
# Codeception Test Suite Configuration

# suite for unit (internal) tests.
actor: UnitTester
modules:
    enabled:
        - Asserts
        - Db
        - \Helper\Unit
```

To access UnitTester methods you can use the `UnitTester` property in a test.

### Testing Database

Let's see how you can do some database testing:

```php
<?php
function testSavingUser()
{
    $user = new User();
    $user->setName('Miles');
    $user->setSurname('Davis');
    $user->save();
    $this->assertEquals('Miles Davis', $user->getFullName());
    $this->tester->seeInDatabase('users', ['name' => 'Miles', 'surname' => 'Davis']);
}
```

To enable the database functionality in unit tests, make sure the `Db` module is included
in the `unit.suite.yml` configuration file.
The database will be cleaned and populated after each test, the same way it happens for acceptance and functional tests.
If that's not your required behavior, change the settings of the `Db` module for the current suite. See [Db Module](http://codeception.com/docs/modules/Db)

### Interacting with the Framework

You should probably not access your database directly if your project already uses ORM for database interactions.
Why not use ORM directly inside your tests? Let's try to write a test using Laravel's ORM Eloquent.
For this we need to configure the Laravel5 module. We won't need its web interaction methods like `amOnPage` or `see`,
so let's enable only the ORM part of it:

```yaml
actor: UnitTester
modules:
    enabled:
        - Asserts
        - Laravel5:
            part: ORM
        - \Helper\Unit
```

We included the Laravel5 module the same way we did for functional testing.
Let's see how we can use it for integration tests:

```php
<?php
function testUserNameCanBeChanged()
{
    // create a user from framework, user will be deleted after the test
    $id = $this->tester->haveRecord('users', ['name' => 'miles']);
    // access model
    $user = User::find($id);
    $user->setName('bill');
    $user->save();
    $this->assertEquals('bill', $user->getName());
    // verify data was saved using framework methods
    $this->tester->seeRecord('users', ['name' => 'bill']);
    $this->tester->dontSeeRecord('users', ['name' => 'miles']);
}
```

A very similar approach can be used for all frameworks that have an ORM implementing the ActiveRecord pattern.
In Yii2 and Phalcon, the methods `haveRecord`, `seeRecord`, `dontSeeRecord` work in the same way.
They also should be included by specifying `part: ORM` in order to not use the functional testing actions.

If you are using Symfony with Doctrine, you don't need to enable Symfony itself but just Doctrine2:

```yaml
actor: UnitTester
modules:
    enabled:
        - Asserts
        - Doctrine2:
            depends: Symfony
        - \Helper\Unit
```

In this case you can use the methods from the Doctrine2 module, while Doctrine itself uses the Symfony module
to establish connections to the database. In this case a test might look like:

```php
<?php
function testUserNameCanBeChanged()
{
    // create a user from framework, user will be deleted after the test
    $id = $this->tester->haveInRepository(User::class, ['name' => 'miles']);
    // get entity manager by accessing module
    $em = $this->getModule('Doctrine2')->em;
    // get real user
    $user = $em->find(User::class, $id);
    $user->setName('bill');
    $em->persist($user);
    $em->flush();
    $this->assertEquals('bill', $user->getName());
    // verify data was saved using framework methods
    $this->tester->seeInRepository(User::class, ['name' => 'bill']);
    $this->tester->dontSeeInRepository(User::class, ['name' => 'miles']);
}
```

In both examples you should not be worried about the data persistence between tests.
The Doctrine2 and Laravel5 modules will clean up the created data at the end of a test.
This is done by wrapping each test in a transaction and rolling it back afterwards.

### Accessing Module

Codeception allows you to access the properties and methods of all modules defined for this suite.
Unlike using the UnitTester class for this purpose, using a module directly grants you access
to all public properties of that module.

We have already demonstrated this in a previous example where we accessed the Entity Manager from a Doctrine2 module:

```php
<?php
/** @var Doctrine\ORM\EntityManager */
$em = $this->getModule('Doctrine2')->em;
```

If you use the `Symfony` module, here is how you can access the Symfony container:

```php
<?php
/** @var Symfony\Component\DependencyInjection\Container */
$container = $this->getModule('Symfony')->container;
```

The same can be done for all public properties of an enabled module. Accessible properties are listed in the module reference.

### Scenario Driven Testing

[Cest format](http://codeception.com/docs/07-AdvancedUsage#Cest-Classes) can also be used for integration testing.
In some cases it makes tests cleaner as it simplifies module access by using common `$I->` syntax:

```php
<?php
public function buildShouldHaveSequence(\UnitTester $I)
{
    $build = $I->have(Build::class, ['project_id' => $this->project->id]);
    $I->assertEquals(1, $build->sequence);
    $build = $I->have(Build::class, ['project_id' => $this->project->id]);
    $I->assertEquals(2, $build->sequence);
    $this->project->refresh();
    $I->assertEquals(3, $this->project->build_sequence);
}
```
This format can be recommended for testing domain and database interactions.

In Cest format you don't have native support for test doubles so it's recommended 
to include a trait `\Codeception\Test\Feature\Stub` to enable mocks inside a test.
Alternatively, install and enable [Mockery module](https://github.com/Codeception/MockeryModule).

## Advanced Tools

### Specify

When writing tests you should prepare them for constant changes in your application.
Tests should be easy to read and maintain. If a specification of your application is changed,
your tests should be updated as well. If you don't have a convention inside your team for documenting tests,
you will have issues figuring out what tests will be affected by the introduction of a new feature.

That's why it's pretty important not just to cover your application with unit tests, but make unit tests self-explanatory.
We do this for scenario-driven acceptance and functional tests, and we should do this for unit and integration tests as well.

For this case we have a stand-alone project [Specify](https://github.com/Codeception/Specify)
(which is included in the phar package) for writing specifications inside unit tests:

```php
<?php
class UserTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /** @specify */
    private $user;

    public function testValidation()
    {
        $this->user = User::create();

        $this->specify("username is required", function() {
            $this->user->username = null;
            $this->assertFalse($this->user->validate(['username']));
        });

        $this->specify("username is too long", function() {
            $this->user->username = 'toolooooongnaaaaaaameeee';
            $this->assertFalse($this->user->validate(['username']));
        });

        $this->specify("username is ok", function() {
            $this->user->username = 'davert';
            $this->assertTrue($this->user->validate(['username']));
        });
    }
}
```

By using `specify` codeblocks, you can describe any piece of a test.
This makes tests much cleaner and comprehensible for everyone in your team.

Code inside `specify` blocks is isolated. In the example above, any changes to `$this->user`
will not be reflected in other code blocks as it is marked with `@specify` annotation.

Also, you may add [Codeception\Verify](https://github.com/Codeception/Verify) for BDD-style assertions.
This tiny library adds more readable assertions, which is quite nice, if you are always confused
about which argument in `assert` calls is expected and which one is actual:

```php
<?php
verify($user->getName())->equals('john');
```

### Domain Assertions

The more complicated your domain is the more explicit your tests should be. With [DomainAssert](https://github.com/Codeception/DomainAssert)
library you can easily create custom assertion methods for unit and integration tests.

It allows to reuse business rules inside assertion methods:

```php
<?php
$user = new User;

// simple custom assertions below:
$this->assertUserIsValid($user);
$this->assertUserIsAdmin($user);

// use combined explicit assertion
// to tell what you expect to check
$this->assertUserCanPostToBlog($user, $blog);
// instead of just calling a bunch of assertions
$this->assertNotNull($user);
$this->assertNotNull($blog);
$this->assertContain($user, $blog->getOwners());
```

With custom assertion methods you can improve readability of your tests and keep them focused around the specification.

### AspectMock

[AspectMock](https://github.com/Codeception/AspectMock) is an advanced mocking framework which allows you to replace any methods of any class in a test.
Static methods, class methods, date and time functions can be easily replaced with AspectMock.
For instance, you can test singletons!

```php
<?php
public function testSingleton()
{
	$class = MySingleton::getInstance();
	$this->assertInstanceOf('MySingleton', $class);
	test::double('MySingleton', ['getInstance' => new DOMDocument]);
	$this->assertInstanceOf('DOMDocument', $class);
}
``` 

* [AspectMock on GitHub](https://github.com/Codeception/AspectMock)
* [AspectMock in Action](http://codeception.com/07-31-2013/nothing-is-untestable-aspect-mock.html)
* [How it Works](http://codeception.com/09-13-2013/understanding-aspectmock.html)

## Conclusion

PHPUnit tests are first-class citizens in test suites. Whenever you need to write and execute unit tests,
you don't need to install PHPUnit seperately, but use Codeception directly to execute them.
Some nice features can be added to common unit tests by integrating Codeception modules.
For most unit and integration testing, PHPUnit tests are enough. They run fast, and are easy to maintain.
