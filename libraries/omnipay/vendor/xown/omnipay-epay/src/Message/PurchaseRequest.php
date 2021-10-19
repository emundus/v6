<?php

namespace Omnipay\Epay\Message;

use Omnipay\Common\Helper;
use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Exception\RuntimeException;
use Omnipay\Common\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\ParameterBag;


/**
 * Epay Purchase Request
 */
class PurchaseRequest extends AbstractRequest
{
    /**
     * @inheritdoc
     */
    public function initialize(array $parameters = array())
    {
        if (null !== $this->response) {
            throw new RuntimeException('Request cannot be modified after it has been sent!');
        }

        $this->parameters = new ParameterBag();
        $supportedKeys = $this->getSupportedKeys();
        if (is_array($parameters)) {
            foreach ($parameters as $key => $value) {
                $method = 'set'.ucfirst(Helper::camelCase($key));
                if (method_exists($this, $method)) {
                    $this->$method($value);
                } else if(in_array($key, $supportedKeys)) {
                    $this->parameters->set($key, $value);
                }
            }
        }

        return $this;
    }

    public function getSupportedKeys() {

        return ['merchantnumber', 'currency','amount', 'secret', 'orderid', 'windowstate', 'mobile', 'windowid',
            'paymentcollection', 'lockpaymentcollection', 'paymenttype', 'language', 'encoding', 'cssurl', 'mobilecssurl',
            'instantcapture', 'splitpayment', 'instantcallback', 'callbackurl', 'accepturl', 'cancelurl', 'ownreceipt',
            'ordertext', 'group', 'description', 'subscription', 'subscriptionname', 'mailreceipt', 'googletracker',
            'backgroundcolor', 'opacity', 'declinetext', 'iframeheight', 'iframewidth', 'timeout'];
    }

    public function getData()
    {
        $this->validate('merchantnumber', 'currency', 'accepturl', 'amount');

        $data = array();
        foreach($this->getSupportedKeys() as $key) {
            $value = $this->parameters->get($key);
            if ($value !== null) {
                $data[$key] = $value;
            }
        }
        $data['amount'] = $this->getAmountInteger();

        if (isset($data['secret'])) {
            unset($data['secret']);
            $data['hash'] = md5(implode("", array_values($data)) . $this->getParameter('secret'));
        }

        return $data;
    }

    public function sendData($data)
    {
        return $this->response = new PurchaseResponse($this, $data);
    }

    /**
     * Send the request
     *
     * @return ResponseInterface
     */
    public function send()
    {
        return $this->sendData($this->getData());
    }
    
    public function setOrderId($value)
    {
    	return $this->setParameter('orderid', $value);
    }
    
    public function getOrderId($value)
    {
    	return $this->getParameter('orderid');
    }
    
    public function setAccepturl($value)
    {
    	return $this->setParameter('accepturl', $value);
    }
    
    public function getAccepturl($value)
    {
    	return $this->getParameter('accepturl');
    }
    
	public function setTimeout($value)
    {
    	return $this->setParameter('timeout', $value);
    }
    
    public function getTimeout($value)
    {
    	return $this->getParameter('timeout');
    }
}
