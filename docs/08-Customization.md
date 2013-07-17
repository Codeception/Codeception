In this chapter we will explain how can you extend and customize file structure and test execution routine.

## One Runner for Multiple Applications

In case your project consist of several applications (frontend, admin, api) or you use Symfony2 framework with its bundles,
you may be interested in having all tests for all applications (bundles) to be executed in one runner.
In this case you will get one report that covers the whole project.

Starting from Codeception 1.6.3 it's now possible to create a meta-config that includes codeception configs from different places.

Place `codeception.yml` file into root of your project and specify paths to other `codeception.yml` configs you want to include.

``` yaml
include:
  - frontend
  - admin
  - api/rest
paths:
  log: log
settings:
  colors: false
```

You should also specify path to `log` directory, where the reports and logs will be stored.

### Namespaces

To avoid naming conflicts between Guy classes and Helpers classes, they should be added into namespace.
To create test suites with namespaces you can add `--namespace` option to bootstrap command.

``` bash
php codecept.phar bootstrap --namespace frontend

```

This will bootstrap a new project with `namespace: frontend` parameter in `codeception.yml` file. 
Helpers will use `frontend\Codeception\Module` namespace and Guy classes will use `frontend` namespace.
Thus, newly generated tests will have this look:

``` php
<?php use frontend\WebGuy;
$I = new WebGuy($scenario);
//...
?>
```

Codeception have tools to upgrade tests of your current project to use namespaces. By running this command

``` bash
php codecept.phar refactor:add-namespace frontend

```

You will get your guy classes, helpers and cept tests upgraded to use namespaces. Please, note that Cest files should be upgraded manually. Also `namespace` option does not change the namespace of Test or Cest classes. It is used only for Guys and Helpers.

Once each your application (bundle) has its own namespace and different helper or guy classes, you can execute all tests in one runner. Use meta-config we created above and run codeception tests as usual.

```
php codecept.phar run

```

This will launch test suites for all 3 applications and merge the reports from all of them. Basically that would be very useful when you run your tests on conitinous integration server and you want to get one report in JUnit and HTML format. Codecoverage report will be merged too. 

If your application should use the same helpers follow the next section of this chapter.

## Autoload Helper classes

In Codeception 1.6.3 a global `_bootstrap.php` file was introduced. By default you can place it into `tests` directory. If file is there it will be included at the very begining of execution routine. We recommend to use it to initialize autoloaders and constants. It is epecially useful if you want to include Modules or Helper classes that are not stored in `tests/_helpers` direactory.

``` php
<?php
require_once __DIR__.'/../lib/tests/helpers/MyHelper.php'
?>
```

Alternatively you can use Composer's autoloader. Codeception has its autoloader too. 
It's not PSR-0 compatible (yet), but is very useful when you need to declare alternative path for Helper classes:


``` php
<?php
Codeception\Util\Autoload::registerSuffix('Helper', __DIR__.'/../lib/tests/helpers');
?>
```

Now all classes with suffix `Helper` will be additionally searched in `__DIR__.'/../lib/tests/helpers'. You can declare to load helpers of specific namespace. 

``` php
<?php
Codeception\Util\Autoload::register('MyApp\\Test','Helper', __DIR__.'/../lib/tests/helpers');
?>
```

That will point autoloader to look for classes like `MyApp\Test\MyHelper` in path `__DIR__.'/../lib/tests/helpers'`.

Alternatively you can use autoloader to specify path for **PageObject and Controller** classes if they have appropriate suffixes in their name.

Example of `tests/_bootstrap.php` file:

``` php
<?php
Codeception\Util\Autoload::register('MyApp\\Test','Helper', __DIR__.'/../lib/tests/helpers');
Codeception\Util\Autoload::register('MyApp\\Test','Page', __DIR__.'/pageobjects');
Codeception\Util\Autoload::register('MyApp\\Test','Controller', __DIR__.'/controller');
?>
```

## Extension classes

<div class="alert">This section requires advanced PHP skills and some knowlegde of Codeception and PHPUnit internals.</div>

Codeception has limited capabilities to extend its core features.
Extensions are not supposed to override current functionality, but are pretty useful if you are experienced developer and you want to hook into testing flow.

Basically speaking, Extensions are nothing more then event listeners based on [Symfony Event Dispatcher](http://symfony.com/doc/current/components/event_dispatcher/introduction.html) component.

Here are the events and event classes. The events are listed in order they happen during execution. Each event has a class which are passed into listener and contains objects of this event.

### Events


|    Event             |     When?                               | What?                       
|:--------------------:| --------------------------------------- | --------------------------:
| `suite.before`       | Before suite is executed                | [Suite, Settings](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Event/Suite.php)
| `test.start`         | Before test is executed                 | [Test](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Event/Test.php)
| `test.before`        | At the very beginning of test execution | [Codeception Test](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Event/Test.php)
| `step.before`        | Before step                             | [Step](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Event/Step.php)
| `step.after`         | After step                              | [Step](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Event/Step.php)
| `step.fail`          | After failed step                       | [Step](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Event/Step.php)
| `test.fail`          | After failed test                       | [Test, Fail](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Event/Fail.php)
| `test.error`         | After test ended with error             | [Test, Fail](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Event/Fail.php)
| `test.incomplete`    | After executing incomplete test         | [Test, Fail](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Event/Fail.php)
| `test.skipped`       | After executing skipped test            | [Test, Fail](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Event/Fail.php)
| `test.success`       | After executing successful test         | [Test](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Event/Test.php)
| `test.after`         | At the end of test execution            | [Codeception Test](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Event/Test.php)
| `test.end`           | After test execution                    | [Test](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Event/Test.php)
| `suite.after`        | After suite was executed                | [Suite, Result, Settings](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Event/Suite.php)
| `test.fail.print`    | When test fails are printed             | [Test, Fail](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Event/Fail.php)
| `result.print.after` | After result was printed                | [Result, Printer](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Event/PrintResult.php)


There may be a confusion between `test.start`/`test.before` and `test.after`/`test.end`. Start/end events are triggered by PHPUnit itself.
But before/after events are triggered by Codeception. Thus, when you have classical PHPUnit test (extended from `PHPUnit_Framework_TestCase`)
before/after events won't be triggered for them. On `test.before` event you can mark test as skipped or incomplete, which is not possible in test/start. You can learn more from [Codeception internal event listeners](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Subscriber).

The extension class itself is inherited from `Codeception\Platform\Extension`.

``` php
<?php
class MyCustomExtension extends \Codeception\Platform\Extension
{
    // list events to listen to
    static $events = array(
        'suite.after' => 'afterSuite',
        'test.before' => 'beforeTest',
        'step.before' => 'beforeStep',
        'test.fail' => 'testFailed',
        'result.print.after' => 'print',
    );

