<?php

namespace Stripe;

class Plan extends ApiResource
{

    const OBJECT_NAME = "plan";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Delete;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;
}
