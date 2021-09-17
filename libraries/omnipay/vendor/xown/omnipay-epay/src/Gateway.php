<?php

namespace Omnipay\Epay;

use Omnipay\Common\AbstractGateway;
use Omnipay\Epay\Message\CaptureRequest;
use Omnipay\Epay\Message\CompletePurchaseRequest;
use Omnipay\Epay\Message\DeleteRequest;
use Omnipay\Epay\Message\PurchaseRequest;
use Omnipay\Epay\Message\RefundRequest;

/**
 * Epay Gateway
 */
class Gateway extends AbstractGateway
{
    public function getName()
    {
        return 'Epay';
    }

    /**
     * @link http://tech.epay.dk/en/payment-window-parameters
     * @return array
     */
    public function getDefaultParameters()
    {
        return array(
            'merchantnumber' => '',
            'secret' => '',
            'language' => '0',
            'ownreceipt' => '1',
            'timeout'    => '',
            'paymentcollection' => '1',
            'lockpaymentcollection' => '1',
            'windowid' => '1'
        );
    }

    public function setTimeout($timeout)
    {
        $this->parameters->set('timeout', $timeout);
    }

    public function setMerchantnumber($merchantNumber)
    {
        $this->parameters->set('merchantnumber', (string) $merchantNumber);
    }

    public function setPaymenttype($paymenttype)
    {
        $this->parameters->set('paymenttype', $paymenttype);
    }

    public function setPaymentcollection($paymentcollection)
    {
        $this->parameters->set('paymentcollection', $paymentcollection);
    }

    public function setSecret($secret)
    {
        $this->parameters->set('secret', $secret);
    }

    public function setLanguage($language)
    {
        $this->parameters->set('language', $language);
    }

    public function setWindowstate($windowstate)
    {
        $this->parameters->set('windowstate', $windowstate);
    }

    public function setWindowid($windowId) {
        $this->parameters->set('windowid', $windowId);
    }

    public function setMobile($mobile) {
        $this->parameters->set('mobile', $mobile);
    }

    public function purchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Epay\Message\PurchaseRequest', $parameters);
    }

    /**
     * @param array $parameters
     * @return CompletePurchaseRequest
     */
    public function completePurchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Epay\Message\CompletePurchaseRequest', $parameters);
    }

    /**
     * @param array $parameters
     * @return CaptureRequest
     */
    public function capture(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Epay\Message\CaptureRequest', $parameters);
    }

    /**
     * @param array $parameters
     * @return RefundRequest
     */
    public function refund(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Epay\Message\RefundRequest', $parameters);
    }

    /**
     * @param array $parameters
     * @return DeleteRequest
     */
    public function delete(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Epay\Message\DeleteRequest', $parameters);
    }

}
