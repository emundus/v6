<?php

namespace Stripe;

class StripeObject implements \ArrayAccess, \Countable, \JsonSerializable
{
    protected $_opts;
    protected $_originalValues;
    protected $_values;
    protected $_unsavedValues;
    protected $_transientValues;
    protected $_retrieveOptions;
    protected $_lastResponse;

    public static function getPermanentAttributes()
    {
        static $permanentAttributes = null;
        if ($permanentAttributes === null) {
            $permanentAttributes = new Util\Set([
                'id',
            ]);
        }
        return $permanentAttributes;
    }

    public static function getAdditiveParams()
    {
        static $additiveParams = null;
        if ($additiveParams === null) {
            $additiveParams = new Util\Set([
                'metadata',
            ]);
        }
        return $additiveParams;
    }

    public function __construct($id = null, $opts = null)
    {
        list($id, $this->_retrieveOptions) = Util\Util::normalizeId($id);
        $this->_opts = Util\RequestOptions::parse($opts);
        $this->_originalValues = [];
        $this->_values = [];
        $this->_unsavedValues = new Util\Set();
        $this->_transientValues = new Util\Set();
        if ($id !== null) {
            $this->_values['id'] = $id;
        }
    }

    public function __set($k, $v)
    {
        if (static::getPermanentAttributes()->includes($k)) {
            throw new \InvalidArgumentException(
                "Cannot set $k on this object. HINT: you can't set: " .
                join(', ', static::getPermanentAttributes()->toArray())
            );
        }

        if ($v === "") {
            throw new \InvalidArgumentException(
                'You cannot set \''.$k.'\'to an empty string. '
                .'We interpret empty strings as NULL in requests. '
                .'You may set obj->'.$k.' = NULL to delete the property'
            );
        }

        $this->_values[$k] = Util\Util::convertToStripeObject($v, $this->_opts);
        $this->dirtyValue($this->_values[$k]);
        $this->_unsavedValues->add($k);
    }

    public function __isset($k)
    {
        return isset($this->_values[$k]);
    }

    public function __unset($k)
    {
        unset($this->_values[$k]);
        $this->_transientValues->add($k);
        $this->_unsavedValues->discard($k);
    }

    public function &__get($k)
    {
        $nullval = null;
        if (!empty($this->_values) && array_key_exists($k, $this->_values)) {
            return $this->_values[$k];
        } else if (!empty($this->_transientValues) && $this->_transientValues->includes($k)) {
            $class = get_class($this);
            $attrs = join(', ', array_keys($this->_values));
            $message = "Stripe Notice: Undefined property of $class instance: $k. "
                    . "HINT: The $k attribute was set in the past, however. "
                    . "It was then wiped when refreshing the object "
                    . "with the result returned by Stripe's API, "
                    . "probably as a result of a save(). The attributes currently "
                    . "available on this object are: $attrs";
            Stripe::getLogger()->error($message);
            return $nullval;
        } else {
            $class = get_class($this);
            Stripe::getLogger()->error("Stripe Notice: Undefined property of $class instance: $k");
            return $nullval;
        }
    }

    public function __debugInfo()
    {
        return $this->_values;
    }

    public function offsetSet($k, $v)
    {
        $this->$k = $v;
    }

    public function offsetExists($k)
    {
        return array_key_exists($k, $this->_values);
    }

    public function offsetUnset($k)
    {
        unset($this->$k);
    }

    public function offsetGet($k)
    {
        return array_key_exists($k, $this->_values) ? $this->_values[$k] : null;
    }

    public function count()
    {
        return count($this->_values);
    }

    public function keys()
    {
        return array_keys($this->_values);
    }

    public function values()
    {
        return array_values($this->_values);
    }

    public static function constructFrom($values, $opts = null)
    {
        $obj = new static(isset($values['id']) ? $values['id'] : null);
        $obj->refreshFrom($values, $opts);
        return $obj;
    }

    public function refreshFrom($values, $opts, $partial = false)
    {
        $this->_opts = Util\RequestOptions::parse($opts);

        $this->_originalValues = self::deepCopy($values);

        if ($values instanceof StripeObject) {
            $values = $values->__toArray(true);
        }

        if ($partial) {
            $removed = new Util\Set();
        } else {
            $removed = new Util\Set(array_diff(array_keys($this->_values), array_keys($values)));
        }

        foreach ($removed->toArray() as $k) {
            unset($this->$k);
        }

        $this->updateAttributes($values, $opts, false);
        foreach ($values as $k => $v) {
            $this->_transientValues->discard($k);
            $this->_unsavedValues->discard($k);
        }
    }

