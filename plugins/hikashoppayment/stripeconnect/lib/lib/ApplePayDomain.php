<?php

namespace Stripe;

class ApplePayDomain extends ApiResource
{

    const OBJECT_NAME = "apple_pay_domain";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Delete;
    use ApiOperations\Retrieve;

    public static function classUrl()
    {
        return '/v1/apple_pay/domains';
    }
}
