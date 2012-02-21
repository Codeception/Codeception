# Unit Module

Unit testing module

This is the heart of CodeGuy testing framework.
By providing unique set of features Unit module makes your tests cleaner, readable, and easier to write.

## Features
* Descriptive - simply write what do you test and how do you test.
* Execution limit - only execute* methods actually execute your code. It's easy to see where tested methods are invoked.
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


Checks the result of last execution doesn't contain a value passed.

 * param $value


### dontSeeResultEquals


Checks result of last execution not equal to variable passed.

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

You can use native PHPUnit asserts in executed code. This can be either static methods of PHPUnit_Framework_assert class,
or functions taken from 'PHPUnit/Framework/Assert/Functions.php'. They start with 'assert_' prefix.
You should manually include this file, as this functions may conflict with functions in your code.

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


Executes method of an object.
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


Executes the method which is tested.
If method is not static, the class instance should be provided.

If a method is static 'executeTestedWith' will be called.
If a method is not static 'executeTestedOn' will be called.
See those methods for the full reference

 * throws \InvalidArgumentException


### executeTestedMethodOn


Execute tested method on an object (stub can be passed).
First argument is an object, rest are supposed to be parameters passed to method.

Example:

``` php
<?php
$I->wantTo('authenticate user');
$I->testMethod('User.authenticate');
$user = new User();
$I->executeTestedMethodOn($user, 'Davert','qwerty');
// By this line $user->authenticate('Davert',''qwerty') was called.
$I->seeResultEquals(true);
?>
```

For static methods use 'executeTestedMethodWith'.

 * param $object


### executeTestedMethodWith


Executes tested static method with parameters provided.

```
<?php
$I->testMethod('User::validateName');
$I->executeTestedMethodWith('davert',true);
// User::validate('davert', true); was called
?>
```
For non-static method use 'executeTestedMethodOn'

 * param $params
 * throws \Codeception\Exception\Module


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


Checks the result of last execution is empty.


### seeExceptionThrown

__not documented__


### seeMethodInvoked


Checks the method of stub was invoked after the last execution.
Requires a stub as a first parameter, a method name as second.
Optionally pass an arguments which are expected for executed method.

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

This method dynamically creates mock from stub.

 * magic
 * see createMocks
 * param $mock
 * param $method
 * param array $params


### seeMethodInvokedMultipleTimes


Checks the method of stub was invoked *only once* after the last execution.
Requires a stub as a first parameter, a method name as second and expected executions number.
Optionally pass an arguments which are expected for executed method.

Look for 'seeMethodInvoked' to see the example.

 * magic
 * see createMocks
 * param $mock
 * param $method
 * param $times
 * param array $params


### seeMethodInvokedOnce


Checks the method of stub was invoked *only once* after the last execution.
Requires a stub as a first parameter, a method name as second.
Optionally pass an arguments which are expected for executed method.

Look for 'seeMethodInvoked' to see the example.

 * magic
 * see createMocks
 * param $mock
 * param $method
 * param array $params


### seeMethodNotInvoked


Checks the method of stub *was not invoked* after the last execution.
Requires a stub as a first parameter, a method name as second.
Optionally pass an arguments which are expected for executed method.

 * magic
 * see createMocks
 * param $mock
 * param $method
 * param array $params


### seeMethodNotReturns


Executes method and checks result is equal to passed value.
Good for testing values taken from getters.

Look for 'seeMethodReturns' for example.

 * param $object
 * param $method
 * param $value
 * param array $params


### seeMethodReturns


Executes method and checks result is equal to passed value.
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


Checks property of object equals to value provided.
Can check even protected or private properties.

Consider testing hidden properties as a bad practice.
Use it if you have no other ways to test.

 * param $object
 * param $property
 * param $value


### seePropertyIs


Checks property is a passed type.
Either 'int', 'bool', 'string', 'array', 'float', 'null', 'resource', 'scalar' can be passed for simple types.
Otherwise property will be checked to be an instance of type.

Consider testing hidden properties as a bad practice.
Use it if you have no other ways to test.

 * param $object
 * param $property
 * param $type


### seeResultContains

__not documented__


### seeResultEquals


Asserts that the last result from tested method is equal to value

 * param $value


### seeResultIs


Checks result of last execution is of specific type.
Either 'int', 'bool', 'string', 'array', 'float', 'null', 'resource', 'scalar' can be passed for simple types.
Otherwise property will be checked to be an instance of type.

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
