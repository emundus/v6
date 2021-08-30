<?php


namespace Omnipay\PayU\Message\Latam;


use Omnipay\PayU\Message\Response;

abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    protected $liveEndpoint = "https://gateway.payulatam.com/ppp-web-gateway/";
    protected $testEndpoint = "https://stg.gateway.payulatam.com/ppp-web-gateway/";

    public function getEndpoint()
    {
        if ($this->getTestMode()) {
            return $this->testEndpoint;
        }
        return $this->liveEndpoint;
    }

    protected function createResponse(
        $data,
        $class = "Omnipay\\PayU\\Message\\Response"
    ) {
        return $this->response = new $class($this, $data);
    }
}
