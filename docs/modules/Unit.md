# Unit Module

Unit testing module

This is the heart of CodeGuy testing framework.
By providing unique set of features Unit module makes your tests cleaner, readable, and easier to write.

## Features
* Descriptive - simply write what do you test and how do you test.
* Method execution limit - you are allowed only to execute tested method inside the scenario. Don't test several methods inside one unit.
* Simple stub definition - create stubbed class with one call. All properties and methods can be passed as callable functions.
* Dynamic mocking - stubs can be automatically turned to mocks.


## Actions


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


### dontSeeResultContains

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


### executeTestedMethodOn


Alias for executeTestedMethod, only for non-static methods

 * alias executeTestedMethod
 * param $object


### executeTestedMethodWith

__not documented__


### haveFakeClass


Adds a stub to internal registry.
Use this command if you need to convert this stub to mock.
Without adding stub to registry you can't trace it's method invocations.

 * param $instance


### haveStub


Alias for haveFakeClass

 * alias haveFakeClass
 * param $instance


### seeEmptyResult

__not documented__


### seeExceptionThrown

__not documented__


### seeMethodInvoked




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


### seePropertyEquals

__not documented__


### seePropertyIs

__not documented__


### seeResultContains

__not documented__


### seeResultEquals


Asserts that the last result from tested method is equal to value

 * param $value


### seeResultIs

__not documented__


### seeResultNotEquals

__not documented__


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
