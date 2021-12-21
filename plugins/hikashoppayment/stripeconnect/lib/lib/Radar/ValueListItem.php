<?php

namespace Stripe\Radar;

class ValueListItem extends \Stripe\ApiResource
{
    const OBJECT_NAME = "radar.value_list_item";

    use \Stripe\ApiOperations\All;
    use \Stripe\ApiOperations\Create;
    use \Stripe\ApiOperations\Delete;
    use \Stripe\ApiOperations\Retrieve;
}
