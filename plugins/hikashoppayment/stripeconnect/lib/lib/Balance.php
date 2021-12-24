<?php

namespace Stripe;

class Balance extends SingletonApiResource
{

    const OBJECT_NAME = "balance";

    public static function retrieve($opts = null)
    {
        return self::_singletonRetrieve($opts);
    }
}
