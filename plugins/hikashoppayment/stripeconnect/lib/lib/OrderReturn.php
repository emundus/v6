<?php

namespace Stripe;

class OrderReturn extends ApiResource
{

    const OBJECT_NAME = "order_return";

    use ApiOperations\All;
    use ApiOperations\Retrieve;
}
