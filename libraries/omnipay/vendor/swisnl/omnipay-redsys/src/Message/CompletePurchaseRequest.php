<?php

namespace Omnipay\RedSys\Message;

use Omnipay\Common\Exception\InvalidResponseException;

/**
 * RedSys Complete Purchase Request
 */
class CompletePurchaseRequest extends PurchaseRequest
{
    public function getData()
    {
        $query = $this->httpRequest->request;
        $signature = strtr($query->get('Ds_Signature'), '-_', '+/');
        $parameters = strtr($query->get('Ds_MerchantParameters'), '-_', '+/');

        $data = $this->getEncoder()->decode($parameters);

        if (!$this->getSigner()->validateSignature($signature, $parameters, $data['Ds_Order'])) {
            throw new InvalidResponseException('Invalid signature: ' . $signature);
        }

        return $data;
    }

    public function sendData($data)
    {
        return $this->response = new CompletePurchaseResponse($this, $data);
    }
}
