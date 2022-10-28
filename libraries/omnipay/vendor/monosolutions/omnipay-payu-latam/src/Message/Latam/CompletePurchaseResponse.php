<?php

namespace Omnipay\PayU\Message\Latam;

use Omnipay\Common\Message\AbstractResponse;

/**
 * WorldPay Complete Purchase Response
 */
class CompletePurchaseResponse extends AbstractResponse
{
    public function isSuccessful()
    {
        return isset($this->data['transactionState']) && $this->data['transactionState'] == '4';
    }

    public function getTransactionId()
    {
    	if (isset($this->data['referenceCode']))
    	{
    		return $this->data['referenceCode'];
    	}
    }
    
    public function getTransactionReference()
    {
        return isset($this->data['transactionId']) ? $this->data['transactionId'] : null;
    }
    
    public function getMessage()
    {
    	return isset($this->data['message']) ? $this->data['message'] : null;
    }
}
