<?php

namespace Codeception\Module;

use Codeception\Lib\Interfaces\RequiresPackage;
use Codeception\Module as CodeceptionModule;
use Codeception\Exception\ModuleException;
use Codeception\TestInterface;
use Predis\Client as RedisDriver;

/**
 * This module uses the [Predis](https://github.com/nrk/predis) library
 * to interact with a Redis server.
 *
 * ## Status
 *
 * * Stability: **beta**
 *
 * ## Configuration
 *
 * * **`host`** (`string`, default `'127.0.0.1'`) - The Redis host
 * * **`port`** (`int`, default `6379`) - The Redis port
 * * **`database`** (`int`, no default) - The Redis database. Needs to be specified.
 * * **`cleanupBefore`**: (`string`, default `'never'`) - Whether/when to flush the database:
 *     * `suite`: at the beginning of every suite
 *     * `test`: at the beginning of every test
 *     * Any other value: never
 *
 * ### Example (`unit.suite.yml`)
 *
 * ```yaml
 *    modules:
 *        - Redis:
 *            host: '127.0.0.1'
 *            port: 6379
 *            database: 0
 *            cleanupBefore: 'never'
 * ```
 *
 * ## Public Properties
 *
 * * **driver** - Contains the Predis client/driver
 *
 * @author Marc Verney <marc@marcverney.net>
 */
class Redis extends CodeceptionModule implements RequiresPackage
{
    /**
     * {@inheritdoc}
     *
     * No default value is set for the database, using this parameter.
     */
    protected $config = [
        'host'          => '127.0.0.1',
        'port'          => 6379,
        'cleanupBefore' => 'never'
    ];

    /**
     * {@inheritdoc}
     */
    protected $requiredFields = [
        'database'
    ];

    /**
     * The Redis driver
     *
     * @var RedisDriver
     */
    public $driver;

    public function _requires()
    {
        return ['Predis\Client' => '"predis/predis": "^1.0"'];
    }

    /**
     * Instructions to run after configuration is loaded
     *
     * @throws ModuleException
     */
    public function _initialize()
    {
        try {
            $this->driver = new RedisDriver([
                'host'     => $this->config['host'],
                'port'     => $this->config['port'],
                'database' => $this->config['database']
            ]);
        } catch (\Exception $e) {
            throw new ModuleException(
                __CLASS__,
                $e->getMessage()
            );
        }
    }

    /**
     * Code to run before each suite
     *
     * @param array $settings
     */
    public function _beforeSuite($settings = [])
    {
        if ($this->config['cleanupBefore'] === 'suite') {
            $this->cleanup();
        }
    }

    /**
     * Code to run before each test
     *
     * @param TestInterface $test
     */
    public function _before(TestInterface $test)
    {
        if ($this->config['cleanupBefore'] === 'test') {
            $this->cleanup();
        }
    }

    /**
     * Delete all the keys in the Redis database
     *
     * @throws ModuleException
     */
    public function cleanup()
    {
        try {
            $this->driver->flushdb();
        } catch (\Exception $e) {
            throw new ModuleException(
                __CLASS__,
                $e->getMessage()
            );
        }
    }

    /**
     * Returns the value of a given key
     *
     * Examples:
     *
     * ``` php
     * <?php
     * // Strings
     * $I->grabFromRedis('string');
     *
     * // Lists: get all members
     * $I->grabFromRedis('example:list');
     *
     * // Lists: get a specific member
     * $I->grabFromRedis('example:list', 2);
     *
     * // Lists: get a range of elements
     * $I->grabFromRedis('example:list', 2, 4);
     *
     * // Sets: get all members
     * $I->grabFromRedis('example:set');
     *
     * // ZSets: get all members
     * $I->grabFromRedis('example:zset');
     *
     * // ZSets: get a range of members
     * $I->grabFromRedis('example:zset', 3, 12);
     *
     * // Hashes: get all fields of a key
     * $I->grabFromRedis('example:hash');
     *
     * // Hashes: get a specific field of a key
     * $I->grabFromRedis('example:hash', 'foo');
     * ```
     *
     * @param string $key The key name
     *
     * @return mixed
     *
     * @throws ModuleException if the key does not exist
     */
    public function grabFromRedis($key)
    {
        $args = func_get_args();

        switch ($this->driver->type($key)) {
            case 'none':
                throw new ModuleException(
                    $this,
                    "Cannot grab key \"$key\" as it does not exist"
                );
                break;

            case 'string':
                $reply = $this->driver->get($key);
                break;

            case 'list':
                if (count($args) === 2) {
                    $reply = $this->driver->lindex($key, $args[1]);
                } else {
                    $reply = $this->driver->lrange(
                        $key,
                        isset($args[1]) ? $args[1] : 0,
                        isset($args[2]) ? $args[2] : -1
                    );
                }
                break;

            case 'set':
                $reply = $this->driver->smembers($key);
                break;

            case 'zset':
                if (count($args) === 2) {
                    throw new ModuleException(
                        $this,
                        "The method grabFromRedis(), when used with sorted "
                        . "sets, expects either one argument or three"
                    );
                }
                $reply = $this->driver->zrange(
                    $key,
                    isset($args[2]) ? $args[1] : 0,
                    isset($args[2]) ? $args[2] : -1,
                    'WITHSCORES'
                );
                break;

            case 'hash':
                $reply = isset($args[1])
                    ? $this->driver->hget($key, $args[1])
                    : $this->driver->hgetall($key);
                break;

            default:
                $reply = null;
        }

        return $reply;
    }

