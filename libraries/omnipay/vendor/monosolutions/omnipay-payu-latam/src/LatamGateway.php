<?php


namespace Omnipay\PayU;


use Omnipay\Common\AbstractGateway;

class LatamGateway extends AbstractGateway
{

    public function getName()
    {
        return "PayU Latam";
    }

    public function getDefaultParameters()
    {
        return [
            "apiKey"     => "",
            "merchantId" => "",
            "accountId"  => "",
            "testMode"   => true,
        ];
    }

    public function purchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\PayU\Message\Latam\PurchaseRequest', $parameters);

    }

    public function completePurchase(array $parameters = array())
    {
    	return $this->createRequest('\Omnipay\PayU\Message\Latam\CompletePurchaseRequest', $parameters);
    }
    
}
