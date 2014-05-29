# Asserts Module

**For additional reference, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/Asserts.php)**


Special module for using asserts in your tests.

Class Asserts
@package Codeception\Module















































### fail
 Fails the test with message.

 * `param`  $message

### seeEquals
 Checks that two variables are equal.

 * `param`         $expected
 * `param`         $actual
 * `param`  string $message

 * `return`  mixed

### dontSeeEquals
 Checks that two variables are not equal

 * `param`         $expected
 * `param`         $actual
 * `param`  string $message

### seeGreaterThen
 Checks that expected is greater then actual

 * `param`         $expected
 * `param`         $actual
 * `param`  string $message

### seeGreaterThenOrEqual
 Checks that expected is greater or equal then actual

 * `param`         $expected
 * `param`         $actual
 * `param`  string $message

### seeContains
 Checks that haystack contains needle

 * `param`         $needle
 * `param`         $haystack
 * `param`  string $message

### dontSeeContains
 Checks that haystack doesn't contain needle.

 * `param`         $needle
 * `param`         $haystack
 * `param`  string $message

### seeEmpty
 Checks that variable is empty.

 * `param`         $actual
 * `param`  string $message

### dontSeeEmpty
 Checks that variable is not empty.

 * `param`         $actual
 * `param`  string $message

### seeNull
 Checks that variable is NULL

 * `param`         $actual
 * `param`  string $message

### dontSeeNull
 Checks that variable is not NULL

 * `param`         $actual
 * `param`  string $message

### seeTrue
 Checks that condition is positive.

 * `param`         $condition
 * `param`  string $message

### seeFalse
 Checks that condition is negative.

 * `param`         $condition
 * `param`  string $message
