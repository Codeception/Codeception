# Unit Tests

Codeception uses PHPUnit as a backend for running tests. Thus, any PHPUnit test can be added to Codeception test suite and then executed.
If you ever wrote a PHPUnit test, then do it as well as you did before. Codeception will add you some cool helpers to simplify common tasks.
If you don't have experience in writing unit tests, please read the [PHPUnit manual](http://www.phpunit.de/manual/3.6/en/index.html) to start.
The basics of unit tests are skipped here, but instead you will get a basic knowledge on what features Codeception adds to unit tests.

__To say it again: you don't need to install PHPUnit to run its tests. Codeception can run them too.__

## Creating Test

Codeception have nice generators to simplify test creation.
You can start with generating a classical PHPUnit test extending `\PHPUnit_Framework_TestCase` class.
This can be done by this command:

```bash
$ php codecept.phar generate:phpunit unit Example
```

Codeception has it's addons to standard unit tests. So let's try them.
We need another command to create Codeception-powered unit tests.

```bash
$ php codecept.phar generate:test unit Example
```

Both tests will create a new `ExampleTest` file located in `tests/unit` directory.

A test created by `generate:test` command will look like this:

```php
<?php
use Codeception\Util\Stub;

class ExampleTest extends \Codeception\TestCase\Test
{
   /**
    * @var CodeGuy
    */
    protected $codeGuy;

    // executed before each test
    protected function _before()
    {
    }

    // executed after each test
    protected function _after()
    {
    }
}
?>
```

This class has predefined `_before` and `_after` methods to start with. You can use them to create a tested object before each test, and destroy it afterwards.

As you see unlike in PHPUnit setUp/tearDown methods are replaced with their aliases: `_before`, `_after`.
The actual setUp and tearDown was implemented by parent class `\Codeception\TestCase\Test` and is used to include a bootstrap file (`_bootstrap.php` by default) and set up the codeGuy class to have all the cool actions from Cept-files to be run as a part of unit tests. Just like in accordance tests, you can choose the proper modules for `CodeGuy` class in `unit.suite.yml` configuration file.
So If you implement `setUp` and `tearDown` be sure, that you will call their parent method.


```yaml
# Codeception Test Suite Configuration

# suite for unit (internal) tests.
class_name: CodeGuy
modules:
    enabled: [CodeHelper]
```

### Classical Unit Testing

Unit tests in Codeception written in absolutely the same way as you do it in PHPUnit:

```php
<?php
class UserTest extends \Codeception\TestCase\Test
{
    public function testValidation()
    {
        $this->user = User::create();

        $this->user->username = null;
        $this->assertFalse($user->validate(['username']); 

        $user->username = 'toolooooongnaaaaaaameeee',
        $this->assertFalse($user->validate(['username']);         

        $user->username = 'davert',
        $this->assertTrue($user->validate(['username']));           
    }
}
?>
```

### BDD Specification Testing

When writing a test you should prepare it to constant changes in your application. Tests should be easy to read and maintain. If a specification to your application is changed, your test should be updated as well. If inside a team you didn't have a convention on documenting tests, you will have issues figuring out what tests was affected by a new feature introduction. 

That's why its pretty important not just to cover your application with unit tests, but make unit tests self-explainable. We do this for scenario-driven acceptance and functional tests, and we should do this for unit and integration tests as well. 