    // methods that handles events

    function afterSuite(\Codeception\Event\Suite $e) {}

    function beforeTest(\Codeception\Event\Test $e) {}

    function beforeStep(\Codeception\Event\Step $e) {}

    function print(\Codeception\Event\PrintResult $e) {}
}
?>
```  

By implementing evend handling methods you can listen to event and even update passed objects.
Extensions have some basic methods you can use:

* `write` - prints to screen
* `writeln` - prints to screen with line end char at the end
* `getModule` - allows you to access a module.
* `_reconfigure` - can be implemented instead of overriding constructor. 

### Enabling Extension

Once you implemented a simple extension class you should include it into `tests/_bootstrap.php` file:

``` php
<?php
include '/path/to/my/MyCustomExtension.php'
?>
```

Then you can enabled it in `codeception.yml`:

```
extensions:
    enabled: [MyCustomExtension]

```

### Configuring Extension

In extension you can access current passed options in `options` property.
You can access global config via `\Codeception\Configuration::config()` method. 
But if you want to have custom options for your extension you can pass them in `codeception.yml` file:

```
extensions:
    enabled: [MyCustomExtension]
    config:
        MyCustomExtension:
            param: value

```

Passed configuration is accessible via `config` property `$this->config['param']`.

Check out a very basic extension [Notifier](https://github.com/Codeception/Notifier).

## Group Classes

Group Classes are extensions listening to events of a tests belonging to a specific group.
When a test is added to a group:

``` php
<?php 
$scenario->group = 'admin';
$I = new WebGuy($scenario);
?>
```

This test will trigger events:

* `test.before.admin`
* `step.before.admin`
* `step.after.admin`
* `test.success.admin`
* `test.fail.admin`
* `test.after.admin`

A group class is built to listen to this events. It is pretty useful when you require additional setup for some of your tests. Let's say you want to load fixtures for tests belonging to `admin` group.

``` php
<?php
class AdminGroup extends \Codeception\Platform\Group
{
    static $group = 'admin';

    public function _before(\Codeception\Event\Test $e)
    {
        $this->writeln("inserting additional admin users...");

        $db = $this->getModule('Db');
        $db->haveInDatabase('users', array('name' => 'bill', 'role' => 'admin'));
        $db->haveInDatabase('users', array('name' => 'jon', 'role' => 'admin'));
        $db->haveInDatabase('users', array('name' => 'mark', 'role' => 'banned'));
    }

    public function _after(\Codeception\Event\Test $e)
    {
        $this->writeln("cleaning up admin users...");
    }
}
?>
```

A group class can be created with `php codecept.phar generate:group groupname` command.
Group class will be stored in `tests/_groups` directory.

A group class should just the same manner you can enable extension class. In file `codeception.yml`:

``` yaml
extensions:
    enabled: [AdminGroup]    
```

Now Admin group class will listen to all events of tests that belong to the `admin` group.

## Conclusion

Each mentioned feature above may dramaticly help when using Codeception to automate large projects. 
Each of feature requires advanced knowledge of PHP. There is no "best practice" or "use cases" when we talk about groups, extensions, or other power features of Codeception. If you see you have a problem that can be solved using this extensions, then give them a try. 