<?php

namespace Stripe\Issuing;

class Dispute extends \Stripe\ApiResource
{
    const OBJECT_NAME = "issuing.dispute";

    use \Stripe\ApiOperations\All;
    use \Stripe\ApiOperations\Create;
    use \Stripe\ApiOperations\Retrieve;
    use \Stripe\ApiOperations\Update;
}
