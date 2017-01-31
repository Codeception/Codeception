
## Codeception\Util\Stub





#### $magicMethods

*public static* **$magicMethods**
#### atLeastOnce()

 *public static* atLeastOnce($params = null) 

Checks if a method has been invoked at least one
time.

If the number of invocations is 0 it will throw an exception in verify.

``` php
<?php
$user = Stub::make(
    'User',
    array(
        'getName' => Stub::atLeastOnce(function() { return 'Davert';}),
        'someMethod' => function() {}
    )
);
$user->getName();
$user->getName();
?>
```

 * `param mixed` $params
 * `return` StubMarshaler

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L696)

#### bindParameters()

 *protected static* bindParameters($mock, $params) 

 * `param \PHPUnit_Framework_MockObject_MockObject` $mock
 * `param array` $params

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L516)

#### closureIfNull()

 *private static* closureIfNull($params) 

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L740)

#### consecutive()

 *public static* consecutive() 

Stubbing a method call to return a list of values in the specified order.

``` php
<?php
$user = Stub::make('User', array('getName' => Stub::consecutive('david', 'emma', 'sam', 'amy')));
$user->getName(); //david
$user->getName(); //emma
$user->getName(); //sam
$user->getName(); //amy
?>
```
 * `return` ConsecutiveMap

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L765)

#### construct()

 *public static* construct($class, $constructorParams = null, $params = null, $testCase = null) 

Instantiates a class instance by running constructor.
Parameters for constructor passed as second argument
Properties and methods can be set in third argument.
Even protected and private properties can be set.

``` php
<?php
Stub::construct('User', array('autosave' => false));
Stub::construct('User', array('autosave' => false), array('name' => 'davert'));
?>
```

Accepts either name of class or object of that class

``` php
<?php
Stub::construct(new User, array('autosave' => false), array('name' => 'davert'));
?>
```

To replace method provide it's name as a key in third parameter
and it's return value or callback function as parameter

``` php
<?php
Stub::construct('User', array(), array('save' => function () { return true; }));
Stub::construct('User', array(), array('save' => true }));
?>
```

 * `param mixed` $class
 * `param array` $constructorParams
 * `param array` $params
 * `param bool|\PHPUnit_Framework_TestCase` $testCase
 * `return` object

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L287)

#### constructEmpty()

 *public static* constructEmpty($class, $constructorParams = null, $params = null, $testCase = null) 

Instantiates a class instance by running constructor with all methods replaced with dummies.
Parameters for constructor passed as second argument
Properties and methods can be set in third argument.
Even protected and private properties can be set.

``` php
<?php
Stub::constructEmpty('User', array('autosave' => false));
Stub::constructEmpty('User', array('autosave' => false), array('name' => 'davert'));
?>
```

Accepts either name of class or object of that class

``` php
<?php
Stub::constructEmpty(new User, array('autosave' => false), array('name' => 'davert'));
?>
```

To replace method provide it's name as a key in third parameter
and it's return value or callback function as parameter

``` php
<?php
Stub::constructEmpty('User', array(), array('save' => function () { return true; }));
Stub::constructEmpty('User', array(), array('save' => true }));
?>
```

 * `param mixed` $class
 * `param array` $constructorParams
 * `param array` $params
 * `param bool|\PHPUnit_Framework_TestCase` $testCase
 * `return` object

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L339)

#### constructEmptyExcept()

 *public static* constructEmptyExcept($class, $method, $constructorParams = null, $params = null, $testCase = null) 

Instantiates a class instance by running constructor with all methods replaced with dummies, except one.
Parameters for constructor passed as second argument
Properties and methods can be set in third argument.
Even protected and private properties can be set.

``` php
<?php
Stub::constructEmptyExcept('User', 'save');
Stub::constructEmptyExcept('User', 'save', array('autosave' => false), array('name' => 'davert'));
?>
```

Accepts either name of class or object of that class

``` php
<?php
Stub::constructEmptyExcept(new User, 'save', array('autosave' => false), array('name' => 'davert'));
?>
```

To replace method provide it's name as a key in third parameter
and it's return value or callback function as parameter

``` php
<?php
Stub::constructEmptyExcept('User', 'save', array(), array('save' => function () { return true; }));
Stub::constructEmptyExcept('User', 'save', array(), array('save' => true }));
?>
```

 * `param mixed` $class
 * `param string` $method
 * `param array` $constructorParams
 * `param array` $params
 * `param bool|\PHPUnit_Framework_TestCase` $testCase
 * `return` object

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L396)

#### copy()

 *public static* copy($obj, $params = null) 

Clones an object and redefines it's properties (even protected and private)

 * `param`       $obj
 * `param array` $params
 * `return` mixed

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L241)

#### doGenerateMock()

 *private static* doGenerateMock($args, $isAbstract = null) 

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L459)

#### exactly()

 *public static* exactly($count, $params = null) 

Checks if a method has been invoked a certain amount
of times.
If the number of invocations exceeds the value it will immediately throw an
exception,
If the number is less it will later be checked in verify() and also throw an
exception.

``` php
<?php
$user = Stub::make(
    'User',
    array(
        'getName' => Stub::exactly(3, function() { return 'Davert';}),
        'someMethod' => function() {}
    )
);
$user->getName();
$user->getName();
$user->getName();
?>
```

 * `param int` $count
 * `param mixed` $params
 * `return` StubMarshaler

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L732)

#### extractTestCaseFromArgs()

 *private static* extractTestCaseFromArgs($args) 

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L481)

