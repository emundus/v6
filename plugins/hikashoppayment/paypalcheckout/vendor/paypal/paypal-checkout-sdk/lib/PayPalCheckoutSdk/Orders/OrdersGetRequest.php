<?php


namespace PayPalCheckoutSdk\Orders;

use PayPalHttp\HttpRequest;

class OrdersGetRequest extends HttpRequest
{
    function __construct($orderId)
    {
        parent::__construct("/v2/checkout/orders/{order_id}?", "GET");

        $this->path = str_replace("{order_id}", urlencode($orderId), $this->path);
        $this->headers["Content-Type"] = "application/json";
    }



}
