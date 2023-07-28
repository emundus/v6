<?php

namespace PayPalHttp;

class HttpRequest
{
    public $path;

    public $body;

    public $verb;

    public $headers;

    function __construct($path, $verb)
    {
        $this->path = $path;
        $this->verb = $verb;
        $this->body = NULL;
        $this->headers = [];
    }
}
