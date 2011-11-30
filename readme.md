# Codeception

Codeception is new PHP full-stack testing framework.
Inspired by BDD it shows you totally new way for writing acceptance, functional and even unit tests.
Powered by PHPUnit 3.6.

Previously called 'TestGuy'. Now is extended to feature CodeGuy, TestGuy, and WebGuy in one pack.

# CodeGuy
## Functional Testing Framework

CodeGuy is a way of writing unit tests in descriptive way.
Allows easily define and use mocks and stubs inside your class.

Check this:

``` php
<?php
$I = new CodeGuy($scenario);
$I->wantTo('run steps from scenario');
$I->testMethod('\Codeception\Scenario.run');
$I->haveFakeClass($test = Stub::makeEmpty('\Codeception\TestCase'));
$I->haveFakeClass($scenario = Stub::make('\Codeception\Scenario', array(
    'test' => $test,
    'steps' => array(
        Stub::makeEmpty('\Codeception\Step\Action'),
        Stub::makeEmpty('\Codeception\Step\Comment')
    )
)));
$I->executeTestedMethodOn($scenario);
$I->seeMethodInvoked($test,'runStep');
$I->seePropertyEquals($scenario, 'currentStep', 1);
```

This is a test scenario. What happens if we run it?

If we start this test in debug mode we will see passed steps one by one.
Each steps priovides us additional information which can be helpful on debug.

```

# Trying to run steps from scenario with \Codeception\Scenario.run (runSpec.php)
# Scenario:

* I test method "\Codeception\Scenario.run"
=> Class: \Codeception\Scenario
=> Method: run
* I have fake class "\Codeception\TestCase"
=> [Registered stub] Stub_0 {\Codeception\TestCase}
* I have fake class "\Codeception\Scenario"
=> [Registered stub] Stub_1 {\Codeception\Scenario}
* I execute tested method on "\Codeception\Scenario"
=> Received STUB
=> PHPUnit_Framework_MockObject_Matcher_InvokedAtLeastOnce attached
=> method run executed
=> Result: null
* I see method invoked ["\\Codeception\\TestCase","runStep"]
=> [Triggered Stub] Stub_0 {\Codeception\TestCase}
* I see property equals ["\\Codeception\\Scenario","currentStep",1]
=> Received STUB
=> Property value is: 1-


```

# TestGuy
## Functional Testing Framework

TestGuy is a functional testing framework powered by PHPUnit.

## Principles
TestGuy library allows to write test scenarios in PHP with DSL designed to look like native English.
Imagine your tester describes her actions and you write them as a functional tests!
Tester can perform some actions and see the results. Whenever she doesn't see expected value the test fails.
This is how testers act. And this is the same how TestGuy is acting.

TestGuy knows a little about internals of your application. When error occur it won't tell you which module triggered this error.
Still it makes you confident that your app is still running correctly and users can perform the same scenarios the TestGuy does.

Cover your application with functional tests and let them stay simple to read, simple to write, and simple to debug.
Use TestGuy!

## In a Glance
This is the sample TestGuy test. User is accessing the site to create a new wiki page about the movie.
He gets to 'new' page, submits form, and sees the page he just created. Also he performs additional checks, if the slug is generated and if the database record is saved.

``` php
<?php

$I = new TestGuy($scenario);
$I->wantTo('create wiki page');
$I->amOnPage('/');
$I->click('Pages');
$I->click('New');
$I->see('New Page');
$I->submitForm('#pageForm', array('page' => array(
    'title' => 'Tree of Life Movie Review',
    'body' => 'Next time don\'t let Hollywood create arthouse =) '
)));
$I->see('page created'); // notice generated
$I->see('Tree of Life Movie Review','h1'); // head of page of is our title
$I->seeInCurrentAddress('pages/tree-of-life-mobie-review'); // slug is generated
$I->seeInDatabase('pages', array('title' => 'Tree of Life Movie Review')); // data is stored in database

```

## About

TestGuy uses PHPUnit (http://http://www.phpunit.de/) as backend for testing framework. If you are familiar with PHPUnit you can your TestGuy installation with it's features.
Also TestGuy uses Mink (http://mink.behat.org/) a powerful library that provides interface for browser emulators.
TestGuy was developed as symfony1 plugin and now it's migrated to standalone version. You can test any project with it!

## Install

If you need stable standalone version download [https://github.com/downloads/DavertMik/TestGuy_Standalone/testguy.phar](phar package).

Put it wherever you expect to store your test suites.

Install TestGuy dependencies.

```
php testguy.phar install
```

Generate empty test suite

````
php testguy.phar init
````

That will create a 'tests' directory with a sample suite inside it.
By default suite will be configured to test web sites with Mink.
Configuration is stored in ```tests/testguy/suites.yml````.


Build TestGuy class

````
php testguy.phar build
````

Then your suite is ready to run first test file.

````
php testguy.phar run
````

You will see the result:

````
Starting app...
TestGuy 0.7 running with modules: Cli, Filesystem.
Powered by PHPUnit 3.5.5 by Sebastian Bergmann.


# Trying to test some feature of my app (SampleSpec.php) - ok

Time: 0 seconds, Memory: 8.75Mb

OK (1 test, 0 assertions)
````

## Writing tests

Each test belongs to suite. You can have several suites for different parts of your application.
By default the 'app' test suite is crated and stored into ````tests/testguy/app````.
Inside of it you will see a first test file: ````SampleSpec.php````

TestGuy tests should be placed in suite directory and should be ended with 'Spec.php'.

Tests should always start with this lines:

``` php
<?php
$I = new TestGuy($scenario);
$I->wantTo('actions you are going to perform');
```

$I - is a magical object. It stores all actions you can perform. Just type ```$I->``` in your IDE and you will see what actions you can execute.
For instance, the Web module is connected and you can open browser on specific page and test the expected result.

``` php
<?php
$I = new TestGuy($scenario);
$I->wantTo('see if registration page is here');
$I->amOnPage('/register');
$I->see('Registration');
```

ALl methods of $I object are taken from TestGuy modules. There are not much of them, but you can write your own.
The most powerful module is Web module, it allows you to test wep sites with headless browser.
You can connect as many modules as you like and use all them together.

The detailed information on modules and configurations you can see [https://github.com/DavertMik/TestGuy_Modules](here)

## Testing Methods
The method names in $I object are designed to be easy to understand their meaning.
There are 3 types of methods:

* Conditions: start with ```am```. They specify the starting conditions. For example: ```$I->amOnPage('/login');```
* Assertions: start with ```see``` or ```dontSee```. They define the expected result and makes a test fail if result is not see.
* Actions: all other actions. They change current application state. For example: ```$I->click('Signin');``` moves user to sign in page.

## Sample tests
You can look at sample tests here, in /tests/ dir.

### License
MIT

(c) Michael Bodnarchuk "Davert"
2011