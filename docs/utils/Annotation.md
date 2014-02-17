
## Codeception\Util\Annotation


Simple annotation parser. Take only key-value annotations for methods or class.

### Methods


[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Annotation.php#L63)

 *  param $annotation
 *  return null

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Annotation.php#L83)

 *  param $annotation
 *  return array

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Annotation.php#L96)

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

 *  param $class
 *  param $method

 *  return $this

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Annotation.php#L58)

 *  param $method

 *  return $this

[See source](https://github.com/Codeception/Codeception/blob/master/src/Codeception/Util/Annotation.php#L73)
