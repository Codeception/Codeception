# Redis


This module uses the [Predis](https://github.com/nrk/predis) library
to interact with a Redis server.

## Status

* Stability: **beta**

## Configuration

* **`host`** (`string`, default `'127.0.0.1'`) - The Redis host
* **`port`** (`int`, default `6379`) - The Redis port
* **`database`** (`int`, no default) - The Redis database. Needs to be specified.
* **`cleanupBefore`**: (`string`, default `'never'`) - Whether/when to flush the database:
    * `suite`: at the beginning of every suite
    * `test`: at the beginning of every test
    * Any other value: never

### Example (`unit.suite.yml`)

```yaml
   modules:
       - Redis:
           host: '127.0.0.1'
           port: 6379
           database: 0
           cleanupBefore: 'never'
```

## Public Properties

* **driver** - Contains the Predis client/driver

@author Marc Verney <marc@marcverney.net>

## Actions

### cleanup
 
Delete all the keys in the Redis database

@throws ModuleException


### dontSeeInRedis
 
Asserts that a key does not exist or, optionally, that it doesn't have the
provided $value

Examples:

``` php
<?php
// With only one argument, only checks the key does not exist
$I->dontSeeInRedis('example:string');

// Checks a String does not exist or its value is not the one provided
$I->dontSeeInRedis('example:string', 'life');

// Checks a List does not exist or its value is not the one provided (order of elements is compared).
$I->dontSeeInRedis('example:list', ['riri', 'fifi', 'loulou']);

// Checks a Set does not exist or its value is not the one provided (order of members is ignored).
$I->dontSeeInRedis('example:set', ['riri', 'fifi', 'loulou']);

// Checks a ZSet does not exist or its value is not the one provided (scores are required, order of members is compared)
$I->dontSeeInRedis('example:zset', ['riri' => 1, 'fifi' => 2, 'loulou' => 3]);

// Checks a Hash does not exist or its value is not the one provided (order of members is ignored).
$I->dontSeeInRedis('example:hash', ['riri' => true, 'fifi' => 'Dewey', 'loulou' => 2]);
```

 * `param string` $key   The key name
 * `param mixed`  $value Optional. If specified, also checks the key has this
value. Booleans will be converted to 1 and 0 (even inside arrays)


### dontSeeRedisKeyContains
 
Asserts that a given key does not contain a given item

Examples:

``` php
<?php
// Strings: performs a substring search
$I->dontSeeRedisKeyContains('string', 'bar');

// Lists
$I->dontSeeRedisKeyContains('example:list', 'poney');

// Sets
$I->dontSeeRedisKeyContains('example:set', 'cat');

// ZSets: check whether the zset has this member
$I->dontSeeRedisKeyContains('example:zset', 'jordan');

// ZSets: check whether the zset has this member with this score
$I->dontSeeRedisKeyContains('example:zset', 'jordan', 23);

// Hashes: check whether the hash has this field
$I->dontSeeRedisKeyContains('example:hash', 'magic');

// Hashes: check whether the hash has this field with this value
$I->dontSeeRedisKeyContains('example:hash', 'magic', 32);
```

 * `param string` $key       The key
 * `param mixed`  $item      The item
 * `param null`   $itemValue Optional and only used for zsets and hashes. If
specified, the method will also check that the $item has this value/score

 * `return` bool


### grabFromRedis
 
Returns the value of a given key

Examples:

``` php
<?php
// Strings
$I->grabFromRedis('string');

// Lists: get all members
$I->grabFromRedis('example:list');

// Lists: get a specific member
$I->grabFromRedis('example:list', 2);

// Lists: get a range of elements
$I->grabFromRedis('example:list', 2, 4);

// Sets: get all members
$I->grabFromRedis('example:set');

// ZSets: get all members
$I->grabFromRedis('example:zset');

// ZSets: get a range of members
$I->grabFromRedis('example:zset', 3, 12);

// Hashes: get all fields of a key
$I->grabFromRedis('example:hash');

// Hashes: get a specific field of a key
$I->grabFromRedis('example:hash', 'foo');
```

 * `param string` $key The key name

@throws ModuleException if the key does not exist


### haveInRedis
 
Creates or modifies keys

If $key already exists:

- Strings: its value will be overwritten with $value
- Other types: $value items will be appended to its value

Examples:

``` php
<?php
// Strings: $value must be a scalar
$I->haveInRedis('string', 'Obladi Oblada');

// Lists: $value can be a scalar or an array
$I->haveInRedis('list', ['riri', 'fifi', 'loulou']);

// Sets: $value can be a scalar or an array
$I->haveInRedis('set', ['riri', 'fifi', 'loulou']);

// ZSets: $value must be an associative array with scores
$I->haveInRedis('zset', ['riri' => 1, 'fifi' => 2, 'loulou' => 3]);

// Hashes: $value must be an associative array
$I->haveInRedis('hash', ['obladi' => 'oblada']);
```

 * `param string` $type  The type of the key
 * `param string` $key   The key name
 * `param mixed`  $value The value

@throws ModuleException


### seeInRedis
 
Asserts that a key exists, and optionally that it has the provided $value

Examples:

``` php
<?php
// With only one argument, only checks the key exists
$I->seeInRedis('example:string');

// Checks a String exists and has the value "life"
$I->seeInRedis('example:string', 'life');

// Checks the value of a List. Order of elements is compared.
$I->seeInRedis('example:list', ['riri', 'fifi', 'loulou']);

// Checks the value of a Set. Order of members is ignored.
$I->seeInRedis('example:set', ['riri', 'fifi', 'loulou']);

// Checks the value of a ZSet. Scores are required. Order of members is compared.
$I->seeInRedis('example:zset', ['riri' => 1, 'fifi' => 2, 'loulou' => 3]);

// Checks the value of a Hash. Order of members is ignored.
$I->seeInRedis('example:hash', ['riri' => true, 'fifi' => 'Dewey', 'loulou' => 2]);
```

 * `param string` $key   The key name
 * `param mixed`  $value Optional. If specified, also checks the key has this
value. Booleans will be converted to 1 and 0 (even inside arrays)


### seeRedisKeyContains
 
Asserts that a given key contains a given item

Examples:

``` php
<?php
// Strings: performs a substring search
$I->seeRedisKeyContains('example:string', 'bar');

// Lists
$I->seeRedisKeyContains('example:list', 'poney');

// Sets
$I->seeRedisKeyContains('example:set', 'cat');

// ZSets: check whether the zset has this member
$I->seeRedisKeyContains('example:zset', 'jordan');

// ZSets: check whether the zset has this member with this score
$I->seeRedisKeyContains('example:zset', 'jordan', 23);

// Hashes: check whether the hash has this field
$I->seeRedisKeyContains('example:hash', 'magic');

// Hashes: check whether the hash has this field with this value
$I->seeRedisKeyContains('example:hash', 'magic', 32);
```

 * `param string` $key       The key
 * `param mixed`  $item      The item
 * `param null`   $itemValue Optional and only used for zsets and hashes. If
specified, the method will also check that the $item has this value/score

 * `return` bool


### sendCommandToRedis
 
Sends a command directly to the Redis driver. See documentation at
https://github.com/nrk/predis
Every argument that follows the $command name will be passed to it.

Examples:

``` php
<?php
$I->sendCommandToRedis('incr', 'example:string');
$I->sendCommandToRedis('strLen', 'example:string');
$I->sendCommandToRedis('lPop', 'example:list');
$I->sendCommandToRedis('zRangeByScore', 'example:set', '-inf', '+inf', ['withscores' => true, 'limit' => [1, 2]]);
$I->sendCommandToRedis('flushdb');
```

 * `param string` $command The command name


<p>&nbsp;</p><div class="alert alert-warning">Module reference is taken from the source code. <a href="https://github.com/Codeception/Codeception/tree/3.0/src/Codeception/Module/Redis.php">Help us to improve documentation. Edit module reference</a></div>
