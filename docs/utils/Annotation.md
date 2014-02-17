
## Codeception\Util\Annotation


Simple annotation parser. Take only key-value annotations for methods or class.

### Methods

#### public **__construct**


#### public **fetch**

@param $annotation
@return null


#### public **fetchAll**

@param $annotation
@return array


#### public static **forClass**

Grabs annotation values.

Usage example:

``` php
<?php
Annotation::forClass('MyTestCase')->fetch('guy');
Annotation::forClass('MyTestCase')->method('testData')->fetch('depends');
Annotation::forClass('MyTestCase')->method('testData')->fetchAll('depends');

?>
```

@param $class

@return $this


#### public static **forMethod**

@param $class
@param $method

@return $this


#### public **method**

@param $method

@return $this


