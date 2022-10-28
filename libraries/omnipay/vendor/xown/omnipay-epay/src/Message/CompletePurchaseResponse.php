<?php

namespace Omnipay\Epay\Message;

use Omnipay\Common\Message\AbstractResponse;

/**
 * Epay Complete Purchase Response
 */
class CompletePurchaseResponse extends AbstractResponse
{
    public function isSuccessful()
    {
        return true;
    }

	public function getTransactionId()
    {
    	return isset($this->data['orderid']) ? $this->data['orderid'] : null;
    }
    
    public function getTransactionReference()
    {
        return isset($this->data['txnid']) ? $this->data['txnid'] : null;
    }
}
