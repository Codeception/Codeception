# Asserts Module

**For additional reference, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/Asserts.php)**
## Codeception\Module\Asserts

* *Extends* `Codeception\Module`
* *Uses* `Codeception\Util\Shared\Asserts`

Special module for using asserts in your tests.

Class Asserts
@package Codeception\Module
#### *public static* includeInheritedActionsBy setting it to false module wan't inherit methods of parent class.

 * `var`  bool
#### *public static* onlyActionsAllows to explicitly set what methods have this class.

 * `var`  array
#### *public static* excludeActionsAllows to explicitly exclude actions from module.

 * `var`  array
#### *public static* aliasesAllows to rename actions

 * `var`  array










































### fail
#### *public* fail($message) Fails the test with message.

 * `param`  $message
### seeEquals
#### *public* seeEquals($expected, $actual, $message = null) Checks that two variables are equal.

 * `param`         $expected
 * `param`         $actual
 * `param`  string $message

 * `return`  mixed
### dontSeeEquals
#### *public* dontSeeEquals($expected, $actual, $message = null) Checks that two variables are not equal

 * `param`         $expected
 * `param`         $actual
 * `param`  string $message
### seeGreaterThen
#### *public* seeGreaterThen($expected, $actual, $message = null) Checks that expected is greater then actual

 * `param`         $expected
 * `param`         $actual
 * `param`  string $message
### seeGreaterThenOrEqual
#### *public* seeGreaterThenOrEqual($expected, $actual, $message = null) Checks that expected is greater or equal then actual

 * `param`         $expected
 * `param`         $actual
 * `param`  string $message
### seeContains
#### *public* seeContains($needle, $haystack, $message = null) Checks that haystack contains needle

 * `param`         $needle
 * `param`         $haystack
 * `param`  string $message
### dontSeeContains
#### *public* dontSeeContains($needle, $haystack, $message = null) Checks that haystack doesn't contain needle.

 * `param`         $needle
 * `param`         $haystack
 * `param`  string $message
### seeEmpty
#### *public* seeEmpty($actual, $message = null) Checks that variable is empty.

 * `param`         $actual
 * `param`  string $message
### dontSeeEmpty
#### *public* dontSeeEmpty($actual, $message = null) Checks that variable is not empty.

 * `param`         $actual
 * `param`  string $message
### seeNull
#### *public* seeNull($actual, $message = null) Checks that variable is NULL

 * `param`         $actual
 * `param`  string $message
### dontSeeNull
#### *public* dontSeeNull($actual, $message = null) Checks that variable is not NULL

 * `param`         $actual
 * `param`  string $message
### seeTrue
#### *public* seeTrue($condition, $message = null) Checks that condition is positive.

 * `param`         $condition
 * `param`  string $message
### seeFalse
#### *public* seeFalse($condition, $message = null) Checks that condition is negative.

 * `param`         $condition
 * `param`  string $message
