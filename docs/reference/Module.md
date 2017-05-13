
## Codeception\Module


* *Uses* `Codeception\Util\Shared\Asserts`

Basic class for Modules and Helpers.
You must extend from it while implementing own helpers.

Public methods of this class start with `_` prefix in order to ignore them in actor classes.
Module contains **HOOKS** which allow to handle test execution routine.




#### $includeInheritedActions

*public static* **$includeInheritedActions**

By setting it to false module wan't inherit methods of parent class.

type `bool`


#### $onlyActions

*public static* **$onlyActions**

Allows to explicitly set what methods have this class.

type `array`


#### $excludeActions

*public static* **$excludeActions**

Allows to explicitly exclude actions from module.

type `array`


#### $aliases

*public static* **$aliases**

Allows to rename actions

type `array`
#### __construct()

 *public* __construct($moduleContainer, $config = null) 

Module constructor.

Requires module container (to provide access between modules of suite) and config.

 * `param ModuleContainer` $moduleContainer
 * `param null` $config

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Module.php#L70)

#### _after()

 *public* _after($test) 

**HOOK** executed after test

 * `param TestInterface` $test

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Module.php#L257)

#### _afterStep()

 *public* _afterStep($step) 

**HOOK** executed after step

 * `param Step` $step

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Module.php#L239)

#### _afterSuite()

 *public* _afterSuite() 

**HOOK** executed after suite

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Module.php#L221)

#### _before()

 *public* _before($test) 

**HOOK** executed before test

 * `param TestInterface` $test

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Module.php#L248)

#### _beforeStep()

 *public* _beforeStep($step) 

**HOOK** executed before step

 * `param Step` $step

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Module.php#L230)

#### _beforeSuite()

 *public* _beforeSuite($settings = null) 

**HOOK** executed before suite

 * `param array` $settings

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Module.php#L214)

#### _cleanup()

 *public* _cleanup() 

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Module.php#L205)

#### _failed()

 *public* _failed($test, $fail) 

**HOOK** executed when test fails but before `_after`

 * `param TestInterface` $test
 * `param \Exception` $fail

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Module.php#L267)

#### _getConfig()

 *public* _getConfig($key = null) 

Get config values or specific config item.

 * `param null` $key
 * `return` array|mixed|null

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Module.php#L342)

#### _getName()

 *public* _getName() 

Returns a module name for a Module, a class name for Helper
 * `return` string

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Module.php#L177)

#### _hasRequiredFields()

 *public* _hasRequiredFields() 

Checks if a module has required fields
 * `return` bool

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Module.php#L193)

#### _initialize()

 *public* _initialize() 

**HOOK** triggered after module is created and configuration is loaded

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Module.php#L201)

#### _reconfigure()

 *public* _reconfigure($config) 

Allows to redefine config for a specific test.
Config is restored at the end of a test.

```php
<?php
// cleanup DB only for specific group of tests
public function _before(Test $test) {
    if (in_array('cleanup', $test->getMetadata()->getGroups()) {
        $this->getModule('Db')->_reconfigure(['cleanup' => true]);
    }
}
```

 * `param` $config
 * `throws` Exception\ModuleConfigException
 * `throws` ModuleException

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Module.php#L119)

#### _resetConfig()

 *public* _resetConfig() 

Reverts config changed by `_reconfigure`

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Module.php#L137)

#### _setConfig()

 *public* _setConfig($config) 

Allows to define initial module config.
Can be used in `_beforeSuite` hook of Helpers or Extensions

```php
<?php
public function _beforeSuite($settings = []) {
    $this->getModule('otherModule')->_setConfig($this->myOtherConfig);
}
```

 * `param` $config
 * `throws` Exception\ModuleConfigException
 * `throws` ModuleException

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Module.php#L95)

#### assert()

 *protected* assert($arguments, $not = null) 

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L6)

