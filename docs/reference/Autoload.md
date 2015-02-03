
## Codeception\Util\Autoload



Custom autoloader to load classes by suffixes: `Helper`, `Page`, `Step`, etc.



#### *public static* load($class) 

 * `param` $class
 * `return`  bool

[See source](https://github.com/Codeception/Codeception/blob/2.0/src/Codeception/Util/Autoload.php#L58)

#### *public static* matches($class, $namespace, $suffix) 

*is public for testing purposes*

 * `param` $class
 * `param` $namespace
 * `param` $suffix
 * `return`  bool

[See source](https://github.com/Codeception/Codeception/blob/2.0/src/Codeception/Util/Autoload.php#L86)

#### *public static* register($namespace, $suffix, $path) 

A very basic yet useful autoloader, not compatible with PSR-0.
It is used to autoload classes by namespaces with suffixes.

Example:

``` php
<?php
// loads UserHelper in 'helpers/UserHelper.php'
Autoload::register('app\Codeception\Helper', 'Helper', __DIR__.'/helpers/');
// loads LoginPage in 'pageobjects/LoginPage.php'
Autoload::register('app\tests', 'Page', __DIR__.'/pageobjects/');
Autoload::register('app\tests', 'Controller', __DIR__.'/controllers/');
?>
```

 * `param` $namespace
 * `param` $suffix
 * `param` $path

[See source](https://github.com/Codeception/Codeception/blob/2.0/src/Codeception/Util/Autoload.php#L34)

#### *public static* registerSuffix($suffix, $path) 

Shortcut for { * `link`  self::register} for classes with empty namespaces.

 * `param` $suffix
 * `param` $path

[See source](https://github.com/Codeception/Codeception/blob/2.0/src/Codeception/Util/Autoload.php#L49)

<p>&nbsp;</p><div class="alert alert-warning">Reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/blob/2.0/src/Codeception/Util/Autoload.php">Help us to improve documentation. Edit module reference</a></div>
