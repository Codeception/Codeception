# Unit

Unit testing module

This is the heart of CodeGuy testing framework.
By providing unique set of features Unit module makes your tests cleaner, readable, and easier to write.

Here is example of unit test, which tests one of the methods of Codeception core.

``` php
<?php
class ScenarioCest
{
    public $class = '\Codeception\Scenario';

    public function run(CodeGuy $I) {
        $I->wantTo('run steps from scenario');

        // creating an empty stub:
        $I->haveFakeClass($test = Stub::makeEmpty('\Codeception\TestCase\Cept'));

        // creating stub with defined properties
        $I->haveFakeClass($scenario = Stub::make('\Codeception\Scenario', array(
            'test' => $test,
            'steps' => array(
               Stub::makeEmpty('\Codeception\Step\Action'),
               Stub::makeEmpty('\Codeception\Step\Comment')
           )
       )));

       // run the method we test on object specified
       $I->executeTestedMethodOn($scenario)

       // perform assertions
           ->seeMethodInvoked($test,'runStep')
           ->seePropertyEquals($scenario, 'currentStep', 1);
   }

```

## Features
* Descriptive - simply write what do you test and how do you test.
* Method execution limit - you are allowed only to execute tested method inside the scenario. Don't test several methods inside one unit.
* Simple stub definition - create stubbed class with one call. All properties and methods can be passed as callable functions.
* Dynamic mocking - stubs can be automatically turned to mocks.

## Unit Testing With Scenarios

CodeGuy allows you to define testing scenarios in a natural way.
Typical test should consist of 3 steps, in this direct order:

* environment definition
* method execution
* assertions

That's the natural and logical way: we do something, and after that we check for result.

In many cases in unit tests, you can't write your test in this order.
For example, if you use mocks, you should define the expected results before the tested method is executed.
Thus, the structure of test becomes harder for reading and understanding. Assertions can be put anywhere in test.

Let's say we want to test method run for class Unit

``` php
<?php

class Unit {

     public function run()
     {
        $this->runExtra();
     }

     protected function runExtra()
     {
     }

}

```

For PHPUnit our test will look like:

``` php
<?php

$unit = $this->getMock('Unit', array('runExtra'));
$observer->expects($this->once())
  ->method('runExtra');

$unit->run();
```

For assertion we use ->expects($this->once()) construction.
So, we have assertions that are assertions, and assertions that are expectations...
What a mess!

Here, how the CodeGuy scenario looks like:

``` php
<?php

$I = new CodeGuy($scenario);
$I->testMethod('Unit.run');
$I->haveStubClass($unit = Stub::make('Unit'));
$I->executeTestedMethodOn($unit);
$I->seeMethodInvoked($unit, 'runExtra');

```
So, that becomes much clearer.
We define stub (as environment) before the method execution.
We execute method.
We check the internal method was invoked.

Also, we describe all our actions in proper and logical order.
Even junior developer can understand how and what you test here.

But how is that possible?
How can we execute $unit->run() and see it triggered $unit->runExtra() afterwards?

## Simple Magic

The magic inside of this module is behind the conecept of Codecept scenarios.
You describe your test in PHP DSL, then it's logged into scenario, and after that it's run step by step.
That's important. The test you are writing won't be executed as it is, in realtime.

Unit module gets a benefit from it: it's methods can review next steps in scenarios and prepare mocks and exception handling.
Thus, the trick is: command $I->executeTestedMethod() doesn't actually execute it :)


## Actions


### testMethod


Registers a class/method which will be tested.
When you run 'execute' this method will be invoked.
Please, not that it also update the feature section of scenario.

For non-static methods:

``` php
<?php
$I->testMethod('ClassName.MethodName'); // I will need ClassName instance for this
```

For static methods:

``` php
<?php
$I->testMethod('ClassName::MethodName');
```

 * param $signature

### haveFakeClass


Adds a stub to internal registry.
Use this command if you need to convert this stub to mock.
Without adding stub to registry you can't trace it's method invocations.

 * param $instance

### haveStub


Alias for haveFakeClass

 * alias haveFakeClass
 * param $instance

### executeTestedMethodOn


Alias for executeTestedMethod, only for non-static methods

 * alias executeTestedMethod
 * param $object

### executeTestedMethodWith

__not documented__

### executeTestedMethod


Executes the method which is tested.
If method is not static, the class instance should be provided.
Otherwise bypass the first parameter blank

Include additional arguments as parameter.

Examples:

For non-static methods:

``` php
<?php
$I->executeTestedMethod($object, 1, 'hello', array(5,4,5));
```

The same for static method

``` php
<?php
$I->executeTestedMethod(1, 'hello', array(5,4,5));
```

 * param $object null
 * throws \InvalidArgumentException

### changeProperties


Updates selected properties for object passed.
Can update even private and protected properties.

 * param $obj
 * param array $values

### changeProperty


Updates property of selected object
Can update even private and protected properties.

 * param $obj
 * param $property
 * param $value

### seeExceptionThrown

__not documented__

### seeMethodInvoked




 * magic
 * see createMocks
 * param $mock
 * param $method
 * param array $params

### seeMethodInvokedOnce



 * magic
 * see createMocks
 * param $mock
 * param $method
 * param array $params

### seeMethodNotInvoked



 * magic
 * see createMocks
 * param $mock
 * param $method
 * param array $params

### seeMethodInvokedMultipleTimes



 * magic
 * see createMocks
 * param $mock
 * param $method
 * param $times
 * param array $params

### seeResultEquals


Asserts that the last result from tested method is equal to value

 * param $value

### seeResultContains

__not documented__

### dontSeeResultContains

__not documented__

### seeResultNotEquals

__not documented__

### seeEmptyResult

__not documented__

### seeResultIs

__not documented__

### seePropertyEquals

__not documented__

### seePropertyIs

__not documented__
