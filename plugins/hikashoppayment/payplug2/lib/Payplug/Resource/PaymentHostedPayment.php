<?php
namespace Payplug\Resource;

class PaymentHostedPayment extends APIResource
{
    public static function fromAttributes(array $attributes)
    {
        $object = new PaymentHostedPayment();
        $object->initialize($attributes);
        return $object;
    }
}
