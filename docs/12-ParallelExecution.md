# Parallel Execution

When execution time of your tests is longer than a coffee break, it is a good reason to think about making your tests faster. If you have already tried to run them on SSD drive, or to use PhantomJS instead of Selenium, and the execution time still upsets you, it might be a good idea to run your tests in parallel.

## Where to start

Codeception does not provide a command like `run-parallel`. There is no common solution that can play well for everyone. Here are the questions you will need to answer:

* How parallel processes will be executed?
* How parallel processes won't affect each other?
* Will they use different databases?
* Will they use different hosts?
* How should I split my tests across parallel processes?

There are two approaches to achieve parallelization. We can use [Docker](http://docker.com) and run each process inside isolated containers, and have those containers executed simultaneously. 

<div class="alert alert-warning">
Docker works really well for isolating testing environments. 
By the time of writing this chapter, we didn't have an awesome tool like it. This chapter demonstrates how to manage parallel execution manually. As you will see we spend too much effort trying to isolate tests which Docker does for free. Today we **recommend using Docker** for parallel testing.
</div>

## What to do

Parallel Test Execution consists of 3 steps:

* splitting tests
* running tests in parallel
* merging results

We propose to perform those steps using a task runner. In this guide we will use [**Robo**](http://robo.li) task runner. It is a modern PHP task runner that is very easy to use. It uses [Symfony Process](http://symfony.com/doc/current/components/process.html) to spawn background and parallel processes. Just what we need for the step 2! What about steps 1 and 3? We have created robo [tasks](https://github.com/Codeception/robo-paracept) for splitting tests into groups and merging resulting JUnit XML reports.

To conclude, we need:

* [Robo](http://robo.li), a task runner.
* [robo-paracept](https://github.com/Codeception/robo-paracept) - Codeception tasks for parallel execution.

## Preparing Robo

`Robo` is recommended to be installed globally. You can either do [a global install with Composer](https://getcomposer.org/doc/03-cli.md#global) or download `robo.phar` and put it somewhere in PATH.

Execute `robo` in the root of your project

```bash
$ robo
  RoboFile.php not found in this dir
  Should I create RoboFile here? (y/n)
```

Confirm to create `RoboFile.php`.

```php
<?php
class RoboFile extends \Robo\Tasks
{
    // define public methods as commands
}
?>
```

Install `codeception/robo-paracept` via Composer and include it into your RoboFile.

Each public method in robofile can be executed as a command from console. Let's define commands for 3 steps.

```php
<?php
require_once 'vendor/autoload.php';

class Robofile extends \Robo\Tasks
{
    use \Codeception\Task\MergeReports;
    use \Codeception\Task\SplitTestsByGroups;

    public function parallelSplitTests()
    {

    }

    public function parallelRun()
    {

    }

    public function parallelMergeResults()
    {

    }
}
?>
```

If you run `robo`, you can see the respective commands:

```bash
$ robo
Robo version 0.4.4
---
Available commands:
  help                     Displays help 
  list                     Lists commands
parallel
  parallel:merge-results   
  parallel:run             
  parallel:split-tests     
```

## Sample Project

Let's say we have long running acceptance tests and we want to split them into 5 processes. To make tests not be conflicting with each other they should use different hosts and databases. Thus, before proceeding we need to configure 5 different hosts in Apache/Nginx (or just run application on different ports in PHP Built-in web server). Based on host our application should use corresponding databases.

Alternatively you can prepare **isolated environments** using [Docker](https://www.docker.io/) or [LXC](https://linuxcontainers.org/) and make each testing process to be executed in its own container. Spawn new containers and run more processes are much easier then manually creating additional databases and hosts. Also you are creating a more stable testing environments (no database, files, process conflicts). But you will have to provision your containers as you regularly do when creating virtual machines.

You can also think about running your tests on remote hosts using SSH. `Robo` has built-in tasks for running SSH commands as well.

In current example we assume that we have prepared 5 databases and 5 independent hosts for our application.

### Step 1: Split Tests

Codeception can organize tests into [groups](http://codeception.com/docs/07-AdvancedUsage#Groups). Starting from 2.0 it can load information about a group from a files. Sample text file with a list of file names can be treated as a dynamically configured group. Take a look into sample group file:

```bash
tests/functional/LoginCept.php
tests/functional/AdminCest.php:createUser
tests/functional/AdminCest.php:deleteUser
```

Tasks from `\Codeception\Task\SplitTestsByGroups` will generate non-intersecting group files.  You can either split your tests by files or by single tests:

```php
<?php
    function parallelSplitTests()
    {
        $this->taskSplitTestFilesByGroups(5)
            ->projectRoot('.')
            ->testsFrom('tests/functional')
            ->groupsTo('tests/_log/p')
            ->run();

        // alternatively
        $this->taskSplitTestsByGroups(5)
            ->projectRoot('.')
            ->testsFrom('tests/functional')
            ->groupsTo('tests/_log/p')
            ->run();
    }    
?>
```

In second case `Codeception\TestLoader` class will be used and test classes will be loaded into memory.

Let's prepare group files:

```bash
$ robo parallel:split-tests

 [Codeception\Task\SplitTestFilesByGroupsTask] Processing 33 files
 [Codeception\Task\SplitTestFilesByGroupsTask] Writing tests/_log/p1
 [Codeception\Task\SplitTestFilesByGroupsTask] Writing tests/_log/p2
 [Codeception\Task\SplitTestFilesByGroupsTask] Writing tests/_log/p3
 [Codeception\Task\SplitTestFilesByGroupsTask] Writing tests/_log/p4
 [Codeception\Task\SplitTestFilesByGroupsTask] Writing tests/_log/p5
```

Now we have group files. We should update `codeception.yml` to load generated group files. In our case we have groups: *p1*, *p2*, *p3*, *p4*, *p5*.

```yaml
groups:
    p*: tests/_log/p*
```

Let's try to execute tests from the second group:

```bash
$ php codecept.phar run functional -g p2
```

### Step 2: Running Tests

As it was mentioned, Robo has `ParallelExec` task to spawn background processes. But you should not think of it as the only option. For instance, you can execute tests remotely via SSH, or spawn processes with Gearman, RabbitMQ, etc. But in our example we will use 5 background processes:

```php
<?php
    function parallelRun()
    {
        $parallel = $this->taskParallelExec();
        for ($i = 1; $i <= 5; $i++) {            
            $parallel->process(
                $this->taskCodecept() // use built-in Codecept task
                ->suite('acceptance') // run acceptance tests
                ->group("p$i")        // for all p* groups
                ->xml("tests/_log/result_$i.xml") // save XML results
            );
        }
        return $parallel->run();
    }
?>    
```

We missed something really important. We forgot to define different databases for different processes. This can be done using [Environments](http://codeception.com/docs/07-AdvancedUsage#Environments). Let's define 5 new environments in `acceptance.suite.yml`:

```yaml
class_name: AcceptanceTester
modules:
    enabled: [WebDriver, Db]
    config:
        Db:
            dsn: 'mysql:dbname=testdb;host=127.0.0.1' 
            user: 'root'
            dump: 'tests/_data/dump.sql'
            populate: true
            cleanup: true
        WebDriver:
            url: 'http://localhost/'
env:
    p1:
        modules:
            config:
                Db:
                    dsn: 'mysql:dbname=testdb_1;host=127.0.0.1' 
                WebDriver:
                    url: 'http://test1.localhost/'
    p2:
        modules:
            config:
                Db:
                    dsn: 'mysql:dbname=testdb_2;host=127.0.0.1' 
                WebDriver:
                    url: 'http://test2.localhost/'
    p3:
        modules:
            config:
                Db:
                    dsn: 'mysql:dbname=testdb_3;host=127.0.0.1' 
                WebDriver:
                    url: 'http://test3.localhost/'
    p4:
        modules:
            config:
                Db:
                    dsn: 'mysql:dbname=testdb_4;host=127.0.0.1' 
                WebDriver:
                    url: 'http://test4.localhost/'
    p5:
        modules:
            config:
                Db:
                    dsn: 'mysql:dbname=testdb_5;host=127.0.0.1' 
                WebDriver:
                    url: 'http://test5.localhost/'
```

Now, we should update our `parallelRun` method to use corresponding environment:

```php
<?php
    function parallelRun()
    {
        $parallel = $this->taskParallelExec();
        for ($i = 1; $i <= 5; $i++) {            
            $parallel->process(
                $this->taskCodecept() // use built-in Codecept task
                ->suite('acceptance') // run acceptance tests
                ->group("p$i")        // for all p* groups
                ->env("p$i")          // in its own environment
                ->xml("tests/_log/result_$i.xml") // save XML results
              );
        }
        return $parallel->run();
    }
?>    
```

Now, we can execute tests with

```bash
$ robo parallel:run
```

### Step 3: Merge Results

We should not rely on console output when running our tests. In case of `parallelExec` task, some text can be missed. We recommend to save results as JUnit XML, which can be merged and plugged into Continuous Integration server.

```php
<?php
    function parallelMergeResults()
    {
        $merge = $this->taskMergeXmlReports();
        for ($i=1; $i<=5; $i++) {
            $merge->from("/tests/_log/result_$i.xml");
        }
        $merge->into("/tests/_log/result.xml")
            ->run();
    }
?>
```

`result.xml` file will be generated. It can be processed and analyzed.

### All Together

To create one command to rule them all we can define new public method `parallelAll` and execute all commands. We will save the result of `parallelRun` and use it for our final exit code:

```php
<?php
    function parallelAll()
    {
        $this->parallelSplitTests();
        $result = $this->parallelRun();
        $this->parallelMergeResults();
        return $result;
    }
?>
```

## Conclusion

Codeception does not provide tools for parallel test execution. This is a complex task and solutions may vary depending on a project. We use [Robo](http://robo.li) task runner as an external tool to perform all required steps. To prepare our tests to be executed in parallel we use Codeception features of dynamic groups and environments. To do even more we can create Extensions and Group classes to perform dynamic configuration depending on a test process.
