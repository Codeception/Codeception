# Apc


This module interacts with the [Alternative PHP Cache (APC)](http://php.net/manual/en/intro.apcu.php)
using either _APCu_ or _APC_ extension.

Performs a cleanup by flushing all values after each test run.

## Status

* Maintainer: **Serghei Iakovlev**
* Stability: **stable**
* Contact: serghei@phalconphp.com

### Example (`unit.suite.yml`)

```yaml
   modules:
       - Apc
```

Be sure you don't use the production server to connect.



## Actions

### dontSeeInApc
 
Checks item in APC(u) doesn't exist or is the same as expected.

Examples:

``` php
<?php
// With only one argument, only checks the key does not exist
$I->dontSeeInApc('users_count');

// Checks a 'users_count' exists does not exist or its value is not the one provided
$I->dontSeeInApc('users_count', 200);
?>
```

 * `param string|string[]` $key
 * `param mixed` $value


### flushApc
 
Clears the APC(u) cache


### grabValueFromApc
 
Grabs value from APC(u) by key.

Example:

``` php
<?php
$users_count = $I->grabValueFromApc('users_count');
?>
```

 * `param string|string[]` $key


### haveInApc
 
Stores an item `$value` with `$key` on the APC(u).

Examples:

```php
<?php
// Array
$I->haveInApc('users', ['name' => 'miles', 'email' => 'miles@davis.com']);

// Object
$I->haveInApc('user', UserRepository::findFirst());

// Key as array of 'key => value'
$entries = [];
$entries['key1'] = 'value1';
$entries['key2'] = 'value2';
$entries['key3'] = ['value3a','value3b'];
$entries['key4'] = 4;
$I->haveInApc($entries, null);
?>
```

 * `param string|array` $key
 * `param mixed` $value
 * `param int` $expiration


### seeInApc
 
Checks item in APC(u) exists and the same as expected.

Examples:

``` php
<?php
// With only one argument, only checks the key exists
$I->seeInApc('users_count');

// Checks a 'users_count' exists and has the value 200
$I->seeInApc('users_count', 200);
?>
```

 * `param string|string[]` $key
 * `param mixed` $value

<p>&nbsp;</p><div class="alert alert-warning">Module reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/tree/2.2/src/Codeception/Module/Apc.php">Help us to improve documentation. Edit module reference</a></div>
