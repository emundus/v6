<?php

namespace Stripe;

class EphemeralKey extends ApiResource
{

    const OBJECT_NAME = "ephemeral_key";

    use ApiOperations\Create {
        create as protected _create;
    }
    use ApiOperations\Delete;

    public static function create($params = null, $opts = null)
    {
        if (!$opts['stripe_version']) {
            throw new \InvalidArgumentException('stripe_version must be specified to create an ephemeral key');
        }
        return self::_create($params, $opts);
    }
}
