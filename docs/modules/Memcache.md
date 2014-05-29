# Memcache Module

**For additional reference, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/Memcache.php)**


Connects to [memcached](http://www.memcached.org/) using either _Memcache_ or _Memcached_ extension.

Performs a cleanup by flushing all values after each test run.

## Status

* Maintainer: **davert**
* Stability: **beta**
* Contact: codecept@davert.mail.ua

## Configuration

* host: localhost - memcached host to connect
* port: 11211 - default memcached port.

Be sure you don't use the production server to connect.

## Public Properties

* memcache - instance of Memcache object












### grabValueFromMemcached
 Grabs value from memcached by key

Example:

``` php
<?php
$users_count = $I->grabValueFromMemcached('users_count');
?>
```

 * `param`  $key
 * `return`  array|string

### seeInMemcached
 Checks item in Memcached exists and the same as expected.

 * `param`  $key
 * `param`  $value

### dontSeeInMemcached
 Checks item in Memcached doesn't exist or is the same as expected.

 * `param`  $key
 * `param`  bool $value

### clearMemcache
 Flushes all Memcached data.






































