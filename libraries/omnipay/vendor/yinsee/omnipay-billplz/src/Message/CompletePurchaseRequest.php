<?php

namespace Omnipay\Billplz\Message;

/**
 * Authorize Request
 */
class CompletePurchaseRequest extends AbstractRequest
{
    protected function createResponse($data, $statusCode)
    {
        return $this->response = new CompletePurchaseResponse($this, $data, $statusCode);
    }

    public function getHttpMethod()
    {
        return 'GET';
    }

    public function getAPI()
    {
        return 'bills/'.$this->getId();
    }

    public function getData()
    {
        return [];
    }
}
