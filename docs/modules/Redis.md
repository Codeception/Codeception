# Redis Module
**For additional reference,, please review the [source](https://github.com/Codeception/Codeception/tree/master/src/Codeception/Module/Redis)**
Works with Redis database.

Cleans up Redis database after each run.

## Status

* Maintainer: **judgedim**
* stability: beta
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

 * author judgedim

## Actions


### cleanupRedis


Cleans up Redis database.
