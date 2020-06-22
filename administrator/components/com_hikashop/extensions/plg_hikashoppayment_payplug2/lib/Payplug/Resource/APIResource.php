<?php
namespace Payplug\Resource;
use Payplug;

abstract class APIResource implements IAPIResourceFactory
{
    protected $_attributes;

    protected function __construct()
    {
    }

    public static function factory(array $attributes)
    {
        if (!array_key_exists('object', $attributes)) {
            throw new Payplug\Exception\UnknownAPIResourceException('Missing "object" property.');
        }

        switch ($attributes['object']) {
            case 'payment':
                return Payplug\Resource\Payment::fromAttributes($attributes);
            case 'refund':
                return Payplug\Resource\Refund::fromAttributes($attributes);
            case 'installment_plan':
                return Payplug\Resource\InstallmentPlan::fromAttributes($attributes);
        }

        throw new Payplug\Exception\UnknownAPIResourceException('Unknown "object" property "' . $attributes['object'] . '".');
    }

    protected final function getAttributes()
    {
        return $this->_attributes;
    }

    protected function setAttributes(array $attributes)
    {
        $this->_attributes = $attributes;
    }

    public function __get($attribute)
    {
        if ($this->__isset($attribute)) {
            return $this->_attributes[$attribute];
        }

        throw new Payplug\Exception\UndefinedAttributeException('Requested attribute ' . $attribute . ' is undefined.');
    }

    public function __isset($attribute)
    {
        return array_key_exists($attribute, $this->_attributes);
    }


    public function __set($attribute, $value)
    {
        $this->_attributes[$attribute] = $value;
    }

    protected function initialize(array $attributes)
    {
        $this->setAttributes($attributes);
    }
}
