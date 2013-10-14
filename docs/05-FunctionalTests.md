# Functional Tests

Now that we've written some acceptance tests, functional tests are almost the same, with just one major difference: Functional tests don't require a web server to run your scenarios. In other words, we will run your application inside the tests, emulating requests and response.

In simple terms we set `$_REQUEST`, `$_GET` and `$_POST` variables, then we execute your script inside a test, we receive output, and then we test it. 
Functional testing may often be better then acceptance testing because it doesn't require a web server and may provide you with more detailed debug output. For example, if your site throws an exception it will be shown in the console.

Codeception can connect to different web frameworks which support functional testing. For example you can run a functional test for an application built on top of the Zend Framework, Symfony or Symfony2 with just the modules provided by Codeception! The list of supported frameworks will be extended in the future.

Modules for all of these frameworks share the same interface, and thus your tests are not bound to any one of them! This is a sample functional test.

``` php
<?php
$I = new TestGuy($scenario);
$I->amOnPage('/');
$I->click('Login');
$I->fillField('Username','Miles');
$I->fillField('Password','Davis');
$I->click('Enter');
$I->see('Hello, Miles', 'h1');
// $I->seeEmailIsSent() - special for Symfony2
?>
```

That was just as acceptance test. As you see you can use same tests for functional and acceptance testing. 
We recommend writing tests on unstable parts of your application as functional tests, and testing the stable parts with acceptance tests.

## Pitfalls

Acceptance tests are usually much slower than functional tests. But functional tests are less stable, as they run testing framework and application in one environment.

#### Headers, Cookies, Sessions

One of the common issues problems with functional tests are usage of PHP functions that deal with `headers`, `sessions`, `cookies`.
As you know, `header` function triggers an error if it is executed more then once. In functional tests we run application multiple times thus, we will get lots of trash errors in the result.

#### Shared Memory

In functional testing unlike the traditional way, PHP application does not stop after it finished processing a request. 
As all requests run in one memory container they are not isolated.
So **if you see that your tests are mysteriously failing when they shouldn't - try to execute a single test.**
This will check if tests were isolated during run. Because it's really easy to spoil environment as all tests are run in shared memory.
Keep your memory clean, avoid memory leaks and clean global and static variables.

## Starting Functional

You have a functional testing suite in `tests/functional` dir.
To start you need to include one of the framework's module in suite config file: `tests/functional.suite.yml`.
Examples on framework configurations you will find below th this chapter.

Then you should rebuild your Guy-classes

```
php codecept.phar build
```

To generate a test you can use standard `generate:cept` command:

```
php codecept.phar generate:cept functional myFirstFunctional
```

And execute them with `run`:

```
php codecept.phar run functional
```

Use `--debug` option for more detailed output.

## Error Reporting

By default Codeception uses `E_ALL & ~E_STRICT & ~E_DEPRECATED` error reporting value. 
In functional tests you might want to change this values depending on framework's error policy.
The error reporting value can be set at suite configuraion file:

{% highlight yaml %}
class_name: TestGuy
modules:
    enabled: [Yii1, TestHelper]
error_level: "E_ALL & ~E_STRICT & ~E_DEPRECATED"
{% endhighlight %}

`error_level` can be set globally in `codeception.yml` file.

## Frameworks

Codeception have integrations for the most popular PHP frameworks.
We aim to get modules for all of the most popular ones.
Please help to develop them if you don't see your favorite framework in a list.

### Symfony2

To perform Symfony2 integrations you don't need to install any bundles or perform any configuration changes.
You just need to include the Symfony2 module into your test suite. If you also use Doctrine2, don't forget to include it either.

Example for `functional.suite.yml`

```yaml
class_name: TestGuy
modules:
    enabled: [Symfony2, Doctrine2, TestHelper] 
```

By default this module wilyl search for Kernel in the `app` directory.

The module uses the Symfony Profiler to provide additional information and assertions.

