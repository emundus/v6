<?php

namespace RocketTheme\Toolbox\ArrayTraits;

/**
 * Implements \Serializable interface.
 *
 * @package RocketTheme\Toolbox\ArrayTraits
 * @author RocketTheme
 * @license MIT
 */
trait Serializable
{
    /**
     * Returns string representation of the object.
     *
     * @return string  Returns the string representation of the object.
     */
    public function serialize()
    {
        return serialize($this->items);
    }

    /**
     * Called during unserialization of the object.
     *
     * @param string $serialized  The string representation of the object.
     * @return void
     */
    public function unserialize($serialized)
    {
        $this->items = unserialize($serialized);
    }
}
