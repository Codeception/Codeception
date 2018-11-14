
## Codeception\Util\JsonType



JsonType matches JSON structures against templates.
You can specify the type of fields in JSON or add additional validation rules.

JsonType is used by REST module in `seeResponseMatchesJsonType` and `dontSeeResponseMatchesJsonType` methods.

Usage example:

```php
<?php
$jsonType = new JsonType(['name' => 'davert', 'id' => 1]);
$jsonType->matches([
  'name' => 'string:!empty',
  'id' => 'integer:>0|string:>0',
]); // => true

$jsonType->matches([
  'id' => 'string',
]); // => `id: 1` is not of type string
?>
```

Class JsonType
@package Codeception\Util


#### __construct()

 *public* __construct($jsonArray) 

Creates instance of JsonType
Pass an array or `\Codeception\Util\JsonArray` with data.
If non-associative array is passed - the very first element of it will be used for matching.

 * `param` $jsonArray array|\Codeception\Util\JsonArray

[See source](https://github.com/Codeception/Codeception/blob/2.5/src/Codeception/Util/JsonType.php#L42)

#### addCustomFilter()

 *public static* addCustomFilter($name, callable $callable) 

Adds custom filter to JsonType list.
You should specify a name and parameters of a filter.

Example:

```php
<?php
JsonType::addCustomFilter('slug', function($value) {
    return strpos(' ', $value) !== false;
});
// => use it as 'string:slug'


// add custom function to matcher with `len($val)` syntax
// parameter matching patterns should be valid regex and start with `/` char
JsonType::addCustomFilter('/len\((.*?)\)/', function($value, $len) {
  return strlen($value) == $len;
});
// use it as 'string:len(5)'
?>
```

 * `param` $name
 * `param callable` $callable

[See source](https://github.com/Codeception/Codeception/blob/2.5/src/Codeception/Util/JsonType.php#L76)

#### cleanCustomFilters()

 *public static* cleanCustomFilters() 

Removes all custom filters

[See source](https://github.com/Codeception/Codeception/blob/2.5/src/Codeception/Util/JsonType.php#L84)

#### matchFilter()

 *protected* matchFilter($filter, $value) 

[See source](https://github.com/Codeception/Codeception/blob/2.5/src/Codeception/Util/JsonType.php#L158)

#### matches()

 *public* matches(array $jsonType) 

Checks data against passed JsonType.
If matching fails function returns a string with a message describing failure.
On success returns `true`.

 * `param array` $jsonType
 * `return` bool|string

[See source](https://github.com/Codeception/Codeception/blob/2.5/src/Codeception/Util/JsonType.php#L97)

#### typeComparison()

 *protected* typeComparison($data, $jsonType) 

[See source](https://github.com/Codeception/Codeception/blob/2.5/src/Codeception/Util/JsonType.php#L116)

<p>&nbsp;</p><div class="alert alert-warning">Reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/blob/2.5/src//Codeception/Util/JsonType.php">Help us to improve documentation. Edit module reference</a></div>
