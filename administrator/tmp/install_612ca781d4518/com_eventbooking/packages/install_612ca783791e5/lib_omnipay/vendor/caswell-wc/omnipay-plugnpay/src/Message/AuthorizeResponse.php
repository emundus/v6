<?php

namespace Omnipay\PlugNPay\Message;

/**
 * Class AuthorizeResponse
 *
 * @package Omnipay\PlugNPay\Message
 */
class AuthorizeResponse extends Response
{
    /**
     * Get the response code for this authorization.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->data['resp-code'];
    }

    /**
     * Get the transaction reference for this authorization.
     *
     * @return string
     */
    public function getTransactionReference()
    {
        return $this->data['orderID'];
    }

    /**
     * Get the auth code for this authorization.
     *
     * @return string
     */
    public function getAuthCode()
    {
        return $this->data['auth-code'];
    }
}
