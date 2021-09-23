<?php

namespace Omnipay\PayU\Message\Latam;

use Omnipay\Common\Exception\InvalidResponseException;

/**
 * WorldPay Complete Purchase Request
 */
class CompletePurchaseRequest extends PurchaseRequest
{
    public function getData()
    {
    	$apiKey = $this->getParameter('apiKey');
    	$merchantId = $this->httpRequest->query->get('merchantId');
    	$referenceCode = $this->httpRequest->query->get('referenceCode');
    	$txValue = $this->httpRequest->query->get('TX_VALUE');
    	$newTxValue = number_format($txValue, 1, '.', '');
    	$currency = $this->httpRequest->query->get('currency');
    	$transactionState = $this->httpRequest->query->get('transactionState');
    	$firmaCadena = "$apiKey~$merchantId~$referenceCode~$newTxValue~$currency~$transactionState";
    	$firmaCreada = md5($firmaCadena);
    	$signature = $this->httpRequest->query->get('signature');
    	if (strtoupper($signature) == strtoupper($firmaCreada))
    	{
    		return $this->httpRequest->query->all();
    	}
    	else 
    	{
    		throw new InvalidResponseException("Error validating digital signature.");
    	}
    }

    public function sendData($data)
    {
        return $this->response = new CompletePurchaseResponse($this, $data);
    }
}
