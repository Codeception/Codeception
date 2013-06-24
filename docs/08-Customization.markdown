---
layout: doc
title: Codeception - Documentation
---

In this chapter we will explain how can you extend and customize file structure and test execution routine.

## One Runner for Multiple Applications

In case your project consist of several applications (frontend, admin, api) or you use Symfony2 framework with its bundles,
you may be interested in having all tests for all applications (bundles) to be executed in one runner.
In this case you will get one report that covers the whole project.

Starting from Codeception 1.6.3 it's now possible to create a meta-config that includes codeception configs from different places.

Place `codeception.yml` file into root of yout project and specify paths to other `codeception.yml` configs you want to include.

{% highlight yaml %}
 yaml
include:
  - frontend
  - admin
  - api/rest
paths:
  log: log
settings:
  colors: false

{% endhighlight %}

You should also specify path to `log` directory, where the reports and logs will be stored.

### Namespaces

To avoid naming conflicts between Guy classes and Helpers classes, they should be added into namespace.
To create test suites with namespaces you can add `--namespace` option to bootstrap command.

{% highlight yaml %}
 bash
php codecept.phar bootstrap --namespace frontend


{% endhighlight %}

This will bootstrap a new project with `namespace: frontend` parameter in `codeception.yml` file. 
Helpers will use `frontend\Codeception\Module` namespace and Guy classes will use `frontend` namespace.
Thus, newly generated tests will have this look:

{% highlight php %}

<?php use frontend\WebGuy;
$I = new WebGuy($scenario);
//...
?>

{% endhighlight %}

Codeception have tools to upgrade tests of your current project to use namespaces. By running this command

{% highlight yaml %}
 bash
php codecept.phar refactor:add-namespace frontend


{% endhighlight %}

You will get your guy classes, helpers and cept tests upgraded to use namespaces. Please, note that Cest files should be upgraded manually. Also `namespace` option does not change the namespace of Test or Cest classes. It is used only for Guys and Helpers.

Once each your application (bundle) has its own namespace and different helper or guy classes, you can execute all tests in one runner. Use meta-config we created above and run codeception tests as usual.

{% highlight php %}
 codecept.phar run


{% endhighlight %}

This will launch test suites for all 3 applications and merge the reports from all of them. Basically that would be very useful when you run your tests on conitinous integration server and you want to get one report in JUnit and HTML format. Codecoverage report will be merged too. 

If your application should use the same helpers follow the next section of this chapter.

## Autoload Helper classes

In Codeception 1.6.3 a global `_bootstrap.php` file was introduced. By default you can place it into `tests` directory. If file is there it will be included at the very begining of execution routine. We recommend to use it to initialize autoloaders and constants. It is epecially useful if you want to include Modules or Helper classes that are not stored in `tests/_helpers` direactory.

{% highlight php %}

<?php
require_once __DIR__.'/../lib/tests/helpers/MyHelper.php'
?>

{% endhighlight %}

Alternatively you can use Composer's autoloader. Codeception has its autoloader too. 
It's not PSR-0 compatible (yet), but is very useful when you need to declare alternative path for Helper classes:


{% highlight php %}

<?php
Codeception\Util\Autoload::registerSuffix('Helper', __DIR__.'/../lib/tests/helpers');
?>

{% endhighlight %}

Now all classes with suffix `Helper` will be additionally searched in `__DIR__.'/../lib/tests/helpers'. You can declare to load helpers of specific namespace. 

{% highlight php %}

<?php
Codeception\Util\Autoload::register('MyApp\\Test','Helper', __DIR__.'/../lib/tests/helpers');
?>

{% endhighlight %}

That will point autoloader to look for classes like `MyApp\Test\MyHelper` in path `__DIR__.'/../lib/tests/helpers'`.

Alternatively you can use autoloader to specify path for **PageObject and Controller** classes if they have appropriate suffixes in their name.

Example of `tests/_bootstrap.php` file:

{% highlight php %}

<?php
Codeception\Util\Autoload::register('MyApp\\Test','Helper', __DIR__.'/../lib/tests/helpers');
Codeception\Util\Autoload::register('MyApp\\Test','Page', __DIR__.'/pageobjects');
Codeception\Util\Autoload::register('MyApp\\Test','Controller', __DIR__.'/controller');
?>

{% endhighlight %}

## Extension classes

*coming soon*

## Group Classes

*coming soon*




* **Next Chapter: [Data >](/docs/09-Data)**
* **Previous Chapter: [< AdvancedUsage](/docs/07-AdvancedUsage)**