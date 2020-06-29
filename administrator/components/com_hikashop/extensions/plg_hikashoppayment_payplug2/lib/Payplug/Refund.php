<?php
namespace Payplug;

class Refund {
    public static function create($payment, array $data = null, Payplug $payplug = null)
    {
    	return Resource\Refund::create($payment, $data, $payplug);
    }

    public static function retrieve($payment, $refundId, Payplug $payplug = null)
    {
    	return Resource\Refund::retrieve($payment, $refundId, $payplug);
    }

    public static function listRefunds($payment, Payplug $payplug = null)
    {
    	return Resource\Refund::listRefunds($payment, $payplug);
    }
}
