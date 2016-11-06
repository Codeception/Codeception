<?php
namespace Codeception\Module;

use Codeception\Module;
use Codeception\TestInterface;
use Codeception\Exception\ModuleException;

/**
 * This module interacts with the [Alternative PHP Cache (APC)](http://php.net/manual/en/intro.apcu.php)
 * using either _APCu_ or _APC_ extension.
 *
 * Performs a cleanup by flushing all values after each test run.
 *
 * ## Status
 *
 * * Maintainer: **Serghei Iakovlev**
 * * Stability: **stable**
 * * Contact: serghei@phalconphp.com
 *
 * ### Example (`unit.suite.yml`)
 *
 * ```yaml
 *    modules:
 *        - Apc
 * ```
 *
 * Be sure you don't use the production server to connect.
 *
 */
class Apc extends Module
{
    /**
     * Code to run before each test.
     *
     * @param TestInterface $test
     * @throws ModuleException
     */
    public function _before(TestInterface $test)
    {
        if (!extension_loaded('apc') && !extension_loaded('apcu')) {
            throw new ModuleException(
                __CLASS__,
                'The APC(u) extension not loaded.'
            );
        }

        if (!ini_get('apc.enabled') || (PHP_SAPI === 'cli' && !ini_get('apc.enable_cli'))) {
            throw new ModuleException(
                __CLASS__,
                'The "apc.enable_cli" parameter must be set to "On".'
            );
        }
    }

    /**
     * Code to run after each test.
     *
     * @param TestInterface $test
     */
    public function _after(TestInterface $test)
    {
        $this->clear();
    }

    /**
     * Grabs value from APC(u) by key.
     *
     * Example:
     *
     * ``` php
     * <?php
     * $users_count = $I->grabValueFromApc('users_count');
     * ?>
     * ```
     *
     * @param string|string[] $key
     * @return mixed
     */
    public function grabValueFromApc($key)
    {
        $value = $this->fetch($key);
        $this->debugSection('Value', $value);

        return $value;
    }

    /**
     * Checks item in APC(u) exists and the same as expected.
     *
     * Examples:
     *
     * ``` php
     * <?php
     * // With only one argument, only checks the key exists
     * $I->seeInApc('users_count');
     *
     * // Checks a 'users_count' exists and has the value 200
     * $I->seeInApc('users_count', 200);
     * ?>
     * ```
     *
     * @param string|string[] $key
     * @param mixed $value
     */
    public function seeInApc($key, $value = null)
    {
        if (null === $value) {
            $this->assertTrue($this->exists($key), "Cannot find key '$key' in APC(u).");
            return;
        }

        $actual = $this->grabValueFromApc($key);
        $this->assertEquals($value, $actual, "Cannot find key '$key' in APC(u) with the provided value.");
    }

    /**
     * Checks item in APC(u) doesn't exist or is the same as expected.
     *
     * Examples:
     *
     * ``` php
     * <?php
     * // With only one argument, only checks the key does not exist
     * $I->dontSeeInApc('users_count');
     *
     * // Checks a 'users_count' exists does not exist or its value is not the one provided
     * $I->dontSeeInApc('users_count', 200);
     * ?>
     * ```
     *
     * @param string|string[] $key
     * @param mixed $value
     */
    public function dontSeeInApc($key, $value = null)
    {
        if (null === $value) {
            $this->assertFalse($this->exists($key), "The key '$key' exists in APC(u).");
            return;
        }

        $actual = $this->grabValueFromApc($key);
        if (false !== $actual) {
            $this->assertEquals($value, $actual, "The key '$key' exists in APC(u) with the provided value.");
        }
    }

    /**
     * Stores an item `$value` with `$key` on the APC(u).
     *
     * Examples:
     *
     * ```php
     * <?php
     * // Array
     * $I->haveInApc('users', ['name' => 'miles', 'email' => 'miles@davis.com']);
     *
     * // Object
     * $I->haveInApc('user', UserRepository::findFirst());
     *
     * // Key as array of 'key => value'
     * $entries = [];
     * $entries['key1'] = 'value1';
     * $entries['key2'] = 'value2';
     * $entries['key3'] = ['value3a','value3b'];
     * $entries['key4'] = 4;
     * $I->haveInApc($entries, null);
     * ?>
     * ```
     *
     * @param string|array $key
     * @param mixed $value
     * @param int $expiration
     * @return mixed
     */
    public function haveInApc($key, $value, $expiration = null)
    {
        $this->store($key, $value, $expiration);

        return $key;
    }

    /**
     * Clears the APC(u) cache
     */
    public function flushApc()
    {
        // Returns TRUE always
        $this->clear();
    }

    /**
     * Clears the APC(u) cache.
     *
     * @return bool
     */
    protected function clear()
    {
        if (function_exists('apcu_clear_cache')) {
            return apcu_clear_cache();
        }

        return apc_clear_cache('user');
    }

    /**
     * Checks if entry exists
     *
     * @param string|string[] $key
     *
     * @return bool|\string[]
     */
    protected function exists($key)
    {
        if (function_exists('apcu_exists')) {
            return apcu_exists($key);
        }

        return apc_exists($key);
    }

    /**
     * Fetch a stored variable from the cache
     *
     * @param string|string[] $key
     *
     * @return mixed
     */
    protected function fetch($key)
    {
        $success = false;

        if (function_exists('apcu_fetch')) {
            $data = apcu_fetch($key, $success);
        } else {
            $data = apc_fetch($key, $success);
        }

        $this->debugSection('Fetching a stored variable', $success ? 'OK' : 'FAILED');

        return $data;
    }

    /**
     * Cache a variable in the data store.
     *
     * @param string|array $key
     * @param mixed $var
     * @param int $ttl
     *
     * @return array|bool
     */
    protected function store($key, $var, $ttl = 0)
    {
        if (function_exists('apcu_store')) {
            return apcu_store($key, $var, $ttl);
        }

        return apc_store($key, $var, $ttl);
    }
}
