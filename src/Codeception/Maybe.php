<?php
/**
 * Author: davert
 * Date: 30.07.12
 *
 * Class Maybe
 * Represents either empty values or defined from results.
 *
 */

namespace Codeception;

class Maybe implements \ArrayAccess
{
    protected $val = null;
   	function __construct($val = null)
   	{
   		$this->val = $val;
   	}

   	function __toString()
   	{
        if ($this->val === null) return "?";
   		return $this->val;
   	}

   	function __get($key)
   	{
   		if ($this->val === null) return new Maybe();
   		return $this->val->key;
   	}

   	function __set($key, $val)
   	{
        if ($this->val === null) return;
        $this->val->key = $val;
   	}

   	function __call($method, $args)
   	{
   		if ($this->val === null) return new Maybe();
        return call_user_func_array(array($this->val,$method), $args);
   	}

    public function offsetExists($offset)
    {
        if (is_array($this->val) or ($this->val instanceof \ArrayAccess)) return isset($this->val[$offset]);
        return false;
    }

    public function offsetGet($offset)
    {
        if (is_array($this->val) or ($this->val instanceof \ArrayAccess)) return $this->val[$offset];
        return new Maybe();
    }

    public function offsetSet($offset, $value)
    {
        if (is_array($this->val) or ($this->val instanceof \ArrayAccess)) $this->val[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        if (is_array($this->val) or ($this->val instanceof \ArrayAccess)) unset($this->val[$offset]);
    }
}