For this case we have a stand-alone project [Specify](https://github.com/Codeception/Specify)(included in phar package) for writing specifications inside a unit test. 


```php
<?php
class UserTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

    public function testValidation()
    {
        $this->user = User::create();

        $this->specify("username is required", function() {
            $this->user->username = null;
            $this->assertFalse($user->validate(['username']); 
        });

        $this->specify("username is too long", function() {
            $user->username = 'toolooooongnaaaaaaameeee',
            $this->assertFalse($user->validate(['username']);         
        });

        $this->specify("username is ok", function() {
            $user->username = 'davert',
            $this->assertTrue($user->validate(['username']));           
        });     
    }
}
?>        
```

Using `specify` codeblocks you can describe any piece of test. This makes tests much more clean and understandable for everyone in a team.

Code inside `specify` block is isolated. In the example above any change to `$this->user` (as any other object property), will not be reflected in other code blocks.

Also you may add [Codeception\Verify](https://github.com/Codeception/Verify) for BDD-style assertions.

## Using Modules

As in scenario-driven functional or acceptance tests you can access actor class methods. If you write integration tests, it may be useful to include Db module for database testing. 

```yaml
# Codeception Test Suite Configuration

# suite for unit (internal) tests.
class_name: CodeGuy
modules:
    enabled: [Db, CodeHelper]
```

To access CodeGuy methods you can use `codeGuy` property in a test.

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
	$this->codeGuy->seeInDatabase('users',array('name' => 'Miles', 'surname' => 'Davis'));
}
?>
```

Database will be cleaned and populated after each test, as it happens for acceptance and functional tests.
If it's not your required behavior, please change the settings of `Db` module for current suite.

### Accessing Module 

Codeception allows you to access properties and methods of all modules defined for this suite. Unlike using the CodeGuy class for this purpose, using module directly grants you access to all public properties of that module.

For example, if you use `Symfony2` module here is the way you can access Symfony container:

```php
<?php
/**
 * @var Symfony\Component\DependencyInjection\Container
 */
$container = $this->getModule('Symfony2')->container;
?>
```

All public variables are listed in references for corresponding modules.

### Cest

Alternatively to testcases extended from `PHPUnit_Framework_TestCase` you may use Codeception-specific Cest format. It does not require to be extended from any other class. All public methods of this class is a test. 

The example above can be rewritten in scenario-driven manner like this:

```php
<?php
class UserCest
{
    function validateUser(CodeGuy $I)
    {
        $user = $I->haveUser();
        $user->username = null;
        $I->canSeeFalse($user->validate(['username']); 

        $user->username = 'toolooooongnaaaaaaameeee',
        $I->canSeeFalse($user->validate(['username']);         

        $user->username = 'davert',
        $I->canSeeTrue($user->validate(['username']));

        $I->seeInDatabase('users', ['name' => 'Miles', 'surname' => 'Davis']);
    }
}
?>
```

For unit testing you may include `Asserts` module, that adds regular assertions to `$I`.

```yaml
# Codeception Test Suite Configuration

# suite for unit (internal) tests.
class_name: CodeGuy
modules:
    enabled: [Asserts, Db, CodeHelper]
```

[Learn more about Cest format](http://codeception.com/docs/07-AdvancedUsage#Cest-Classes).

### Bootstrap

The bootstrap file is located in suite directory and is named `_bootstrap` and is **included before each test** (with `setUp` method in parent class). It's widely used in acceptance and functional tests to initialize the predefined variables. In unit tests it can be used for sharing the same data among the different tests. But the main purpose of is to set up an autoloader for your project inside this class. Otherwise Codeception will not find the testing classes and fail.

### Stubs

The first line of generated class includes a Stub utility class into a test file. This means you can easily create dummy classes instead of real one. Don't waste your time on adding many parameters to constructor, just run the `Stub::make` to create a new class.

Stubs are created with PHPUnit's mocking framework. Alternatively you can use [Mockery](https://github.com/padraic/mockery) (with [Mockery module](https://github.com/Codeception/MockeryModule)), [AspectMock](https://github.com/Codeception/AspectMock) or others. 

Full reference on stub util class can be [found here](/docs/reference/stubs).


## Conclusion

PHPUnit tests is a first-class citizen in test suites. Whenever you need to write and execute unit tests, you don't need to install PHPUnit manually, but use a Codeception to execute them. Some nice features are added to common unit tests by integrating Codeception modules. For most of unit and integration testing PHPUnit tests are just enough. They are fast and easy to maintain.
