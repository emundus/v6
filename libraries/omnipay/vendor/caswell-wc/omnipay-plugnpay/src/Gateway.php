<?php

namespace Omnipay\PlugNPay;


use Omnipay\Common\AbstractGateway;

/**
 * Class Gateway
 * Plug & Pay Remote Client Integration
 *
 * ##Create your Omnipay PlugNPay object
 * <code>
 *
 * $gateway = Omnipay::create('PlugNPay');
 * $gateway->setUsername('<your username here>');
 * $gateway->setPassword('<your password here>');
 *
 * </code>
 *
 * @package Omnipay\PlugNPay
 */
class Gateway extends AbstractGateway
{

    /**
     * @return string Gateway name.
     */
    public function getName()
    {
        return 'Plug & Pay Remote Client';
    }

    /**
     * @return array Default parameters.
     */
    public function getDefaultParameters()
    {
        return [
            'username'=>'',
            'password'=>''
        ];
    }

    /**
     * @param $username
     *
     * @return $this
     */
    public function setUsername($username)
    {
        return $this->setParameter('username', $username);
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->getParameter('username');
    }

    /**
     * @param $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        return $this->setParameter('password', $password);
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->getParameter('password');
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Common\Message\AbstractRequest|\Omnipay\PlugNPay\Message\AuthorizeRequest
     */
    public function authorize(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\PlugNPay\Message\AuthorizeRequest', $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Common\Message\AbstractRequest|\Omnipay\PlugNPay\Message\CaptureRequest
     */
    public function capture(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\PlugNPay\Message\CaptureRequest', $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Common\Message\AbstractRequest|\Omnipay\PlugNPay\Message\PurchaseRequest
     */
    public function purchase(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\PlugNPay\Message\PurchaseRequest', $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Common\Message\AbstractRequest|\Omnipay\PlugNPay\Message\RefundRequest
     */
    public function refund(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\PlugNPay\Message\RefundRequest', $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Common\Message\AbstractRequest|\Omnipay\PlugNPay\Message\VoidRequest
     */
    public function void(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\PlugNPay\Message\VoidRequest', $parameters);
    }
}
