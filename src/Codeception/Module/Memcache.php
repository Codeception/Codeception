<?php
namespace Codeception\Module;

use Codeception\Module as CodeceptionModule;
use Codeception\TestInterface;
use Codeception\Exception\ModuleConfigException;

/**
 * Connects to [memcached](http://www.memcached.org/) using either _Memcache_ or _Memcached_ extension.
 *
 * Performs a cleanup by flushing all values after each test run.
 *
 * ## Status
 *
 * * Maintainer: **davert**
 * * Stability: **beta**
 * * Contact: codecept@davert.mail.ua
 *
 * ## Configuration
 *
 * * host: localhost - memcached host to connect
 * * port: 11211 - default memcached port.
 *
 * Be sure you don't use the production server to connect.
 *
 * ## Public Properties
 *
 * * memcache - instance of Memcache object
 *
 */
class Memcache extends CodeceptionModule
{
    /**
     * @var \Memcache|\Memcached
     */
    public $memcache = null;

    protected $config = ['host' => 'localhost', 'port' => 11211];

    public function _before(TestInterface $test)
    {
        if (class_exists('\Memcache')) {
            $this->memcache = new \Memcache;
            $this->memcache->connect($this->config['host'], $this->config['port']);
        } elseif (class_exists('\Memcached')) {
            $this->memcache = new \Memcached;
            $this->memcache->addServer($this->config['host'], $this->config['port']);
        } else {
            throw new ModuleConfigException(__CLASS__, 'Memcache classes not loaded');
        }
    }

    public function _after(TestInterface $test)
    {
        $this->memcache->flush();
        switch (get_class($this->memcache)) {
            case 'Memcache':
                $this->memcache->close();
                break;
            case 'Memcached':
                $this->memcache->quit();
                break;
        }
    }

    /**
     * Grabs value from memcached by key
     *
     * Example:
     *
     * ``` php
     * <?php
     * $users_count = $I->grabValueFromMemcached('users_count');
     * ?>
     * ```
     *
     * @param $key
     * @return array|string
     */
    public function grabValueFromMemcached($key)
    {
        $value = $this->memcache->get($key);
        $this->debugSection("Value", $value);
        return $value;
    }

    /**
     * Checks item in Memcached exists and the same as expected.
     *
     * @param $key
     * @param $value
     */
    public function seeInMemcached($key, $value = false)
    {
        $actual = $this->memcache->get($key);
        $this->debugSection("Value", $actual);
        $this->assertEquals($value, $actual);
    }

    /**
     * Checks item in Memcached doesn't exist or is the same as expected.
     *
     * @param $key
     * @param bool $value
     */
    public function dontSeeInMemcached($key, $value = false)
    {
        $actual = $this->memcache->get($key);
        $this->debugSection("Value", $actual);
        $this->assertNotEquals($value, $actual);
    }

    /**
     * Flushes all Memcached data.
     */
    public function clearMemcache()
    {
        $this->memcache->flush();
    }
}
