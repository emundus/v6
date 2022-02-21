<?php

namespace Stripe\Terminal;

class Reader extends \Stripe\ApiResource
{
    const OBJECT_NAME = "terminal.reader";

    use \Stripe\ApiOperations\All;
    use \Stripe\ApiOperations\Create;
    use \Stripe\ApiOperations\Delete;
    use \Stripe\ApiOperations\Retrieve;
    use \Stripe\ApiOperations\Update;
}
