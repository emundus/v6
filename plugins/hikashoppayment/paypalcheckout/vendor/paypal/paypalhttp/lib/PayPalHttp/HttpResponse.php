<?php

namespace PayPalHttp;

class HttpResponse
{
    public $statusCode;

    public $result;

    public $headers;

    public function __construct($statusCode, $body, $headers)
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->result = $body;
    }
}
