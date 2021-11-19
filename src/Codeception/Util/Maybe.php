<?php

declare(strict_types=1);

namespace Codeception\Util;

use ArrayAccess;
use Iterator;
use JsonSerializable;
use function array_keys;
use function call_user_func_array;
use function count;
use function is_array;
use function is_object;
use function property_exists;
use function range;

/**
 * Class to represent any type of content.
 * This class can act as an object, array, or string.
 * Method or property calls to this class won't cause any errors.
 *
 * Maybe was used in Codeception 1.x to represent data on parsing step.
 * Not widely used in 2.0 anymore, but left for compatibility.
 *
 * For instance, you may use `Codeception\Util\Maybe` as a test dummies.
 *
 * ```php
 * <?php
 * $user = new Maybe;
 * $user->posts->comments->count();
 * ```
 */
class Maybe implements ArrayAccess, Iterator, JsonSerializable
{
    protected int $position = 0;

    /**
     * @var mixed
     */
    protected $val = null;

    protected ?bool $assocArray = null;

    /**
     * @param mixed $val
     */
    public function __construct($val = null)
    {
        $this->val = $val;
        if (is_array($this->val)) {
            $this->assocArray = $this->isAssocArray($this->val);
        }
        $this->position = 0;
    }

    private function isAssocArray(array $arr): bool
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public function __toString(): string
    {
        if ($this->val === null) {
            return '?';
        }

        return (string)$this->val;
    }

    public function __get(string $key): Maybe
    {
        if ($this->val === null) {
            return new Maybe();
        }

        if (is_object($this->val) && (isset($this->val->{$key}) || property_exists($this->val, $key))) {
            return $this->val->{$key};
        }

        return $this->val->key;
    }

    public function __set(string $key, $val)
    {
        if ($this->val === null) {
            return;
        }

        if (is_object($this->val)) {
            $this->val->{$key} = $val;
            return;
        }

        $this->val->key = $val;
    }

    public function __call(string $method, array $args)
    {
        if ($this->val === null) {
            return new Maybe();
        }
        return call_user_func_array([$this->val, $method], $args);
    }

    public function __clone()
    {
        if (is_object($this->val)) {
            $this->val = clone $this->val;
        }
    }

    public function __unset(string $key)
    {
        if (is_object($this->val) && (isset($this->val->{$key}) || property_exists($this->val, $key))) {
            unset($this->val->{$key});
        }
    }

    public function offsetExists($offset): bool
    {
        if (is_array($this->val) || ($this->val instanceof ArrayAccess)) {
            return isset($this->val[$offset]);
        }
        return false;
    }

    public function offsetGet($offset): Maybe
    {
        if (is_array($this->val) || $this->val instanceof ArrayAccess) {
            return $this->val[$offset];
        }
        return new Maybe();
    }

    public function offsetSet($offset, $value): void
    {
        if (is_array($this->val) || ($this->val instanceof ArrayAccess)) {
            $this->val[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        if (is_array($this->val) || ($this->val instanceof ArrayAccess)) {
            unset($this->val[$offset]);
        }
    }

    public function value()
    {
        $val = $this->val;
        if (is_array($val)) {
            foreach ($val as $k => $v) {
                if ($v instanceof self) {
                    $v = $v->value();
                }
                $val[$k] = $v;
            }
        }
        return $val;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return null|mixed Can return any type.
     */
    public function current()
    {
        if (!is_array($this->val)) {
            return null;
        }
        if ($this->assocArray) {
            $keys = array_keys($this->val);
            return $this->val[$keys[$this->position]];
        }

        return $this->val[$this->position];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return int|string|null scalar on success, or null on failure.
     */
    public function key()
    {
        if ($this->assocArray) {
            $keys = array_keys($this->val);
            return $keys[$this->position];
        }

        return $this->position;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid(): ?bool
    {
        if (!is_array($this->val)) {
            return null;
        }
        if ($this->assocArray) {
            $keys = array_keys($this->val);
            return isset($keys[$this->position]);
        }

        return isset($this->val[$this->position]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind(): void
    {
        if (is_array($this->val)) {
            $this->assocArray = $this->isAssocArray($this->val);
        }
        $this->position = 0;
    }

    /**
     * (PHP 5 >= 5.4.0)
     * Serializes the object to a value that can be serialized natively by json_encode().
     * @link http://docs.php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed Returns data which can be serialized by json_encode(), which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return $this->value();
    }
}
