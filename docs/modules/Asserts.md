# Asserts


Special module for using asserts in your tests.

## Actions

### assertArrayHasKey
 
 * `param` $key
 * `param` $actual
 * `param` $description


### assertArrayNotHasKey
 
 * `param` $key
 * `param` $actual
 * `param` $description


### assertContains
 
Checks that haystack contains needle

 * `param`        $needle
 * `param`        $haystack
 * `param string` $message


### assertCount
 
 * `param` $expectedCount
 * `param` $actual
 * `param` $description


### assertEmpty
 
Checks that variable is empty.

 * `param`        $actual
 * `param string` $message


### assertEquals
 
Checks that two variables are equal.

 * `param`        $expected
 * `param`        $actual
 * `param string` $message
 * `param float`  $delta


### assertEqualsCanonicalizing
__not documented__


### assertEqualsIgnoringCase
__not documented__


### assertEqualsWithDelta
__not documented__


### assertFalse
 
Checks that condition is negative.

 * `param`        $condition
 * `param string` $message


### assertFileExists
 
Checks if file exists

 * `param string` $filename
 * `param string` $message


### assertFileNotExists
 
Checks if file doesn't exist

 * `param string` $filename
 * `param string` $message


### assertGreaterOrEquals
 
 * `param` $expected
 * `param` $actual
 * `param` $description


### assertGreaterThan
 
Checks that actual is greater than expected

 * `param`        $expected
 * `param`        $actual
 * `param string` $message


### assertGreaterThanOrEqual
 
Checks that actual is greater or equal than expected

 * `param`        $expected
 * `param`        $actual
 * `param string` $message


### assertInstanceOf
 
 * `param` $class
 * `param` $actual
 * `param` $description


### assertInternalType
 
 * `param` $type
 * `param` $actual
 * `param` $description


### assertIsArray
__not documented__


### assertIsBool
__not documented__


### assertIsCallable
__not documented__


### assertIsEmpty
 
 * `param` $actual
 * `param` $description


### assertIsFloat
__not documented__


### assertIsInt
__not documented__


### assertIsNotArray
__not documented__


### assertIsNotBool
__not documented__


### assertIsNotCallable
__not documented__


### assertIsNotFloat
__not documented__


### assertIsNotInt
__not documented__


### assertIsNotNumeric
__not documented__


### assertIsNotObject
__not documented__


### assertIsNotResource
__not documented__


### assertIsNotScalar
__not documented__


### assertIsNotString
__not documented__


### assertIsNumeric
__not documented__


### assertIsObject
__not documented__


### assertIsResource
__not documented__


### assertIsScalar
__not documented__


### assertIsString
__not documented__


### assertLessOrEquals
 
 * `param` $expected
 * `param` $actual
 * `param` $description


### assertLessThan
 
Checks that actual is less than expected

 * `param`        $expected
 * `param`        $actual
 * `param string` $message


### assertLessThanOrEqual
 
Checks that actual is less or equal than expected

 * `param`        $expected
 * `param`        $actual
 * `param string` $message


### assertNotContains
 
Checks that haystack doesn't contain needle.

 * `param`        $needle
 * `param`        $haystack
 * `param string` $message


### assertNotEmpty
 
Checks that variable is not empty.

 * `param`        $actual
 * `param string` $message


### assertNotEquals
 
Checks that two variables are not equal

 * `param`        $expected
 * `param`        $actual
 * `param string` $message
 * `param float`  $delta


### assertNotEqualsCanonicalizing
__not documented__


### assertNotEqualsIgnoringCase
__not documented__


### assertNotEqualsWithDelta
__not documented__


### assertNotFalse
 
Checks that the condition is NOT false (everything but false)

 * `param`        $condition
 * `param string` $message


### assertNotInstanceOf
 
 * `param` $class
 * `param` $actual
 * `param` $description


### assertNotNull
 
Checks that variable is not NULL

 * `param`        $actual
 * `param string` $message


### assertNotRegExp
 
Checks that string not match with pattern

 * `param string` $pattern
 * `param string` $string
 * `param string` $message


### assertNotSame
 
Checks that two variables are not same

 * `param`        $expected
 * `param`        $actual
 * `param string` $message


### assertNotTrue
 
Checks that the condition is NOT true (everything but true)

 * `param`        $condition
 * `param string` $message


### assertNull
 
Checks that variable is NULL

 * `param`        $actual
 * `param string` $message


### assertRegExp
 
Checks that string match with pattern

 * `param string` $pattern
 * `param string` $string
 * `param string` $message


### assertSame
 
Checks that two variables are same

 * `param`        $expected
 * `param`        $actual
 * `param string` $message


### assertStringContainsString
__not documented__


### assertStringContainsStringIgnoringCase
__not documented__


### assertStringNotContainsString
__not documented__


### assertStringNotContainsStringIgnoringCase
__not documented__


### assertStringStartsNotWith
 
Checks that a string doesn't start with the given prefix.

 * `param string` $prefix
 * `param string` $string
 * `param string` $message


### assertStringStartsWith
 
Checks that a string starts with the given prefix.

 * `param string` $prefix
 * `param string` $string
 * `param string` $message


### assertTrue
 
Checks that condition is positive.

 * `param`        $condition
 * `param string` $message


### expectException
 
Handles and checks exception called inside callback function.
Either exception class name or exception instance should be provided.

```php
<?php
$I->expectException(MyException::class, function() {
    $this->doSomethingBad();
});

$I->expectException(new MyException(), function() {
    $this->doSomethingBad();
});
```
If you want to check message or exception code, you can pass them with exception instance:
```php
<?php
// will check that exception MyException is thrown with "Don't do bad things" message
$I->expectException(new MyException("Don't do bad things"), function() {
    $this->doSomethingBad();
});
```

 * `param` $exception string or \Exception
 * `param` $callback


### expectThrowable
 
Handles and checks throwables (Exceptions/Errors) called inside the callback function.
Either throwable class name or throwable instance should be provided.

```php
<?php
$I->expectThrowable(MyThrowable::class, function() {
    $this->doSomethingBad();
});

$I->expectThrowable(new MyException(), function() {
    $this->doSomethingBad();
});
```
If you want to check message or throwable code, you can pass them with throwable instance:
```php
<?php
// will check that throwable MyError is thrown with "Don't do bad things" message
$I->expectThrowable(new MyError("Don't do bad things"), function() {
    $this->doSomethingBad();
});
```

 * `param` $throwable string or \Throwable
 * `param` $callback


### fail
 
Fails the test with message.

 * `param` $message

<p>&nbsp;</p><div class="alert alert-warning">Module reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/tree/3.0/src/Codeception/Module/Asserts.php">Help us to improve documentation. Edit module reference</a></div>