    /**
     * Creates or modifies keys
     *
     * If $key already exists:
     *
     * - Strings: its value will be overwritten with $value
     * - Other types: $value items will be appended to its value
     *
     * Examples:
     *
     * ``` php
     * <?php
     * // Strings: $value must be a scalar
     * $I->haveInRedis('string', 'Obladi Oblada');
     *
     * // Lists: $value can be a scalar or an array
     * $I->haveInRedis('list', ['riri', 'fifi', 'loulou']);
     *
     * // Sets: $value can be a scalar or an array
     * $I->haveInRedis('set', ['riri', 'fifi', 'loulou']);
     *
     * // ZSets: $value must be an associative array with scores
     * $I->haveInRedis('zset', ['riri' => 1, 'fifi' => 2, 'loulou' => 3]);
     *
     * // Hashes: $value must be an associative array
     * $I->haveInRedis('hash', ['obladi' => 'oblada']);
     * ```
     *
     * @param string $type  The type of the key
     * @param string $key   The key name
     * @param mixed  $value The value
     *
     * @throws ModuleException
     */
    public function haveInRedis($type, $key, $value)
    {
        switch (strtolower($type)) {
            case 'string':
                if (!is_scalar($value)) {
                    throw new ModuleException(
                        $this,
                        'If second argument of haveInRedis() method is "string", '
                        . 'third argument must be a scalar'
                    );
                }
                $this->driver->set($key, $value);
                break;

            case 'list':
                $this->driver->rpush($key, $value);
                break;

            case 'set':
                $this->driver->sadd($key, $value);
                break;

            case 'zset':
                if (!is_array($value)) {
                    throw new ModuleException(
                        $this,
                        'If second argument of haveInRedis() method is "zset", '
                        . 'third argument must be an (associative) array'
                    );
                }
                $this->driver->zadd($key, $value);
                break;

            case 'hash':
                if (!is_array($value)) {
                    throw new ModuleException(
                        $this,
                        'If second argument of haveInRedis() method is "hash", '
                        . 'third argument must be an array'
                    );
                }
                $this->driver->hmset($key, $value);
                break;

            default:
                throw new ModuleException(
                    $this,
                    "Unknown type \"$type\" for key \"$key\". Allowed types are "
                    . '"string", "list", "set", "zset", "hash"'
                );
        }
    }

    /**
     * Asserts that a key does not exist or, optionaly, that it doesn't have the
     * provided $value
     *
     * Examples:
     *
     * ``` php
     * <?php
     * // With only one argument, only checks the key does not exist
     * $I->dontSeeInRedis('example:string');
     *
     * // Checks a String does not exist or its value is not the one provided
     * $I->dontSeeInRedis('example:string', 'life');
     *
     * // Checks a List does not exist or its value is not the one provided (order of elements is compared).
     * $I->dontSeeInRedis('example:list', ['riri', 'fifi', 'loulou']);
     *
     * // Checks a Set does not exist or its value is not the one provided (order of members is ignored).
     * $I->dontSeeInRedis('example:set', ['riri', 'fifi', 'loulou']);
     *
     * // Checks a ZSet does not exist or its value is not the one provided (scores are required, order of members is compared)
     * $I->dontSeeInRedis('example:zset', ['riri' => 1, 'fifi' => 2, 'loulou' => 3]);
     *
     * // Checks a Hash does not exist or its value is not the one provided (order of members is ignored).
     * $I->dontSeeInRedis('example:hash', ['riri' => true, 'fifi' => 'Dewey', 'loulou' => 2]);
     * ```
     *
     * @param string $key   The key name
     * @param mixed  $value Optional. If specified, also checks the key has this
     * value. Booleans will be converted to 1 and 0 (even inside arrays)
     */
    public function dontSeeInRedis($key, $value = null)
    {
        $this->assertFalse(
            (bool) $this->checkKeyExists($key, $value),
            "The key \"$key\" exists" . ($value ? ' and its value matches the one provided' : '')
        );
    }

