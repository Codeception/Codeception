# Codeception Internal Tests

In case you submit a pull request, you will be asked to write a test.

There are 3 suites for testing

* cli - acceptance tests of CLI commands
* coverage - acceptance tests of code coverage
* unit - all unit/integration/etc tests.

Tests of the modules (PhpBrowser, WebDriver, Db, REST, ...) are not part of this repository.
They live in the repository of each module, e.g. [module-phpbrowser](https://github.com/Codeception/module-phpbrowser).

## Set up

1. Clone the repository to your local machine
2. Run `composer install` in the cloned project directory
3. Generate the actor classes:
    ```
    php codecept build
    ```
4. Some tests need the demo application. Start PHP's internal webserver in the project directory:
    ```
    php -S 127.0.0.1:8008 -t tests/data/app
    ```

The coverage suite additionally requires [Xdebug](https://xdebug.org/) or [PCOV](https://github.com/krakjoe/pcov).

## Running tests

Run all suites like the CI does:

```
php codecept run cli,unit,coverage
```

Run a suite, a directory or a single test:

```
php codecept run cli
php codecept run unit Codeception/Command
php codecept run cli ExtensionsCest
```

Before sending a pull request, check the code style:

```
composer cs-prod
composer cs-tests
```

## Unit

Unit tests are located in `tests/unit` and follow the directory structure of `src`, e.g. the tests for `src/Codeception/Util/Annotation.php` are in `tests/unit/Codeception/Util/AnnotationTest.php`.

They are PHPUnit tests, most of them extend `Codeception\Test\Unit` or `Codeception\PHPUnit\TestCase`.

Tests of console commands extend `BaseCommandRunner` (`tests/unit/Codeception/Command/BaseCommandRunner.php`), which executes a command, collects its output and records the files the command would create instead of writing them.

## Cli

For most cases Codeception is self-tested using acceptance tests in *cli* suite. That is how Codeception core classes are tested. And actually there is no possibility to unit test many cases. Because you can't ask PHPUnit to mock PHPUnit classes.

If you send Pull Request to Codeception core and you don't know how to get it tested, just create new cli test for that. Probably you will need some additional files, maybe another suite configurations, so add them.

You can run all cli tests with

```
php codecept run cli
```

Test cases are:

* generating codeception templates
* running tests with different configs in different modes
* testing execution order
* running multi-app tests
* etc

### Claypit + Sandbox

Before each test `tests/data/claypit` is copied to `tests/data/sandbox`, and all the test actions will be executed inside that sandbox. In the end this directory is removed. In sandbox different codeception tests may be executed and checked for expected output.

Example test:

```php
<?php
$I = new \Tests\Support\CliTester($scenario);
$I->wantToTest('build command');
$I->runShellCommand('php codecept build');
$I->seeInShellOutput('generated successfully');
$I->seeFileFound('CodeTester.php','tests/unit');
$I->seeFileFound('CliTester.php','tests/cli');
$I->seeInThisFile('seeFileFound(');
?>
```

There are various test suites in `tests/data/claypit`. Maybe you will need to create a new suite for your tests.

### Demo Application

Some suites in `tests/data/claypit` (e.g. `remote`) send requests to the demo application in `tests/data/app`, which has to be running on `http://127.0.0.1:8008` (see [Set up](#set-up)).

It is a very basic PHP application developed with `glue` microframework. To add a new html page for a test:

1. Create a new file in `tests/data/app/view`
2. Add a route in `tests/data/app/index.php`
3. Add a class in `tests/data/app/controllers.php`

To see the page in the browser, open `http://127.0.0.1:8008/your-route`

## Coverage

Acceptance tests that use demo application and `c3` collector, to check that a code coverage can be collected remotely. These tests are rarely updated, they should just work )

---

## Running tests with Docker

If you don't want to install PHP locally, you can run the tests in a container with [`tests/compose.yaml`](compose.yaml).
It uses the official `php` image with Composer and Xdebug added, mounts the repository and starts the demo application in the background.
The container runs as user and group 1000, set `UID` and `GID` if your ids differ:

    export UID GID=$(id -g)

Install the dependencies and generate the actor classes

    docker compose -f tests/compose.yaml run --rm php composer install
    docker compose -f tests/compose.yaml run --rm php php codecept build

Run the cli and unit suites, except the tests which require code coverage

    docker compose -f tests/compose.yaml run --rm php

Run a folder or a single test

    docker compose -f tests/compose.yaml run --rm php php codecept run unit Codeception/Command
    docker compose -f tests/compose.yaml run --rm php php codecept run cli ExtensionsCest

Open a shell

    docker compose -f tests/compose.yaml run --rm php sh

Run all suites including the tests which require code coverage (Xdebug is disabled by default, as it slows down the tests)

    docker compose -f tests/compose.yaml run --rm -e XDEBUG_MODE=coverage php php codecept run cli,unit,coverage

Run the tests with another PHP version (default: 8.2, the lowest supported version). The dependencies have to be updated when switching the version, as `composer.lock` and `vendor` are shared with the host

    export PHP_VERSION=8.5
    docker compose -f tests/compose.yaml run --rm php composer update
    docker compose -f tests/compose.yaml run --rm php

Note: the `Dockerfile` in the root of the repository builds the `codeception/codeception` image for running *your* tests, it is not meant for developing Codeception.
