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

Docker works really well for isolating testing environments. 
By the time of writing this chapter, we didn't have an awesome tool like it. This chapter demonstrates how to manage parallel execution manually. As you will see we spend too much effort trying to isolate tests which Docker does for free. Today we <strong>recommend using Docker</strong> for parallel testing.

## Docker

Please make sure you have `docker` or [Docker Toolbox](https://www.docker.com/products/docker-toolbox) installed. Docker experience is required as well.

### Using Codeception Docker image

Run official Codeception image from DockerHub:

    docker run codeception/codeception    

Running tests from a project, by mounting the current path as a host-volume into the container.
The default working directory in the container is `/project`.
    
    docker run -v ${PWD}:/project codeception/codeception run

To prepare application and tests to be executed inside containers you will need to use [Docker Compose](https://docs.docker.com/compose/) to run multiple containers and connect them together. 

Define all required services in `docker-compose.yml` file. Make sure to follow Docker philisophy: 1 service = 1 container. So each process should be defined as its own service. Those services can use official Docker images pulled from DockerHub. Directories with code and tests should be mounted using `volume` directive. And exposed ports should be explicitly set using `ports` directive.

We prepared a sample config with codeception, web server, database, and selenium with firefox to be executed together.

```yaml
version: '2'
services:
  codeception:
    image: codeception/codeception
    depends_on:
      - firefox    
      - web
    volumes:
      - ./src:/src      
      - ./tests:/tests
      - ./codeception.yml:/codeception.yml
  web:
    image: php:7-apache
    depends_on:
      - db  
    volumes:
      - .:/var/www/html      
  db:
    image: percona:5.6
    ports:
      - '3306'
  firefox:
    image: selenium/standalone-firefox-debug:2.53.0
    ports:
      - '4444'
      - '5900'
```

Codeception service will execute command `codecept run` but only after all services are started. This is defined using `depends_on` parameter. 

It is easy to add more custom services. For instance to use Redis you just simple add this lines:

```yaml
  redis:
    image: redis:3
```

By default the image has codecept as its entrypoint, to run the tests simply supply the run command

```
docker-compose run --rm codecept help
```

Run suite

```
docker-compose run --rm codecept run acceptance
```


```
docker-compose run --rm codecept run acceptance LoginCest
```

Development bash

```
docker-compose run --rm --entrypoint bash codecept
```


And finally to execute testing in parallel you should define how you split your tests and run parallel processes for `docker-compose`. Here we split tests by suites, but you can use different groups to split your tests. In section below you will learn how to do that with Robo.

```
docker-compose --project-name test-web run -d --rm codecept run --html report-web.html web & \
docker-compose --project-name test-unit run -d --rm codecept run --html report-unit.html unit & \
docker-compose --project-name test-functional run -d --rm codecept run --html report-functional.html functional
```

At the end, it is worth specifying that Docker setup can be complicated and please make sure you understand Docker and Docker Compose before proceed. We prepared some links that might help you:

* [Acceptance Tests Demo Repository](https://github.com/dmstr/docker-acception)
* [Dockerized Codeception Internal Tests](https://github.com/Codeception/Codeception/blob/master/tests/README.md#dockerized-testing)
* [Phundament App with Codeception](https://gist.github.com/schmunk42/d6893a64963509ff93daea80f722f694)

If you want to automate splitting tests by parallel processes, and executing them using PHP script you should use Robo task runner to do that.

## Robo

### What to do

Parallel Test Execution consists of 3 steps:

* splitting tests
* running tests in parallel
* merging results

We propose to perform those steps using a task runner. In this guide we will use [**Robo**](http://robo.li) task runner. It is a modern PHP task runner that is very easy to use. It uses [Symfony Process](http://symfony.com/doc/current/components/process.html) to spawn background and parallel processes. Just what we need for the step 2! What about steps 1 and 3? We have created robo [tasks](https://github.com/Codeception/robo-paracept) for splitting tests into groups and merging resulting JUnit XML reports.

To conclude, we need:

* [Robo](http://robo.li), a task runner.
* [robo-paracept](https://github.com/Codeception/robo-paracept) - Codeception tasks for parallel execution.
 
## Preparing Robo and Robo-paracept

Execute this command in an empty folder to install Robo and Robo-paracept :
```bash
$ composer require codeception/robo-paracept:dev-master
```

You need to install Codeception after, if codeception is already installed it will not work.
```bash
$ composer require codeception/codeception
```


### Preparing Robo

Intitalizes basic RoboFile in the root of your project

```bash
$ robo init
```

Open `RoboFile.php` to edit it

```php
<?php

class RoboFile extends \Robo\Tasks
{
    // define public methods as commands
}
```

Each public method in robofile can be executed as a command from console. Let's define commands for 3 steps and include autoload.

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
```

If you run `robo`, you can see the respective commands:

```bash
$ robo
Robo version 0.6.0

Usage:
  command [options] [arguments]

Options:
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  help                    Displays help for a command
  list                    Lists commands
 parallel
  parallel:merge-results  
  parallel:run            
  parallel:split-tests   
```

#### Step 1: Split Tests

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
        // Split your tests by files
        $this->taskSplitTestFilesByGroups(5)
            ->projectRoot('.')
            ->testsFrom('tests/acceptance')
            ->groupsTo('tests/_data/paracept_')
            ->run();
            
        /*
        // Split your tests by single tests (alternatively)
        $this->taskSplitTestsByGroups(5)
            ->projectRoot('.')
            ->testsFrom('tests/acceptance')
            ->groupsTo('tests/_data/paracept_')
            ->run();
        */
    }    

```

Let's prepare group files:

```bash
$ robo parallel:split-tests

 [Codeception\Task\SplitTestFilesByGroupsTask] Processing 33 files
 [Codeception\Task\SplitTestFilesByGroupsTask] Writing tests/_data/paracept_1
 [Codeception\Task\SplitTestFilesByGroupsTask] Writing tests/_data/paracept_2
 [Codeception\Task\SplitTestFilesByGroupsTask] Writing tests/_data/paracept_3
 [Codeception\Task\SplitTestFilesByGroupsTask] Writing tests/_data/paracept_4
 [Codeception\Task\SplitTestFilesByGroupsTask] Writing tests/_data/paracept_5
```

Now we have group files. We should update `codeception.yml` to load generated group files. In our case we have groups: *paracept_1*, *paracept_2*, *paracept_3*, *paracept_4*, *paracept_5*.

```yaml
groups:
    paracept_*: tests/_data/paracept_*
```

Let's try to execute tests from the second group:

```bash
$ codecept run acceptance -g paracept_2
```

#### Step 2: Running Tests

Robo has `ParallelExec` task to spawn background processes.

##### Inside Container

If you are using [Docker](#docker)  containers you can launch multiple Codeception containers for different groups:


```php
public function parallelRun()
{
    $parallel = $this->taskParallelExec();
    for ($i = 1; $i <= 5; $i++) {            
        $parallel->process(
            $this->taskExec('docker-compose run --rm codecept run')
                ->opt('group', "p$i") // run for groups p*
                ->opt('xml', "tests/_log/result_$i.xml"); // provide xml report
        );
    }
    return $parallel->run();
}
```

##### Locally

If you want to run tests locally just use preinstalled `taskCodecept` task of Robo to define Codeception commands and put them inside `parallelExec`.

```php
<?php
public function parallelRun()
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
```


In case you don't use containers you can isolate processes by starting different web servers and databases per each test process.

We can define different databases for different processes. This can be done using [Environments](http://codeception.com/docs/07-AdvancedUsage#Environments). Let's define 5 new environments in `acceptance.suite.yml`:

```yaml
actor: AcceptanceTester
modules:
    enabled:
        - Db:
            dsn: 'mysql:dbname=testdb;host=127.0.0.1' 
            user: 'root'
            dump: 'tests/_data/dump.sql'
            populate: true
            cleanup: true
        - WebDriver:
            url: 'http://localhost/'
env:
    env1:
        modules:
            config:
                Db:
                    dsn: 'mysql:dbname=testdb_1;host=127.0.0.1' 
                WebDriver:
                    url: 'http://test1.localhost/'
    env2:
        modules:
            config:
                Db:
                    dsn: 'mysql:dbname=testdb_2;host=127.0.0.1' 
                WebDriver:
                    url: 'http://test2.localhost/'
    env3:
        modules:
            config:
                Db:
                    dsn: 'mysql:dbname=testdb_3;host=127.0.0.1' 
                WebDriver:
                    url: 'http://test3.localhost/'
    env4:
        modules:
            config:
                Db:
                    dsn: 'mysql:dbname=testdb_4;host=127.0.0.1' 
                WebDriver:
                    url: 'http://test4.localhost/'
    env5:
        modules:
            config:
                Db:
                    dsn: 'mysql:dbname=testdb_5;host=127.0.0.1' 
                WebDriver:
                    url: 'http://test5.localhost/'
```


----

After the `parallelRun` method is defined you can execute tests with

```bash
$ robo parallel:run
```

#### Step 3: Merge Results

In case of `parallelExec` task we recommend to save results as JUnit XML, which can be merged and plugged into Continuous Integration server.

```php
<?php
    function parallelMergeResults()
    {
        $merge = $this->taskMergeXmlReports();
        for ($i=1; $i<=5; $i++) {
            $merge->from("tests/_output/result_paracept_$i.xml");
        }
        $merge->into("tests/_output/result_paracept.xml")->run();
    }

```
Now, we can execute :
```bash
$ robo parallel:merge-results
```
`result_paracept.xml` file will be generated. It can be processed and analyzed.

#### All Together

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

```


## Conclusion

Codeception does not provide tools for parallel test execution. This is a complex task and solutions may vary depending on a project. We use [Robo](http://robo.li) task runner as an external tool to perform all required steps. To prepare our tests to be executed in parallel we use Codeception features of dynamic groups and environments. To do even more we can create Extensions and Group classes to perform dynamic configuration depending on a test process.
