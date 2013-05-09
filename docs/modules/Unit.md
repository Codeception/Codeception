# Unit Module
**For additional reference, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/Unit.php)**


Unit testing module

## Please don't use that anymore. Really. It's deprecated in [favor of common unit tests](http://codeception.com/03-18-2013/scenario-unit-deprecated.html).

This is the heart of the CodeGuy testing framework.
By providing a unique set of features Unit, the module makes your tests cleaner, more readable, and easier to write.

## Status

* Maintainer: **davert**
* Stability: **deprecated**
* Contact: codecept@davert.mail.ua

## Features
* Descriptive - simply write what you are testing and how you are testing.
* Execution limit - only 'execute* methods actually execute your code. It's easy to see where tested methods are invoked.
* Simple stub definition - create stubbed class with one call. All properties and methods can be passed as callable functions.
* Dynamic mocking - stubs can be automatically turned into mocks.


## Actions


### changeProperties


Updates multiple properties of the selected object.
Can update even private and protected properties.

Properties to be updated and their values are passed in the second parameter as an array:
array('theProperty'     => 'some value',
     ('anotherProperty' => 'another value')

 * param $obj
 * param array $values


### changeProperty


Updates a single property of the selected object
Can update even private and protected properties.

 * param $obj
 * param $property
 * param $value


### dontSeeResultContains


Checks that the result of the last execution doesn't contain a value.

 * param $value


### dontSeeResultEquals


Checks that the result of the last execution is not equal to a value.

 * param $value


### execute


Executes a code block. The result of execution will be stored.
Parameter should be a valid Closure. The returned value can be checked with seeResult actions.

Example:

``` php
<?php
$user = new User();
$I->execute(function() use ($user) {
     $user->setName('Davert');
     return $user->getName();
});
$I->seeResultEquals('Davert');
?>
```

You can use native PHPUnit asserts in the executed code. 
These can be either static methods of the 'PHPUnit_Framework_assert' class,
or functions taken from 'PHPUnit/Framework/Assert/Functions.php'. They start with 'assert_' prefix.
You should manually include this file, as these functions may conflict with functions in your code.

Example:

``` php
<?php
require_once 'PHPUnit/Framework/Assert/Functions.php';

$user = new User();
$I->execute(function() use ($user) {
     $user->setName('Davert');
     assertEquals('Davert', $user->getName());
});
```

 * param \Closure $code


### executeMethod


Executes a method of an object.
Additional parameters can be provided.

Example:

``` php
<?php
// to execute $user->getName()
$I->executeMethod($user,'getName');

// to execute $user->setName('davert');
$I->executeMethod($user,'setName', 'davert');

// or more parameters
$I->executeMethod($user, 'setNameAndAge', 'davert', '30');

?>
```

 * param $object
 * param $method


### executeTestedMethod


Executes the method which is being tested.
If the method is not static, the class instance should be provided.

If a method is static 'executeTestedWith' will be called.
If a method is not static 'executeTestedOn' will be called.
See those methods for the full reference

 * throws \InvalidArgumentException


### executeTestedMethodOn


Execute The tested method on an object (a stub can be passed).
First argument is an object, the rest are supposed to be parameters passed to method.

Example:

``` php
<?php
$I->wantTo('authenticate user');
$I->testMethod('User.authenticate');
$user = new User();
$I->executeTestedMethodOn($user, 'Davert','qwerty');
// This line $user->authenticate('Davert','qwerty') was called.
$I->seeResultEquals(true);
?>
```

For static methods use 'executeTestedMethodWith'.

 * param $object


### executeTestedMethodWith


Executes the tested static method with parameters provided.

```
<?php
$I->testMethod('User::validateName');
$I->executeTestedMethodWith('davert',true);
// This line User::validate('davert', true); was called
?>
```
For a non-static method use 'executeTestedMethodOn'

 * param $params
 * throws \Codeception\Exception\Module


### haveFakeClass


Adds a stub to the internal registry.
Use this command if you need to convert this stub to a mock.
Without adding the stub to registry you can't trace it's method invocations.

 * param $instance


### haveStub


Alias for haveFakeClass

 * alias haveFakeClass
 * param $instance


### seeEmptyResult


Checks that the result of the last execution is empty.


### seeExceptionThrown

__not documented__


### seeMethodInvoked


Checks that a method of a stub was invoked after the last execution.
Requires a stub as the first parameter, the method name as the second.
Optionally pass the arguments which are expected by the executed method.

Example:

``` php
<?php
$I->testMethod('UserService.create');
$I->haveStub($user = Stub::make('Model\User'));*
$service = new UserService($user);
$I->executeTestedMethodOn($service);
// we expect $user->save was invoked.
$I->seeMethodInvoked($user, 'save');
?>
```

This method dynamically creates a mock from a stub.

 * magic
 * see createMocks
 * param $mock
 * param $method
 * param array $params


### seeMethodInvokedMultipleTimes


Checks that a method of a stub was invoked *multiple times* after the last execution.
Requires a stub as the first parameter, a method name as the second and the expected number of executions.
Optionally pass the arguments which are expected by the executed method.

Look for 'seeMethodInvoked' to see the example.

 * magic
 * see createMocks
 * param $mock
 * param $method
 * param $times
 * param array $params


### seeMethodInvokedOnce


Checks that a method of a stub was invoked *only once* after the last execution.
Requires a stub as the first parameter, a method name as the second.
Optionally pass the arguments which are expected by the executed method.

Look for 'seeMethodInvoked' to see the example.

 * magic
 * see createMocks
 * param $mock
 * param $method
 * param array $params


### seeMethodNotInvoked


Checks that a method of a stub *was not invoked* after the last execution.
Requires a stub as the first parameter, a method name as the second.
Optionally pass the arguments which are expected by the executed method.

 * magic
 * see createMocks
 * param $mock
 * param $method
 * param array $params


### seeMethodNotReturns


Executes a method and checks that the result is NOT equal to a value.
Good for testing values taken from getters.

Look for 'seeMethodReturns' for example.

 * param $object
 * param $method
 * param $value
 * param array $params


### seeMethodReturns


Executes a method and checks that the result is equal to a value.
Good for testing values taken from getters.

Example:

``` php
$I->testMethod('User.setName');
$user = new User();
$I->executeTestedMethodOn($user, 'davert');
$I->seeMethodReturns($user,'getName','davert');

```
    *
 * param $object
 * param $method
 * param $value
 * param array $params


### seePropertyEquals


Checks that the property of an object equals the value provided.
Can check even protected or private properties.

Bear in mind that testing non-public properties is not a good practice.
Use it only if you have no other way to test it.

 * param $object
 * param $property
 * param $value


### seePropertyIs


Checks that the property is a passed type.
Either 'int', 'bool', 'string', 'array', 'float', 'null', 'resource', 'scalar' can be passed for simple types.
Otherwise the parameter must be a class and the property must be an instance of that class.

Bear in mind that testing non-public properties is not a good practice.
Use it only if you have no other way to test it.

 * param $object
 * param $property
 * param $type


### seeResultContains

__not documented__


### seeResultEquals


Asserts that the last result from the tested method is equal to value

 * param $value


### seeResultIs


Checks that the result of the last execution is a specific type.
Either 'int', 'bool', 'string', 'array', 'float', 'null', 'resource', 'scalar' can be passed for simple types.
Otherwise the parameter must be a class and the result must be an instance of that class.

Example:

``` php
<?php
$I->execute(function() { return new User });
$I->seeResultIs('User');
?>
```

 * param $type


### testMethod


Registers a class/method which will be tested.
When you run 'execute' this method will be invoked.
Please, note that it also updates the feature section of the scenario.

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
