<?php

namespace Stripe;

class WebhookEndpoint extends ApiResource
{

    const OBJECT_NAME = "webhook_endpoint";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Delete;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;
}
