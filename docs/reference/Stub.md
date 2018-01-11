
## Codeception\Stub



#### *public static* make($class, $params = null, $testCase = null) 
Instantiates a class without executing a constructor.
Properties and methods can be set as a second parameter.
Even protected and private properties can be set.

``` php
<?php
Stub::make('User');
Stub::make('User', ['name' => 'davert']);
?>
```

Accepts either name of class or object of that class

``` php
<?php
Stub::make(new User, ['name' => 'davert']);
?>
```

To replace method provide it's name as a key in second parameter
and it's return value or callback function as parameter

``` php
<?php
Stub::make('User', ['save' => function () { return true; }]);
Stub::make('User', ['save' => true]);
?>
```

**To create a mock, pass current testcase name as last argument:**

```php
<?php
Stub::make('User', [
     'save' => \Codeception\Stub\Expected::once()
], $this);
```

 * `param mixed` $class - A class to be mocked
 * `param array` $params - properties and methods to set
 * `param bool|\PHPUnit\Framework\TestCase` $testCase

 * return object - mock
 * throws \RuntimeException when class does not exist
 * throws \Exception

#### *public static* factory($class, $num = null, $params = null) 
Creates $num instances of class through `Stub::make`.

 * `param mixed` $class
 * `param int` $num
 * `param array` $params

 * return array
 * throws \Exception

#### *public static* makeEmptyExcept($class, $method, $params = null, $testCase = null) 
Instantiates class having all methods replaced with dummies except one.
Constructor is not triggered.
Properties and methods can be replaced.
Even protected and private properties can be set.

``` php
<?php
Stub::makeEmptyExcept('User', 'save');
Stub::makeEmptyExcept('User', 'save', ['name' => 'davert']);
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
Stub::makeEmptyExcept('User', 'save', ['isValid' => function () { return true; }]);
Stub::makeEmptyExcept('User', 'save', ['isValid' => true]);
?>
```

**To create a mock, pass current testcase name as last argument:**

```php
<?php
Stub::makeEmptyExcept('User', 'validate', [
     'save' => \Codeception\Stub\Expected::once()
], $this);
```

 * `param mixed` $class
 * `param string` $method
 * `param array` $params
 * `param bool|\PHPUnit\Framework\TestCase` $testCase

 * return object
 * throws \Exception

#### *public static* makeEmpty($class, $params = null, $testCase = null) 
Instantiates class having all methods replaced with dummies.
Constructor is not triggered.
Properties and methods can be set as a second parameter.
Even protected and private properties can be set.

``` php
<?php
Stub::makeEmpty('User');
Stub::makeEmpty('User', ['name' => 'davert']);
```

Accepts either name of class or object of that class

``` php
<?php
Stub::makeEmpty(new User, ['name' => 'davert']);
```

To replace method provide it's name as a key in second parameter
and it's return value or callback function as parameter

``` php
<?php
Stub::makeEmpty('User', ['save' => function () { return true; }]);
Stub::makeEmpty('User', ['save' => true));
```

**To create a mock, pass current testcase name as last argument:**

```php
<?php
Stub::makeEmpty('User', [
     'save' => \Codeception\Stub\Expected::once()
], $this);
```

 * `param mixed` $class
 * `param array` $params
 * `param bool|\PHPUnit\Framework\TestCase` $testCase

 * return object
 * throws \Exception

#### *public static* copy($obj, $params = null) 
Clones an object and redefines it's properties (even protected and private)

 * `param`       $obj
 * `param array` $params

 * return mixed
 * throws \Exception

#### *public static* construct($class, $constructorParams = null, $params = null, $testCase = null) 
Instantiates a class instance by running constructor.
Parameters for constructor passed as second argument
Properties and methods can be set in third argument.
Even protected and private properties can be set.

``` php
<?php
Stub::construct('User', ['autosave' => false]);
Stub::construct('User', ['autosave' => false], ['name' => 'davert']);
?>
```

Accepts either name of class or object of that class

``` php
<?php
Stub::construct(new User, ['autosave' => false), ['name' => 'davert']);
?>
```

To replace method provide it's name as a key in third parameter
and it's return value or callback function as parameter

``` php
<?php
Stub::construct('User', [], ['save' => function () { return true; }]);
Stub::construct('User', [], ['save' => true]);
?>
```

**To create a mock, pass current testcase name as last argument:**

```php
<?php
Stub::construct('User', [], [
     'save' => \Codeception\Stub\Expected::once()
], $this);
```

 * `param mixed` $class
 * `param array` $constructorParams
 * `param array` $params
 * `param bool|\PHPUnit\Framework\TestCase` $testCase

 * return object
 * throws \Exception

#### *public static* constructEmpty($class, $constructorParams = null, $params = null, $testCase = null) 
Instantiates a class instance by running constructor with all methods replaced with dummies.
Parameters for constructor passed as second argument
Properties and methods can be set in third argument.
Even protected and private properties can be set.

``` php
<?php
Stub::constructEmpty('User', ['autosave' => false]);
Stub::constructEmpty('User', ['autosave' => false), ['name' => 'davert']);
```

Accepts either name of class or object of that class

``` php
<?php
Stub::constructEmpty(new User, ['autosave' => false], ['name' => 'davert']);
```

To replace method provide it's name as a key in third parameter
and it's return value or callback function as parameter

``` php
<?php
Stub::constructEmpty('User', [], ['save' => function () { return true; }]);
Stub::constructEmpty('User', [], ['save' => true]);
```

**To create a mock, pass current testcase name as last argument:**

```php
<?php
Stub::constructEmpty('User', [], [
     'save' => \Codeception\Stub\Expected::once()
], $this);
```

 * `param mixed` $class
 * `param array` $constructorParams
 * `param array` $params
 * `param bool|\PHPUnit\Framework\TestCase` $testCase

 * return object

#### *public static* constructEmptyExcept($class, $method, $constructorParams = null, $params = null, $testCase = null) 
Instantiates a class instance by running constructor with all methods replaced with dummies, except one.
Parameters for constructor passed as second argument
Properties and methods can be set in third argument.
Even protected and private properties can be set.

``` php
<?php
Stub::constructEmptyExcept('User', 'save');
Stub::constructEmptyExcept('User', 'save', ['autosave' => false], ['name' => 'davert']);
?>
```

Accepts either name of class or object of that class

``` php
<?php
Stub::constructEmptyExcept(new User, 'save', ['autosave' => false], ['name' => 'davert']);
?>
```

To replace method provide it's name as a key in third parameter
and it's return value or callback function as parameter

``` php
<?php
Stub::constructEmptyExcept('User', 'save', [], ['save' => function () { return true; }]);
Stub::constructEmptyExcept('User', 'save', [], ['save' => true]);
?>
```

**To create a mock, pass current testcase name as last argument:**

```php
<?php
Stub::constructEmptyExcept('User', 'save', [], [
     'save' => \Codeception\Stub\Expected::once()
], $this);
```

 * `param mixed` $class
 * `param string` $method
 * `param array` $constructorParams
 * `param array` $params
 * `param bool|\PHPUnit\Framework\TestCase` $testCase

 * return object

#### *public static* update($mock, array $params) 
Replaces properties of current stub

 * `param \PHPUnit\Framework\MockObject\MockObject` $mock
 * `param array` $params

 * return mixed
 * throws \LogicException

#### *public static* consecutive() 
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

 * return ConsecutiveMap


