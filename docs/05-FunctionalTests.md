# Functional Tests

Now that we've written some acceptance tests, functional tests are almost the same, with just one major difference: Functional tests don't require a web server to run your scenarios. In other words, we will run your application inside the tests, emulating requests and response.

In simple terms we set $_REQUEST, $_GET and $_POST variables, then we execute your script inside a test, we receive output, and then we test it. 
Functional testing may often be better then acceptance testing because it doesn't require a web server and may provide you with more detailed debug output. For example, if your site throws an exception it will be shown in the console. 

There is an exception: If it was inside an acceptance test you would just see the error page, but here we see the actual exception with a stack trace. 

```bash
There was 1 error:
Couldn't create new blog post (CreateBlogCept.php)
  Exception thrown Zend_Db_Adapter_Exception:
  SQLSTATE[42000] [1049] Unknown database 'zfblog'
  Stack trace:
   #1 _connect C:\WebServers\usr\local\php5\PEAR\Zend\Db\Adapter\Pdo\Mysql.php:109
   #2 _connect C:\WebServers\usr\local\php5\PEAR\Zend\Db\Adapter\Abstract.php:459
   #3 query C:\WebServers\usr\local\php5\PEAR\Zend\Db\Adapter\Pdo\Abstract.php:238
   #4 query C:\WebServers\usr\local\php5\PEAR\Zend\Db\Adapter\Pdo\Mysql.php:169
   #5 describeTable C:\WebServers\usr\local\php5\PEAR\Zend\Db\Table\Abstract.php:835
   #6 _setupMetadata C:\WebServers\usr\local\php5\PEAR\Zend\Db\Table\Abstract.php:874
   #7 _setupPrimaryKey C:\WebServers\usr\local\php5\PEAR\Zend\Db\Table\Abstract.php:982
```

Good integration can allow you to perform additional operations, like checking if an email was sent or authenticating users.

Codeception can connect to different web frameworks which support functional testing. For example you can run a functional test for an application built on top of the Zend Framework, Symfony or Symfony2 with just the modules provided by Codeception! The list of supported frameworks will be extended in the future.

Modules for all of these frameworks share the same interface, and thus your tests are not bound to any one of them!
You can even run your acceptance tests as a functional test and vice versa.

We recommend writing tests on unstable parts of your application as functional tests, and testing the stable parts with acceptance tests. 

Please note that acceptance tests are usually much slower then functional tests, since they require database repopulation on each run. For sites that are using the Doctrine ORM, all operations are performed inside a transaction, which will be rolled back at the end. This will work much faster then rebuilding the database from a dump on each test. 

## Connecting Frameworks

Let's see how you can integrate Codeception with your favorite framework. 

### Symfony2

To perform Symfony2 integrations you don't need to install any bundles or perform any configuration changes.
You just need to include the Symfony2 module into your test suite. If you also use Doctrine2, don't forget to include it either.

Example for `functional.suite.yml`

```yaml
class_name: TestGuy
modules:
    enabled: [Symfony2, Doctrine2, TestHelper] 
```

By default this module will search for Kernel in the `app` directory.

The module uses the Symfony Profiler to provide additional information and assertions.

[See the full reference](http://codeception.com/docs/modules/Symfony2)

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
So if you don't use any of the frameworks listed above, you might want to integrate your framework into Codeception. That task requires some knowledge of Codeception internals and some time. Probably, you are ok with just acceptance tests, but any help in extending Codeception functionality will be appreciated. We will review what should be done to have your framework integrated.

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
This can be done in the _before and _after methods of the helper module. Check that the framework doesn't cache any data or configuration between tests.

After you get your module stabilized, share it with the community. Fork a Codeception repository, add your module and make a Pull Request.

There are some requirements for modules:
* It should be easy to configure
* It should contain proper documentation.
* It should extend basic operations by using framework internals.
* It's preferred that it be able to print additional debug information.

We don't require unit tests, because there is no good way for writing proper unit tests for framework integration.
But you can demonstrate a sample application with your framework which uses Codeception tests and your module. 

## Conclusion

Functional tests are great if you are using powerful frameworks. By using functional tests you can access and manipulate their internal state. 
This makes your tests shorter and faster. In other cases, if you don't use frameworks, there is no practical reason to write functional tests.
If you are using a framework other than the ones listed here, create a module for it and share it with community.
| Style : Background15, Font0, Size16 |