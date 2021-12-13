<?php

namespace Stripe\Terminal;

class Location extends \Stripe\ApiResource
{
    const OBJECT_NAME = "terminal.location";

    use \Stripe\ApiOperations\All;
    use \Stripe\ApiOperations\Create;
    use \Stripe\ApiOperations\Delete;
    use \Stripe\ApiOperations\Retrieve;
    use \Stripe\ApiOperations\Update;
}