#### factory()

 *public static* factory($class, $num = null, $params = null) 

Creates $num instances of class through `Stub::make`.

 * `param mixed` $class
 * `param int` $num
 * `param array` $params
 * `return` array

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L95)

#### generateMock()

 *private static* generateMock() 

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L431)

#### generateMockForAbstractClass()

 *private static* generateMockForAbstractClass() 

Returns a mock object for the specified abstract class with all abstract
methods of the class mocked. Concrete methods to mock can be specified with
the last parameter

 * `param`  string $originalClassName
 * `param`  array $arguments
 * `param`  string $mockClassName
 * `param`  boolean $callOriginalConstructor
 * `param`  boolean $callOriginalClone
 * `param`  boolean $callAutoload
 * `param`  array $mockedMethods
 * `param`  boolean $cloneArguments
 * `return` object
 * `since`  Method available since Release 1.0.0
 * `throws` \InvalidArgumentException

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L454)

#### getClassname()

 *protected static* getClassname($object) 
 * `todo` should be simplified

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L584)

#### getMethodsToReplace()

 *protected static* getMethodsToReplace($reflection, $params) 

 * `param \ReflectionClass` $reflection
 * `param` $params
 * `return` array

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L602)

#### make()

 *public static* make($class, $params = null, $testCase = null) 

Instantiates a class without executing a constructor.
Properties and methods can be set as a second parameter.
Even protected and private properties can be set.

``` php
<?php
Stub::make('User');
Stub::make('User', array('name' => 'davert'));
?>
```

Accepts either name of class or object of that class

``` php
<?php
Stub::make(new User, array('name' => 'davert'));
?>
```

To replace method provide it's name as a key in second parameter
and it's return value or callback function as parameter

``` php
<?php
Stub::make('User', array('save' => function () { return true; }));
Stub::make('User', array('save' => true }));
?>
```

 * `param mixed` $class - A class to be mocked
 * `param array` $params - properties and methods to set
 * `param bool|\PHPUnit_Framework_TestCase` $testCase
 * `return` object - mock
 * `throws` \RuntimeException when class does not exist

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L46)

#### makeEmpty()

 *public static* makeEmpty($class, $params = null, $testCase = null) 

Instantiates class having all methods replaced with dummies.
Constructor is not triggered.
Properties and methods can be set as a second parameter.
Even protected and private properties can be set.

``` php
<?php
Stub::makeEmpty('User');
Stub::makeEmpty('User', array('name' => 'davert'));
?>
```

Accepts either name of class or object of that class

``` php
<?php
Stub::makeEmpty(new User, array('name' => 'davert'));
?>
```

To replace method provide it's name as a key in second parameter
and it's return value or callback function as parameter

``` php
<?php
Stub::makeEmpty('User', array('save' => function () { return true; }));
Stub::makeEmpty('User', array('save' => true }));
?>
```

 * `param mixed` $class
 * `param array` $params
 * `param bool|\PHPUnit_Framework_TestCase` $testCase
 * `return` object

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L215)

#### makeEmptyExcept()

 *public static* makeEmptyExcept($class, $method, $params = null, $testCase = null) 

Instantiates class having all methods replaced with dummies except one.
Constructor is not triggered.
Properties and methods can be replaced.
Even protected and private properties can be set.

``` php
<?php
Stub::makeEmptyExcept('User', 'save');
Stub::makeEmptyExcept('User', 'save', array('name' => 'davert'));
?>
```

Accepts either name of class or object of that class

``` php
<?php
* Stub::makeEmptyExcept(new User, 'save');
?>
```

To replace method provide it's name as a key in second parameter
and it's return value or callback function as parameter

``` php
<?php
Stub::makeEmptyExcept('User', 'save', array('isValid' => function () { return true; }));
Stub::makeEmptyExcept('User', 'save', array('isValid' => true }));
?>
```

 * `param mixed` $class
 * `param string` $method
 * `param array` $params
 * `param bool|\PHPUnit_Framework_TestCase` $testCase
 * `return` object

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L143)

#### markAsMock()

 *private static* markAsMock($mock, $reflection) 

Set __mock flag, if at all possible

 * `param object` $mock
 * `param \ReflectionClass` $reflection
 * `return` object

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L78)

#### never()

 *public static* never($params = null) 

Checks if a method never has been invoked

If method invoked, it will immediately throw an
exception.

``` php
<?php
$user = Stub::make('User', array('getName' => Stub::never(), 'someMethod' => function() {}));
$user->someMethod();
?>
```

 * `param mixed` $params
 * `return` StubMarshaler

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L631)

#### once()

 *public static* once($params = null) 

Checks if a method has been invoked exactly one
time.

If the number is less or greater it will later be checked in verify() and also throw an
exception.

``` php
<?php
$user = Stub::make(
    'User',
    array(
        'getName' => Stub::once(function() { return 'Davert';}),
        'someMethod' => function() {}
    )
);
$userName = $user->getName();
$this->assertEquals('Davert', $userName);
?>
```

 * `param mixed` $params
 * `return` StubMarshaler

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L664)

#### update()

 *public static* update($mock, array $params) 

Replaces properties of current stub

 * `param \PHPUnit_Framework_MockObject_MockObject` $mock
 * `param array` $params
 * `return` mixed
 * `throws` \LogicException

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L500)

<p>&nbsp;</p><div class="alert alert-warning">Reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/blob/2.2/src//Codeception/Util/Stub.php">Help us to improve documentation. Edit module reference</a></div>