#### assertArrayHasKey()

 *protected* assertArrayHasKey($key, $actual, $description = null) 

 * `param` $key
 * `param` $actual
 * `param` $description

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L339)

#### assertArrayNotHasKey()

 *protected* assertArrayNotHasKey($key, $actual, $description = null) 

 * `param` $key
 * `param` $actual
 * `param` $description

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L349)

#### assertContains()

 *protected* assertContains($needle, $haystack, $message = null) 

Checks that haystack contains needle

 * `param`        $needle
 * `param`        $haystack
 * `param string` $message

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L149)

#### assertCount()

 *protected* assertCount($expectedCount, $actual, $description = null) 

 * `param` $expectedCount
 * `param` $actual
 * `param` $description

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L359)

#### assertEmpty()

 *protected* assertEmpty($actual, $message = null) 

Checks that variable is empty.

 * `param`        $actual
 * `param string` $message

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L197)

#### assertEquals()

 *protected* assertEquals($expected, $actual, $message = null, $delta = null) 

Checks that two variables are equal.

 * `param`        $expected
 * `param`        $actual
 * `param string` $message
 * `param float`  $delta

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L35)

#### assertFalse()

 *protected* assertFalse($condition, $message = null) 

Checks that condition is negative.

 * `param`        $condition
 * `param string` $message

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L252)

#### assertFileExists()

 *protected* assertFileExists($filename, $message = null) 

Checks if file exists

 * `param string` $filename
 * `param string` $message

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L288)

#### assertFileNotExists()

 *protected* assertFileNotExists($filename, $message = null) 

Checks if file doesn't exist

 * `param string` $filename
 * `param string` $message

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L300)

#### assertGreaterOrEquals()

 *protected* assertGreaterOrEquals($expected, $actual, $description = null) 

 * `param` $expected
 * `param` $actual
 * `param` $description

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L310)

#### assertGreaterThan()

 *protected* assertGreaterThan($expected, $actual, $message = null) 

Checks that actual is greater than expected

 * `param`        $expected
 * `param`        $actual
 * `param string` $message

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L84)

#### assertGreaterThanOrEqual()

 *protected* assertGreaterThanOrEqual($expected, $actual, $message = null) 

Checks that actual is greater or equal than expected

 * `param`        $expected
 * `param`        $actual
 * `param string` $message

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L104)

#### assertGreaterThen()

 *protected* assertGreaterThen($expected, $actual, $message = null) 
 * `deprecated` 
[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L92)

#### assertGreaterThenOrEqual()

 *protected* assertGreaterThenOrEqual($expected, $actual, $message = null) 
 * `deprecated` 
[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L112)

#### assertInstanceOf()

 *protected* assertInstanceOf($class, $actual, $description = null) 

 * `param` $class
 * `param` $actual
 * `param` $description

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L369)

#### assertInternalType()

 *protected* assertInternalType($type, $actual, $description = null) 

 * `param` $type
 * `param` $actual
 * `param` $description

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L389)

#### assertIsEmpty()

 *protected* assertIsEmpty($actual, $description = null) 

 * `param` $actual
 * `param` $description

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L329)

#### assertLessOrEquals()

 *protected* assertLessOrEquals($expected, $actual, $description = null) 

 * `param` $expected
 * `param` $actual
 * `param` $description

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L320)

#### assertLessThan()

 *protected* assertLessThan($expected, $actual, $message = null) 

Checks that actual is less than expected

 * `param`        $expected
 * `param`        $actual
 * `param string` $message

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L124)

#### assertLessThanOrEqual()

 *protected* assertLessThanOrEqual($expected, $actual, $message = null) 

Checks that actual is less or equal than expected

 * `param`        $expected
 * `param`        $actual
 * `param string` $message

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L136)

#### assertNot()

 *protected* assertNot($arguments) 

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L22)

#### assertNotContains()

 *protected* assertNotContains($needle, $haystack, $message = null) 

Checks that haystack doesn't contain needle.

 * `param`        $needle
 * `param`        $haystack
 * `param string` $message

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L161)

