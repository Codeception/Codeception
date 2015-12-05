<?php

namespace Codeception\Module;

use Codeception\Exception\ModuleException;
use Codeception\Module as CodeceptionModule;
use Codeception\TestCase;

/**
 * Connects to [Aerospike](http://www.aerospike.com/) using [php-aerospike](http://www.aerospike.com/docs/client/php) extension.
 *
 * Performs a cleanup by flushing all values after each test run.
 *
 * ## Status
 *
 * * Maintainer: **Serghei Iakovlev**
 * * Stability: **beta**
 * * Contact: sadhooklay@gmail.com
 *
 * ## Configuration
 *
 * * addr: localhost - Aerospike host to connect
 * * port: 3000 - default Aerospike port
 * * set: cache - the Aerospike set to store data
 * * namespace: test - the Aerospike namespace to store data
 * * persistent: false - use persistent connection
 *
 *
 * ## Example
 *
 *     modules:
 *         enabled:
 *             - Aerospike:
 *                 addr: '127.0.0.1'
 *                 port: 3000
 *                 set: 'cache'
 *                 namespace: 'test'
 *                 persistent: false
 *
 * Be sure you don't use the production server to connect.
 *
 * ## Public Properties
 *
 * * aerospike - instance of Aerospike object
 *
 */
class Aerospike extends CodeceptionModule
{
    /**
     * The Aerospike
     * @var \Aerospike
     */
    public $aerospike = null;

    protected $config = [
        'addr'    => '127.0.0.1',
        'port'    => 3000,
        'options' => [],
        'cleanup' => true
    ];

    /**
     * @var array
     */
    protected $requiredFields = ['set', 'namespace'];

    protected $keys = [];

    public function _initialize()
    {
        if (!class_exists('\Aerospike')) {
            throw new ModuleException(__CLASS__, 'Aerospike classes not loaded');
        }

        $persistent = false;
        if (isset($this->config['persistent'])) {
            $persistent = (bool) $this->config['persistent'];
        }

        $opts = [];
        if (isset($this->config['options']) && is_array($this->config['options'])) {
            $opts = $this->config['options'];
        }

        $this->aerospike = new \Aerospike(
            ['hosts' => [['addr' => $this->config['addr'], 'port' => $this->config['port']]]],
            $persistent,
            $opts
        );
    }

    public function _before(TestCase $test)
    {
        if ($this->config['cleanup']) {
            $this->cleanup();
        }
        parent::_before($test);
    }

    public function _after(TestCase $test)
    {
        if ($this->aerospike->isConnected()) {
            $this->aerospike->close();
        }

        parent::_after($test);
    }

    /**
     * Grabs value from Aerospike by key
     *
     * Example:
     *
     * ```php
     * <?php
     * $users_count = $I->grabValueFromAerospike('users_count');
     * ?>
     * ```
     *
     * @param $key
     * @return mixed
     */
    public function grabValueFromAerospike($key)
    {
        $key = $this->buildKey($key);
        $this->aerospike->get($key, $data);

        $this->debugSection('Value', $data['bins']['value']);
        return $data['bins']['value'];
    }

    /**
     * Checks item in Aerospike exists and the same as expected.
     *
     * @param $key
     * @param mixed $value
     */
    public function seeInAerospike($key, $value = false)
    {
        $key = $this->buildKey($key);
        $this->aerospike->get($key, $actual);

        $this->debugSection('Value', $actual['bins']['value']);
        $this->assertEquals($value, $actual['bins']['value']);
    }

    /**
     * Checks item in Aerospike doesn't exist or is the same as expected.
     *
     * @param $key
     * @param mixed $value
     */
    public function dontSeeInAerospike($key, $value = false)
    {
        $key = $this->buildKey($key);
        $this->aerospike->get($key, $actual);

        $this->debugSection('Value', $actual['bins']['value']);
        $this->assertNotEquals($value, $actual['bins']['value']);
    }

    /**
     * Cleans up Aerospike database.
     */
    public function cleanupAerospike()
    {
        $this->cleanup();
    }

    protected function cleanup()
    {
        foreach ($this->keys as $key) {
            $status = $this->aerospike->remove(
                $key,
                [\Aerospike::OPT_POLICY_RETRY => \Aerospike::POLICY_RETRY_ONCE]
            );

            if (\Aerospike::OK != $status) {
                $this->fail(sprintf('Error [%s]: %s', $this->aerospike->errorno(), $this->aerospike->error()));
            }
        }
    }

    /**
     * Generates a unique key used for storing data in Aerospike DB.
     *
     * @param string $key Cache key
     * @return array
     */
    protected function buildKey($key)
    {
        return $this->aerospike->initKey(
            $this->config['namespace'],
            $this->config['set'],
            $key
        );
    }
}
