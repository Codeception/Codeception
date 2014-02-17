
## Codeception\Util\Annotation


Simple annotation parser. Take only key-value annotations for methods or class.

### Methods


#### *public* __construct
[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Annotation.php#L63)

#### *public* fetch
 *  param $annotation
 *  return null

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Annotation.php#L83)

#### *public* fetchAll
 *  param $annotation
 *  return array

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Annotation.php#L96)

#### *public static* forClass
Grabs annotation values.

Usage example:

``` php
<?php
Annotation::forClass('MyTestCase')->fetch('guy');
Annotation::forClass('MyTestCase')->method('testData')->fetch('depends');
Annotation::forClass('MyTestCase')->method('testData')->fetchAll('depends');

?>
```

 *  param $class

 *  return $this

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Annotation.php#L39)

#### *public static* forMethod
 *  param $class
 *  param $method

 *  return $this

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Annotation.php#L58)

#### *public* method
 *  param $method

 *  return $this

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Annotation.php#L73)