    /**
     * Asserts that a given key does not contain a given item
     *
     * Examples:
     *
     * ``` php
     * <?php
     * // Strings: performs a substring search
     * $I->dontSeeRedisKeyContains('string', 'bar');
     *
     * // Lists
     * $I->dontSeeRedisKeyContains('example:list', 'poney');
     *
     * // Sets
     * $I->dontSeeRedisKeyContains('example:set', 'cat');
     *
     * // ZSets: check whether the zset has this member
     * $I->dontSeeRedisKeyContains('example:zset', 'jordan');
     *
     * // ZSets: check whether the zset has this member with this score
     * $I->dontSeeRedisKeyContains('example:zset', 'jordan', 23);
     *
     * // Hashes: check whether the hash has this field
     * $I->dontSeeRedisKeyContains('example:hash', 'magic');
     *
     * // Hashes: check whether the hash has this field with this value
     * $I->dontSeeRedisKeyContains('example:hash', 'magic', 32);
     * ```
     *
     * @param string $key       The key
     * @param mixed  $item      The item
     * @param null   $itemValue Optional and only used for zsets and hashes. If
     * specified, the method will also check that the $item has this value/score
     *
     * @return bool
     */
    public function dontSeeRedisKeyContains($key, $item, $itemValue = null)
    {
        $this->assertFalse(
            (bool) $this->checkKeyContains($key, $item, $itemValue),
            "The key \"$key\" contains " . (
                is_null($itemValue)
                ? "\"$item\""
                : "[\"$item\" => \"$itemValue\"]"
            )
        );
    }

    /**
     * Asserts that a key exists, and optionally that it has the provided $value
     *
     * Examples:
     *
     * ``` php
     * <?php
     * // With only one argument, only checks the key exists
     * $I->seeInRedis('example:string');
     *
     * // Checks a String exists and has the value "life"
     * $I->seeInRedis('example:string', 'life');
     *
     * // Checks the value of a List. Order of elements is compared.
     * $I->seeInRedis('example:list', ['riri', 'fifi', 'loulou']);
     *
     * // Checks the value of a Set. Order of members is ignored.
     * $I->seeInRedis('example:set', ['riri', 'fifi', 'loulou']);
     *
     * // Checks the value of a ZSet. Scores are required. Order of members is compared.
     * $I->seeInRedis('example:zset', ['riri' => 1, 'fifi' => 2, 'loulou' => 3]);
     *
     * // Checks the value of a Hash. Order of members is ignored.
     * $I->seeInRedis('example:hash', ['riri' => true, 'fifi' => 'Dewey', 'loulou' => 2]);
     * ```
     *
     * @param string $key   The key name
     * @param mixed  $value Optional. If specified, also checks the key has this
     * value. Booleans will be converted to 1 and 0 (even inside arrays)
     */
    public function seeInRedis($key, $value = null)
    {
        $this->assertTrue(
            (bool) $this->checkKeyExists($key, $value),
            "Cannot find key \"$key\"" . ($value ? ' with the provided value' : '')
        );
    }

    /**
     * Sends a command directly to the Redis driver. See documentation at
     * https://github.com/nrk/predis
     * Every argument that follows the $command name will be passed to it.
     *
     * Examples:
     *
     * ``` php
     * <?php
     * $I->sendCommandToRedis('incr', 'example:string');
     * $I->sendCommandToRedis('strLen', 'example:string');
     * $I->sendCommandToRedis('lPop', 'example:list');
     * $I->sendCommandToRedis('zRangeByScore', 'example:set', '-inf', '+inf', ['withscores' => true, 'limit' => [1, 2]]);
     * $I->sendCommandToRedis('flushdb');
     * ```
     *
     * @param string $command The command name
     *
     * @return mixed
     */
    public function sendCommandToRedis($command)
    {
        return call_user_func_array(
            [$this->driver, $command],
            array_slice(func_get_args(), 1)
        );
    }

