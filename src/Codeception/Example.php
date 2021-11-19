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
    /**
     * @var mixed
     */
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>An offset to check for.</p>
     * @return bool true on success or false on failure.
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>The offset to retrieve.</p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new AssertionFailedError(sprintf("Example %s doesn't exist", $offset));
        }
        return $this->data[$offset];
    }

    /**
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>The offset to assign the value to.</p>
     * @param mixed $value <p>The value to set.</p>
     */
    public function offsetSet($offset, $value): void
    {
        $this->data[$offset] = $value;
    }

    /**
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>The offset to unset.</p>
     */
    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }

    /**
     * Count elements of an object
     *
     * @link http://php.net/manual/en/countable.count.php
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
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }
}
