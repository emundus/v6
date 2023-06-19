<?php


namespace PayPalCheckoutSdk\Payments;

use PayPalHttp\HttpRequest;

class CapturesGetRequest extends HttpRequest
{
    function __construct($captureId)
    {
        parent::__construct("/v2/payments/captures/{capture_id}?", "GET");

        $this->path = str_replace("{capture_id}", urlencode($captureId), $this->path);
        $this->headers["Content-Type"] = "application/json";
    }


}
