<?php

namespace Stripe\Terminal;

class ConnectionToken extends \Stripe\ApiResource
{
    const OBJECT_NAME = "terminal.connection_token";

    use \Stripe\ApiOperations\Create;
}
