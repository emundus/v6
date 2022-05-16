<?php

namespace Stripe\Util;

use ArrayAccess;

class CaseInsensitiveArray implements ArrayAccess
{
    private $container = array();

    public function __construct($initial_array = array())
    {
        $this->container = array_map("strtolower", $initial_array);
    }

    public function offsetSet($offset, $value)
    {
        $offset = static::maybeLowercase($offset);
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        $offset = static::maybeLowercase($offset);
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset)
    {
        $offset = static::maybeLowercase($offset);
        unset($this->container[$offset]);
    }

    public function offsetGet($offset)
    {
        $offset = static::maybeLowercase($offset);
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    private static function maybeLowercase($v)
    {
        if (is_string($v)) {
            return strtolower($v);
        } else {
            return $v;
        }
    }
}
