# Functional Tests

We've written some of acceptance tests. Functional tests are just the same. With only one major difference. Functional tests doesn't require a web server to run your scenarios. In other words, we will run your application inside the tests, emulating requests and response.

In a simple terms we are setting $_REQUEST, $_GET and $_POST variables, then we executing your script inside a test, and we receive output, then testing it. 
For some reasons it may be better then acceptance testing, this doesn't require web server and may provide you detailed debug output. For example, If your site throws an exception, it will be shown in console. 

Here is the exeception. If it was inside an acceptance test you would just see the error page, but here we see the actual exception with a stack trace. 

```

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

Good integration can allow you perform additional operations, like checking if the email was sent, or authenticating the users.

Codeception can connect to different web frameworks which support functional testing. For example you can run a functional test for application build on top of Zend Framework, symfony or Symdony2 with just the modules prvided by Codeception! List of supported frameworks will be extended.

Modules for all this frameworks share the same interface, and thus your tests are not bounded to any of them!
You can even run your acceptance tests as a functional and vice versa.

We recommend writing a tests on unstable parts as a functional, and testing a stable parts with acceptance tests. 

Please note, that acceptance tests are much slower then functional, as they require database repopulation on each run. For sites that are using Doctrine ORM all operations are performed inside a transactions, which will be rolled back at the end. This will work much faster then rebuilding database from dump on each test. 

## Connecting Frameworks

Let's see you can integrate Codeception with your favorite framework. 

### Symfony2

To perform Symfony2 integrations you don't need to install any bundles or perform any configuration changes.
You just need to include Symfony2 module into your suite. If you also use Doctrine2, don't forget to include it either.

Example for 'functional.suite.yml'

```
class_name: TestGuy
modules:
    enabled: [Symfony2, Doctrine2, TestHelper] 
```

By default this module will search for Kernel in 'app' directory.
Module uses Symfony Profiler ro provide additional information and assertions.

[See the full reference](http://codeception.com/docs/modules/Symfony2)

### Zend Framework 1.x

Module for Zend Framework is highly inspired by ControllerTestCase class, used for functional testing with PHPUnit. 
It follows similar approcahes for bootstrapping and cleaning up. To start using Zend Framework in your functional tests, include ZF1 module.

Example for 'functional.suite.yml'

```
class_name: TestGuy
modules:
    enabled: [ZF1, TestHelper] 
```

[See the full reference](http://codeception.com/docs/modules/ZF1)

### symfony

This module was the first one developed for Codeception. Because of this, it's actions may differ from what are used in another frameworks.
It provides varaious useful operations like logging in user with sfGuardAuth or validating the form inside a test.

Example for 'functional.suite.yml'

```
class_name: TestGuy
modules:
    enabled: [Symfony1, TestHelper] 
```

[See the full reference](http://codeception.com/docs/modules/Symfony1)


## Integrating Other Framwork

Codeception doesn't provide any general functional testing module because there are a lot of details we can't implment in general.
So if you don't use any of frameworks listed above, you might want to integrate your framework into Codeception. That task requires some knowledge of Codeception internals and some time. Probably, you are ok with just acceptnace tests, but any help in extending Codeception functionality will be appreciated. We will review what should be done to have your framework intrgrated.

Codeception uses [BrowserKit](https://github.com/symfony/BrowserKit) from Symfony Components to interact with applications in functional tests. This component provides all common actions we see in modules: click, fillField, see, etc... Thus, you have no need in writing this methods in your module. For the integration you should provide a bridge from BrowserKit to your application.

We will start with writing a helper class for framework integration.

``` php
<?php
namespace Codeception\Module;
class SomeFrameworkHelper extends \Codeception\Util\Framework {
	
}
?>
```

Let's investigate [Codeception source code](https://github.com/Codeception/Codeception).
Look into src/Util/Framework.php file we extend. It implements all common actions for all frameworks.

You may see that all interactions are performed by 'client' instance. We need to create a client that connects to a framework.
Client should extend Symfony\BrowserKit\Client module, sample clients are in src/Util/Connector path. 
If framework doesn't provide it's own tools for functional testing you can try using Universal connector. Otherwise, look how Zend connector is implemented, and implement your own connector.

Whenever you decide using Universal connector or write your own, you can include it into your module.

``` php
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

If you include this helper into your suite it can already run perform interactions with your applications.
You can extend it's abilitites by connecting to framework internals. It's important to perform a proper clean up on each test run. 
This can be done in _before and _after methods of helper module. Check that framework doesn't cache any data or configuration between tests.

After you get your module stabilizied - share it with community. Fork a Codeception repository, add your module and make Pull Request.

There are some requirements for modules:

* it should be easy to configure
* it should contain proper documentation.
* it should extend basic operations by using framework internals.
* it's preferred to make it print additional debug informations.

We don't requre unit tests, because there is no good way for writing proper unit tests for framework integration.
But you can demonstrate a sample application with your framwork which uses Codeception tests and your module. 

## Conclusion

Functional tests are great if your are using powerful frameworks. By using test you can access and manipulate their internal state. 
This makes your tests shorter and faster. In other case, If you don't use frameworks, there is no practical reason writing functional tests.
If you are using other framework then listed here - create a module for it and share it with community.