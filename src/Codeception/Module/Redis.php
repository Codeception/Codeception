<?php

namespace Codeception\Module;

use Codeception\Exception\ModuleException as ModuleException;
use Codeception\Lib\Driver\Redis as RedisDriver;

/**
 * Works with Redis database.
 *
 * Cleans up Redis database after each run.
 *
 * ## Status
 *
 * * Maintainer: **judgedim**
 * * Stability: **beta**
 * * Contact: https://github.com/judgedim
 *
 * ## Configuration
 *
 * * host *required* - redis host to connect
 * * port *required* - redis port.
 * * database *required* - redis database.
 * * cleanup: true - defined data will be purged before running every test.
 *
 * ## Public Properties
 * * driver - contains Connection Driver
 *
 * ### Beta Version
 *
 * Report an issue if this module doesn't work for you.
 *
 * @author judgedim
 */
class Redis extends \Codeception\Module
{
    protected $config = [
        'cleanup' => true
    ];

    /**
     * @var RedisDriver
     */
    public $driver;

    protected $requiredFields = ['host', 'port', 'database'];

    public function _initialize()
    {
        try {
            $this->driver = new RedisDriver($this->config['host'], $this->config['port']);
            $this->driver->select_db($this->config['database']);
        } catch (\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage());
        }

    }

    public function _before(\Codeception\TestCase $test)
    {
        if ($this->config['cleanup']) {
            $this->cleanup();
        }
        parent::_before($test);
    }

    public function _after(\Codeception\TestCase $test)
    {
        parent::_after($test);
    }

    /**
     * Cleans up Redis database.
     */
    public function cleanupRedis()
    {
        $this->cleanup();
    }

    protected function cleanup()
    {
        try {
            $this->driver->flushdb();
        } catch (\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage());
        }
    }

}
