<?php

namespace Stripe\Issuing;

class Cardholder extends \Stripe\ApiResource
{
    const OBJECT_NAME = "issuing.cardholder";

    use \Stripe\ApiOperations\All;
    use \Stripe\ApiOperations\Create;
    use \Stripe\ApiOperations\Retrieve;
    use \Stripe\ApiOperations\Update;
}
