<?php
namespace Codeception\Util;

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
 * ?>
 * ```
 */
class Maybe implements \ArrayAccess, \Iterator, \JsonSerializable
{
    protected $position = 0;
    protected $val = null;
    protected $assocArray = null;

    function __construct($val = null)
    {
        $this->val = $val;
        if (is_array($this->val)) {
            $this->assocArray = $this->isAssocArray($this->val);
        }
        $this->position = 0;
    }

    private function isAssocArray($arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    function __toString()
    {
        if ($this->val === null) {
            return "?";
        }
        if (is_scalar($this->val)) {
            return (string)$this->val;
        }

        if (is_object($this->val) && method_exists($this->val, '__toString')) {
            return $this->val->__toString();
        }

        return $this->val;
    }

    function __get($key)
    {
        if ($this->val === null) {
            return new Maybe();
        }

        if (is_object($this->val)) {
            if (isset($this->val->{$key}) || property_exists($this->val, $key)) {
                return $this->val->{$key};
            }
        }

        return $this->val->key;
    }

    function __set($key, $val)
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

    function __call($method, $args)
    {
        if ($this->val === null) {
            return new Maybe();
        }
        return call_user_func_array(array($this->val, $method), $args);
    }

    function __clone()
    {
        if (is_object($this->val)) {
            $this->val = clone $this->val;
        }
    }

    public function __unset($key)
    {
        if (is_object($this->val)) {
            if (isset($this->val->{$key}) || property_exists($this->val, $key)) {
                unset($this->val->{$key});
                return;
            }
        }
    }

    public function offsetExists($offset)
    {
        if (is_array($this->val) or ($this->val instanceof \ArrayAccess)) {
            return isset($this->val[$offset]);
        }
        return false;
    }

    public function offsetGet($offset)
    {
        if (is_array($this->val) or ($this->val instanceof \ArrayAccess)) {
            return $this->val[$offset];
        }
        return new Maybe();
    }

    public function offsetSet($offset, $value)
    {
        if (is_array($this->val) or ($this->val instanceof \ArrayAccess)) {
            $this->val[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        if (is_array($this->val) or ($this->val instanceof \ArrayAccess)) {
            unset($this->val[$offset]);
        }
    }

    public function __value()
    {
        $val = $this->val;
        if (is_array($val)) {
            foreach ($val as $k => $v) {
                if ($v instanceof self) {
                    $v = $v->__value();
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
     * @return mixed Can return any type.
     */
    public function current()
    {
        if (! is_array($this->val)) {
            return null;
        }
        if ($this->assocArray) {
            $keys = array_keys($this->val);
            return $this->val[$keys[$this->position]];
        } else {
            return $this->val[$this->position];
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        if ($this->assocArray) {
            $keys = array_keys($this->val);
            return $keys[$this->position];
        } else {
            return $this->position;
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        if (! is_array($this->val)) {
            return null;
        }
        if ($this->assocArray) {
            $keys = array_keys($this->val);
            return isset($keys[$this->position]);
        } else {
            return isset($this->val[$this->position]);
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
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
        return $this->__value();
    }
}
