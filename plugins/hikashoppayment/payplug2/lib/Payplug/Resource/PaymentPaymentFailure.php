<?php
namespace Payplug\Resource;

class PaymentPaymentFailure extends APIResource
{
    public static function fromAttributes(array $attributes)
    {
        $object = new PaymentPaymentFailure();
        $object->initialize($attributes);
        return $object;
    }
}
