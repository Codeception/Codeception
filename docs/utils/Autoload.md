
## Codeception\Util\Autoload


Custom autoloader to load classes by suffixes: `Helper`, `Page`, `Step`, etc.


### Methods

#### public static **load**

@param $class
@return bool


#### public static **matches**

*is public for testing purposes*

@param $class
@param $namespace
@param $suffix
@return bool


#### public static **register**

A very basic yet useful autoloader, not compatible with PSR-0.
It is used to autoload classes by namespaces with suffixes.

Example:

``` php
<?php
// loads UserHelper in 'helpers/UserHelper.php'
Autoload::register('app\Codeception\Helper','Helper', __DIR__.'/helpers/');
// loads UserHelper in 'helpers/UserHelper.php'
Autoload::register('app\tests','Page', __DIR__.'/pageobjects/');
Autoload::register('app\tests','Controller', __DIR__.'/controllers/');
?>
```

@param $namespace
@param $suffix
@param $path


#### public static **registerSuffix**

Shortcut for {@link self::register} for classes with empty namespaces.

@param $suffix
@param $path


