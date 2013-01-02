<?php
/**
 * @author judgedim
 */

namespace Codeception\Module;

use \Codeception\Util\Driver\Redis as RedisDriver;

class Redis extends \Codeception\Module
{
    /**
     * @api
     * @var
     */
    public $dbh;

    protected $isDumpFileEmpty = false;

    protected $config = array(
        'cleanup' => true
    );

    /**
     * @var \Codeception\Util\Driver\Redis
     */
    public $driver;

    protected $requiredFields = array('host', 'port', 'database');

    public function _initialize()
    {
        try {
            $this->driver = new RedisDriver($this->config['host'], $this->config['port']);
            $this->driver->select_db($this->config['database']);
        } catch (\Exception $e) {
            throw new \Codeception\Exception\Module(__CLASS__, $e->getMessage());
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

    protected function cleanup()
    {
        try {
            $this->driver->flushdb();

        } catch (\Exception $e) {
            throw new \Codeception\Exception\Module(__CLASS__, $e->getMessage());
        }
    }

}
