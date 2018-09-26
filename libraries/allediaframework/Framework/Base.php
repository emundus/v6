<?php
/**
 * @package   AllediaFramework
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC., All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\Framework;

defined('_JEXEC') or die();

class Base
{
    const MAP_UNDEFINED = 'undefined';

    /**
     * Retrieve all public properties and their values
     * Although this duplicates get_object_vars(), it
     * is mostly useful for internal calls when we need
     * to filter out the non-public properties.
     *
     * @param bool $publicOnly
     *
     * @return array
     */
    public function getProperties($publicOnly = true)
    {
        $reflection = new \ReflectionObject($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        if (!$publicOnly) {
            $properties = array_merge(
                $properties,
                $reflection->getProperties(\ReflectionProperty::IS_PROTECTED)
            );
        }

        $data = array();
        foreach ($properties as $property) {
            $name        = $property->name;
            $data[$name] = $this->$name;
        }

        return $data;
    }

    /**
     * Set the public properties from the passed array/object
     *
     * @param array|Base $data Values to copy to $this
     * @param array      $map  Use properties from $data translated using a field map
     *
     * @return $this
     * @throws Exception
     */
    public function setProperties($data, array $map = null)
    {
        $properties = $this->getProperties();
        if ($map !== null) {
            $data = $this->map($data, array_keys($properties), $map);
        } elseif (is_object($data)) {
            $data = get_object_vars($data);
        }
        if (!is_array($data)) {
            throw new Exception('Invalid argument given - ' . gettype($data));
        }

        foreach ($data as $k => $v) {
            if (array_key_exists($k, $properties)) {
                $this->$k = $data[$k];
            }
        }

        return $this;
    }

    /**
     * Set all properties to null
     *
     * @param bool $publicOnly Pass false to include protected properties as well
     *
     * @return $this
     */
    public function clearProperties($publicOnly = true)
    {
        $properties = array_keys($this->getProperties($publicOnly));
        foreach ($properties as $property) {
            $this->$property = null;
        }

        return $this;
    }

    /**
     * Map values in a source object/array to Joomlashack Framework keys using a map
     * of key equivalences. Any fields in $keys not present in $map will be
     * mapped name to name. Map fields mapped to null will be ignored.
     *
     * Special mappings for field values are recognized with another array. e.g.:
     *
     * $map['status'] = array(
     *     'state' => array(
     *         'active'              => 1,
     *         'closed'              => 0,
     *         Object::MAP_UNDEFINED => -1
     *     )
     * )
     * Will map the extension field 'status' to the source field 'state' and
     * set status based on the value in the state field. If no match, Object::MAP_UNDEFINED
     * will be used for the unknown value.
     *
     *
     * @param array|Base $source Source data to be mapped
     * @param array      $keys   Extension keys for which values are being requested
     * @param array      $map    Associative array where key=Extension Key, value=Source Key
     *
     * @return array An array of all specified keys with values filled in based on map
     * @throws Exception
     */
    public function map($source, array $keys, array $map = array())
    {
        if (!is_object($source) && !is_array($source)) {
            throw new Exception('Expected array or object for source argument');
        }

        $result = array_fill_keys($keys, null);
        foreach ($keys as $srKey) {
            $value = null;
            if (!array_key_exists($srKey, $map)) {
                $field = $srKey;
                $value = $this->getKeyValue($source, $field);
            } else {
                // This is a mapped key
                $field = $map[$srKey];

                if (!is_array($field)) {
                    $value = $this->getKeyValue($source, $field);
                } else {
                    // Mapped to field value
                    $values   = reset($map[$srKey]);
                    $field    = key($map[$srKey]);
                    $srcValue = $this->getKeyValue($source, $field);

                    if (isset($values[$srcValue])) {
                        $value = $values[$srcValue];
                    } elseif (isset($values[self::MAP_UNDEFINED])) {
                        $value = $values[self::MAP_UNDEFINED];
                    }
                }
            }
            $result[$srKey] = $value;
        }
        return $result;
    }

    /**
     * Safely get a value from an object|array
     *
     * @param Base|array $data
     * @param string     $var
     * @param mixed      $default
     *
     * @return mixed
     */
    public function getKeyValue($data, $var, $default = null)
    {
        if (is_object($data)) {
            return isset($data->$var) ? $data->$var : $default;
        }

        return isset($data[$var]) ? $data[$var] : $default;
    }

    /**
     *
     * Default string rendering for the object. Subclasses should feel
     * free to override as desired.
     *
     * @return string
     */
    public function asString()
    {
        return get_class($this);
    }

    /**
     * Expose properties with defined getters for direct use
     *
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        $method = 'get' . ucfirst($name);
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        return null;
    }

    public function __toString()
    {
        return $this->asString();
    }
}
