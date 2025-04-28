<?php

declare(strict_types=1);

namespace Codeception;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use PHPUnit\Framework\AssertionFailedError;
use Traversable;

class Example implements ArrayAccess, Countable, IteratorAggregate
{
    public function __construct(protected $data)
    {
    }

    /**
     * Whether an offset exists
     *
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * Offset to retrieve
     *
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>The offset to retrieve.</p>
     * @return mixed Can return all value types.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return array_key_exists($offset, $this->data)
            ? $this->data[$offset]
            : throw new AssertionFailedError(sprintf("Example %s doesn't exist", $offset));
    }

    /**
     * Offset to set
     *
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>The offset to assign the value to.</p>
     * @param mixed $value <p>The value to set.</p>
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->data[$offset] = $value;
    }

    /**
     * Offset to unset
     *
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>The offset to unset.</p>
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }

    /**
     * Count elements of an object
     *
     * @link https://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * The return value is cast to an integer.
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Retrieve an external iterator
     *
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or <b>Traversable</b>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }
}
