<?php


namespace PayPalCheckoutSdk\Payments;

use PayPalHttp\HttpRequest;

class AuthorizationsGetRequest extends HttpRequest
{
    function __construct($authorizationId)
    {
        parent::__construct("/v2/payments/authorizations/{authorization_id}?", "GET");

        $this->path = str_replace("{authorization_id}", urlencode($authorizationId), $this->path);
        $this->headers["Content-Type"] = "application/json";
    }


}
