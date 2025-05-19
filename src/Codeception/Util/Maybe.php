<?php

declare(strict_types=1);

namespace Codeception\Util;

use ArrayAccess;
use Iterator;
use JsonSerializable;
use Stringable;

use function array_keys;
use function call_user_func_array;
use function count;
use function is_array;
use function is_object;
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
class Maybe implements ArrayAccess, Iterator, JsonSerializable, Stringable
{
    protected int $position = 0;
    protected mixed $val = null;
    protected ?bool $assocArray = null;
    private array $keys = [];

    public function __construct(mixed $val = null)
    {
        $this->set($val);
    }

    private function set(mixed $val): void
    {
        $this->val = $val;
        if (is_array($val)) {
            $this->assocArray = $this->isAssocArray($val);
            $this->keys = array_keys($val);
        } else {
            $this->assocArray = null;
            $this->keys = [];
        }
        $this->position = 0;
    }

    private function isAssocArray(array $arr): bool
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public function __toString(): string
    {
        return $this->val === null ? '?' : (string)$this->val;
    }

    public function __get(string $key): Maybe
    {
        return new self(
            is_object($this->val)
                ? ($this->val->{$key} ?? null)
                : (is_array($this->val) ? ($this->val[$key] ?? null) : null)
        );
    }

    public function __set(string $key, $val)
    {
        if (is_object($this->val)) {
            $this->val->{$key} = $val;
        } elseif (is_array($this->val)) {
            $this->val[$key] = $val;
            $this->set($this->val);
        }
    }

    public function __call(string $method, array $args)
    {
        return $this->val === null ? new self() : call_user_func_array([$this->val, $method], $args);
    }

    public function __clone()
    {
        if (is_object($this->val)) {
            $this->val = clone $this->val;
        }
    }

    public function __unset(string $key)
    {
        if (is_object($this->val)) {
            unset($this->val->{$key});
        } elseif (is_array($this->val)) {
            unset($this->val[$key]);
            $this->set($this->val);
        }
    }

    public function offsetExists(mixed $offset): bool
    {
        return (is_array($this->val) || $this->val instanceof ArrayAccess) && isset($this->val[$offset]);
    }

    public function offsetGet(mixed $offset): Maybe
    {
        return new self(
            (is_array($this->val) || $this->val instanceof ArrayAccess) ? ($this->val[$offset] ?? null) : null
        );
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_array($this->val) || $this->val instanceof ArrayAccess) {
            $this->val[$offset] = $value;
            if (is_array($this->val)) {
                $this->set($this->val);
            }
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        if (is_array($this->val) || $this->val instanceof ArrayAccess) {
            unset($this->val[$offset]);
            if (is_array($this->val)) {
                $this->set($this->val);
            }
        }
    }

    public function value()
    {
        if (!is_array($this->val)) {
            return $this->val;
        }

        return array_map(fn($v) => $v instanceof self ? $v->value() : $v, $this->val);
    }

    /**
     * Return the current element
     * @link https://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current(): mixed
    {
        if (!is_array($this->val)) {
            return null;
        }
        $key = $this->assocArray === true ? ($this->keys[$this->position] ?? null) : $this->position;
        return $this->val[$key] ?? null;
    }

    /**
     * Move forward to next element
     * @link https://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * Return the key of the current element
     * @link https://php.net/manual/en/iterator.key.php
     * @return int|string|null scalar on success, or null on failure.
     */
    public function key(): mixed
    {
        return $this->assocArray === true ? ($this->keys[$this->position] ?? null) : $this->position;
    }

    /**
     * Checks if current position is valid
     * @link https://php.net/manual/en/iterator.valid.php
     * @return bool The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid(): bool
    {
        if (!is_array($this->val)) {
            return false;
        }
        return $this->assocArray === true ? isset($this->keys[$this->position]) : isset($this->val[$this->position]);
    }

    /**
     * Rewind the Iterator to the first element
     * @link https://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind(): void
    {
        if (is_array($this->val)) {
            $this->assocArray = $this->isAssocArray($this->val);
            $this->keys = array_keys($this->val);
        }
        $this->position = 0;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by json_encode(),
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize(): mixed
    {
        return $this->value();
    }
}
