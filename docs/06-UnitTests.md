# Unit Tests

Codeception uses PHPUnit as a backend for running tests. Thus, any PHPUnit test can be added to Codeception test suite and then executed.
If you ever wrote a PHPUnit test, then do it as well as you did before. Codeception will add you some cool helpers to simplify common tasks.
If you don't have experience in writing unit tests, please read the [PHPUnit manual](http://www.phpunit.de/manual/3.6/en/index.html) to start.
The basics of unit tests are skipped here, but instead you will get a basic knowledge on what features Codeception adds to unit tests.

__To say it again: you don't need to install PHPUnit to run it's tests. Codeception can run them too.__

## Creating Test

Codeception have nice generators to simplify test creation.
You can start with generating a classical PHPUnit test extending `\PHPUnit_Framework_TestCase` class.
This can be done by this command:

```bash
$ php codecept.phar generate:phpunit unit Simple
```

Codeception has it's addons to standard unit tests. So let's try them.
We need another command to create Codeception-powered unit tests.

```bash
$ php codecept.phar generate:test unit Simple
```

Both tests will create a new `SimpleTest` file located in `tests/unit` directory.

A test created by `generate:test` command will look like this:

```php
<?php
use Codeception\Util\Stub;

class SimpleTest extends \Codeception\TestCase\Test
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
    enabled: [Unit, CodeHelper]
```

### Testing Database

Probably, there is no very useful modules set up by default for CodeGuy class. That's because the CodeGuy class is mostly used for scenario-driven unit tests, described in next chapters. But that's ok, we can get a use of it by adding modules we need. For example, we can add a Db module to test updates in database.

```yaml
# Codeception Test Suite Configuration

# suite for unit (internal) tests.
class_name: CodeGuy
modules:
    enabled: [Unit, Db, CodeHelper]
```

After running the build command

```bash
$ php codecept.phar build
```

A new methods will be added into CodeGuy class. Thus, you can start using database methods in your test:

```php
<?php
function testSavingUser()
{
	$user = new User();
	$user->setName('Miles');
	$user->save();
	$this->codeGuy->seeInDatabase('users',array('name' => 'Miles'));
}
?>
```

Database will be cleaned and populated after each test, as it happens for acceptance and functional tests.
If it's not your required behavior, please change the settings of `Db` module for current suite.

### Modules

*new in 1.5.2*

Codeception allows you to access properties and methods of all modules defined for this suite. Unlike using the CodeGuy class for this purpose, using module directly grants you access to all public properties of that module.

For example, if you use `Symfony2` module here is the way you can access Symfony container:

```php
<?php
/**
 * @var Symfony\Component\DependencyInjection\Container
 */
$container = $this->getModule('Symfony2')->container;
```

All public variables are listed in references for corresponding modules.

### Bootstrap

The bootstrap file is located in suite directory and is named `_bootstrap` and is **included before each test** (with `setUp` method in parent class). It's widely used in acceptance and functional tests to initialize the predefined variables. In unit tests it can be used for sharing share same data among the different tests. But the main purpose of is to set up an autoloader for your project inside this class. Otherwise Codeception will not find the testing classes and fail.

### Stubs

The first line of generated class includes a Stub utility class into a test file. This means you can easily create dummy classes instead of real one. Don't waste your time on adding many parameters to constructor, just run the `Stub::make` to create a new class.

Full reference on stub util class can be [found here](/docs/reference/stubs).

### Mix it all together!

Less words, more code for better understanding.

```php
<?php
use Codeception\Util\Stub;

class SimpleTest extends \Codeception\TestCase\Test
{
   /**
    * @var CodeGuy
    */
    protected $codeGuy;

    function _before()
    {
        $this->user = new User();
    }

    function testUserCanBeBanned()
    {
    	$this->user->setIsBanned(true);
    	$this->user->setUpdatedBy(Stub::make('User', array('name' => 'admin')));
    	$this->user->save();
    	$this->codeGuy->seeInDatabase('users', array('name' => 'Miles', 'is_banned' => true));
    }
}
?>
```

### Limitations

PHPUnit tests are very cool, but for complex tests you want to have more strict and readable structure of test. This is done to make a test readable and self-explain,. Whenever you come to idea that your test requires mocks, usage of reflection, you should consider using the specific Codeception Cest format which is a hybrid between PHPUnit Tests and scenario-based Cepts (Cest = Cept + Test). The usage example of the Cest files will be shown in next chapter.

## Conclusion

PHPUnit tests is a first-class citizen in test suites. Whenever you need to write and execute unit tests, you don't need to install PHPUnit manually, but use a Codeception to execute them. Some nice features are added to common unit tests by integrating Codeception modules. For most of unit and integration testing PHPUnit tests are just enough. They are fast and easy to maintain. But when you need some advanced features like mocking, use the special Cest format, described in next chapters.