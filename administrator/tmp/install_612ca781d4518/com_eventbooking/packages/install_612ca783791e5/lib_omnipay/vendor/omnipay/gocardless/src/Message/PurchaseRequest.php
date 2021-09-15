<?php

namespace Omnipay\GoCardless\Message;

/**
 * GoCardless Purchase Request
 */
class PurchaseRequest extends AbstractRequest
{
    public function getData()
    {
        $this->validate('amount');

        $data = array();
        $data['client_id'] = $this->getAppId();
        $data['nonce'] = $this->generateNonce();
        $data['timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
        $data['redirect_uri'] = $this->getReturnUrl();
        $data['cancel_uri'] = $this->getCancelUrl();
        $data['state'] = $this->getState();
        $data['bill'] = array();
        $data['bill']['merchant_id'] = $this->getMerchantId();
        $data['bill']['amount'] = $this->getAmount();
        $data['bill']['name'] = $this->getDescription();

        if ($this->getCard()) {
            $data['bill']['user'] = array();
            $data['bill']['user']['first_name'] = $this->getCard()->getFirstName();
            $data['bill']['user']['last_name'] = $this->getCard()->getLastName();
            $data['bill']['user']['email'] = $this->getCard()->getEmail();
            $data['bill']['user']['billing_address1'] = $this->getCard()->getAddress1();
            $data['bill']['user']['billing_address2'] = $this->getCard()->getAddress2();
            $data['bill']['user']['billing_town'] = $this->getCard()->getCity();
            $data['bill']['user']['billing_county'] = $this->getCard()->getCountry();
            $data['bill']['user']['billing_postcode'] = $this->getCard()->getPostcode();
        }

        $data['signature'] = $this->generateSignature($data);

        return $data;
    }

    public function sendData($data)
    {
        return $this->response = new PurchaseResponse($this, $data);
    }
}
