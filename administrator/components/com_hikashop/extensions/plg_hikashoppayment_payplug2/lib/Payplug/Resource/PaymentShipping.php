<?php
namespace Payplug\Resource;

class PaymentShipping extends APIResource
{
    public static function fromAttributes(array $attributes)
    {
        $object = new PaymentShipping();
        $object->initialize($attributes);
        return $object;
    }
}
