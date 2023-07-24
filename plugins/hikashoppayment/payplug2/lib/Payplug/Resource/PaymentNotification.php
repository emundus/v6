<?php
namespace Payplug\Resource;

class PaymentNotification extends APIResource
{
    public static function fromAttributes(array $attributes)
    {
        $object = new PaymentNotification();
        $object->initialize($attributes);
        return $object;
    }
}
