<?php

namespace Stripe;

class RequestTelemetry
{
    public $requestId;
    public $requestDuration;

    public function __construct($requestId, $requestDuration)
    {
        $this->requestId = $requestId;
        $this->requestDuration = $requestDuration;
    }
}
