<?php

namespace Stripe\Radar;

class ValueList extends \Stripe\ApiResource
{
    const OBJECT_NAME = "radar.value_list";

    use \Stripe\ApiOperations\All;
    use \Stripe\ApiOperations\Create;
    use \Stripe\ApiOperations\Delete;
    use \Stripe\ApiOperations\Retrieve;
    use \Stripe\ApiOperations\Update;
}
