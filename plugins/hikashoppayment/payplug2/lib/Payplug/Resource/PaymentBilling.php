<?php
namespace Payplug\Resource;

class PaymentBilling extends APIResource
{
    public static function fromAttributes(array $attributes)
    {
        $object = new PaymentBilling();
        $object->initialize($attributes);
        return $object;
    }
}
