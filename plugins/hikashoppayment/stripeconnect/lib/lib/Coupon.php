<?php

namespace Stripe;

class Coupon extends ApiResource
{

    const OBJECT_NAME = "coupon";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Delete;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;
}
