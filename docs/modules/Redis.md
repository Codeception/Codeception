# Redis Module

**For additional reference, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/Redis.php)**
## Codeception\Module\Redis

* *Extends* `Codeception\Module`

Works with Redis database.

Cleans up Redis database after each run.

## Status

* Maintainer: **judgedim**
* Stability: **beta**
* Contact: https://github.com/judgedim

## Configuration

* host *required* - redis host to connect
* port *required* - redis port.
* database *required* - redis database.
* cleanup: true - defined data will be purged before running every test.

## Public Properties
* driver - contains Connection Driver

### Beta Version

Report an issue if this module doesn't work for you.

@author judgedim

#### *public* driver* `var`  RedisDriver

#### *public static* includeInheritedActionsBy setting it to false module wan't inherit methods of parent class.

 * `var`  bool
#### *public static* onlyActionsAllows to explicitly set what methods have this class.

 * `var`  array
#### *public static* excludeActionsAllows to explicitly exclude actions from module.

 * `var`  array
#### *public static* aliasesAllows to rename actions

 * `var`  array




### cleanupRedis
#### *public* cleanupRedis()Cleans up Redis database.





































