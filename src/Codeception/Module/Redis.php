<?php

namespace Codeception\Module;

use Codeception\Module as CodeceptionModule;
use Codeception\TestCase;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Driver\Credis_Client as RedisDriver;
use Codeception\TestInterface;

/**
 * This module uses the [Credis](https://github.com/colinmollenhour/credis) client
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
 * * **`database`** (`int`, no default) - The Redis database. Needs to be explicitely specified.
 * * **`cleanupBefore`**: (`string`, default `'suite'`) - Whether/when to flush the database:
 *     * `suite`: at the beginning of every suite
 *     * `test`: at the beginning of every test
 *     * Any other value: never
 *
 * ## Public Properties
 * * **driver** - Contains the Credis client/driver
 *
 * @author Marc Verney <marc@marcverney.net>
 */
class Redis extends CodeceptionModule
{
    /**
     * {@inheritdoc}
     *
     * No default value is set for the database, as this module will delete
     * every data in it. The user is required to explicitely set this parameter.
     */
    protected $config = array(
        'host'          => '127.0.0.1',
        'port'          => 6379,
        'cleanupBefore' => 'suite'
    );

    /**
     * {@inheritdoc}
     */
    protected $requiredFields = array(
        'database'
    );

    /**
     * The Redis driver
     *
     * @var RedisDriver
     */
    public $driver;

