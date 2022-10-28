<?php

namespace Omnipay\RedSys\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\RedSys\ResponseCode;

/**
 * RedSys Complete Purchase Response
 */
class CompletePurchaseResponse extends AbstractResponse
{
    public function isSuccessful()
    {
        return preg_match('/^00[0-9][0-9]$/', $this->getCode());
    }

    public function getCode()
    {
        return $this->data['Ds_Response'];
    }

    public function getMessage()
    {
        $responseCode = ResponseCode::find($this->getCode());
        return $responseCode ? $responseCode->getDescription() : null;
    }

    public function getTransactionReference()
    {
        return $this->data['Ds_Order'];
    }

    public function getTransactionId()
    {
        //return $this->data['Ds_Order'];
        return '';
    }
}
