<?php

namespace Stripe\Error;

class Card extends Base
{
    public function __construct(
        $message,
        $stripeParam,
        $stripeCode,
        $httpStatus,
        $httpBody,
        $jsonBody,
        $httpHeaders = null
    ) {
        parent::__construct($message, $httpStatus, $httpBody, $jsonBody, $httpHeaders);
        $this->stripeParam = $stripeParam;

        $this->stripeCode = $stripeCode;

        $this->declineCode = isset($jsonBody["error"]["decline_code"]) ? $jsonBody["error"]["decline_code"] : null;
    }

    public function getDeclineCode()
    {
        return $this->declineCode;
    }

    public function getStripeParam()
    {
        return $this->stripeParam;
    }
}
