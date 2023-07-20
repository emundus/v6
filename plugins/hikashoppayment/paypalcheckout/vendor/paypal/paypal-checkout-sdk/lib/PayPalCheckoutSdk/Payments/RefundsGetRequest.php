<?php


namespace PayPalCheckoutSdk\Payments;

use PayPalHttp\HttpRequest;

class RefundsGetRequest extends HttpRequest
{
    function __construct($refundId)
    {
        parent::__construct("/v2/payments/refunds/{refund_id}?", "GET");

        $this->path = str_replace("{refund_id}", urlencode($refundId), $this->path);
        $this->headers["Content-Type"] = "application/json";
    }


}
