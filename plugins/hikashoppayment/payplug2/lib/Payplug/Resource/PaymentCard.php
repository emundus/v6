<?php
namespace Payplug\Resource;

class PaymentCard extends APIResource
{
    public static function fromAttributes(array $attributes)
    {
        $object = new PaymentCard();
        $object->initialize($attributes);
        return $object;
    }
}