    /**
     * Instructions to run after configuration is loaded
     *
     * @throws ModuleException
     */
    public function _initialize()
    {
        try {
            $this->driver = new RedisDriver(
                $this->config['host'],
                $this->config['port'],
                null,
                '',
                $this->config['database']
            );
        } catch (\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage());
        }
    }

    /**
     * Code to run before each suite
     *
     * @param array $settings
     */
    public function _beforeSuite($settings = array())
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
            $this->driver->flushDb();
        } catch (\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage());
        }
    }

    /**
     * Returns the value of a given key
     *
     * Examples:
     *
     * ```php?start_inline=1
     * // Strings
     * $I->grabFromRedis('example:string')
     * // Lists: get all members
     * $I->grabFromRedis('example:list')
     * // Lists: get a specific member
     * $I->grabFromRedis('example:list', 2)
     * // Lists: get a range of elements
     * $I->grabFromRedis('example:list', 2, 4)
     * // Sets: get all members
     * $I->grabFromRedis('example:set')
     * // ZSets: get all members
     * $I->grabFromRedis('example:zset')
     * // ZSets: get a range of members
     * $I->grabFromRedis('example:zset', 3, 12)
     * // Hashes: get all fields of a key
     * $I->grabFromRedis('example:hash')
     * // Hashes: get a specific field of a key
     * $I->grabFromRedis('example:hash', 'foo')
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
            case RedisDriver::TYPE_NONE:
                throw new ModuleException(
                    $this,
                    "Cannot grab key \"$key\" as it does not exist"
                );
                break;

            case RedisDriver::TYPE_STRING:
                $reply = $this->driver->get($key);
                break;

            case RedisDriver::TYPE_LIST:
                if (count($args) === 2) {
                    $reply = $this->driver->lIndex($key, $args[1]);
                } else {
                    $reply = $this->driver->lRange(
                        $key,
                        isset($args[1]) ? $args[1] : 0,
                        isset($args[2]) ? $args[2] : -1
                    );
                }
                break;

            case RedisDriver::TYPE_SET:
                $reply = $this->driver->sMembers($key);
                break;

            case RedisDriver::TYPE_ZSET:
                if (count($args) === 2) {
                    throw new ModuleException(
                        $this,
                        "The method grabFromRedis(), when used with sorted "
                        . "sets, expects either one argument or three"
                    );
                }
                $reply = $this->driver->zRange(
                    $key,
                    isset($args[2]) ? $args[1] : 0,
                    isset($args[2]) ? $args[2] : -1,
                    true
                );
                break;

            case RedisDriver::TYPE_HASH:
                $reply = isset($args[1])
                    ? $this->driver->hGet($key, $args[1])
                    : $this->driver->hGetAll($key);
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
     * ```php?start_inline=1
     * // Strings: $value must be a scalar
     * $I->haveInRedis('example:string', 'Obladi Oblada')
     * // Lists: $value can be a scalar or an array
     * $I->haveInRedis('example:list', ['riri', 'fifi', 'loulou'])
     * // Sets: $value can be a scalar or an array
     * $I->haveInRedis('example:set', ['riri', 'fifi', 'loulou'])
     * // ZSets: $value must be an associative array with scores
     * $I->haveInRedis('example:set', ['riri' => 1, 'fifi' => 2, 'loulou' => 3])
     * // Hashes: $value must be an associative array
     * $I->haveInRedis('example:hash', ['obladi' => 'oblada'])
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
            case RedisDriver::TYPE_STRING:
                if (!is_scalar($value)) {
                    throw new ModuleException($this,
                        "If second argument of haveInRedis() method is "
                        . "\"" . RedisDriver::TYPE_STRING . "\", third argument "
                        . "must be a scalar");
                }
                $this->driver->set($key, $value);
                break;

            case RedisDriver::TYPE_LIST:
                $this->driver->rPush($key, $value);
                break;

            case RedisDriver::TYPE_SET:
                $this->driver->sAdd($key, $value);
                break;

            case RedisDriver::TYPE_ZSET:
                if (!is_array($value)) {
                    throw new ModuleException($this,
                        "If second argument of haveInRedis() method is "
                        . "\"" . RedisDriver::TYPE_ZSET . "\", third argument "
                        . "must be an (associative) array");
                }
                $this->driver->zAdd($key, $this->zsetAssocToSimple($value));
                break;

            case RedisDriver::TYPE_HASH:
                if (!is_array($value)) {
                    throw new ModuleException($this,
                        "If second argument of haveInRedis() method is "
                        . "\"" . RedisDriver::TYPE_HASH . "\", third argument "
                        . "must be an array");
                }
                $this->driver->hMSet($key, $value);
                break;

            default:
                throw new ModuleException($this, "Unknown type \"$type\" for key "
                    . "\"$key\". Allowed types are "
                    . RedisDriver::TYPE_STRING . ', '
                    . RedisDriver::TYPE_LIST   . ', '
                    . RedisDriver::TYPE_SET    . ', '
                    . RedisDriver::TYPE_ZSET   . ', '
                    . RedisDriver::TYPE_HASH
                );
        }
    }

    /**
     * Asserts that a key does not exist or, optionaly, that it doesn't have the
     * provided $value
     *
     * Examples:
     *
     * ```php?start_inline=1
     * // With only one argument, only checks the key does not exist
     * $I->dontSeeInRedis('example:string');
     * // Checks a String does not exist or its value is not the one provided
     * $I->dontSeeInRedis('example:string', 'life');
     * // Checks a List does not exist or its value is not the one provided (order of elements is compared).
     * $I->dontSeeInRedis('example:list', ['riri', 'fifi', 'loulou'])
     * // Checks a Set does not exist or its value is not the one provided (order of members is ignored).
     * $I->dontSeeInRedis('example:set', ['riri', 'fifi', 'loulou'])
     * // Checks a ZSet does not exist or its value is not the one provided (scores are required, order of members is compared)
     * $I->dontSeeInRedis('example:zset', ['riri' => 1, 'fifi' => 2, 'loulou' => 3])
     * // Checks a Hash does not exist or its value is not the one provided (order of members is ignored).
     * $I->dontSeeInRedis('example:hash', ['riri' => true, 'fifi' => 'Dewey', 'loulou' => 2])
     * ```
     *
     * @param string $key   The key name
     * @param mixed  $value Optional. If specified, also checks the key has this
     * value. Booleans will be converted to 1 and 0 (even inside arrays)
     */
    public function dontSeeInRedis($key, $value = null)
    {
        $this->assertFalse(
            $this->checkKeyExists($key, $value)
        );
    }

    /**
     * Asserts that a given key does not contain a given item
     *
     * Examples:
     *
     * ```php?start_inline=1
     * // Strings: performs a substring search
     * $I->dontSeeRedisKeyContains('example:string', 'bar') // true for foobar
     * // Lists
     * $I->dontSeeRedisKeyContains('example:list', 'poney')
     * // Sets
     * $I->dontSeeRedisKeyContains('example:set', 'cat')
     * // ZSets: check whether the zset has this member
     * $I->dontSeeRedisKeyContains('example:zset', 'jordan')
     * // ZSets: check whether the zset has this member with this score
     * $I->dontSeeRedisKeyContains('example:zset', 'jordan', 23)
     * // Hashes: check whether the hash has this field
     * $I->dontSeeRedisKeyContains('example:hash', 'magic')
     * // Hashes: check whether the hash has this field with this value
     * $I->dontSeeRedisKeyContains('example:hash', 'magic', 32)
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
        $this->assertSame(
            false,
            $this->checkKeyContains($key, $item, $itemValue)
        );
    }

    /**
     * Asserts that a key exists, and optionaly that it has the provided $value
     *
     * Examples:
     *
     * ```php?start_inline=1
     * // With only one argument, only checks the key exists
     * $I->seeInRedis('example:string');
     * // Checks a String exists and has the value "life"
     * $I->seeInRedis('example:string', 'life');
     * // Checks the value of a List. Order of elements is compared.
     * $I->seeInRedis('example:list', ['riri', 'fifi', 'loulou'])
     * // Checks the value of a Set. Order of members is ignored.
     * $I->seeInRedis('example:set', ['riri', 'fifi', 'loulou'])
     * // Checks the value of a ZSet. Scores are required. Order of members is compared.
     * $I->seeInRedis('example:zset', ['riri' => 1, 'fifi' => 2, 'loulou' => 3])
     * // Checks the value of a Hash. Order of members is ignored.
     * $I->seeInRedis('example:hash', ['riri' => true, 'fifi' => 'Dewey', 'loulou' => 2])
     * ```
     *
     * @param string $key   The key name
     * @param mixed  $value Optional. If specified, also checks the key has this
     * value. Booleans will be converted to 1 and 0 (even inside arrays)
     */
    public function seeInRedis($key, $value = null)
    {
        $this->assertTrue(
            $this->checkKeyExists($key, $value)
        );
    }

    /**
     * Sends a command directly to the Redis driver. See documentation at
     * https://github.com/colinmollenhour/credis
     * Every argument that follows the $command name will be passed to it
     *
     * Examples:
     *
     * ```php?start_inline=1
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
            array($this->driver, $command),
            array_slice(func_get_args(), 1)
        );
    }

    /**
     * Asserts that a given key contains a given item
     *
     * Examples:
     *
     * ```php?start_inline=1
     * // Strings: performs a substring search
     * $I->seeRedisKeyContains('example:string', 'bar') // true for foobar
     * // Lists
     * $I->seeRedisKeyContains('example:list', 'poney')
     * // Sets
     * $I->seeRedisKeyContains('example:set', 'cat')
     * // ZSets: check whether the zset has this member
     * $I->seeRedisKeyContains('example:zset', 'jordan')
     * // ZSets: check whether the zset has this member with this score
     * $I->seeRedisKeyContains('example:zset', 'jordan', 23)
     * // Hashes: check whether the hash has this field
     * $I->seeRedisKeyContains('example:hash', 'magic')
     * // Hashes: check whether the hash has this field with this value
     * $I->seeRedisKeyContains('example:hash', 'magic', 32)
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
        $this->assertSame(
            true,
            $this->checkKeyContains($key, $item, $itemValue)
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
        $copy = is_array($var) ? $var : array($var);

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
            throw new ModuleException($this,
                "All arguments of [dont]seeRedisKeyContains() must be scalars"
            );
        }

        switch ($this->driver->type($key)) {
            case RedisDriver::TYPE_STRING:
                $reply = $this->driver->get($key);
                $result = strpos($reply, $item) !== false;
                break;

            case RedisDriver::TYPE_LIST:
                $reply = $this->driver->lRange($key, 0, -1);
                $result = in_array($item, $reply);
                break;

            case RedisDriver::TYPE_SET:
                $result = $this->driver->sIsMember($key, $item);
                break;

            case RedisDriver::TYPE_ZSET:
                $reply = $this->driver->zScore($key, $item);
                if ($reply === false) {
                    $result = false;
                } elseif (!is_null($itemValue)) {
                    $result = (int) $reply === (int) $itemValue;
                } else {
                    $result = true;
                }
                break;

            case RedisDriver::TYPE_HASH:
                $reply = $this->driver->hGet($key, $item);
                $result = is_null($itemValue)
                    ? $reply !== false
                    : (string) $reply === (string) $itemValue;
                break;

            case RedisDriver::TYPE_NONE:
                throw new ModuleException($this, "Key \"$key\" does not exist");
                break;
        }

        return $result;
    }

    /**
     * Checks whether a key exists and, optionaly, whether it has a given $value
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
            return $type != RedisDriver::TYPE_NONE;
        }

        $value = $this->boolToString($value);

        switch ($type) {
            case RedisDriver::TYPE_STRING:
                $reply = $this->driver->get($key);
                // Allow non strict equality (2 equals '2')
                $result = $reply == $value;
                break;

            case RedisDriver::TYPE_LIST:
                $reply = $this->driver->lRange($key, 0, -1);
                // Check both arrays have the same key/value pairs + same order
                $result = $reply === $value;
                break;

            case RedisDriver::TYPE_SET:
                $reply = $this->driver->sMembers($key);
                // Only check both arrays have the same values
                sort($reply);
                sort($value);
                $result = $reply === $value;
                break;

            case RedisDriver::TYPE_ZSET:
                $reply = $this->driver->zRange($key, 0, -1, true);
                // Check both arrays have the same key/value pairs + same order
                $value = $this->scoresToFloat($value);
                $result = $reply === $value;
                break;

            case RedisDriver::TYPE_HASH:
                $reply = $this->driver->hGetAll($key);
                // Only check both arrays have the same key/value pairs (==)
                $result = $reply == $value;
                break;

            default:
                $result = false;
        }

        return $result;
    }

    /**
     * Explicitely cast the scores of a Zset associative array as float/double
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

    /**
     * Converts an associative array respresenting a zset to a format that the
     * Redis driver will accept as input for zAdd()
     *
     * @param array $arr The associative array
     *
     * @return array
     */
    private function zsetAssocToSimple(array $arr)
    {
        $result = array();

        foreach ($arr as $key => $value) {
            $result[] = $value;
            $result[] = $key;
        }

        return $result;

    }
}
