<?php


namespace PayPalCheckoutSdk\Payments;

use PayPalHttp\HttpRequest;

class AuthorizationsCaptureRequest extends HttpRequest
{
    function __construct($authorizationId)
    {
        parent::__construct("/v2/payments/authorizations/{authorization_id}/capture?", "POST");

        $this->path = str_replace("{authorization_id}", urlencode($authorizationId), $this->path);
        $this->headers["Content-Type"] = "application/json";
    }


    public function payPalRequestId($payPalRequestId)
    {
        $this->headers["PayPal-Request-Id"] = $payPalRequestId;
    }
    public function prefer($prefer)
    {
        $this->headers["Prefer"] = $prefer;
    }
}
