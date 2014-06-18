# Functional Tests

Now that we've written some acceptance tests, functional tests are almost the same, with just one major difference: Functional tests don't require a web server to run tests.

In simple terms we set `$_REQUEST`, `$_GET` and `$_POST` variables then we execute application from a test. This may be valuable as functional tests are faster and provide detailed stack traces on failures.

Codeception can connect to different web frameworks which support functional testing: Symfony2, Laravel4, Yii2, Zend Framework and others. You just need to enable desired module in your functional suite config to start.

Modules for all of these frameworks share the same interface, and thus your tests are not bound to any one of them. This is a sample functional test.

```php
<?php
$I = new FunctionalTester($scenario);
$I->amOnPage('/');
$I->click('Login');
$I->fillField('Username','Miles');
$I->fillField('Password','Davis');
$I->click('Enter');
$I->see('Hello, Miles', 'h1');
// $I->seeEmailIsSent() - special for Symfony2
?>
```

As you see you can use same tests for functional and acceptance testing. 

## Pitfalls

Acceptance tests are usually much slower than functional tests. But functional tests are less stable as they run Codeception and application in one environment.

#### Headers, Cookies, Sessions

One of the common issues problems with functional tests are usage of PHP functions that deal with `headers`, `sessions`, `cookies`.
As you know, `header` function triggers an error if it is executed more then once. In functional tests we run application multiple times thus, we will get lots of trash errors in the result.

#### Shared Memory

In functional testing unlike the traditional way, PHP application does not stop after it finished processing a request. 
As all requests run in one memory container they are not isolated.
So **if you see that your tests are mysteriously failing when they shouldn't - try to execute a single test.**
This will check if tests were isolated during run. Because it's really easy to spoil environment as all tests are run in shared memory.
Keep your memory clean, avoid memory leaks and clean global and static variables.

## Enabling Framework Modules

You have a functional testing suite in `tests/functional` dir.
To start you need to include one of the framework's module in suite config file: `tests/functional.suite.yml`. Below we provide simplified instructions for setting up functional tests with most popular PHP frameworks

### Symfony2

To perform Symfony2 integrations you don't need to install any bundles or do any configuration changes.
You just need to include the Symfony2 module into your test suite. If you also use Doctrine2, don't forget to include it either.

Example for `functional.suite.yml`

```yaml
class_name: FunctionalTester
modules:
    enabled: [Symfony2, Doctrine2, TestHelper] 
```

By default this module will search for App Kernel in the `app` directory.

The module uses the Symfony Profiler to provide additional information and assertions.

[See the full reference](http://codeception.com/docs/modules/Symfony2)

### Laravel 4

[Laravel](http://codeception.com/docs/modules/Laravel4) module is zero configuration and can be easily set up.

```yaml
class_name: FunctionalTester
modules:
    enabled: [Laravel4, TestHelper]
```


### Yii2

Yii2 tests are included in [Basic](https://github.com/yiisoft/yii2-app-basic) and [Advanced](https://github.com/yiisoft/yii2-app-advanced) application templates. Follow Yii2 guides to start.

### Yii

By itself Yii framework does not have an engine for functional testing.
So Codeception is the first and only functional testing framework for Yii.
To use it with Yii include `Yii1` module into config.

```yaml
class_name: FunctionalTester
modules:
    enabled: [Yii1, TestHelper]
```

To avoid common pitfalls we discussed earlier, Codeception provides basic hooks over Yii engine.
Please set them up [following the installation steps in module reference](http://codeception.com/docs/modules/Yii1).

### Zend Framework 2

Use [ZF2](http://codeception.com/docs/modules/ZF2) module to run functional tests inside Zend Framework 2.

```yaml
class_name: FunctionalTester
modules:
    enabled: [ZF2, TestHelper]
```

### Zend Framework 1.x

The module for Zend Framework is highly inspired by ControllerTestCase class, used for functional testing with PHPUnit. 
It follows similar approaches for bootstrapping and cleaning up. To start using Zend Framework in your functional tests, include the ZF1 module.

Example for `functional.suite.yml`

```yaml
class_name: FunctionalTester
modules:
    enabled: [ZF1, TestHelper] 
```

[See the full reference](http://codeception.com/docs/modules/ZF1)

#### Phalcon 1.x

Phalcon1 module requires creating bootstrap file which returns instance of `\Phalcon\Mvc\Application`. To start functional tests with Phalcon you should enable Phalon1 module and provid path to this bootstrap file:

```yaml
class_name: FunctionalTester
modules:
    enabled: [Phalcon1, FunctionalHelper]
    config:
        Phalcon1
            bootstrap: 'app/config/bootstrap.php'
```

[See the full reference](http://codeception.com/docs/modules/Phalcon1)

## Writing Functional Tests

Functional tests are written in the same manner as [Acceptance Tests](http://codeception.com/docs/04-AcceptanceTests) with PhpBrowser module enabled. All framework modules and PHPBrowser module share the same methods and the same engine.

So we can open a web page with `amOnPage` command.

```php
<?php
$I = new FunctionalTester;
$I->amOnPage('/login');
?>
```

We can click on links to open web pages of application.

```php
<?php
$I->click('Logout');
// click link inside .nav element
$I->click('Logout', '.nav');
// click by CSS
$I->click('a.logout');
// click with strict locator
$I->click(['class' => 'logout']);
?>
```

We can submit form as well

```php
<?php
$I->submitForm('form#login', ['name' => 'jon', 'password' => '123456']);
// alternatively
$I->fillField('#login input[name=name]', 'jon');
$I->fillField('#login input[name=password]', '123456');
$I->click('Sibmut', '#login');
?>
```

And do assertions:

```php
<?php
$I->see('Welcome, jon');
$I->see('Logged in successfulyy', '.notice');
$I->seeCurrentUrlEquals('/profile/jon');
?>
```

Framework modules also contain additional methods to access framework internals. For instance, Laravel4, Phalcon1, and Yii2 module have `seeRecord` method which uses ActiveRecord layer to check that record exists in database.
Laravel4 module also contain methods to do additional session checks. You may find `seeSessionHasErrors` useful when you test form validations.

Take a look on the complete reference on module you are using. Most of its methods are common for all modules but some of them are unique.

Also you can access framework globals inside a test or access Depenency Injection containers inside `FunctionalHelper` class.

```php
<?php
class FunctionalHelper extends \Codeception\Module
{
    function doSomethingWithMyService()
    {
        $service = $this->getModule('Symfony2') // lookup for Symfony 2 module
            ->container // get current DI container
            ->get('my_service'); // access a service

        $service->doSomething();
    }
}
?>
```

We accessed Symfony2 internal kernel and took a service out of container. We also created custom method in `FunctionalTester` class which can be used in test.

You can learn more about accessing framework you use by checking *Public Properties* section in respective module. 

## Error Reporting

By default Codeception uses `E_ALL & ~E_STRICT & ~E_DEPRECATED` error reporting value. 
In functional tests you might want to change this values depending on framework's error policy.
The error reporting value can be set at suite configuraion file:

```yaml
class_name: FunctionalTester
modules:
    enabled: [Yii1, TestHelper]
error_level: "E_ALL & ~E_STRICT & ~E_DEPRECATED"
```

`error_level` can be set globally in `codeception.yml` file.


## Conclusion

Functional tests are great if you are using powerful frameworks. By using functional tests you can access and manipulate their internal state. 
This makes your tests shorter and faster. In other cases, if you don't use frameworks there is no practical reason to write functional tests.
If you are using a framework other than the ones listed here, create a module for it and share it with community.