    /**
     * Asserts that a given key contains a given item
     *
     * Examples:
     *
     * ``` php
     * <?php
     * // Strings: performs a substring search
     * $I->seeRedisKeyContains('example:string', 'bar');
     *
     * // Lists
     * $I->seeRedisKeyContains('example:list', 'poney');
     *
     * // Sets
     * $I->seeRedisKeyContains('example:set', 'cat');
     *
     * // ZSets: check whether the zset has this member
     * $I->seeRedisKeyContains('example:zset', 'jordan');
     *
     * // ZSets: check whether the zset has this member with this score
     * $I->seeRedisKeyContains('example:zset', 'jordan', 23);
     *
     * // Hashes: check whether the hash has this field
     * $I->seeRedisKeyContains('example:hash', 'magic');
     *
     * // Hashes: check whether the hash has this field with this value
     * $I->seeRedisKeyContains('example:hash', 'magic', 32);
     * ```
     *
     * @param string $key       The key
     * @param mixed  $item      The item
     * @param null   $itemValue Optional and only used for zsets and hashes. If
     * specified, the method will also check that the $item has this value/score
     *
     * @return bool
     */
    public function seeRedisKeyContains($key, $item, $itemValue = null)
    {
        $this->assertTrue(
            (bool) $this->checkKeyContains($key, $item, $itemValue),
            "The key \"$key\" does not contain " . (
            is_null($itemValue)
                ? "\"$item\""
                : "[\"$item\" => \"$itemValue\"]"
            )
        );
    }

    /**
     * Converts boolean values to "0" and "1"
     *
     * @param mixed $var The variable
     *
     * @return mixed
     */
    private function boolToString($var)
    {
        $copy = is_array($var) ? $var : [$var];

        foreach ($copy as $key => $value) {
            if (is_bool($value)) {
                $copy[$key] = $value ? '1' : '0';
            }
        }

        return is_array($var) ? $copy : $copy[0];
    }

    /**
     * Checks whether a key contains a given item
     *
     * @param string $key       The key
     * @param mixed  $item      The item
     * @param null   $itemValue Optional and only used for zsets and hashes. If
     * specified, the method will also check that the $item has this value/score
     *
     * @return bool
     *
     * @throws ModuleException
     */
    private function checkKeyContains($key, $item, $itemValue = null)
    {
        $result = null;

        if (!is_scalar($item)) {
            throw new ModuleException(
                $this,
                "All arguments of [dont]seeRedisKeyContains() must be scalars"
            );
        }

        switch ($this->driver->type($key)) {
            case 'string':
                $reply = $this->driver->get($key);
                $result = strpos($reply, $item) !== false;
                break;

            case 'list':
                $reply = $this->driver->lrange($key, 0, -1);
                $result = in_array($item, $reply);
                break;

            case 'set':
                $result = $this->driver->sismember($key, $item);
                break;

            case 'zset':
                $reply = $this->driver->zscore($key, $item);

                if (is_null($reply)) {
                    $result = false;
                } elseif (!is_null($itemValue)) {
                    $result = (float) $reply === (float) $itemValue;
                } else {
                    $result = true;
                }
                break;

            case 'hash':
                $reply = $this->driver->hget($key, $item);

                $result = is_null($itemValue)
                    ? !is_null($reply)
                    : (string) $reply === (string) $itemValue;
                break;

            case 'none':
                throw new ModuleException(
                    $this,
                    "Key \"$key\" does not exist"
                );
                break;
        }

        return $result;
    }

    /**
     * Checks whether a key exists and, optionally, whether it has a given $value
     *
     * @param string $key   The key name
     * @param mixed  $value Optional. If specified, also checks the key has this
     * value. Booleans will be converted to 1 and 0 (even inside arrays)
     *
     * @return bool
     */
    private function checkKeyExists($key, $value = null)
    {
        $type = $this->driver->type($key);

        if (is_null($value)) {
            return $type != 'none';
        }

        $value = $this->boolToString($value);

        switch ($type) {
            case 'string':
                $reply = $this->driver->get($key);
                // Allow non strict equality (2 equals '2')
                $result = $reply == $value;
                break;

            case 'list':
                $reply = $this->driver->lrange($key, 0, -1);
                // Check both arrays have the same key/value pairs + same order
                $result = $reply === $value;
                break;

            case 'set':
                $reply = $this->driver->smembers($key);
                // Only check both arrays have the same values
                sort($reply);
                sort($value);
                $result = $reply === $value;
                break;

            case 'zset':
                $reply = $this->driver->zrange($key, 0, -1, 'WITHSCORES');
                // Check both arrays have the same key/value pairs + same order
                $reply = $this->scoresToFloat($reply);
                $value = $this->scoresToFloat($value);
                $result = $reply === $value;
                break;

            case 'hash':
                $reply = $this->driver->hgetall($key);
                // Only check both arrays have the same key/value pairs (==)
                $result = $reply == $value;
                break;

            default:
                $result = false;
        }

        return $result;
    }

    /**
     * Explicitly cast the scores of a Zset associative array as float/double
     *
     * @param array $arr The ZSet associative array
     *
     * @return array
     */
    private function scoresToFloat(array $arr)
    {
        foreach ($arr as $member => $score) {
            $arr[$member] = (float) $score;
        }

        return $arr;
    }
}
