# Unit

Unit testing module




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


Adds stub in internal registry.
Use this command if you need to convert this stub to mock.
Without adding stub to registry you can't trace it's method invocations.

 * param $instance

### haveStub


Alias for haveFakeClass

 * alias haveFakeClass
 * param $instance

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

__not documented__

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
