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

### assertArraySubset

Checks that array contains subset.

 * `param array`  $subset
 * `param array`  $array
 * `param bool`   $strict
 * `param string` $message

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

Checks that two variables are equal. If you're comparing floating-point values,
you can specify the optional "delta" parameter which dictates how great of a precision
error are you willing to tolerate in order to consider the two values equal.

Regular example:
```php
<?php
$I->assertEquals($element->getChildrenCount(), 5);
```

Floating-point example:
```php
<?php
$I->assertEquals($calculator->add(0.1, 0.2), 0.3, 'Calculator should add the two numbers correctly.', 0.01);
```

 * `param`        $expected
 * `param`        $actual
 * `param string` $message
 * `param float`  $delta

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

### assertIsEmpty

 * `param` $actual
 * `param` $description

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

Checks that two variables are not equal. If you're comparing floating-point values,
you can specify the optional "delta" parameter which dictates how great of a precision
error are you willing to tolerate in order to consider the two values not equal.

Regular example:
```php
<?php
$I->assertNotEquals($element->getChildrenCount(), 0);
```

Floating-point example:
```php
<?php
$I->assertNotEquals($calculator->add(0.1, 0.2), 0.4, 'Calculator should add the two numbers correctly.', 0.01);
```

 * `param`        $expected
 * `param`        $actual
 * `param string` $message
 * `param float`  $delta

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

### fail

Fails the test with message.

 * `param` $message

<p>&nbsp;</p><div class="alert alert-warning">Module reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/tree/2.3/src/Codeception/Module/Asserts.php">Help us to improve documentation. Edit module reference</a></div>
