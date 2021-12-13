<?php

namespace Stripe\Issuing;

class Transaction extends \Stripe\ApiResource
{
    const OBJECT_NAME = "issuing.transaction";

    use \Stripe\ApiOperations\All;
    use \Stripe\ApiOperations\Create;
    use \Stripe\ApiOperations\Retrieve;
    use \Stripe\ApiOperations\Update;
}
