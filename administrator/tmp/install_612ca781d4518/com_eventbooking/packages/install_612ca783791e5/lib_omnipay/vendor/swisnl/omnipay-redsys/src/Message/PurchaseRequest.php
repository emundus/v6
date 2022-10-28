<?php

namespace Omnipay\RedSys\Message;

/**
 * RedSys Purchase Request
 */
class PurchaseRequest extends RequestBase
{
    public function setTransactionId($value)
    {
    	/*
        if (!preg_match('/^[0-9]{4}[0-9a-zA-Z]{0,8}$/', $value)) {
            throw new \InvalidArgumentException('Invalid transaction id');
        }
        */
        return parent::setTransactionId($value);
    }

    public function setTransactionReference($value)
    {
    	/*
        if (!preg_match('/^[0-9]{4}[0-9a-zA-Z]{0,8}$/', $value)) {
            throw new \InvalidArgumentException('Invalid transaction reference');
        }
        */
        return parent::setTransactionReference($value);
    }

    public function setTitular($titular)
    {
        return $this->setParameter('titular', $titular);
    }

    public function getMerchantCode()
    {
        return $this->getParameter('merchantCode');
    }

    public function setMerchantCode($value)
    {
        return $this->setParameter('merchantCode', $value);
    }

    public function getSecretKey()
    {
        return $this->getParameter('secretKey');
    }

    public function setSecretKey($value)
    {
        return $this->setParameter('secretKey', $value);
    }

    public function getTerminal()
    {
        return $this->getParameter('terminal');
    }

    public function setTerminal($value)
    {
        return $this->setParameter('terminal', $value);
    }

    public function getMerchantName()
    {
        return $this->getParameter('merchantName');
    }

    public function setMerchantName($value)
    {
        return $this->setParameter('merchantName', $value);
    }

    public function setConsumerLanguage($consumerLanguage)
    {
        return $this->setParameter('consumerLanguage', $consumerLanguage);
    }

    public function getExtraData()
    {
        return $this->getParameter('extraData');
    }

    public function setExtraData($value)
    {
        return $this->setParameter('extraData', $value);
    }

    public function getTransactionType()
    {
        return '0';
    }

    public function getAuthorisationCode()
    {
        return $this->getParameter('authorisationCode');
    }

    public function setAuthorisationCode($value)
    {
        return $this->setParameter('authorisationCode', $value);
    }

    public function getPayMethods()
    {
        return $this->getParameter('payMethods');
    }

    public function setPayMethods($value)
    {
        return $this->setParameter('payMethods', $value);
    }

    protected function getMerchantOrder()
    {
        return $this->getTransactionReference() ?: $this->getTransactionId();
    }
    
    protected function getCurrencyNumber()
    {
    	return $this->getParameter('currencyNumber');
    }
    
    public function setCurrencyNumber($value)
    {
    	return $this->setParameter('currencyNumber', $value);
    }

    public function getData()
    {
        $this->validate('amount', 'currency', 'transactionId', 'merchantCode', 'terminal');
        $parameters = [
	        'DS_MERCHANT_AMOUNT'				=> $this->getAmountInteger(),
	        'DS_MERCHANT_ORDER'					=> $this->getMerchantOrder(),
	        'DS_MERCHANT_MERCHANTCODE'			=> $this->getMerchantCode(),
	        'DS_MERCHANT_CURRENCY'				=> $this->getCurrencyNumber(),
	        'DS_MERCHANT_TRANSACTIONTYPE'		=> $this->getTransactionType(),
	        'DS_MERCHANT_TERMINAL'				=> $this->getTerminal(),
	        'DS_MERCHANT_MERCHANTURL'			=> $this->getNotifyUrl(),
	        'DS_MERCHANT_URLOK'					=> $this->getReturnUrl(),
	        'DS_MERCHANT_URLKO'					=> $this->getCancelUrl(),
	        'Ds_Merchant_ConsumerLanguage'		=> $this->getParameter('consumerLanguage'),
	        'Ds_Merchant_ProductDescription'	=> $this->getDescription(),
	        'Ds_Merchant_Titular'				=> '',
	        'Ds_Merchant_MerchantData'			=> sha1($this->getNotifyUrl()),
	        'Ds_Merchant_MerchantName'			=> $this->getMerchantName(),
	        'Ds_Merchant_Module'				=> 'EShop Shopping Cart',
        ];
        
        if ($this->getPayMethods())
        {
        	$parameters['Ds_Merchant_PayMethods'] = $this->getPayMethods();
        }
        
        $parameters = $this->getEncoder()->encode($parameters);
        $signature = $this->getSigner()->generateSignature($parameters, $this->getMerchantOrder());

        $data = [
          'Ds_MerchantParameters' => $parameters,
          'Ds_Signature' => $signature,
          'Ds_SignatureVersion' => 'HMAC_SHA256_V1',
        ];

        return $data;
    }

    public function sendData($data)
    {
        return $this->response = new PurchaseResponse($this, $data);
    }
}