    public function updateAttributes($values, $opts = null, $dirty = true)
    {
        foreach ($values as $k => $v) {
            if (($k === "metadata") && (is_array($v))) {
                $this->_values[$k] = StripeObject::constructFrom($v, $opts);
            } else {
                $this->_values[$k] = Util\Util::convertToStripeObject($v, $opts);
            }
            if ($dirty) {
                $this->dirtyValue($this->_values[$k]);
            }
            $this->_unsavedValues->add($k);
        }
    }

    public function serializeParameters($force = false)
    {
        $updateParams = [];

        foreach ($this->_values as $k => $v) {
            $original = array_key_exists($k, $this->_originalValues) ? $this->_originalValues[$k] : null;
            $unsaved = $this->_unsavedValues->includes($k);
            if ($force || $unsaved || $v instanceof StripeObject) {
                $updateParams[$k] = $this->serializeParamsValue(
                    $this->_values[$k],
                    $original,
                    $unsaved,
                    $force,
                    $k
                );
            }
        }

        $updateParams = array_filter(
            $updateParams,
            function ($v) {
                return $v !== null;
            }
        );

        return $updateParams;
    }


    public function serializeParamsValue($value, $original, $unsaved, $force, $key = null)
    {
        if ($value === null) {
            return "";
        } elseif (($value instanceof APIResource) && (!$value->saveWithParent)) {
            if (!$unsaved) {
                return null;
            } elseif (isset($value->id)) {
                return $value;
            } else {
                throw new \InvalidArgumentException(
                    "Cannot save property `$key` containing an API resource of type " .
                    get_class($value) . ". It doesn't appear to be persisted and is " .
                    "not marked as `saveWithParent`."
                );
            }
        } elseif (is_array($value)) {
            if (Util\Util::isList($value)) {
                $update = [];
                foreach ($value as $v) {
                    array_push($update, $this->serializeParamsValue($v, null, true, $force));
                }
                if ($update !== $this->serializeParamsValue($original, null, true, $force, $key)) {
                    return $update;
                }
            } else {
                return Util\Util::convertToStripeObject($value, $this->_opts)->serializeParameters();
            }
        } elseif ($value instanceof StripeObject) {
            $update = $value->serializeParameters($force);
            if ($original && $unsaved && $key && static::getAdditiveParams()->includes($key)) {
                $update = array_merge(self::emptyValues($original), $update);
            }
            return $update;
        } else {
            return $value;
        }
    }

    public function jsonSerialize()
    {
        return $this->__toArray(true);
    }

    public function __toJSON()
    {
        return json_encode($this->__toArray(true), JSON_PRETTY_PRINT);
    }

    public function __toString()
    {
        $class = get_class($this);
        return $class . ' JSON: ' . $this->__toJSON();
    }

    public function __toArray($recursive = false)
    {
        if ($recursive) {
            return Util\Util::convertStripeObjectToArray($this->_values);
        } else {
            return $this->_values;
        }
    }

    public function dirty()
    {
        $this->_unsavedValues = new Util\Set(array_keys($this->_values));
        foreach ($this->_values as $k => $v) {
            $this->dirtyValue($v);
        }
    }

    protected function dirtyValue($value)
    {
        if (is_array($value)) {
            foreach ($value as $v) {
                $this->dirtyValue($v);
            }
        } elseif ($value instanceof StripeObject) {
            $value->dirty();
        }
    }

    protected static function deepCopy($obj)
    {
        if (is_array($obj)) {
            $copy = [];
            foreach ($obj as $k => $v) {
                $copy[$k] = self::deepCopy($v);
            }
            return $copy;
        } elseif ($obj instanceof StripeObject) {
            return $obj::constructFrom(
                self::deepCopy($obj->_values),
                clone $obj->_opts
            );
        } else {
            return $obj;
        }
    }

    public static function emptyValues($obj)
    {
        if (is_array($obj)) {
            $values = $obj;
        } elseif ($obj instanceof StripeObject) {
            $values = $obj->_values;
        } else {
            throw new \InvalidArgumentException(
                "empty_values got got unexpected object type: " . get_class($obj)
            );
        }
        $update = array_fill_keys(array_keys($values), "");
        return $update;
    }

    public function getLastResponse()
    {
        return $this->_lastResponse;
    }

    public function setLastResponse($resp)
    {
        $this->_lastResponse = $resp;
    }

    public function isDeleted()
    {
        return isset($this->_values['deleted']) ? $this->_values['deleted'] : false;
    }
}
