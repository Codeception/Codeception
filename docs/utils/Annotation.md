
## Codeception\Util\Annotation



Simple annotation parser. Take only key-value annotations for methods or class.





#### *public* __construct($class) 

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Annotation.php#L63)

#### *public* fetch($annotation) 

 * `param`  $annotation
 * `return`  null

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Annotation.php#L83)

#### *public* fetchAll($annotation) 

 * `param`  $annotation
 * `return`  array

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Annotation.php#L96)

#### *public static* forClass($class) 

Grabs annotation values.

Usage example:

``` php
<?php
Annotation::forClass('MyTestCase')->fetch('guy');
Annotation::forClass('MyTestCase')->method('testData')->fetch('depends');
Annotation::forClass('MyTestCase')->method('testData')->fetchAll('depends');

?>
```

 * `param`  $class

 * `return`  $this

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Annotation.php#L39)

#### *public static* forMethod($class, $method) 

 * `param`  $class
 * `param`  $method

 * `return`  $this

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Annotation.php#L58)

#### *public* method($method) 

 * `param`  $method

 * `return`  $this

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Annotation.php#L73)

