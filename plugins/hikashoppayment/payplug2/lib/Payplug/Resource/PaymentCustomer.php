<?php
namespace Payplug\Resource;

class PaymentCustomer extends APIResource
{
    public static function fromAttributes(array $attributes)
    {
        $object = new PaymentCustomer();
        $object->initialize($attributes);
        return $object;
    }
}
