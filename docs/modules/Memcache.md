# Memcache Module

Connects to [memcached](http://www.memcached.org/) using [PECL](http://www.php.net/manual/en/intro.memcache.php) extension.
Performs a cleanup by flushing all values after each test run.

## Configuration

* host: localhost - memcached host to connect
* port: 11211 - default memcached port.

Be sure you don't use the production server to connect.

## Public Properties

* memcache - instance of Memcache object


## Actions


### clearMemcache


Flushes all Memcached data.


### dontSeeInMemcached


Checks item in Memcached doesn't exist or is the same as expected.

 * param $key
 * param bool $value


### grabValueFromMemcached


Grabs value from memcached by key

Example:

``` php
<?php
$users_count = $I->grabValueFromMemcached('users_count');
?>
```

 * param $key
 * return array|string


### seeInMemcached


Checks item in Memcached exists and the same as expected.

 * param $key
 * param $value
