<?php

namespace Stripe\Util;

class RandomGenerator
{
    public function randFloat($max = 1.0)
    {
        return mt_rand() / mt_getrandmax() * $max;
    }

    public function uuid()
    {
        $arr = array_values(unpack('N1a/n4b/N1c', openssl_random_pseudo_bytes(16)));
        $arr[2] = ($arr[2] & 0x0fff) | 0x4000;
        $arr[3] = ($arr[3] & 0x3fff) | 0x8000;
        return vsprintf('%08x-%04x-%04x-%04x-%04x%08x', $arr);
    }
}
