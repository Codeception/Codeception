
## Codeception\Util\Autoload



Autoloader, which is fully compatible with PSR-4, and can be used to autoload your `Helper`, `Page`, and `Step` classes.


#### *public static* addNamespace($prefix, $base_dir, $prepend = null) 

Adds a base directory for a namespace prefix.

Example:

```php
<?php
// app\Codeception\UserHelper will be loaded from '/path/to/helpers/UserHelper.php'
Autoload::addNamespace('app\Codeception', '/path/to/helpers');

// LoginPage will be loaded from '/path/to/pageobjects/LoginPage.php'
Autoload::addNamespace('', '/path/to/pageobjects');

Autoload::addNamespace('app\Codeception', '/path/to/controllers');
?>
```

 * `param string` $prefix The namespace prefix.
 * `param string` $base_dir A base directory for class files in the namespace.
 * `param bool` $prepend If true, prepend the base directory to the stack instead of appending it; this causes it to be searched first rather than last.
 * `return`  void

[See source](https://github.com/Codeception/Codeception/blob/2.0/src/Codeception/Util/Autoload.php#L42)

#### *public static* load($class) 

[See source](https://github.com/Codeception/Codeception/blob/2.0/src/Codeception/Util/Autoload.php#L85)

#### *public static* register($namespace, $suffix, $path) 

 * `deprecated`  Use self::addNamespace() instead.

[See source](https://github.com/Codeception/Codeception/blob/2.0/src/Codeception/Util/Autoload.php#L72)

#### *public static* registerSuffix($suffix, $path) 

 * `deprecated`  Use self::addNamespace() instead.

[See source](https://github.com/Codeception/Codeception/blob/2.0/src/Codeception/Util/Autoload.php#L80)

<p>&nbsp;</p><div class="alert alert-warning">Reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/blob/2.0/src/Codeception/Util/Autoload.php">Help us to improve documentation. Edit module reference</a></div>
