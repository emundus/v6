<?php

namespace Stripe\Checkout;

class Session extends \Stripe\ApiResource
{

    const OBJECT_NAME = "checkout.session";

    use \Stripe\ApiOperations\Create;
    use \Stripe\ApiOperations\Retrieve;
}
