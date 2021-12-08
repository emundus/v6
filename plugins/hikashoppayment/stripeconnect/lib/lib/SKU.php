<?php

namespace Stripe;

class SKU extends ApiResource
{

    const OBJECT_NAME = "sku";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Delete;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;
}
