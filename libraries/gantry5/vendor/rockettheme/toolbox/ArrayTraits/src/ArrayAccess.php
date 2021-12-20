<?php

namespace RocketTheme\Toolbox\ArrayTraits;

/**
 * Implements ArrayAccess interface.
 *
 * @package RocketTheme\Toolbox\ArrayTraits
 * @author RocketTheme
 * @license MIT
 */
trait ArrayAccess
{
    /**
     * Whether or not an offset exists.
     *
     * @param string $offset An offset to check for.
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    /**
     * Returns the value at specified offset.
     *
     * @param string $offset The offset to retrieve.
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return isset($this->items[$offset]) ? $this->items[$offset] : null;
    }

    /**
     * Assigns a value to the specified offset.
     *
     * @param string|null $offset The offset to assign the value to.
     * @param mixed $value The value to set.
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (null === $offset) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * Unsets an offset.
     *
     * @param string $offset  The offset to unset.
     * @return void
     */
    public function offsetUnset($offset)
    {
        // Hack to make Iterator trait work together with unset.
        if (isset($this->iteratorUnset) && (string)$offset === (string)key($this->items)) {
            $this->iteratorUnset = true;
        }

        unset($this->items[$offset]);
    }
}
