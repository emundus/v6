<?php
namespace Payplug\Resource;

class PaymentAuthorization extends APIResource
{
    public static function fromAttributes(array $attributes)
    {
        $object = new PaymentAuthorization();
        $object->initialize($attributes);
        return $object;
    }
}
