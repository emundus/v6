<?php

namespace Omnipay\Eway\Message;

use Omnipay\Common\Message\AbstractRequest;

/**
 * eWAY Direct Abstract Request
 */
abstract class DirectAbstractRequest extends AbstractRequest
{

    public function sendData($data)
    {
		// Support TLS 1.2
	    $config = $this->httpClient->getConfig();
	    $curlOptions = $config->get('curl.options');
	    $curlOptions[CURLOPT_SSLVERSION] = 6;
	    $config->set('curl.options', $curlOptions);
	    $this->httpClient->setConfig($config);
		// End support TLS 1.2
		
        $httpResponse = $this->httpClient->post($this->getEndpoint(), null, $data->asXML())->send();

        return $this->response = new DirectResponse($this, $httpResponse->xml());
    }

    public function getCustomerId()
    {
        return $this->getParameter('customerId');
    }

    public function setCustomerId($value)
    {
        return $this->setParameter('customerId', $value);
    }

    public function setOption1($value)
    {
        return $this->setParameter('option1', $value);
    }

    public function getOption1()
    {
        return $this->getParameter('option1');
    }

    public function setOption2($value)
    {
        return $this->setParameter('option2', $value);
    }

    public function getOption2()
    {
        return $this->getParameter('option2');
    }

    public function setOption3($value)
    {
        return $this->setParameter('option3', $value);
    }

    public function getOption3()
    {
        return $this->getParameter('option3');
    }

    /**
     * Get End Point
     *
     * Depends on Test or Live environment
     *
     * @return string
     */
    public function getEndpoint()
    {
        return $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;
    }
}
