
## Codeception\Util\Stub




#### *public static* magicMethods### atLeastOnce 

*static*

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

 * `return`  StubMarshaler

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L695)

### consecutive 

*static*

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

 * `return`  ConsecutiveMap

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L764)

### construct 

*static*

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

 * `return`  object

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L286)

### constructEmpty 

*static*

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

 * `return`  object

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L338)

### constructEmptyExcept 

*static*

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

 * `return`  object

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L395)

### copy 

*static*

Clones an object and redefines it's properties (even protected and private)

 * `param`       $obj
 * `param array` $params

 * `return`  mixed

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L240)

### exactly 

*static*

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

 * `return`  StubMarshaler

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L731)

### factory 

*static*

Creates $num instances of class through `Stub::make`.

 * `param mixed` $class
 * `param int` $num
 * `param array` $params

 * `return`  array

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L94)

### make 

*static*

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

 * `return`  object - mock
 * `throws`  \RuntimeException when class does not exist

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L45)

### makeEmpty 

*static*

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

 * `return`  object

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L214)

### makeEmptyExcept 

*static*

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

 * `return`  object

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L142)

### never 

*static*

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

 * `return`  StubMarshaler

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L630)

### once 

*static*

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

 * `return`  StubMarshaler

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L663)

### update 

*static*

Replaces properties of current stub

 * `param \PHPUnit_Framework_MockObject_MockObject` $mock
 * `param array` $params

 * `return`  mixed
 * `throws`  \LogicException

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php#L499)

<p>&nbsp;</p><div class="alert alert-warning">Reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Stub.php">Help us to improve documentation. Edit module reference</a></div>
