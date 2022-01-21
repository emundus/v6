<?php

namespace Stripe;

class ApiResponse
{
    public $headers;
    public $body;
    public $json;
    public $code;

    public function __construct($body, $code, $headers, $json)
    {
        $this->body = $body;
        $this->code = $code;
        $this->headers = $headers;
        $this->json = $json;
    }
}