[See the full reference](http://codeception.com/docs/modules/Symfony2)

### Laravel 4

[Laravel](http://codeception.com/docs/modules/Laravel4) module is zero configuration and can be easily set up.

```yaml
class_name: TestGuy
modules:
    enabled: [Laravel4, TestHelper]
```

### Yii

By itself Yii framework does not have an engine for functional testing.
So Codeception is the first and only functional testing framework for Yii.
To use it with Yii include `Yii1` module into config.

```yaml
class_name: TestGuy
modules:
    enabled: [Yii1, TestHelper]
```

To avoid common pitfalls we discussed earlier, Codeception provides basic hooks over Yii engine.
Please set them up [following the installation steps in module reference](http://codeception.com/docs/modules/Yii1).

### Zend Framework 2

Use [ZF2](http://codeception.com/docs/modules/ZF2) module to run functional tests inside Zend Framework 2.

```yaml
class_name: TestGuy
modules:
    enabled: [ZF2, TestHelper]
```

### Zend Framework 1.x

The module for Zend Framework is highly inspired by ControllerTestCase class, used for functional testing with PHPUnit. 
It follows similar approaches for bootstrapping and cleaning up. To start using Zend Framework in your functional tests, include the ZF1 module.

Example for `functional.suite.yml`

```yaml
class_name: TestGuy
modules:
    enabled: [ZF1, TestHelper] 
```

[See the full reference](http://codeception.com/docs/modules/ZF1)

### symfony

This module was the first one developed for Codeception. Because of this, its actions may differ from what are used in another frameworks.
It provides various useful operations like logging in a user with sfGuardAuth or validating the form inside a test.

Example for `functional.suite.yml`

```yaml
class_name: TestGuy
modules:
    enabled: [Symfony1, TestHelper] 
```

[See the full reference](http://codeception.com/docs/modules/Symfony1)

## Integrating Other Frameworks

Codeception doesn't provide any generic functional testing module because there are a lot of details we can't implement in general.
We already discussed the common pitfalls for functional testing. There is no single recipe to solve them for all PHP applications.
So if you don't use any of the frameworks listed above, you might want to integrate your framework into Codeception. That task requires some knowledge of Codeception internals and some time. Probably, you are ok with just acceptance tests, but any help in extending Codeception functionality will be appreciated. We will review what should be done to have your framework integrated.

#### With HttpKernel

If you have a framework that uses Symfony's `HttpKernel`, using it with Codeception will be a piece of cake.
You will need to create a module for it and test it on your application.
We already have a [guide for such an integration](http://codeception.com/01-24-2013/connecting-php-frameworks-1.html).
Develop a module, try it and share with community.

#### Any Other

Integration is a bit harder if your framework is not using HttpKernel component.
The hardest part of it is resolving common pitfalls: memory management, and usage of `headers` function.
Codeception uses [BrowserKit](https://github.com/symfony/BrowserKit) from the Symfony Components to interact with applications in functional tests. This component provides all of the common actions we see in modules: click, fillField, see, etc... So you don't need to write these methods in your module. For the integration you should provide a bridge from BrowserKit to your application.

We will start with writing a helper class for framework integration.

```php
<?php
namespace Codeception\Module;
class SomeFrameworkHelper extends \Codeception\Util\Framework {
     
}
?>
```

Let's investigate [Codeception source code](https://github.com/Codeception/Codeception).
Look into the `src/Util/Framework.php` file that we extend. It implements all of the common actions for all frameworks.

You may see that all of the interactions are performed by a 'client' instance. We need to create a client that connects to a framework.
Client should extend Symfony\BrowserKit\Client module, sample clients are in the `src/Util/Connector` path. 
If the framework doesn't provide its own tools for functional testing you can try using the Universal connector. Otherwise, look at how the Zend connector is implemented, and implement your own connector.

Whether you decide to use the Universal connector or write your own, you can include it into your module.

```php
<?php
namespace Codeception\Module;
class SomeFrameworkHelper extends \Codeception\Util\Framework {
     
    public function _initialize() {
        $this->client = new \Codeception\Util\Connector\Universal();
        // or any other connector you implement
        
        // we need specify path to index file
        $this->client->setIndex('public_html/index.php');
    }     
}
?>
```

If you include this helper into your suite, it can already perform interactions with your applications.
You can extend its abilities by connecting to the framework internals. 
It's important to perform a proper clean up on each test run. 
This can be done in the _before_ and _after_ methods of the helper module. Check that the framework doesn't cache any data or configuration between tests.

After you get your module stabilized, share it with the community. Fork a Codeception repository, add your module and make a Pull Request.

There are some requirements for modules:

* It should be easy to configure.
* It should contain proper documentation.
* It should extend basic operations by using framework internals.
* It's preferred that it be able to print additional debug information.

We don't require unit tests, because there is no good way for writing proper unit tests for framework integration.
But you can demonstrate a sample application with your framework which uses Codeception tests and your module. 

## Conclusion

Functional tests are great if you are using powerful frameworks. By using functional tests you can access and manipulate their internal state. 
This makes your tests shorter and faster. In other cases, if you don't use frameworks, there is no practical reason to write functional tests.
If you are using a framework other than the ones listed here, create a module for it and share it with community.