#### assertNotEmpty()

 *protected* assertNotEmpty($actual, $message = null) 

Checks that variable is not empty.

 * `param`        $actual
 * `param string` $message

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L208)

#### assertNotEquals()

 *protected* assertNotEquals($expected, $actual, $message = null, $delta = null) 

Checks that two variables are not equal

 * `param`        $expected
 * `param`        $actual
 * `param string` $message
 * `param float`  $delta

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L48)

#### assertNotInstanceOf()

 *protected* assertNotInstanceOf($class, $actual, $description = null) 

 * `param` $class
 * `param` $actual
 * `param` $description

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L379)

#### assertNotNull()

 *protected* assertNotNull($actual, $message = null) 

Checks that variable is not NULL

 * `param`        $actual
 * `param string` $message

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L230)

#### assertNotRegExp()

 *protected* assertNotRegExp($pattern, $string, $message = null) 

Checks that string not match with pattern

 * `param string` $pattern
 * `param string` $string
 * `param string` $message

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L185)

#### assertNotSame()

 *protected* assertNotSame($expected, $actual, $message = null) 

Checks that two variables are not same

 * `param`        $expected
 * `param`        $actual
 * `param string` $message

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L72)

#### assertNull()

 *protected* assertNull($actual, $message = null) 

Checks that variable is NULL

 * `param`        $actual
 * `param string` $message

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L219)

#### assertRegExp()

 *protected* assertRegExp($pattern, $string, $message = null) 

Checks that string match with pattern

 * `param string` $pattern
 * `param string` $string
 * `param string` $message

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L173)

#### assertSame()

 *protected* assertSame($expected, $actual, $message = null) 

Checks that two variables are same

 * `param`        $expected
 * `param`        $actual
 * `param string` $message

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L60)

#### assertThat()

 *protected* assertThat($haystack, $constraint, $message = null) 


 * `param`        $haystack
 * `param`        $constraint
 * `param string` $message

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L263)

#### assertThatItsNot()

 *protected* assertThatItsNot($haystack, $constraint, $message = null) 

Checks that haystack doesn't attend

 * `param`        $haystack
 * `param`        $constraint
 * `param string` $message

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L275)

#### assertTrue()

 *protected* assertTrue($condition, $message = null) 

Checks that condition is positive.

 * `param`        $condition
 * `param string` $message

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L241)

#### debug()

 *protected* debug($message) 

Print debug message to the screen.

 * `param` $message

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Module.php#L276)

#### debugSection()

 *protected* debugSection($title, $message) 

Print debug message with a title

 * `param` $title
 * `param` $message

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Module.php#L287)

#### fail()

 *protected* fail($message) 

Fails the test with message.

 * `param` $message

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Util/Shared/Asserts.php#L399)

#### getModule()

 *protected* getModule($name) 

Get another module by its name:

```php
<?php
$this->getModule('WebDriver')->_findElements('.items');
```

 * `param` $name
 * `return` Module
 * `throws` ModuleException

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Module.php#L328)

#### getModules()

 *protected* getModules() 

Get all enabled modules
 * `return` array

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Module.php#L311)

#### hasModule()

 *protected* hasModule($name) 

Checks that module is enabled.

 * `param` $name
 * `return` bool

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Module.php#L301)

#### onReconfigure()

 *protected* onReconfigure() 

HOOK to be executed when config changes with `_reconfigure`.

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Module.php#L129)

#### scalarizeArray()

 *protected* scalarizeArray($array) 

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Module.php#L353)

#### validateConfig()

 *protected* validateConfig() 

Validates current config for required fields and required packages.
 * `throws` Exception\ModuleConfigException
 * `throws` ModuleException

[See source](https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Module.php#L148)

<p>&nbsp;</p><div class="alert alert-warning">Reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/blob/2.2/src/Codeception/Module.php">Help us to improve documentation. Edit module reference</a></div>
