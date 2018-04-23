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
 * * Contact: davert@codeception.com
 *
 * ## Configuration
 *
 * * **`host`** (`string`, default `'localhost'`) - The memcached host
 * * **`port`** (`int`, default `11211`) - The memcached port
 *
 * ### Example (`unit.suite.yml`)
 *
 * ```yaml
 *    modules:
 *        - Memcache:
 *            host: 'localhost'
 *            port: 11211
 * ```
 *
 * Be sure you don't use the production server to connect.
 *
 * ## Public Properties
 *
 * * **memcache** - instance of _Memcache_ or _Memcached_ object
 *
 */
class Memcache extends CodeceptionModule
{
    /**
     * @var \Memcache|\Memcached
     */
    public $memcache = null;

    /**
     * {@inheritdoc}
     */
    protected $config = [
        'host' => 'localhost',
        'port' => 11211
    ];

    /**
     * Code to run before each test.
     *
     * @param TestInterface $test
     * @throws ModuleConfigException
     */
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

    /**
     * Code to run after each test.
     *
     * @param TestInterface $test
     */
    public function _after(TestInterface $test)
    {
        if (empty($this->memcache)) {
            return;
        }

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
     * Grabs value from memcached by key.
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
     * Examples:
     *
     * ``` php
     * <?php
     * // With only one argument, only checks the key exists
     * $I->seeInMemcached('users_count');
     *
     * // Checks a 'users_count' exists and has the value 200
     * $I->seeInMemcached('users_count', 200);
     * ?>
     * ```
     *
     * @param $key
     * @param $value
     */
    public function seeInMemcached($key, $value = null)
    {
        $actual = $this->memcache->get($key);
        $this->debugSection("Value", $actual);

        if (null === $value) {
            $this->assertNotFalse($actual, "Cannot find key '$key' in Memcached");
        } else {
            $this->assertEquals($value, $actual, "Cannot find key '$key' in Memcached with the provided value");
        }
    }

    /**
     * Checks item in Memcached doesn't exist or is the same as expected.
     *
     * Examples:
     *
     * ``` php
     * <?php
     * // With only one argument, only checks the key does not exist
     * $I->dontSeeInMemcached('users_count');
     *
     * // Checks a 'users_count' exists does not exist or its value is not the one provided
     * $I->dontSeeInMemcached('users_count', 200);
     * ?>
     * ```
     *
     * @param $key
     * @param $value
     */
    public function dontSeeInMemcached($key, $value = null)
    {
        $actual = $this->memcache->get($key);
        $this->debugSection("Value", $actual);

        if (null === $value) {
            $this->assertFalse($actual, "The key '$key' exists in Memcached");
        } else {
            if (false !== $actual) {
                $this->assertEquals($value, $actual, "The key '$key' exists in Memcached with the provided value");
            }
        }
    }

    /**
     * Stores an item `$value` with `$key` on the Memcached server.
     *
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     */
    public function haveInMemcached($key, $value, $expiration = null)
    {
        switch (get_class($this->memcache)) {
            case 'Memcache':
                $this->assertTrue($this->memcache->set($key, $value, null, $expiration));
                break;
            case 'Memcached':
                $this->assertTrue($this->memcache->set($key, $value, $expiration));
                break;
        }
    }

    /**
     * Flushes all Memcached data.
     */
    public function clearMemcache()
    {
        $this->memcache->flush();
    }
}
