<?php

namespace Stripe;

class ThreeDSecure extends ApiResource
{

    const OBJECT_NAME = "three_d_secure";

    use ApiOperations\Create;
    use ApiOperations\Retrieve;

    public static function classUrl()
    {
        return "/v1/3d_secure";
    }
}
