<?php

namespace Omnipay\PlugNPay\Message;

use Guzzle\Http\Message\Response as GuzzleResponse;
use Omnipay\Common\Message\AbstractRequest as BaseAbstractRequest;

abstract class AbstractRequest extends BaseAbstractRequest
{
    /** @var string Endpoint to send the data to */
    protected $endpoint = 'https://pay1.plugnpay.com/payment/pnpremote.cgi';
    /** @var string Tells PlugNPay what this request is doing. This should be overridden in each concrete class. */
    protected $mode;

    /**
     * @return string
     */
    public function getEndPoint()
    {
        return $this->endpoint;
    }

    /**
     * @param $value
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setUsername($value)
    {
        return $this->setParameter('username', $value);
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->getParameter('username');
    }

    /**
     * @param $value
     *
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setPassword($value)
    {
        return $this->setParameter('password', $value);
    }
    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->getParameter('password');
    }

    /**
     * Instantiate a new Response object with the data coming back from PlugNPay
     *
     * @param \Guzzle\Http\Message\Response $httpResponse
     *
     * @return \Omnipay\PlugNPay\Message\Response
     */
    protected function generateResponse(GuzzleResponse $httpResponse)
    {
        return $this->response = new Response($this, $httpResponse->getBody());
    }

    /**
     * @param array $data
     *
     * @return \Omnipay\PlugNPay\Message\Response
     */
    public function sendData($data)
    {
        $httpResponse = $this->httpClient->post(
            $this->getEndPoint(),
            null,
            http_build_query($data)
        )->send();

        return $this->generateResponse($httpResponse);
    }

    /**
     * Get base data that all requests use
     *
     * @return array
     */
    public function getBaseData()
    {
        $this->validate('username', 'password');

        return [
            'publisher-name'=>$this->getUsername(),
            'publisher-password'=>$this->getPassword(),
            'mode'=>$this->mode
        ];
    }
}
