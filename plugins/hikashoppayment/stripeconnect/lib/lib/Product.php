<?php

namespace Stripe;

class Product extends ApiResource
{

    const OBJECT_NAME = "product";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Delete;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;

    const TYPE_GOOD    = 'good';
    const TYPE_SERVICE = 'service';
}
